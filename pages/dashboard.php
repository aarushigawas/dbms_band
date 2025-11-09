<?php
require_once __DIR__ . '/../config.php';
require_login();
$user_id = current_user_id();
$stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
include __DIR__ . '/header.php';
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;900&family=Poppins:wght@300;400;500;600;700&display=swap');

:root {
  --dark-purple: #190019;
  --deep-purple: #2B124C;
  --medium-purple: #522B5B;
  --rich-purple: #854F6C;
  --light-purple: #A084CA;
  --lavender: #BFACE2;
  --mauve: #DFB6B2;
  --cream: #F8E4D8;
  --white: #FBFCF8;
}

.dashboard-wrapper {
  background: url('https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?q=80&w=2070') center/cover fixed;
  min-height: 100vh;
  position: relative;
  font-family: 'Poppins', sans-serif;
}

.dashboard-wrapper::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(25, 0, 25, 0.92), rgba(43, 18, 76, 0.88), rgba(133, 79, 108, 0.85));
  z-index: 0;
}

.dashboard-container {
  position: relative;
  z-index: 1;
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px 20px;
}

/* Hero Section */
.hero-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  align-items: center;
  margin-bottom: 60px;
  padding: 60px 40px;
  background: rgba(25, 0, 25, 0.6);
  border-radius: 30px;
  border: 2px solid rgba(223, 182, 178, 0.3);
  backdrop-filter: blur(20px);
  position: relative;
  overflow: hidden;
  animation: fadeInUp 0.8s ease;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -50%;
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, rgba(160, 132, 202, 0.15), transparent);
  animation: float 8s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translate(0, 0); }
  50% { transform: translate(-20px, 20px); }
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.hero-content h1 {
  font-family: 'Playfair Display', serif;
  font-size: 56px;
  font-weight: 900;
  margin: 0 0 20px 0;
  background: linear-gradient(135deg, var(--white), var(--cream), var(--mauve), var(--lavender));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1.2;
  animation: gradient 3s ease infinite;
  background-size: 200% 200%;
}

@keyframes gradient {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

.hero-content p {
  font-size: 20px;
  color: var(--mauve);
  line-height: 1.8;
  margin-bottom: 30px;
  font-weight: 300;
}

.hero-content .welcome-name {
  display: inline-block;
  padding: 12px 24px;
  background: linear-gradient(135deg, var(--medium-purple), var(--rich-purple));
  border-radius: 50px;
  color: var(--cream);
  font-weight: 600;
  margin-bottom: 20px;
  box-shadow: 0 4px 15px rgba(133, 79, 108, 0.4);
}

.hero-image {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
}

.hero-image img {
  width: 100%;
  max-width: 450px;
  border-radius: 30px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
  border: 3px solid var(--mauve);
  animation: floatImage 6s ease-in-out infinite;
}

@keyframes floatImage {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-20px); }
}

/* Stats Cards */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 25px;
  margin-bottom: 50px;
}

.stat-card {
  background: rgba(25, 0, 25, 0.7);
  padding: 30px;
  border-radius: 20px;
  border: 2px solid rgba(223, 182, 178, 0.2);
  backdrop-filter: blur(15px);
  text-align: center;
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  animation: fadeInUp 0.8s ease;
  animation-fill-mode: both;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(223, 182, 178, 0.2), transparent);
  transition: left 0.6s ease;
}

.stat-card:hover::before {
  left: 100%;
}

.stat-card:hover {
  transform: translateY(-10px) scale(1.02);
  border-color: var(--lavender);
  box-shadow: 0 15px 40px rgba(160, 132, 202, 0.4);
}

.stat-icon {
  font-size: 48px;
  margin-bottom: 15px;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.stat-number {
  font-size: 42px;
  font-weight: 700;
  color: var(--cream);
  margin-bottom: 8px;
  font-family: 'Playfair Display', serif;
}

.stat-label {
  font-size: 16px;
  color: var(--mauve);
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 500;
}

/* Section Heading */
.section-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 30px;
  padding-bottom: 15px;
  border-bottom: 2px solid rgba(223, 182, 178, 0.2);
}

.section-header h2 {
  font-family: 'Playfair Display', serif;
  font-size: 36px;
  font-weight: 700;
  color: var(--cream);
  margin: 0;
}

.section-header .icon {
  font-size: 36px;
  animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

/* Performance Cards */
.performances-section {
  background: rgba(25, 0, 25, 0.6);
  padding: 40px;
  border-radius: 25px;
  border: 2px solid rgba(223, 182, 178, 0.2);
  backdrop-filter: blur(15px);
  margin-bottom: 40px;
}

.performance-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 25px;
}

