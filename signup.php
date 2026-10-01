<?php
include 'connection.php';

$error = '';

if (isset($_POST['save'])) {
    $f_name   = trim($_POST['f_name']);
    $l_name   = trim($_POST['l_name']);
    $email    = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($f_name) || empty($l_name) || empty($email) || empty($username) || empty($password)) {
        $error = "All fields are required.";
    } else {
        try {
            $sql = "INSERT INTO users (f_name, l_name, email, username, password)
                    VALUES (?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssss', $f_name, $l_name, $email, $username, $password);
            $stmt->execute();

            header("Location: login.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light-subtle">

    <div class="container min-vh-100 d-flex justify-content-center align-items-center py-5">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
            
            <!-- Professional Registration Card -->
            <div class="card border border-light-subtle shadow-sm rounded-3 p-4 p-sm-5 bg-white">
                
                <!-- Header / Logo Area -->
                <div class="text-center mb-4">
                    <div class="bg-primary bg-opacity-10 text-primary d-inline-flex align-items-center justify-content-center rounded-3 mb-3" style="width: 52px; height: 52px;">
                        <i class="bi bi-person-plus-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Create Account</h3>
                    <p class="text-muted small mb-0">Fill in your details to get started</p>
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
                    
                    <!-- First & Last Name Row -->
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label for="f_name" class="form-label small fw-medium text-secondary">First Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" id="f_name" name="f_name" class="form-control border-start-0 py-2 ps-2" placeholder="" required autofocus>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label for="l_name" class="form-label small fw-medium text-secondary">Last Name</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                                    <i class="bi bi-person"></i>
                                </span>
                                <input type="text" id="l_name" name="l_name" class="form-control border-start-0 py-2 ps-2" placeholder="" required>
                            </div>
                        </div>
                    </div>

                    <!-- Email Input -->
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-medium text-secondary">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" id="email" name="email" class="form-control border-start-0 py-2 ps-2" placeholder="" required>
                        </div>
                    </div>

                    <!-- Username Input -->
                    <div class="mb-3">
                        <label for="username" class="form-label small fw-medium text-secondary">Username</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                                <i class="bi bi-at"></i>
                            </span>
                            <input type="text" id="username" name="username" class="form-control border-start-0 py-2 ps-2" placeholder="" required>
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="mb-4">
                        <label for="password" class="form-label small fw-medium text-secondary">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" id="password" name="password" class="form-control border-start-0 py-2 ps-2" placeholder="" required>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="save" class="btn btn-primary w-100 py-2 fw-semibold rounded-2 mb-3">
                        Create Account
                    </button>
                </form>

                <!-- Card Footer -->
                <div class="text-center pt-3 border-top mt-2">
                    <p class="small text-secondary mb-0">
                        Already have an account? 
                        <a href="login.php" class="text-primary fw-semibold text-decoration-none">Sign In</a>
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