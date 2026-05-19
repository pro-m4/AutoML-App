<?php
session_start();
require_once '../dbconnect.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    die("Access Denied.");
}

$model_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Βρίσκουμε το path του αρχείου στη βάση
$stmt = $mysqli->prepare("SELECT model_path FROM trained_models WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $model_id, $user_id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if ($res) {
    $filepath = "D:/AutoML_WebApp/models/" . $res['model_path'];
    if (file_exists($filepath)) {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        readfile($filepath);
        exit;
    }
}
die("File not found.");