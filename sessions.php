<?php
session_start();
include 'config.php';

// Отримання даних сеансу для редагування (AJAX)
if (isset($_GET['id'])) {
    $session_id = $_GET['id'];
    
    $query = "SELECT s.id, s.car_brand, s.car_model, srv.name AS service_name, s.date_time
              FROM sessions s
              JOIN services srv ON s.service_id = srv.id
              WHERE s.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $session = $result->fetch_assoc();
        echo json_encode($session);
    } else {
        echo json_encode([]);
    }
    exit(); // Важливо додати exit(), щоб код далі не виконувався
}

// Перевірка авторизації
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT s.id, s.car_brand, s.car_model, srv.name AS service_name, s.date_time 
          FROM sessions s
          JOIN services srv ON s.service_id = srv.id
          WHERE s.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Обробка оновлення сеансу
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_id'])) {
    $session_id = $_POST['session_id'];
    $car_brand = $_POST['car_brand'];
    $car_model = $_POST['car_model'];
    $service_name = $_POST['service_name'];
    $date_time = $_POST['date_time'];

    $update_query = "UPDATE sessions SET car_brand = ?, car_model = ?, service_id = (SELECT id FROM services WHERE name = ?), date_time = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssi", $car_brand, $car_model, $service_name, $date_time, $session_id);
    $stmt->execute();
    header("Location: sessions.php"); // Перенаправлення після збереження
    exit();
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мої Сеанси</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>Ваші сеанси</h1>
        <a href="index.php">На головну</a>
    </header>

    <main>
        <table>
            <tr>
                <th>Марка</th>
                <th>Модель</th>
                <th>Послуга</th>
                <th>Дата та час</th>
                <th>Редагувати</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['car_brand']) ?></td>
                    <td><?= htmlspecialchars($row['car_model']) ?></td>
                    <td><?= htmlspecialchars($row['service_name']) ?></td>
                    <td><?= htmlspecialchars($row['date_time']) ?></td>
                    <td><button onclick="editSession(<?= $row['id'] ?>)">Редагувати</button></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <div id="edit-session-form" style="display:none;">
            <h2>Редагувати сеанс</h2>
            <form method="POST" action="">
                <input type="hidden" name="session_id" id="session-id">
                <label for="car_brand">Марка</label>
                <input type="text" name="car_brand" id="car_brand" required>
                
                <label for="car_model">Модель</label>
                <input type="text" name="car_model" id="car_model" required>
                
                <label for="service_name">Послуга</label>
                <input type="text" name="service_name" id="service_name" required>
                
                <label for="date_time">Дата та час</label>
                <input type="datetime-local" name="date_time" id="date_time" required>
                
                <button type="submit">Зберегти</button>
            </form>
        </div>
    </main>

    <script>
        function editSession(sessionId) {
            // Завантажити дані сеансу і заповнити форму
            fetch(`sessions.php?id=${sessionId}`) // Виправлено шлях до файлу
                .then(response => response.json())
                .then(session => {
                    if (session) {
                        document.getElementById("session-id").value = session.id;
                        document.getElementById("car_brand").value = session.car_brand;
                        document.getElementById("car_model").value = session.car_model;
                        document.getElementById("service_name").value = session.service_name;
                        document.getElementById("date_time").value = session.date_time;
                        document.getElementById("edit-session-form").style.display = 'block';
                    }
                })
                .catch(error => console.error('Помилка:', error));
        }
    </script>
</body>
</html>