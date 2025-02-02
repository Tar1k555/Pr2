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
    $auth_button_action = "window.location.href='login.php'";
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
    <!-- Верхній прямокутник -->
    <div class="header-container">
        <h1>Автосервіс "Mecha"</h1>
        <div class="buttons-container">
           <!-- навігаційні кнопки -->
            <button class="service-button" onclick="showSection('services')">Послуги</button>
            <button class="product-button" onclick="showSection('products')">Товари</button>
            <button class="about-button" onclick="showSection('contact')">Про нас</button>
        </div>

        <!-- Кнопка авторизації -->
        <button id="authButton" onclick="<?php echo $auth_button_action; ?>">
            <?php echo $auth_button_text; ?>
        </button>
    </div>
</body>
<!-- Кнопка Послуги -->
<button class="service-button" onclick="toggleSection('services')">Послуги</button>

<!-- Контент для Послуг -->
<div id="services" class="section" style="display:none;">
    <div class="content-container">
        <h2>Наші послуги</h2>
        
        <!-- Послуга 1: Евакуатор -->
        <div class="service">
            <h3>Евакуатор</h3>
            <p>Швидка евакуація транспортних засобів на будь-яку відстань. Забезпечимо ваш спокій на дорозі.</p>
            <p><strong>Вартість:</strong> 1500 грн</p>
            <button onclick="orderService('Евакуатор')">Замовити</button>
        </div>

        <!-- Послуга 2: Обслуговування автомобіля -->
        <div class="service">
            <h3>Обслуговування автомобіля</h3>
            <p>Професійне обслуговування вашого автомобіля, включаючи заміну масла, фільтрів та багато іншого.</p>
            <p><strong>Вартість:</strong> 800 грн</p>
            <button onclick="orderService('Обслуговування автомобіля')">Замовити</button>
        </div>

        <!-- Послуга 3: Ремонт гальмівної системи -->
        <div class="service">
            <h3>Ремонт гальмівної системи</h3>
            <p>Професійний ремонт гальмівної системи для забезпечення вашої безпеки на дорозі.</p>
            <p><strong>Вартість:</strong> 1200 грн</p>
            <button onclick="orderService('Ремонт гальмівної системи')">Замовити</button>
        </div>

        <!-- Послуга 4: Технічна діагностика -->
        <div class="service">
            <h3>Технічна діагностика</h3>
            <p>Комплексна перевірка вашого автомобіля на наявність несправностей та проблем.</p>
            <p><strong>Вартість:</strong> 500 грн</p>
            <button onclick="orderService('Технічна діагностика')">Замовити</button>
        </div>

        <!-- Послуга 5: Ремонт двигуна -->
        <div class="service">
            <h3>Ремонт двигуна</h3>
            <p>Професійний ремонт двигуна для різних марок та моделей автомобілів.</p>
            <p><strong>Вартість:</strong> 2500 грн</p>
            <button onclick="orderService('Ремонт двигуна')">Замовити</button>
        </div>
    </div>

<body>
    <!-- Вибір авто (переміщений вище) -->
    <div id="vehicle-selection" style="display: none;">
        <h3>Оберіть марку та модель авто</h3>
        <select id="brand" onchange="showModels(this.value)">
            <option value="">Виберіть марку</option>
            <option value="audi">Audi</option>
            <option value="bmw">BMW</option>
            <option value="ford">Ford</option>
            <option value="hyundai">Hyundai</option>
            <option value="mercedes">Mercedes</option>
            <option value="nissan">Nissan</option>
            <option value="opel">Opel</option>
            <option value="peugeot">Peugeot</option>
            <option value="toyota">Toyota</option>
            <option value="volkswagen">Volkswagen</option>
        </select>

        <div id="models-container" style="display: none;">
            <select id="model" onchange="updatePrice()">
                <option value="">Виберіть модель</option>
            </select>
        </div>
        <p id="service-price">Оберіть марку та модель</p>
    </div>

    <!-- Секція послуг -->
    <div class="service-section" id="services" style="display: none;">
        <h2>Наші послуги</h2>
        <div class="service-item">
            <h3>Евакуатор</h3>
            <p>Опис послуги та ціна: 2000 грн.</p>
            <button onclick="orderService('Евакуатор')">Замовити</button>
        </div>
        <div class="service-item">
            <h3>Ремонт мотору</h3>
            <p>Опис послуги та ціна: 5000 грн.</p>
            <button onclick="orderService('Ремонт мотору')">Замовити</button>
        </div>
        <!-- Інші послуги -->
    </div>
</body>
    <script src="script.js" defer></script>