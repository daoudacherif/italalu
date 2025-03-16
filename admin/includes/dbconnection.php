<?php
<<<<<<< HEAD
$con = mysqli_connect("mysql.hostinger.com", "u553063725_italalu", "Daoudacherif4321", "u553063725_italalu");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
} else {
    echo "Connected successfully"; // Remove this after testing
}
=======
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
>>>>>>> 2fa1a665205de4d55591ec5f88ac902ac1e369d9
?>