.performance-card {
  background: linear-gradient(135deg, rgba(43, 18, 76, 0.8), rgba(82, 43, 91, 0.6));
  border-radius: 20px;
  padding: 30px;
  border: 2px solid rgba(223, 182, 178, 0.2);
  transition: all 0.4s ease;
  position: relative;
  overflow: hidden;
  cursor: pointer;
}

.performance-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 5px;
  background: linear-gradient(90deg, var(--light-purple), var(--lavender), var(--mauve));
  transform: scaleX(0);
  transition: transform 0.4s ease;
}

.performance-card:hover::before {
  transform: scaleX(1);
}

.performance-card:hover {
  transform: translateY(-8px);
  border-color: var(--mauve);
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
}

.performance-card .band-name {
  font-family: 'Playfair Display', serif;
  font-size: 24px;
  font-weight: 700;
  color: var(--cream);
  margin-bottom: 10px;
}

.performance-card .venue-name {
  color: var(--lavender);
  font-size: 18px;
  margin-bottom: 15px;
  font-weight: 500;
}

.performance-card .date-time {
  color: var(--mauve);
  font-size: 15px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.performance-card .date-time::before {
  content: '📅';
}

/* Quick Links */
.quick-links-section {
  background: rgba(25, 0, 25, 0.6);
  padding: 40px;
  border-radius: 25px;
  border: 2px solid rgba(223, 182, 178, 0.2);
  backdrop-filter: blur(15px);
}

.quick-links-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.quick-link {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 30px 20px;
  background: linear-gradient(135deg, rgba(82, 43, 91, 0.5), rgba(133, 79, 108, 0.5));
  border: 2px solid rgba(223, 182, 178, 0.2);
  border-radius: 18px;
  text-decoration: none;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.quick-link::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(191, 172, 226, 0.2);
  transform: translate(-50%, -50%);
  transition: width 0.4s ease, height 0.4s ease;
}

.quick-link:hover::before {
  width: 300px;
  height: 300px;
}

.quick-link:hover {
  transform: translateY(-5px);
  border-color: var(--lavender);
  box-shadow: 0 10px 30px rgba(191, 172, 226, 0.4);
}

.quick-link .link-icon {
  font-size: 40px;
  margin-bottom: 12px;
  z-index: 1;
}

.quick-link .link-text {
  color: var(--cream);
  font-weight: 600;
  font-size: 16px;
  text-align: center;
  z-index: 1;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--mauve);
}

.empty-state p {
  font-size: 18px;
  margin-bottom: 20px;
}

.empty-state img {
  max-width: 300px;
  opacity: 0.7;
  margin-bottom: 20px;
}

