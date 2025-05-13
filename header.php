<?php
session_start();

// Проверка, что пользователь администратор
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ Панель - Управление</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <style>
        /* Пример базовых стилей для нового header */
        .admin-header {
            background-color: #222;
            color: #fff;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .admin-header .logo {
            font-size: 24px;
            font-weight: bold;
        }
        .admin-header nav {
            display: flex;
            gap: 15px;
        }
        .admin-header nav a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
        }
        .admin-header nav a:hover {
            text-decoration: underline;
        }
        .admin-header .user-info {
            font-size: 14px;
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
<!-- Левая часть шаблона: контент админ-панели начинается ниже -->
<div class="admin-content">
<!-- Здесь будет выводитьс�� основной контент страницы -->
 
<!-- Замечание: Этот header можно подключать через include 'header.php'; в файлах админ-панели -->
 
</div>
</body>
</html>