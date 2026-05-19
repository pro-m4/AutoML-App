<?php
session_start();
error_reporting(0); // Καλό είναι να υπάρχει για να μην χαλάει το JSON output
header('Content-Type: application/json');

require_once '../dbconnect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]); 
    exit();
}

$user_id = $_SESSION['user_id'];

// SQL: Προσθέσαμε το file_path στο SELECT!
$sql = "SELECT id, file_name, file_path, is_public, upload_date, user_id FROM datasets 
        WHERE user_id = ? OR is_public = 1 
        ORDER BY upload_date DESC";

if ($stmt = $mysqli->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $datasets = [];
    while ($row = $result->fetch_assoc()) {
        // Flag για το αν ο τρέχων χρήστης είναι ο ιδιοκτήτης
        $row['can_delete'] = ($row['user_id'] == $user_id);
        
        $datasets[] = $row;
    }

    echo json_encode($datasets);
    $stmt->close();
} else {
    echo json_encode(["error" => $mysqli->error]);
}

$mysqli->close();
?>