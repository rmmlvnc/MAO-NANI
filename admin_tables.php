<?php
session_start();
include 'database.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

// Fetch tables
$table_result = $conn->query("SELECT table_id, table_number, capacity, status, table_type FROM tables ORDER BY table_number");
$tables = $table_result ? $table_result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Table Management - Neo Bistro Admin</title>
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
    .table-section {
      background: #2c2c3c;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }
    .add-btn {
      display: inline-block;
      background: #1dd1a1;
      color: white;
      padding: 0.6rem 1rem;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      margin-bottom: 1rem;
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
    .status-available { color: #1dd1a1; font-weight: bold; }
    .status-occupied { color: #ff6b6b; font-weight: bold; }
    .status-reserved { color: #f7b731; font-weight: bold; }
    .edit-link {
      color: #1dd1a1;
      text-decoration: none;
      font-weight: 500;
    }
    .edit-link:hover {
      text-decoration: underline;
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
    <h2>🍽️ Manage Tables</h2>
    <a href="admin_add_table.php" class="add-btn">Add Table</a>

    <div class="table-section">
      <?php if (empty($tables)): ?>
        <p>No tables found.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Table #</th>
              <th>Capacity</th>
              <th>Status</th>
              <th>Type</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tables as $table): ?>
              <tr>
                <td data-label="Table #"><?= $table['table_number'] ?></td>
                <td data-label="Capacity"><?= $table['capacity'] ?></td>
                <td data-label="Status">
                  <span class="<?=
                    $table['status'] === 'Available' ? 'status-available' :
                    ($table['status'] === 'Occupied' ? 'status-occupied' : 'status-reserved')
                  ?>">
                    <?= $table['status'] ?>
                  </span>
                </td>
                <td data-label="Type"><?= htmlspecialchars($table['table_type']) ?></td>
                <td data-label="Actions">
                  <a class="edit-link" href="edit_table.php?id=<?= $table['table_id'] ?>">Edit</a>
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
