
<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM files"); // Return files from all users
$stmt->execute();
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($files);
?>
