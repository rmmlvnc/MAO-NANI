<?php
session_start();
include 'database.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

// Get filter period
$period = isset($_GET['period']) ? $_GET['period'] : 'all';

// Determine date condition based on period
$date_condition = "";
switch($period) {
  case 'today':
    $date_condition = "AND DATE(o.order_date) = CURDATE()";
    break;
  case 'week':
    $date_condition = "AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    break;
  case 'month':
    $date_condition = "AND MONTH(o.order_date) = MONTH(CURDATE()) AND YEAR(o.order_date) = YEAR(CURDATE())";
    break;
  case 'year':
    $date_condition = "AND YEAR(o.order_date) = YEAR(CURDATE())";
    break;
  default:
    $date_condition = "";
}

// Core metrics
// Core metrics with date filter
$sales_result = $conn->query("SELECT SUM(total_amount) AS total_sales FROM orders o WHERE 1=1 $date_condition");
$orders_result = $conn->query("SELECT COUNT(*) AS total_orders FROM orders o WHERE 1=1 $date_condition");
$paid_result = $conn->query("SELECT COUNT(*) AS paid_orders FROM payment p JOIN orders o ON p.order_id = o.order_id WHERE p.payment_status = 'Paid' $date_condition");
$unpaid_result = $conn->query("SELECT COUNT(*) AS unpaid_orders FROM orders o LEFT JOIN payment p ON o.order_id = p.order_id WHERE (p.payment_status IS NULL OR p.payment_status = 'Pending') $date_condition");
$average_result = $conn->query("SELECT AVG(total_amount) AS avg_order FROM orders o WHERE 1=1 $date_condition");
$customer_result = $conn->query("SELECT COUNT(*) AS total_customers FROM customer");
$popular_method_result = $conn->query("SELECT payment_method, COUNT(*) AS count FROM payment p JOIN orders o ON p.order_id = o.order_id WHERE 1=1 $date_condition GROUP BY payment_method ORDER BY count DESC LIMIT 1");

