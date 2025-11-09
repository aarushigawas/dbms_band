<?php
require_once __DIR__ . '/../config.php';
require_login();
$user_id = current_user_id();
// Get/Create role-specific ID for display
$role = current_user_role();
if ($role === 'manager') {
  $role_label = 'Manager ID';
  $role_id_value = get_or_create_manager_id($user_id);
} elseif ($role === 'venue_owner') {
  $role_label = 'Owner ID';
  $role_id_value = get_or_create_owner_id($user_id);
} else {
  $role_label = 'User ID';
  $role_id_value = get_or_create_general_id($user_id);
}
$errors = [];
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $number = trim($_POST['number'] ?? '');
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
  }
  if (!$errors) {
    $stmt = $pdo->prepare('SELECT user_id FROM users WHERE (email = ?) AND user_id <> ? LIMIT 1');
    $stmt->execute([$email,$user_id]);
    if ($stmt->fetch()) {
      $errors[] = 'Email already in use.';
    }
  }
  if (!$errors) {
    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, number = ? WHERE user_id = ?');
    $stmt->execute([$name,$email,$number,$user_id]);
    $msg = 'Profile updated.';
  }
}
$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
include __DIR__ . '/header.php';
?>

<style>
.profile-page-wrapper {
  background: linear-gradient(135deg, #2B124C 0%, #522B5B 50%, #854F6C 100%);
  min-height: 100vh;
  padding: 80px 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.profile-container {
  max-width: 900px;
  width: 100%;
  display: grid;
  grid-template-columns: 400px 1fr;
  background: linear-gradient(135deg, rgba(248, 228, 216, 0.98), rgba(223, 182, 178, 0.98));
  border-radius: 25px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
}

.profile-image-section {
  background: url('https://images.unsplash.com/photo-1557672172-298e090bd0f1?q=80&w=1000') center/cover;
  position: relative;
}

.profile-image-section::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(43, 18, 76, 0.3), rgba(133, 79, 108, 0.3));
}

.profile-form-section {
  padding: 60px 50px;
}

.profile-form-section h2 {
  font-size: 32px;
  color: #2B124C;
  margin: 0 0 10px 0;
  font-weight: 700;
}

.profile-subtitle {
  color: #854F6C;
  font-size: 15px;
  margin: 0 0 35px 0;
}

.notification {
  padding: 14px 18px;
  border-radius: 10px;
  margin-bottom: 25px;
  font-size: 14px;
  font-weight: 600;
}

.notification.success {
  background: rgba(76, 175, 80, 0.15);
  border: 1px solid rgba(76, 175, 80, 0.3);
  color: #2B5329;
}

.notification.error {
  background: rgba(244, 67, 54, 0.15);
  border: 1px solid rgba(244, 67, 54, 0.3);
  color: #5C1C19;
}

.form-group {
  margin-bottom: 22px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #522B5B;
  font-weight: 600;
  font-size: 14px;
}

.form-group input {
  width: 100%;
  padding: 14px 16px;
  border: 2px solid rgba(133, 79, 108, 0.2);
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.7);
  color: #2B124C;
  font-size: 15px;
  transition: all 0.3s ease;
  box-sizing: border-box;
  font-weight: 500;
}

.form-group input:focus {
  outline: none;
  border-color: #854F6C;
  background: rgba(255, 255, 255, 1);
  box-shadow: 0 0 0 3px rgba(133, 79, 108, 0.1);
}

.form-group input::placeholder {
  color: rgba(82, 43, 91, 0.4);
}

.btn-save {
  width: 100%;
  padding: 15px;
  background: linear-gradient(135deg, #522B5B, #854F6C);
  color: #F8E4D8;
  border: none;
  border-radius: 10px;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 10px;
}

.btn-save:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(82, 43, 91, 0.4);
}

@media (max-width: 768px) {
  .profile-container {
    grid-template-columns: 1fr;
    max-width: 500px;
  }
  
  .profile-image-section {
    height: 250px;
  }
  
  .profile-form-section {
    padding: 40px 30px;
  }
}
</style>

<div class="profile-page-wrapper">
  <div class="profile-container">
    <div class="profile-image-section"></div>
    
    <div class="profile-form-section">
      <h2>Profile</h2>
      <p class="profile-subtitle">Update your account information</p>

      <div style="margin: 0 0 15px 0; font-weight:700; color:#522B5B;">
        <?=$role_label?>: <span style="color:#2B124C;">#<?=$role_id_value?></span>
      </div>

      <?php if ($msg): ?>
        <div class="notification success"><?=htmlspecialchars($msg)?></div>
      <?php endif; ?>
      
      <?php if ($errors): ?>
        <div class="notification error"><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div>
      <?php endif; ?>

      <form method="post" action="">
        <div class="form-group">
          <label>Name</label>
          <input type="text" name="name" value="<?=htmlspecialchars($user['name'] ?? '')?>" required>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input type="email" name="email" value="<?=htmlspecialchars($user['email'] ?? '')?>" required>
        </div>

        <div class="form-group">
          <label>Phone</label>
          <input type="text" name="number" value="<?=htmlspecialchars($user['number'] ?? '')?>">
        </div>

        <button type="submit" class="btn-save">Save Changes</button>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>