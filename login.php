<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin']; // Added admin flag
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Неправильный Логин или Пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
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
    <video class="video-background" autoplay loop muted>
        <source src="assets/video/vecteezy_abstract-gradient-fluid-animation-background_27222497.mp4" type="video/mp4">
        Ваш браузер не поддерживает видео.
    </video>
    <div class="container">
        <h1>Вход</h1>
        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Логин" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Войти</button>
        </form>
        <p>Ещё нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div>
</body>
</html>