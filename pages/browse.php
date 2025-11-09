<?php
require_once __DIR__ . '/../config.php';
require_login();
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

.browse-wrapper {
  background: url('https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?q=80&w=2070') center/cover fixed;
  min-height: 100vh;
  position: relative;
  font-family: 'Poppins', sans-serif;
}

.browse-wrapper::before {
  content: '';
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(25, 0, 25, 0.93), rgba(43, 18, 76, 0.9), rgba(82, 43, 91, 0.88));
  z-index: 0;
}

.browse-container {
  position: relative;
  z-index: 1;
  max-width: 1400px;
  margin: 0 auto;
  padding: 40px 20px 80px;
}

/* Hero Section */
.browse-hero {
  text-align: center;
  padding: 80px 20px 60px;
  margin-bottom: 60px;
  position: relative;
  animation: fadeInDown 0.8s ease;
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.browse-hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: 72px;
  font-weight: 900;
  margin: 0 0 20px 0;
  background: linear-gradient(135deg, var(--white), var(--cream), var(--mauve), var(--lavender));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1.1;
  animation: gradient 3s ease infinite;
  background-size: 200% 200%;
  text-shadow: 0 4px 30px rgba(223, 182, 178, 0.3);
}

@keyframes gradient {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

.browse-hero p {
  font-size: 24px;
  color: var(--mauve);
  font-weight: 300;
  letter-spacing: 0.5px;
  animation: fadeIn 1s ease 0.3s both;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.browse-hero .subtitle {
  display: inline-block;
  padding: 12px 30px;
  background: rgba(160, 132, 202, 0.2);
  border: 2px solid rgba(191, 172, 226, 0.4);
  border-radius: 50px;
  margin-top: 20px;
  font-size: 16px;
  color: var(--lavender);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 2px;
  animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

/* Filter Section */
.filter-section {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-bottom: 50px;
  flex-wrap: wrap;
  animation: fadeInUp 0.8s ease 0.2s both;
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

.filter-btn {
  padding: 12px 28px;
  background: rgba(25, 0, 25, 0.6);
  border: 2px solid rgba(223, 182, 178, 0.3);
  border-radius: 50px;
  color: var(--mauve);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
}

.filter-btn:hover,
.filter-btn.active {
  background: linear-gradient(135deg, var(--medium-purple), var(--rich-purple));
  border-color: var(--lavender);
  color: var(--cream);
  transform: translateY(-3px);
  box-shadow: 0 8px 25px rgba(160, 132, 202, 0.4);
}

/* Performance Grid */
.performances-section {
  animation: fadeInUp 0.8s ease 0.4s both;
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 40px;
  padding: 0 10px;
}

.section-header h2 {
  font-family: 'Playfair Display', serif;
  font-size: 42px;
  font-weight: 700;
  color: var(--cream);
  margin: 0;
  display: flex;
  align-items: center;
  gap: 15px;
}

.section-header .icon {
  font-size: 42px;
  animation: bounce 2s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.section-header .count {
  font-size: 18px;
  color: var(--mauve);
  font-weight: 500;
  padding: 8px 20px;
  background: rgba(223, 182, 178, 0.15);
  border-radius: 50px;
  border: 2px solid rgba(223, 182, 178, 0.3);
}

.performance-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 30px;
}

.performance-card {
  background: rgba(25, 0, 25, 0.7);
  border-radius: 24px;
  overflow: hidden;
  border: 2px solid rgba(223, 182, 178, 0.2);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  cursor: pointer;
  backdrop-filter: blur(15px);
  animation: cardFadeIn 0.6s ease both;
}

.performance-card:nth-child(1) { animation-delay: 0.1s; }
.performance-card:nth-child(2) { animation-delay: 0.15s; }
.performance-card:nth-child(3) { animation-delay: 0.2s; }
.performance-card:nth-child(4) { animation-delay: 0.25s; }
.performance-card:nth-child(5) { animation-delay: 0.3s; }
.performance-card:nth-child(6) { animation-delay: 0.35s; }

@keyframes cardFadeIn {
  from {
    opacity: 0;
    transform: translateY(30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.performance-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 6px;
  background: linear-gradient(90deg, var(--light-purple), var(--lavender), var(--mauve));
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.5s ease;
}

.performance-card:hover::before {
  transform: scaleX(1);
}

.performance-card:hover {
  transform: translateY(-12px);
  border-color: var(--lavender);
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 40px rgba(160, 132, 202, 0.3);
}

.card-image-wrapper {
  position: relative;
  width: 100%;
  height: 240px;
  overflow: hidden;
  background: linear-gradient(135deg, var(--deep-purple), var(--medium-purple));
}

.card-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.performance-card:hover .card-image {
  transform: scale(1.15) rotate(2deg);
}

.card-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(180deg, transparent 0%, rgba(25, 0, 25, 0.9) 100%);
  opacity: 0;
  transition: opacity 0.4s ease;
}

.performance-card:hover .card-overlay {
  opacity: 1;
}

.card-badge {
  position: absolute;
  top: 15px;
  right: 15px;
  padding: 8px 16px;
  background: rgba(191, 172, 226, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 20px;
  color: var(--deep-purple);
  font-weight: 700;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
  animation: badgePulse 2s ease-in-out infinite;
}

@keyframes badgePulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.05); }
}

.card-content {
  padding: 25px;
}

.band-name {
  font-family: 'Playfair Display', serif;
  font-size: 26px;
  font-weight: 700;
  color: var(--cream);
  margin-bottom: 10px;
  line-height: 1.3;
}

.venue-name {
  color: var(--lavender);
  font-size: 18px;
  margin-bottom: 15px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
}

.venue-name::before {
  content: '📍';
  font-size: 16px;
}

.date-time-wrapper {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.date-time {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--mauve);
  font-size: 15px;
  font-weight: 500;
  padding: 8px 16px;
  background: rgba(160, 132, 202, 0.15);
  border-radius: 20px;
  border: 1px solid rgba(160, 132, 202, 0.3);
}

.date-time .icon {
  font-size: 16px;
}

.card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 15px;
  border-top: 1px solid rgba(223, 182, 178, 0.2);
}

.performance-type {
  color: var(--lavender);
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
}

.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: linear-gradient(135deg, var(--medium-purple), var(--rich-purple));
  color: var(--cream);
  border: none;
  border-radius: 50px;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.3s ease;
  cursor: pointer;
  font-size: 15px;
  box-shadow: 0 6px 20px rgba(82, 43, 91, 0.4);
  position: relative;
  overflow: hidden;
}

.btn::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 0;
  height: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  transform: translate(-50%, -50%);
  transition: width 0.6s ease, height 0.6s ease;
}

