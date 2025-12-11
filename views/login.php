<?php
require_once '../controllers/AuthController.php';

$auth = new AuthController();
$auth->checkLogin(); 
$error = $auth->handleLogin(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0F172B; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; border-radius: 15px; overflow: hidden; }
        .card-header { background-color: #FEA116; color: white; text-align: center; padding: 30px; }
        .btn-login { background-color: #FEA116; border: none; color: white; width: 100%; padding: 10px; font-weight: bold; }
        .btn-login:hover { background-color: #d98a12; }
    </style>
</head>
<body>
    <div class="card login-card shadow-lg">
        <div class="card-header">
            <h2><i class="fa fa-user-lock me-2"></i>Login Staff</h2>
        </div>
        <div class="card-body p-4 bg-white">
            <?php if($error): ?>
                <div class="alert alert-danger text-center p-2 small"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="fw-bold small">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="fw-bold small">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-login">MASUK</button>
            </form>
        </div>
    </div>
</body>
</html>