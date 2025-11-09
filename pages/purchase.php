<?php
require_once __DIR__ . '/../config.php';
require_login();
if (current_user_role()==='general') { require_basic_profile(); }
require_role('general');
// Ensure general role ID exists
$general_id = get_or_create_general_id(current_user_id());

$errors = [];
$msg = '';
$user_id = current_user_id();
$performance_id = isset($_GET['performance_id']) ? (int)$_GET['performance_id'] : 0;

// Load performance info
$perf = null;
if ($performance_id) {
  $stmt = $pdo->prepare("
    SELECT p.performance_id, p.date, p.start_time, p.performance_type, b.band_name, v.venue_name
    FROM performances p
    JOIN bands b ON p.b_id = b.b_id
    JOIN venues v ON p.venue_id = v.venue_id
    WHERE p.performance_id = ? AND p.status = 'scheduled'
  ");
  $stmt->execute([$performance_id]);
  $perf = $stmt->fetch();
}
if (!$perf) {
  $errors[] = 'Performance not found or not available.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errors) {
  $price = $_POST['price'] !== '' ? (float)$_POST['price'] : 0.00;
  if ($price < 0) $price = 0.00;
  // Insert ticket
  $stmt = $pdo->prepare('INSERT INTO tickets (performance_id, buyer_user_id, price, status) VALUES (?,?,?,?)');
  $stmt->execute([$performance_id, $user_id, $price, 'purchased']);
  $msg = 'Ticket purchased successfully!';
}

include __DIR__ . '/header.php';
?>

<style>
.ticket-page-wrapper {
  background: url('https://images.unsplash.com/photo-1540039155733-5bb30b53aa14?q=80&w=2074') center/cover fixed;
  min-height: 100vh;
  padding: 40px 0;
  position: relative;
}

.ticket-page-wrapper::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(25, 0, 25, 0.88), rgba(43, 18, 76, 0.85), rgba(82, 43, 91, 0.82));
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

.ticket-card {
  background: rgba(25, 0, 25, 0.7);
  padding: 40px;
  border-radius: 20px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  backdrop-filter: blur(15px);
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
  max-width: 600px;
  margin: 0 auto;
}

.performance-details {
  background: linear-gradient(135deg, rgba(160, 132, 202, 0.15), rgba(191, 172, 226, 0.15));
  padding: 30px;
  border-radius: 15px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  margin-bottom: 30px;
  position: relative;
  overflow: hidden;
}

.performance-details::before {
  content: '🎫';
  position: absolute;
  top: -20px;
  right: -20px;
  font-size: 120px;
  opacity: 0.1;
  transform: rotate(15deg);
}

.performance-details h3 {
  color: #F8E4D8;
  font-size: 28px;
  margin: 0 0 20px 0;
  text-align: center;
  text-shadow: 0 2px 10px rgba(223, 182, 178, 0.3);
}

