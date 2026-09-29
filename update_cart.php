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

    $userId    = (int)$_SESSION['user_id'];
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $action    = $_POST['action'] ?? '';

    if (!$productId || !in_array($action, ['update', 'remove'], true)) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Invalid request.'];
        header("Location: cart.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT cart_id FROM cart_master WHERE user_id = ? ORDER BY cart_id DESC LIMIT 1");
    $stmt->execute([$userId]);
    $cartId = $stmt->fetchColumn();

    if (!$cartId) {
        header("Location: cart.php");
        exit();
    }

    if ($action === 'remove') {
        $stmt = $pdo->prepare("DELETE FROM cart_details WHERE cart_id = ? AND prod_id = ?");
        $stmt->execute([$cartId, $productId]);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Item removed from cart.'];
    } else {
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        if (!$quantity || $quantity < 1) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Quantity must be at least 1.'];
            header("Location: cart.php");
            exit();
        }
        $stmt = $pdo->prepare("SELECT name, stock FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Product not found.'];
        } elseif ($quantity > (int)$product['stock']) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => "Only {$product['stock']} of \"{$product['name']}\" in stock."];
        } else {
            $stmt = $pdo->prepare("UPDATE cart_details SET cqty = ? WHERE cart_id = ? AND prod_id = ?");
            $stmt->execute([$quantity, $cartId, $productId]);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Cart updated.'];
        }
    }

    header("Location: cart.php");
    exit();
?>