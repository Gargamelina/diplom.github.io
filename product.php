<?php
require 'config.php';
session_start();

// Product data array...
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

    if (!$customerName || !$phone || !$address || $quantity < 1) {
        $orderMessage = "Пожалуйста, заполните все поля, укажите адрес и количество (минимум 1).";
    } else {
        $stmt = $conn->prepare("INSERT INTO orders (article, product_name, price, quantity, customer_name, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$article, $product['name'], $product['price'], $quantity, $customerName, $phone, $address])) {
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
    <div class="single-product-container">
        <div class="product-slider-single">
            <div class="slides">
                <?php foreach ($product['images'] as $i=>$img): ?>
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>"
                    class="<?php echo $i===0 ? 'active' : ''; ?>">
                <?php endforeach; ?>
            </div>
            <button class="slider-btn prev" onclick="moveSlide(-1)">&#10094;</button>
            <button class="slider-btn next" onclick="moveSlide(1)">&#10095;</button>
        </div>
        <div class="single-product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-price">
                <?php echo htmlspecialchars($product['price']); ?> руб.
            </div>
            <p>Артикул: <?php echo htmlspecialchars($article); ?></p>
        </div>
        <form method="post" class="order-form" autocomplete="off">
            <legend>Оформление заказа</legend>
            <label>
                ФИО:
                <input type="text" name="customer_name" required>
            </label>
            <label>
                Номер телефона:
                <input type="text" name="phone" required>
            </label>
            <label>
                Адрес доставки:
                <input type="text" name="address" required>
            </label>
            <label>
                Количество товара:
                <input type="number" name="quantity" min="1" value="1" required>
            </label>
            <button type="submit" class="btn">Оформить заказ</button>
            <?php if ($orderMessage): ?>
                <div class="order-feedback"><?php echo htmlspecialchars($orderMessage); ?></div>
            <?php elseif (isset($_GET['order']) && $_GET['order'] === 'success'): ?>
                <div class="order-feedback success">Ваш заказ успешно оформлен!</div>
            <?php endif; ?>
        </form>
    </div>
    <script>
        // JS-слайдер для карточки товара
        (function(){
            const slides = document.querySelectorAll('.product-slider-single .slides img');
            let current = 0;
            function showSlide(idx) {
                slides[current].classList.remove('active');
                current = (idx + slides.length) % slides.length;
                slides[current].classList.add('active');
            }
            window.moveSlide = function(step) {
                showSlide(current + step);
            };
        })();
    </script>
</body>
</html>