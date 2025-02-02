<?php
session_start();

// Вихід з сесії
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php"); // Оновлення сторінки після виходу
    exit();
}

// Перевірка, чи користувач авторизований
if (isset($_SESSION['user'])) {
    $auth_button_text = "Вийти (" . $_SESSION['user'] . ")";
    $auth_button_action = "window.location.href='index.php?logout=true'"; // Вихід без перекидання на іншу сторінку
} else {
    $auth_button_text = "Авторизуватися";
    $auth_button_action = "window.location.href='login.php'"; // Авторизація у тій же вкладці
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Autoservice</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script src="script.js" defer></script>
</head>
<body>
    <!-- Верхній прямокутник (navbar) -->
    <div class="header-container">
        <h1>Автосервіс "Mecha"</h1>
        <div class="buttons-container">
            <button class="dynamic-button" onclick="showSection('services')">Послуги</button>
            <button class="dynamic-button" onclick="showSection('products')">Товари</button>
            <button class="dynamic-button" onclick="showSection('contact')">Про нас</button>
        </div>

        <!-- Кнопка авторизації -->
        <button id="authButton" onclick="<?php echo $auth_button_action; ?>">
            <?php echo $auth_button_text; ?>
        </button>
    </div>
</body>
