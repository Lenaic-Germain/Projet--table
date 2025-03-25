<?php
// Inclure le fichier de connexion à la base de données
require 'secure.php';

session_start(); // Démarrer la session pour stocker les informations de l'utilisateur

$error_message = ""; // Message d'erreur par défaut

// Vérification lorsque le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les valeurs du formulaire
    $user = $_POST['user'];
    $password = $_POST['password'];

    try {
        // Préparer la requête SQL pour vérifier si l'utilisateur existe dans la base de données
        $sql = "SELECT * FROM users WHERE user = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':username', $user, PDO::PARAM_STR);
        $stmt->execute();

        // Vérifier si l'utilisateur existe
        if ($stmt->rowCount() > 0) {
            // L'utilisateur existe, vérifier le mot de passe
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                // Connexion réussie
                $_SESSION['user_id'] = $row['id']; // Stocker l'ID de l'utilisateur dans la session
                $_SESSION['user'] = $row['user']; // Stocker le nom d'utilisateur dans la session

                // Rediriger l'utilisateur vers une page protégée (ex. dashboard)
                header("Location: alertes_1.php");
                exit();
            } else {
                // Le mot de passe est incorrect
                $error_message = "Mot de passe incorrect.";
            }
        } else {
            // L'utilisateur n'existe pas
            $error_message = "Nom d'utilisateur introuvable.";
        }
    } catch (PDOException $e) {
        $error_message = "Erreur de base de données: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="connexion-container">
        <h2>Connexion</h2>

        <!-- Affichage du message d'erreur si la connexion échoue -->
        <?php if (!empty($error_message)): ?>
            <p class="error-message" style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST" id="registerForm">
            <div class="input-group">
                <label for="user">Nom d'utilisateur :</label>
                <input type="text" id="user" class="user" name="user" required>
            </div>

            <div class="input-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" class="password" name="password" required>
                
                <button type="button" class="toggle-password" data-target="password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <a href="autentification_1.php" class="forgotten_password">
                Mot de passe oublié ?
            </a>
            <button id="inscrire" class="inscrire" type="submit">Se connecter</button>
        </form>
    </div>

    <script>
        // Afficher ou masquer les mots de passe
        document.querySelectorAll(".toggle-password").forEach(button => {
            button.addEventListener("click", function() {
                var target = document.getElementById(this.getAttribute("data-target"));
                var icon = this.querySelector("i");

                if (target.type === "password") {
                    target.type = "text";
                    icon.classList.remove("fa-eye");
                    icon.classList.add("fa-eye-slash");
                } else {
                    target.type = "password";
                    icon.classList.remove("fa-eye-slash");
                    icon.classList.add("fa-eye");
                }
            });
        });
    </script>
</body>
</html>
