<!DOCTYPE html>
<html lang="fr">
<head>
<title>Système de Gestion des Inventaires || Rapport entre deux dates</title>
<?php include_once('includes/cs.php');?>
</head>
<body>

<?php include_once('includes/header.php');?>
<?php include_once('includes/sidebar.php');?>


<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> 
      <a href="dashboard.php" title="Aller à l'accueil" class="tip-bottom">
        <i class="icon-home"></i> Accueil
      </a> 
      <a href="stock-report.php" class="current">Rapport entre deux dates</a> 
    </div>
    <h1>Détails des Produits</h1>
  </div>
  <div class="container-fluid">
    <hr>
    <div class="row-fluid">
      <div class="span12">
        
        <div class="widget-box">
          <div class="widget-title"> 
            <span class="icon"><i class="icon-th"></i></span>
            <?php
$fdate=$_POST['fromdate'];
$tdate=$_POST['todate'];

?>
<h5 align="center" style="color:blue">Rapport d'inventaire du <?php echo $fdate?> au <?php echo $tdate?></h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered data-table">
              <thead>
                <tr>
                  <th>N°</th>
                  <th>Nom du Produit</th>
                  <th>Nom de la Catégorie</th>
                   <th>Nom de la Sous-Catégorie</th>
                  <th>Nom de la Marque</th>
                  <th>Numéro de Modèle</th>
                  <th>Stock</th>
                  <th>Stock Restant</th>
                  <th>Statut</th>
                </tr>
              </thead>
              <tbody>
                <?php
$ret=mysqli_query($con,"select tblcategory.CategoryName,tblsubcategory.SubCategoryname as subcat,tblproducts.ProductName,tblproducts.BrandName,tblproducts.ID as pid,tblproducts.Status,tblproducts.CreationDate,tblproducts.ModelNumber,tblproducts.Stock,sum(tblcart.ProductQty) as selledqty from tblproducts join tblcategory on tblcategory.ID=tblproducts.CatID join tblsubcategory on tblsubcategory.ID=tblproducts.SubcatID left join tblcart  on tblproducts.ID=tblcart.ProductId where date(tblproducts.CreationDate) between '$fdate' and '$tdate' group by tblproducts.ProductName");
$qty=$result['selledqty'];
$num=mysqli_num_rows($ret);
if($num>0){
$cnt=1;
while ($row=mysqli_fetch_array($ret)) {
$qty=$row['selledqty'];
?>
                <tr class="gradeX">
                  <td><?php echo $cnt;?></td>
                  <td><?php  echo $row['ProductName'];?></td>
                  <td><?php  echo $row['CategoryName'];?></td>
                  <td><?php  echo $row['subcat'];?></td>
                  <td><?php  echo $row['BrandName'];?></td>
                  <td><?php  echo $row['ModelNumber'];?></td>
                  <td><?php  echo $row['Stock'];?></td>
                   <td><?php  echo ($_SESSION['rqty']=$row['Stock']-$qty);?></td>
                  <?php if($row['Status']=="1"){ ?>

                     <td><?php echo "Actif"; ?></td>
<?php } else { ?>                  
                     <td><?php echo "Inactif"; ?></td>
                  <?php } ?>               
                </tr>
                <?php 
$cnt=$cnt+1;
} } else { ?>
  <tr>
    <td colspan="8"> Aucun enregistrement trouvé.</td>
  </tr>
   
<?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--Pied de Page-->
<?php include_once('includes/footer.php');?>
<!--Fin Pied de Page-->
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
<?php?>
