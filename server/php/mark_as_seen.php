<?php
// mark_as_seen.php
header('Content-Type: application/json');

// Χρήση __DIR__ για σιγουριά στο path
require_once __DIR__ . '/../dbconnect.php';

if (isset($_POST['job_id'])) {
    $job_id = intval($_POST['job_id']); 

    // ΑΛΛΑΓΗ: Χρησιμοποιούμε $mysqli αντί για $conn
    if (!isset($mysqli)) {
        echo json_encode(['status' => 'error', 'message' => 'Database variable $mysqli not found']);
        exit;
    }

    $sql = "UPDATE jobs SET is_notified = 1 WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => $mysqli->error]);
        exit;
    }

    $stmt->bind_param("i", $job_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'updated_id' => $job_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $mysqli->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No job_id provided']);
}
?>