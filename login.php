<?php
session_start();

// Якщо користувач вже авторизований, повертаємо його на головну
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Обробка входу
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === 'admin' && $password === 'password') {
        $_SESSION['user'] = $username;
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Невірний логін або пароль";
    }
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизація</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <form method="POST" class="auth-form">
        <h2>Авторизація</h2>
        <?php if (isset($error_message)) echo "<p style='color: red;'>$error_message</p>"; ?>
        <input type="text" name="username" placeholder="Логін" required><br>
        <input type="password" name="password" placeholder="Пароль" required><br>
        <button type="submit">Увійти</button>
    </form>
</body>
</html>