// Top products with date filter
$top_products = $conn->query("SELECT p.product_name, SUM(oi.quantity) as total_qty FROM order_item oi JOIN product p ON oi.product_id = p.product_id JOIN orders o ON oi.order_id = o.order_id WHERE 1=1 $date_condition GROUP BY oi.product_id ORDER BY total_qty DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Recent orders with date filter
$recent_orders = $conn->query("SELECT o.order_id, c.first_name, c.last_name, o.total_amount, o.order_date, p.payment_status FROM orders o JOIN customer c ON o.customer_id = c.customer_id LEFT JOIN payment p ON o.order_id = p.order_id WHERE 1=1 $date_condition ORDER BY o.order_date DESC, o.order_time DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

// Payment methods breakdown with date filter
$payment_breakdown = $conn->query("SELECT payment_method, COUNT(*) as count FROM payment p JOIN orders o ON p.order_id = o.order_id WHERE 1=1 $date_condition GROUP BY payment_method")->fetch_all(MYSQLI_ASSOC);

// Extract values
$sales = $sales_result->fetch_assoc()['total_sales'] ?? 0;
$orders = $orders_result->fetch_assoc()['total_orders'] ?? 0;
$paid = $paid_result->fetch_assoc()['paid_orders'] ?? 0;
$unpaid = $unpaid_result->fetch_assoc()['unpaid_orders'] ?? 0;
$average = $average_result->fetch_assoc()['avg_order'] ?? 0;
$customers = $customer_result->fetch_assoc()['total_customers'] ?? 0;
$popular_method = $popular_method_result->fetch_assoc()['payment_method'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Business Reports - Neo Bistro Admin</title>
  <link rel="stylesheet" href="theme.css" />
  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #1e1e2f;
      color: #fefefe;
    }
    main {
      max-width: 1200px;
      margin: auto;
      padding: 2rem;
    }
    h2 {
      margin-bottom: 2rem;
      color: #fefefe;
    }
    
    /* Filter Section */
    .filter-section {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2rem;
      flex-wrap: wrap;
      gap: 1rem;
      background: #2c2c3c;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .filter-section h3 {
      margin: 0;
      color: #fefefe;
      font-size: 1.2rem;
    }
    .filter-buttons {
      display: flex;
      gap: 0.5rem;
      flex-wrap: wrap;
    }
    .filter-btn {
      padding: 0.6rem 1.2rem;
      background: #1e1e2f;
      color: #fefefe;
      border: 2px solid #333;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.2s ease;
      cursor: pointer;
    }
    .filter-btn:hover {
      background: #252538;
      border-color: #1dd1a1;
    }
    .filter-btn.active {
      background: #1dd1a1;
      color: #1e1e2f;
      border-color: #1dd1a1;
    }
    
    /* Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 1.5rem;
      margin-bottom: 3rem;
    }
    .stat-card {
      background: #2c2c3c;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      transition: transform 0.2s ease;
    }
    .stat-card:hover {
      transform: translateY(-5px);
    }
    .stat-icon {
      font-size: 2rem;
      margin-bottom: 0.5rem;
    }
    .stat-label {
      font-size: 0.9rem;
      color: #aaa;
      margin-bottom: 0.5rem;
    }
    .stat-value {
      font-size: 1.8rem;
      font-weight: bold;
      color: #1dd1a1;
      margin: 0;
    }
    .stat-card.red .stat-value {
      color: #ff6b6b;
    }
    .stat-card.blue .stat-value {
      color: #5f99f8;
    }
    .stat-card.yellow .stat-value {
      color: #feca57;
    }
    
    /* Top Products Section */
    .products-section {
      background: #2c2c3c;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      margin-bottom: 2rem;
    }
    .products-section h3 {
      margin-top: 0;
      margin-bottom: 1.5rem;
      color: #fefefe;
    }
    .product-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .product-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      margin-bottom: 0.75rem;
      background: #1e1e2f;
      border-radius: 8px;
      transition: background 0.2s ease;
    }
    .product-item:hover {
      background: #252538;
    }
    .product-name {
      font-weight: 500;
      color: #fefefe;
    }
    .product-qty {
      font-weight: bold;
      color: #1dd1a1;
    }
    .empty-state {
      text-align: center;
      padding: 2rem;
      color: #888;
    }
    
    /* Info Grid */
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
      gap: 2rem;
    }
    
    /* Recent Orders Table */
    .orders-table {
      width: 100%;
      border-collapse: collapse;
    }
    .orders-table th {
      text-align: left;
      padding: 0.75rem;
      background: #1e1e2f;
      color: #aaa;
      font-size: 0.85rem;
      font-weight: 600;
      border-bottom: 2px solid #333;
    }
    .orders-table td {
      padding: 0.75rem;
      border-bottom: 1px solid #333;
      color: #fefefe;
    }
    .orders-table tr:hover td {
      background: #1e1e2f;
    }
    .status-paid {
      color: #1dd1a1;
      font-weight: 600;
    }
    .status-pending {
      color: #ff6b6b;
      font-weight: 600;
    }
    
    /* Payment Methods */
    .payment-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem;
      margin-bottom: 0.75rem;
      background: #1e1e2f;
      border-radius: 8px;
    }
    .payment-method {
      font-weight: 500;
      color: #fefefe;
    }
    .payment-count {
      font-weight: bold;
      color: #5f99f8;
    }
    
    @media screen and (max-width: 768px) {
      main {
        padding: 1rem;
      }
      .filter-section {
        padding: 1rem;
      }
      .filter-section h3 {
        font-size: 1rem;
      }
      .filter-buttons {
        width: 100%;
      }
      .filter-btn {
        flex: 1;
        text-align: center;
        padding: 0.5rem;
        font-size: 0.85rem;
      }
      .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
      }
      .stat-card {
        padding: 1rem;
      }
      .stat-value {
        font-size: 1.5rem;
      }
      .info-grid {
        grid-template-columns: 1fr;
      }
      .orders-table {
        font-size: 0.85rem;
      }
      .orders-table th,
      .orders-table td {
        padding: 0.5rem;
      }
    }
  </style>
