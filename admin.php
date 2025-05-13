<?php
session_start();
require 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

// Check if current user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
  echo "Access Denied. Admins only.";
  exit();
}

// Process POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  // Delete file action
  if ($_POST['action'] == 'delete_file' && isset($_POST['file_id'])) {
    $fileId = $_POST['file_id'];
    // Get file details
    $stmt = $conn->prepare("SELECT filename FROM files WHERE id = ?");
    $stmt->execute([$fileId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($file) {
      $filePath = 'uploads/' . $file['filename'];
      if (file_exists($filePath)) {
        unlink($filePath);
      }
      $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
      $stmt->execute([$fileId]);
      $_SESSION['admin_message'] = "Файл успешно удалён.";
    }
  }
  // Update file description action
  elseif ($_POST['action'] == 'update_file_description' && isset($_POST['file_id']) && isset($_POST['description'])) {
    $fileId = $_POST['file_id'];
    $description = trim($_POST['description']);
    $stmt = $conn->prepare("UPDATE files SET description = ? WHERE id = ?");
    $stmt->execute([$description, $fileId]);
    $_SESSION['admin_message'] = "Описание файла успешно обновлено.";
  }
  // Delete order action
  elseif ($_POST['action'] == 'delete_order' && isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    if ($stmt->execute([$orderId])) {
      $_SESSION['admin_message'] = "Заказ успешно удалён.";
    } else {
      $_SESSION['admin_message'] = "Ошибка при удалении заказа.";
    }
  }
  // Delete user action
  elseif ($_POST['action'] == 'delete_user' && isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];
    // Prevent admin from deleting themselves
    if ($userId == $_SESSION['user_id']) {
      $_SESSION['admin_message'] = "Нельзя удалить самого себя.";
    } else {
      $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
      $stmt->execute([$userId]);
      $_SESSION['admin_message'] = "Пользователь успешно удалён.";
    }
  }
  // Update username action
  elseif ($_POST['action'] == 'update_username' && isset($_POST['user_id']) && isset($_POST['username'])) {
    $userId = $_POST['user_id'];
    $newUsername = trim($_POST['username']);
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->execute([$newUsername, $userId]);
    $_SESSION['admin_message'] = "Имя пользователя успешно обновлено.";
  }
  header("Location: admin.php");
  exit();
}

