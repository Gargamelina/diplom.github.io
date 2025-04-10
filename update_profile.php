<?php
session_start();
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$userId = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profile_description'])) {
    $profile_description = trim($_POST['profile_description']);
    $stmt = $conn->prepare("UPDATE users SET profile_description = ? WHERE id = ?");
    $stmt->execute([$profile_description, $userId]);
    $_SESSION['message'] = "Описание профиля обновлено.";
}
header("Location: cabinet.php");
exit();
?>