<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['imsaid']==0)) {
  header('location:logout.php');
  } else{
    if(isset($_POST['submit']))
  {
    $pname=$_POST['pname'];
    $category=$_POST['category'];
    $subcategory=$_POST['subcategory'];
    $bname=$_POST['bname'];
    $modelno=$_POST['modelno'];
    $stock=$_POST['stock'];
     $price=$_POST['price'];
    $status=$_POST['status'];
     
    $query=mysqli_query($con, "insert into tblproducts(ProductName,CatID,SubcatID,BrandName,ModelNumber,Stock,Price,Status) value('$pname','$category','$subcategory','$bname','$modelno','$stock','$price','$status')");
    if ($query) {
   
    echo '<script>alert("Le produit a été créé.")</script>';
  }
  else
    {
     echo '<script>alert("Quelque chose s\'est mal passé. Veuillez réessayer")</script>';
    }

  
}
  ?>
<!DOCTYPE html>
<html lang="fr">
<head>
<title>Système de Gestion des Stocks || Ajouter des Produits</title>
<?php include_once('includes/cs.php');?>
<script>
function getSubCat(val) {
  $.ajax({
type:"POST",
url:"get-subcat.php",
data:'catid='+val,
success:function(data){
$("#subcategory").html(data);
}

  });
}
</script>
</head>
<body>

<!--Header-part-->
<?php include_once('includes/header.php');?>
<?php include_once('includes/sidebar.php');?>


<div id="content">
<div id="content-header">
  <div id="breadcrumb"> <a href="dashboard.php" title="Aller à l'accueil" class="tip-bottom"><i class="icon-home"></i> Accueil</a> <a href="add-product.php" class="tip-bottom">Ajouter un Produit</a></div>
  <h1>Ajouter un Produit</h1>
</div>
<div class="container-fluid">
  <hr>
  <div class="row-fluid">
    <div class="span12">
      <div class="widget-box">
        <div class="widget-title"> <span class="icon"> <i class="icon-align-justify"></i> </span>
          <h5>Ajouter un Produit</h5>
        </div>
        <div class="widget-content nopadding">
          <form method="post" class="form-horizontal">
           <div class="control-group">
              <label class="control-label">Nom du Produit :</label>
              <div class="controls">
                <input type="text" class="span11" name="pname" id="pname" value="" required='true' placeholder="Entrez le nom du produit" />
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">Catégorie :</label>
              <div class="controls">
                <select type="text" class="span11" name="category" id="category" onChange="getSubCat(this.value)" value="" required='true' />
                   <option value="">Sélectionnez une Catégorie</option>
                    <?php $query=mysqli_query($con,"select * from tblcategory where Status='1'");
              while($row=mysqli_fetch_array($query))
              {
              ?>      
                  <option value="<?php echo $row['ID'];?>"><?php echo $row['CategoryName'];?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">Nom de la Sous-Catégorie :</label>
              <div class="controls">
                <select type="text" class="span11" name="subcategory" id="subcategory" value="" required='true' />
                  <option value="">Sélectionnez une Sous-Catégorie</option>
                </select>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">Nom de la Marque :</label>
              <div class="controls">
                <select type="text" class="span11" name="bname" id="bname" value="" required='true' />
                  <option value="">Sélectionnez une Marque</option>
                  <?php $query1=mysqli_query($con,"select * from tblbrand where Status='1'");
              while($row1=mysqli_fetch_array($query1))
              {
              ?>
                  <option value="<?php echo $row1['BrandName'];?>"><?php echo $row1['BrandName'];?></option><?php } ?>
                </select>
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">Numéro de Modèle :</label>
              <div class="controls">
                <input type="text" class="span11"  name="modelno" id="modelno" value="" required="true" maxlength="5" placeholder="Entrez le numéro de modèle" />
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">Stock (unités) :</label>
              <div class="controls">
                <input type="text" class="span11"  name="stock" id="stock" value="" required="true" placeholder="Entrez le stock" />
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">Prix (par unité) :</label>
              <div class="controls">
                <input type="text" class="span11" name="price" id="price" value="" required="true" placeholder="Entrez le prix" />
              </div>
            </div>
            <div class="control-group">
              <label class="control-label">Statut :</label>
              <div class="controls">
                <input type="checkbox"  name="status" id="status" value="1" required="true" />
              </div>
            </div>          
           
            <div class="form-actions">
              <button type="submit" class="btn btn-success" name="submit">Ajouter</button>
            </div>
          </form>
        </div>
      </div>
    
    </div>
  </div>
 </div>
</div>
<?php include_once('includes/footer.php');?>
<?php include_once('includes/js.php');?>
</body>
</html>
<?php } ?>