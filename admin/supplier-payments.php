<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

if (strlen($_SESSION['imsaid'] == 0)) {
  header('location:logout.php');
  exit;
}

// ==========================
// 1) Insertion d'un paiement
// ==========================
if (isset($_POST['submit'])) {
  $supplierID  = intval($_POST['supplierid']);
  $payDate     = $_POST['paydate'];
  $amount      = floatval($_POST['amount']);
  $comments    = mysqli_real_escape_string($con, $_POST['comments']);

  if ($supplierID <= 0 || $amount <= 0) {
    echo "<script>alert('Invalid data');</script>";
  } else {
    $sql = "
      INSERT INTO tblsupplierpayments(SupplierID, PaymentDate, Amount, Comments)
      VALUES('$supplierID', '$payDate', '$amount', '$comments')
    ";
    $res = mysqli_query($con, $sql);
    if ($res) {
      echo "<script>alert('Payment recorded !');</script>";
    } else {
      echo "<script>alert('Error inserting payment');</script>";
    }
  }
  echo "<script>window.location.href='supplier-payments.php'</script>";
  exit;
}

// ==========================
// 2) Filtre pour afficher le total pour un fournisseur
// ==========================
$selectedSupplier = 0;
$totalArrivals = 0;
$totalPaid     = 0;
$totalDue      = 0;

if (isset($_GET['supplierSearch'])) {
  $selectedSupplier = intval($_GET['supplierSearch']);

  if ($selectedSupplier > 0) {
    // Calculer la somme des arrivages
    $sqlArr = "
      SELECT IFNULL(SUM(Cost),0) as sumArrivals
      FROM tblproductarrivals
      WHERE SupplierID='$selectedSupplier'
    ";
    $resArr = mysqli_query($con, $sqlArr);
    $rowArr = mysqli_fetch_assoc($resArr);
    $totalArrivals = floatval($rowArr['sumArrivals']);

    // Calculer la somme des paiements
    $sqlPay = "
      SELECT IFNULL(SUM(Amount),0) as sumPaid
      FROM tblsupplierpayments
      WHERE SupplierID='$selectedSupplier'
    ";
    $resPay = mysqli_query($con, $sqlPay);
    $rowPay = mysqli_fetch_assoc($resPay);
    $totalPaid = floatval($rowPay['sumPaid']);

    // Solde
    $totalDue = $totalArrivals - $totalPaid;
    if ($totalDue < 0) $totalDue = 0;
  }
}

// ==========================
// 3) Liste des paiements
// ==========================
$sqlList = "
  SELECT sp.ID as paymentID,
         sp.PaymentDate,
         sp.Amount,
         sp.Comments,
         s.SupplierName
  FROM tblsupplierpayments sp
  LEFT JOIN tblsupplier s ON s.ID = sp.SupplierID
  ORDER BY sp.ID DESC
  LIMIT 100
";
$resList = mysqli_query($con, $sqlList);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Paiements Fournisseurs</title>
  <?php include_once('includes/cs.php'); ?>
  <?php include_once('includes/responsive.php'); ?>
<?php include_once('includes/header.php'); ?>
<?php include_once('includes/sidebar.php'); ?>

