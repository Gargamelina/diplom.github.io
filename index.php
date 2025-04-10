<?php
session_start();
require 'config.php';

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT id, filename, uploader, description FROM files");
    $stmt->execute();
    $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FileShare - Главная страница</title>
    <link rel="stylesheet" href="assets/styles.css">
</head>
<body>
    <!-- Header -->
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
        <a href="admin/admin.php">Admin Panel</a>
      <?php endif; ?>
      <a href="logout.php">Выйти (<?= htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES) ?>)</a>
    <?php endif; ?>
  </nav>
</header>

    <video class="video-background" autoplay loop muted>
        <source src="assets/video/vecteezy_abstract-gradient-fluid-animation-background_27222497.mp4" type="video/mp4">
        Ваш браузер не поддерживает видео.
    </video>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="container">
            <h1>Добро пожаловать, <?php echo htmlspecialchars($username); ?>!</h1>
            <div id="fileList" class="file-list"></div>
            <button type="button" id="openUploadModal" class="btn">Выгрузить файл</button>
            <a href="logout.php" class="btn btn-logout">Выйти</a>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="banner">
                <h1>Добро пожаловать на FileShare!</h1>
                <p>Платформа для удобного обмена файлами между пользователями.</p>
            </div>
            <div class="description">
                <p>У нас вы можете легко загружать, скачивать и делиться файлами. Начните сегодня: зарегистрируйтесь или авторизуйтесь, если у вас уже есть аккаунт!</p>
            </div>
            <div class="buttons">
                <a href="login.php" class="btn btn-login">Войти</a>
                <a href="register.php" class="btn btn-register">Зарегистрироваться</a>
            </div>
        </div>
    <?php endif; ?>
    <script src="assets/script.js"></script>
</body>
</html>