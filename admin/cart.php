<?php
include('includes/dbconnection.php');
session_start();

// Vérifier si le client est connecté
if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    exit();
}
$userId = $_SESSION['userid'];

// Ajout d'un produit au panier
if (isset($_POST['add_to_cart'])) {
    $productId = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Vérifier si le produit est déjà dans le panier
    $query = "SELECT * FROM tblcart WHERE ProductId = '$productId' AND UserID = '$userId'";
    $result = mysqli_query($con, $query);

    if (mysqli_num_rows($result) > 0) {
        // Mise à jour de la quantité si le produit est déjà dans le panier
        $row = mysqli_fetch_assoc($result);
        $cartId = $row['ID'];
        $newQty = $row['ProductQty'] + $quantity;

        $updateQuery = "UPDATE tblcart SET ProductQty = '$newQty' WHERE ID = '$cartId'";
        mysqli_query($con, $updateQuery);
    } else {
        // Récupération du prix du produit
        $priceQuery = "SELECT ProductPrice FROM tblproducts WHERE ID = '$productId'";
        $priceResult = mysqli_query($con, $priceQuery);
        $priceRow = mysqli_fetch_assoc($priceResult);
        $price = $priceRow['ProductPrice'];

        // Insertion dans le panier
        $insertQuery = "INSERT INTO tblcart(UserID, ProductId, ProductQty, Price, IsCheckOut) 
                        VALUES('$userId', '$productId', '$quantity', '$price', '0')";
        mysqli_query($con, $insertQuery);
    }
    header("Location: cart.php");
    exit();
}

// Suppression d'un produit du panier
if (isset($_GET['remove'])) {
    $cartId = $_GET['remove'];
    mysqli_query($con, "DELETE FROM tblcart WHERE ID = '$cartId'");
    header("Location: cart.php");
    exit();
}

// Validation du panier et enregistrement en tant que commande
if (isset($_POST['checkout'])) {
    $billingnum = 'INV-' . time();
    $custname = $_POST['customer_name'];
    $custmobilenum = $_POST['customer_mobile'];
    $modepayment = $_POST['payment_mode'];
    
    // Calcul du total du panier
    $totalQuery = "SELECT SUM(ProductQty * Price) AS Total FROM tblcart WHERE UserID = '$userId'";
    $totalResult = mysqli_query($con, $totalQuery);
    $totalRow = mysqli_fetch_assoc($totalResult);
    $netTotal = $totalRow['Total'];
    
    // Enregistrement du client
    $customerQuery = "INSERT INTO tblcustomer (BillingNumber, CustomerName, MobileNumber, ModeofPayment, FinalAmount) 
                      VALUES ('$billingnum', '$custname', '$custmobilenum', '$modepayment', '$netTotal')";
    mysqli_query($con, $customerQuery);
    $customerId = mysqli_insert_id($con);
    
    // Déplacement des articles du panier vers tblorders
    $cartItemsQuery = "SELECT * FROM tblcart WHERE UserID = '$userId'";
    $cartItemsResult = mysqli_query($con, $cartItemsQuery);
    
    while ($row = mysqli_fetch_assoc($cartItemsResult)) {
        $productId = $row['ProductId'];
        $quantity = $row['ProductQty'];
        $price = $row['Price'];
        
        $orderQuery = "INSERT INTO tblorders (CustomerID, ProductID, Quantity, Price) 
                        VALUES ('$customerId', '$productId', '$quantity', '$price')";
        mysqli_query($con, $orderQuery);
    }
    
    // Vider le panier après validation
    mysqli_query($con, "DELETE FROM tblcart WHERE UserID = '$userId'");
    header("Location: confirmation.php");
    exit();
}
?>
