<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('general');
require_basic_profile();
// Ensure general ID exists for review attribution
$__general_id = get_or_create_general_id(current_user_id());

$errors = [];
$msg = '';
$user_id = current_user_id();
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_review') {
  $target_type = $_POST['target_type'] === 'venue' ? 'venue' : 'band';
  $target_id = (int)($_POST['target_id'] ?? 0);
  $rating = (int)($_POST['rating'] ?? 0);
  $comment = trim($_POST['comment'] ?? '');

  if (!$target_id) $errors[] = 'Please select a target.';
  if ($rating < 1 || $rating > 5) $errors[] = 'Rating must be between 1 and 5.';

  if (!$errors) {
    $stmt = $pdo->prepare('INSERT INTO reviews (`author_user_id`, `target_type`, `target_id`, `rating`, `comment`) VALUES (?,?,?,?,?)');
    $stmt->execute([$user_id, $target_type, $target_id, $rating, $comment]);
    $msg = 'Review submitted.';
  }
}

// Handle review update (author can edit their own review)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_review') {
  $review_id = (int)($_POST['review_id'] ?? 0);
  $rating = (int)($_POST['rating'] ?? 0);
  $comment = trim($_POST['comment'] ?? '');
  if ($rating < 1 || $rating > 5) $errors[] = 'Rating must be between 1 and 5.';
  if (!$review_id) $errors[] = 'Invalid review.';
  if (!$errors) {
    $stmt = $pdo->prepare('UPDATE reviews SET `rating` = ?, `comment` = ? WHERE `review_id` = ? AND `author_user_id` = ?');
    $stmt->execute([$rating, $comment, $review_id, $user_id]);
    if ($stmt->rowCount() > 0) {
      $msg = 'Review updated.';
    } else {
      $errors[] = 'Unable to update review.';
    }
  }
}

// Load choices
$bands = $pdo->query('SELECT b_id, band_name FROM bands ORDER BY band_name')->fetchAll();
$venues = $pdo->query('SELECT venue_id, venue_name FROM venues ORDER BY venue_name')->fetchAll();

// Load current user's reviews with target and recipient (manager for bands, owner for venues)
$reviews_sql = "
  SELECT r.review_id, r.target_type, r.target_id, r.rating, r.comment, r.review_date,
         CASE WHEN r.target_type='band' THEN b.band_name ELSE v.venue_name END AS target_name,
         CASE WHEN r.target_type='band' THEN um.user_id ELSE uo.user_id END AS recipient_user_id,
         CASE WHEN r.target_type='band' THEN COALESCE(um.name, um.username) ELSE COALESCE(uo.name, uo.username) END AS recipient_name,
         CASE WHEN r.target_type='band' THEN 'manager' ELSE 'venue_owner' END AS recipient_role
  FROM reviews r
  LEFT JOIN bands b  ON r.target_type='band'  AND r.target_id=b.b_id
  LEFT JOIN users um ON b.manager_user_id = um.user_id
  LEFT JOIN venues v ON r.target_type='venue' AND r.target_id=v.venue_id
  LEFT JOIN users uo ON v.owner_user_id = uo.user_id
  WHERE r.author_user_id = ?
  ORDER BY r.review_date DESC, r.review_id DESC
";
$myReviews = [];
$stmt = $pdo->prepare($reviews_sql);
$stmt->execute([$user_id]);
$myReviews = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>

