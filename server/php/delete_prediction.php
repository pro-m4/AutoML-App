<?php
session_start();
require_once "../dbconnect.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$prediction_id = $_POST['id'];
$user_id = $_SESSION['user_id'];


$stmt = $mysqli->prepare("SELECT output_file FROM user_predictions WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $prediction_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $file_to_delete = "../../uploads/inference/" . $row['output_file'];
    
    
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
    }

    
    $delete_stmt = $mysqli->prepare("DELETE FROM user_predictions WHERE id = ? AND user_id = ?");
    $delete_stmt->bind_param("ii", $prediction_id, $user_id);
    
    if ($delete_stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Prediction not found"]);
}