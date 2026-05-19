<?php
session_start();
require_once '../dbconnect.php'; // Σιγουρέψου ότι το path είναι σωστό
header('Content-Type: application/json');

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Παίρνουμε τα μοντέλα του συγκεκριμένου χρήστη
// Κάνουμε JOIN αν θέλουμε extra πληροφορίες, αλλά προς το παρόν αρκεί ο πίνακας trained_models
$sql = "SELECT * FROM trained_models WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$models = [];
while ($row = $result->fetch_assoc()) {
    // Μπορούμε να κάνουμε format το score εδώ αν θέλουμε
    $row['score'] = number_format($row['score'], 4);
    $models[] = $row;
}

echo json_encode($models);