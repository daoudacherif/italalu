<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check if an invoice ID is available
if (!isset($_SESSION['invoiceid'])) {
    echo "<script>alert('No invoice found.'); window.location.href='cart.php';</script>";
    exit;
}

$invoiceid = mysqli_real_escape_string($con, $_SESSION['invoiceid']);

// Fetch invoice details from tblcustomer
$queryInvoice = mysqli_query($con, "SELECT * FROM tblcustomer WHERE BillingNumber = '$invoiceid' LIMIT 1");
$invoice = mysqli_fetch_assoc($queryInvoice);
if (!$invoice) {
    echo "<script>alert('Invoice not found in the database.'); window.location.href='cart.php';</script>";
    exit;
}

// Fetch the products associated with the invoice from tblcart (joined with tblproducts)
$queryItems = mysqli_query($con, "
    SELECT c.ProductQty, c.Price, p.ProductName 
    FROM tblcart c
    LEFT JOIN tblproducts p ON p.ID = c.ProductId
    WHERE c.BillingId = '$invoiceid' AND c.IsCheckOut = 1
");

$total = 0;
$items = [];
while ($item = mysqli_fetch_assoc($queryItems)) {
    $item['lineTotal'] = $item['ProductQty'] * $item['Price'];
    $total += $item['lineTotal'];
    $items[] = $item;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture #<?php echo $invoiceid; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-container { width: 800px; margin: auto; border: 1px solid #ddd; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 10px; text-align: left; }
        .text-right { text-align: right; }
        .print-btn { margin-top: 20px; display: block; text-align: center; }
    </style>
</head>
<body>
<div class="invoice-container">
    <div class="header">
        <h1>Facture</h1>
        <h3>Numéro de facture : <?php echo $invoiceid; ?></h3>
    </div>
    <div class="details">
        <p><strong>Date de Facturation :</strong> <?php echo $invoice['BillingDate']; ?></p>
        <p><strong>Client :</strong> <?php echo htmlentities($invoice['CustomerName']); ?></p>
        <p><strong>Numéro de Mobile :</strong> <?php echo htmlentities($invoice['MobileNumber']); ?></p>
        <p><strong>Mode de Paiement :</strong> <?php echo $invoice['ModeofPayment']; ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 1;
            foreach ($items as $item) {
                echo "<tr>";
                echo "<td>" . $i . "</td>";
                echo "<td>" . htmlentities($item['ProductName']) . "</td>";
                echo "<td>" . $item['ProductQty'] . "</td>";
                echo "<td class='text-right'>" . number_format($item['Price'], 2) . "</td>";
                echo "<td class='text-right'>" . number_format($item['lineTotal'], 2) . "</td>";
                echo "</tr>";
                $i++;
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total Général</th>
                <th class="text-right"><?php echo number_format($total, 2); ?></th>
            </tr>
        </tfoot>
    </table>
    <div class="details">
        <p><strong>Remise :</strong> <?php echo number_format($invoice['FinalAmount'] - $total, 2); ?></p>
        <p><strong>Montant Final :</strong> <?php echo number_format($invoice['FinalAmount'], 2); ?></p>
        <p><strong>Montant Payé :</strong> <?php echo number_format($invoice['Paid'], 2); ?></p>
        <p><strong>Reste à Payer :</strong> <?php echo number_format($invoice['Dues'], 2); ?></p>
    </div>
    <div class="print-btn">
        <button onclick="window.print()">Imprimer la Facture</button>
    </div>
</div>
</body>
</html>
