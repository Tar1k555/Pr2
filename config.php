<?php
$servername = "localhost";
$username = "root";
$password = "";   
$dbname = "autoservicenew";

// Створюємо підключення
$conn = new mysqli($servername, $username, $password, $dbname,3308);

// Перевіряємо підключення
if ($conn->connect_error) {
    die("Помилка підключення: " . $conn->connect_error);
}
?>