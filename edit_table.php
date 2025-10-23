<?php
session_start();
include 'database.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

$table_id = $_GET['id'] ?? null;
if (!$table_id) {
  header("Location: admin_tables.php");
  exit();
}

// Fetch existing table data
$stmt = $conn->prepare("SELECT table_number, capacity, status, table_type FROM tables WHERE table_id = ?");
$stmt->bind_param("i", $table_id);
$stmt->execute();
$table = $stmt->get_result()->fetch_assoc();

if (!$table) {
  echo "<p style='color:white; padding:2rem;'>Table not found.</p>";
  exit();
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $number = $_POST['table_number'];
  $capacity = $_POST['capacity'];
  $status = $_POST['status'];
  $type = $_POST['table_type'];

  $update = $conn->prepare("UPDATE tables SET table_number = ?, capacity = ?, status = ?, table_type = ? WHERE table_id = ?");
  $update->bind_param("iissi", $number, $capacity, $status, $type, $table_id);
  $update->execute();
  header("Location: admin_tables.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Edit Table - Neo Bistro Admin</title>
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
    form.edit-form {
      background: #2c2c3c;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
      display: grid;
      gap: 1rem;
    }
    form.edit-form input, form.edit-form select, form.edit-form button {
      padding: 0.6rem;
      border-radius: 6px;
      border: none;
      font-size: 1rem;
    }
    form.edit-form input, form.edit-form select {
      background: #1e1e2f;
      color: #fefefe;
      border: 1px solid #555;
    }
    form.edit-form button {
      background: #1dd1a1;
      color: white;
      cursor: pointer;
      transition: background 0.2s ease;
    }
    form.edit-form button:hover {
      background: #10ac84;
    }
  </style>
</head>
<body>
  <?php include 'admin_nav.php'; ?>

  <main>
    <h2>✏️ Edit Table #<?= htmlspecialchars($table['table_number']) ?></h2>
    <form method="POST" class="edit-form">
      <input type="number" name="table_number" value="<?= $table['table_number'] ?>" required />
      <input type="number" name="capacity" value="<?= $table['capacity'] ?>" required />
      <select name="status" required>
        <option value="Available" <?= $table['status'] === 'Available' ? 'selected' : '' ?>>Available</option>
        <option value="Occupied" <?= $table['status'] === 'Occupied' ? 'selected' : '' ?>>Occupied</option>
        <option value="Reserved" <?= $table['status'] === 'Reserved' ? 'selected' : '' ?>>Reserved</option>
      </select>
      <select name="table_type" required>
        <option value="Regular" <?= $table['table_type'] === 'Regular' ? 'selected' : '' ?>>Regular</option>
        <option value="Regular (Outside)" <?= $table['table_type'] === 'Regular (Outside)' ? 'selected' : '' ?>>Regular (Outside)</option>
        <option value="Regular (Inside)" <?= $table['table_type'] === 'Regular (Inside)' ? 'selected' : '' ?>>Regular (Inside)</option>
        <option value="Birthday Party Room" <?= $table['table_type'] === 'Birthday Party Room' ? 'selected' : '' ?>>Birthday Party Room</option>
        <option value="Meeting Room" <?= $table['table_type'] === 'Meeting Room' ? 'selected' : '' ?>>Meeting Room</option>
      </select>
      <button type="submit">💾 Save Changes</button>
    </form>
  </main>
</body>
</html>
