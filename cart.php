<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Verify the user still exists
$stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();

if (!$stmt->get_result()->fetch_assoc()) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get cart items
$stmt = $conn->prepare("
    SELECT cd.prod_id, cd.cqty, p.name, p.price, p.stock, p.image
    FROM cart_master cm
    JOIN cart_details cd ON cd.cart_id = cm.cart_id
    JOIN products p ON p.id = cd.prod_id
    WHERE cm.user_id = ?
    ORDER BY cd.prod_id
");
$stmt->bind_param('i', $userId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total     = 0;
$cartCount = 0;
foreach ($items as $it) {
    $total     += $it['price'] * $it['cqty'];
    $cartCount += (int)$it['cqty'];
}

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
    <title>My Cart</title>
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

        /* ===== Cart specifics ===== */
        .cart-card, .summary-card {
            border: none;
            border-radius: 8px;
        }
        .cart-card table { font-size: 15px; }
        .cart-card thead th {
            font-weight: 600;
            color: #555;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .summary-header {
            background: var(--sidebar-bg);
            color: #fff;
            border-radius: 8px 8px 0 0 !important;
            font-weight: 500;
        }
        .summary-total {
            color: var(--accent);
        }
        .empty-state {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 5px rgba(0,0,0,.07);
        }

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
                <a href="index.php">
                    Products <i class="bi bi-chevron-down"></i>
                </a>
            </li>
            <li>
                <a href="cart.php" class="active">
                    My Cart
                    <?php if ($cartCount > 0): ?>
                        <span class="nav-badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li>
                <a href="index.php">Add Product <i class="bi bi-plus-lg"></i></a>
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
                <a href="index.php">Home</a>
                <a href="cart.php" class="active">Cart<?php if ($cartCount > 0): ?><span class="count-badge"><?= $cartCount > 99 ? '99+' : $cartCount ?></span><?php endif; ?></a>
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
            <div class="mb-4">
                <h1 class="page-title">My Cart</h1>
                <p class="page-sub mb-0">Review your items, update quantities, and place your order.</p>
            </div>

            <?php if (empty($items)): ?>
                <!-- Empty state -->
                <div class="empty-state text-center text-muted py-5">
                    <i class="bi bi-cart-x fs-1 d-block mb-2" style="color: var(--accent);"></i>
                    Your cart is empty.
                    <div class="mt-3">
                        <a href="index.php" class="btn btn-theme"><i class="bi bi-arrow-left me-1"></i>Continue Shopping</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <!-- Items table -->
                    <div class="col-lg-8">
                        <div class="card cart-card shadow-sm">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th style="width: 180px;">Quantity</th>
                                            <th>Subtotal</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $it): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <img src="<?= htmlspecialchars($it['image'] ?: 'uploads/placeholder.svg') ?>"
                                                             onerror="this.onerror=null; this.src='uploads/placeholder.svg';"
                                                             class="rounded" style="width: 50px; height: 50px; object-fit: cover;" alt="">
                                                        <div>
                                                            <?= htmlspecialchars($it['name']) ?>
                                                            <?php if ((int)$it['cqty'] > (int)$it['stock']): ?>
                                                                <div class="text-danger small">Only <?= (int)$it['stock'] ?> left in stock!</div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>$<?= number_format((float)$it['price'], 2) ?></td>
                                                <td>
                                                    <form action="update_cart.php" method="POST" class="d-flex gap-1">
                                                        <input type="hidden" name="product_id" value="<?= (int)$it['prod_id'] ?>">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="number" name="quantity" value="<?= (int)$it['cqty'] ?>"
                                                               min="1" max="<?= (int)$it['stock'] ?>"
                                                               class="form-control form-control-sm" style="width: 70px;">
                                                        <button class="btn btn-outline-theme btn-sm" title="Update">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                                <td class="fw-bold">$<?= number_format($it['price'] * $it['cqty'], 2) ?></td>
                                                <td>
                                                    <form action="update_cart.php" method="POST">
                                                        <input type="hidden" name="product_id" value="<?= (int)$it['prod_id'] ?>">
                                                        <input type="hidden" name="action" value="remove">
                                                        <button class="btn btn-outline-danger btn-sm" title="Remove">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Order summary -->
                    <div class="col-lg-4">
                        <div class="card summary-card shadow-sm">
                            <div class="card-header summary-header">
                                <i class="bi bi-receipt me-2" style="color: var(--accent);"></i>Order Summary
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Items</span><span><?= $cartCount ?></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                                    <span>Total</span><span class="summary-total">$<?= number_format($total, 2) ?></span>
                                </div>
                                <form action="checkout.php" method="POST"
                                      onsubmit="return confirm('Place this order for $<?= number_format($total, 2) ?>?');">
                                    <button type="submit" class="btn btn-theme w-100 mb-2">
                                        <i class="bi bi-bag-check me-1"></i>Place Order
                                    </button>
                                </form>
                                <a href="index.php" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-arrow-left me-1"></i>Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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