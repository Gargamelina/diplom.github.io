<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Get all files for display (files of all users)
$stmt = $conn->prepare("SELECT * FROM files");
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Доска с файлами</title>
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
        <a href="admin.php">Admin Panel</a>
      <?php endif; ?>
      <a href="logout.php">Выйти (<?= htmlspecialchars($_SESSION['username'] ?? 'User', ENT_QUOTES) ?>)</a>
    <?php endif; ?>
  </nav>
</header>

    <video class="video-background" autoplay loop muted>
        <source src="assets/video/1118458_4k_Pattern_1920x1080.mp4" type="video/mp4">
        Ваш браузер не поддерживает видео.
    </video>
    <div class="container">
        <h1>Добро пожаловать, <?php echo htmlspecialchars($username); ?></h1>
        <a href="logout.php" class="btn btn-login">Выйти</a>
        <!-- Button to open upload modal -->
        <button type="button" id="openUploadModal" class="btn">Выгрузить</button>
        <h2>Файлы пользователей</h2>
        <div class="file-list">
            <?php foreach ($files as $file): 
                $filePath = "uploads/" . htmlspecialchars($file['filename']);
                $desc = $file['description'];
                $shortDesc = ($desc && strlen($desc) > 150) ? substr($desc, 0, 150) . "..." : $desc;
            ?>
                <div class="file-item">
                    <?php
                    $fileExt = pathinfo($filePath, PATHINFO_EXTENSION);
                    if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'jfif', 'webp', 'tiff', 'svg'])):
                    ?>
                        <img src="<?php echo $filePath; ?>" alt="<?php echo htmlspecialchars($file['filename']); ?>">
                    <?php elseif (in_array($fileExt, ['mp4', 'webm', 'mov', 'avi', 'mkv'])): ?>
                        <video controls>
                            <source src="<?php echo $filePath; ?>" type="video/<?php echo $fileExt; ?>">
                            Ваш браузер не поддерживает видео.
                        </video>
                    <?php elseif ($fileExt === 'pdf'): ?>
                        <embed src="<?php echo $filePath; ?>" type="application/pdf" width="100%" height="150px">
                    <?php else: ?>
                        <div class="file-icon">
                            <i class="file-icon-file"></i>
                            <p><?php echo htmlspecialchars($file['filename']); ?></p>
                        </div>
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($file['filename']); ?></p>
                    <p>Загружено пользователем: <?php echo htmlspecialchars($file['uploader']); ?></p>
                    <?php if ($desc): ?>
                        <p class="file-desc"><?php echo htmlspecialchars($shortDesc); ?></p>
                        <?php if (strlen($desc) > 150): ?>
                            <button type="button" class="btn btn-readmore" data-full-desc="<?php echo htmlspecialchars($desc); ?>">Подробнее</button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <a href="<?php echo $filePath; ?>" download>Загрузить</a>
                    <form action="delete.php" method="post" style="display: inline;">
                        <input type="hidden" name="file_id" value="<?php echo htmlspecialchars($file['id']); ?>">
                        <button type="submit" class="btn btn-delete">Удалить</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Upload Modal -->
    <div id="uploadModal" class="modal">
        <div class="modal-content">
            <span class="close-button" data-modal="uploadModal">&times;</span>
            <h2>Выгрузка файла</h2>
            <form id="modalUploadForm" enctype="multipart/form-data">
                <input type="file" name="file" required>
                <textarea name="description" placeholder="Добавьте описание к файлу (необязательно)"></textarea>
                <button type="submit" class="btn">Загрузить файл</button>
            </form>
        </div>
    </div>
    
    <!-- Read More Modal -->
    <div id="descModal" class="modal">
        <div class="modal-content">
            <span class="close-button" data-modal="descModal">&times;</span>
            <h2>Полное описание</h2>
            <p id="fullDescText"></p>
        </div>
    </div>
    
    <?php if (isset($_SESSION['message'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showMessage("<?php echo htmlspecialchars($_SESSION['message']); ?>", 'success');
            });
        </script>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    
    <script src="assets/script.js"></script>
</body>
</html>