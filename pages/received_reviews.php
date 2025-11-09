<?php
require_once __DIR__ . '/../config.php';
require_login();
$role = current_user_role();
if (!in_array($role, ['manager','venue_owner'])) {
  header('Location: /band/pages/dashboard.php');
  exit;
}

$user_id = current_user_id();

if ($role === 'manager') {
  $sql = "
    SELECT r.review_id, r.target_type, r.target_id, r.rating, r.comment, r.review_date,
           u.name AS author_name, COALESCE(u.name, u.username) AS author_display,
           b.band_name AS target_name
    FROM reviews r
    JOIN bands b ON r.target_type='band' AND r.target_id=b.b_id
    JOIN users u ON u.user_id = r.author_user_id
    WHERE b.manager_user_id = ?
    ORDER BY r.review_date DESC, r.review_id DESC
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$user_id]);
  $reviews = $stmt->fetchAll();
} else { // venue_owner
  $sql = "
    SELECT r.review_id, r.target_type, r.target_id, r.rating, r.comment, r.review_date,
           u.name AS author_name, COALESCE(u.name, u.username) AS author_display,
           v.venue_name AS target_name
    FROM reviews r
    JOIN venues v ON r.target_type='venue' AND r.target_id=v.venue_id
    JOIN users u ON u.user_id = r.author_user_id
    WHERE v.owner_user_id = ?
    ORDER BY r.review_date DESC, r.review_id DESC
  ";
  $stmt = $pdo->prepare($sql);
  $stmt->execute([$user_id]);
  $reviews = $stmt->fetchAll();
}

include __DIR__ . '/header.php';
?>

<style>
.reviews-wrap { min-height: 70vh; padding: 24px 0; }
.card { background: rgba(25,0,25,0.7); padding: 20px; border-radius: 12px; border:2px solid rgba(223,182,178,0.3); backdrop-filter: blur(10px); box-shadow: 0 10px 24px rgba(0,0,0,.35); }
.item { border:1px solid rgba(223,182,178,0.25); border-radius:10px; padding:14px; margin:10px 0; background: rgba(25,0,25,0.5); }
.badge { display:inline-block; padding:4px 8px; border-radius:999px; background:rgba(133,79,108,.25); color:#F8E4D8; font-size:12px; border:1px solid rgba(133,79,108,.5); }
.heading { color:#F8E4D8; font-weight:700; margin:0 0 6px 0; }
.meta { color:#DFB6B2; font-size:12px; }
.rating { color:#F8E4D8; font-weight:700; }
.comment { color:#DFB6B2; white-space:pre-wrap; margin-top:6px; }
</style>

<div class="reviews-wrap">
  <div class="container">
    <div class="card">
      <h2 class="heading" style="font-size:20px;">Received Reviews (<?= htmlspecialchars($role) ?>)</h2>
      <?php if (!$reviews): ?>
        <div class="item" style="color:#DFB6B2;">No reviews yet.</div>
      <?php else: ?>
        <?php foreach ($reviews as $rv): ?>
          <div class="item">
            <div style="display:flex; justify-content:space-between; gap:8px; flex-wrap:wrap;">
              <div class="heading">
                <?= $role === 'manager' ? 'Band' : 'Venue' ?>:
                <span style="color:#DFB6B2; font-weight:600;"><?= htmlspecialchars($rv['target_name'] ?? 'Unknown') ?></span>
              </div>
              <div class="badge">Rating: <span class="rating"><?= (int)$rv['rating'] ?>/5</span></div>
            </div>
            <div class="meta">By <?= htmlspecialchars($rv['author_display'] ?? 'User') ?> on <?= htmlspecialchars($rv['review_date']) ?></div>
            <?php if (!empty($rv['comment'])): ?>
              <div class="comment"><?= htmlspecialchars($rv['comment']) ?></div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
