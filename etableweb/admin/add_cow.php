<?php
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cowID = $_POST['cowID'];
    $milk_temperature = $_POST['milk_temperature'];
    $milk_flow = $_POST['milk_flow'];

    $image = $_FILES['image']['name'];
    $target = "../cow_images/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $stmt = $conn->prepare("INSERT INTO cows (cowID, image, milk_temperature, milk_flow) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdd", $cowID, $image, $milk_temperature, $milk_flow);

    if ($stmt->execute()) {
        echo "Cow added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
