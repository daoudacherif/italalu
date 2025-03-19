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
// 0) Préparer la liste de tous les produits pour le <datalist>
// ==========================
$allProdQuery = mysqli_query($con, "SELECT ProductName FROM tblproducts ORDER BY ProductName ASC");
$productNames = [];
while ($rowProd = mysqli_fetch_assoc($allProdQuery)) {
  $productNames[] = $rowProd['ProductName'];
}

// ==========================
// 1) Ajouter un produit au panier
// ==========================
if (isset($_POST['addtocart'])) {
  $productId = intval($_POST['productid']);
  $quantity  = intval($_POST['quantity']);
  $price     = floatval($_POST['price']);  // prix saisi ou calculé

  if ($quantity <= 0)  $quantity = 1;
  if ($price    < 0)  $price    = 0;

  // Vérifier si ce produit est déjà dans le panier (IsCheckOut=0)
  $checkCart = mysqli_query($con, "
    SELECT ID, ProductQty 
    FROM tblcart 
    WHERE ProductId='$productId' AND IsCheckOut=0 
    LIMIT 1
  ");
  if (mysqli_num_rows($checkCart) > 0) {
    // Mise à jour de la quantité
    $row    = mysqli_fetch_assoc($checkCart);
    $cartId = $row['ID'];
    $oldQty = $row['ProductQty'];
    $newQty = $oldQty + $quantity;

    mysqli_query($con, "
      UPDATE tblcart 
      SET ProductQty='$newQty', Price='$price'
      WHERE ID='$cartId'
    ");
  } else {
    // Insérer un nouvel article dans le panier
    mysqli_query($con, "
      INSERT INTO tblcart(ProductId, ProductQty, Price, IsCheckOut) 
      VALUES('$productId', '$quantity', '$price', '0')
    ");
  }
  echo "<script>alert('Produit ajouté au panier!');</script>";
  echo "<script>window.location.href='cart.php'</script>";
  exit;
}

// ==========================
// 2) Retirer un produit du panier
// ==========================
if (isset($_GET['delid'])) {
  $rid = intval($_GET['delid']);
  mysqli_query($con, "DELETE FROM tblcart WHERE ID='$rid'");
  echo "<script>alert('Produit retiré du panier');</script>";
  echo "<script>window.location.href = 'cart.php'</script>";
  exit;
}

// ==========================
// 3) Gérer la remise (discount) en session
// ==========================
if (isset($_POST['applyDiscount'])) {
  $_SESSION['discount'] = floatval($_POST['discount']);
  echo "<script>window.location.href='cart.php'</script>";
  exit;
}
$discount = isset($_SESSION['discount']) ? $_SESSION['discount'] : 0;

// ==========================
// 4) Validation du panier (checkout) & création de commande
// ==========================
if (isset($_POST['submit'])) {
  $custname      = mysqli_real_escape_string($con, $_POST['customername']);
  $custmobilenum = mysqli_real_escape_string($con, $_POST['mobilenumber']);
  $modepayment   = mysqli_real_escape_string($con, $_POST['modepayment']);

  // Recalculer le total du panier
  $cartQuery = mysqli_query($con, "
    SELECT ID, ProductId, ProductQty, Price 
    FROM tblcart
    WHERE IsCheckOut=0
  ");
  $grandTotal = 0;
  $cartItems = [];
  while ($row = mysqli_fetch_assoc($cartQuery)) {
    $cartItems[] = $row;
    $grandTotal += ($row['ProductQty'] * $row['Price']);
  }

  // Appliquer la remise
  $netTotal = $grandTotal - $discount;
  if ($netTotal < 0) $netTotal = 0;

  // Générer un OrderNumber unique
  $orderNumber = mt_rand(100000000, 999999999);

  // Insérer la commande dans tblorders
  $orderDate = date('Y-m-d');
  $subtotal  = $grandTotal;
  $tax       = 0;    // tu peux calculer la TVA si besoin
  $paid      = 0;    // si tu gères un paiement direct
  $dues      = $netTotal; // s'il reste tout à payer

  $sqlOrder = "
    INSERT INTO tblorders(
      OrderNumber,
      OrderDate,
      RecipientName,
      RecipientContact,
      Subtotal,
      Tax,
      Discount,
      NetTotal,
      Paid,
      Dues,
      PaymentMethod
    ) VALUES(
      '$orderNumber',
      '$orderDate',
      '$custname',
      '$custmobilenum',
      '$subtotal',
      '$tax',
      '$discount',
      '$netTotal',
      '$paid',
      '$dues',
      '$modepayment'
    )
  ";
  $orderRes = mysqli_query($con, $sqlOrder);
  if (!$orderRes) {
    echo "<script>alert('Erreur lors de la création de la commande');</script>";
    exit;
  }

  // Récupérer l'ID de la commande qu'on vient d'insérer
  $orderID = mysqli_insert_id($con);

  // Pour chaque article du panier, insérer une ligne dans tblorderdetails
  foreach ($cartItems as $item) {
    $prodID = $item['ProductId'];
    $qty    = $item['ProductQty'];
    $ppu    = $item['Price'];
    $lineTotal = $qty * $ppu;

    $sqlDetail = "
      INSERT INTO tblorderdetails(OrderID, ProductID, Price, Qty, Total)
      VALUES('$orderID', '$prodID', '$ppu', '$qty', '$lineTotal')
    ";
    mysqli_query($con, $sqlDetail);
  }

  // Marquer les articles du panier comme validés (IsCheckOut=1)
  mysqli_query($con, "UPDATE tblcart SET IsCheckOut=1 WHERE IsCheckOut=0");

  // Nettoyage de la remise
  unset($_SESSION['discount']);

  // Confirmation
  echo "<script>alert('Commande créée avec succès. Numéro : $orderNumber');</script>";
  // Redirection, par exemple vers une page invoice
  $_SESSION['invoiceid'] = $orderNumber;
  echo "<script>window.location.href='invoice.php'</script>";
  exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Système de gestion des stocks | Panier</title>
  <?php include_once('includes/cs.php'); ?>
</head>
<body>

<!-- Header + Sidebar -->
<?php include_once('includes/header.php'); ?>
<?php include_once
