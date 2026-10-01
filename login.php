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
    <title>Account Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light-subtle">

    <div class="container min-vh-100 d-flex justify-content-center align-items-center py-5">
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            
            <!-- Professional Login Card -->
            <div class="card border border-light-subtle shadow-sm rounded-3 p-4 p-sm-5 bg-white">
                
                <!-- Header / Logo Area -->
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center rounded-3 mb-3" style="width: 52px; height: 52px;">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Sign In</h3>
                    <p class="text-muted small mb-0">Enter your credentials to access your portal</p>
                </div>

                <!-- Dismissible Error Alert -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2 px-3 small rounded-2 mb-4" role="alert">
                        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                        <div><?= htmlspecialchars($error) ?></div>
                        <button type="button" class="btn-close ms-auto p-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Form -->
                <form action="" method="POST" autocomplete="off">
                    
                    <!-- Username Input -->
                    <div class="mb-3">
                        <label for="username" class="form-label small fw-medium text-secondary">Username or Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" id="username" name="username" class="form-control border-start-0 py-2 ps-2" placeholder="e.g. klint" required autofocus>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="mb-4">
                        <label for="password" class="form-label small fw-medium text-secondary">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" class="form-control border-start-0 py-2 ps-2" placeholder="••••••••" required>
                        </div>
                        
                        <!-- Forgot Password Link Below Input -->
                        <div class="text-end mt-1">
                            <a href="#" class="small text-decoration-none text-primary">Forgot password?</a>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-semibold rounded-2 mb-3">
                        Sign In
                    </button>
                </form>

                <!-- Card Footer -->
                <div class="text-center pt-3 border-top mt-2">
                    <p class="small text-secondary mb-0">
                        Don't have an account? 
                        <a href="signup.php" class="text-primary fw-semibold text-decoration-none">Sign Up</a>
                    </p>
                </div>

            </div>

            <!-- Page Bottom Copyright -->
            <div class="text-center mt-4">
                <p class="small text-muted mb-0">&copy; <?= date('Y') ?> Management System. All rights reserved.</p>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>