<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifier si l'admin est connecté
if (strlen($_SESSION['imsaid']) == 0) {
    header('location:logout.php');
    exit;
}

// Récupérer l'identifiant de la facture depuis la session
if (!isset($_SESSION['invoiceid'])) {
    echo "<script>alert('Aucune facture trouvée.'); window.location.href='cart.php';</script>";
    exit;
}

$billingid = mysqli_real_escape_string($con, $_SESSION['invoiceid']);

// Récupérer les informations de la facture (client, paiement)
$queryInvoice = mysqli_query($con, "
    SELECT DISTINCT 
           tblcustomer.CustomerName,
           tblcustomer.MobileNumber,
           tblcustomer.ModeofPayment,
           tblcustomer.BillingDate,
           tblcustomer.FinalAmount,
           tblcustomer.Paid,
           tblcustomer.Dues
    FROM tblcart 
    JOIN tblcustomer ON tblcustomer.BillingNumber = tblcart.BillingId
    WHERE tblcustomer.BillingNumber = '$billingid'
    LIMIT 1
");
$invoice = mysqli_fetch_assoc($queryInvoice);
if (!$invoice) {
    echo "<script>alert('Facture introuvable.'); window.location.href='cart.php';</script>";
    exit;
}

// Récupérer les produits associés à la facture depuis tblcart et tblproducts
$queryItems = mysqli_query($con, "
    SELECT p.ProductName, p.ModelNumber, tblcart.ProductQty, tblcart.Price 
    FROM tblcart 
    LEFT JOIN tblproducts p ON p.ID = tblcart.ProductId
    WHERE tblcart.BillingId = '$billingid' AND tblcart.IsCheckOut = 1
");
$gtotal = 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Système de Gestion d'Inventaire || Facture</title>
    <?php include_once('includes/cs.php'); ?>
    <script type="text/javascript">
    function printInvoice(elementId) {
        if(confirm("Voulez-vous imprimer la facture ?")) {
            var invoiceContent = document.getElementById(elementId).innerHTML;
            var printWindow = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
            printWindow.document.write('<html><head><title>Facture</title>');
            printWindow.document.write('<style>body { font-family: Arial, sans-serif; margin: 20px; } table { width: 100%; border-collapse: collapse; } table, th, td { border: 1px solid #ccc; padding: 8px; } .text-right { text-align: right; }</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(invoiceContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        }
    }
    </script>
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
      <a href="invoice.php" class="current">Facture</a>
    </div>
    <h1>Facture</h1>
  </div>
  <div class="container-fluid">
    <hr>
    <div class="row-fluid">
      <!-- Zone à imprimer -->
      <div class="span12" id="invoiceContent">
        <h3 style="text-align: center;">Facture #<?php echo $billingid; ?></h3>
        <div class="table-responsive">
          <table class="table table-bordered" border="1">
            <tr>
              <th>Nom du client :</th>
              <td><?php echo htmlentities($invoice['CustomerName']); ?></td>
              <th>Numéro du client :</th>
              <td><?php echo htmlentities($invoice['MobileNumber']); ?></td>
            </tr>
            <tr>
              <th>Mode de paiement :</th>
              <td colspan="3"><?php echo $invoice['ModeofPayment']; ?></td>
            </tr>
            <tr>
              <th>Date de facturation :</th>
              <td colspan="3"><?php echo $invoice['BillingDate']; ?></td>
            </tr>
          </table>
        </div>
        <br>
        <div class="widget-box">
          <div class="widget-title">
            <span class="icon"><i class="icon-th"></i></span>
            <h5>Produits facturés</h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered" style="font-size: 15px">
              <thead>
                <tr>
                  <th>N°</th>
                  <th>Produit</th>
                  <th>Numéro de Modèle</th>
                  <th>Quantité</th>
                  <th>Prix (unité)</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                $cnt = 1;
                while ($item = mysqli_fetch_assoc($queryItems)) {
                    $qty = $item['ProductQty'];
                    $ppu = $item['Price'];
                    $lineTotal = $qty * $ppu;
                    $gtotal += $lineTotal;
                ?>
                <tr>
                  <td><?php echo $cnt; ?></td>
                  <td><?php echo htmlentities($item['ProductName']); ?></td>
                  <td><?php echo htmlentities($item['ModelNumber']); ?></td>
                  <td><?php echo $qty; ?></td>
                  <td class="text-right"><?php echo number_format($ppu, 2); ?></td>
                  <td class="text-right"><?php echo number_format($lineTotal, 2); ?></td>
                </tr>
                <?php 
                    $cnt++;
                }
                ?>
              </tbody>
              <tfoot>
                <tr style="font-weight: bold;">
                  <td colspan="5" class="text-right">Total général</td>
                  <td class="text-right"><?php echo number_format($gtotal, 2); ?></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <br>
        <div class="table-responsive">
          <table class="table table-bordered" border="1">
            <tr>
              <th>Final :</th>
              <td><?php echo number_format($invoice['FinalAmount'], 2); ?></td>
            </tr>
            <tr>
              <th>Payé :</th>
              <td><?php echo number_format($invoice['Paid'], 2); ?></td>
            </tr>
            <tr>
              <th>Reste à payer :</th>
              <td><?php echo number_format($invoice['Dues'], 2); ?></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <br>
    <p style="text-align: center;">
        <button type="button" class="btn btn-primary" onclick="printInvoice('invoiceContent')">
            Imprimer la Facture
        </button>
    </p>
  </div><!-- container-fluid -->
</div><!-- content -->

<?php include_once('includes/footer.php'); ?>
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
<?php ?>
