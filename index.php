<?php
session_start();
include 'connection.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

// No parameters, so a plain query() is fine
$result = $conn->query("SELECT id, name, description, price, stock, image FROM products ORDER BY id DESC");
$products = $result->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("
    SELECT COALESCE(SUM(cd.cqty), 0)
    FROM cart_master cm
    JOIN cart_details cd ON cd.cart_id = cm.cart_id
    WHERE cm.user_id = ?
");
$stmt->bind_param('i', $userId);
$stmt->execute();

// Equivalent to PDO's fetchColumn()
$row = $stmt->get_result()->fetch_row();
$cartCount = (int)($row[0] ?? 0);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$username = htmlspecialchars($_SESSION['username'] ?? 'User');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Dashboard</title>
    <style>
        :root {
            --sidebar-bg: #1c1c1c;
            --accent: #f0a500;
            --text-gray: #777;
        }
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f4f4f4; }

        /* ===== Sidebar ===== */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 270px;
            height: 100vh;
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            z-index: 1045;
            transition: transform .3s ease;
        }
        .sidebar .profile {
            text-align: center;
            padding: 45px 20px 25px;
        }
        .sidebar .profile img {
            width: 115px; height: 115px;
            border-radius: 50%;
            object-fit: cover;
        }
        .sidebar .profile h6 {
            color: #fff;
            margin-top: 15px;
            font-weight: 500;
        }
        .side-nav {
            list-style: none;
            padding: 0 25px;
            margin: 0;
        }
        .side-nav li a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #cfcfcf;
            text-decoration: none;
            padding: 14px 2px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            font-size: 15px;
            transition: color .2s;
        }
        .side-nav li a:hover,
        .side-nav li a.active { color: var(--accent); }
        .side-nav .nav-badge {
            background: var(--accent);
            color: #fff;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 20px;
        }
        .side-footer {
            margin-top: auto;
            padding: 25px;
            font-size: 13px;
            color: #9a9a9a;
            line-height: 1.8;
        }
        .side-footer a { color: var(--accent); text-decoration: none; }

        /* ===== Main area ===== */
        .main {
            margin-left: 270px;
            min-height: 100vh;
            padding: 30px 40px;
            transition: margin-left .3s ease;
        }
        .topbar {
            background: #fff;
            box-shadow: 0 1px 5px rgba(0,0,0,.07);
            padding: 14px 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 35px;
        }
        .menu-btn {
            width: 42px; height: 42px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .top-links { display: flex; align-items: center; }
        .top-links a {
            color: var(--text-gray);
            text-decoration: none;
            margin-left: 25px;
            font-size: 15px;
        }
        .top-links a.active,
        .top-links a:hover { color: #222; font-weight: 600; }
        .top-links .count-badge {
            background: var(--accent);
            color: #fff;
            font-size: 11px;
            padding: 1px 7px;
            border-radius: 20px;
            margin-left: 4px;
        }

        .page-title {
            font-weight: 600;
            color: #222;
            margin-bottom: 5px;
        }
        .page-sub { color: var(--text-gray); font-size: 15px; }

        /* ===== Theme buttons ===== */
        .btn-theme {
            background: var(--accent);
            border: 1px solid var(--accent);
            color: #fff;
        }
        .btn-theme:hover { background: #d89400; border-color: #d89400; color: #fff; }
        .btn-outline-theme {
            border: 1px solid var(--accent);
            color: var(--accent);
            background: transparent;
        }
        .btn-outline-theme:hover { background: var(--accent); color: #fff; }
        .badge-theme { background: var(--accent); color: #fff; }

        /* ===== Product cards ===== */
        .product-card {
            border: none;
            border-radius: 8px;
            transition: transform .2s, box-shadow .2s;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,.1) !important;
        }
        .product-card .card-title { font-weight: 600; font-size: 1rem; }

        /* ===== Sidebar toggle behaviour ===== */
        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1040;
        }
        @media (min-width: 992px) {
            body.toggled .sidebar { transform: translateX(-100%); }
            body.toggled .main { margin-left: 0; }
        }
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            body.toggled .sidebar { transform: translateX(0); }
            body.toggled .overlay { display: block; }
            .main { margin-left: 0 !important; padding: 20px; }
            .top-links a { margin-left: 15px; }
        }
    </style>
</head>
<body>

    <!-- Overlay for mobile sidebar -->
    <div class="overlay" id="overlay"></div>

    <!-- ===== Sidebar ===== -->
    <aside class="sidebar" id="sidebar">
        <div class="profile">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($username) ?>&background=f0a500&color=fff&size=256"
                 onerror="this.onerror=null; this.src='uploads/placeholder.svg';"
                 alt="Profile">
            <h6>Welcome, <?= $username ?></h6>
        </div>

        <ul class="side-nav">
            <li>
                <a href="index.php" class="active">
                    Products <i class="bi bi-chevron-down"></i>
                </a>
            </li>
            <li>
                <a href="cart.php">
                    My Cart
                    <?php if ($cartCount > 0): ?>
                        <span class="nav-badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="#" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    Add Product <i class="bi bi-plus-lg"></i>
                </a>
            </li>
            <li>
                <a href="logout.php">Logout <i class="bi bi-box-arrow-right"></i></a>
            </li>
        </ul>

        <div class="side-footer">
            Copyright ©<?= date('Y') ?> All rights reserved |<br>
            Sales System — made with <a href="#">Colorlib style</a>
        </div>
    </aside>

    <!-- ===== Main ===== -->
    <div class="main">

        <!-- Top bar -->
        <header class="topbar">
            <button class="menu-btn" id="menuToggle" type="button">
                <i class="bi bi-list"></i>
            </button>
            <nav class="top-links">
                <a href="index.php" class="active">Home</a>
                <a href="cart.php">Cart<?php if ($cartCount > 0): ?><span class="count-badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span><?php endif; ?></a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>

        <div class="content">

            <?php if ($flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show">
                    <?= htmlspecialchars($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Page heading -->
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h1 class="page-title">Products</h1>
                    <p class="page-sub mb-0">Browse the catalog, edit items, or add products to your cart.</p>
                </div>
                <button class="btn btn-theme" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus-lg me-1"></i>Add Product
                </button>
            </div>

            <!-- Product Grid -->
            <?php if (empty($products)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No products yet. Click <strong>Add Product</strong> to create one.
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
                    <?php foreach ($products as $p): ?>
                        <div class="col">
                            <div class="card product-card h-100 shadow-sm">
                                <img src="<?= htmlspecialchars($p['image'] ?: 'uploads/placeholder.svg') ?>"
                                     onerror="this.onerror=null; this.src='uploads/placeholder.svg';"
                                     class="card-img-top"
                                     alt="<?= htmlspecialchars($p['name']) ?>"
                                     style="height: 180px; object-fit: cover;">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h5 class="card-title mb-0"><?= htmlspecialchars($p['name']) ?></h5>
                                        <span class="badge badge-theme">$<?= number_format((float)$p['price'], 2) ?></span>
                                    </div>
                                    <p class="card-text text-muted small flex-grow-1">
                                        <?= htmlspecialchars($p['description']) ?>
                                    </p>
                                    <p class="mb-2">
                                        <?php if ((int)$p['stock'] > 0): ?>
                                            <span class="badge text-bg-secondary">
                                                <i class="bi bi-stack me-1"></i><?= (int)$p['stock'] ?> in stock
                                            </span>
                                        <?php else: ?>
                                            <span class="badge text-bg-danger">Out of stock</span>
                                        <?php endif; ?>
                                    </p>

                                    <!-- Edit + Add to Cart -->
                                    <div class="mt-auto">
                                        <a href="edit_product.php?id=<?= (int)$p['id'] ?>"
                                           class="btn btn-outline-theme btn-sm w-100 mb-2">
                                            <i class="bi bi-pencil me-1"></i>Edit Product
                                        </a>
                                        <form action="add_to_cart.php" method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                                            <input type="number" name="quantity" value="1" min="1"
                                                   max="<?= (int)$p['stock'] ?>"
                                                   class="form-control form-control-sm" style="width: 70px;"
                                                   <?= (int)$p['stock'] === 0 ? 'disabled' : '' ?>>
                                            <button type="submit" class="btn btn-theme btn-sm flex-grow-1"
                                                    <?= (int)$p['stock'] === 0 ? 'disabled' : '' ?>>
                                                <i class="bi bi-cart-plus me-1"></i>Add to Cart
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 8px;">
                <form action="add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">
                            <i class="bi bi-plus-circle me-2" style="color: var(--accent);"></i>Add New Product
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="name" required maxlength="100">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="productPrice" class="form-label">Price ($)</label>
                                <input type="number" class="form-control" id="productPrice" name="price"
                                       step="0.01" min="0" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="productStock" class="form-label">Stock Qty</label>
                                <input type="number" class="form-control" id="productStock" name="stock"
                                       min="0" value="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="productDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="productDescription" name="description"
                                      rows="3" maxlength="500"></textarea>
                        </div>
                        <div class="mb-1">
                            <label for="productImage" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="productImage" name="image"
                                   accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG or WebP — max 2MB. Leave empty to use the default image.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-theme">
                            <i class="bi bi-save me-1"></i>Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar (collapse on desktop, slide-in on mobile)
        const menuToggle = document.getElementById('menuToggle');
        const overlay = document.getElementById('overlay');

        menuToggle.addEventListener('click', () => {
            document.body.classList.toggle('toggled');
        });
        overlay.addEventListener('click', () => {
            document.body.classList.remove('toggled');
        });
    </script>
</body>
</html>