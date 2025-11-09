<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) {
  header('Location: pages/dashboard.php');
  exit;
}
header('Location: pages/login.php');
exit;
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Band Management System</title>
  <link rel="stylesheet" href="/band/style.css">
</head>
<body>
  <main class="container">
    <h1>Welcome to Band Management</h1>
    <p><a href="pages/login.php">Login</a> or <a href="pages/register.php">Register</a></p>
  </main>
</body>
</html>
