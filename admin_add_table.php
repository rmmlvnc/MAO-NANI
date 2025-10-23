<?php
session_start();
include 'database.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = $_POST['table_name'];
  $number = $_POST['table_number'];
  $capacity = $_POST['capacity'];
  $type = $_POST['table_type'];
  $description = $_POST['description'];
  $price = $_POST['price_per_hour'];
  $status = $_POST['status'];

  $stmt = $conn->prepare("
    INSERT INTO tables (table_name, table_number, capacity, table_type, description, price_per_hour, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->bind_param("siissds", $name, $number, $capacity, $type, $description, $price, $status);
  $stmt->execute();
  header("Location: admin_tables.php");
  exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Table - Neo Bistro Admin</title>
  <link rel="stylesheet" href="theme.css" />
  <style>
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #1e1e2f;
      color: #fefefe;
    }
    main {
      max-width: 600px;
      margin: auto;
      padding: 2rem;
    }
    h2 {
      margin-bottom: 1rem;
      color: #fefefe;
    }
    form.add-form {
      background: #2c2c3c;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      display: grid;
      gap: 1rem;
    }
    form.add-form input, form.add-form select, form.add-form button {
      padding: 0.6rem;
      border-radius: 6px;
      border: none;
      font-size: 1rem;
    }
    form.add-form input, form.add-form select {
      background: #1e1e2f;
      color: #fefefe;
      border: 1px solid #555;
    }
    form.add-form button {
      background: #1dd1a1;
      color: white;
      cursor: pointer;
      transition: background 0.2s ease;
    }
    form.add-form button:hover {
      background: #10ac84;
    }
    .cancel-btn {
        background: #ff6b6b;
        color: white;
        padding: 0.6rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        display: inline-block;
        text-align: center;
    }

  </style>
</head>
<body>
  <?php include 'admin_nav.php'; ?>

  <main>
    <h2>➕ Add New Table</h2>
    <form method="POST" class="add-form">
        <input type="text" name="table_name" placeholder="Table Name" required />
        <input type="number" name="table_number" placeholder="Table Number" required />
        <input type="number" name="capacity" placeholder="Capacity" required />
        <select name="table_type" required>
            <option value="Regular">Regular</option>
            <option value="Regular (Outside)">Regular (Outside)</option>
            <option value="Regular (Inside)">Regular (Inside)</option>
            <option value="Birthday Party Room">Birthday Party Room</option>
            <option value="Meeting Room">Meeting Room</option>
        </select>
        <textarea name="description" placeholder="Description" rows="3"></textarea>
        <input type="number" step="0.01" name="price_per_hour" placeholder="Price per Hour" required />
        <select name="status" required>
            <option value="Available">Available</option>
            <option value="Occupied">Occupied</option>
            <option value="Reserved">Reserved</option>
        </select>
        <div style="display: flex; gap: 1rem;">
            <button type="submit">Add Table</button>
            <a href="admin_tables.php" class="cancel-btn">Cancel</a>
        </div>
    </form>
  </main>
</body>
</html>
