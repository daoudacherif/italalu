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
    $custname = mysqli_real_escape_string($con, $_POST['custname']);
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
            echo "<script>alert('Erreur lors de l\'insertion du paiement');</script>";
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
// 3) Sous-requête : Somme factures
// ============================
// On regroupe par (CustomerName, MobileNumber)
$sqlFactures = "
  SELECT CustomerName, MobileNumber, SUM(FinalAmount) AS totalFactures
  FROM tblcustomer
  $whereCust
  GROUP BY CustomerName, MobileNumber
";

// ============================
// 4) Sous-requête : Somme paiements
// ============================
$sqlPaiements = "
  SELECT CustomerName, MobileNumber, SUM(Amount) AS totalPaiements
  FROM tblpayments
  $wherePay
  GROUP BY CustomerName, MobileNumber
";

// ============================
// 5) Jointure sur (CustomerName, MobileNumber)
// ============================
// On fait un LEFT JOIN dans un sens, puis un RIGHT JOIN, ou un FULL OUTER JOIN si MySQL le permet (ce n'est pas standard).
// Méthode simple : on fait un "union" de clients, puis on left join sur chaque. 
// Mais ici, je vais utiliser la technique de la "join" sur la sous-requête
$sql = "
  SELECT 
    COALESCE(f.CustomerName, p.CustomerName) as cName,
    COALESCE(f.MobileNumber, p.MobileNumber) as cMobile,
    IFNULL(f.totalFactures, 0) as totalFactures,
    IFNULL(p.totalPaiements, 0) as totalPaiements
  FROM
    ($sqlFactures) f
  FULL JOIN
    ($sqlPaiements) p
    ON f.CustomerName = p.CustomerName
    AND f.MobileNumber = p.MobileNumber
  ORDER BY cName ASC
";

// NOTE : MySQL n'a pas de FULL JOIN natif, donc on peut faire un UNION trick. 
// Si ton MySQL ne supporte pas FULL JOIN, on peut faire un LEFT JOIN + RIGHT JOIN union. 
// Par simplicité, si tu es sur MariaDB ou MySQL8, tu peux installer la table MDEV-10132 pour FULL JOIN. 
// Sinon, on va faire autrement. 
// => Je vais faire un LEFT JOIN + un RIGHT JOIN et un UNION. 
// => Pour l'exemple, je vais le simplifier en 2 requêtes + union distinct.

$engine = mysqli_get_server_info($con);
// Si FULL JOIN non supporté, on fait un "trick" :

if (stripos($engine, 'MariaDB') === false && stripos($engine, 'mysql') !== false) {
    // On va faire la solution "union" (LEFT + RIGHT)
    // LEFT
    $sqlLeft = "
      SELECT 
        f.CustomerName AS cName,
        f.MobileNumber AS cMobile,
        f.totalFactures,
        IFNULL(p.totalPaiements, 0) AS totalPaiements
      FROM ($sqlFactures) f
      LEFT JOIN ($sqlPaiements) p
        ON f.CustomerName = p.CustomerName
       AND f.MobileNumber = p.MobileNumber
    ";
    // RIGHT
    $sqlRight = "
      SELECT
        p.CustomerName AS cName,
        p.MobileNumber AS cMobile,
        IFNULL(f.totalFactures, 0) AS totalFactures,
        p.totalPaiements
      FROM ($sqlPaiements) p
      RIGHT JOIN ($sqlFactures) f
        ON f.CustomerName = p.CustomerName
       AND f.MobileNumber = p.MobileNumber
      WHERE f.CustomerName IS NULL
        AND f.MobileNumber IS NULL
    ";
    // union
    $sql = "
      SELECT * FROM (
        $sqlLeft
        UNION
        $sqlRight
      ) as unioned
      ORDER BY cName ASC
    ";
}

// Exécution
$res = mysqli_query($con, $sql);

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
        if ($solde < 0) $solde = 0;
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
