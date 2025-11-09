<?php
require_once __DIR__ . '/../config.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Band Management</title>
  <link rel="stylesheet" href="/band/style.css">
  <script src="/band/assets/js/main.js" defer></script>
</head>
<body>
  <header class="site-header">
    <div class="container">
      <a class="brand" href="/band/index.php">BandMgmt</a>
      <nav>
        <a href="/band/pages/dashboard.php">Dashboard</a>
        <?php if (current_user_role()==='manager'): ?>
          <a href="/band/pages/performances.php">Performances</a>
          <a href="/band/pages/band_request.php">Bands</a>
          <a href="/band/pages/bookings.php">Bookings</a>
          <a href="/band/pages/received_reviews.php">Received Reviews</a>
        <?php endif; ?>
        <?php if (current_user_role()==='venue_owner'): ?>
          <a href="/band/pages/venues.php">My Venues</a>
          <a href="/band/pages/venue_requests.php">Venue Requests</a>
          <a href="/band/pages/received_reviews.php">Received Reviews</a>
        <?php endif; ?>
        <?php if (current_user_role()==='general'): ?>
          <a href="/band/pages/browse.php">Browse</a>
          <a href="/band/pages/reviews.php">Reviews</a>
        <?php endif; ?>
        <a href="/band/pages/profile.php">Profile</a>
        <?php if(is_logged_in()): ?>
          <a href="/band/pages/logout.php">Logout</a>
        <?php else: ?>
          <a href="/band/pages/login.php">Login</a>
          <a href="/band/pages/register.php">Register</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <main class="container">
