<?php
    session_start();
    include 'connection.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([(int)$_SESSION['user_id']]);
    if (!$stmt->fetch()) {
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

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT cart_id FROM cart_master WHERE user_id = ? ORDER BY cart_id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $cartId = $stmt->fetchColumn();
        if (!$cartId) {
            throw new RuntimeException('Your cart is empty.');
        }

        $stmt = $pdo->prepare("
            SELECT cd.prod_id, cd.cqty, p.name, p.price, p.stock
            FROM cart_details cd
            JOIN products p ON p.id = cd.prod_id
            WHERE cd.cart_id = ?
            FOR UPDATE
        ");
        $stmt->execute([$cartId]);
        $items = $stmt->fetchAll();

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

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
        $stmt->execute([$userId, $total]);
        $orderId = (int)$pdo->lastInsertId();

        // 2) Order lines + atomic stock decrement
        $lineStmt  = $pdo->prepare("INSERT INTO order_details (order_id, prod_id, qty, price) VALUES (?, ?, ?, ?)");
        $stockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

        foreach ($items as $it) {
            $lineStmt->execute([$orderId, $it['prod_id'], $it['cqty'], $it['price']]);

            $stockStmt->execute([$it['cqty'], $it['prod_id'], $it['cqty']]);
            if ($stockStmt->rowCount() === 0) {
                throw new RuntimeException("Stock for \"{$it['name']}\" just changed. Please review your cart.");
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM cart_details WHERE cart_id = ?");
        $stmt->execute([$cartId]);

        $pdo->commit();
        $_SESSION['flash'] = ['type' => 'success',
            'message' => "Order #$orderId placed successfully! Total: $" . number_format($total, 2)];
        header("Location: index.php");
        exit();

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
        header("Location: cart.php");
        exit();
    }
?>