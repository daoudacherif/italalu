<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['imsaid']==0)) {
  header('location:logout.php');
  } else{



  ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<title>Système de Gestion d'Inventaire || Gérer la Sous-catégorie</title>
<?php include_once('includes/cs.php');?>
</head>
<body>

<?php include_once('includes/header.php');?>
<?php include_once('includes/sidebar.php');?>


<div id="content">
  <div id="content-header">
    <div id="breadcrumb"> <a href="dashboard.php" title="Aller à l'accueil" class="tip-bottom"><i class="icon-home"></i> Accueil</a> <a href="manage-subcategory.php" class="current">Gérer la Sous-catégorie</a> </div>
    <h1>Gérer la Sous-catégorie</h1>
  </div>
  <div class="container-fluid">
    <hr>
    <div class="row-fluid">
      <div class="span12">
        
       
     
        
        <div class="widget-box">
          <div class="widget-title"> <span class="icon"><i class="icon-th"></i></span>
            <h5>Gérer la Catégorie</h5>
          </div>
          <div class="widget-content nopadding">
            <table class="table table-bordered data-table">
              <thead>
                <tr>
                  <th>N°</th>
                  <th>Nom de la Catégorie</th>
                  <th>Nom de la Sous-catégorie</th>
                  <th>Statut</th>
                  <th>Date de Création</th>
                  <th>Action</th>
                  
                </tr>
              </thead>
              <tbody>
                <?php
$ret=mysqli_query($con,"select tblcategory.CategoryName,tblsubcategory.ID as sid,tblsubcategory.SubCategoryname,tblsubcategory.Status,tblsubcategory.CreationDate from  tblsubcategory join tblcategory on tblcategory.ID=tblsubcategory.CatID");
$cnt=1;
while ($row=mysqli_fetch_array($ret)) {

?>
                <tr class="gradeX">
                  <td><?php echo $cnt;?></td>
                  <td><?php  echo $row['CategoryName'];?></td>
                  <td><?php  echo $row['SubCategoryname'];?></td>
                  <?php if($row['Status']=="1"){ ?>

                     <td><?php echo "Actif"; ?></td>
<?php } else { ?>                  <td><?php echo "Inactif"; ?>
                  </td>
                  <?php } ?>
                  <td><?php  echo $row['CreationDate'];?></td>
                  <td class="center"><a href="editsubcategory.php?scid=<?php echo $row['sid'];?>"><i class=" icon-edit"></i></a></td>
                </tr>
                <?php 
$cnt=$cnt+1;
}?> 
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--Footer-part-->
<?php include_once('includes/footer.php');?>
<!--end-Footer-part-->
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
<?php } ?>