<?php
session_start();
require 'config.php';

// Перевірка, чи користувач є адміном
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Обробка додавання товару
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $image_url = $_POST['image_url'];

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock_quantity, image_url) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdis", $name, $description, $price, $stock_quantity, $image_url);
    $stmt->execute();
}

// Обробка видалення товару
if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Обробка додавання послуги
if (isset($_POST['add_service'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $priceserv = $_POST['priceserv'];
    $vehicle_id = $_POST['vehicle_id'];

    $stmt = $conn->prepare("INSERT INTO service_prices (name, description, priceserv, vehicle_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $name, $description, $priceserv, $vehicle_id);
    $stmt->execute();
}

// Обробка видалення послуги
if (isset($_GET['delete_service'])) {
    $id = $_GET['delete_service'];
    $stmt = $conn->prepare("DELETE FROM service_prices WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Отримання списку товарів
$products = $conn->query("SELECT * FROM products")->fetch_all(MYSQLI_ASSOC);

// Отримання списку послуг
$services = $conn->query("SELECT * FROM service_prices")->fetch_all(MYSQLI_ASSOC);

// Отримання списку авто
$vehicles = $conn->query("SELECT * FROM vehicles")->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Адмін-панель</title>
</head>
<body>
    <h1>Адмін-панель</h1>
    <a href="index.php?logout=true">Вийти</a>

    <h2>Додати товар</h2>
    <form method="post">
        <input type="hidden" name="add_product" value="1">
        <label>Назва:</label>
        <input type="text" name="name" required><br>
        <label>Опис:</label>
        <textarea name="description" required></textarea><br>
        <label>Ціна:</label>
        <input type="number" name="price" required><br>
        <label>Кількість на складі:</label>
        <input type="number" name="stock_quantity" required><br>
        <label>URL зображення:</label>
        <input type="text" name="image_url" required><br>
        <button type="submit">Додати</button>
    </form>

    <h2>Список товарів</h2>
    <table>
        <thead>
            <tr>
                <th>Назва</th>
                <th>Опис</th>
                <th>Ціна</th>
                <th>Кількість</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['name'] ?></td>
                    <td><?= $product['description'] ?></td>
                    <td><?= $product['price'] ?></td>
                    <td><?= $product['stock_quantity'] ?></td>
                    <td><a href="admin.php?delete_product=<?= $product['id'] ?>">Видалити</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Додати послугу</h2>
    <form method="post">
        <input type="hidden" name="add_service" value="1">
        <label>Назва:</label>
        <input type="text" name="name" required><br>
        <label>Опис:</label>
        <textarea name="description" required></textarea><br>
        <label>Ціна:</label>
        <input type="number" name="priceserv" required><br>
        <label>Авто:</label>
        <select name="vehicle_id">
            <?php foreach ($vehicles as $vehicle): ?>
                <option value="<?= $vehicle['id'] ?>"><?= $vehicle['brand'] ?> <?= $vehicle['model'] ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Додати</button>
    </form>

    <h2>Список послуг</h2>
    <table>
        <thead>
            <tr>
                <th>Назва</th>
                <th>Опис</th>
                <th>Ціна</th>
                <th>Авто</th>
                <th>Дії</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($services as $service): ?>
                <tr>
                    <td><?= $service['name'] ?></td>
                    <td><?= $service['description'] ?></td>
                    <td><?= $service['priceserv'] ?></td>
                    <td>
                        <?php
                            $vehicle = array_filter($vehicles, function($v) use ($service) {
                                return $v['id'] == $service['vehicle_id'];
                            });
                            $vehicle = reset($vehicle);
                            echo $vehicle['brand'] . ' ' . $vehicle['model'];
                        ?>
                    </td>
                    <td><a href="admin.php?delete_service=<?= $service['id'] ?>">Видалити</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>