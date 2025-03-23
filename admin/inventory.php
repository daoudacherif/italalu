<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
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
                  <th>Nom de la Catégorie</th>
                  <th>Nom de la Sous-Catégorie</th>
                  <th>Nom de la Marque</th>
                  <th>Numéro de Modèle</th>
                  <!-- Retirer la colonne “Stock” -->
                  <th>Stock Restant</th>
                  <th>Statut</th>
                  <th>Action</th> <!-- pour montrer si on peut ajouter au panier -->
                </tr>
              </thead>
              <tbody>
                <?php
                // On calcule le stock restant = tblproducts.Stock - sum(tblcart.ProductQty)
                // On fait une LEFT JOIN sur tblcart pour connaître la quantité vendue
                $ret = mysqli_query($con, "
                  SELECT 
                    tblcategory.CategoryName,
                    tblsubcategory.SubCategoryname AS subcat,
                    tblproducts.ProductName,
                    tblproducts.BrandName,
                    tblproducts.ID AS pid,
                    tblproducts.Status,
                    tblproducts.CreationDate,
                    tblproducts.ModelNumber,
                    tblproducts.Stock,
                    SUM(tblcart.ProductQty) AS selledqty
                  FROM tblproducts
                  JOIN tblcategory ON tblcategory.ID = tblproducts.CatID
                  JOIN tblsubcategory ON tblsubcategory.ID = tblproducts.SubcatID
                  LEFT JOIN tblcart ON tblproducts.ID = tblcart.ProductId
                  GROUP BY tblproducts.ID
                  ORDER BY tblproducts.ID DESC
                ");

                $num = mysqli_num_rows($ret);
                if ($num > 0) {
                  $cnt = 1;
                  while ($row = mysqli_fetch_array($ret)) {
                    $qtySold = $row['selledqty'];
                    if (!$qtySold) {
                      $qtySold = 0; // si aucune vente
                    }
                    // Calcul du stock restant
                    $stockRemain = $row['Stock'] - $qtySold;
                    ?>
                    <tr class="gradeX">
                      <td><?php echo $cnt; ?></td>
                      <td><?php echo $row['ProductName']; ?></td>
                      <td><?php echo $row['CategoryName']; ?></td>
                      <td><?php echo $row['subcat']; ?></td>
                      <td><?php echo $row['BrandName']; ?></td>
                      <td><?php echo $row['ModelNumber']; ?></td>

                      <!-- Stock Restant -->
                      <td>
                        <?php
                        echo $stockRemain; 
                        ?>
                      </td>

                      <!-- Statut (Actif/Inactif) -->
                      <td>
                        <?php 
                        if ($row['Status'] == "1") {
                          echo "Actif";
                        } else {
                          echo "Inactif";
                        }
                        ?>
                      </td>

                      <!-- Action : on empêche l’ajout au panier si stock <= 0 -->
                      <td>
                        <?php
                        if ($stockRemain <= 0) {
                          echo "<span style='color:red;'>Stock épuisé</span>";
                        } else {
                          // Ex. un lien ou bouton pour ajouter au panier
                          echo "<a href='#' class='btn btn-mini btn-success'>Ajouter au panier</a>";
                        }
                        ?>
                      </td>
                    </tr>
                    <?php
                    $cnt++;
                  }
                } else {
                  ?>
                  <tr>
                    <td colspan="9" style="text-align:center;">Aucun enregistrement trouvé.</td>
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
<!--end-Footer-part-->

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
