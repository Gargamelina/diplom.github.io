<?php
session_start();
require 'config.php';

// Suppress warnings and notices
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(["error" => "Не авторизован"]);
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $userId = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $uploadDir = 'uploads/';
    $fileName = basename($_FILES['file']['name']);
    $fileSize = $_FILES['file']['size'];
    $fileTmpName = $_FILES['file']['tmp_name'];
    $fileType = mime_content_type($fileTmpName);
    $allowedFileTypes = [
        'image/jpeg',
        'image/png',
        'image/heic',
        'image/gif',
        'image/jfif',
        'image/webp',
        'image/svg+xml',
        'image/tiff',
        'application/pdf',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/x-tar',
        'application/x-gzip',
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/x-matroska',
        'audio/mpeg',
        'audio/mp3',
        'audio/wav',
        'audio/ogg',
        'audio/aac'
    ];
    $maxFileSize = 300 * 1024 * 1024; // 300 МБ
    if (!in_array($fileType, $allowedFileTypes)) {
        echo json_encode(["error" => "Данный формат файлов не поддерживается."]);
        exit();
    }
    if ($fileSize > $maxFileSize) {
        echo json_encode(["error" => "Размер файла превышает лимит 300 МБ."]);
        exit();
    }
    $targetFilePath = $uploadDir . $fileName;
    if (file_exists($targetFilePath)) {
        echo json_encode(["error" => "Файл с таким именем уже существует."]);
        exit();
    }
    if (move_uploaded_file($fileTmpName, $targetFilePath)) {
        // Get the file description from the form
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        // Store file information in the database (assumes "description" column exists in files table)
        $stmt = $conn->prepare("INSERT INTO files (user_id, filename, uploader, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $fileName, $username, $description]);
        echo json_encode(["success" => "Файл успешно выгружен."]);
    } else {
        echo json_encode(["error" => "Ошибка выгрузки файла."]);
    }
} else {
    echo json_encode(["error" => "No file uploaded."]);
}
?>