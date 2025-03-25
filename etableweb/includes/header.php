<?php
session_start();
?>

<header>
    <link rel="stylesheet" href="/css/styles.css"> <!-- Absolute Path Fix -->
    
    <h1>Projet Etable</h1>
    <nav>
        <a href="/index.php">Home</a>
        <a href="/about.php">About</a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/cows.php">Our Cows</a>
            <a href="/auth/logout.php">Logout</a>
        <?php else: ?>
            <a href="/auth/login.php">Login</a>
            <a href="/auth/register.php">Sign Up</a>
        <?php endif; ?>
    </nav>
</header>

<div class="milk-container">
    <div class="milk-wave"></div>
</div>
