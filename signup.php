<?php
    include 'connection.php';

    $error = '';

    if(isset($_POST['save'])){
        $f_name = trim($_POST['f_name']);
        $l_name = trim($_POST['l_name']);
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if(empty($f_name) || empty($l_name) || empty($email) || empty($username) || empty($password)){
            $error = "All fields are required.";
        } else {
            try {
                $sql = "INSERT INTO users (f_name, l_name, email, username, password)
                        VALUES (:f_name, :l_name, :email, :username, :password)";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':f_name' => $f_name,
                    ':l_name' => $l_name,
                    ':email' => $email,
                    ':username' => $username,
                    ':password' => $password 
                ]);

                header("Location: login.php");
                exit();
            } catch (PDOException $e) {
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <title>Sign Up</title>
</head>
<body class="bg-light">
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card p-4 shadow-sm" style="max-width: 500px; width: 100%;">
            <div class="text-center mb-4">
                <i class="bi bi-person-plus text-primary fs-1"></i>
                <h2 class="fw-bold mt-2">Create Account</h2>
                <p class="text-muted">Please fill in this form to register.</p>
            </div>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="row g-2 mb-3">
                    <div class="col">
                        <label class="form-label small fw-semibold">First Name</label>
                        <input type="text" name="f_name" class="form-control" required>
                    </div>
                    <div class="col">
                        <label class="form-label small fw-semibold">Last Name</label>
                        <input type="text" name="l_name" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <button type="submit" name="save" class="btn btn-primary w-100 py-2 fw-semibold">Sign Up</button>
                
                <div class="text-center mt-3">
                    <p class="small text-muted mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-semibold">Log In</a></p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
