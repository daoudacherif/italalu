<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifier si l'admin est connecté
if (strlen($_SESSION['imsaid']) == 0) {
  header('location:logout.php');
  exit;
}

$ret = mysqli_query($con, "
    SELECT 
      tblproducts.ID AS pid,
      tblproducts.ProductName,
      tblproducts.BrandName,
      tblproducts.ModelNumber,
      tblproducts.Stock,
      tblproducts.Status,
      tblproducts.CreationDate,
      tblcategory.CategoryName,
      tblsubcategory.SubCategoryname AS subcat
    FROM tblproducts
    INNER JOIN tblcategory ON tblcategory.ID = tblproducts.CatID
    INNER JOIN tblsubcategory ON tblsubcategory.ID = tblproducts.SubcatID
    ORDER BY tblproducts.ID DESC
");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Système de Gestion des Stocks || Gérer les Produits</title>
  <?php include_once('includes/cs.php'); ?>
</head>
<body>

<?php include_once('includes/header.php'); ?>
<?php include_once('includes/sidebar.php'); ?>

<div id="content">
  <div id="content-header">
    <div id="breadcrumb">
      <a href="dashboard.php" title="Aller à l'accueil" class="tip-bottom">
        <i class="icon-home"></i> Accueil
      </a>
      <a href="manage-product.php" class="tip-bottom">Gérer les Produits</a>
    </div>
    <h1>Gérer les Produits</h1>
  </div>
  <div class="container-fluid">
    <hr>
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title">
            <span class="icon"><i class="icon-th"></i></span>
            <h5>Gérer les Produits</h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered data-table">
              <thead>
                <tr>
                  <th>N°</th>
                  <th>Nom du Produit</th>
                  <th>Nom de la Catégorie</th>
                  <th>Nom de la Sous-Catégorie</th>
                  <th>Nom de la Marque</th>
                  <th>Numéro de Modèle</th>
                  <th>Stock</th>
                  <th>Statut</th>
                  <th>Date de Création</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $cnt = 1;
                while ($row = mysqli_fetch_assoc($ret)) {
                  ?>
                  <tr class="gradeX">
                    <td><?php echo $cnt; ?></td>
                    <td><?php echo $row['ProductName']; ?></td>
                    <td><?php echo $row['CategoryName']; ?></td>
                    <td><?php echo $row['subcat']; ?></td>
                    <td><?php echo $row['BrandName']; ?></td>
                    <td><?php echo $row['ModelNumber']; ?></td>
                    <td><?php echo $row['Stock']; ?></td>
                    <td><?php echo ($row['Status'] == "1") ? "Actif" : "Inactif"; ?></td>
                    <td><?php echo $row['CreationDate']; ?></td>
                    <td class="center">
                      <a href="editproducts.php?editid=<?php echo $row['pid']; ?>">
                        <i class="icon-edit"></i>
                      </a>
                    </td>
                  </tr>
                  <?php 
                  $cnt++;
                }
                ?>
              </tbody>
            </table>
          </div><!-- widget-content nopadding -->
        </div><!-- widget-box -->
      </div><!-- span12 -->
    </div><!-- row-fluid -->
  </div><!-- container-fluid -->
</div><!-- content -->

<?php include_once('includes/footer.php'); ?>
<script src="js/jquery.min.js"></script> 
<script src="js/jquery.ui.custom.js"></script> 
<script src="js/bootstrap.min.js"></script> 
<script src="js/jquery.uniform.js"></script> 
<script src="js/select2.min.js"></script> 
<script src="js/jquery.dataTables.min.js"></script> 
<script src="js/matrix.js"></script> 
<script src="js/matrix.tables.js"></script>
</body>
</html>
