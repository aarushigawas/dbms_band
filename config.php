<?php
// config.php - DB connection + common helpers (PDO)
session_start();

$DB_HOST = '127.0.0.1';
$DB_NAME = 'band';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3307; // Change if your MySQL runs on a different port (default 3306)

try {
  $pdo = new PDO("mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
  die("DB connection failed: " . $e->getMessage());
}

// Role ID helpers: return existing id or create a new row bound to this user
function get_or_create_manager_id($user_id) {
  global $pdo;
  $q = $pdo->prepare('SELECT manager_id FROM manager WHERE user_id = ?');
  $q->execute([$user_id]);
  $id = $q->fetchColumn();
  if ($id) return (int)$id;
  $ins = $pdo->prepare('INSERT INTO manager (user_id, name, email, number) SELECT user_id, name, email, number FROM users WHERE user_id = ?');
  $ins->execute([$user_id]);
  return (int)$pdo->lastInsertId();
}

function get_or_create_owner_id($user_id) {
  global $pdo;
  $q = $pdo->prepare('SELECT owner_id FROM venue_owner WHERE user_id = ?');
  $q->execute([$user_id]);
  $id = $q->fetchColumn();
  if ($id) return (int)$id;
  $ins = $pdo->prepare('INSERT INTO venue_owner (user_id, name, email, number) SELECT user_id, name, email, number FROM users WHERE user_id = ?');
  $ins->execute([$user_id]);
  return (int)$pdo->lastInsertId();
}

function get_or_create_general_id($user_id) {
  global $pdo;
  $q = $pdo->prepare('SELECT general_id FROM gen_user WHERE user_id = ?');
  $q->execute([$user_id]);
  $id = $q->fetchColumn();
  if ($id) return (int)$id;
  $ins = $pdo->prepare('INSERT INTO gen_user (user_id, name, email, number) SELECT user_id, name, email, number FROM users WHERE user_id = ?');
  $ins->execute([$user_id]);
  return (int)$pdo->lastInsertId();
}

function is_logged_in() {
  return !empty($_SESSION['user_id']);
}

function current_user_id() {
  return $_SESSION['user_id'] ?? null;
}

function current_user_role() {
  return $_SESSION['role'] ?? 'general';
}

function require_role($roles) {
  $roles = (array)$roles;
  if (!in_array(current_user_role(), $roles, true)) {
    header('Location: /band/pages/dashboard.php');
    exit;
  }
}

function require_login() {
  if (!is_logged_in()) {
    header('Location: /band/pages/login.php');
    exit;
  }
}

// Ensure basic profile fields are filled before proceeding to important actions
function require_basic_profile() {
  if (!is_logged_in()) return; // login gate will handle redirects
  global $pdo;
  $uid = current_user_id();
  $stmt = $pdo->prepare('SELECT name, email FROM users WHERE user_id = ?');
  $stmt->execute([$uid]);
  $u = $stmt->fetch();
  $needs = (!$u || trim((string)$u['name']) === '' || trim((string)$u['email']) === '');
  if ($needs) {
    $msg = urlencode('Please fill your info in Profile before continuing.');
    header('Location: /band/pages/profile.php?msg=' . $msg);
    exit;
  }
}
?>
