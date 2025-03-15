<?php
// Utilisation des variables d'environnement pour les informations sensibles
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'David4321';
$dbname = getenv('DB_NAME') ?: 'italalu';

$con = mysqli_connect($host, $user, $password, $dbname);

if (mysqli_connect_errno()) {
    // Enregistrement de l'erreur dans un fichier de log
    error_log("Connection Failed: " . mysqli_connect_error());
    // Affichage d'un message générique pour l'utilisateur
    echo "Database connection failed. Please try again later.";
    exit();
}

// Vous pouvez maintenant utiliser $con pour interagir avec la base de données
?>
