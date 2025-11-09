<?php
require_once __DIR__ . '/../config.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Band Management</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/style.css">
  <script src="<?= BASE_URL ?>/assets/js/main.js" defer></script>
</head>
<body>
  <header class="site-header">
    <div class="container">
      <a class="brand" href="<?= BASE_URL ?>/index.php">BandSync</a>
      <nav>
        <a href="<?= BASE_URL ?>/pages/dashboard.php">Dashboard</a>
        <?php if (current_user_role()==='manager'): ?>
          <a href="<?= BASE_URL ?>/pages/performances.php">Performances</a>
          <a href="<?= BASE_URL ?>/pages/band_request.php">Bands</a>
          <a href="<?= BASE_URL ?>/pages/bookings.php">Bookings</a>
          <a href="<?= BASE_URL ?>/pages/received_reviews.php">Received Reviews</a>
        <?php endif; ?>
        <?php if (current_user_role()==='venue_owner'): ?>
          <a href="<?= BASE_URL ?>/pages/venues.php">My Venues</a>
          <a href="<?= BASE_URL ?>/pages/venue_requests.php">Venue Requests</a>
          <a href="<?= BASE_URL ?>/pages/received_reviews.php">Received Reviews</a>
        <?php endif; ?>
        <?php if (current_user_role()==='general'): ?>
          <a href="<?= BASE_URL ?>/pages/browse.php">Browse</a>
          <a href="<?= BASE_URL ?>/pages/reviews.php">Reviews</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/pages/profile.php">Profile</a>
        <?php if(is_logged_in()): ?>
          <a href="<?= BASE_URL ?>/pages/logout.php">Logout</a>
        <?php else: ?>
          <a href="<?= BASE_URL ?>/pages/login.php">Login</a>
          <a href="<?= BASE_URL ?>/pages/register.php">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <main class="container">