// Fetch all orders
$stmt = $conn->prepare("SELECT * FROM orders ORDER BY created_at DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all files
$stmt = $conn->prepare("SELECT * FROM files");
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all users
$stmt = $conn->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <title>Админ Панель</title>
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
        <source src="assets/video/vecteezy_abstract-digital-particle-wave-and-light-cyber-or-technology_9271722.mp4" type="video/mp4">
        Ваш браузер не поддерживает видео.
    </video>
  <div class="container">
    <h1>Админ Панель</h1>
    <?php if (isset($_SESSION['admin_message'])): ?>
      <p><?php echo $_SESSION['admin_message'];
          unset($_SESSION['admin_message']); ?></p>
    <?php endif; ?>

    <h2>Заказы пользователей</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Артикул</th>
          <th>Название товара</th>
          <th>Цена</th>
          <th>Количество</th>
          <th>ФИО</th>
          <th>Телефон</th>
          <th>Адрес</th>
          <th>Дата заказа</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($orders): ?>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><?php echo htmlspecialchars($order['id']); ?></td>
              <td><?php echo htmlspecialchars($order['article']); ?></td>
              <td><?php echo htmlspecialchars($order['product_name'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($order['price'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($order['quantity'] ?? '1'); ?></td>
              <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
              <td><?php echo htmlspecialchars($order['phone']); ?></td>
              <td><?php echo htmlspecialchars($order['address'] ?? ''); ?></td>
              <td><?php echo htmlspecialchars($order['created_at'] ?? ''); ?></td>
              <td style="text-align: right;">
                <button class="pdf-btn" onclick="generatePDF(<?= htmlspecialchars($order['id']) ?>)">Сгенерировать PDF</button>
                <span id="spinner_<?= htmlspecialchars($order['id']) ?>" style="display:none;" class="spinner"></span>
                <form method="post" style="display:inline;">
                  <input type="hidden" name="action" value="delete_order">
                  <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order['id']); ?>">
                  <button type="submit" class="delete-order-btn" onclick="return confirm('Удалить этот заказ?')">Удалить заказ</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="10">Заказы отсутствуют</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <h2>Управление Файлами</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Имя файла</th>
          <th>Загрузил</th>
          <th>Описание</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($files as $file): ?>
          <tr>
            <td><?php echo htmlspecialchars($file['id']); ?></td>
            <td><?php echo htmlspecialchars($file['filename']); ?></td>
            <td><?php echo htmlspecialchars($file['uploader']); ?></td>
            <td><?php echo htmlspecialchars($file['description']); ?></td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="delete_file">
                <input type="hidden" name="file_id" value="<?php echo htmlspecialchars($file['id']); ?>">
                <button type="submit">Удалить</button>
              </form>
              <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="update_file_description">
                <input type="hidden" name="file_id" value="<?php echo htmlspecialchars($file['id']); ?>">
                <input type="text" name="description" value="<?php echo htmlspecialchars($file['description']); ?>" placeholder="Новое описание">
                <button type="submit">Сохранить</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <h2>Управление Пользователями</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Имя пользователя</th>
          <th>Email</th>
          <th>is_admin</th>
          <th>Действия</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?php echo htmlspecialchars($user['id']); ?></td>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
            <td><?php echo htmlspecialchars($user['is_admin']); ?></td>
            <td>
              <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <button type="submit">Удалить</button>
              </form>
              <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="update_username">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>">
                <button type="submit">Сохранить</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Новая секция: Список PDF-документов (накладных) -->
    <h2>Список PDF-документов</h2>
    <?php
    // Получение списка накладных с данными заказа (имя клиента) через JOIN
    $invoicesStmt = $conn->query("SELECT i.invoice_number, i.pdf_path, i.created_at, o.customer_name FROM invoices i JOIN orders o ON i.order_id = o.id ORDER BY i.created_at DESC");
    $invoices = $invoicesStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <table>
      <thead>
        <tr>
          <th>Номер накладной</th>
          <th>Дата создания</th>
          <th>Клиент</th>
          <th>Скачать</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($invoices): ?>
          <?php foreach ($invoices as $inv): ?>
            <tr>
              <td><?= htmlspecialchars($inv['invoice_number']) ?></td>
              <td><?= htmlspecialchars($inv['created_at']) ?></td>
              <td><?= htmlspecialchars($inv['customer_name']) ?></td>
              <td>
                <a href="admin/download_pdf.php?file=<?= urlencode(basename($inv['pdf_path'])) ?>&preview=1" target="_blank">Открыть</a>
                <a href="admin/download_pdf.php?file=<?= urlencode(basename($inv['pdf_path'])) ?>" style="margin-left:8px;">Скачать</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4">PDF-документы отсутствуют</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Скрипт для AJAX вызова generate_pdf.php -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
      function generatePDF(orderId) {
        // Показываем анимацию загрузки
        var spinner = $('#spinner_' + orderId);
        spinner.show();

        $.ajax({
          type: "POST",
          url: "admin/generate_pdf.php",
          data: {
            order_id: orderId
          },
          dataType: "json",
          success: function(response) {
            spinner.hide();
            if (response.status === "success") {
              alert("PDF накладная успешно создана. Путь: " + response.pdf_path);
              location.reload();
            } else {
              alert("Ошибка: " + response.message);
            }
          },
          error: function() {
            spinner.hide();
            alert("Ошибка при генерации PDF.");
          }
        });
      }
    </script>
</body>

</html>