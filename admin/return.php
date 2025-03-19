<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifier si l'admin est connecté
if (strlen($_SESSION['imsaid'] == 0)) {
  header('location:logout.php');
  exit;
}

// Tableau qui contiendra les produits du numéro de facture saisi
$productsList = [];

// ======================
// 1) RECHERCHE DE FACTURE (tblcustomer)
// ======================
$billingnumberSearch = '';
if (isset($_POST['searchInvoice'])) {
  // L'utilisateur a cliqué sur 'Rechercher'
  $billingnumberSearch = mysqli_real_escape_string($con, $_POST['billingnumberSearch']);

  // Requête pour trouver l'ID du client/facture dans tblcustomer
  $custQ = mysqli_query($con, "
    SELECT ID
    FROM tblcustomer
    WHERE BillingNumber = '$billingnumberSearch'
    LIMIT 1
  ");
  $custRow = mysqli_fetch_assoc($custQ);
  if ($custRow) {
    $customerID = $custRow['ID'];

    // Requête pour charger les produits de cette facture
    // On récupère ProductID + ProductName depuis tblcart + tblproducts
    // On suppose c.IsCheckOut=1 signifie “facture validée”
    $prodQ = mysqli_query($con, "
      SELECT DISTINCT p.ID, p.ProductName
      FROM tblcart c
      JOIN tblproducts p ON p.ID = c.ProductId
      WHERE c.BillingId = '$customerID'
        AND c.IsCheckOut = 1
    ");
    while ($rowP = mysqli_fetch_assoc($prodQ)) {
      $productsList[] = $rowP;
    }
  } else {
    // Aucune facture trouvée
    echo "<script>alert('Aucune facture trouvée pour ce numéro.');</script>";
  }
}

// ======================
// 2) ENREGISTREMENT DU RETOUR
// ======================
if (isset($_POST['submitReturn'])) {
  $billingNumber = mysqli_real_escape_string($con, $_POST['billingnumber']);
  $productID     = intval($_POST['productid']);
  $quantity      = intval($_POST['quantity']);
  $returnPrice   = floatval($_POST['price']);
  $returnDate    = $_POST['returndate'];
  $reason        = mysqli_real_escape_string($con, $_POST['reason']);

  // Validation
  if (empty($billingNumber) || $productID <= 0 || $quantity <= 0 || $returnPrice < 0) {
    echo "<script>alert('Données invalides. Vérifiez la facture, le produit, la quantité, le prix.');</script>";
  } else {
    // Insert into tblreturns
    $sqlInsert = "
      INSERT INTO tblreturns(
        BillingNumber,
        ReturnDate,
        ProductID,
        Quantity,
        Reason,
        ReturnPrice
      ) VALUES(
        '$billingNumber',
        '$returnDate',
        '$productID',
        '$quantity',
        '$reason',
        '$returnPrice'
      )
    ";
    $queryInsert = mysqli_query($con, $sqlInsert);

    if ($queryInsert) {
      // Mettre à jour le stock
      $sqlUpdate = "UPDATE tblproducts
                    SET Stock = Stock + $quantity
                    WHERE ID='$productID'";
      mysqli_query($con, $sqlUpdate);

      echo "<script>alert('Retour enregistré et stock mis à jour!');</script>";
    } else {
      echo "<script>alert('Erreur lors de l\\'insertion du retour.');</script>";
    }
  }
  // Refresh
  echo "<script>window.location.href='return.php'</script>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Gestion des retours</title>
  <?php include_once('includes/cs.php'); ?>
</head>
<body>

<!-- Header + Sidebar -->
<?php include_once('includes/header.php'); ?>
<?php include_once('includes/sidebar.php'); ?>

<div id="content">
  <div id="content-header">
    <h1>Gérer les retours de produits</h1>
  </div>

  <div class="container-fluid">
    <hr>

    <!-- Formulaire de recherche de la facture (BillingNumber) -->
    <form method="post" class="form-inline">
      <label>Numéro de facture :</label>
      <input type="text" name="billingnumberSearch" 
             value="<?php echo htmlspecialchars($billingnumberSearch); ?>" 
             placeholder="ex. 123456789" />
      <button type="submit" name="searchInvoice" class="btn btn-info">
        Rechercher
      </button>
    </form>

    <hr>

    <!-- Formulaire de retour (avec liste de produits) -->
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title">
            <span class="icon"><i class="icon-align-justify"></i></span>
            <h5>Ajouter un nouveau retour</h5>
          </div>
          <div class="widget-content nopadding">
            <form method="post" class="form-horizontal">

              <!-- On réutilise la facture qu'on vient de rechercher -->
              <div class="control-group">
                <label class="control-label">Numéro de facture :</label>
                <div class="controls">
                  <input type="text" name="billingnumber"
                         value="<?php echo htmlspecialchars($billingnumberSearch); ?>"
                         readonly />
                </div>
              </div>

              <!-- Return Date -->
              <div class="control-group">
                <label class="control-label">Date de retour :</label>
                <div class="controls">
                  <input type="date" name="returndate" value="<?php echo date('Y-m-d'); ?>" required />
                </div>
              </div>

              <!-- Product Selection (seuls les produits de la facture) -->
              <div class="control-group">
                <label class="control-label">Sélectionner un produit :</label>
                <div class="controls">
                  <select name="productid" required>
                    <option value="">-- Choisir un produit --</option>
                    <?php
                    // Affiche seulement les produits trouvés
                    foreach ($productsList as $prod) {
                      echo '<option value="'.$prod['ID'].'">'.$prod['ProductName'].'</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>

              <!-- Quantity -->
              <div class="control-group">
                <label class="control-label">Quantité retournée :</label>
                <div class="controls">
                  <input type="number" name="quantity" min="1" value="1" required />
                </div>
              </div>

              <!-- Price -->
              <div class="control-group">
                <label class="control-label">Prix :</label>
                <div class="controls">
                  <input type="number" name="price" step="any" min="0" value="0" required />
                </div>
              </div>

              <!-- Reason -->
              <div class="control-group">
                <label class="control-label">Raison (facultatif) :</label>
                <div class="controls">
                  <input type="text" name="reason" placeholder="ex. Défaut, Mauvaise taille..." />
                </div>
              </div>

              <div class="form-actions">
                <button type="submit" name="submitReturn" class="btn btn-success">
                  Enregistrer le retour
                </button>
              </div>
            </form>
          </div><!-- widget-content nopadding -->
        </div><!-- widget-box -->
      </div>
    </div><!-- row-fluid -->

    <hr>

    <!-- Liste des retours récents (facultatif) -->
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title">
            <span class="icon"><i class="icon-th"></i></span>
            <h5>Retours récents</h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered data-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Numéro de facture</th>
                  <th>Date de retour</th>
                  <th>Produit</th>
                  <th>Quantité</th>
                  <th>Prix</th>
                  <th>Raison</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $sqlReturns = "
                  SELECT r.ID as returnID,
                         r.BillingNumber,
                         r.ReturnDate,
                         r.Quantity,
                         r.Reason,
                         r.ReturnPrice,
                         p.ProductName
                  FROM tblreturns r
                  LEFT JOIN tblproducts p ON p.ID = r.ProductID
                  ORDER BY r.ID DESC
                  LIMIT 50
                ";
                $returnsQuery = mysqli_query($con, $sqlReturns);
                $cnt = 1;
                while ($row = mysqli_fetch_assoc($returnsQuery)) {
                  ?>
                  <tr>
                    <td><?php echo $cnt; ?></td>
                    <td><?php echo $row['BillingNumber']; ?></td>
                    <td><?php echo $row['ReturnDate']; ?></td>
                    <td><?php echo $row['ProductName']; ?></td>
                    <td><?php echo $row['Quantity']; ?></td>
                    <td><?php echo number_format($row['ReturnPrice'],2); ?></td>
                    <td><?php echo $row['Reason']; ?></td>
                  </tr>
                  <?php
                  $cnt++;
                }
                ?>
              </tbody>
            </table>
          </div><!-- widget-content nopadding -->
        </div><!-- widget-box -->
      </div>
    </div><!-- row-fluid -->

  </div><!-- container-fluid -->
</div><!-- content -->

<?php include_once('includes/footer.php'); ?>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
