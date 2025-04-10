<?php
// catalog.php
require 'config.php';

// Define furniture products (static list) including images array for slider
$products = [
    [
        "article" => "CHAIR001",
        "name"    => "Стул",
        "images"  => [
            "assets/images/chair/chair1.png",
            "assets/images/chair/chair2.png",
            "assets/images/chair/chair3.png",
            "assets/images/chair/chair4.png"
        ],
        "price"   => 1000
    ],
    [
        "article" => "TABLE001",
        "name"    => "Стол",
        "images"  => [
          "assets/images/table/table1.jpg",
          "assets/images/table/table2.jpg",
          "assets/images/table/table3.jpg",
          "assets/images/table/table4.jpg"
          
        ],
        "price"   => 5000
    ],
    [
        "article" => "CABINET001",
        "name"    => "Шкаф",
        "images"  => [
          "assets/images/cabinet/cabinet1.jpg",
          "assets/images/cabinet/cabinet2.jpg",
          "assets/images/cabinet/cabinet3.jpg",
          "assets/images/cabinet/cabinet4.jpg"
        ],
        "price"   => 15000
    ],
    [
        "article" => "BED001",
        "name"    => "Кровать",
        "images"  => [
           "assets/images/bed/bed1.jpg",
            "assets/images/bed/bed2.jpg",
            "assets/images/bed/bed3.jpg",
            "assets/images/bed/bed4.jpg"
        ],
        "price"   => 25000
    ]
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог мебели</title>
    <link rel="stylesheet" href="assets/styles.css">
    <style>
      /* Use a CSS grid for a responsive product layout */
      .product-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
          gap: 20px;
          justify-items: center;
          padding: 10px;
      }
      .product-card {
          border: 1px solid #ccc;
          padding: 15px;
          border-radius: 8px;
          text-align: center;
          width: 100%;
          max-width: 220px;
          background: #222;
          color: #fff;
          box-sizing: border-box;
      }
      /* Adjust slider: force images to fill the slider area */
      .product-slider {
          position: relative;
          width: 100%;
          height: 150px;
          overflow: hidden;
          margin-bottom: 10px;
      }
      .product-slider .slides img {
          width: 100%;
          height: 150px;
          object-fit: cover;
          display: none;
      }
      .product-slider .slides img.active {
          display: block;
      }
      .product-slider .prev,
      .product-slider .next {
          position: absolute;
          top: 50%;
          transform: translateY(-50%);
          background-color: rgba(0, 0, 0, 0.5);
          color: #fff;
          border: none;
          padding: 5px;
          cursor: pointer;
          border-radius: 50%;
          font-size: 12px;
      }
      .product-slider .prev {
          left: 5px;
      }
      .product-slider .next {
          right: 5px;
      }
      /* Remove absolute positioning from the product button */
      .product-card .btn {
          display: inline-block;
          padding: 8px 12px;
          background-color: rgba(0, 123, 255, 0.8);
          color: #fff;
          border-radius: 8px;
          text-decoration: none;
          transition: background-color 0.3s ease;
          margin-top: 10px;
      }
      .product-card .btn:hover {
          background-color: rgba(0, 123, 255, 1);
      }
    </style>
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
    <h1>Каталог мебели</h1>
    <div class="product-grid">
        <?php foreach($products as $product): ?>
            <div class="product-card">
                <div class="product-slider" data-index="0">
                    <div class="slides">
                        <?php foreach($product['images'] as $img): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php endforeach; ?>
                    </div>
                    <button class="prev" onclick="prevSlide(this)">&#10094;</button>
                    <button class="next" onclick="nextSlide(this)">&#10095;</button>
                </div>
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p>Артикул: <?php echo htmlspecialchars($product['article']); ?></p>
                <p>Цена: <?php echo htmlspecialchars($product['price']); ?> руб.</p>
                <a href="product.php?article=<?php echo urlencode($product['article']); ?>" class="btn">Просмотреть товар</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Initialize sliders: show the first image for each slider
document.querySelectorAll('.product-slider').forEach(function(slider) {
    const slides = slider.querySelectorAll('.slides img');
    if (slides.length > 0) {
        slides[0].classList.add('active');
        slider.setAttribute('data-index', '0');
    }
});

function nextSlide(button) {
    const slider = button.closest('.product-slider');
    let index = parseInt(slider.getAttribute('data-index')) || 0;
    const slides = slider.querySelectorAll('.slides img');
    slides[index].classList.remove('active');
    index = (index + 1) % slides.length;
    slides[index].classList.add('active');
    slider.setAttribute('data-index', index);
}

function prevSlide(button) {
    const slider = button.closest('.product-slider');
    let index = parseInt(slider.getAttribute('data-index')) || 0;
    const slides = slider.querySelectorAll('.slides img');
    slides[index].classList.remove('active');
    index = (index - 1 + slides.length) % slides.length;
    slides[index].classList.add('active');
    slider.setAttribute('data-index', index);
}
</script>
</body>
</html>