<?php
session_start();
require 'config.php';

// Вихід з акаунту
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['user'])) {
    $auth_button_text = "Авторизовано: " . $_SESSION['username'];
    $auth_button_action = "window.location.href='index.php?logout=true'";
    $logout_button = "<button onclick=\"window.location.href='index.php?logout=true'\">Вийти</button>";
    $change_user_button = "<button onclick=\"window.location.href='login.php'\">Змінити користувача</button>";
} else {
    $auth_button_text = "Авторизуватися";
    $auth_button_action = "window.location.href='login.php'";
    $logout_button = "";
    $change_user_button = "";
}

// AJAX-запити для отримання даних
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_brands':
            $query = "SELECT DISTINCT brand FROM vehicles";
            $result = $conn->query($query);
            $brands = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode($brands);
            exit;

        case 'get_models':
            if (!isset($_GET['brand'])) exit();
            $brand = $_GET['brand'];
            $stmt = $conn->prepare("SELECT DISTINCT model FROM vehicles WHERE brand = ?");
            $stmt->bind_param("s", $brand);
            $stmt->execute();
            $models = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            echo json_encode($models);
            exit;

        case 'get_products':
            $query = "SELECT * FROM products";
            $result = $conn->query($query);
            echo json_encode($result->fetch_all(MYSQLI_ASSOC));
            exit;

        case 'get_services':
            if (!isset($_GET['brand'], $_GET['model'])) exit();
            $brand = $_GET['brand'];
            $model = $_GET['model'];
            $stmt = $conn->prepare("
                SELECT sp.name, sp.description, sp.priceserv 
                FROM service_prices sp
                JOIN vehicles v ON sp.vehicle_id = v.id
                WHERE v.brand = ? AND v.model = ?
            ");
            $stmt->bind_param("ss", $brand, $model);
            $stmt->execute();
            echo json_encode($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
            exit;
    }
}

// Обробка запису на послугу
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    if (!isset($_POST['booking_date'], $_POST['booking_time'], $_POST['service_id'])) {
        exit("Помилка! Всі поля обов'язкові.");
    }

    $user_id = $_SESSION['user_id'];
    $service_id = $_POST['service_id'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];

    $stmt = $conn->prepare("SELECT name FROM service_prices WHERE id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $service_name = $stmt->get_result()->fetch_assoc()['name'] ?? 'Невідома послуга';

    $stmt = $conn->prepare("
        INSERT INTO sessions (user_id, service_id, username, servicename, session_date, status) 
        VALUES (?, ?, ?, ?, ?, 'Очікується')
    ");
    $stmt->bind_param("iisss", $user_id, $service_id, $_SESSION['user'], $service_name, "$booking_date $booking_time");
    $stmt->execute();

    echo "Ваше замовлення підтверджено!";
    exit();
}

// Обробка додавання товару в кошик
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Перевірка, чи є товар у кошику
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $cart = &$_SESSION['cart'];
    if (isset($cart[$product_id])) {
        $cart[$product_id] += $quantity;
    } else {
        $cart[$product_id] = $quantity;
    }

    echo "Товар додано до кошика!";
    exit();
}

// Обробка оформлення замовлення
if (isset($_POST['checkout'])) {
    // Логіка оформлення замовлення
    echo "Замовлення оформлено!";
    exit();
}

?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Автосервіс "Mecha"</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <script src="script.js" defer></script>
</head>
<body>
<div class="header-container">
    <h1>Автосервіс "Mecha"</h1>
    <button class="service-button" onclick="toggleServices()">Послуги</button>
    <button class="product-button" onclick="toggleProducts()">Товари</button>
    <button class="book-button" onclick="toggleSessions()">Сеанси</button>
    <button class="cart-button" onclick="toggleCart()">Кошик</button>       
    <button class="about-button" onclick="ToggleAbout('contacts')">Про нас</button>
    <button id="authButton" onclick="<?php echo $auth_button_action; ?>">
        <?php echo $auth_button_text; ?>
    </button>
</div>

<!-- Секція Послуг -->
<div id="services" class="section" style="display: none;">
    <div class="content-container">
    <h2 class="section-title">Наші послуги</h2>
        <div id="vehicle-selection-container">
            <label for="brand">Марка:</label>
            <select id="brand" onchange="loadModels()">
                <option value="">Оберіть марку</option>
            </select>
            <label for="model">Модель:</label>
            <select id="model" disabled onchange="loadServices()">
                <option value="">Оберіть модель</option>
            </select>
        </div>
        <div id="services-container"></div>
    </div>
</div>

<!-- Модальне вікно для запису на послугу -->
<div id="bookingModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h3>Виберіть дату та час</h3>
        <input type="date" id="bookingDate" min="<?= date('Y-m-d'); ?>" />
        <input type="time" id="bookingTime" />
        <button onclick="confirmBooking()">Підтвердити</button>
    </div>
</div>

<!-- Секція Товарів -->
<div id="products" class="section" style="display: none;">
<h2 class="section-title">Наші товари</h2>
    <div id="products-container"></div>
</div>

<!-- Сеанси -->
<div id="sessions" class="section" style="display: none;">
<h2 class="section-title">Ваші сеанси</h2>
    <div id="sessions-container"></div>
</div>

<!-- Кошик -->
<div id="cart" class="section" style="display: none;">
    <h2 class="section-title">Ваш кошик</h2>
    <div id="cart-container"></div>
    <button onclick="checkout()">Оформити замовлення</button>
</div>

