<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Check if admin is logged in
if (strlen($_SESSION['imsaid'] == 0)) {
    header('location:logout.php');
    exit;
}

// =======================
// 1) Handle new arrival submission
// =======================
if (isset($_POST['submit'])) {
    $productID   = intval($_POST['productid']);
    $quantity    = intval($_POST['quantity']);
    $arrivalDate = $_POST['arrivaldate'];
    $comments    = mysqli_real_escape_string($con, $_POST['comments']);

    if ($productID <= 0 || $quantity <= 0) {
        echo "<script>alert('Invalid product or quantity');</script>";
    } else {
        // Insert into tblproductarrivals
        $sqlInsert = "INSERT INTO tblproductarrivals(ProductID, ArrivalDate, Quantity, Comments)
                      VALUES('$productID', '$arrivalDate', '$quantity', '$comments')";
        $queryInsert = mysqli_query($con, $sqlInsert);

        if ($queryInsert) {
            // Update tblproducts stock
            // (Stock = Stock + quantity)
            $sqlUpdate = "UPDATE tblproducts
                          SET Stock = Stock + $quantity
                          WHERE ID='$productID'";
            mysqli_query($con, $sqlUpdate);

            echo "<script>alert('Product arrival recorded and stock updated!');</script>";
        } else {
            echo "<script>alert('Error inserting arrival record');</script>";
        }
    }
    // Refresh or redirect as needed
    echo "<script>window.location.href='arrival.php'</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inventory Management | Product Arrivals</title>
    <?php include_once('includes/cs.php'); ?>
</head>
<body>

<!-- Header + Sidebar -->
<?php include_once('includes/header.php'); ?>
<?php include_once('includes/sidebar.php'); ?>

<div id="content">
    <div id="content-header">
      <div id="breadcrumb">
        <a href="dashboard.php" title="Go to Home" class="tip-bottom">
          <i class="icon-home"></i> Home
        </a>
        <a href="product-arrivals.php" class="current">Product Arrivals</a>
      </div>
      <h1>Manage Product Arrivals (Stock In)</h1>
    </div>

    <div class="container-fluid">
        <hr>

        <!-- ================== NEW ARRIVAL FORM ================== -->
        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                  <div class="widget-title">
                    <span class="icon"><i class="icon-align-justify"></i></span>
                    <h5>Add New Product Arrival</h5>
                  </div>
                  <div class="widget-content nopadding">
                    <form method="post" class="form-horizontal">
                      
                      <!-- Arrival Date -->
                      <div class="control-group">
                        <label class="control-label">Arrival Date :</label>
                        <div class="controls">
                          <input type="date" name="arrivaldate" value="<?php echo date('Y-m-d'); ?>" required />
                        </div>
                      </div>

                      <!-- Product -->
                      <div class="control-group">
                        <label class="control-label">Select Product :</label>
                        <div class="controls">
                          <select name="productid" required>
                            <option value="">-- Choose Product --</option>
                            <?php
                            // Load products from tblproducts
                            $prodQuery = mysqli_query($con, "SELECT ID, ProductName FROM tblproducts ORDER BY ProductName ASC");
                            while ($prodRow = mysqli_fetch_assoc($prodQuery)) {
                                echo '<option value="'.$prodRow['ID'].'">'.$prodRow['ProductName'].'</option>';
                            }
                            ?>
                          </select>
                        </div>
                      </div>

                      <!-- Quantity -->
                      <div class="control-group">
                        <label class="control-label">Quantity :</label>
                        <div class="controls">
                          <input type="number" name="quantity" min="1" value="1" required />
                        </div>
                      </div>

                      <!-- Comments (Optional) -->
                      <div class="control-group">
                        <label class="control-label">Comments (optional) :</label>
                        <div class="controls">
                          <input type="text" name="comments" placeholder="e.g. Supplier info, invoice #, etc." />
                        </div>
                      </div>

                      <div class="form-actions">
                        <button type="submit" name="submit" class="btn btn-success">
                          Record Arrival
                        </button>
                      </div>
                    </form>
                  </div><!-- widget-content nopadding -->
                </div><!-- widget-box -->
            </div>
        </div><!-- row-fluid -->

        <hr>

        <!-- ================== RECENT ARRIVALS LIST ================== -->
        <div class="row-fluid">
            <div class="span12">
                <div class="widget-box">
                  <div class="widget-title">
                    <span class="icon"><i class="icon-th"></i></span>
                    <h5>Recent Product Arrivals</h5>
                  </div>
                  <div class="widget-content nopadding">
                    <table class="table table-bordered data-table">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>Arrival Date</th>
                          <th>Product</th>
                          <th>Quantity</th>
                          <th>Comments</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        // Join tblproductarrivals with tblproducts to show product name
                        $sqlArrivals = "
                          SELECT a.ID as arrivalID, a.ArrivalDate, a.Quantity, a.Comments,
                                 p.ProductName
                          FROM tblproductarrivals a
                          LEFT JOIN tblproducts p ON p.ID = a.ProductID
                          ORDER BY a.ID DESC
                          LIMIT 50
                        ";
                        $arrivalsQuery = mysqli_query($con, $sqlArrivals);
                        $cnt = 1;
                        while ($row = mysqli_fetch_assoc($arrivalsQuery)) {
                            ?>
                            <tr>
                              <td><?php echo $cnt; ?></td>
                              <td><?php echo $row['ArrivalDate']; ?></td>
                              <td><?php echo $row['ProductName']; ?></td>
                              <td><?php echo $row['Quantity']; ?></td>
                              <td><?php echo $row['Comments']; ?></td>
                            </tr>
                            <?php
                            $cnt++;
                        }
                        ?>
                      </tbody>
                    </table>
                  </div><!-- widget-content nopadding -->
                </div><!-- widget-box -->
            </div>
        </div><!-- row-fluid -->

    </div><!-- container-fluid -->
</div><!-- content -->

<?php include_once('includes/footer.php'); ?>

<!-- Scripts -->
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
