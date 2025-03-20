<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// (Optionnel) Vérifier la session admin
if (strlen($_SESSION['imsaid'] == 0)) {
    header('location:logout.php');
    exit;
}

if (isset($_POST['catid'])) {
    $cid = intval($_POST['catid']);

    // Sélection des sous-catégories actives
    $query = mysqli_query($con, "
        SELECT ID, SubCategoryname 
        FROM tblsubcategory 
        WHERE CatID='$cid' 
          AND Status='1'
        ORDER BY SubCategoryname ASC
    ");

    $count = mysqli_num_rows($query);
    if ($count > 0) {
        // On propose d'abord une option par défaut
        echo '<option value="">Sélectionnez une sous-catégorie</option>';
        while ($row = mysqli_fetch_assoc($query)) {
            echo '<option value="'.$row['ID'].'">'.$row['SubCategoryname'].'</option>';
        }
    } else {
        // Aucun résultat
        echo '<option value="">Aucune sous-catégorie disponible</option>';
    }
} else {
    // Pas de catid reçu
    echo '<option value="">Erreur : aucune catégorie reçue</option>';
}
?>
