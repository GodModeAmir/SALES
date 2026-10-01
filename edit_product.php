<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Verify user still exists
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();

if (!$stmt->get_result()->fetch_assoc()) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock       = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    if (!$id || $name === '' || $price === false || $price < 0 || $stock === false || $stock < 0) {
        $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Please fill in all fields correctly.'];
        header("Location: edit_product.php?id=" . ($id ?: 0));
        exit();
    }

    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Image upload failed.'];
            header("Location: edit_product.php?id=$id");
            exit();
        }
        if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Image must be 2MB or smaller.'];
            header("Location: edit_product.php?id=$id");
            exit();
        }
        $finfo   = new finfo(FILEINFO_MIME_TYPE);
        $mime    = $finfo->file($_FILES['image']['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Only JPG, PNG or WebP images are allowed.'];
            header("Location: edit_product.php?id=$id");
            exit();
        }
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        $imagePath = 'uploads/' . uniqid('product_', true) . '.' . $allowed[$mime];
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Could not save the uploaded image.'];
            header("Location: edit_product.php?id=$id");
            exit();
        }
    }

    if ($imagePath) {
        // Get old image path
        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_row();
        $oldImage = $row ? $row[0] : null;

        // Update with new image
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ? WHERE id = ?");
        $stmt->bind_param('ssdssi', $name, $description, $price, $stock, $imagePath, $id);
        $stmt->execute();

        // Delete old image file
        if ($oldImage && strpos($oldImage, 'uploads/') === 0 && file_exists($oldImage)) {
            unlink($oldImage);
        }
    } else {
        // Update without changing image
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ? WHERE id = ?");
        $stmt->bind_param('ssdii', $name, $description, $price, $stock, $id);
        $stmt->execute();
    }

    $_SESSION['flash'] = ['type' => 'success', 'message' => "Product \"$name\" updated."];
    header("Location: index.php");
    exit();
}

// GET request: show edit form
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'No product specified.'];
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, name, description, price, stock, image FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Product not found.'];
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Edit Product</title>
</head>
<body class="bg-light">
    <div class="container py-4" style="max-width: 640px;">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-pencil-square me-2"></i>Edit Product #<?= (int)$product['id'] ?>
            </div>
            <div class="card-body">
                <form action="edit_product.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">

                    <div class="text-center mb-3">
                        <img src="<?= htmlspecialchars($product['image'] ?: 'uploads/placeholder.svg') ?>"
                             onerror="this.onerror=null; this.src='uploads/placeholder.svg';"
                             class="rounded" style="height: 140px; object-fit: cover;" alt="Current image">
                        <div class="form-text">Current image</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="name" required maxlength="100"
                               value="<?= htmlspecialchars($product['name']) ?>">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Price ($)</label>
                            <input type="number" class="form-control" name="price" step="0.01" min="0" required
                                   value="<?= htmlspecialchars($product['price']) ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Stock Qty</label>
                            <input type="number" class="form-control" name="stock" min="0" required
                                   value="<?= (int)$product['stock'] ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"
                                  maxlength="500"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Replace Image (optional)</label>
                        <input type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Leave empty to keep the current image.</div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="index.php" class="btn btn-outline-secondary flex-fill">Cancel</a>
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="bi bi-save me-1"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>