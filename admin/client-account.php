<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifier si admin connecté
if (strlen($_SESSION['imsaid'] == 0)) {
  header('location:logout.php');
  exit;
}

// ============================
// 1) Gérer l'ajout d'un paiement
// ============================
if (isset($_POST['addPayment'])) {
    $custname   = mysqli_real_escape_string($con, $_POST['custname']);
    $custmobile = mysqli_real_escape_string($con, $_POST['custmobile']);
    $amountPaid = floatval($_POST['amount']);
    $comments   = mysqli_real_escape_string($con, $_POST['comments']);

    if ($amountPaid <= 0) {
        echo "<script>alert('Montant invalide');</script>";
    } else {
        // Insérer dans tblpayments
        $sqlPay = "
          INSERT INTO tblpayments(PaymentDate, CustomerName, MobileNumber, Amount, Comments)
          VALUES(NOW(), '$custname', '$custmobile', '$amountPaid', '$comments')
        ";
        $resPay = mysqli_query($con, $sqlPay);
        if ($resPay) {
            echo "<script>alert('Paiement enregistré !');</script>";
        } else {
            echo "<script>alert('Erreur lors de l\\'insertion du paiement');</script>";
        }
    }
    // Refresh
    echo "<script>window.location.href='client-account.php'</script>";
    exit;
}

// ============================
// 2) Filtre de recherche
// ============================
$searchTerm = '';
$whereCust  = '';
$wherePay   = '';
if (isset($_GET['searchTerm']) && !empty($_GET['searchTerm'])) {
    $searchTerm = mysqli_real_escape_string($con, $_GET['searchTerm']);
    // On cherche dans CustomerName ou MobileNumber
    $whereCust = "WHERE (CustomerName LIKE '%$searchTerm%' OR MobileNumber LIKE '%$searchTerm%')";
    $wherePay  = "WHERE (CustomerName LIKE '%$searchTerm%' OR MobileNumber LIKE '%$searchTerm%')";
}

// ============================
// 3) Sous-requête : Somme factures (groupé par client)
// ============================
$subFact = "
  SELECT CustomerName, MobileNumber, SUM(FinalAmount) AS totalFactures
  FROM tblcustomer
  $whereCust
  GROUP BY CustomerName, MobileNumber
";

// ============================
// 4) Sous-requête : Somme paiements (groupé par client)
// ============================
$subPay = "
  SELECT CustomerName, MobileNumber, SUM(Amount) AS totalPaiements
  FROM tblpayments
  $wherePay
  GROUP BY CustomerName, MobileNumber
";

// ============================
// 5) Liste unifiée des clients
// ============================
// On prend tous ceux présents dans tblcustomer union tblpayments
$allClients = "
  SELECT CustomerName, MobileNumber FROM tblcustomer $whereCust
  UNION
  SELECT CustomerName, MobileNumber FROM tblpayments $wherePay
";

// ============================
// 6) On fait un LEFT JOIN des sommes sur la liste unifiée
// ============================
// => Cela simule un FULL JOIN
$sql = "
  SELECT 
    ac.CustomerName AS cName,
    ac.MobileNumber AS cMobile,
    IFNULL(f.totalFactures, 0) AS totalFactures,
    IFNULL(p.totalPaiements, 0) AS totalPaiements
  FROM (
    $allClients
  ) AS ac
  LEFT JOIN ($subFact) f
    ON f.CustomerName = ac.CustomerName
   AND f.MobileNumber = ac.MobileNumber
  LEFT JOIN ($subPay) p
    ON p.CustomerName = ac.CustomerName
   AND p.MobileNumber = ac.MobileNumber
  ORDER BY cName ASC
";

// Exécution
$res = mysqli_query($con, $sql);
if (!$res) {
    die("Erreur SQL : " . mysqli_error($con));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Compte Client (Factures + Paiements Indépendants)</title>
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

    <!-- Tableau listant les clients, totalFactures, totalPaiements, solde -->
    <table class="table table-bordered table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Nom du client</th>
          <th>Téléphone</th>
          <th>Total Facturé</th>
          <th>Total Payé</th>
          <th>Solde (Dû)</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $cnt=1;
      while($row = mysqli_fetch_assoc($res)) {
        $cName   = $row['cName'];
        $cMobile = $row['cMobile'];
        $fact    = floatval($row['totalFactures']);
        $pay     = floatval($row['totalPaiements']);
        $solde   = $fact - $pay; 
        if ($solde < 0) $solde = 0; // on évite un solde négatif
        ?>
        <tr>
          <td><?php echo $cnt++; ?></td>
          <td><?php echo $cName; ?></td>
          <td><?php echo $cMobile; ?></td>
          <td><?php echo number_format($fact,2); ?></td>
          <td><?php echo number_format($pay,2); ?></td>
          <td style="font-weight: bold; color: red;">
            <?php echo number_format($solde,2); ?>
          </td>
          <td>
            <!-- Formulaire pour ajouter un paiement -->
            <form method="post" style="margin:0; display:inline;">
              <input type="hidden" name="custname" value="<?php echo htmlspecialchars($cName); ?>" />
              <input type="hidden" name="custmobile" value="<?php echo htmlspecialchars($cMobile); ?>" />
              <input type="number" name="amount" step="any" placeholder="Paiement" style="width:80px;" />
              <input type="text" name="comments" placeholder="Note" style="width:80px;" />
              <button type="submit" name="addPayment" class="btn btn-info btn-mini">
                Payer
              </button>
            </form>

            <!-- Lien pour voir le détail (facultatif) -->
            <a href="client-account-detail.php?name=<?php echo urlencode($cName); ?>&mobile=<?php echo urlencode($cMobile); ?>"
               class="btn btn-success btn-mini">
               Détails
            </a>
          </td>
        </tr>
        <?php
      }
      ?>
      </tbody>
    </table>

  </div><!-- container-fluid -->
</div><!-- content -->

<?php include_once('includes/footer.php'); ?>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
