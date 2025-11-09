<?php
require_once __DIR__ . '/../config.php';
require_login();

$errors = [];
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $band_name = trim($_POST['band_name'] ?? '');
  $genre = trim($_POST['genre'] ?? '');
  $formation_year = $_POST['formation_year'] !== '' ? (int)$_POST['formation_year'] : null;
  $no_of_members = (int)($_POST['no_of_members'] ?? 1);
  $manager_id = $_POST['manager_user_id'] !== '' ? (int)$_POST['manager_user_id'] : current_user_id();

  if ($band_name === '') $errors[] = 'Band name is required.';
  if (!$errors) {
    $stmt = $pdo->prepare('INSERT INTO bands (band_name, genre, formation_year, no_of_members, manager_user_id) VALUES (?,?,?,?,?)');
    $stmt->execute([$band_name,$genre,$formation_year,$no_of_members,$manager_id]);
    $msg = 'Band created successfully.';
  }
}
include __DIR__ . '/../pages/header.php';
?>
<h2>Create Band</h2>
<?php if ($msg): ?><div class="notice"><?=htmlspecialchars($msg)?></div><?php endif; ?>
<?php if ($errors): ?><div class="errors"><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div><?php endif; ?>
<form method="post" action="" needs-validation>
  <label>Band Name <input name="band_name" required></label>
  <label>Genre <input name="genre"></label>
  <label>Formation Year <input name="formation_year" type="number" min="1900" max="2100"></label>
  <label>No. of Members <input name="no_of_members" type="number" min="1" value="1"></label>
  <label>Manager User ID (optional) <input name="manager_user_id" type="number" placeholder="defaults to current user"></label>
  <button type="submit">Create</button>
</form>
<?php include __DIR__ . '/../pages/footer.php'; ?>
