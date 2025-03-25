<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="style.css"> <!-- Lien vers le fichier CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <h2>Inscription</h2>

        <!-- Message de confirmation après redirection -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1) : ?>
        <p class="success-message">Inscription réussie ! Vous pouvez vous connecter.</p>
        <?php endif; ?>

        <form action="config.php" method="POST" id="registerForm">
            <div class="input-group">
                <label for="user">Nom d'utilisateur :</label>
                <input type="text" id="user" class="user" name="user" value="<?php echo isset($user) ? $user : ''; ?>"required>
                <?php if (isset($errors['user'])): ?>
                    <div style="color: red;"><?php echo $errors['user']; ?></div>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" class="password" name="password" required>
                <button type="button" class="toggle-password" data-target="password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <div class="input-group">
                <label for="confirm_password">Réecrivez votre mot de passe :</label>
                <input type="password" id="confirm_password" class="confirm_password"name="confirm_password" required>
                <button type="button" class="toggle-password" data-target="confirm_password">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            
            <p class="error" id="errorMessage"></p>

            <a href="login.php" class="connexion_page">
                Page de connection
            </a>

            <button action="login.php" class="inscrire"type="submit">Valider l'inscription</button>
        </form>
    </div>

    <script>
        // Vérification que les mots de passe correspondent
            document.getElementById("registerForm").addEventListener("submit", function(event) {
        var password = document.getElementById("password").value;
        var confirmPassword = document.getElementById("confirm_password").value;
        var errorMessage = document.getElementById("errorMessage");

        if (password !== confirmPassword) {
            event.preventDefault(); // Empêche l'envoi du formulaire
            errorMessage.textContent = "Les mots de passe ne correspondent pas !";
        } else {
            errorMessage.textContent = ""; // Vider le message d'erreur si les mots de passe correspondent
        }
    });


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