<!-- Про нас -->
<div id="contacts" class="section" style="display: none;background-color: rgba(0, 0, 0, 0.8); 
     padding: 20px;
     max-width: 600px; 
     margin: 0 auto">
<h2>Про нас</h2>
    <p>Автосервіс "Mecha" - професійне обслуговування вашого авто.</p>
    <p>Адреса: м. Київ, вул. Технічна, 12</p>
    <p>Телефон: +38 (044) 123-45-67</p>
    <p>Email: info@mechauto.com</p>
</div>

<script>
    // Завантаження марок
    function loadBrands() {
        fetch('index.php?action=get_brands')
            .then(response => response.json())
            .then(brands => {
                const brandSelect = document.getElementById('brand');
                brands.forEach(brand => {
                    const option = document.createElement('option');
                    option.value = brand.brand;
                    option.textContent = brand.brand;
                    brandSelect.appendChild(option);
                });
            });
    }

    // Завантаження моделей
    function loadModels() {
        const brand = document.getElementById('brand').value;
        if (brand) {
            fetch(`index.php?action=get_models&brand=${brand}`)
                .then(response => response.json())
                .then(models => {
                    const modelSelect = document.getElementById('model');
                    modelSelect.disabled = false;
                    modelSelect.innerHTML = '<option value="">Оберіть модель</option>';
                    models.forEach(model => {
                        const option = document.createElement('option');
                        option.value = model.model;
                        option.textContent = model.model;
                        modelSelect.appendChild(option);
                    });
                });
        }
    }

    // Завантаження послуг
    function loadServices() {
        const brand = document.getElementById('brand').value;
        const model = document.getElementById('model').value;
        if (brand && model) {
            fetch(`index.php?action=get_services&brand=${brand}&model=${model}`)
                .then(response => response.json())
                .then(services => {
                    const servicesContainer = document.getElementById('services-container');
                    servicesContainer.innerHTML = '';
                    services.forEach(service => {
                        const serviceDiv = document.createElement('div');
                        serviceDiv.classList.add('service');
                        serviceDiv.innerHTML = `
                            <h4>${service.name}</h4>
                            <p>${service.description}</p>
                            <p>Ціна: ${service.priceserv} грн</p>
                            <button onclick="openBookingModal(${service.id})">Записатися</button>
                        `;
                        servicesContainer.appendChild(serviceDiv);
                    });
                });
        }
    }

    function openBookingModal(serviceId) {
        document.getElementById('bookingModal').style.display = 'block';
        document.getElementById('bookingModal').setAttribute('data-service-id', serviceId);
    }

    function closeModal() {
        document.getElementById('bookingModal').style.display = 'none';
    }

    function confirmBooking() {
        const serviceId = document.getElementById('bookingModal').getAttribute('data-service-id');
        const bookingDate = document.getElementById('bookingDate').value;
        const bookingTime = document.getElementById('bookingTime').value;

        if (!bookingDate || !bookingTime) {
            alert('Будь ласка, виберіть дату і час.');
            return;
        }

        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `service_id=${serviceId}&booking_date=${bookingDate}&booking_time=${bookingTime}`
        }).then(response => response.text()).then(message => {
            alert(message);
            closeModal();
        });
    }

    function toggleSection(sectionId) {
    const section = document.getElementById(sectionId);
    
    // Перевіряємо, чи секція відкрита
    if (section.style.display === 'block') {
        section.style.display = 'none'; // Якщо відкрита — закриваємо
    } else {
        // Закриваємо всі секції перед відкриттям нової
        document.querySelectorAll('.section').forEach(otherSection => {
            otherSection.style.display = 'none';
        });

        // Відкриваємо потрібну секцію
        section.style.display = 'block';
    }
}

    function toggleServices() {
        toggleSection('services');
    }

    function toggleProducts() {
        toggleSection('products');
    }

    function toggleSessions() {
        toggleSection('sessions');
        loadSessions();
    }

    function toggleCart() {
        toggleSection('cart');
        loadCart();
    }

    function ToggleAbout() {
        toggleSection('contacts');
    }

    function loadSessions() {
        fetch('index.php?action=get_sessions')
            .then(response => response.json())
            .then(sessions => {
                const sessionsContainer = document.getElementById('sessions-container');
                sessionsContainer.innerHTML = '';
                sessions.forEach(session => {
                    const sessionDiv = document.createElement('div');
                    sessionDiv.className = 'session-item';
                    sessionDiv.innerHTML = `
                        <h3>${session.servicename}</h3>
                        <p>Дата: ${session.session_date}</p>
                        <p>Статус: ${session.status}</p>
                    `;
                    sessionsContainer.appendChild(sessionDiv);
                });
            });
    }

    function loadCart() {
        fetch('index.php?action=get_cart')
            .then(response => response.json())
            .then(cartItems => {
                const cartContainer = document.getElementById('cart-container');
                cartContainer.innerHTML = '';
                cartItems.forEach(item => {
                    const cartDiv = document.createElement('div');
                    cartDiv.className = 'cart-item';
                    cartDiv.innerHTML = `
                        <h3>${item.name}</h3>
                        <p>Ціна: ${item.price} грн</p>
                        <p>Кількість: ${item.quantity}</p>
                    `;
                    cartContainer.appendChild(cartDiv);
                });
            });
    }

    function checkout() {
        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'checkout=true'
        }).then(response => response.text()).then(message => {
            alert(message);
            loadCart();
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadBrands();
    });
</script>
</body>
</html>