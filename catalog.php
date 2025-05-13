<?php
require 'config.php';
require_once 'header.php';
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

</head>
<body>
    <div class="dynamic-bg"></div>
<div class="catalog-container">
    <h1 style="text-align:center; color:#21306c; font-size:32px; font-weight:700; letter-spacing:0.3px; margin-bottom:32px;">Каталог мебели</h1>
    <div class="products-grid">
        <?php foreach($products as $product): ?>
            <div class="product-card">
                <div class="product-slider" data-index="0">
                    <div class="slides">
                        <?php foreach($product['images'] as $i=>$img): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>"
                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                            class="<?php echo $i===0 ? 'active' : ''; ?>">
                        <?php endforeach; ?>
                    </div>
                    <button class="prev" onclick="prevSlide(this)">&#10094;</button>
                    <button class="next" onclick="nextSlide(this)">&#10095;</button>
                </div>
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p>Артикул: <?php echo htmlspecialchars($product['article']); ?></p>
                <div class="product-price"><?php echo htmlspecialchars($product['price']); ?> руб.</div>
                <a href="product.php?article=<?php echo urlencode($product['article']); ?>" class="btn">Просмотреть товар</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<script>
// Унификация JS-слайдера для всех карточек
document.querySelectorAll('.product-slider').forEach(function(slider) {
    const slides = slider.querySelectorAll('.slides img');
    let idx = 0;
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