.btn:hover::before {
  width: 300px;
  height: 300px;
}

.btn:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 30px rgba(160, 132, 202, 0.5);
  background: linear-gradient(135deg, var(--light-purple), var(--lavender));
  color: var(--deep-purple);
}

.btn span {
  position: relative;
  z-index: 1;
}

/* Empty State */
.empty-state {
  text-align: center;
  padding: 100px 20px;
  animation: fadeIn 1s ease;
}

.empty-state img {
  max-width: 400px;
  width: 100%;
  margin-bottom: 30px;
  opacity: 0.8;
  animation: float 6s ease-in-out infinite;
}

.empty-state h3 {
  font-family: 'Playfair Display', serif;
  font-size: 36px;
  color: var(--cream);
  margin-bottom: 15px;
}

.empty-state p {
  font-size: 18px;
  color: var(--mauve);
  max-width: 500px;
  margin: 0 auto 30px;
  line-height: 1.8;
}

.empty-state .explore-btn {
  display: inline-block;
  padding: 15px 40px;
  background: linear-gradient(135deg, var(--medium-purple), var(--rich-purple));
  color: var(--cream);
  text-decoration: none;
  border-radius: 50px;
  font-weight: 700;
  font-size: 16px;
  transition: all 0.3s ease;
  box-shadow: 0 8px 25px rgba(82, 43, 91, 0.4);
}

.empty-state .explore-btn:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 35px rgba(160, 132, 202, 0.5);
  background: linear-gradient(135deg, var(--light-purple), var(--lavender));
  color: var(--deep-purple);
}

/* Loading Animation */
@keyframes shimmer {
  0% { background-position: -1000px 0; }
  100% { background-position: 1000px 0; }
}

/* Responsive */
@media (max-width: 1024px) {
  .browse-hero h1 {
    font-size: 56px;
  }
  
  .performance-grid {
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
  }
}

