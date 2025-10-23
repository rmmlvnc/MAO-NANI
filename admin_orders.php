<?php
session_start();
include 'database.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

$username = $_SESSION['admin'];
$stmt = $conn->prepare("SELECT first_name FROM admin WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$first_name = $admin ? $admin['first_name'] : 'Admin';

$order_result = $conn->query("
  SELECT o.order_id, o.customer_id, o.order_date, o.order_time, o.total_amount,
         c.first_name, c.last_name, c.phone_number,
         p.payment_status
  FROM orders o
  JOIN customer c ON o.customer_id = c.customer_id
  LEFT JOIN payment p ON o.order_id = p.order_id
  ORDER BY o.order_date DESC, o.order_time DESC
");

$orders = $order_result ? $order_result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Order Management - Neo Bistro Admin</title>
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
      margin-bottom: 1rem;
      color: #fefefe;
    }
    .orders-section {
      background: #2c2c3c;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      background: #1e1e2f;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    th, td {
      padding: 0.75rem;
      text-align: left;
      border-bottom: 1px solid #444;
    }
    th {
      background: #333;
      color: #fefefe;
    }
    tr:hover {
      background: #2a2a3a;
    }
    .status-paid {
      color: #1dd1a1;
      font-weight: bold;
    }
    .status-unpaid {
      color: #ff6b6b;
      font-weight: bold;
    }
    @media screen and (max-width: 768px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead {
        display: none;
      }
      tr {
        margin-bottom: 1rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        padding: 1rem;
        border-radius: 8px;
        background: #2c2c3c;
      }
      td {
        padding: 0.5rem 0;
        border: none;
      }
      td::before {
        content: attr(data-label);
        font-weight: bold;
        display: block;
        margin-bottom: 0.3rem;
        color: #aaa;
      }
    }
  </style>
</head>
<body>
  <?php include 'admin_nav.php'; ?>

  <main>
    <h2>📦 Order Management</h2>
    <div class="orders-section">
      <?php if (empty($orders)): ?>
        <p>No orders have been placed yet.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Contact</th>
              <th>Date</th>
              <th>Time</th>
              <th>Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td data-label="Order ID"><?= $order['order_id'] ?></td>
                <td data-label="Customer"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                <td data-label="Contact"><?= htmlspecialchars($order['phone_number']) ?></td>
                <td data-label="Date"><?= $order['order_date'] ?></td>
                <td data-label="Time"><?= $order['order_time'] ?></td>
                <td data-label="Total">₱<?= number_format($order['total_amount'], 2) ?></td>
                <td data-label="Status">
                  <span class="<?= $order['payment_status'] === 'Paid' ? 'status-paid' : 'status-unpaid' ?>">
                    <?= $order['payment_status'] ?? 'Unpaid' ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
