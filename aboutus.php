<?php
session_start();

// cart count sa session
$cart_count = 0;
if (isset($_SESSION['cart'])) {
  foreach ($_SESSION['cart'] as $item) {
    $cart_count += $item['quantity'];
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>About Us | Kyla's Bistro</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .about-hero {
      background: linear-gradient(rgba(160, 82, 45, 0.7), rgba(0, 0, 0, 0.7)), url('pictures/logo.jpg');
      background-size: cover;
      background-position: center;
      padding: 80px 20px;
      text-align: center;
      color: white;
    }
    .about-hero h1 {
      font-size: 3em;
      margin-bottom: 20px;
      text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
    }
    .about-hero p {
      font-size: 1.3em;
      max-width: 700px;
      margin: 0 auto;
      line-height: 1.6;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    }
    .about-content {
      max-width: 1200px;
      margin: 60px auto;
      padding: 0 20px;
    }
    .about-section {
      margin-bottom: 60px;
    }
    .about-section h2 {
      color: #a0522d;
      font-size: 2.2em;
      margin-bottom: 20px;
      text-align: center;
    }
    .about-section p {
      font-size: 1.1em;
      line-height: 1.8;
      color: #333;
      text-align: center;
      max-width: 900px;
      margin: 0 auto 20px;
    }
    .story-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 30px;
      margin-top: 40px;
    }
    .story-card {
      background: #fff8f0;
      padding: 30px;
      border-radius: 12px;
      border: 2px solid #e0c3a0;
      text-align: center;
    }
    .story-card h3 {
      color: #d2691e;
      font-size: 1.5em;
      margin-bottom: 15px;
    }
    .story-card p {
      color: #555;
      line-height: 1.6;
    }
    .restaurant-info {
      background-color: #fff8f0;
      padding: 50px 20px;
      border-top: 2px solid #e0c3a0;
      margin-top: 60px;
    }
    .info-container {
      max-width: 1000px;
      margin: auto;
      text-align: center;
    }
    .restaurant-info h2 {
      font-size: 2.5em;
      color: #a0522d;
      margin-bottom: 15px;
    }
    .tagline {
      font-style: italic;
      color: #555;
      font-size: 1.2em;
      margin-bottom: 40px;
    }
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 25px;
      margin-top: 30px;
    }
    .info-item {
      background-color: #fff;
      border: 2px solid #e0c3a0;
      padding: 25px;
      border-radius: 10px;
      transition: transform 0.3s ease;
    }
    .info-item h3 {
      color: #d2691e;
      margin-bottom: 15px;
      font-size: 1.4em;
    }
    .info-item p {
      margin: 8px 0;
      color: #333;
    }
    .info-item a {
      color: #a0522d;
      text-decoration: none;
      font-weight: 500;
    }
  </style>
</head>
<body class="index">
  <header>
    <div class="nav-bar">
      <img src="pictures/logo.jpg" alt="Kyla Logo" class="logo" />
      <div class="nav-actions">
        <?php if (isset($_SESSION['username'])): ?>
          <span class="welcome-text">👋 Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
          <a href="profile.php" class="btn profile-btn" title="View Profile">👤 Profile</a>
          <a href="cart.php" class="cart-icon" title="View Cart">🛒<?= $cart_count > 0 ? " ($cart_count)" : "" ?></a>
          <a href="customer_logout.php" class="btn logout-btn">LOG OUT</a>
        <?php else: ?>
          <a href="login.php" class="btn login-btn">LOGIN</a>
          <a href="registration.php" class="btn signup-btn">SIGN UP</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <nav>
    <ul class="links">
      <li><a href="index.php">HOME</a></li>
      <li><a href="menu.php">MENU</a></li>
      <li><a href="aboutus.php" class="active">ABOUT US</a></li>
    </ul>
  </nav>

  <section class="about-hero">
    <h1>About Kyla's Bistro</h1>
    <p>Where culinary passion meets warm hospitality. Experience the perfect blend of delicious food, cozy ambiance, and memorable moments.</p>
  </section>

  <div class="about-content">
    <section class="about-section">
      <h2>What Makes Us Special</h2>
      <div class="story-grid">
        <div class="story-card">
          <h3>🍽️ Fresh Ingredients</h3>
          <p>We source the finest local ingredients to ensure every dish is crafted with care and bursting with authentic flavors.</p>
        </div>
        <div class="story-card">
          <h3>👨‍🍳 Expert Chefs</h3>
          <p>Our talented culinary team brings years of experience and passion to create memorable dining experiences.</p>
        </div>
        <div class="story-card">
          <h3>🏠 Cozy Atmosphere</h3>
          <p>Enjoy our warm, inviting space perfect for intimate dinners, family gatherings, or special celebrations.</p>
        </div>
      </div>
    </section>

    <section class="about-section">
      <h2>Perfect for Your Events</h2>
      <p>
        Looking for a cozy, stylish spot for your next special occasion? Kyla's Bistro is now open for event bookings! Whether it's an intimate birthday celebration, corporate meeting, or any milestone worth celebrating, we provide the perfect setting and exceptional service to make your event unforgettable.
      </p>
    </section>
  </div>

  <section class="restaurant-info">
    <div class="info-container">
      <h2>📍 Visit Us at Kyla's Bistro</h2>
      <p class="tagline">Where every hour is flavor hour!</p>
      <div class="info-grid">
        <div class="info-item">
          <h3>📞 Contact</h3>
          <p>Phone: <a href="tel:+639123456789">+63 912 345 6789</a></p>
          <p>Email: <a href="mailto:hello@kylasbistro.com">hello@kylasbistro.com</a></p>
        </div>
        <div class="info-item">
          <h3>📌 Location</h3>
          <p>Ground Floor, Riverside Plaza,<br>Iligan City, Northern Mindanao</p>
        </div>
        <div class="info-item">
          <h3>⏰ Hours</h3>
          <p>Monday – Friday: 10:00 AM – 9:00 PM</p>
          <p>Saturday – Sunday: 9:00 AM – 10:00 PM</p>
        </div>
        <div class="info-item">
          <h3>ⓕ Follow Us</h3>
          <p>Facebook: <a href="https://www.facebook.com/profile.php?id=100063483683835" target="_blank">@kylasbistro</a></p>
          <p>Stay updated with our latest menu & promos!</p>
        </div>
      </div>
    </div>
  </section>
</body>
</html>