@media (max-width: 768px) {
  .browse-hero h1 {
    font-size: 42px;
  }
  
  .browse-hero p {
    font-size: 18px;
  }
  
  .performance-grid {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .section-header h2 {
    font-size: 32px;
  }
}

@media (max-width: 480px) {
  .browse-hero h1 {
    font-size: 32px;
  }
  
  .filter-section {
    gap: 10px;
  }
  
  .filter-btn {
    padding: 10px 20px;
    font-size: 14px;
  }
}
</style>

<div class="browse-wrapper">
  <div class="browse-container">
    
    <!-- Hero Section -->
    <div class="browse-hero">
      <h1>🎵 Browse Performances</h1>
      <p>Discover amazing live shows and book your tickets</p>
      <div class="subtitle">🎤 Live Music Experience</div>
    </div>

    <!-- Filter Section (Optional - Can be activated with JS) -->
    <div class="filter-section">
      <button class="filter-btn active">All Shows</button>
      <button class="filter-btn">This Week</button>
      <button class="filter-btn">This Month</button>
      <button class="filter-btn">Concerts</button>
      <button class="filter-btn">Private Events</button>
    </div>

    <!-- Performances Section -->
    <div class="performances-section">
      <?php
      $stmt = $pdo->query("
        SELECT p.performance_id, p.date, p.start_time, p.performance_type, b.band_name, v.venue_name
        FROM performances p
        JOIN bands b ON p.b_id = b.b_id
        JOIN venues v ON p.venue_id = v.venue_id
        WHERE p.status = 'scheduled' AND p.date >= CURDATE()
        ORDER BY p.date ASC, p.start_time ASC
      ");
      $rows = $stmt->fetchAll();
      ?>
      
      <?php if ($rows): ?>
        <div class="section-header">
          <h2>
            <span class="icon">🎸</span>
            Upcoming Shows
          </h2>
          <div class="count"><?=count($rows)?> Events</div>
        </div>
        
        <div class="performance-grid">
          <?php 
          $images = [
            'https://images.unsplash.com/photo-1501612780327-45045538702b?q=80&w=800',
            'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?q=80&w=800',
            'https://images.unsplash.com/photo-1524368535928-5b5e00ddc76b?q=80&w=800',
            'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?q=80&w=800',
            'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?q=80&w=800',
            'https://images.unsplash.com/photo-1506157786151-b8491531f063?q=80&w=800'
          ];
          $imgIndex = 0;
          ?>
          
          <?php foreach($rows as $r): ?>
            <div class="performance-card">
              <div class="card-image-wrapper">
                <img src="<?=$images[$imgIndex % count($images)]?>" alt="<?=htmlspecialchars($r['band_name'])?>" class="card-image">
                <div class="card-overlay"></div>
                <div class="card-badge">Featured</div>
              </div>
              
              <div class="card-content">
                <div class="band-name"><?=htmlspecialchars($r['band_name'])?></div>
                <div class="venue-name"><?=htmlspecialchars($r['venue_name'])?></div>
                
                <div class="date-time-wrapper">
                  <div class="date-time">
                    <span class="icon">📅</span>
                    <?=date('M j, Y', strtotime($r['date']))?>
                  </div>
                  <?php if ($r['start_time']): ?>
                    <div class="date-time">
                      <span class="icon">🕐</span>
                      <?=date('g:i A', strtotime($r['start_time']))?>
                    </div>
                  <?php endif; ?>
                </div>
                
                <div class="card-footer">
                  <div class="performance-type">
                    <?=htmlspecialchars($r['performance_type'] ?: 'Concert')?>
                  </div>
                  <?php if (current_user_role()==='general'): ?>
                    <a href="/band/pages/purchase.php?performance_id=<?=$r['performance_id']?>" class="btn">
                      <span>🎫 Get Tickets</span>
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <?php $imgIndex++; ?>
          <?php endforeach; ?>
        </div>
        
      <?php else: ?>
        <div class="empty-state">
          <img src="https://images.unsplash.com/photo-1511735111819-9a3f7709049c?q=80&w=400" alt="No performances">
          <h3>No Shows Available</h3>
          <p>There are no upcoming performances at the moment. Check back soon for exciting new shows and events!</p>
          <a href="/band/pages/dashboard.php" class="explore-btn">Back to Dashboard</a>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>