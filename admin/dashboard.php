<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['imsaid']) == 0) {
  header('location:logout.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tableau de Bord | Inventaire</title>
  <link rel="stylesheet" href="includes/cs.php">
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background-color: #f0f4f5;
      margin: 0;
      padding: 0;
    }

    .dashboard-container {
      display: flex;
    }

    .sidebar {
      width: 240px;
      background-color: #164e42;
      color: white;
      height: 100vh;
      display: flex;
      flex-direction: column;
      padding: 20px;
    }

    .sidebar h2 {
      color: white;
      margin-bottom: 30px;
    }

    .sidebar a {
      color: white;
      text-decoration: none;
      margin: 10px 0;
      display: flex;
      align-items: center;
      font-weight: 500;
    }

    .sidebar a:hover {
      background-color: #1e6355;
      padding: 10px;
      border-radius: 6px;
    }

    .main-content {
      flex: 1;
      padding: 30px;
    }

    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
      gap: 20px;
      margin-top: 30px;
    }

    .card {
      background-color: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .card h3 {
      margin-bottom: 10px;
    }

    .card span {
      font-size: 20px;
      font-weight: bold;
    }

    .sales-chart, .product-table {
      background-color: white;
      border-radius: 10px;
      margin-top: 30px;
      padding: 20px;
    }

    .product-table table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }

    .product-table th, .product-table td {
      text-align: left;
      padding: 10px;
      border-bottom: 1px solid #ddd;
    }

    .product-table th {
      background-color: #f5f5f5;
    }

    .highlight {
      color: #16a085;
      font-weight: bold;
    }
  </style>
</head>
<body>

<div class="dashboard-container">
  <!-- Sidebar -->
  <div class="sidebar">
    <h2>gk</h2>
    <a href="dashboard.php">🏠 Tableau de Bord</a>
    <a href="manage-category.php">📂 Catégories</a>
    <a href="manage-subcategory.php">📁 Sous-catégories</a>
    <a href="manage-brand.php">🏷️ Marques</a>
    <a href="manage-product.php">📦 Produits</a>
    <a href="inventory.php">📋 Inventaire</a>
    <a href="cart.php">💰 Comptant</a>
    <a href="dettecart.php">💳 Terme</a>
    <a href="client-account.php">👤 Clients</a>
    <a href="sales-report.php">📈 Rapports</a>
    <a href="settings.php">⚙️ Paramètres</a>
  </div>

  <!-- Main content -->
  <div class="main-content">
    <div class="dashboard-header">
      <h1>Tableau de Bord</h1>
      <div class="user-info">Admin connecté</div>
    </div>

    <div class="cards">
      <?php
        $totalBrands = mysqli_num_rows(mysqli_query($con, "SELECT * FROM tblbrand WHERE Status='1'"));
        $totalCategories = mysqli_num_rows(mysqli_query($con, "SELECT * FROM tblcategory WHERE Status='1'"));
        $totalSubcategories = mysqli_num_rows(mysqli_query($con, "SELECT * FROM tblsubcategory WHERE Status='1'"));
        $totalProducts = mysqli_num_rows(mysqli_query($con, "SELECT * FROM tblproducts"));
      ?>
      <div class="card">
        <h3>Marques</h3>
        <span class="highlight"><?php echo $totalBrands; ?></span>
      </div>
      <div class="card">
        <h3>Catégories</h3>
        <span class="highlight"><?php echo $totalCategories; ?></span>
      </div>
      <div class="card">
        <h3>Sous-catégories</h3>
        <span class="highlight"><?php echo $totalSubcategories; ?></span>
      </div>
      <div class="card">
        <h3>Produits</h3>
        <span class="highlight"><?php echo $totalProducts; ?></span>
      </div>
    </div>

    <!-- Ventes -->
    <div class="cards" style="margin-top: 40px;">
      <?php
        function getSales($condition) {
          global $con;
          $query = mysqli_query($con, "
            SELECT ProductQty, tblproducts.Price
            FROM tblcart 
            JOIN tblproducts ON tblproducts.ID = tblcart.ProductId 
            WHERE $condition AND IsCheckOut='1'
          ");
          $total = 0;
          while ($r = mysqli_fetch_assoc($query)) {
            $total += $r['ProductQty'] * $r['Price'];
          }
          return number_format($total, 2);
        }
      ?>
      <div class="card">
        <h3>Vente aujourd'hui</h3>
        <span>$<?php echo getSales("DATE(CartDate) = CURDATE()"); ?></span>
      </div>
      <div class="card">
        <h3>Vente hier</h3>
        <span>$<?php echo getSales("DATE(CartDate) = CURDATE()-1"); ?></span>
      </div>
      <div class="card">
        <h3>7 derniers jours</h3>
        <span>$<?php echo getSales("DATE(CartDate) >= DATE(NOW()) - INTERVAL 7 DAY"); ?></span>
      </div>
      <div class="card">
        <h3>Vente totale</h3>
        <span>$<?php echo getSales("1=1"); ?></span>
      </div>
    </div>
  </div>
</div>

</body>
</html>
