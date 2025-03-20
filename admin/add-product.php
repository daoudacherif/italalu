<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifier si l'admin est connecté
if (strlen($_SESSION['imsaid'] == 0)) {
  header('location:logout.php');
  exit;
}

if (isset($_POST['submit'])) {
    $pname       = $_POST['pname'];
    $category    = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $bname       = $_POST['bname'];
    $modelno     = $_POST['modelno'];
    $stock       = $_POST['stock'];
    $price       = $_POST['price'];
    // Si la case "Statut" est cochée => 1, sinon 0 (ou on peut laisser 1 si on veut forcer)
    $status      = isset($_POST['status']) ? 1 : 0;

    $query = mysqli_query($con, "
      INSERT INTO tblproducts(
        ProductName, CatID, SubcatID, BrandName, ModelNumber, Stock, Price, Status
      ) VALUES(
        '$pname', '$category', '$subcategory', '$bname', '$modelno', '$stock', '$price', '$status'
      )
    ");
    if ($query) {
        echo '<script>alert("Le produit a été créé.")</script>';
    } else {
        echo '<script>alert("Quelque chose s\'est mal passé. Veuillez réessayer")</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Système de Gestion des Stocks || Ajouter des Produits</title>
  <?php include_once('includes/cs.php');?>

  <!-- jQuery (si pas déjà inclus) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
  // Fonction AJAX pour récupérer les sous-catégories
  function getSubCat(val) {
    $.ajax({
      type: "POST",
      url: "get-subcat.php",
      data: { catid: val },
      success: function(data) {
        $("#subcategory").html(data);
      }
    });
  }
  </script>
</head>
<body>

<!--Header-part-->
<?php include_once('includes/header.php');?>
<?php include_once('includes/sidebar.php');?>


<div id="content">
  <div id="content-header">
    <div id="breadcrumb">
      <a href="dashboard.php" title="Aller à l'accueil" class="tip-bottom">
        <i class="icon-home"></i> Accueil
      </a>
      <a href="add-product.php" class="tip-bottom">Ajouter un Produit</a>
    </div>
    <h1>Ajouter un Produit</h1>
  </div>
  <div class="container-fluid">
    <hr>
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title">
            <span class="icon"> <i class="icon-align-justify"></i> </span>
            <h5>Ajouter un Produit</h5>
          </div>
          <div class="widget-content nopadding">
            <form method="post" class="form-horizontal">
              <!-- Nom du Produit -->
              <div class="control-group">
                <label class="control-label">Nom du Produit :</label>
                <div class="controls">
                  <input type="text" class="span11" name="pname" required placeholder="Entrez le nom du produit" />
                </div>
              </div>

              <!-- Catégorie -->
              <div class="control-group">
                <label class="control-label">Catégorie :</label>
                <div class="controls">
                  <select class="span11" name="category" onChange="getSubCat(this.value)" required>
                    <option value="">Sélectionnez une Catégorie</option>
                    <?php
                    $query = mysqli_query($con, "SELECT * FROM tblcategory WHERE Status='1'");
                    while ($row = mysqli_fetch_array($query)) {
                      echo '<option value="'.$row['ID'].'">'.$row['CategoryName'].'</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>

              <!-- Sous-Catégorie -->
              <div class="control-group">
                <label class="control-label">Sous-Catégorie :</label>
                <div class="controls">
                  <select class="span11" name="subcategory" id="subcategory" required>
                    <option value="">Sélectionnez une Sous-Catégorie</option>
                  </select>
                </div>
              </div>

              <!-- Marque -->
              <div class="control-group">
                <label class="control-label">Marque :</label>
                <div class="controls">
                  <select class="span11" name="bname" required>
                    <option value="">Sélectionnez une Marque</option>
                    <?php
                    $query1 = mysqli_query($con, "SELECT * FROM tblbrand WHERE Status='1'");
                    while ($row1 = mysqli_fetch_array($query1)) {
                      echo '<option value="'.$row1['BrandName'].'">'.$row1['BrandName'].'</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>

              <!-- Numéro de Modèle -->
              <div class="control-group">
                <label class="control-label">Numéro de Modèle :</label>
                <div class="controls">
                  <input type="text" class="span11" name="modelno" required maxlength="20" placeholder="Ex: ABC12" />
                </div>
              </div>

              <!-- Stock -->
              <div class="control-group">
                <label class="control-label">Stock (unités) :</label>
                <div class="controls">
                  <input type="number" class="span11" name="stock" required placeholder="Entrez le stock" />
                </div>
              </div>

              <!-- Prix -->
              <div class="control-group">
                <label class="control-label">Prix (par unité) :</label>
                <div class="controls">
                  <input type="number" step="any" class="span11" name="price" required placeholder="Entrez le prix" />
                </div>
              </div>

              <!-- Statut -->
              <div class="control-group">
                <label class="control-label">Statut :</label>
                <div class="controls">
                  <input type="checkbox" name="status" value="1" />
                  (cocher pour Actif)
                </div>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn btn-success" name="submit">Ajouter</button>
              </div>
            </form>
          </div><!-- widget-content nopadding -->
        </div><!-- widget-box -->
      </div><!-- span12 -->
    </div><!-- row-fluid -->
  </div><!-- container-fluid -->
</div><!-- content -->

<?php include_once('includes/footer.php');?>
<?php include_once('includes/js.php');?>
</body>
</html>
