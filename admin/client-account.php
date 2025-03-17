<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifier si l'admin est connecté
if (strlen($_SESSION['imsaid'] == 0)) {
  header('location:logout.php');
  exit;
}

// ==========================
// 1) Action "Solder le compte" (exemple)
// ==========================
if (isset($_POST['solderCompte'])) {
  $customerName = mysqli_real_escape_string($con, $_POST['custname']);
  $mobileNumber = mysqli_real_escape_string($con, $_POST['custmobile']);

  $sqlSolder = "
    UPDATE tblcustomer
    SET Paid = FinalAmount, Dues = 0
    WHERE CustomerName = '$customerName'
      AND MobileNumber = '$mobileNumber'
  ";
  $resSolder = mysqli_query($con, $sqlSolder);
  if ($resSolder) {
    echo "<script>alert('Le compte du client a été soldé.');</script>";
  } else {
    echo "<script>alert('Erreur lors du solder du compte.');</script>";
  }
  echo "<script>window.location.href='client-account.php'</script>";
  exit;
}

// ==========================
// 2) Filtre de recherche
// ==========================
$searchTerm = '';
$whereClause = '';
if (isset($_GET['searchTerm']) && !empty($_GET['searchTerm'])) {
  $searchTerm = mysqli_real_escape_string($con, $_GET['searchTerm']);
  // On cherche dans CustomerName ou MobileNumber
  $whereClause = "WHERE (CustomerName LIKE '%$searchTerm%' OR MobileNumber LIKE '%$searchTerm%')";
}

// ==========================
// 3) Requête pour lister les clients + sommes
// ==========================
$sql = "
  SELECT 
    CustomerName,
    MobileNumber,
    SUM(FinalAmount) AS totalBilled,
    SUM(Paid) AS totalPaid,
    SUM(Dues) AS totalDue
  FROM tblcustomer
  $whereClause
  GROUP BY CustomerName, MobileNumber
  ORDER BY CustomerName ASC
";
$res = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Compte Client | Ventes à terme</title>
  <?php include_once('includes/cs.php'); ?>
</head>
<body>
<?php include_once('includes/header.php'); ?>
<?php include_once('includes/sidebar.php'); ?>

<div id="content">
  <div id="content-header">
    <h1>Compte Client</h1>
  </div>
  <div class="container-fluid">
    <hr>

    <!-- Formulaire de recherche -->
    <form method="get" action="client-account.php" class="form-inline">
      <label>Rechercher un client :</label>
      <input type="text" name="searchTerm" placeholder="Nom ou téléphone" 
             value="<?php echo htmlspecialchars($searchTerm); ?>" class="span3" />
      <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>
    <hr>

    <!-- Tableau des clients -->
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Nom du client</th>
          <th>Téléphone</th>
          <th>Total Facturé</th>
          <th>Total Payé</th>
          <th>Reste à Payer</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php
      // Variables pour accumuler les totaux
      $grandBilled = 0;
      $grandPaid   = 0;
      $grandDue    = 0;

      $cnt = 1;
      while ($row = mysqli_fetch_assoc($res)) {
        $customerName = $row['CustomerName'];
        $mobile       = $row['MobileNumber'];
        $billed       = floatval($row['totalBilled']);
        $paid         = floatval($row['totalPaid']);
        $due          = floatval($row['totalDue']);

        // Accumuler dans les variables globales
        $grandBilled += $billed;
        $grandPaid   += $paid;
        $grandDue    += $due;
        ?>
        <tr>
          <td><?php echo $cnt++; ?></td>
          <td><?php echo $customerName; ?></td>
          <td><?php echo $mobile; ?></td>
          <td><?php echo number_format($billed,2); ?></td>
          <td><?php echo number_format($paid,2); ?></td>
          <td><?php echo number_format($due,2); ?></td>
          <td>
            <!-- Lien Détails -->
            <a href="client-account-details.php?name=<?php echo urlencode($customerName); ?>&mobile=<?php echo urlencode($mobile); ?>"
               class="btn btn-info btn-small">Détails</a>

            <!-- Bouton "Solder" si le client a un due > 0 -->
            <?php if ($due > 0) { ?>
              <form method="post" style="display:inline;">
                <input type="hidden" name="custname" value="<?php echo htmlspecialchars($customerName); ?>" />
                <input type="hidden" name="custmobile" value="<?php echo htmlspecialchars($mobile); ?>" />
                <button type="submit" name="solderCompte" class="btn btn-success btn-small">
                  Solder
                </button>
              </form>
            <?php } else { ?>
              <span style="color: green; font-weight: bold;">Soldé</span>
            <?php } ?>
          </td>
        </tr>
        <?php
      } // end while
      ?>
      </tbody>

      <!-- Ligne de total -->
      <tfoot>
        <tr style="font-weight: bold;">
          <td colspan="3" style="text-align: right;">TOTAL GÉNÉRAL</td>
          <td><?php echo number_format($grandBilled,2); ?></td>
          <td><?php echo number_format($grandPaid,2); ?></td>
          <td><?php echo number_format($grandDue,2); ?></td>
          <td></td>
        </tr>
      </tfoot>
    </table>

  </div><!-- container-fluid -->
</div><!-- content -->

<?php include_once('includes/footer.php'); ?>
<!-- scripts -->
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
