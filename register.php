<?php
session_start(); // Ensure session is started

require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    // Insert new user into database
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $password]);

    // Automatically log in the new user
    $userId = $conn->lastInsertId();
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;

    // Set the success message to be shown on the dashboard
    $_SESSION['message'] = "Вы успешно вошли в систему.";

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Страница Регистрации</title>
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
        <h1>Страница Регистрации</h1>
        <form action="register.php" method="POST">
            <input type="text" name="username" placeholder="Логин" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <button type="submit">Зарегистрироваться</button>
        </form>
    </div>
</body>

</html>