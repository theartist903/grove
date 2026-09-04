<?php
require_once __DIR__ . '/../includes/auth.php';

admin_session_start();
if (admin_is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    if (admin_attempt_login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en-US">
<head>
  <meta charset="UTF-8" />
  <title>Admin Login | The Grove</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../assets/css/bootstrap.css" media="all" />
  <link rel="icon" href="../assets/images/06/Favicon.png" sizes="32x32" />
  <style>
    body {
      font-family: "Open Sans", Arial, sans-serif;
      background: linear-gradient(135deg, #6c3fa0, #9c5fd6);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
      padding: 20px;
    }
    .login-box {
      background: #fff;
      padding: 40px 36px;
      border-radius: 12px;
      box-shadow: 0 15px 40px rgba(0,0,0,0.25);
      width: 100%;
      max-width: 360px;
    }
    .login-box img {
      display: block;
      margin: 0 auto 16px;
      height: 64px;
      width: auto;
    }
    .login-box h1 {
      font-size: 18px;
      margin: 0 0 24px;
      text-align: center;
      color: #6c3fa0;
      font-weight: 700;
    }
    .login-box .form-label {
      font-weight: 600;
      font-size: 14px;
    }
    .login-box .btn-login {
      width: 100%;
      background: linear-gradient(120deg, #6c3fa0, #9c5fd6);
      color: #fff;
      border: none;
      font-weight: 700;
      padding: 10px;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <img src="../assets/images/06/Logo-Final.png" alt="The Grove">
    <h1>Admin Login</h1>
    <?php if ($error): ?>
      <div class="alert alert-danger py-2" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
      <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" id="username" name="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-login">Log In</button>
    </form>
  </div>
</body>
</html>
