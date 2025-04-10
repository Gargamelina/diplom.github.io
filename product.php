<?php
// product.php
require 'config.php';

// Define products with multiple images for slider (ensure images exist under assets/images/)
// Added price field for each product (matching catalog values)
$products = [
    "CHAIR001"   => [
        "name"   => "Стул",
        "images" => [
            "assets/images/chair/chair1.png",
            "assets/images/chair/chair2.png",
            "assets/images/chair/chair3.png",
            "assets/images/chair/chair4.png"
        ],
        "price"  => 1000
    ],
    "TABLE001"   => [
        "name"   => "Стол",
        "images" => [
            "assets/images/table/table1.jpg",
            "assets/images/table/table2.jpg",
            "assets/images/table/table3.jpg",
            "assets/images/table/table4.jpg"

        ],
        "price"  => 5000
    ],
    "CABINET001" => [
        "name"   => "Шкаф",
        "images" => [
            "assets/images/cabinet/cabinet1.jpg",
            "assets/images/cabinet/cabinet2.jpg",
            "assets/images/cabinet/cabinet3.jpg",
            "assets/images/cabinet/cabinet4.jpg"

        ],
        "price"  => 15000
    ],
    "BED001"     => [
        "name"   => "Кровать",
        "images" => [
            "assets/images/bed/bed1.jpg",
            "assets/images/bed/bed2.jpg",
            "assets/images/bed/bed3.jpg",
            "assets/images/bed/bed4.jpg"
        ],
        "price"  => 25000
    ],
];

if (!isset($_GET['article']) || !isset($products[$_GET['article']])) {
    echo "Товар не найден.";
    exit();
}

$article = $_GET['article'];
$product = $products[$article];

$orderMessage = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $customerName = trim($_POST['customer_name'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $address      = trim($_POST['address'] ?? '');
    $quantity     = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    // Validate all required fields including quantity must be at least 1
    if (!$customerName || !$phone || !$address || $quantity < 1) {
        $orderMessage = "Пожалуйста, заполните все поля, укажите адрес и количество (минимум 1).";
    } else {
        // Insert order data into database.
        // Inserts the product_name, price, and quantity based on the product's article.
        $stmt = $conn->prepare("INSERT INTO orders (article, product_name, price, quantity, customer_name, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$article, $product['name'], $product['price'], $quantity, $customerName, $phone, $address])) {
            // Redirect to avoid re-submission if the page is refreshed.
            header("Location: product.php?article=" . urlencode($article) . "&order=success");
            exit();
        } else {
            $orderMessage = "Ошибка при оформлении заказа.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="assets/styles.css">
</head>

<body>
    <header class="site-header">
        <div class="logo">
            <a href="index.php" aria-label="На главную">LOGO</a>
        </div>
        <nav class="nav-menu">
            <a href="index.php">Главная</a>
            <a href="catalog.php">Каталог мебели</a>
            <?php if (empty($_SESSION['user_id'])): ?>
                <a href="login.php">Вход</a>
                <a href="register.php">Регистрация</a>
            <?php else: ?>
                <a href="cabinet.php">Личный кабинет</a>
                <a href="dashboard.php">Доска файлов</a>
                <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                    <a href="admin.php">Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php">Выйти (<?= htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES) ?>)</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <h1><?php echo htmlspecialchars($product['name']); ?></h1>
        <!-- Product Image Slider -->
        <div class="slider">
            <div class="slides">
                <?php foreach ($product['images'] as $img): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php endforeach; ?>
            </div>
            <button class="prev" onclick="plusSlides(-1)">&#10094;</button>
            <button class="next" onclick="plusSlides(1)">&#10095;</button>
        </div>

        <p>Артикул: <?php echo htmlspecialchars($article); ?></p>
        <p>Цена: <?php echo htmlspecialchars($product['price']); ?> руб.</p>

        <form method="post">
            <fieldset>
                <legend>Оформление заказа</legend>
                <label>
                    ФИО:<br>
                    <input type="text" name="customer_name" required>
                </label><br><br>
                <label>
                    Номер телефона:<br>
                    <input type="text" name="phone" required>
                </label><br><br>
                <label>
                    Адрес доставки:<br>
                    <input type="text" name="address" required>
                </label><br><br>
                <label>
                    Количество товара:<br>
                    <input type="number" name="quantity" min="1" value="1" required>
                </label>
            </fieldset>
            <br>
            <button type="submit" class="btn">Оформить заказ</button>
        </form>
        <?php if ($orderMessage): ?>
            <p><?php echo htmlspecialchars($orderMessage); ?></p>
        <?php elseif (isset($_GET['order']) && $_GET['order'] === 'success'): ?>
            <p>Ваш заказ успешно оформлен!</p>
        <?php endif; ?>
    </div>
    <script src="assets/script.js"></script>
</body>

</html>