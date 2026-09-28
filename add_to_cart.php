<?php
    session_start();
    include 'connection.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php");
        exit();
    }

    $userId    = (int)$_SESSION['user_id'];
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity  = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if (!$productId || !$quantity || $quantity < 1) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid product or quantity.'];
        header("Location: index.php");
        exit();
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ? FOR UPDATE");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            throw new RuntimeException('Product not found.');
        }

        $stmt = $pdo->prepare("SELECT cart_id FROM cart_master WHERE user_id = ? ORDER BY cart_id DESC LIMIT 1");
        $stmt->execute([$userId]);
        $cartId = $stmt->fetchColumn();

        if (!$cartId) {
            $stmt = $pdo->prepare("INSERT INTO cart_master (user_id) VALUES (?)");
            $stmt->execute([$userId]);
            $cartId = (int)$pdo->lastInsertId();
        }

        $stmt = $pdo->prepare("SELECT cqty FROM cart_details WHERE cart_id = ? AND prod_id = ?");
        $stmt->execute([$cartId, $productId]);
        $inCart = (int)($stmt->fetchColumn() ?: 0);

        if ($inCart + $quantity > (int)$product['stock']) {
            throw new RuntimeException(
                "Only {$product['stock']} of \"{$product['name']}\" in stock (you already have {$inCart} in your cart)."
            );
        }

        $stmt = $pdo->prepare("
            INSERT INTO cart_details (cart_id, prod_id, cqty)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE cqty = cqty + VALUES(cqty)
        ");
        $stmt->execute([$cartId, $productId, $quantity]);

        $pdo->commit();
        $_SESSION['flash'] = ['type' => 'success', 'message' => "\"{$product['name']}\" added to cart."];

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['flash'] = ['type' => 'danger', 'message' => $e->getMessage()];
    }

    header("Location: index.php");
    exit();
?>