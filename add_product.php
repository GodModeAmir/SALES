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

    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock       = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    $errors = [];
    if ($name === '' || mb_strlen($name) > 100) $errors[] = 'Name is required (max 100 chars).';
    if ($price === false || $price < 0)         $errors[] = 'Price must be a non-negative number.';
    if ($stock === false || $stock < 0)         $errors[] = 'Stock must be a non-negative integer.';

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload failed.';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image must be under 2MB.';
        } else {
            $mime    = mime_content_type($_FILES['image']['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

            if (!isset($allowed[$mime])) {
                $errors[] = 'Only JPG, PNG or WebP images are allowed.';
            } else {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                    $imagePath = 'uploads/' . $filename;
                } else {
                    $errors[] = 'Could not save the uploaded image.';
                }
            }
        }
    }

    if ($errors) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => implode(' ', $errors)];
        header("Location: index.php");
        exit();
    }

    $stmt = $pdo->prepare(
        "INSERT INTO products (name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$name, $description, $price, $stock, $imagePath]);

    $_SESSION['flash'] = ['type' => 'success', 'message' => "Product \"{$name}\" added."];
    header("Location: index.php");
    exit();
?>