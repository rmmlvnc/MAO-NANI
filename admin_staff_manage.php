<?php
session_start();
include 'database.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit();
}

$staff_result = $conn->query("
  SELECT staff_id, first_name, middle_name, last_name, contact_number
  FROM staff
  ORDER BY first_name
");
$staff_list = $staff_result ? $staff_result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Staff Management - Neo Bistro Admin</title>
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
    .staff-section {
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
      margin-top: 1rem;
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
    a.action-link {
      color: #1dd1a1;
      text-decoration: none;
      font-weight: 500;
      margin-right: 0.5rem;
    }
    a.action-link:hover {
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
    <h2>👥 Manage Staff</h2>
    <div class="staff-section">
      <?php if (empty($staff_list)): ?>
        <p>No staff records found.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Contact</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($staff_list as $staff): ?>
              <tr>
                <td data-label="Name">
                  <?= htmlspecialchars($staff['first_name'] . ' ' . $staff['middle_name'] . ' ' . $staff['last_name']) ?>
                </td>
                <td data-label="Contact">
                  <a href="tel:<?= htmlspecialchars($staff['contact_number']) ?>" style="color:#fefefe;">
                    <?= htmlspecialchars($staff['contact_number']) ?>
                  </a>
                </td>
                <td data-label="Actions">
                  <a class="action-link" href="edit_staff.php?id=<?= $staff['staff_id'] ?>">Edit</a>
                  <a class="action-link" href="delete_staff.php?id=<?= $staff['staff_id'] ?>" onclick="return confirm('Delete this staff member?')">Delete</a>
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
