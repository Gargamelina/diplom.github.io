<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$userId = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id']) && isset($_POST['new_description'])) {
    $fileId = $_POST['file_id'];
    $new_description = trim($_POST['new_description']);
    // Check that the file belongs to the current user
    $stmt = $conn->prepare("SELECT id FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$fileId, $userId]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        $stmt = $conn->prepare("UPDATE files SET description = ? WHERE id = ?");
        $stmt->execute([$new_description, $fileId]);
        $_SESSION['message'] = "Описание файла обновлено.";
    } else {
        $_SESSION['message'] = "Ошибка: доступ запрещен.";
    }
}
header("Location: cabinet.php");
exit();
?>