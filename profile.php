<?php
session_start();
include 'database.php';

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
  header("Location: login.php");
  exit();
}

$customer_id = $_SESSION['customer_id'];
$success_message = '';
$error_message = '';

// Fetch customer information
$customer_stmt = $conn->prepare("SELECT * FROM customer WHERE customer_id = ?");
$customer_stmt->bind_param("i", $customer_id);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
$customer = $customer_result->fetch_assoc();
$customer_stmt->close();

if (!$customer) {
  header("Location: login.php");
  exit();
}

// Fetch customer orders with status
$orders_stmt = $conn->prepare("
  SELECT 
    o.order_id,
    o.order_date,
    o.order_time,
    o.total_amount,
    o.order_status
  FROM `orders` o
  WHERE o.customer_id = ?
  ORDER BY o.order_date DESC, o.order_time DESC
");
$orders_stmt->bind_param("i", $customer_id);
$orders_stmt->execute();
$orders_result = $orders_stmt->get_result();
$orders_stmt->close();

// Fetch customer reservations
$reservations_stmt = $conn->prepare("
  SELECT 
    r.reservation_id,
    r.reservation_date,
    r.reservation_time,
    r.event_type,
    r.status,
    r.total_hours,
    r.total_price,
    t.table_number,
    t.table_type
  FROM reservation r
  JOIN tables t ON r.table_id = t.table_id
  WHERE r.customer_id = ?
  ORDER BY r.reservation_date DESC, r.reservation_time DESC
");
$reservations_stmt->bind_param("i", $customer_id);
$reservations_stmt->execute();
$reservations_result = $reservations_stmt->get_result();
$reservations_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - Kyla's Bistro</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .profile-container {
      max-width: 900px;
      margin: 40px auto;
      padding: 20px;
    }
    
    .page-title {
      font-size: 28px;
      margin-bottom: 30px;
      color: #333;
    }
    
    .alert {
      padding: 12px 16px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    
    .alert-success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    
    .alert-error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    
    .profile-section {
      background: white;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 25px;
      margin-bottom: 25px;
    }
    
    .section-title {
      font-size: 18px;
      font-weight: 600;
      color: #333;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .edit-btn {
      background: #007bff;
      color: white;
      padding: 6px 12px;
      border-radius: 3px;
      text-decoration: none;
      font-size: 14px;
      border: none;
      cursor: pointer;
    }
    
    .edit-btn:hover {
      background: #0056b3;
    }
    
    .info-row {
      display: grid;
      grid-template-columns: 150px 1fr;
      padding: 10px 0;
      border-bottom: 1px solid #f5f5f5;
    }
    
    .info-row:last-child {
      border-bottom: none;
    }
    
    .info-label {
      font-weight: 600;
      color: #666;
    }
    
    .info-value {
      color: #333;
    }
    
    .orders-table, .reservations-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    
    .orders-table th, .reservations-table th {
      text-align: left;
      padding: 10px;
      background: #f8f9fa;
      border-bottom: 2px solid #dee2e6;
      font-weight: 600;
      color: #333;
      font-size: 14px;
    }
    
    .orders-table td, .reservations-table td {
      padding: 12px 10px;
      border-bottom: 1px solid #dee2e6;
      font-size: 14px;
    }
    
    .orders-table tr:hover, .reservations-table tr:hover {
      background: #f8f9fa;
    }
    
    .view-btn {
      background: #28a745;
      color: white;
      padding: 5px 12px;
      border-radius: 3px;
      text-decoration: none;
      font-size: 13px;
      display: inline-block;
    }
    
    .view-btn:hover {
      background: #218838;
    }
    
    .back-btn {
      background: #6c757d;
      color: white;
      padding: 8px 16px;
      border-radius: 3px;
      text-decoration: none;
      display: inline-block;
      margin-bottom: 20px;
    }
    
    .back-btn:hover {
      background: #5a6268;
    }
    
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: #999;
    }
    
    .status-badge {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
    }
    
    /* Order Status Colors */
    .status-pending {
      background-color: #fff3cd;
      color: #856404;
    }
    
    .status-preparing {
      background-color: #cfe2ff;
      color: #084298;
    }
    
    .status-ready {
      background-color: #d1e7dd;
      color: #0f5132;
    }
    
    .status-completed {
      background-color: #d1ecf1;
      color: #0c5460;
    }
    
    .status-cancelled {
      background-color: #f8d7da;
      color: #721c24;
    }
    
    /* Reservation Status Colors */
    .status-confirmed {
      background-color: #d4edda;
      color: #155724;
    }
    
    .reservation-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
    }
    
    .tab-btn {
      padding: 8px 16px;
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      border-radius: 3px;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.3s ease;
    }
    
    .tab-btn.active {
      background: #8b4513;
      color: white;
      border-color: #8b4513;
    }
    
    .tab-content {
      display: none;
    }
    
    .tab-content.active {
      display: block;
    }

    /* Status icon indicators */
    .status-icon {
      display: inline-block;
      margin-right: 5px;
    }
  </style>
