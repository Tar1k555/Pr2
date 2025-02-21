<?php
session_start();
require 'config.php';

// Обробка виходу з акаунту
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Авторизація кнопки
if (isset($_SESSION['user'])) {
    $auth_button_text = "Авторизовано: " . $_SESSION['username'];
    $auth_button_action = "window.location.href='index.php?logout=true'";
    $logout_button = "<button onclick=\"window.location.href='index.php?logout=true'\">Вийти</button>";
    $change_user_button = "<button onclick=\"window.location.href='login.php'\">Змінити користувача</button>";
} else {

    $auth_button_action = "window.location.href='login.php'";
    $logout_button = "";
    $change_user_button = "";
}

$message = ""; // Для виводу повідомлень

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        // 🔹 Авторизація
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        $query = "SELECT * FROM login WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $password === $user['password']) { // 🔥 Перевірка без хешування
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user'] = $user['username']; // додали цей рядок
            header("Location: index.php");
            exit();
        } else {
            $message = "❌ Неправильний email або пароль!";
        }
    } elseif (isset($_POST['register'])) {
        // 🔹 Реєстрація
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']); // 🚀 Збереження пароля як звичайний текст
        $role = 'client'; // За замовчуванням клієнт

        // Перевірка, чи існує email або username
        $query = "SELECT * FROM login WHERE email = ? OR username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "⚠️ Email або ім'я користувача вже зайняті!";
        } else {
            // Додаємо в БД
            $query = "INSERT INTO login (email, username, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $email, $username, $password, $role);
            if ($stmt->execute()) {
                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                header("Location: index.php");
                exit();
            } else {
                $message = "❌ Помилка реєстрації!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Логін / Реєстрація</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2 class="section-title">Вхід</h2>
        <?php if ($message) echo "<p class='error'>$message</p>"; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit" name="login">Увійти</button>
        </form>

        <h2 class="section-title">Реєстрація</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="username" placeholder="Ім'я користувача" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit" name="register">Зареєструватися</button>
        </form>


        <!-- Якщо користувач авторизований, додаємо кнопки виходу та зміни користувача -->
        <?php
        if (isset($_SESSION['user'])) {
            echo $logout_button;
            echo $change_user_button;
        }
        ?>
    </div>
    <script src="script.js"></script>
</body>
</html>
