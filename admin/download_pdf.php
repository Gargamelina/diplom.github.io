<?php
// admin/download_pdf.php

session_start();

// Защита: только залогиненные админы
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    exit('Access denied');
}

// Проверка GET параметра (можно по id или по относительному пути)
if (empty($_GET['file'])) {
    http_response_code(400);
    exit('Нет файла');
}

$file = $_GET['file'];

// Безопасность: разрешить только внутри invoices, без ../ и абсолютных путей
$baseDir = realpath(__DIR__ . '/../invoices');
$path = realpath($baseDir . '/' . $file);

// Файл должен быть внутри invoices
if (!$path || strpos($path, $baseDir)!==0 || !is_file($path)) {
    http_response_code(404);
    exit('Файл не найден');
}

$basename = basename($path);

// Для предпросмотра в браузере:
if (!empty($_GET['preview'])) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $basename . '"');
} else {
    // Для скачивания!
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $basename . '"');
}

header('Content-Length: ' . filesize($path));
readfile($path);
exit;