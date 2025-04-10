<?php
session_start();

// Проверка, что пользователь администратор
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php");
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
<header class="admin-header">
    <div class="logo">
        <a href="admin.php" style="color: #fff;">Админ Панель</a>
    </div>
    <nav>
        <a href="orders.php">Заказы</a>
        <a href="files.php">Файлы</a>
        <a href="users.php">Пользователи</a>
        <a href="admin.php">Главная</a>
        <a href="invoices.php">PDF накладные</a>
    </nav>
    <div class="user-info">
        Добро пожаловать, <?= htmlspecialchars($_SESSION['username'] ?? "Администратор", ENT_QUOTES) ?> |
        <a href="../logout.php" style="color: #ff4444;">Выйти</a>
    </div>
</header>
<!-- Левая часть шаблона: контент админ-панели начинается ниже -->
<div class="admin-content">
<!-- Здесь будет выводитьс�� основной контент страницы -->
 
<!-- Замечание: Этот header можно подключать через include 'header.php'; в файлах админ-панели -->
 
</div>
</body>
</html>