<style>
.reviews-wrapper { min-height: 100vh; padding: 40px 0; }
.form-card { background: rgba(25,0,25,0.7); padding: 32px; border-radius: 16px; border:2px solid rgba(223,182,178,0.3); backdrop-filter: blur(12px); box-shadow: 0 10px 30px rgba(0,0,0,.4); }
.form-grid { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
.form-grid.full { grid-template-columns: 1fr; }
label { display:block; color:#DFB6B2; font-weight:600; font-size:14px; margin-bottom:6px; }
select, textarea, input[type=number] { width:100%; padding:12px 14px; border-radius:10px; border:2px solid rgba(223,182,178,0.3); background: rgba(248,228,216,.95); color:#2B124C; }
textarea{ min-height:110px; resize:vertical; }
.button { padding:14px 18px; border:none; border-radius:10px; background:linear-gradient(135deg,#522B5B,#854F6C); color:#F8E4D8; font-weight:700; width:100%; margin-top:10px; }
.note{ padding:12px 14px; border-radius:10px; margin-bottom:12px; }
.note.success{ background: rgba(76,175,80,.15); border:1px solid rgba(76,175,80,.3); color:#ccffdd; }
.note.error{ background: rgba(244,67,54,.15); border:1px solid rgba(244,67,54,.3); color:#ffcccc; }
@media(max-width:768px){ .form-grid{ grid-template-columns:1fr; } }
</style>

<div class="reviews-wrapper">
  <div class="container">
    <div class="form-card">
      <h2 style="color:#F8E4D8; margin-top:0">Write a Review</h2>
      <p style="color:#DFB6B2; margin-top:4px">Share your experience about a band or a venue.</p>

      <?php if ($msg): ?><div class="note success"><?=htmlspecialchars($msg)?></div><?php endif; ?>
      <?php if ($errors): ?><div class="note error"><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div><?php endif; ?>

      <form method="post" action="">
        <input type="hidden" name="action" value="add_review">
        <div class="form-grid">
          <div>
            <label>Target Type</label>
            <select name="target_type" id="target-type" required>
              <option value="band">Band</option>
              <option value="venue">Venue</option>
            </select>
          </div>
          <div id="band-select-wrap">
            <label>Band</label>
            <select name="target_id" id="band-select">
              <option value="">-- Select Band --</option>
              <?php foreach($bands as $b): ?>
                <option value="<?=$b['b_id']?>"><?=htmlspecialchars($b['band_name'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div id="venue-select-wrap" style="display:none">
            <label>Venue</label>
            <select id="venue-select">
              <option value="">-- Select Venue --</option>
              <?php foreach($venues as $v): ?>
                <option value="<?=$v['venue_id']?>"><?=htmlspecialchars($v['venue_name'])?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-grid">
          <div>
            <label>Rating (1-5)</label>
            <input type="number" name="rating" min="1" max="5" value="5" required>
          </div>
          <div>
            <label>&nbsp;</label>
            <div style="color:#DFB6B2">Use 5 for excellent, 1 for poor.</div>
          </div>
        </div>

        <div class="form-grid full">
          <div>
            <label>Comment (optional)</label>
            <textarea name="comment" placeholder="Write your feedback..."></textarea>
          </div>
        </div>

        <button class="button" type="submit">Submit Review</button>
      </form>
    </div>

    <div class="form-card" style="margin-top:24px;">
      <h3 style="color:#F8E4D8; margin:0 0 10px 0;">My Reviews</h3>
      <?php if (!$myReviews): ?>
        <div class="note" style="background:rgba(255,255,255,.05); color:#DFB6B2;">You haven't written any reviews yet.</div>
      <?php else: ?>
        <?php foreach ($myReviews as $rv): ?>
          <div style="border:1px solid rgba(223,182,178,0.25); border-radius:12px; padding:16px; margin-bottom:14px; background: rgba(25,0,25,0.5);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
              <div style="color:#F8E4D8; font-weight:700;">
                <?=htmlspecialchars(ucfirst($rv['target_type']))?>:
                <span style="color:#DFB6B2; font-weight:600;"><?=htmlspecialchars($rv['target_name'] ?? 'Unknown')?></span>
              </div>
              <div style="color:#DFB6B2;">
                Recipient (<?=htmlspecialchars($rv['recipient_role'])?>):
                <span style="color:#F8E4D8; font-weight:600;"><?=htmlspecialchars($rv['recipient_name'] ?? 'Unassigned')?></span>
              </div>
            </div>
            <div style="color:#DFB6B2; font-size:12px; margin-top:4px;">On <?=htmlspecialchars($rv['review_date'])?></div>

            <form method="post" action="" style="margin-top:10px;">
              <input type="hidden" name="action" value="update_review">
              <input type="hidden" name="review_id" value="<?= (int)$rv['review_id'] ?>">
              <div class="form-grid">
                <div>
                  <label>Rating (1-5)</label>
                  <input type="number" name="rating" min="1" max="5" value="<?= (int)$rv['rating'] ?>" required>
                </div>
                <div>
                  <label>&nbsp;</label>
                  <div style="color:#DFB6B2">Update your score and comment.</div>
                </div>
              </div>
              <div class="form-grid full">
                <div>
                  <label>Comment</label>
                  <textarea name="comment" placeholder="Write your feedback..."><?=htmlspecialchars($rv['comment'] ?? '')?></textarea>
                </div>
              </div>
              <button class="button" type="submit">Save Changes</button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
(function(){
  const type = document.getElementById('target-type');
  const bandWrap = document.getElementById('band-select-wrap');
  const venueWrap = document.getElementById('venue-select-wrap');
  const bandSel = document.getElementById('band-select');
  const venueSel = document.getElementById('venue-select');
  const ensureName = 'target_id';

  function syncTargetName(){
    if(type.value === 'venue'){
      bandSel.removeAttribute('name');
      venueSel.setAttribute('name', ensureName);
    } else {
      venueSel.removeAttribute('name');
      bandSel.setAttribute('name', ensureName);
    }
  }

  function toggle(){
    if(type.value === 'venue'){
      bandWrap.style.display='none';
      venueWrap.style.display='block';
    } else {
      bandWrap.style.display='block';
      venueWrap.style.display='none';
    }
    syncTargetName();
  }

  type.addEventListener('change', toggle);
  toggle();
})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
