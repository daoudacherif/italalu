<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Ensure the admin is logged in
if (strlen($_SESSION['imsaid'] == 0)) {
    header('location:logout.php');
    exit;
}

// ==========================
// 1) Add to Cart
// ==========================
if (isset($_POST['addtocart'])) {
    $productId = intval($_POST['productid']);
    $quantity  = intval($_POST['quantity']);
    $price     = floatval($_POST['price']);  // <-- user-typed price

    if ($quantity <= 0)  $quantity = 1;
    if ($price    < 0)  $price    = 0;

    // Check if this product is already in the cart
    $checkCart = mysqli_query($con, "
        SELECT ID, ProductQty 
        FROM tblcart 
        WHERE ProductId='$productId' AND IsCheckOut=0 
        LIMIT 1
    ");
    if (mysqli_num_rows($checkCart) > 0) {
        // Update existing cart row
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
        // Insert a new cart row
        mysqli_query($con, "
            INSERT INTO tblcart(ProductId, ProductQty, Price, IsCheckOut) 
            VALUES('$productId', '$quantity', '$price', '0')
        ");
    }
    echo "<script>alert('Product added to cart!');</script>";
    echo "<script>window.location.href='cart.php'</script>";
    exit;
}

// ==========================
// 2) Remove from Cart
// ==========================
if (isset($_GET['delid'])) {
    $rid = intval($_GET['delid']);
    mysqli_query($con, "DELETE FROM tblcart WHERE ID='$rid'");
    echo "<script>alert('Product removed from cart');</script>";
    echo "<script>window.location.href = 'cart.php'</script>";
    exit;
}

// ==========================
// 3) Handle Discount Form
// ==========================
// We store the discount in a session variable so it applies only to this order.
if (isset($_POST['applyDiscount'])) {
    $_SESSION['discount'] = floatval($_POST['discount']);
    // Reload to recalc
    echo "<script>window.location.href='cart.php'</script>";
    exit;
}

// If discount not set, default to 0
$discount = isset($_SESSION['discount']) ? $_SESSION['discount'] : 0;

// ==========================
// 4) Checkout & Create Invoice
// ==========================
if (isset($_POST['submit'])) {
    $custname      = $_POST['customername'];
    $custmobilenum = $_POST['mobilenumber'];
    $modepayment   = $_POST['modepayment'];

    // Recompute the cart total
    $cartQuery = mysqli_query($con, "
        SELECT ProductQty, Price 
        FROM tblcart
        WHERE IsCheckOut=0
    ");
    $grandTotal = 0;
    while ($row = mysqli_fetch_assoc($cartQuery)) {
        $grandTotal += ($row['ProductQty'] * $row['Price']);
    }

    // Subtract discount => net total (but we do NOT store discount in DB)
    $netTotal = $grandTotal - $discount;
    if ($netTotal < 0) $netTotal = 0;

    // Generate unique BillingId
    $billingnum = mt_rand(100000000, 999999999);

    // Mark cart items as checked out, store final amount in tblcustomer
    // (But do NOT store discount anywhere—only netTotal.)
    $query  = "UPDATE tblcart 
               SET BillingId='$billingnum', IsCheckOut=1 
               WHERE IsCheckOut=0;";

    $query .= "INSERT INTO tblcustomer(
                 BillingNumber,
                 CustomerName,
                 MobileNumber,
                 ModeofPayment,
                 FinalAmount
               ) VALUES(
                 '$billingnum',
                 '$custname',
                 '$custmobilenum',
                 '$modepayment',
                 '$netTotal'
               );";

    $result = mysqli_multi_query($con, $query);
    if ($result) {
        // If needed, we can store the invoice ID in session
        $_SESSION['invoiceid'] = $billingnum;

        // Clear the discount session so it doesn't persist for next order
        unset($_SESSION['discount']);

        echo "<script>alert('Invoice created successfully. Billing number is $billingnum');</script>";
        echo "<script>window.location.href='invoice.php'</script>";
        exit;
    } else {
        echo "<script>alert('Error during checkout');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inventory Management System | Cart</title>
    <?php include_once('includes/cs.php'); ?>
</head>
<body>
<?php include_once('includes/header.php'); ?>
<?php include_once('includes/sidebar.php'); ?>

<div id="content">
    <div id="content-header">
        <div id="breadcrumb">
          <a href="dashboard.php" title="Go to Home" class="tip-bottom">
            <i class="icon-home"></i> Home
          </a>
          <a href="cart.php" class="current">Products Cart</a>
        </div>
        <h1>Products Cart</h1>
    </div>
    <div class="container-fluid">
        <hr>

        <!-- ========== SEARCH FORM ========== -->
        <div class="row-fluid">
          <div class="span12">
            <form method="get" action="cart.php" class="form-inline">
              <label>Search Products:</label>
              <input type="text" name="searchTerm" class="span3" placeholder="Product name or model..." />
              <button type="submit" class="btn btn-primary">Search</button>
            </form>
          </div>
        </div>
        <hr>

        <!-- ========== SEARCH RESULTS ========== -->
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
                <h4>Search Results for "<em><?php echo htmlentities($searchTerm); ?></em>"</h4>
                <?php if ($count > 0) { ?>
                  <table class="table table-bordered table-striped">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>SubCategory</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Default Price</th>
                        <th>Custom Price</th>
                        <th>Qty</th>
                        <th>Add</th>
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
                          <!-- Let user override the price here -->
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
                              <i class="icon-plus"></i> Add
                            </button>
                          </form>
                        </td>
                      </tr>
                    <?php } ?>
                    </tbody>
                  </table>
                <?php } else { ?>
                  <p style="color:red;">No matching products found.</p>
                <?php } ?>
              </div>
            </div>
            <hr>
        <?php } // end if searchTerm ?>

        <!-- ========== CART DISPLAY & DISCOUNT & CHECKOUT ========== -->
        <div class="row-fluid">
            <div class="span12">

                <!-- DISCOUNT FORM (not stored in DB) -->
                <form method="post" class="form-inline" style="text-align:right;">
                  <label>Discount:</label>
                  <input type="number" name="discount" step="any" 
                         value="<?php echo $discount; ?>" 
                         style="width:80px;" />
                  <button class="btn btn-info" type="submit" name="applyDiscount">Apply</button>
                </form>
                <hr>

                <!-- Checkout Form (Customer Info) -->
                <form method="post" class="form-horizontal" name="submit">
                    <div class="control-group">
                      <label class="control-label">Customer Name :</label>
                      <div class="controls">
                        <input type="text" class="span11" id="customername" name="customername" required />
                      </div>
                    </div>
                    <div class="control-group">
                      <label class="control-label">Customer Mobile Number :</label>
                      <div class="controls">
                        <input type="text" class="span11" id="mobilenumber" name="mobilenumber" required
                               maxlength="10" pattern="[0-9]+"/>
                      </div>
                    </div>
                    <div class="control-group">
                      <label class="control-label">Mode of payment :</label>
                      <div class="controls">
                        <label><input type="radio" name="modepayment" value="cash" checked> Cash</label>
                        <label><input type="radio" name="modepayment" value="card"> Card</label>
                      </div>
                    </div>
                    <div class="text-center">
                      <button class="btn btn-primary" type="submit" name="submit">
                        Checkout & Create Invoice
                      </button>
                    </div>
                </form>

                <div class="widget-box">
                  <div class="widget-title">
                    <span class="icon"><i class="icon-th"></i></span>
                    <h5>Products in Cart</h5>
                  </div>
                  <div class="widget-content nopadding">
                    <table class="table table-bordered" style="font-size: 15px">
                      <thead>
                        <tr>
                          <th>S.NO</th>
                          <th>Product Name</th>
                          <th>Qty</th>
                          <th>Price (per unit)</th>
                          <th>Total</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php
                      // Retrieve cart items (IsCheckOut=0)
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
                               onclick="return confirm('Do you really want to remove this item?');">
                               <i class="icon-trash"></i>
                            </a>
                          </td>
                        </tr>
                      <?php
                          $cnt++;
                        }
                        // Show the grand total minus discount (ephemeral)
                        $netTotal = $grandTotal - $discount;
                        if ($netTotal < 0) $netTotal = 0;
                      ?>
                        <tr>
                          <th colspan="4" style="text-align: right; font-weight: bold;">
                            Grand Total
                          </th>
                          <th colspan="2" style="text-align: center; font-weight: bold;">
                            <?php echo number_format($grandTotal,2); ?>
                          </th>
                        </tr>
                        <tr>
                          <th colspan="4" style="text-align: right; font-weight: bold;">
                            Discount
                          </th>
                          <th colspan="2" style="text-align: center; font-weight: bold;">
                            <?php echo number_format($discount,2); ?>
                          </th>
                        </tr>
                        <tr>
                          <th colspan="4" style="text-align: right; font-weight: bold; color: green;">
                            Net Total
                          </th>
                          <th colspan="2" style="text-align: center; font-weight: bold; color: green;">
                            <?php echo number_format($netTotal,2); ?>
                          </th>
                        </tr>
                      <?php } else { ?>
                        <tr>
                          <td colspan="6" style="color:red; text-align:center">
                            No items found in cart
                          </td>
                        </tr>
                      <?php } ?>
                      </tbody>
                    </table>
                  </div>
                </div><!-- widget-box -->
            </div>
        </div><!-- row-fluid -->
    </div><!-- container-fluid -->
</div><!-- content -->

<!--Footer-part-->
<?php include_once('includes/footer.php'); ?>
<!--end-Footer-part-->

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