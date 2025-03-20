<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');

// Vérifie que l'admin est connecté
if (strlen($_SESSION['imsaid'] == 0)) {
  header('location:logout.php');
  exit;
}

if (isset($_POST['catid'])) {
    $cid = intval($_POST['catid']);

    // Requête pour récupérer les sous-catégories actives
    $query = mysqli_query($con, "
        SELECT ID, SubCategoryname
        FROM tblsubcategory
        WHERE CatID='$cid'
          AND Status='1'
        ORDER BY SubCategoryname ASC
    ");

    $count = mysqli_num_rows($query);
    if ($count > 0) {
        // Afficher une option par défaut
        echo '<option value="">Sélectionnez une sous-catégorie</option>';
        // Boucle sur les résultats
        while ($rw = mysqli_fetch_array($query)) {
            echo '<option value="'.$rw['ID'].'">'.$rw['SubCategoryname'].'</option>';
        }
    } else {
        // S'il n'y a pas de sous-catégorie pour cette catégorie
        echo '<option value="">Aucune sous-catégorie disponible</option>';
    }
} else {
    // Si catid n'est pas envoyé
    echo '<option value="">Erreur: pas de catégorie reçue</option>';
}
?>
