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
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$first_name = $admin ? $admin['first_name'] : 'Admin';

$payments = $conn->query("SELECT payment_id, order_id, payment_method, payment_status, payment_date FROM payment ORDER BY payment_date DESC")->fetch_all(MYSQLI_ASSOC);
$orders = $conn->query("
  SELECT o.order_id, o.customer_id, o.order_date, o.order_time, o.total_amount,
         c.first_name, c.last_name,
         p.payment_status
  FROM orders o
  JOIN customer c ON o.customer_id = c.customer_id
  LEFT JOIN payment p ON o.order_id = p.order_id
  ORDER BY o.order_date DESC, o.order_time DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Neo Bistro Admin Dashboard</title>
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
    h2, h3 {
      margin-top: 2rem;
      color: #fefefe;
    }
    .card-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 1.5rem;
      margin-top: 1rem;
    }
    .card {
      background: #2c2c3c;
      border-radius: 12px;
      padding: 1rem;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      transition: transform 0.2s ease;
      text-align: center;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .card h4 {
      margin-bottom: 0.5rem;
      color: #1dd1a1;
    }
    .card p {
      font-size: 0.9rem;
      color: #ccc;
    }
    .card a {
      display: inline-block;
      margin-top: 0.5rem;
      background: #ff6b6b;
      color: white;
      padding: 0.4rem 0.8rem;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 1rem;
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
    select, button {
      padding: 0.3rem 0.6rem;
      border-radius: 4px;
      font-size: 0.9rem;
    }
    select {
      background: #2c2c3c;
      color: #fefefe;
      border: 1px solid #555;
    }
    button {
      background: #ff6b6b;
      color: white;
      border: none;
      cursor: pointer;
    }
    button:hover {
      background: #ee5253;
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
    <h2>👋 Welcome, <?= htmlspecialchars($first_name) ?>!</h2>

    <section>
      <h3>Quick Access</h3>
      <div class="card-grid">
        <div class="card">
          <h4>Staff</h4>
          <p>Manage staff accounts and roles</p>
          <a href="admin_staff_manage.php">Manage</a>
        </div>
        <div class="card">
          <h4>Orders</h4>
          <p>View and track customer orders</p>
          <a href="admin_orders.php">View</a>
        </div>
        <div class="card">
          <h4>Tables</h4>
          <p>Update table availability</p>
          <a href="admin_tables.php">Edit</a>
        </div>
        <div class="card">
          <h4>Reports</h4>
          <p>Sales and performance insights</p>
          <a href="admin_reports.php">Analyze</a>
        </div>
      </div>
    </section>

    <section>
      <h3>Recent Payments</h3>
      <?php if (empty($payments)): ?>
        <p>No payments recorded yet.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Order</th>
              <th>Method</th>
              <th>Status</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($payments as $row): ?>
              <tr>
                <td data-label="ID"><?= $row['payment_id'] ?></td>
                <td data-label="Order"><?= $row['order_id'] ?></td>
                <td data-label="Method"><?= $row['payment_method'] ?></td>
                <td data-label="Status">
                  <span class="<?= $row['payment_status'] === 'Paid' ? 'status-paid' : 'status-unpaid' ?>">
                    <?= $row['payment_status'] ?>
                  </span>
                </td>
                <td data-label="Date"><?= $row['payment_date'] ?></td>
                <td data-label="Action">
                  <form method="POST" action="update_payment_status.php">
                    <input type="hidden" name="payment_id" value="<?= $row['payment_id'] ?>">
                    <select name="payment_status">
                      <option value="Pending" <?= $row['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                      <option value="Paid" <?= $row['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                    </select>
                    <button type="submit">Update</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <section>
      <h3>Recent Orders</h3>
      <?php if (empty($orders)): ?>
        <p>No orders placed yet.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Time</th>
              <th>Total</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td data-label="ID"><?= $order['order_id'] ?></td>
                <td data-label="Customer"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></                <td data-label="Customer"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
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
    </section>
  </main>
</body>
</html>
