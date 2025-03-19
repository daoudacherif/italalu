<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifier si l'admin est connecté
if (strlen($_SESSION['imsaid'] == 0)) {
  header('location:logout.php');
  exit;
}

// 1) Gérer la soumission d'un paiement partiel
if (isset($_POST['addPayment'])) {
    $oid       = intval($_POST['oid']);        // ID from tblorders
    $payAmount = floatval($_POST['payAmount']); // The additional payment

    if ($payAmount <= 0) {
        echo "<script>alert('Montant invalide. Doit être > 0.');</script>";
    } else {
        // Fetch current Paid & Dues for this row
        $sql = "SELECT Paid, Dues FROM tblorders WHERE OrderID='$oid' LIMIT 1";
        $res = mysqli_query($con, $sql);
        if (mysqli_num_rows($res) > 0) {
            $row     = mysqli_fetch_assoc($res);
            $oldPaid = floatval($row['Paid']);
            $oldDues = floatval($row['Dues']);

            // Calculate new amounts
            $newPaid = $oldPaid + $payAmount;
            $newDues = $oldDues - $payAmount;
            if ($newDues < 0) {
                $newDues = 0; // cannot go below zero
            }

            // Update the record
            $update = "UPDATE tblorders 
                       SET Paid='$newPaid', Dues='$newDues'
                       WHERE OrderID='$oid'";
            mysqli_query($con, $update);

            echo "<script>alert('Paiement mis à jour avec succès !');</script>";
        } else {
            echo "<script>alert('Commande introuvable.');</script>";
        }
    }
    // Refresh the page
    echo "<script>window.location.href='order-details.php'</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Système de gestion des stocks | Détails des commandes</title>
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
      <a href="order-details.php" class="current">Détails des commandes</a>
    </div>
    <h1>Commandes / Factures</h1>
  </div>

  <div class="container-fluid">
    <hr>
    <div class="row-fluid">
      <div class="span12">

        <div class="widget-box">
          <div class="widget-title">
            <span class="icon"><i class="icon-th"></i></span>
            <h5>Toutes les commandes</h5>
          </div>
          <div class="widget-content nopadding">

            <table class="table table-bordered data-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Numéro de Commande</th>
                  <th>Nom du client</th>
                  <th>Numéro de mobile</th>
                  <th>Mode de paiement</th>
                  <th>Date de commande</th>
                  <th>Montant final</th>
                  <th>Payé</th>
                  <th>Reste dû</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Initialize totals
                $totalPaid = 0;
                $totalDues = 0;

                // Fetch all rows from tblorders
                $ret = mysqli_query($con, "SELECT * FROM tblorders ORDER BY OrderID DESC");
                $cnt = 1;
                while ($row = mysqli_fetch_array($ret)) {
                    // Accumulate for totals
                    $thisPaid = floatval($row['Paid']);
                    $thisDues = floatval($row['Dues']);
                    $totalPaid += $thisPaid;
                    $totalDues += $thisDues;

                    $orderID   = $row['OrderID'];
                    $orderNum  = $row['OrderNumber'];
                    $recipient = $row['RecipientName'];
                    $contact   = $row['RecipientContact'];
                    $method    = $row['PaymentMethod'];
                    $orderDate = $row['OrderDate'];
                    $netTotal  = floatval($row['NetTotal']);
                ?>
                    <tr class="gradeX">
                      <td><?php echo $cnt; ?></td>
                      <td><?php echo $orderNum; ?></td>
                      <td><?php echo $recipient; ?></td>
                      <td><?php echo $contact; ?></td>
                      <td><?php echo $method; ?></td>
                      <td><?php echo $orderDate; ?></td>
                      <td><?php echo number_format($netTotal, 2); ?></td>
                      <td><?php echo number_format($thisPaid, 2); ?></td>
                      <td><?php echo number_format($thisDues, 2); ?></td>
                      <td>
                        <?php if ($thisDues > 0) { ?>
                          <!-- Inline form to add partial payment -->
                          <form method="post" style="margin:0; display:inline;">
                            <input type="hidden" name="oid" value="<?php echo $orderID; ?>" />
                            <input type="number" name="payAmount" step="any" placeholder="Pay" style="width:60px;" />
                            <button type="submit" name="addPayment" class="btn btn-info btn-mini">
                              Add Payment
                            </button>
                          </form>
                        <?php } else { ?>
                          <span style="color: green; font-weight: bold;">Payé en intégralité</span>
                        <?php } ?>
                      </td>
                    </tr>
                <?php
                    $cnt++;
                } // end while
                ?>
              </tbody>
              <!-- Add a final row for totals -->
              <tfoot>
                <tr>
                  <!-- We'll merge the first 6 columns -->
                  <th colspan="7" style="text-align: right; font-weight: bold;">
                    Totaux:
                  </th>
                  <!-- Display the total of the Paid column -->
                  <th style="font-weight: bold;">
                    <?php echo number_format($totalPaid, 2); ?>
                  </th>
                  <!-- Display the total of the Dues column -->
                  <th style="font-weight: bold;">
                    <?php echo number_format($totalDues, 2); ?>
                  </th>
                  <th></th> <!-- Action column blank -->
                </tr>
              </tfoot>
            </table>

          </div><!-- widget-content nopadding -->
        </div><!-- widget-box -->

      </div><!-- span12 -->
    </div><!-- row-fluid -->
  </div><!-- container-fluid -->
</div><!-- content -->

<!-- Footer -->
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
