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
<?php include_once('includes/sidebar.php'); ?>

<div id="content">
  <div id="content-header">
    <div id="breadcrumb">
      <a href="dashboard.php" title="Aller à l'accueil" class="tip-bottom">
        <i class="icon-home"></i> Accueil
      </a>
      <a href="cart.php" class="current">Panier de produits</a>
    </div>
    <h1>Panier de produits</h1>
  </div>

  <div class="container-fluid">
    <hr>

    <!-- ========== FORMULAIRE DE RECHERCHE (avec datalist) ========== -->
    <div class="row-fluid">
      <div class="span12">
        <form method="get" action="cart.php" class="form-inline">
          <label>Rechercher des produits :</label>
          <input type="text" name="searchTerm" class="span3"
                 placeholder="Nom du produit..." list="productsList" />

          <!-- La datalist contenant tous les noms de produits -->
          <datalist id="productsList">
            <?php
            // Générer <option> pour chaque nom de produit
            foreach ($productNames as $pname) {
              echo '<option value="' . htmlspecialchars($pname) . '"></option>';
            }
            ?>
          </datalist>

          <button type="submit" class="btn btn-primary">Rechercher</button>
        </form>
      </div>
    </div>
    <hr>

    <!-- ========== AFFICHAGE DES RÉSULTATS DE RECHERCHE ========== -->
    <?php
    if (!empty($_GET['searchTerm'])) {
      $searchTerm = mysqli_real_escape_string($con, $_GET['searchTerm']);
      $sql = "
        SELECT p.ID, p.ProductName, p.BrandName, p.ModelNumber, p.Price,
               c.CategoryName, s.SubCategoryName
        FROM tblproducts p
        LEFT JOIN tblcategory c ON c.ID = p.CatID
        LEFT JOIN tblsubcategory s ON s.ID = p.SubcatID
        WHERE (p.ProductName LIKE '%$searchTerm%' 
          OR p.ModelNumber LIKE '%$searchTerm%')
      ";
      $res = mysqli_query($con, $sql);
      $count = mysqli_num_rows($res);
      ?>
      <div class="row-fluid">
        <div class="span12">
          <h4>Résultats de recherche pour "<em><?php echo htmlentities($searchTerm); ?></em>"</h4>
          <?php if ($count > 0) { ?>
            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nom du produit</th>
                  <th>Catégorie</th>
                  <th>Sous-catégorie</th>
                  <th>Marque</th>
                  <th>Modèle</th>
                  <th>Prix par défaut</th>
                  <th>Prix personnalisé</th>
                  <th>Quantité</th>
                  <th>Ajouter</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i=1;
                while ($row = mysqli_fetch_assoc($res)) {
                  ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo $row['ProductName']; ?></td>
                    <td><?php echo $row['CategoryName']; ?></td>
                    <td><?php echo $row['SubCategoryName']; ?></td>
                    <td><?php echo $row['BrandName']; ?></td>
                    <td><?php echo $row['ModelNumber']; ?></td>
                    <td><?php echo $row['Price']; ?></td>
                    <td>
                      <form method="post" action="cart.php" style="margin:0;">
                        <input type="hidden" name="productid" value="<?php echo $row['ID']; ?>" />
                        <input type="number" name="price" step="any" 
                               value="<?php echo $row['Price']; ?>" style="width:80px;" />
                    </td>
                    <td>
                        <input type="number" name="quantity" value="1" min="1" style="width:60px;" />
                    </td>
                    <td>
                        <button type="submit" name="addtocart" class="btn btn-success btn-small">
                          <i class="icon-plus"></i> Ajouter
                        </button>
                      </form>
                    </td>
                  </tr>
                  <?php
                }
                ?>
              </tbody>
            </table>
          <?php } else { ?>
            <p style="color:red;">Aucun produit correspondant trouvé.</p>
          <?php } ?>
        </div>
      </div>
      <hr>
    <?php } // end if searchTerm ?>

    <!-- ========== PANIER + REMISE + PAIEMENT ========== -->
    <div class="row-fluid">
      <div class="span12">

        <!-- Formulaire pour la remise -->
        <form method="post" class="form-inline" style="text-align:right;">
          <label>Remise :</label>
          <input type="number" name="discount" step="any" 
                 value="<?php echo $discount; ?>" style="width:80px;" />
          <button class="btn btn-info" type="submit" name="applyDiscount">Appliquer</button>
        </form>
        <hr>

        <!-- Formulaire checkout (infos client) -->
        <form method="post" class="form-horizontal" name="submit">
          <div class="control-group">
            <label class="control-label">Nom du client :</label>
            <div class="controls">
              <input type="text" class="span11" name="customername" required />
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">Numéro de mobile du client :</label>
            <div class="controls">
              <input type="text" class="span11" name="mobilenumber" required
                     maxlength="10" pattern=\"[0-9]+\" />
            </div>
          </div>
          <div class="control-group">
            <label class="control-label">Mode de paiement :</label>
            <div class="controls">
              <label><input type="radio" name="modepayment" value="cash" checked> Espèces</label>
              <label><input type="radio" name="modepayment" value="card"> Carte</label>
            </div>
          </div>
          <div class="text-center">
            <button class="btn btn-primary" type="submit" name="submit">
              Paiement & Créer une commande
            </button>
          </div>
        </form>

        <!-- Tableau du panier -->
        <div class="widget-box">
          <div class="widget-title">
            <span class="icon"><i class="icon-th"></i></span>
            <h5>Produits dans le panier</h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered" style="font-size: 15px">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nom du produit</th>
                  <th>Quantité</th>
                  <th>Prix (par unité)</th>
                  <th>Total</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $ret = mysqli_query($con, "
                  SELECT 
                    tblcart.ID as cid,
                    tblcart.ProductQty,
                    tblcart.Price as cartPrice,
                    tblproducts.ProductName
                  FROM tblcart
                  LEFT JOIN tblproducts ON tblproducts.ID = tblcart.ProductId
                  WHERE tblcart.IsCheckOut = 0
                  ORDER BY tblcart.ID ASC
                ");
                $cnt=1; 
                $grandTotal=0; 
                $num=mysqli_num_rows($ret);
                if($num>0){
                  while ($row=mysqli_fetch_array($ret)) {
                    $pq    = $row['ProductQty'];
                    $ppu   = $row['cartPrice'];
                    $lineTotal = $pq * $ppu;
                    $grandTotal += $lineTotal;
                    ?>
                    <tr class="gradeX">
                      <td><?php echo $cnt; ?></td>
                      <td><?php echo $row['ProductName']; ?></td>
                      <td><?php echo $pq; ?></td>
                      <td><?php echo number_format($ppu,2); ?></td>
                      <td><?php echo number_format($lineTotal,2); ?></td>
                      <td>
                        <a href="cart.php?delid=<?php echo $row['cid'];?>"
                           onclick="return confirm('Voulez-vous vraiment retirer cet article?');">
                           <i class="icon-trash"></i>
                        </a>
                      </td>
                    </tr>
                    <?php
                    $cnt++;
                  }
                  $netTotal = $grandTotal - $discount;
                  if ($netTotal < 0) $netTotal = 0;
                  ?>
                  <tr>
                    <th colspan="4" style="text-align: right; font-weight: bold;">
                      Total général
                    </th>
                    <th colspan="2" style="text-align: center; font-weight: bold;">
                      <?php echo number_format($grandTotal,2); ?>
                    </th>
                  </tr>
                  <tr>
                    <th colspan="4" style="text-align: right; font-weight: bold;">
                      Remise
                    </th>
                    <th colspan="2" style="text-align: center; font-weight: bold;">
                      <?php echo number_format($discount,2); ?>
                    </th>
                  </tr>
                  <tr>
                    <th colspan="4" style="text-align: right; font-weight: bold; color: green;">
                      Total net
                    </th>
                    <th colspan="2" style="text-align: center; font-weight: bold; color: green;">
                      <?php echo number_format($netTotal,2); ?>
                    </th>
                  </tr>
                  <?php
                } else {
                  ?>
                  <tr>
                    <td colspan="6" style="color:red; text-align:center">
                      Aucun article trouvé dans le panier
                    </td>
                  </tr>
                  <?php
                }
                ?>
              </tbody>
            </table>
          </div><!-- widget-content -->
        </div><!-- widget-box -->
      </div>
    </div><!-- row-fluid -->

  </div><!-- container-fluid -->
</div><!-- content -->

<!-- Footer -->
<?php include_once('includes/footer.php'); ?>

<!-- SCRIPTS -->
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
