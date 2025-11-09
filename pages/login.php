<?php
require_once __DIR__ . '/../config.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $login = trim($_POST['login'] ?? '');
  $password = $_POST['password'] ?? '';
  $stmt = $pdo->prepare('SELECT user_id, password_hash, role FROM users WHERE email = ? OR username = ? LIMIT 1');
  $stmt->execute([$login,$login]);
  $user = $stmt->fetch();
  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'] ?? 'general';
    header('Location: dashboard.php');
    exit;
  } else {
    $errors[] = 'Invalid credentials.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Band Management</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #2B124C 0%, #522B5B 50%, #854F6C 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .login-container {
      max-width: 950px;
      width: 100%;
      display: grid;
      grid-template-columns: 420px 1fr;
      background: #fff;
      border-radius: 25px;
      overflow: hidden;
      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.5);
      animation: slideIn 0.6s ease-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: scale(0.95);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .login-image-section {
      background: url('https://images.unsplash.com/photo-1470225620780-dba8ba36b745?q=80&w=1000') center/cover;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-image-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(43, 18, 76, 0.75), rgba(133, 79, 108, 0.65));
    }

    .image-text {
      position: relative;
      z-index: 1;
      text-align: center;
      padding: 40px;
      color: #fff;
    }

    .image-text h3 {
      font-family: 'Playfair Display', serif;
      font-size: 52px;
      font-weight: 800;
      margin-bottom: 20px;
      text-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
      line-height: 1.2;
    }

    .image-text p {
      font-size: 17px;
      font-weight: 400;
      line-height: 1.6;
      opacity: 0.95;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
    }

    .login-form-section {
      padding: 70px 60px;
      background: #fff;
    }

    .login-header h2 {
      font-size: 36px;
      color: #2B124C;
      font-weight: 800;
      margin-bottom: 8px;
    }

    .login-header p {
      font-size: 15px;
      color: #999;
      font-weight: 500;
      margin-bottom: 40px;
    }

    .notification {
      padding: 14px 18px;
      border-radius: 12px;
      margin-bottom: 25px;
      font-size: 14px;
      font-weight: 600;
      text-align: center;
    }

    .notification.success {
      background: rgba(76, 175, 80, 0.1);
      border: 1px solid rgba(76, 175, 80, 0.3);
      color: #4CAF50;
    }

    .notification.error {
      background: rgba(244, 67, 54, 0.1);
      border: 1px solid rgba(244, 67, 54, 0.3);
      color: #F44336;
    }

    .form-group {
      margin-bottom: 25px;
    }

    .form-group label {
      display: block;
      margin-bottom: 10px;
      color: #522B5B;
      font-size: 14px;
      font-weight: 600;
    }

    .form-group input {
      width: 100%;
      padding: 16px 20px;
      border: 2px solid #f0f0f0;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 500;
      color: #333;
      transition: all 0.3s ease;
      background: #fafafa;
      box-sizing: border-box;
    }

    .form-group input:focus {
      outline: none;
      border-color: #A084CA;
      background: #fff;
      box-shadow: 0 0 0 4px rgba(160, 132, 202, 0.1);
    }

    .form-group input::placeholder {
      color: #bbb;
    }

    .forgot-password {
      text-align: right;
      margin-bottom: 30px;
    }

    .forgot-password a {
      font-size: 14px;
      color: #854F6C;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .forgot-password a:hover {
      color: #522B5B;
    }

    .btn-login {
      width: 100%;
      padding: 17px;
      background: linear-gradient(135deg, #522B5B, #854F6C, #A084CA);
      color: #F8E4D8;
      border: none;
      border-radius: 12px;
      font-size: 17px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 10px 25px rgba(82, 43, 91, 0.35);
      margin-bottom: 25px;
    }

    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(82, 43, 91, 0.45);
    }

    .divider {
      text-align: center;
      margin: 30px 0 25px 0;
      position: relative;
    }

    .divider::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      width: 100%;
      height: 1px;
      background: #e5e5e5;
    }

    .divider span {
      background: #fff;
      padding: 0 20px;
      position: relative;
      font-size: 14px;
      color: #999;
      font-weight: 600;
    }

    .btn-register {
      width: 100%;
      padding: 17px;
      background: transparent;
      color: #854F6C;
      border: 2px solid #854F6C;
      border-radius: 12px;
      font-size: 17px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-register:hover {
      background: #854F6C;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(133, 79, 108, 0.35);
    }

    @media (max-width: 768px) {
      .login-container {
        grid-template-columns: 1fr;
        max-width: 500px;
      }
      
      .login-image-section {
        height: 300px;
      }

      .image-text h3 {
        font-size: 38px;
      }

      .image-text p {
        font-size: 15px;
      }

      .login-form-section {
        padding: 50px 35px;
      }

      .login-header h2 {
        font-size: 32px;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-image-section">
      <div class="image-text">
        <h3>Welcome Back</h3>
        <p>Sign in to continue managing your bands and venues</p>
      </div>
    </div>

    <div class="login-form-section">
      <div class="login-header">
        <h2>Login</h2>
        <p>Enter your credentials to continue</p>
      </div>

      <?php if (!empty($_GET['registered'])): ?>
        <div class="notification success">Registration successful. Please login.</div>
      <?php endif; ?>
      
      <?php if (!empty($errors)): ?>
        <div class="notification error"><?=htmlspecialchars($errors[0])?></div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="form-group">
          <label>Username or Email</label>
          <input type="text" name="login" placeholder="Enter your username or email" required>
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <div class="forgot-password">
          <a href="#">Forgot Password?</a>
        </div>

        <button type="submit" class="btn-login">Login</button>
      </form>

      <div class="divider">
        <span>Don't have an account?</span>
      </div>

      <button type="button" class="btn-register" onclick="window.location.href='<?= BASE_URL ?>/pages/register.php'">Sign Up</button>
    </div>
  </div>
</body>
</html>