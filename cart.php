<?php
session_start();
require_once 'config.php'; // Підключення до бази даних

// Перевірка на авторизацію користувача
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username']; // Припускаємо, що ім'я користувача зберігається в сесії

// Якщо була спроба додати товар в кошик
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    
    // Отримуємо назву товару
    $query = "SELECT name FROM products WHERE product_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    $product_name = $product['name']; // Реальна назва товару

    // Перевірка чи товар вже є в кошику
    $query = "SELECT * FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_item) {
        // Якщо товар є, оновлюємо кількість
        $new_quantity = $existing_item['quantity'] + $quantity;
        $query = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$new_quantity, $user_id, $product_id]);
    } else {
        // Якщо товару немає в кошику, додаємо новий запис
        $query = "INSERT INTO cart (user_id, product_id, username, productname, quantity) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $product_id, $username, $product_name, $quantity]);
    }
}

// Якщо була спроба видалити товар з кошика
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];

    // Отримуємо кількість товару для відновлення на складі
    $query = "SELECT quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $product_id]);
    $cart_item = $stmt->fetch(PDO::FETCH_ASSOC);
    $quantity = $cart_item['quantity'];

    // Видалення товару з кошика
    $query = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id, $product_id]);

    // Повернення кількості товару на склад
    $query = "UPDATE products SET stock = stock + ? WHERE product_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$quantity, $product_id]);
}

// Якщо користувач оформляє замовлення
if (isset($_POST['checkout'])) {
    // Логіка для оформлення замовлення
    $query = "INSERT INTO orders (user_id, total_price) 
              SELECT user_id, SUM(price * quantity) FROM cart 
              WHERE user_id = ? GROUP BY user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);

    // Отримуємо всі товари з кошика
    $query = "SELECT * FROM cart WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Оновлюємо кількість товарів в таблиці products
    foreach ($cart_items as $item) {
        $query = "UPDATE products SET stock = stock - ? WHERE product_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Очищаємо кошик після оформлення
    $query = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
}

// Якщо користувач хоче очистити кошик
if (isset($_POST['clear_cart'])) {
    // Очищення кошика
    $query = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
}

// Отримуємо всі товари кошика для поточного користувача з БД
$query = "SELECT * FROM cart WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Кошик</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="cart-container">
        <h2>Ваш кошик</h2>
        <div id="cart-container">
            <?php if (empty($cart_items)): ?>
                <p style="text-align: center;">Ваш кошик порожній</p>
            <?php else: ?>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <p><?= $item['productname']; ?> - <?= $item['quantity']; ?> шт.</p>
                        <a href="cart.php?remove=<?= $item['product_id']; ?>" class="remove-btn">Видалити</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="cart-buttons">
            <form action="cart.php" method="POST">
                <button class="checkout-btn" type="submit" name="checkout">Оформити замовлення</button>
            </form>
            <form action="cart.php" method="POST">
                <button class="clear-btn" type="submit" name="clear_cart">Очистити кошик</button>
            </form>
        </div>
        
        <p style="text-align: center; margin-top: 20px;">
            <a href="index.php" style="color: #007bff; text-decoration: none;">Повернутися до магазину</a>
        </p>
    </div>
</body>
</html>