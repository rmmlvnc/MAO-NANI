<?php
session_start();
include 'database.php';

if (!isset($_SESSION['staff_id'])) {
  header("Location: staff_login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['order_status'])) {
  $order_id = $_POST['order_id'];
  $new_status = $_POST['order_status'];
  
  // Get the old status before updating
  $check_stmt = $conn->prepare("SELECT order_status FROM orders WHERE order_id = ?");
  $check_stmt->bind_param("i", $order_id);
  $check_stmt->execute();
  $result = $check_stmt->get_result();
  $old_order = $result->fetch_assoc();
  $old_status = $old_order['order_status'];
  $check_stmt->close();
  
  // Start transaction
  $conn->begin_transaction();
  
  try {
    // Update the order status
    $update_stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $update_stmt->bind_param("si", $new_status, $order_id);
    
    if (!$update_stmt->execute()) {
      throw new Exception("Failed to update order status");
    }
    $update_stmt->close();
    
    // If status changed to "Ready" and wasn't "Ready" before, deduct stock
    if ($new_status === 'Ready' && $old_status !== 'Ready') {
      // Fetch all order items for this order
      $items_stmt = $conn->prepare("
        SELECT product_id, quantity 
        FROM order_item 
        WHERE order_id = ?
      ");
      $items_stmt->bind_param("i", $order_id);
      $items_stmt->execute();
      $items_result = $items_stmt->get_result();
      
      // Update stock for each product
      while ($item = $items_result->fetch_assoc()) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        
        // Check if enough stock is available
        $stock_check = $conn->prepare("SELECT stock_quantity FROM product WHERE product_id = ?");
        $stock_check->bind_param("i", $product_id);
        $stock_check->execute();
        $stock_result = $stock_check->get_result();
        $product = $stock_result->fetch_assoc();
        $stock_check->close();
        
        if ($product['stock_quantity'] < $quantity) {
          throw new Exception("Insufficient stock for product ID: " . $product_id);
        }
        
        // Deduct stock quantity
        $stock_stmt = $conn->prepare("
          UPDATE product 
          SET stock_quantity = stock_quantity - ? 
          WHERE product_id = ?
        ");
        $stock_stmt->bind_param("ii", $quantity, $product_id);
        
        if (!$stock_stmt->execute()) {
          throw new Exception("Failed to update stock for product ID: " . $product_id);
        }
        $stock_stmt->close();
      }
      
      $items_stmt->close();
    }
    
    // If status changed FROM "Ready" to something else, restore stock
    if ($old_status === 'Ready' && $new_status !== 'Ready') {
      // Fetch all order items for this order
      $items_stmt = $conn->prepare("
        SELECT product_id, quantity 
        FROM order_item 
        WHERE order_id = ?
      ");
      $items_stmt->bind_param("i", $order_id);
      $items_stmt->execute();
      $items_result = $items_stmt->get_result();
      
      // Restore stock for each product
      while ($item = $items_result->fetch_assoc()) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        
        // Add back stock quantity
        $stock_stmt = $conn->prepare("
          UPDATE product 
          SET stock_quantity = stock_quantity + ? 
          WHERE product_id = ?
        ");
        $stock_stmt->bind_param("ii", $quantity, $product_id);
        
        if (!$stock_stmt->execute()) {
          throw new Exception("Failed to restore stock for product ID: " . $product_id);
        }
        $stock_stmt->close();
      }
      
      $items_stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    $_SESSION['status_message'] = "Order status updated to " . $new_status . " successfully!" . 
                                   ($new_status === 'Ready' ? " Stock quantities have been updated." : "");
    $_SESSION['status_type'] = "success";
    
  } catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $_SESSION['status_message'] = "Error: " . $e->getMessage();
    $_SESSION['status_type'] = "error";
  }
  
} else {
  $_SESSION['status_message'] = "Invalid request";
  $_SESSION['status_type'] = "error";
}

header("Location: staff.php");
exit();
?>