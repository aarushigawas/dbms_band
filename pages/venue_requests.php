<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('venue_owner');

$errors = [];
$msg = '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$owner_id = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (in_array($action, ['confirm','reject','cancel'], true)) {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $new = $action === 'confirm' ? 'confirmed' : ($action === 'reject' ? 'rejected' : 'cancelled');
    if ($booking_id) {
      try {
        $pdo->beginTransaction();
        // Update booking status, enforcing venue ownership
        $stmt = $pdo->prepare("
          UPDATE bookings bk
          JOIN venues v ON bk.venue_id = v.venue_id
          SET bk.status = ?
          WHERE bk.booking_id = ? AND v.owner_user_id = ?
        ");
        $stmt->execute([$new,$booking_id,$owner_id]);

        // If confirmed, create a scheduled performance (avoid duplicates)
        if ($action === 'confirm') {
          // Insert only if a matching performance does not already exist
          $ins = $pdo->prepare("
            INSERT INTO performances (b_id, venue_id, date, start_time, end_time, performance_type, status)
            SELECT bk.b_id, bk.venue_id, bk.booking_date, NULL, NULL, 'concert', 'scheduled'
            FROM bookings bk
            JOIN venues v ON bk.venue_id = v.venue_id
            WHERE bk.booking_id = ? AND v.owner_user_id = ?
              AND NOT EXISTS (
                SELECT 1 FROM performances p
                WHERE p.b_id = bk.b_id
                  AND p.venue_id = bk.venue_id
                  AND p.date = bk.booking_date
                  AND p.status = 'scheduled'
              )
          ");
          $ins->execute([$booking_id,$owner_id]);
        }

        $pdo->commit();
        $msg = 'Booking status updated.' . ($action === 'confirm' ? ' Performance scheduled.' : '');
      } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errors[] = 'Failed to update booking: ' . $e->getMessage();
      }
    }
  }
}

include __DIR__ . '/header.php';
?>

<style>
.booking-requests-wrapper {
  background: url('https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?q=80&w=2070') center/cover fixed;
  min-height: 100vh;
  padding: 60px 0;
  position: relative;
}

.booking-requests-wrapper::before {
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
  margin-bottom: 50px;
  animation: fadeIn 0.6s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.page-header h2 {
  font-size: 48px;
  margin: 0 0 10px 0;
  color: #F8E4D8;
  text-shadow: 0 4px 20px rgba(223, 182, 178, 0.5);
  font-weight: 800;
}

.page-header p {
  color: #DFB6B2;
  font-size: 18px;
  margin: 0;
}

.notification {
  padding: 18px 24px;
  border-radius: 15px;
  margin-bottom: 30px;
  font-weight: 600;
  text-align: center;
  animation: slideDown 0.5s ease;
  font-size: 15px;
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

.notification.success {
  background: rgba(100, 255, 150, 0.15);
  border: 2px solid rgba(100, 255, 150, 0.3);
  color: #ccffdd;
}

.requests-card {
  background: rgba(25, 0, 25, 0.7);
  padding: 40px;
  border-radius: 20px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  backdrop-filter: blur(15px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  animation: slideUp 0.7s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.requests-card h3 {
  text-align: center;
  font-size: 28px;
  margin-bottom: 30px;
  color: #F8E4D8;
}

.request-item {
  background: rgba(248, 228, 216, 0.1);
  padding: 25px;
  margin-bottom: 20px;
  border-radius: 15px;
  border: 2px solid rgba(223, 182, 178, 0.2);
  transition: all 0.3s ease;
}

.request-item:hover {
  border-color: rgba(223, 182, 178, 0.5);
  transform: translateX(5px);
  box-shadow: 0 5px 20px rgba(223, 182, 178, 0.2);
}

.request-info {
  margin-bottom: 15px;
}

.request-info .band-name {
  color: #F8E4D8;
  font-size: 20px;
  font-weight: 700;
  display: block;
  margin-bottom: 8px;
}

.request-info .venue-name {
  color: #BFACE2;
  font-size: 16px;
  margin-bottom: 5px;
}

.request-info .booking-date {
  color: #DFB6B2;
  font-size: 14px;
}

.request-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.btn-confirm {
  padding: 12px 24px;
  background: linear-gradient(135deg, #4CAF50, #66BB6A);
  color: white;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
}

.btn-confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
}

.btn-reject {
  padding: 12px 24px;
  background: rgba(244, 67, 54, 0.2);
  color: #E57373;
  border: 2px solid rgba(244, 67, 54, 0.4);
  border-radius: 10px;
  cursor: pointer;
  font-weight: 700;
  font-size: 14px;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.btn-reject:hover {
  background: rgba(244, 67, 54, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
}

.empty-state {
  text-align: center;
  color: #DFB6B2;
  font-size: 18px;
  padding: 60px 20px;
}

@media (max-width: 768px) {
  .page-header h2 {
    font-size: 36px;
  }

  .requests-card {
    padding: 30px 20px;
  }

  .request-actions {
    flex-direction: column;
  }

  .btn-confirm,
  .btn-reject {
    width: 100%;
  }
}
</style>

<div class="booking-requests-wrapper">
  <div class="container">
    <div class="page-header">
      <h2>Booking Requests</h2>
      <p>Manage venue booking requests from bands</p>
    </div>

    <?php if ($msg): ?>
      <div class="notification success"><?=htmlspecialchars($msg)?></div>
    <?php endif; ?>

    <div class="requests-card">
      <h3>Pending Requests</h3>
      
      <?php
      $stmt = $pdo->prepare("
        SELECT bk.*, b.band_name, v.venue_name
        FROM bookings bk
        JOIN bands b ON bk.b_id = b.b_id
        JOIN venues v ON bk.venue_id = v.venue_id
        WHERE bk.status = 'pending' AND v.owner_user_id = ?
        ORDER BY bk.booking_date DESC
      ");
      $stmt->execute([$owner_id]);
      $rows = $stmt->fetchAll();
      ?>
      
      <?php if ($rows): ?>
        <div>
          <?php foreach($rows as $r): ?>
            <div class="request-item">
              <div class="request-info">
                <span class="band-name"><?=htmlspecialchars($r['band_name'])?></span>
                <div class="venue-name">Venue: <?=htmlspecialchars($r['venue_name'])?></div>
                <div class="booking-date">Date: <?=htmlspecialchars(date('F j, Y', strtotime($r['booking_date'])))?></div>
                <?php if ($r['total_amount'] > 0): ?>
                  <div class="booking-date">Amount: $<?=number_format($r['total_amount'], 2)?></div>
                <?php endif; ?>
              </div>
              
              <div class="request-actions">
                <form method="post" action="" style="display:inline">
                  <input type="hidden" name="booking_id" value="<?=$r['booking_id']?>">
                  <button type="submit" name="action" value="confirm" class="btn-confirm">Confirm</button>
                </form>
                <form method="post" action="" style="display:inline">
                  <input type="hidden" name="booking_id" value="<?=$r['booking_id']?>">
                  <button type="submit" name="action" value="reject" class="btn-reject">Reject</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="empty-state">No pending requests at the moment.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>