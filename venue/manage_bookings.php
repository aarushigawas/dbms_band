<?php
require_once __DIR__ . '/../config.php';
require_login();

$errors = [];
$msg = '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $booking_id = (int)($_POST['booking_id'] ?? 0);
  if ($booking_id && in_array($action, ['confirm','reject','cancel'], true)) {
    // TODO: Verify the current user owns the venue for this booking
    $new = $action === 'confirm' ? 'confirmed' : ($action === 'reject' ? 'rejected' : 'cancelled');
    $stmt = $pdo->prepare('UPDATE bookings SET status = ? WHERE booking_id = ?');
    $stmt->execute([$new, $booking_id]);
    $msg = 'Booking status updated.';
  }
}

include __DIR__ . '/../pages/header.php';
?>
<h2>Manage Bookings</h2>
<?php if ($msg): ?><div class="notice"><?=htmlspecialchars($msg)?></div><?php endif; ?>
<section>
  <h3>Your Venue Bookings</h3>
  <?php
  // TODO: Filter by venues owned by current user
  $rows = $pdo->query("\n    SELECT bk.*, b.band_name, v.venue_name\n    FROM bookings bk\n    JOIN bands b ON bk.b_id = b.b_id\n    JOIN venues v ON bk.venue_id = v.venue_id\n    ORDER BY bk.booking_date DESC, bk.booking_id DESC\n  ")->fetchAll();
  ?>
  <?php if ($rows): ?>
    <ul>
      <?php foreach($rows as $r): ?>
        <li>
          <strong><?=htmlspecialchars($r['band_name'])?></strong> -> <?=htmlspecialchars($r['venue_name'])?> on <?=htmlspecialchars($r['booking_date'])?>
          <em>(<?=htmlspecialchars($r['status'])?>)</em>
          <form method="post" action="" style="display:inline">
            <input type="hidden" name="booking_id" value="<?=$r['booking_id']?>">
            <button type="submit" name="action" value="confirm">Confirm</button>
            <button type="submit" name="action" value="reject" class="secondary">Reject</button>
            <button type="submit" name="action" value="cancel" class="secondary">Cancel</button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No bookings.</p>
  <?php endif; ?>
</section>
<?php include __DIR__ . '/../pages/footer.php'; ?>
