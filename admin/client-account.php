<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifier si l'admin est connecté
if (strlen($_SESSION['imsaid']) == 0) { // Correction de la condition
    header('location:logout.php');
    exit;
}

// ============================
// 1) Gérer l'ajout d'un paiement (indépendant des factures)
// ============================
if (isset($_POST['addPayment'])) {
    $custname   = $_POST['custname'];
    $custmobile = $_POST['custmobile'];
    $amountPaid = floatval($_POST['amount']);
    $comments   = $_POST['comments'];

    if ($amountPaid <= 0) {
        echo "<script>alert('Montant invalide');</script>";
    } else {
        // Utilisation d'une requête préparée
        $sqlPay = "INSERT INTO tblpayments(PaymentDate, CustomerName, MobileNumber, Amount, Comments)
                   VALUES(NOW(), ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sqlPay);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssds", $custname, $custmobile, $amountPaid, $comments);
            $resPay = mysqli_stmt_execute($stmt);
            if ($resPay) {
                echo "<script>alert('Paiement enregistré !');</script>";
            } else {
                echo "<script>alert('Erreur lors de l\\'insertion du paiement');</script>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<script>alert('Erreur de préparation de la requête');</script>";
        }
    }
    echo "<script>window.location.href='client-account.php'</script>";
    exit;
}

// ============================
// 2) Filtre de recherche (nom/téléphone)
// ============================
$searchTerm = '';
$searchParam = '';
$hasSearch = false;
if (isset($_GET['searchTerm']) && !empty($_GET['searchTerm'])) {
    $searchTerm = $_GET['searchTerm'];
    $searchParam = "%".$searchTerm."%";
    $hasSearch = true;
}

// ============================
// 3) Requête principale sécurisée
// ============================
$sql = "SELECT 
          ac.CustomerName AS cName,
          ac.MobileNumber AS cMobile,
          COALESCE(f.totalFactures, 0) AS totalFactures,
          COALESCE(p.totalPaiements, 0) AS totalPaiements
        FROM (
          SELECT CustomerName, MobileNumber FROM tblcustomer
          UNION
          SELECT CustomerName, MobileNumber FROM tblpayments
        ) ac
        LEFT JOIN (
          SELECT CustomerName, MobileNumber, SUM(FinalAmount) AS totalFactures
          FROM tblcustomer
          GROUP BY CustomerName, MobileNumber
        ) f USING (CustomerName, MobileNumber)
        LEFT JOIN (
          SELECT CustomerName, MobileNumber, SUM(Amount) AS totalPaiements
          FROM tblpayments
          GROUP BY CustomerName, MobileNumber
        ) p USING (CustomerName, MobileNumber)
        WHERE (? = '' OR ac.CustomerName LIKE ? OR ac.MobileNumber LIKE ?)
        ORDER BY cName ASC";

$stmt = mysqli_prepare($con, $sql);
if ($stmt) {
    if ($hasSearch) {
        mysqli_stmt_bind_param($stmt, "sss", $searchParam, $searchParam, $searchParam);
    } else {
        $dummy = '';
        mysqli_stmt_bind_param($stmt, "sss", $dummy, $dummy, $dummy);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
} else {
    die("Erreur de requête : " . mysqli_error($con));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Compte Client</title>
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
    <form method="get" class="form-inline">
      <input type="text" name="searchTerm" placeholder="Nom ou téléphone"
             value="<?php echo htmlspecialchars($searchTerm); ?>" class="span3">
      <button type="submit" class="btn btn-primary">Rechercher</button>
    </form>
    <hr>

    <!-- Tableau des comptes -->
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Client</th>
          <th>Téléphone</th>
          <th>Total Facturé</th>
          <th>Total Payé</th>
          <th>Solde</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $cnt = 1;
      while ($row = mysqli_fetch_assoc($res)) {
          $solde = $row['totalFactures'] - $row['totalPaiements'];
          ?>
          <tr>
            <td><?php echo $cnt++; ?></td>
            <td><?php echo htmlspecialchars($row['cName']); ?></td>
            <td><?php echo htmlspecialchars($row['cMobile']); ?></td>
            <td><?php echo number_format($row['totalFactures'], 2); ?></td>
            <td><?php echo number_format($row['totalPaiements'], 2); ?></td>
            <td class="<?php echo ($solde > 0) ? 'text-error' : 'text-success' ?>">
              <?php echo number_format($solde, 2); ?>
            </td>
            <td>
              <form method="post" style="display:inline-block;">
                <input type="hidden" name="custname" value="<?php echo htmlspecialchars($row['cName']); ?>">
                <input type="hidden" name="custmobile" value="<?php echo htmlspecialchars($row['cMobile']); ?>">
                <input type="number" name="amount" step="0.01" placeholder="Montant" style="width:80px;" required>
                <input type="text" name="comments" placeholder="Commentaire" maxlength="50">
                <button type="submit" name="addPayment" class="btn btn-mini btn-success">
                  <i class="icon-plus"></i> Ajouter
                </button>
              </form>
            </td>
          </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>

<?php include_once('includes/footer.php'); ?>
</body>
</html>