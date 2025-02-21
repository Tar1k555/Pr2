<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_service'])) {
    $service_id = $_POST['service_id'];
    $car_brand = $_POST['car_brand'];
    $car_model = $_POST['car_model'];
    $date_time = $_POST['date_time'];

    $query = "INSERT INTO sessions (user_id, service_id, car_brand, car_model, date_time) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisss", $user_id, $service_id, $car_brand, $car_model, $date_time);
    $stmt->execute();

    header("Location: sessions.php");
    exit();
}

$services_query = "SELECT * FROM services";
$services_result = $conn->query($services_query);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Послуги</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <header>
        <h1>Послуги</h1>
        <a href="index.php">На головну</a>
    </header>

    <main>
        <form method="POST">
            <label for="car_brand">Марка авто:</label>
            <input type="text" name="car_brand" id="car_brand" required>

            <label for="car_model">Модель авто:</label>
            <input type="text" name="car_model" id="car_model" required>

            <label for="service_id">Оберіть послугу:</label>
            <select name="service_id" id="service_id">
                <?php while ($service = $services_result->fetch_assoc()): ?>
                    <option value="<?= $service['id'] ?>"><?= $service['name'] ?> (<?= $service['price'] ?> грн)</option>
                <?php endwhile; ?>
            </select>

            <label for="date_time">Дата і час:</label>
            <input type="datetime-local" name="date_time" id="date_time" required>

            <button type="submit" name="book_service">Замовити</button>
        </form>
    </main>
</body>
</html>
