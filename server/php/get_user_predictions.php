<?php

error_reporting(0); 
ini_set('display_errors', 0);


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../dbconnect.php";


ob_clean(); 
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([]);
        exit;
    }

    $user_id = $_SESSION['user_id'];

    
    $sql = "SELECT id, created_at, input_file, output_file, framework, model_id, metrics 
            FROM user_predictions 
            WHERE user_id = ? 
            ORDER BY created_at DESC";

    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        throw new Exception($mysqli->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $preds = [];
    while ($row = $result->fetch_assoc()) {
        $preds[] = $row;
    }

    
    echo json_encode($preds);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
exit;