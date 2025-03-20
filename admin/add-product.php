<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifier si l'admin est connecté
if (strlen($_SESSION['imsaid'] == 0)) {
  header('location:logout.php');
  exit;
}

// 1) Traitement du formulaire
if (isset($_POST['submit'])) {
    $pname      = mysqli_real_escape_string($con, $_POST['pname']);
    $category   = intval($_POST['category']);      // ID de la catégorie
    $subcategory= intval($_POST['subcategory']);   // ID de la sous-catégorie
    $bname      = mysqli_real_escape_string($con, $_POST['bname']);
    $modelno    = mysqli_real_escape_string($con, $_POST['modelno']);
    $stock      = intval($_POST['stock']);
    $price      = floatval($_POST['price']);
    // Si la case est cochée => 1, sinon 0
    $status     = isset($_POST['status']) ? 1 : 0;

    // Insertion dans tblproducts (ou la table que tu utilises)
    $query = mysqli_query($con, "
      INSERT INTO tblproducts(
        ProductName, CatID, SubcatID, BrandName, ModelNumber, Stock, Price, Status
      ) VALUES(
        '$pname', '$category', '$subcategory', '$bname', '$modelno', '$stock', '$price', '$status'
      )
    ");
    if ($query) {
        echo '<script>alert("Le produit a été créé avec succès.")</script>';
    } else {
        echo '<script>alert("Une erreur est survenue. Veuillez réessayer.")</script>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Système de Gestion des Stocks | Ajouter un Produit</title>
  <?php include_once('includes/cs.php'); // styles, etc. ?>

  <!-- jQuery si besoin -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <script>
  // Fonction pour récupérer la liste des sous-catégories via AJAX
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
            <span class="icon"><i class="icon-align-justify"></i></span>
            <h5>Formulaire d'ajout</h5>
          </div>
          <div class="widget-content nopadding">
            <form method="post" class="form-horizontal">

              <!-- Nom du Produit -->
              <div class="control-group">
                <label class="control-label">Nom du Produit :</label>
                <div class="controls">
                  <input type="text" class="span11" name="pname" required placeholder="Nom du produit" />
                </div>
              </div>

              <!-- Catégorie -->
              <div class="control-group">
                <label class="control-label">Catégorie :</label>
                <div class="controls">
                  <select name="category" class="span11" onChange="getSubCat(this.value)" required>
                    <option value="">Sélectionnez une Catégorie</option>
                    <?php
                    // Charger les catégories actives
                    $catQuery = mysqli_query($con, "SELECT ID, CategoryName FROM tblcategory WHERE Status='1' ORDER BY CategoryName ASC");
                    while ($row = mysqli_fetch_assoc($catQuery)) {
                      echo '<option value="'.$row['ID'].'">'.$row['CategoryName'].'</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>

              <!-- Sous-catégorie -->
              <div class="control-group">
                <label class="control-label">Sous-Catégorie :</label>
                <div class="controls">
                  <select name="subcategory" id="subcategory" class="span11" required>
                    <option value="">Sélectionnez d'abord une catégorie</option>
                  </select>
                </div>
              </div>

              <!-- Marque -->
              <div class="control-group">
                <label class="control-label">Marque :</label>
                <div class="controls">
                  <select name="bname" class="span11" required>
                    <option value="">Sélectionnez une Marque</option>
                    <?php
                    // Charger les marques actives
                    $brandQuery = mysqli_query($con, "SELECT BrandName FROM tblbrand WHERE Status='1' ORDER BY BrandName ASC");
                    while ($brow = mysqli_fetch_assoc($brandQuery)) {
                      echo '<option value="'.$brow['BrandName'].'">'.$brow['BrandName'].'</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>

              <!-- Numéro de Modèle -->
              <div class="control-group">
                <label class="control-label">Numéro de Modèle :</label>
                <div class="controls">
                  <input type="text" class="span11" name="modelno" placeholder="Ex: ABC12" maxlength="20" required />
                </div>
              </div>

              <!-- Stock -->
              <div class="control-group">
                <label class="control-label">Stock (unités) :</label>
                <div class="controls">
                  <input type="number" class="span11" name="stock" min="0" placeholder="0" required />
                </div>
              </div>

              <!-- Prix -->
              <div class="control-group">
                <label class="control-label">Prix (par unité) :</label>
                <div class="controls">
                  <input type="number" step="any" class="span11" name="price" placeholder="0.00" required />
                </div>
              </div>

              <!-- Statut -->
              <div class="control-group">
                <label class="control-label">Activer ce produit ?</label>
                <div class="controls">
                  <input type="checkbox" name="status" value="1" /> (cocher pour Actif)
                </div>
              </div>

              <div class="form-actions">
                <button type="submit" class="btn btn-success" name="submit">Ajouter le produit</button>
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
