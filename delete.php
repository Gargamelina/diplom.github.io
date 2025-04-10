<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_id'])) {
    $fileId = $_POST['file_id'];
    $userId = $_SESSION['user_id'];

    // Check if the file belongs to the current user
    $stmt = $conn->prepare("SELECT filename FROM files WHERE id = ? AND user_id = ?");
    $stmt->execute([$fileId, $userId]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        $filePath = 'uploads/' . $file['filename'];
        if (file_exists($filePath)) {
            unlink($filePath); // Delete the file from disk
        }
        // Delete the record from the database
        $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$fileId]);
        echo json_encode(["success" => "File successfully deleted."]);
    } else {
        echo json_encode(["error" => "You do not own this file."]);
    }
}
