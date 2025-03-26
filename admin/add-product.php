<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifier si l'admin est connecté
if (strlen($_SESSION['imsaid'] == 0)) {
  header('location:logout.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Système de Gestion d'Inventaire || Voir l'Inventaire des Produits</title>
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
      <strong>Voir l'Inventaire des Produits</strong>
    </div>
    <h1>Voir l'Inventaire des Produits</h1>
  </div>
  <div class="container-fluid">
    <hr>
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title">
            <span class="icon"><i class="icon-th"></i></span>
            <h5>Inventaire des Produits</h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered data-table">
              <thead>
                <tr>
                  <th>N°</th>
                  <th>Nom du Produit</th>
                  <th>Catégorie</th>
                  <th>Sous-Catégorie</th>
                  <th>Marque</th>
                  <th>Modèle</th>
                  <th>Stock Restant</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Utilisation de LEFT JOIN pour inclure tous les produits
                $sql = "
                  SELECT
                    p.ID as pid,
                    p.ProductName,
                    p.BrandName,
                    p.ModelNumber,
                    p.Stock,
                    p.Status,
                    IFNULL(c.CategoryName, 'Inconnue') as CategoryName,
                    IF(p.SubcatID IS NULL OR p.SubcatID = 0, 'Inconnue', IFNULL(sc.SubCategoryname, 'Inconnue')) as SubCategoryname,
                    SUM(cart.ProductQty) as selledqty
                  FROM tblproducts p
                  LEFT JOIN tblcategory c ON c.ID = p.CatID
                  LEFT JOIN tblsubcategory sc ON sc.ID = p.SubcatID
                  LEFT JOIN tblcart cart ON p.ID = cart.ProductId
                  GROUP BY p.ID
                  ORDER BY p.ID DESC
                ";
                $ret = mysqli_query($con, $sql);
                $num = mysqli_num_rows($ret);
                if ($num > 0) {
                  $cnt = 1;
                  while ($row = mysqli_fetch_assoc($ret)) {
                    // Si aucune vente, on définit selledqty à 0
                    $qtySold = $row['selledqty'];
                    if (!$qtySold) {
                      $qtySold = 0;
                    }
                    // Calcul du stock restant
                    $stockRemain = $row['Stock'] - $qtySold;
                    ?>
                    <tr class="gradeX">
                      <td><?php echo $cnt; ?></td>
                      <td><?php echo $row['ProductName']; ?></td>
                      <td><?php echo $row['CategoryName']; ?></td>
                      <td><?php echo $row['SubCategoryname']; ?></td>
                      <td><?php echo $row['BrandName']; ?></td>
                      <td><?php echo $row['ModelNumber']; ?></td>
                      <td><?php echo $stockRemain; ?></td>
                      <td><?php echo ($row['Status'] == "1") ? "Actif" : "Inactif"; ?></td>
                    </tr>
                    <?php
                    $cnt++;
                  }
                } else {
                  ?>
                  <tr>
                    <td colspan="8" style="text-align:center;">Aucun enregistrement trouvé.</td>
                  </tr>
                  <?php
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

<!-- Scripts -->
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