<div id="content">
  <div id="content-header">
    <h1>Paiements aux Fournisseurs</h1>
  </div>
  <div class="container-fluid">
    <hr>

    <!-- ========== FORMULAIRE pour voir combien on doit et a payé ========== -->
    <div class="row-fluid">
      <div class="span12">
        <form method="get" action="supplier-payments.php" class="form-inline">
          <label>Choisir un fournisseur :</label>
          <select name="supplierSearch" required>
            <option value="">-- Tous --</option>
            <?php
            // Charger la liste des fournisseurs
            $suppQ = mysqli_query($con, "SELECT ID, SupplierName FROM tblsupplier ORDER BY SupplierName ASC");
            while ($sRow = mysqli_fetch_assoc($suppQ)) {
              $sid   = $sRow['ID'];
              $sname = $sRow['SupplierName'];
              $sel   = ($sid == $selectedSupplier) ? 'selected' : '';
              echo "<option value='$sid' $sel>$sname</option>";
            }
            ?>
          </select>
          <button type="submit" class="btn btn-info">Voir le total</button>
        </form>
        <hr>

        <?php
        // Afficher le total pour le fournisseur sélectionné
        if ($selectedSupplier > 0) {
          // Récupérer son nom
          $qsupp = mysqli_query($con, "SELECT SupplierName FROM tblsupplier WHERE ID='$selectedSupplier' LIMIT 1");
          $rSupp = mysqli_fetch_assoc($qsupp);
          $supplierName = $rSupp ? $rSupp['SupplierName'] : '???';

          echo "<div style='border:1px solid #ccc; padding:10px;'>";
          echo "<h4>Fournisseur : <strong>$supplierName</strong></h4>";
          echo "<p>Total des arrivages : <strong>".number_format($totalArrivals,2)."</strong></p>";
          echo "<p>Total payé : <strong>".number_format($totalPaid,2)."</strong></p>";
          echo "<p>Solde dû : <strong style='color:red;'>".number_format($totalDue,2)."</strong></p>";
          echo "</div>";
        }
        ?>
      </div>
    </div><!-- row-fluid -->

    <hr>

    <!-- ========== FORMULAIRE d'ajout de paiement ========== -->
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title">
            <span class="icon"><i class="icon-align-justify"></i></span>
            <h5>Enregistrer un paiement</h5>
          </div>
          <div class="widget-content nopadding">
            <form method="post" class="form-horizontal">
              <div class="control-group">
                <label class="control-label">Fournisseur :</label>
                <div class="controls">
                  <select name="supplierid" required>
                    <option value="">-- Choisir --</option>
                    <?php
                    // Recharger la liste
                    $suppQ2 = mysqli_query($con, "SELECT ID, SupplierName FROM tblsupplier ORDER BY SupplierName ASC");
                    while ($rowS2 = mysqli_fetch_assoc($suppQ2)) {
                      echo '<option value="'.$rowS2['ID'].'">'.$rowS2['SupplierName'].'</option>';
                    }
                    ?>
                  </select>
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Date de paiement :</label>
                <div class="controls">
                  <input type="date" name="paydate" value="<?php echo date('Y-m-d'); ?>" required />
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Montant :</label>
                <div class="controls">
                  <input type="number" name="amount" step="any" min="0" value="0" required />
                </div>
              </div>
              <div class="control-group">
                <label class="control-label">Commentaires :</label>
                <div class="controls">
                  <input type="text" name="comments" placeholder="Référence, note..." />
                </div>
              </div>
              <div class="form-actions">
                <button type="submit" name="submit" class="btn btn-success">
                  Enregistrer
                </button>
              </div>
            </form>
          </div><!-- widget-content nopadding -->
        </div><!-- widget-box -->
      </div>
    </div><!-- row-fluid -->

    <hr>

    <!-- ========== LISTE DES PAIEMENTS ========== -->
    <div class="row-fluid">
      <div class="span12">
        <div class="widget-box">
          <div class="widget-title">
            <span class="icon"><i class="icon-th"></i></span>
            <h5>Liste des Paiements</h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered data-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Date</th>
                  <th>Fournisseur</th>
                  <th>Montant</th>
                  <th>Commentaires</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $cnt=1;
                while ($row = mysqli_fetch_assoc($resList)) {
                  ?>
                  <tr>
                    <td><?php echo $cnt; ?></td>
                    <td><?php echo $row['PaymentDate']; ?></td>
                    <td><?php echo $row['SupplierName']; ?></td>
                    <td><?php echo number_format($row['Amount'],2); ?></td>
                    <td><?php echo $row['Comments']; ?></td>
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
<script src="js/jquery.dataTables.min.js"></script>
<script src="js/matrix.tables.js"></script>
</body>
</html>
