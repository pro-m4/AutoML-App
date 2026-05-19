<?php
session_start();
require_once '../dbconnect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized or Missing ID"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$model_id = (int)$_POST['id'];

// 1. ΠΡΩΤΑ βρίσκουμε το όνομα του αρχείου ZIP για να το σβήσουμε από τον δίσκο
$query = "SELECT model_path FROM trained_models WHERE id = ? AND user_id = ?";
$stmt = $mysqli->prepare($query);
$stmt->bind_param("ii", $model_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$model = $result->fetch_assoc();

if ($model) {
    // ΣΩΣΤΟ (Linux path)
$file_to_delete = "/var/www/html/webkmeans/kclusterhub/automl/models/" . $model['model_path'];

    // 2. Διαγραφή από τη βάση δεδομένων
    $del_stmt = $mysqli->prepare("DELETE FROM trained_models WHERE id = ?");
    $del_stmt->bind_param("i", $model_id);

    if ($del_stmt->execute()) {
        // 3. ΑΦΟΥ διαγραφεί από τη βάση, σβήνουμε και το αρχείο από τον δίσκο
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error during deletion"]);
    }
} else {
    // Εδώ είναι το "Model ID not found" που έλεγες
    echo json_encode(["status" => "error", "message" => "Model not found or access denied"]);
}