@media (max-width: 1024px) {
  .hero-section {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .hero-content h1 {
    font-size: 42px;
  }
  
  .hero-image img {
    max-width: 350px;
  }
}

@media (max-width: 768px) {
  .hero-content h1 {
    font-size: 36px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr 1fr;
    max-width: 500px;
  }
  
  .performance-grid {
    grid-template-columns: 1fr;
  }
  
  .quick-links-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .section-header h2 {
    font-size: 28px;
  }
}

@media (max-width: 500px) {
  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .stat-card {
    padding: 20px 15px;
  }
  
  .stat-icon {
    font-size: 32px;
  }
  
  .stat-number {
    font-size: 28px;
  }
}
</style>

<div class="dashboard-wrapper">
  <div class="dashboard-container">
    
    <!-- Hero Section -->
    <div class="hero-section">
      <div class="hero-content">
        <div class="welcome-name">👋 Welcome Back!</div>
        <h1><?=htmlspecialchars($user['name'] ?: $user['username'])?></h1>
        <p>Your personality is what makes you character. Manage your performances, bands, and venues all in one beautiful space.</p>
      </div>
      <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?q=80&w=800" alt="Music Performance">
      </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🎸</div>
        <div class="stat-number">
          <?php
          $stmt = $pdo->prepare("SELECT COUNT(*) FROM bands WHERE manager_user_id = ?");
          $stmt->execute([$user_id]);
          echo $stmt->fetchColumn();
          ?>
        </div>
        <div class="stat-label">Your Bands</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🎤</div>
        <div class="stat-number">
          <?php
          $role = current_user_role();
          if ($role === 'manager') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM performances p JOIN bands b ON p.b_id=b.b_id WHERE p.status='scheduled' AND p.date >= CURDATE() AND b.manager_user_id = ?");
            $stmt->execute([$user_id]);
            echo $stmt->fetchColumn();
          } elseif ($role === 'venue_owner') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM performances p JOIN venues v ON p.venue_id=v.venue_id WHERE p.status='scheduled' AND p.date >= CURDATE() AND v.owner_user_id = ?");
            $stmt->execute([$user_id]);
            echo $stmt->fetchColumn();
          } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM performances WHERE status='scheduled' AND date >= CURDATE()");
            echo $stmt->fetchColumn();
          }
          ?>
        </div>
        <div class="stat-label">Upcoming Shows</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🏛️</div>
        <div class="stat-number">
          <?php
          $role = current_user_role();
          if ($role === 'venue_owner') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM venues WHERE owner_user_id = ?");
            $stmt->execute([$user_id]);
            echo $stmt->fetchColumn();
          } else {
            $stmt = $pdo->query("SELECT COUNT(*) FROM venues");
            echo $stmt->fetchColumn();
          }
          ?>
        </div>
        <div class="stat-label">Total Venues</div>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">🎫</div>
        <div class="stat-number">
          <?php
          $role = current_user_role();
          if ($role === 'manager') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE created_by = ?");
            $stmt->execute([$user_id]);
            echo $stmt->fetchColumn();
          } elseif ($role === 'venue_owner') {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings bk JOIN venues v ON bk.venue_id = v.venue_id WHERE v.owner_user_id = ?");
            $stmt->execute([$user_id]);
            echo $stmt->fetchColumn();
          } else {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tickets WHERE buyer_user_id = ?");
            $stmt->execute([$user_id]);
            echo $stmt->fetchColumn();
          }
          ?>
        </div>
        <div class="stat-label">Your Bookings</div>
      </div>
    </div>

    <!-- Upcoming Performances Section -->
    <div class="performances-section">
      <div class="section-header">
        <span class="icon">🎵</span>
        <h2>Upcoming Performances</h2>
      </div>
      
      <?php
      $stmt = $pdo->query("
        SELECT p.*, b.band_name, v.venue_name
        FROM performances p
        JOIN bands b ON p.b_id = b.b_id
        JOIN venues v ON p.venue_id = v.venue_id
        WHERE p.date >= CURDATE()
        ORDER BY p.date ASC
        LIMIT 9
      ");
      $rows = $stmt->fetchAll();
      
      if ($rows) {
        echo '<div class="performance-grid">';
        foreach($rows as $r) {
          echo '<div class="performance-card">';
          echo '<div class="band-name">'.htmlspecialchars($r['band_name']).'</div>';
          echo '<div class="venue-name">@ '.htmlspecialchars($r['venue_name']).'</div>';
          echo '<div class="date-time">'.date('F j, Y', strtotime($r['date'])).'</div>';
          echo '</div>';
        }
        echo '</div>';
      } else {
        echo '<div class="empty-state">';
        echo '<img src="https://images.unsplash.com/photo-1511735111819-9a3f7709049c?q=80&w=300" alt="No performances">';
        echo '<p>No upcoming performances scheduled yet.</p>';
        echo '<p style="font-size: 14px; opacity: 0.7;">Create your first band and start booking venues!</p>';
        echo '</div>';
      }
      ?>
    </div>

    <!-- Quick Links Section -->
    <div class="quick-links-section">
      <div class="section-header">
        <span class="icon">⚡</span>
        <h2>Quick Access</h2>
      </div>
      
      <div class="quick-links-grid">
        <a href="/band/pages/performances.php" class="quick-link">
          <div class="link-icon">🎭</div>
          <div class="link-text">Manage Performances</div>
        </a>
        
        <a href="/band/pages/band_request.php" class="quick-link">
          <div class="link-icon">🎸</div>
          <div class="link-text">View Bands</div>
        </a>
        
        <a href="/band/pages/venue_requests.php" class="quick-link">
          <div class="link-icon">🏛️</div>
          <div class="link-text">Venue Requests</div>
        </a>
        
        <a href="/band/pages/profile.php" class="quick-link">
          <div class="link-icon">👤</div>
          <div class="link-text">Edit Profile</div>
        </a>
        
        <a href="/band/pages/browse.php" class="quick-link">
          <div class="link-icon">🔍</div>
          <div class="link-text">Browse Shows</div>
        </a>
        
        <a href="/band/pages/bookings.php" class="quick-link">
          <div class="link-icon">📅</div>
          <div class="link-text">My Bookings</div>
        </a>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>