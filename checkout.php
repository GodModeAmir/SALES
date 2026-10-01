<?php
    session_start();
    include 'connection.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();

    if (!$stmt->get_result()->fetch_assoc()) {
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: cart.php");
        exit();
    }

    $userId = (int)$_SESSION['user_id'];
    $transactionActive = false;

    try {
        $conn->begin_transaction();
        $transactionActive = true;

        $stmt = $conn->prepare("SELECT cart_id FROM cart_master WHERE user_id = ? ORDER BY cart_id DESC LIMIT 1");
        $stmt->bind_param('i', $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result->fetch_row();
        $cartId = $row ? (int)$row[0] : null;

        if (!$cartId) {
            throw new RuntimeException('Your cart is empty.');
        }

        $stmt = $conn->prepare("
            SELECT cd.prod_id, cd.cqty, p.name, p.price, p.stock
            FROM cart_details cd
            JOIN products p ON p.id = cd.prod_id
            WHERE cd.cart_id = ?
            FOR UPDATE
        ");
        $stmt->bind_param('i', $cartId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        if (empty($items)) {
            throw new RuntimeException('Your cart is empty.');
        }

        $total = 0;
        foreach ($items as $it) {
            if ((int)$it['cqty'] > (int)$it['stock']) {
                throw new RuntimeException(
                    "Not enough stock for \"{$it['name']}\" (requested {$it['cqty']}, only {$it['stock']} available)."
                );
            }
            $total += $it['price'] * $it['cqty'];
        }

        $stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
        $stmt->bind_param('id', $userId, $total);
        $stmt->execute();
        $orderId = (int)$conn->insert_id;

        $lineStmt  = $conn->prepare("INSERT INTO order_details (order_id, prod_id, qty, price) VALUES (?, ?, ?, ?)");
        $stockStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

        foreach ($items as $it) {
            $lineStmt->bind_param('iiid', $orderId, $it['prod_id'], $it['cqty'], $it['price']);
            $lineStmt->execute();

            $stockStmt->bind_param('iii', $it['cqty'], $it['prod_id'], $it['cqty']);
            $stockStmt->execute();

            if ($stockStmt->affected_rows === 0) {
                throw new RuntimeException("Stock for \"{$it['name']}\" just changed. Please review your cart.");
            }
        }

        $stmt = $conn->prepare("DELETE FROM cart_details WHERE cart_id = ?");
        $stmt->bind_param('i', $cartId);
        $stmt->execute();

        $conn->commit();
        $transactionActive = false;

        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => "Order #$orderId placed successfully! Total: $" . number_format($total, 2)
        ];
        header("Location: index.php");
        exit();

    } catch (Throwable $e) {
        if ($transactionActive) {
            $conn->rollback();
        }
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
        header("Location: cart.php");
        exit();
    }
?>