</head>
<body>
  <?php include 'admin_nav.php'; ?>

  <main>
    <h2>📊 Business Reports</h2>

    <!-- Filter Section -->
    <div class="filter-section">
      <h3>📅 Filter by Period</h3>
      <div class="filter-buttons">
        <a href="?period=all" class="filter-btn <?= $period === 'all' ? 'active' : '' ?>">All Time</a>
        <a href="?period=today" class="filter-btn <?= $period === 'today' ? 'active' : '' ?>">Today</a>
        <a href="?period=week" class="filter-btn <?= $period === 'week' ? 'active' : '' ?>">This Week</a>
        <a href="?period=month" class="filter-btn <?= $period === 'month' ? 'active' : '' ?>">This Month</a>
        <a href="?period=year" class="filter-btn <?= $period === 'year' ? 'active' : '' ?>">This Year</a>
      </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-label">Total Sales</div>
        <p class="stat-value">₱<?= number_format($sales, 2) ?></p>
      </div>
      
      <div class="stat-card blue">
        <div class="stat-icon">🛒</div>
        <div class="stat-label">Total Orders</div>
        <p class="stat-value"><?= $orders ?></p>
      </div>
      
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-label">Paid Orders</div>
        <p class="stat-value"><?= $paid ?></p>
      </div>
      
      <div class="stat-card red">
        <div class="stat-icon">⏳</div>
        <div class="stat-label">Unpaid Orders</div>
        <p class="stat-value"><?= $unpaid ?></p>
      </div>
      
      <div class="stat-card yellow">
        <div class="stat-icon">📈</div>
        <div class="stat-label">Average Order</div>
        <p class="stat-value">₱<?= number_format($average, 2) ?></p>
      </div>
      
      <div class="stat-card blue">
        <div class="stat-icon">👥</div>
        <div class="stat-label">Total Customers</div>
        <p class="stat-value"><?= $customers ?></p>
      </div>
      
      <div class="stat-card yellow">
        <div class="stat-icon">💳</div>
        <div class="stat-label">Top Payment Method</div>
        <p class="stat-value" style="font-size: 1.2rem;"><?= htmlspecialchars($popular_method) ?></p>
      </div>
    </div>

    <!-- Top Products -->
    <div class="products-section">
      <h3>🔥 Top Selling Products</h3>
      <?php if (empty($top_products)): ?>
        <div class="empty-state">No product sales data available</div>
      <?php else: ?>
        <ul class="product-list">
          <?php foreach ($top_products as $product): ?>
            <li class="product-item">
              <span class="product-name"><?= htmlspecialchars($product['product_name']) ?></span>
              <span class="product-qty"><?= $product['total_qty'] ?> sold</span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- Additional Info Grid -->
    <div class="info-grid">
      <!-- Recent Orders -->
      <div class="products-section">
        <h3>📋 Recent Orders</h3>
        <?php if (empty($recent_orders)): ?>
          <div class="empty-state">No recent orders</div>
        <?php else: ?>
          <table class="orders-table">
            <thead>
              <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($recent_orders as $order): ?>
                <tr>
                  <td>#<?= $order['order_id'] ?></td>
                  <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                  <td>₱<?= number_format($order['total_amount'], 2) ?></td>
                  <td>
                    <span class="<?= $order['payment_status'] === 'Paid' ? 'status-paid' : 'status-pending' ?>">
                      <?= $order['payment_status'] ?? 'Pending' ?>
                    </span>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <!-- Payment Methods -->
      <div class="products-section">
        <h3>💳 Payment Methods</h3>
        <?php if (empty($payment_breakdown)): ?>
          <div class="empty-state">No payment data</div>
        <?php else: ?>
          <div>
            <?php foreach ($payment_breakdown as $payment): ?>
              <div class="payment-item">
                <span class="payment-method"><?= htmlspecialchars($payment['payment_method']) ?></span>
                <span class="payment-count"><?= $payment['count'] ?> orders</span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>