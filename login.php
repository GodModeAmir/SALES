<?php
session_start();
include 'connection.php';

$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        try {
            $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $username);
            $stmt->execute();

            $user = $stmt->get_result()->fetch_assoc();

            if ($user && $user['password'] === $password) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } catch (mysqli_sql_exception $e) {
            $error = "An error occurred: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Account Login — Sales System</title>
    <style>
        :root {
            --sidebar-bg: #1c1c1c;
            --accent: #f0a500;
            --text-gray: #777;
        }
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f4f4f4; }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .login-card {
            display: flex;
            width: 100%;
            max-width: 880px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0,0,0,.1);
        }

        /* ===== Left brand panel (matches dashboard sidebar) ===== */
        .login-brand {
            width: 42%;
            background: var(--sidebar-bg);
            color: #fff;
            padding: 50px 40px 30px;
            display: flex;
            flex-direction: column;
        }
        .login-brand .brand-icon {
            width: 115px; height: 115px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 46px;
            color: #fff;
            margin: 0 auto 25px;
        }
        .login-brand h4 { font-weight: 600; text-align: center; margin-bottom: 8px; }
        .login-brand .brand-sub {
            text-align: center;
            color: #cfcfcf;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .brand-points { list-style: none; padding: 0 10px; margin: 0; }
        .brand-points li {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #cfcfcf;
            font-size: 14px;
            padding: 12px 2px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .brand-points li i { color: var(--accent); font-size: 17px; }
        .brand-footer {
            margin-top: auto;
            padding-top: 25px;
            font-size: 13px;
            color: #9a9a9a;
            line-height: 1.8;
            text-align: center;
        }
        .brand-footer a { color: var(--accent); text-decoration: none; }

        /* ===== Right form panel (matches main content area) ===== */
        .login-panel { flex: 1; padding: 55px 48px; }
        .page-title { font-weight: 600; color: #222; margin-bottom: 5px; }
        .page-sub { color: var(--text-gray); font-size: 15px; }

        .form-label { font-size: 13px; font-weight: 500; color: #555; }
        .input-group-text { background: #f4f4f4; color: var(--text-gray); }
        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 .2rem rgba(240,165,0,.15);
        }

        .btn-theme {
            background: var(--accent);
            border: 1px solid var(--accent);
            color: #fff;
        }
        .btn-theme:hover { background: #d89400; border-color: #d89400; color: #fff; }

        .link-theme { color: var(--accent); text-decoration: none; font-weight: 500; }
        .link-theme:hover { color: #d89400; }

        @media (max-width: 767px) {
            .login-card { flex-direction: column; max-width: 440px; }
            .login-brand { width: 100%; padding: 35px 30px 20px; }
            .brand-points { display: none; }
            .login-panel { padding: 35px 28px; }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">

            <!-- Left: brand panel (mirrors the sidebar) -->
            <div class="login-brand">
                <div class="brand-icon">
                    <i class="bi bi-bag-check-fill"></i>
                </div>
                <h4>Sales System</h4>
                <p class="brand-sub">Sign in to manage your products and cart.</p>

                <ul class="brand-points">
                    <li><i class="bi bi-box-seam"></i> Browse &amp; manage products</li>
                    <li><i class="bi bi-cart3"></i> Quick add-to-cart checkout</li>
                    <li><i class="bi bi-shield-lock"></i> Secure member access</li>
                </ul>

                <div class="brand-footer">
                    Copyright &copy;<?= date('Y') ?> All rights reserved |<br>
                    Sales System — made with <a href="#">Colorlib style</a>
                </div>
            </div>

            <!-- Right: login form -->
            <div class="login-panel">
                <h1 class="page-title">Sign In</h1>
                <p class="page-sub mb-4">Enter your credentials to access your portal.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 small rounded-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                        <button type="button" class="btn-close ms-auto p-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="" method="POST" autocomplete="off">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0"><i class="bi bi-person"></i></span>
                            <input type="text" id="username" name="username"
                                   class="form-control border-start-0 py-2" placeholder="e.g. klint" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0"><i class="bi bi-lock"></i></span>
                            <input type="password" id="password" name="password"
                                   class="form-control border-start-0 py-2" placeholder="••••••••" required>
                        </div>
                        <div class="text-end mt-1">
                            <a href="#" class="small link-theme">Forgot password?</a>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn btn-theme w-100 py-2 fw-semibold mt-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                    </button>
                </form>

                <div class="text-center pt-4 mt-4 border-top">
                    <p class="small mb-0" style="color: var(--text-gray);">
                        Don't have an account?
                        <a href="signup.php" class="link-theme">Sign Up</a>
                    </p>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>