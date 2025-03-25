<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'includes/header.php';
?>

<h2>Welcome to Your Dashboard</h2>
<p>Your role: <?php echo $_SESSION['role']; ?></p>

<?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="admin/add_cow.php">Add Cow Data</a>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
