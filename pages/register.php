<?php
require_once __DIR__ . '/../config.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $name = trim($_POST['name'] ?? '');
  $role = $_POST['role'] ?? 'general';
  
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
  }
  if (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters.';
  }
  if (!$errors) {
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE username = ? OR email = ? LIMIT 1');
    $stmt->execute([$username, $email]);
    if ($stmt->fetch()) {
      $errors[] = 'Username or email already exists.';
    }
  }
  if (!$errors) {
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, name, role) VALUES (?,?,?,?,?)');
    $stmt->execute([$username, $email, $hash, $name, $role]);
    header('Location: login.php?registered=1');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Band Management</title>
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

    .register-container {
      max-width: 950px;
      width: 100%;
      display: grid;
      grid-template-columns: 1fr 420px;
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

    .register-form-section {
      padding: 60px 50px;
      background: #fff;
    }

    .register-header h2 {
      font-size: 36px;
      color: #2B124C;
      font-weight: 800;
      margin-bottom: 8px;
    }

    .register-header p {
      font-size: 15px;
      color: #999;
      font-weight: 500;
      margin-bottom: 35px;
    }

    .notification {
      padding: 14px 18px;
      border-radius: 12px;
      margin-bottom: 25px;
      font-size: 14px;
      font-weight: 600;
      text-align: center;
    }

    .notification.error {
      background: rgba(244, 67, 54, 0.1);
      border: 1px solid rgba(244, 67, 54, 0.3);
      color: #F44336;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      color: #522B5B;
      font-size: 14px;
      font-weight: 600;
    }

    .form-group input,
    .form-group select {
      width: 100%;
      padding: 14px 18px;
      border: 2px solid #f0f0f0;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 500;
      color: #333;
      transition: all 0.3s ease;
      background: #fafafa;
      box-sizing: border-box;
    }

    .form-group input:focus,
    .form-group select:focus {
      outline: none;
      border-color: #A084CA;
      background: #fff;
      box-shadow: 0 0 0 4px rgba(160, 132, 202, 0.1);
    }

    .form-group input::placeholder {
      color: #bbb;
    }

    .btn-register {
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
      margin-top: 10px;
      margin-bottom: 25px;
    }

    .btn-register:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 35px rgba(82, 43, 91, 0.45);
    }

    .divider {
      text-align: center;
      margin: 25px 0;
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

    .btn-login {
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

    .btn-login:hover {
      background: #854F6C;
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(133, 79, 108, 0.35);
    }

    .register-image-section {
      background: url('https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?q=80&w=1000') center/cover;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .register-image-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(160, 132, 202, 0.8), rgba(191, 172, 226, 0.7));
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

    @media (max-width: 768px) {
      .register-container {
        grid-template-columns: 1fr;
        max-width: 500px;
      }
      
      .register-image-section {
        height: 300px;
        order: 2;
      }

      .image-text h3 {
        font-size: 38px;
      }

      .image-text p {
        font-size: 15px;
      }

      .register-form-section {
        padding: 40px 30px;
      }

      .register-header h2 {
        font-size: 32px;
      }
    }
  </style>
</head>
<body>
  <div class="register-container">
    <div class="register-form-section">
      <div class="register-header">
        <h2>Sign Up</h2>
        <p>Create your account to get started</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="notification error"><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="form-group">
          <label>Full Name</label>
          <input type="text" name="name" placeholder="Enter your full name" required>
        </div>

        <div class="form-group">
          <label>Username</label>
          <input type="text" name="username" placeholder="Choose a username" required>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Create a password (min 6 characters)" required>
        </div>

        <div class="form-group">
          <label>Role</label>
          <select name="role" required>
            <option value="general">General</option>
            <option value="manager">Manager</option>
            <option value="venue_owner">Venue Owner</option>
          </select>
        </div>

        <button type="submit" class="btn-register">Create Account</button>
      </form>

      <div class="divider">
        <span>Already have an account?</span>
      </div>

      <button type="button" class="btn-login" onclick="window.location.href='<?= BASE_URL ?>/pages/login.php'">Login</button>
    </div>

    <div class="register-image-section">
      <div class="image-text">
        <h3>Join Us</h3>
        <p>Start your journey in band management today</p>
      </div>
    </div>
  </div>
</body>
</html>