</head>
<body class="index">
  <header>
    <div class="nav-bar">
      <img src="pictures/logo.jpg" alt="Kyla Logo" class="logo" />
      <div class="nav-actions">
        <?php $cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>
        <span class="welcome-text">👋 Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
        <a href="profile.php" class="btn profile-btn" title="View Profile">👤 Profile</a>
        <a href="cart.php" class="cart-icon" title="View Cart">🛒<?= $cart_count > 0 ? " ($cart_count)" : "" ?></a>
        <a href="customer_logout.php" class="btn logout-btn">LOG OUT</a>
      </div>
    </div>
  </header>

  <nav>
    <ul class="links">
      <li><a href="index.php">HOME</a></li>
      <li><a href="menu.php">MENU</a></li>
      <li><a href="aboutus.php">ABOUT US</a></li>
    </ul>
  </nav>

  <div class="profile-container">
    <a href="menu.php" class="back-btn">← Back to Menu</a>
    
    <?php if ($success_message): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>
    
    <h1 class="page-title">My Profile</h1>

    <div class="profile-section">
      <div class="section-title">
        <span>Personal Information</span>
        <a href="profile_edit.php" class="edit-btn">Edit Profile</a>
      </div>
      
      <div class="info-row">
        <div class="info-label">First Name:</div>
        <div class="info-value"><?= htmlspecialchars($customer['first_name']) ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Middle Name:</div>
        <div class="info-value"><?= htmlspecialchars($customer['middle_name']) ?: '-' ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Last Name:</div>
        <div class="info-value"><?= htmlspecialchars($customer['last_name']) ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Username:</div>
        <div class="info-value"><?= htmlspecialchars($customer['username']) ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Email:</div>
        <div class="info-value"><?= htmlspecialchars($customer['email']) ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Phone Number:</div>
        <div class="info-value"><?= htmlspecialchars($customer['phone_number']) ?></div>
      </div>
      <div class="info-row">
        <div class="info-label">Address:</div>
        <div class="info-value"><?= htmlspecialchars($customer['address']) ?></div>
      </div>
    </div>

    <!-- Order History Section with Status -->
    <div class="profile-section">
      <h2 class="section-title">Order History & Status</h2>
      
      <?php if ($orders_result && $orders_result->num_rows > 0): ?>
        <table class="orders-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Date</th>
              <th>Time</th>
              <th>Total Amount</th>
              <th>Order Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($order = $orders_result->fetch_assoc()): 
              $status = $order['order_status'] ?: 'Pending';
              $status_lower = strtolower(str_replace(' ', '', $status));
              
              // Status icons
              $status_icons = [
                'pending' => '⏳',
                'preparing' => '👨‍🍳',
                'ready' => '✅',
                'completed' => '🎉',
                'cancelled' => '❌'
              ];
              $icon = $status_icons[$status_lower] ?? '📦';
            ?>
              <tr>
                <td><strong>#<?= htmlspecialchars($order['order_id']) ?></strong></td>
                <td><?= date('F d, Y', strtotime($order['order_date'])) ?></td>
                <td><?= date('h:i A', strtotime($order['order_time'])) ?></td>
                <td><strong>₱<?= number_format($order['total_amount'], 2) ?></strong></td>
                <td>
                  <span class="status-badge status-<?= $status_lower ?>">
                    <span class="status-icon"><?= $icon ?></span>
                    <?= htmlspecialchars($status) ?>
                  </span>
                </td>
                <td>
                  <a href="view_order.php?id=<?= $order['order_id'] ?>" class="view-btn">View Details</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px; font-size: 13px;">
          <strong>Status Guide:</strong><br>
          <span>➤ ⏳ <strong>Pending</strong> - Order received</span> <br>
          <span>➤ 👨‍🍳 <strong>Preparing</strong> - Being prepared</span> <br>
          <span>➤ ✅ <strong>Ready</strong> - Ready for pickup/delivery</span> <br>
          <span>➤ 🎉 <strong>Completed</strong> - Order delivered</span> <br>
          <span>➤ ❌ <strong>Cancelled</strong> - Order cancelled</span>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <p>You haven't placed any orders yet</p>
          <a href="menu.php" class="view-btn">Start Shopping</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- Reservations Section -->
    <div class="profile-section">
      <h2 class="section-title">My Reservations</h2>
      
      <?php if ($reservations_result && $reservations_result->num_rows > 0): ?>
        <div class="reservation-tabs">
          <button class="tab-btn active" onclick="showTab('current')">Current Reservations</button>
          <button class="tab-btn" onclick="showTab('history')">History</button>
        </div>
        
        <div id="current-tab" class="tab-content active">
          <table class="reservations-table">
            <thead>
              <tr>
                <th>Reservation ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Table/Room</th>
                <th>Event Type</th>
                <th>Status</th>
                <th>Total Price</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $has_current = false;
              $reservations_result->data_seek(0);
              while ($res = $reservations_result->fetch_assoc()): 
                $reservation_date = strtotime($res['reservation_date']);
                $today = strtotime(date('Y-m-d'));
                
                if ($reservation_date >= $today || $res['status'] === 'Pending' || $res['status'] === 'Confirmed'):
                  $has_current = true;
              ?>
                <tr>
                  <td>#<?= htmlspecialchars($res['reservation_id']) ?></td>
                  <td><?= date('M d, Y', strtotime($res['reservation_date'])) ?></td>
                  <td><?= date('h:i A', strtotime($res['reservation_time'])) ?></td>
                  <td><?= htmlspecialchars($res['table_number']) ?> (<?= htmlspecialchars($res['table_type']) ?>)</td>
                  <td><?= htmlspecialchars($res['event_type']) ?></td>
                  <td>
                    <span class="status-badge status-<?= strtolower($res['status']) ?>">
                      <?= htmlspecialchars($res['status']) ?>
                    </span>
                  </td>
                  <td>
                    <?= $res['total_price'] > 0 ? '₱' . number_format($res['total_price'], 2) : 'Free' ?>
                  </td>
                  <td>
                    <a href="view_reservation.php?id=<?= $res['reservation_id'] ?>" class="view-btn">View Details</a>
                  </td>
                </tr>
              <?php 
                endif;
              endwhile; 
              
              if (!$has_current):
              ?>
                <tr>
                  <td colspan="8" class="empty-state">
                    <p>No current reservations</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        
        <div id="history-tab" class="tab-content">
          <table class="reservations-table">
            <thead>
              <tr>
                <th>Reservation ID</th>
                <th>Date</th>
                <th>Time</th>
                <th>Table/Room</th>
                <th>Event Type</th>
                <th>Status</th>
                <th>Total Price</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $has_history = false;
              $reservations_result->data_seek(0);
              while ($res = $reservations_result->fetch_assoc()): 
                $reservation_date = strtotime($res['reservation_date']);
                $today = strtotime(date('Y-m-d'));
                
                if ($reservation_date < $today && $res['status'] !== 'Pending' && $res['status'] !== 'Confirmed' || 
                    $res['status'] === 'Cancelled' || $res['status'] === 'Completed'):
                  $has_history = true;
              ?>
                <tr>
                  <td>#<?= htmlspecialchars($res['reservation_id']) ?></td>
                  <td><?= date('M d, Y', strtotime($res['reservation_date'])) ?></td>
                  <td><?= date('h:i A', strtotime($res['reservation_time'])) ?></td>
                  <td><?= htmlspecialchars($res['table_number']) ?> (<?= htmlspecialchars($res['table_type']) ?>)</td>
                  <td><?= htmlspecialchars($res['event_type']) ?></td>
                  <td>
                    <span class="status-badge status-<?= strtolower($res['status']) ?>">
                      <?= htmlspecialchars($res['status']) ?>
                    </span>
                  </td>
                  <td>
                    <?= $res['total_price'] > 0 ? '₱' . number_format($res['total_price'], 2) : 'Free' ?>
                  </td>
                  <td>
                    <a href="view_reservation.php?id=<?= $res['reservation_id'] ?>" class="view-btn">View Details</a>
                  </td>
                </tr>
              <?php 
                endif;
              endwhile; 
              
              if (!$has_history):
              ?>
                <tr>
                  <td colspan="8" class="empty-state">
                    <p>No reservation history</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <p>You haven't made any reservations yet</p><br>
          <a href="reservation.php" class="view-btn">Make a Reservation</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <section class="banner">
    <img src="pictures/bg.jpg" alt="bg Kyla's Bistro" />
  </section>

  <script>
    function showTab(tab) {
      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
      });
      
      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      
      if (tab === 'current') {
        document.getElementById('current-tab').classList.add('active');
        document.querySelectorAll('.tab-btn')[0].classList.add('active');
      } else {
        document.getElementById('history-tab').classList.add('active');
        document.querySelectorAll('.tab-btn')[1].classList.add('active');
      }
    }
  </script>
</body>
</html>