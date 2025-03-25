<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

require_once "includes/db.php";

// Fetch cows from the database
$sql = "SELECT * FROM cows";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Cows</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section>
    <h1>Our Cows</h1>
    <?php if ($result->num_rows > 0): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Breed</th>
                <th>Age</th>
                <th>Image</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["id"]); ?></td>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["breed"]); ?></td>
                    <td><?php echo htmlspecialchars($row["age"]); ?></td>
                    <td><img src="cow_images/<?php echo htmlspecialchars($row["image"]); ?>" width="100"></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No cows found.</p>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