.detail-row {
  display: flex;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid rgba(223, 182, 178, 0.2);
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-icon {
  font-size: 24px;
  margin-right: 15px;
  min-width: 30px;
}

.detail-content {
  flex: 1;
}

.detail-label {
  color: #A084CA;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
  margin-bottom: 4px;
}

.detail-value {
  color: #F8E4D8;
  font-size: 18px;
  font-weight: 600;
}

.performance-type-badge {
  display: inline-block;
  padding: 6px 16px;
  background: rgba(191, 172, 226, 0.3);
  color: #BFACE2;
  border-radius: 20px;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border: 1px solid rgba(191, 172, 226, 0.4);
}

.form-section {
  margin-top: 30px;
}

.form-section h4 {
  color: #DFB6B2;
  font-size: 18px;
  margin-bottom: 20px;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.price-input-wrapper {
  position: relative;
  margin-bottom: 25px;
}

.price-input-wrapper label {
  display: block;
  margin-bottom: 10px;
  color: #DFB6B2;
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.price-input-wrapper input {
  width: 100%;
  padding: 18px 20px 18px 50px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  border-radius: 12px;
  background: rgba(248, 228, 216, 0.95);
  color: #2B124C;
  font-size: 24px;
  font-weight: 700;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.price-input-wrapper input:focus {
  outline: none;
  border-color: #DFB6B2;
  background: rgba(248, 228, 216, 1);
  box-shadow: 0 0 0 4px rgba(223, 182, 178, 0.2);
  transform: translateY(-2px);
}

.currency-symbol {
  position: absolute;
  left: 20px;
  top: 52px;
  font-size: 24px;
  font-weight: 700;
  color: #522B5B;
}

.btn-purchase {
  width: 100%;
  padding: 18px;
  background: linear-gradient(135deg, #522B5B, #854F6C);
  color: #F8E4D8;
  border: none;
  border-radius: 12px;
  font-size: 20px;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  box-shadow: 0 6px 25px rgba(43, 18, 76, 0.4);
  position: relative;
  overflow: hidden;
}

.btn-purchase::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  transform: translate(-50%, -50%);
  transition: width 0.6s, height 0.6s;
}

.btn-purchase:hover::before {
  width: 300px;
  height: 300px;
}

.btn-purchase:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 35px rgba(43, 18, 76, 0.6);
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

.secure-badge {
  text-align: center;
  margin-top: 20px;
  color: #A084CA;
  font-size: 13px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.secure-badge::before {
  content: '🔒';
  font-size: 16px;
}

@media (max-width: 768px) {
  .page-header h2 {
    font-size: 32px;
  }
  
  .ticket-card {
    padding: 25px;
  }
  
  .performance-details {
    padding: 20px;
  }
}
</style>

<div class="ticket-page-wrapper">
  <div class="container">
    <div class="page-header">
      <h2>🎫 Buy Ticket</h2>
      <p>Secure your spot at this amazing performance</p>
    </div>

    <?php if ($msg): ?>
      <div class="notification success"><?=htmlspecialchars($msg)?></div>
    <?php endif; ?>
    
    <?php if ($errors): ?>
      <div class="notification error"><?=implode('<br>', array_map('htmlspecialchars',$errors))?></div>
    <?php endif; ?>

    <?php if ($perf): ?>
      <div class="ticket-card">
        <div class="performance-details">
          <h3>Performance Details</h3>
          
          <div class="detail-row">
            <div class="detail-icon">🎸</div>
            <div class="detail-content">
              <div class="detail-label">Band</div>
              <div class="detail-value"><?=htmlspecialchars($perf['band_name'])?></div>
            </div>
          </div>
          
          <div class="detail-row">
            <div class="detail-icon">📍</div>
            <div class="detail-content">
              <div class="detail-label">Venue</div>
              <div class="detail-value"><?=htmlspecialchars($perf['venue_name'])?></div>
            </div>
          </div>
          
          <div class="detail-row">
            <div class="detail-icon">📅</div>
            <div class="detail-content">
              <div class="detail-label">Date</div>
              <div class="detail-value"><?=htmlspecialchars(date('F j, Y', strtotime($perf['date'])))?></div>
            </div>
          </div>
          
          <?php if ($perf['start_time']): ?>
          <div class="detail-row">
            <div class="detail-icon">⏰</div>
            <div class="detail-content">
              <div class="detail-label">Time</div>
              <div class="detail-value"><?=htmlspecialchars(date('g:i A', strtotime($perf['start_time'])))?></div>
            </div>
          </div>
          <?php endif; ?>
          
          <?php if ($perf['performance_type']): ?>
          <div class="detail-row">
            <div class="detail-icon">🎭</div>
            <div class="detail-content">
              <div class="detail-label">Type</div>
              <div>
                <span class="performance-type-badge"><?=htmlspecialchars($perf['performance_type'])?></span>
              </div>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <form method="post" action="">
          <div class="form-section">
            <h4>💳 Payment Information</h4>
            
            <div class="price-input-wrapper">
              <label>Ticket Price</label>
              <span class="currency-symbol">$</span>
              <input type="number" step="0.01" name="price" value="100.00" required min="0">
            </div>

            <button type="submit" class="btn-purchase">
              🎫 Pay & Get Ticket
            </button>
            
            <div class="secure-badge">
              Secure Payment Processing
            </div>
          </div>
        </form>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>