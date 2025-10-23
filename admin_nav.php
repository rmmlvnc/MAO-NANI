<?php
if (!isset($_SESSION)) {
  session_start();
}

// Get admin's name from session
$admin_name = '';
if (isset($_SESSION['admin'])) {
  include_once 'database.php';
  $username = $_SESSION['admin'];
  $stmt = $conn->prepare("SELECT first_name, last_name FROM admin WHERE username = ?");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($admin = $result->fetch_assoc()) {
    $admin_name = $admin['first_name'] . ' ' . $admin['last_name'];
  }
  $stmt->close();
}
?>
<style>
  nav.admin-nav {
    background: #ff6f61;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: 'Quicksand', sans-serif;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    flex-wrap: wrap;
    gap: 1rem;
  }
  nav.admin-nav .nav-left {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  nav.admin-nav .nav-logo {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  }
  nav.admin-nav .nav-brand {
    font-size: 1.4rem;
    font-weight: bold;
    color: white;
  }
  nav.admin-nav .nav-center {
    display: flex;
    gap: 0.5rem;
    align-items: center;
  }
  nav.admin-nav .nav-center a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.2s ease;
  }
  nav.admin-nav .nav-center a:hover {
    background: rgba(255,255,255,0.2);
  }
  nav.admin-nav .nav-right {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
  nav.admin-nav .admin-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255,255,255,0.2);
    padding: 0.5rem 1rem;
    border-radius: 8px;
    color: white;
  }
  nav.admin-nav .admin-icon {
    font-size: 1.2rem;
  }
  nav.admin-nav .admin-name {
    font-weight: 600;
    font-size: 0.95rem;
  }
  nav.admin-nav .logout-btn {
    color: white;
    text-decoration: none;
    font-weight: 600;
    padding: 0.5rem 1.2rem;
    border-radius: 6px;
    background: rgba(0,0,0,0.2);
    transition: all 0.2s ease;
  }
  nav.admin-nav .logout-btn:hover {
    background: rgba(0,0,0,0.4);
  }
  
  @media screen and (max-width: 992px) {
    nav.admin-nav {
      padding: 1rem;
    }
    nav.admin-nav .nav-brand {
      font-size: 1.2rem;
    }
    nav.admin-nav .nav-center {
      order: 3;
      width: 100%;
      justify-content: center;
      flex-wrap: wrap;
    }
    nav.admin-nav .nav-center a {
      font-size: 0.9rem;
      padding: 0.4rem 0.8rem;
    }
  }
  
  @media screen and (max-width: 576px) {
    nav.admin-nav .nav-logo {
      width: 40px;
      height: 40px;
    }
    nav.admin-nav .nav-brand {
      font-size: 1rem;
    }
    nav.admin-nav .admin-info {
      padding: 0.4rem 0.8rem;
    }
    nav.admin-nav .admin-name {
      font-size: 0.85rem;
    }
  }
</style>

<nav class="admin-nav">
  <div class="nav-left">
    <img src="pictures/logo.jpg" alt="Kyla's Bistro Logo" class="nav-logo">
    <div class="nav-brand">Kyla's Bistro Admin</div>
  </div>
  
  <div class="nav-center">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="admin_orders.php">Orders</a>
    <a href="admin_staff_manage.php">Staff</a>
    <a href="admin_tables.php">Tables</a>
    <a href="admin_reports.php">Reports</a>
  </div>
  
  <div class="nav-right">
    <?php if ($admin_name): ?>
      <div class="admin-info">
        <span class="admin-icon">👤</span>
        <span class="admin-name"><?= htmlspecialchars($admin_name) ?></span>
      </div>
    <?php endif; ?>
    <a href="admin_logout.php" class="logout-btn">Logout</a>
  </div>
</nav>