<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('manager');
require_basic_profile();
// Ensure manager ID exists for this user
$manager_id = get_or_create_manager_id(current_user_id());

$errors = [];
$msg = '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($action === 'create_band') {
    $band_name = trim($_POST['band_name'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $formation_year = $_POST['formation_year'] !== '' ? (int)$_POST['formation_year'] : null;
    $no_of_members = (int)($_POST['no_of_members'] ?? 1);
    if ($band_name === '') $errors[] = 'Band name is required.';
    if (!$errors) {
      $stmt = $pdo->prepare('INSERT INTO bands (band_name, genre, formation_year, no_of_members, manager_user_id) VALUES (?,?,?,?,?)');
      $manager_id = current_user_id();
      $stmt->execute([$band_name,$genre,$formation_year,$no_of_members,$manager_id]);
      $msg = 'Band created.';
    }
  }
  if ($action === 'update_availability') {
    $b_id = (int)($_POST['b_id'] ?? 0);
    $status = $_POST['availability_status'] ?? 'available';
    if ($b_id && in_array($status, ['available','booked','inactive'], true)) {
      // Only allow updating bands owned by this manager
      $stmt = $pdo->prepare('UPDATE bands SET availability_status = ? WHERE b_id = ? AND manager_user_id = ?');
      $stmt->execute([$status,$b_id,current_user_id()]);
      $msg = 'Availability updated.';
    }
  }
  if ($action === 'add_member') {
    $b_id = (int)($_POST['b_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    if ($b_id && $name !== '') {
      // Only allow adding member to manager's own band
      $stmt = $pdo->prepare('INSERT INTO members (b_id,name,position) SELECT ?, ?, ? FROM bands WHERE b_id = ? AND manager_user_id = ?');
      $stmt->execute([$b_id,$name,$position,$b_id,current_user_id()]);
      $msg = 'Member added.';
    }
  }
}

include __DIR__ . '/header.php';
?>

<style>
.band-page-wrapper {
  background: url('https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?q=80&w=2070') center/cover fixed;
  min-height: 100vh;
  padding: 40px 0;
  position: relative;
}

.band-page-wrapper::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(25, 0, 25, 0.85), rgba(43, 18, 76, 0.75), rgba(133, 79, 108, 0.65));
  z-index: -1;
}

.page-header {
  text-align: center;
  margin-bottom: 40px;
  padding: 30px;
  background: rgba(25, 0, 25, 0.6);
  border-radius: 20px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  backdrop-filter: blur(15px);
}

.page-header h2 {
  font-size: 42px;
  margin: 0 0 10px 0;
  color: #F8E4D8;
  text-shadow: 0 4px 20px rgba(223, 182, 178, 0.5);
}

.page-header p {
  color: #DFB6B2;
  font-size: 18px;
  margin: 0;
}

.form-card {
  background: rgba(25, 0, 25, 0.7);
  padding: 40px;
  border-radius: 20px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  backdrop-filter: blur(15px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  margin-bottom: 30px;
}

.form-card h3 {
  text-align: center;
  font-size: 28px;
  margin-bottom: 30px;
  color: #F8E4D8;
  text-shadow: 0 2px 10px rgba(223, 182, 178, 0.3);
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 25px;
}

.form-grid.full {
  grid-template-columns: 1fr;
}

.form-group {
  margin-bottom: 0;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #DFB6B2;
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 14px 18px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  border-radius: 12px;
  background: rgba(248, 228, 216, 0.95);
  color: #2B124C;
  font-size: 15px;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #DFB6B2;
  background: rgba(248, 228, 216, 1);
  box-shadow: 0 0 0 4px rgba(223, 182, 178, 0.2);
  transform: translateY(-2px);
}

.btn-submit {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #522B5B, #854F6C);
  color: #F8E4D8;
  border: none;
  border-radius: 12px;
  font-size: 18px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0 6px 25px rgba(43, 18, 76, 0.4);
}

.btn-submit:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 35px rgba(43, 18, 76, 0.6);
}

