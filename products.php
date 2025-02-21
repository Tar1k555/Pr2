<?php
include "config.php";
$result = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Товари</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <h1>Каталог товарів</h1>
    <div id="products">
        <?php while ($row = $result->fetch_assoc()) : ?>
            <div class="product">
                <img src="img/<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>" class="product-img">
                <p><?php echo $row['name']; ?> - <?php echo $row['price']; ?> грн</p>
                <button onclick="addToCart(<?php echo $row['id']; ?>, '<?php echo $row['name']; ?>')">Додати в кошик</button>
            </div>
        <?php endwhile; ?>
    </div>

    <h2>Ваш кошик</h2>
    <div id="cart"></div>
</body>
</html>
