<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$userId = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Fetch user data (including profile_description)
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get only user’s files
$stmt = $conn->prepare("SELECT * FROM files WHERE user_id = ?");
$stmt->execute([$userId]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
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
    <div class="container">
        <h1>Личный кабинет: <?php echo htmlspecialchars($username); ?></h1>
        
        <!-- Информация о себе -->
        <h2>О себе</h2>
        <div id="profileDescriptionDisplay" style="text-align:left; margin-bottom:15px;">
            <?php 
            if (!empty($user['profile_description'])) {
                // Преобразуем переносы строк в <br> для корректного отображения
                echo nl2br(htmlspecialchars($user['profile_description']));
            } else {
                echo "Описание не задано.";
            }
            ?>
        </div>
        <button type="button" id="editProfileButton" class="btn">Редактировать описание</button>
        <h2>Ваши файлы</h2>
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
                    <?php if ($desc): ?>
                        <p class="file-desc"><?php echo htmlspecialchars($shortDesc); ?></p>
                        <?php if (strlen($desc) > 150): ?>
                            <button type="button" class="btn btn-readmore" data-full-desc="<?php echo htmlspecialchars($desc); ?>">Подробнее</button>
                        <?php endif; ?>
                    <?php endif; ?>
                    <!-- Inline form to update file description -->
                    <form action="update_file.php" method="post" style="margin-top:10px;">
                        <input type="hidden" name="file_id" value="<?php echo htmlspecialchars($file['id']); ?>">
                        <textarea name="new_description" placeholder="Редактировать описание" rows="2" style="width:100%;"><?php echo htmlspecialchars($file['description']); ?></textarea>
                        <button type="submit" class="btn" style="margin-top:5px;">Сохранить описание</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
        <a href="logout.php" class="btn btn-logout">Выйти</a>
    </div>
    
    <!-- Read More Modal for descriptions -->
    <div id="descModal" class="modal">
        <div class="modal-content">
            <span class="close-button" data-modal="descModal">&times;</span>
            <h2>Полное описание</h2>
            <p id="fullDescText"></p>
        </div>
    </div>
    
    <!-- Модальное окно для редактирования описания профиля -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <span class="close-button" data-modal="editProfileModal">&times;</span>
            <h2>Редактировать описание</h2>
            <form action="update_profile.php" method="post">
                <textarea name="profile_description" rows="4" style="width:100%;"><?php echo htmlspecialchars($user['profile_description']); ?></textarea>
                <button type="submit" class="btn">Сохранить</button>
            </form>
        </div>
    </div>
    
    <script src="assets/script.js"></script>
    <script>
        // Добавляем обработчик для кнопки редактирования описания профиля
        const editProfileButton = document.getElementById('editProfileButton');
        const editProfileModal = document.getElementById('editProfileModal');
        if (editProfileButton && editProfileModal) {
            editProfileButton.addEventListener('click', () => {
                editProfileModal.style.display = 'block';
            });
        }
    </script>
</body>
</html>
