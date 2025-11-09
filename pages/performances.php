<?php
require_once __DIR__ . '/../config.php';
require_login();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$msg = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (current_user_role() !== 'manager') {
    $errors[] = 'Not authorized.';
  } else {
    if ($action === 'add') {
      $b_id = (int)($_POST['b_id'] ?? 0);
      $venue_id = (int)($_POST['venue_id'] ?? 0);
      $date = $_POST['date'] ?? '';
      $start_time = $_POST['start_time'] ?? null;
      $end_time = $_POST['end_time'] ?? null;
      $ptype = trim($_POST['performance_type'] ?? '');
      if (!$b_id || !$venue_id || !$date) { $errors[] = 'Band, Venue and Date are required.'; }
      if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO performances (b_id, venue_id, date, start_time, end_time, performance_type) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$b_id,$venue_id,$date,$start_time,$end_time,$ptype]);
        $msg = 'Performance added.';
      }
    }
    if ($action === 'delete') {
      $id = (int)($_POST['performance_id'] ?? 0);
      if ($id) {
        $stmt = $pdo->prepare('DELETE FROM performances WHERE performance_id = ?');
        $stmt->execute([$id]);
        $msg = 'Performance deleted.';
      }
    }
    if ($action === 'update') {
      $id = (int)($_POST['performance_id'] ?? 0);
      $status = $_POST['status'] ?? '';
      if ($id && in_array($status, ['scheduled','completed','cancelled'], true)) {
        $stmt = $pdo->prepare('UPDATE performances SET status = ? WHERE performance_id = ?');
        $stmt->execute([$status,$id]);
        $msg = 'Performance updated.';
      }
    }
  }
}

include __DIR__ . '/header.php';
?>

<style>
.performance-page-wrapper {
  background: url('https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?q=80&w=2070') center/cover fixed;
  min-height: 100vh;
  padding: 40px 0;
  position: relative;
}

