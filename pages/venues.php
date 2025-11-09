<?php
require_once __DIR__ . '/../config.php';
require_login();
require_role('venue_owner');
require_basic_profile();
// Ensure venue owner ID exists
$owner_profile_id = get_or_create_owner_id(current_user_id());

$errors = [];
$msg = '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$owner_id = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if ($action === 'add') {
    $venue_name = trim($_POST['venue_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    // Enhanced location fields
    $country = trim($_POST['country'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    if ($country !== '' || $address !== '' || $city !== '' || $state !== '' || $pincode !== '') {
      $parts = [];
      if ($address !== '') $parts[] = $address;
      if ($city !== '') $parts[] = $city;
      if ($state !== '') $parts[] = $state;
      if ($pincode !== '') $parts[] = $pincode;
      if ($country !== '') $parts[] = $country;
      $location = implode(', ', array_filter($parts));
    }
    $venue_type = trim($_POST['venue_type'] ?? '');
    $capacity = $_POST['capacity'] !== '' ? (int)$_POST['capacity'] : null;
    $rent = $_POST['rent'] !== '' ? (float)$_POST['rent'] : 0.0;
    $contact_number = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($venue_name === '') $errors[] = 'Venue name is required.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
    if (!$errors) {
      $stmt = $pdo->prepare('INSERT INTO venues (venue_name, location, venue_type, capacity, rent, owner_user_id, contact_number, email) VALUES (?,?,?,?,?,?,?,?)');
      $stmt->execute([$venue_name,$location,$venue_type,$capacity,$rent,$owner_id,$contact_number,$email]);
      $msg = 'Venue added.';
    }
  }
  if ($action === 'delete') {
    $venue_id = (int)($_POST['venue_id'] ?? 0);
    if ($venue_id) {
      $stmt = $pdo->prepare('DELETE FROM venues WHERE venue_id = ? AND owner_user_id = ?');
      $stmt->execute([$venue_id,$owner_id]);
      $msg = 'Venue deleted.';
    }
  }
}

include __DIR__ . '/header.php';
?>

<style>
.venues-page-wrapper {
  background: url('https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?q=80&w=2070') center/cover fixed;
  min-height: 100vh;
  padding: 60px 0;
  position: relative;
}

.venues-page-wrapper::before {
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

.notification.error {
  background: rgba(255, 100, 100, 0.15);
  border: 2px solid rgba(255, 100, 100, 0.3);
  color: #ffcccc;
}

.form-card {
  background: rgba(25, 0, 25, 0.7);
  padding: 40px;
  border-radius: 20px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  backdrop-filter: blur(15px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  margin-bottom: 40px;
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

.form-group input {
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

.form-group input:focus {
  outline: none;
  border-color: #DFB6B2;
  background: rgba(248, 228, 216, 1);
  box-shadow: 0 0 0 4px rgba(223, 182, 178, 0.2);
  transform: translateY(-2px);
}

.btn-submit {
  width: 100%;
  padding: 16px;
  background: linear-gradient(135deg, #522B5B, #854F6C, #A084CA);
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

.venues-list {
  background: rgba(25, 0, 25, 0.7);
  padding: 40px;
  border-radius: 20px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  backdrop-filter: blur(15px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  animation: slideUp 0.8s ease-out;
}

.venues-list h3 {
  text-align: center;
  font-size: 28px;
  margin-bottom: 30px;
  color: #F8E4D8;
}

.venue-item {
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

.venue-item:hover {
  border-color: rgba(223, 182, 178, 0.5);
  transform: translateX(5px);
  box-shadow: 0 5px 20px rgba(223, 182, 178, 0.2);
}

.venue-info {
  flex: 1;
  min-width: 250px;
}

.venue-info strong {
  color: #F8E4D8;
  font-size: 20px;
  display: block;
  margin-bottom: 8px;
}

.venue-info .venue-details {
  color: #DFB6B2;
  font-size: 14px;
  margin-top: 5px;
}

.venue-actions {
  margin-top: 10px;
}

.btn-delete {
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

.btn-delete:hover {
  background: rgba(244, 67, 54, 0.3);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(244, 67, 54, 0.3);
}

.empty-state {
  text-align: center;
  color: #DFB6B2;
  font-size: 18px;
  padding: 40px;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .page-header h2 {
    font-size: 36px;
  }
  
  .venue-item {
    flex-direction: column;
    align-items: flex-start;
  }

  .form-card, .venues-list {
    padding: 30px 20px;
  }
}
</style>

<div class="venues-page-wrapper">
  <div class="container">
    <div class="page-header">
      <h2>My Venues</h2>
      <p>Manage your venue properties</p>
    </div>

    <?php if ($msg): ?>
      <div class="notification success"><?=htmlspecialchars($msg)?></div>
    <?php endif; ?>
    
    <?php if ($errors): ?>
      <div class="notification error"><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div>
    <?php endif; ?>

    <div class="form-card">
      <h3>Add New Venue</h3>
      <form method="post" action="">
        <input type="hidden" name="action" value="add">
        
        <div class="form-grid">
          <div class="form-group">
            <label>Venue Name *</label>
            <input name="venue_name" placeholder="Enter venue name" required>
          </div>
          
          <div class="form-group">
            <label>Country</label>
            <input name="country" id="country-input" placeholder="Select/Enter Country">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Type</label>
            <input name="venue_type" placeholder="Hall / Club / Stadium">
          </div>
          
          <div class="form-group">
            <label>Capacity</label>
            <input type="number" name="capacity" min="1" placeholder="e.g. 500">
          </div>
        </div>

        <!-- Advanced Location Details: shown when country is filled -->
        <div id="adv-location" class="form-grid">
          <div class="form-group">
            <label>Pincode</label>
            <input name="pincode" placeholder="Postal / ZIP Code">
          </div>
          <div class="form-group">
            <label>State</label>
            <input name="state" placeholder="State / Province">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>City</label>
            <input name="city" placeholder="City">
          </div>
          <div class="form-group">
            <label>Address</label>
            <input name="address" placeholder="Street, Area, Landmark">
          </div>
        </div>

        <!-- Fallback single-line location (optional) -->
        <div class="form-grid full">
          <div class="form-group">
            <label>Location (optional single line)</label>
            <input name="location" placeholder="If used, overrides the composed address above">
          </div>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Rent ($)</label>
            <input type="number" step="0.01" name="rent" placeholder="e.g. 1000.00">
          </div>
          
          <div class="form-group">
            <label>Contact Number</label>
            <input name="contact_number" placeholder="Phone number">
          </div>
        </div>

        <div class="form-grid full">
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="contact@venue.com">
          </div>
        </div>

        <button type="submit" class="btn-submit">Add Venue</button>
      </form>
    </div>

    <div class="venues-list">
      <h3>Your Venues</h3>
      <?php
        $stmt = $pdo->prepare('SELECT * FROM venues WHERE owner_user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$owner_id]);
        $venues = $stmt->fetchAll();
      ?>
      
      <?php if ($venues): ?>
        <div>
          <?php foreach($venues as $v): ?>
            <div class="venue-item">
              <div class="venue-info">
                <strong><?=htmlspecialchars($v['venue_name'])?></strong>
                <div class="venue-details">
                  Type: <?=htmlspecialchars($v['venue_type'] ?: 'N/A')?> | 
                  Capacity: <?=htmlspecialchars((string)($v['capacity'] ?? 'N/A'))?> | 
                  Rent: $<?=number_format($v['rent'] ?? 0, 2)?>
                </div>
                <?php if ($v['location']): ?>
                  <div class="venue-details">Location: <?=htmlspecialchars($v['location'])?></div>
                <?php endif; ?>
              </div>
              
              <div class="venue-actions">
                <form method="post" action="" style="display:inline" onsubmit="return confirm('Delete this venue?')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="venue_id" value="<?=$v['venue_id']?>">
                  <button type="submit" class="btn-delete">Delete</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="empty-state">No venues yet. Add your first venue above!</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>

<script>
(function(){
  const country = document.getElementById('country-input');
  const adv = document.getElementById('adv-location');
  function toggle() {
    if (!adv) return;
    const show = country && country.value.trim().length > 0;
    adv.style.display = show ? 'grid' : 'none';
  }
  if (country) {
    country.addEventListener('input', toggle);
    document.addEventListener('DOMContentLoaded', toggle);
    toggle();
  }
})();
</script>