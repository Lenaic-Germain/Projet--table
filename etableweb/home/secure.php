<?php
$host = "localhost"; // Hôte de la base de données
$dbname = "Etable"; // Nom de la base de données
$username = "admin"; // Nom d'utilisateur MySQL
$password = "admin"; // Mot de passe MySQL (laisser vide pour XAMPP)

try {
    
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Définir le mode d'erreur PDO sur Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