.performance-page-wrapper::before {
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

.form-grid.three-col {
  grid-template-columns: 1fr 1fr 1fr;
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
.form-group select {
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

.performances-list {
  background: rgba(25, 0, 25, 0.7);
  padding: 40px;
  border-radius: 20px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  backdrop-filter: blur(15px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.performances-list h3 {
  text-align: center;
  font-size: 28px;
  margin-bottom: 30px;
  color: #F8E4D8;
}

.performance-item {
  background: rgba(248, 228, 216, 0.1);
  padding: 25px;
  margin-bottom: 20px;
  border-radius: 15px;
  border: 2px solid rgba(223, 182, 178, 0.2);
  transition: all 0.3s ease;
}

.performance-item:hover {
  border-color: rgba(223, 182, 178, 0.5);
  transform: translateX(5px);
  box-shadow: 0 5px 20px rgba(223, 182, 178, 0.2);
}

.performance-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
  flex-wrap: wrap;
  gap: 15px;
}

.performance-info strong {
  color: #F8E4D8;
  font-size: 20px;
  display: block;
  margin-bottom: 8px;
}

.performance-info .venue-name {
  color: #A084CA;
  font-size: 16px;
  margin-bottom: 5px;
}

.performance-info .performance-date {
  color: #DFB6B2;
  font-size: 14px;
  margin-bottom: 3px;
}

.performance-info .performance-time {
  color: #DFB6B2;
  font-size: 14px;
}

.performance-type {
  display: inline-block;
  padding: 6px 14px;
  background: rgba(160, 132, 202, 0.2);
  color: #A084CA;
  border-radius: 15px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-top: 8px;
}

.status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  text-transform: uppercase;
  font-weight: 600;
  display: inline-block;
}

.status-badge.scheduled {
  background: rgba(33, 150, 243, 0.2);
  color: #64B5F6;
  border: 1px solid rgba(33, 150, 243, 0.4);
}

.status-badge.completed {
  background: rgba(76, 175, 80, 0.2);
  color: #81C784;
  border: 1px solid rgba(76, 175, 80, 0.4);
}

.status-badge.cancelled {
  background: rgba(244, 67, 54, 0.2);
  color: #E57373;
  border: 1px solid rgba(244, 67, 54, 0.4);
}

.performance-actions {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
  margin-top: 15px;
  padding-top: 15px;
  border-top: 1px solid rgba(223, 182, 178, 0.2);
}

.status-update-form {
  display: flex;
  gap: 10px;
  align-items: center;
  background: rgba(25, 0, 25, 0.4);
  padding: 10px 15px;
  border-radius: 10px;
}

.status-update-form select {
  padding: 8px 12px;
  border-radius: 8px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  background: rgba(248, 228, 216, 0.95);
  color: #2B124C;
  font-size: 14px;
}

.btn-action {
  padding: 8px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  font-size: 13px;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border: 2px solid;
}

.btn-save {
  background: rgba(76, 175, 80, 0.2);
  color: #81C784;
  border-color: rgba(76, 175, 80, 0.4);
}

.btn-save:hover {
  background: rgba(76, 175, 80, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
}

.btn-delete {
  background: rgba(244, 67, 54, 0.2);
  color: #E57373;
  border-color: rgba(244, 67, 54, 0.4);
}

.btn-delete:hover {
  background: rgba(244, 67, 54, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(244, 67, 54, 0.3);
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

@media (max-width: 968px) {
  .form-grid.three-col {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .page-header h2 {
    font-size: 32px;
  }
  
  .performance-header {
    flex-direction: column;
  }
  
  .performance-actions {
    flex-direction: column;
    align-items: stretch;
  }
  
  .status-update-form {
    flex-direction: column;
  }
}
</style>

<div class="performance-page-wrapper">
  <div class="container">
    <div class="page-header">
      <h2>🎤 Performance Management</h2>
      <p>Schedule and track your band performances</p>
    </div>

    <?php if ($msg): ?>
      <div class="notification success"><?=htmlspecialchars($msg)?></div>
    <?php endif; ?>
    
    <?php if ($errors): ?>
      <div class="notification error"><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div>
    <?php endif; ?>

    <?php if (current_user_role()==='manager'): ?>
    <div class="form-card">
      <h3>✨ Add New Performance</h3>
      <?php
        $bands = $pdo->query('SELECT b_id, band_name FROM bands ORDER BY band_name')->fetchAll();
        $venues = $pdo->query('SELECT venue_id, venue_name FROM venues ORDER BY venue_name')->fetchAll();
      ?>
      <form method="post" action="">
        <input type="hidden" name="action" value="add">
        
        <div class="form-grid">
          <div class="form-group">
            <label>Band *</label>
            <select name="b_id" required>
              <option value="">-- Select Band --</option>
              <?php foreach($bands as $b): ?>
                <option value="<?=$b['b_id']?>"><?=htmlspecialchars($b['band_name'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="form-group">
            <label>Venue *</label>
            <select name="venue_id" required>
              <option value="">-- Select Venue --</option>
              <?php foreach($venues as $v): ?>
                <option value="<?=$v['venue_id']?>"><?=htmlspecialchars($v['venue_name'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-grid three-col">
          <div class="form-group">
            <label>Date *</label>
            <input type="date" name="date" required>
          </div>
          
          <div class="form-group">
            <label>Start Time</label>
            <input type="time" name="start_time">
          </div>
          
          <div class="form-group">
            <label>End Time</label>
            <input type="time" name="end_time">
          </div>
        </div>

        <div class="form-group">
          <label>Performance Type</label>
          <input name="performance_type" placeholder="Concert / Rehearsal / Festival / etc.">
        </div>

        <button type="submit" class="btn-submit">Add Performance</button>
      </form>
    </div>
    <?php endif; ?>

    <div class="performances-list">
      <h3>🎵 All Performances</h3>
      <?php
        $rows = $pdo->query('SELECT p.*, b.band_name, v.venue_name FROM performances p JOIN bands b ON p.b_id=b.b_id JOIN venues v ON p.venue_id=v.venue_id ORDER BY p.date DESC, p.performance_id DESC')->fetchAll();
      ?>
      
      <?php if ($rows): ?>
        <div>
          <?php foreach($rows as $r): ?>
            <div class="performance-item">
              <div class="performance-header">
                <div class="performance-info">
                  <strong><?=htmlspecialchars($r['band_name'])?></strong>
                  <div class="venue-name">📍 <?=htmlspecialchars($r['venue_name'])?></div>
                  <div class="performance-date">
                    📅 <?=htmlspecialchars(date('F j, Y', strtotime($r['date'])))?>
                  </div>
                  <?php if ($r['start_time'] || $r['end_time']): ?>
                    <div class="performance-time">
                      ⏰ 
                      <?php if ($r['start_time']): ?>
                        <?=htmlspecialchars(date('g:i A', strtotime($r['start_time'])))?>
                      <?php endif; ?>
                      <?php if ($r['start_time'] && $r['end_time']): ?>
                        - 
                      <?php endif; ?>
                      <?php if ($r['end_time']): ?>
                        <?=htmlspecialchars(date('g:i A', strtotime($r['end_time'])))?>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                  <?php if ($r['performance_type']): ?>
                    <span class="performance-type"><?=htmlspecialchars($r['performance_type'])?></span>
                  <?php endif; ?>
                </div>
                
                <div>
                  <span class="status-badge <?=htmlspecialchars($r['status'])?>">
                    <?=htmlspecialchars($r['status'])?>
                  </span>
                </div>
              </div>
              
              <div class="performance-actions">
                <form method="post" action="" class="status-update-form">
                  <input type="hidden" name="action" value="update">
                  <input type="hidden" name="performance_id" value="<?=$r['performance_id']?>">
                  <label style="margin: 0; color: #DFB6B2; font-size: 13px; font-weight: 600;">Update Status:</label>
                  <select name="status">
                    <option <?=($r['status']==='scheduled'?'selected':'')?> value="scheduled">Scheduled</option>
                    <option <?=($r['status']==='completed'?'selected':'')?> value="completed">Completed</option>
                    <option <?=($r['status']==='cancelled'?'selected':'')?> value="cancelled">Cancelled</option>
                  </select>
                  <button type="submit" class="btn-action btn-save">Save</button>
                </form>
                
                <form method="post" action="" style="display:inline" onsubmit="return confirm('Delete this performance? This action cannot be undone.')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="performance_id" value="<?=$r['performance_id']?>">
                  <button type="submit" class="btn-action btn-delete">Delete</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="text-align: center; color: #DFB6B2; font-size: 18px; padding: 40px;">
          No performances scheduled yet. Add your first performance above! 🎸
        </p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>