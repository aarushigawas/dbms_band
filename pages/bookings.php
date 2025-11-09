<?php
require_once __DIR__ . '/../config.php';
require_login();

$errors = [];
$msg = '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (current_user_role() !== 'manager') {
    $errors[] = 'Not authorized.';
  } else {
    if ($action === 'add') {
      $b_id = (int)($_POST['b_id'] ?? 0);
      $venue_id = (int)($_POST['venue_id'] ?? 0);
      $booking_date = $_POST['booking_date'] ?? '';
      $total_amount = $_POST['total_amount'] !== '' ? (float)$_POST['total_amount'] : 0.0;
      if (!$b_id || !$venue_id || !$booking_date) $errors[] = 'Band, Venue and Booking Date are required.';
      if (!$errors) {
        $stmt = $pdo->prepare('INSERT INTO bookings (b_id, venue_id, booking_date, status, created_by, total_amount) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$b_id,$venue_id,$booking_date,'pending',$user_id,$total_amount]);
        $msg = 'Booking request created. Awaiting venue confirmation.';
      }
    }
    if ($action === 'cancel') {
      $booking_id = (int)($_POST['booking_id'] ?? 0);
      if ($booking_id) {
        $stmt = $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE booking_id = ? AND created_by = ? AND status='pending'");
        $stmt->execute([$booking_id,$user_id]);
        $msg = 'Booking cancelled (if it was pending and created by you).';
      }
    }
  }
}

include __DIR__ . '/header.php';
?>

<style>
.booking-page-wrapper {
  background: url('https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?q=80&w=2070') center/cover fixed;
  min-height: 100vh;
  padding: 40px 0;
  position: relative;
}

.booking-page-wrapper::before {
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

.bookings-list {
  background: rgba(25, 0, 25, 0.7);
  padding: 40px;
  border-radius: 20px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  backdrop-filter: blur(15px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.bookings-list h3 {
  text-align: center;
  font-size: 28px;
  margin-bottom: 30px;
  color: #F8E4D8;
}

.booking-item {
  background: rgba(248, 228, 216, 0.1);
  padding: 25px;
  margin-bottom: 20px;
  border-radius: 15px;
  border: 2px solid rgba(223, 182, 178, 0.2);
  transition: all 0.3s ease;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
}

.booking-item:hover {
  border-color: rgba(223, 182, 178, 0.5);
  transform: translateX(5px);
  box-shadow: 0 5px 20px rgba(223, 182, 178, 0.2);
}

.booking-info {
  flex: 1;
  min-width: 250px;
}

.booking-info strong {
  color: #F8E4D8;
  font-size: 20px;
  display: block;
  margin-bottom: 8px;
}

.booking-info .venue-name {
  color: #A084CA;
  font-size: 16px;
  margin-bottom: 5px;
}

.booking-info .booking-date {
  color: #DFB6B2;
  font-size: 14px;
}

.status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 13px;
  text-transform: uppercase;
  font-weight: 600;
  display: inline-block;
  margin: 10px 10px 0 0;
}

.status-badge.pending {
  background: rgba(255, 193, 7, 0.2);
  color: #FFD54F;
  border: 1px solid rgba(255, 193, 7, 0.4);
}

.status-badge.confirmed {
  background: rgba(76, 175, 80, 0.2);
  color: #81C784;
  border: 1px solid rgba(76, 175, 80, 0.4);
}

.status-badge.cancelled {
  background: rgba(244, 67, 54, 0.2);
  color: #E57373;
  border: 1px solid rgba(244, 67, 54, 0.4);
}

.booking-actions {
  margin-top: 10px;
}

.btn-cancel {
  padding: 10px 20px;
  background: rgba(244, 67, 54, 0.2);
  color: #E57373;
  border: 2px solid rgba(244, 67, 54, 0.4);
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-cancel:hover {
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

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .page-header h2 {
    font-size: 32px;
  }
  
  .booking-item {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

<div class="booking-page-wrapper">
  <div class="container">
    <div class="page-header">
      <h2>📅 Booking Management</h2>
      <p>Create and manage your venue booking requests</p>
    </div>

    <?php if ($msg): ?>
      <div class="notification success"><?=htmlspecialchars($msg)?></div>
    <?php endif; ?>
    
    <?php if ($errors): ?>
      <div class="notification error"><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div>
    <?php endif; ?>

    <?php if (current_user_role()==='manager'): ?>
    <div class="form-card">
      <h3>✨ Create Booking Request</h3>
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

        <div class="form-grid">
          <div class="form-group">
            <label>Booking Date *</label>
            <input type="date" name="booking_date" required>
          </div>
          
          <div class="form-group">
            <label>Total Amount (Optional)</label>
            <input type="number" step="0.01" name="total_amount" placeholder="0.00">
          </div>
        </div>

        <button type="submit" class="btn-submit">Request Booking</button>
      </form>
    </div>
    <?php endif; ?>

    <div class="bookings-list">
      <h3>🎫 Your Booking Requests</h3>
      <?php
        $stmt = $pdo->prepare("
          SELECT bk.*, b.band_name, v.venue_name
          FROM bookings bk
          JOIN bands b ON bk.b_id = b.b_id
          JOIN venues v ON bk.venue_id = v.venue_id
          WHERE bk.created_by = ?
          ORDER BY bk.booking_date DESC, bk.booking_id DESC
        ");
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll();
      ?>
      
      <?php if ($rows): ?>
        <div>
          <?php foreach($rows as $r): ?>
            <div class="booking-item">
              <div class="booking-info">
                <strong><?=htmlspecialchars($r['band_name'])?></strong>
                <div class="venue-name">📍 <?=htmlspecialchars($r['venue_name'])?></div>
                <div class="booking-date">📅 <?=htmlspecialchars(date('F j, Y', strtotime($r['booking_date'])))?></div>
                <?php if ($r['total_amount'] > 0): ?>
                  <div class="booking-date">💰 $<?=number_format($r['total_amount'], 2)?></div>
                <?php endif; ?>
              </div>
              
              <div>
                <span class="status-badge <?=htmlspecialchars($r['status'])?>">
                  <?=htmlspecialchars($r['status'])?>
                </span>
                
                <?php if ($r['status'] === 'pending' && current_user_role()==='manager'): ?>
                  <div class="booking-actions">
                    <form method="post" action="" style="display:inline">
                      <input type="hidden" name="action" value="cancel">
                      <input type="hidden" name="booking_id" value="<?=$r['booking_id']?>">
                      <button type="submit" class="btn-cancel" onclick="return confirm('Cancel this booking request?')">
                        Cancel Request
                      </button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p style="text-align: center; color: #DFB6B2; font-size: 18px; padding: 40px;">
          No booking requests yet. Create your first booking above! 🎫
        </p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>