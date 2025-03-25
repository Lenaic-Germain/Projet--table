<?php
session_start();
require_once "includes/db.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Handle Add Cow
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_cow'])) {
    $cow_name = $_POST['cow_name'];
    $age = $_POST['age'];
    $milk_amount = $_POST['milk_amount'];
    $temperature = $_POST['temperature'];

    $stmt = $conn->prepare("INSERT INTO cows (cow_name, age, milk_amount, temperature) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sidd", $cow_name, $age, $milk_amount, $temperature);
    $stmt->execute();

    // Log the action
    $cow_id = $conn->insert_id;
    $log_stmt = $conn->prepare("INSERT INTO cow_logs (cow_id, action) VALUES (?, 'Added')");
    $log_stmt->bind_param("i", $cow_id);
    $log_stmt->execute();

    header("Location: cows.php");
    exit();
}

// Handle Delete Cow
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_cow'])) {
    $cow_id = $_POST['cow_id'];

    // Log the action before deleting
    $log_stmt = $conn->prepare("INSERT INTO cow_logs (cow_id, action) VALUES (?, 'Deleted')");
    $log_stmt->bind_param("i", $cow_id);
    $log_stmt->execute();

    // Delete the cow
    $stmt = $conn->prepare("DELETE FROM cows WHERE cow_id=?");
    $stmt->bind_param("i", $cow_id);
    $stmt->execute();

    // Check if the table is now empty
    $result = $conn->query("SELECT COUNT(*) AS total FROM cows");
    $row = $result->fetch_assoc();
    if ($row['total'] == 0) {
        $conn->query("ALTER TABLE cows AUTO_INCREMENT = 1"); // Reset ID counter
    }

    header("Location: cows.php");
    exit();
}

// Fetch all cows
$result = $conn->query("SELECT * FROM cows ORDER BY cow_id DESC");
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
    <h1>Manage Cows</h1>

    <!-- Add New Cow Form -->
    <div class="form-container">
        <h2>Add New Cow</h2>
        <form method="post">
            <input type="text" name="cow_name" required placeholder="Cow Name">
            <input type="number" name="age" required placeholder="Age">
            <input type="number" step="0.1" name="milk_amount" required placeholder="Milk Amount (L)">
            <input type="number" step="0.1" name="temperature" required placeholder="Temperature (°C)">
            <button type="submit" name="add_cow">Add Cow</button>
        </form>
    </div>

    <!-- List of Cows -->
    <h2>Current Cows</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Milk Amount (L)</th>
            <th>Temperature (°C)</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row["cow_id"]); ?></td>
                <td>
                    <a href="cow_details.php?cow_id=<?php echo $row['cow_id']; ?>" 
                       style="text-decoration: none; color: #003366; font-weight: bold;">
                        <?php echo htmlspecialchars($row["cow_name"]); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($row["age"]); ?></td>
                <td><?php echo htmlspecialchars($row["milk_amount"]); ?></td>
                <td><?php echo htmlspecialchars($row["temperature"]); ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="cow_id" value="<?php echo $row['cow_id']; ?>">
                        <button type="submit" name="delete_cow" style="background-color: red;">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Recent Changes Log -->
    <h2>Recent Changes</h2>
    <table border="1">
        <tr>
            <th>Log ID</th>
            <th>Cow ID</th>
            <th>Action</th>
            <th>Timestamp</th>
        </tr>
        <?php
        $log_result = $conn->query("SELECT * FROM cow_logs WHERE action IN ('Added', 'Deleted') ORDER BY log_time DESC LIMIT 10"); // Show last 10 changes
        while ($log = $log_result->fetch_assoc()):
        ?>
            <tr>
                <td><?php echo htmlspecialchars($log["log_id"]); ?></td>
                <td><?php echo htmlspecialchars($log["cow_id"] ?? 'N/A'); ?></td>
                <td><?php echo htmlspecialchars($log["action"]); ?></td>
                <td><?php echo htmlspecialchars($log["log_time"]); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
