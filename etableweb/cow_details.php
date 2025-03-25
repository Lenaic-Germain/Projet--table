<?php
session_start();
require_once "includes/db.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Get cow_id from URL
if (!isset($_GET['cow_id']) || !is_numeric($_GET['cow_id'])) {
    die("Invalid cow ID.");
}

$cow_id = $_GET['cow_id'];

// Fetch cow data
$stmt = $conn->prepare("SELECT * FROM cows WHERE cow_id = ?");
$stmt->bind_param("i", $cow_id);
$stmt->execute();
$result = $stmt->get_result();
$cow = $result->fetch_assoc();

if (!$cow) {
    die("Cow not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($cow['cow_name']); ?>'s Details</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Chart.js Library -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section>
    <h1><?php echo htmlspecialchars($cow['cow_name']); ?>'s Information</h1>

    <!-- Cow Details -->
    <div class="form-container">
        <p><strong>Age:</strong> <?php echo htmlspecialchars($cow['age']); ?> years</p>
        <p><strong>Milk Amount:</strong> <?php echo htmlspecialchars($cow['milk_amount']); ?> Liters</p>
        <p><strong>Temperature:</strong> <?php echo htmlspecialchars($cow['temperature']); ?> °C</p>
    </div>

    <!-- Graph Section -->
    <h2>Milk Production Over Age</h2>
    <canvas id="cowChart"></canvas>

    <script>
        var ctx = document.getElementById('cowChart').getContext('2d');

        // Create age values from 0 to 20
        var ageLabels = [];
        var milkData = [];
        for (var i = 0; i <= 20; i++) {
            ageLabels.push(i);
            if (i == <?php echo $cow['age']; ?>) {
                milkData.push(<?php echo $cow['milk_amount']; ?>);
            } else {
                milkData.push(0); // Default value for ages without data
            }
        }

        var cowChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ageLabels,
                datasets: [{
                    label: "<?php echo htmlspecialchars($cow['cow_name']); ?>'s Milk Production",
                    data: milkData,
                    backgroundColor: "#003366",
                    borderColor: "#002244",
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    x: {
                        title: { display: true, text: "Age (Years)" }
                    },
                    y: {
                        title: { display: true, text: "Milk Amount (Liters)" },
                        min: 0,
                        max: 50
                    }
                }
            }
        });
    </script>

    <!-- Back Button -->
    <p><a href="cows.php" style="text-decoration: none; font-weight: bold;">← Back to Cows List</a></p>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>
