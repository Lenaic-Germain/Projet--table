<?php
require 'secure.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["user"]) && !empty($_POST["password"])) {
        $user = htmlspecialchars($_POST["user"]);
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT); // Hachage du mot de passe

        try {
            $stmt = $pdo->prepare("INSERT INTO users (user, password) VALUES (:user, :password)");
            $stmt->execute([
                ':user' => $user,
                ':password' => $password
            ]);

            // Redirection vers login.php avec message de succès
            header("Location: login.php?success=1");
            exit();
        } catch (PDOException $e) {
            // Si l'erreur est une duplication (user UNIQUE), on affiche un message
            if ($e->getCode() == 23000) {
                echo "❌ Erreur : ce nom d'utilisateur est déjà pris.";
            } else {
                echo "❌ Erreur lors de l'enregistrement : " . $e->getMessage();
            }
        }
    } else {
        echo "⚠️ Tous les champs doivent être remplis.";
    }
}
?>