.bands-list {
  background: rgba(25, 0, 25, 0.7);
  padding: 40px;
  border-radius: 20px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  backdrop-filter: blur(15px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.bands-list h3 {
  text-align: center;
  font-size: 28px;
  margin-bottom: 30px;
  color: #F8E4D8;
}

.band-item {
  background: rgba(248, 228, 216, 0.1);
  padding: 25px;
  margin-bottom: 20px;
  border-radius: 15px;
  border: 2px solid rgba(223, 182, 178, 0.2);
  transition: all 0.3s ease;
}

.band-item:hover {
  border-color: rgba(223, 182, 178, 0.5);
  transform: translateX(5px);
  box-shadow: 0 5px 20px rgba(223, 182, 178, 0.2);
}

.band-item strong {
  color: #F8E4D8;
  font-size: 20px;
  display: block;
  margin-bottom: 8px;
}

.band-item em {
  color: #DFB6B2;
  font-style: normal;
  padding: 4px 12px;
  background: rgba(223, 182, 178, 0.2);
  border-radius: 20px;
  font-size: 13px;
  text-transform: uppercase;
  font-weight: 600;
}

.status-form {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  margin-top: 15px;
  background: rgba(25, 0, 25, 0.4);
  padding: 12px 16px;
  border-radius: 10px;
}

.status-form select {
  padding: 8px 12px;
  border-radius: 8px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  background: rgba(248, 228, 216, 0.95);
  color: #2B124C;
}

.status-form button {
  padding: 8px 20px;
  border-radius: 8px;
}

details {
  margin-top: 20px;
  background: rgba(25, 0, 25, 0.3);
  padding: 15px;
  border-radius: 12px;
  border: 1px solid rgba(223, 182, 178, 0.2);
}

details summary {
  cursor: pointer;
  color: #DFB6B2;
  font-weight: 700;
  padding: 10px;
  font-size: 16px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

details[open] summary {
  color: #F8E4D8;
  border-bottom: 2px solid rgba(223, 182, 178, 0.3);
  margin-bottom: 15px;
}

.member-item {
  background: rgba(248, 228, 216, 0.1);
  padding: 12px 16px;
  margin: 8px 0;
  border-radius: 8px;
  color: #DFB6B2;
  border-left: 3px solid #854F6C;
}

.add-member-form {
  margin-top: 20px;
  padding: 20px;
  background: rgba(25, 0, 25, 0.4);
  border-radius: 12px;
  border: 2px dashed rgba(223, 182, 178, 0.3);
}

.add-member-form .form-grid {
  margin-bottom: 15px;
}

.notification {
  padding: 16px 20px;
  border-radius: 12px;
  margin-bottom: 25px;
  font-weight: 600;
  text-align: center;
  animation: slideDown 0.5s ease;
}

.notification.success {
  background: rgba(100, 255, 150, 0.15);
  border: 2px solid rgba(100, 255, 150, 0.3);
  color: #ccffdd;
}

.notification.error {
  background: rgba(255, 100, 100, 0.15);
  border: 2px solid rgba(255, 100, 100, 0.3);
  color: #ffcccc;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .page-header h2 {
    font-size: 32px;
  }
}
</style>

<div class="band-page-wrapper">
  <div class="container">
    <div class="page-header">
      <h2>🎸 Band Management</h2>
      <p>Create and manage your bands, members, and availability</p>
    </div>

    <?php if ($msg): ?>
      <div class="notification success"><?=htmlspecialchars($msg)?></div>
    <?php endif; ?>
    
    <?php if ($errors): ?>
      <div class="notification error"><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div>
    <?php endif; ?>

    <div class="form-card">
      <h3>✨ Create a New Band</h3>
      <form method="post" action="">
        <input type="hidden" name="action" value="create_band">
        
        <div class="form-grid">
          <div class="form-group">
            <label>Band Name *</label>
            <input name="band_name" required placeholder="Enter your band name">
          </div>
          
          <div class="form-group">
            <label>Genre</label>
            <input name="genre" placeholder="Rock, Jazz, Pop, etc.">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Formation Year</label>
            <input name="formation_year" type="number" min="1900" max="2100" placeholder="Year formed">
          </div>
          
          <div class="form-group">
            <label>Number of Members</label>
            <input name="no_of_members" type="number" min="1" value="1" placeholder="Total members">
          </div>
        </div>

        <button type="submit" class="btn-submit">Create Band</button>
      </form>
    </div>

    <div class="bands-list">
      <h3>🎵 Your Bands</h3>
      <?php
        // Managers see only their bands
        $stmt = $pdo->prepare('SELECT * FROM bands WHERE manager_user_id = ? ORDER BY created_at DESC');
        $stmt->execute([current_user_id()]);
        $bands = $stmt->fetchAll();
      ?>
      <?php if ($bands): ?>
        <div>
          <?php foreach($bands as $b): ?>
            <div class="band-item">
              <strong><?=htmlspecialchars($b['band_name'])?></strong>
              <span style="color: #A084CA;"><?=htmlspecialchars($b['genre'] ?: 'Genre not specified')?></span>
              <span style="margin-left: 15px;">Status: <em><?=htmlspecialchars($b['availability_status'])?></em></span>
              
              <form method="post" action="" class="status-form">
                <input type="hidden" name="action" value="update_availability">
                <input type="hidden" name="b_id" value="<?=$b['b_id']?>">
                <label style="margin: 0; color: #DFB6B2; font-size: 14px;">Change Status:</label>
                <select name="availability_status">
                  <option value="available" <?=($b['availability_status']==='available'?'selected':'')?>>Available</option>
                  <option value="booked" <?=($b['availability_status']==='booked'?'selected':'')?>>Booked</option>
                  <option value="inactive" <?=($b['availability_status']==='inactive'?'selected':'')?>>Inactive</option>
                </select>
                <button type="submit" class="secondary">Update</button>
              </form>
              
              <details>
                <summary>👥 Band Members</summary>
                <?php
                  $stmt = $pdo->prepare('SELECT * FROM members WHERE b_id = ? ORDER BY member_id');
                  $stmt->execute([$b['b_id']]);
                  $members = $stmt->fetchAll();
                ?>
                <?php if ($members): ?>
                  <div style="margin: 15px 0;">
                    <?php foreach($members as $m): ?>
                      <div class="member-item">
                        <strong><?=htmlspecialchars($m['name'])?></strong> 
                        <span style="color: #A084CA;">— <?=htmlspecialchars($m['position'] ?: 'Band Member')?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <p style="color: #DFB6B2; margin: 15px 0;">No members added yet.</p>
                <?php endif; ?>
                
                <form method="post" action="" class="add-member-form">
                  <input type="hidden" name="action" value="add_member">
                  <input type="hidden" name="b_id" value="<?=$b['b_id']?>">
                  <div class="form-grid">
                    <div class="form-group">
                      <label>Member Name *</label>
                      <input name="name" required placeholder="Full name">
                    </div>
                    <div class="form-group">
                      <label>Position / Role</label>
                      <input name="position" placeholder="Vocalist, Guitarist, etc.">
                    </div>
                  </div>
                  <button type="submit" style="width: 100%;">Add Member</button>
                </form>
              </details>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="text-align: center; color: #DFB6B2; font-size: 18px; padding: 40px;">
          No bands created yet. Create your first band above! 🎤
        </p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>