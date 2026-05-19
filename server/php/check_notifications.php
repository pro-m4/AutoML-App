<?php
// Απενεργοποίηση εμφάνισης λαθών για να μη χαλάει το JSON output
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
session_start();

// 1. Σωστό path (υποθέτοντας ότι το dbconnect είναι έναν φάκελο πίσω)
require_once __DIR__ . '/../dbconnect.php'; 

// 2. Έλεγχος αν η μεταβλητή $mysqli υπάρχει (από το dbconnect.php)
if (!isset($mysqli)) {
    echo json_encode(['has_new' => false, 'error' => 'Variable $mysqli not found. Check dbconnect.php']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['has_new' => false, 'error' => 'No session active']);
    exit;
}

$user_id = $_SESSION['user_id'];

// 3. Χρήση της $mysqli για το query
// Αναζητούμε το τελευταίο job που ολοκληρώθηκε και δεν έχει εμφανιστεί ειδοποίηση (is_notified = 0)
$sql = "SELECT id, results_json FROM jobs 
        WHERE user_id = ? AND status = 'completed' AND is_notified = 0 
        ORDER BY id DESC LIMIT 1";

$stmt = $mysqli->prepare($sql);

if (!$stmt) {
    echo json_encode(['has_new' => false, 'error' => $mysqli->error]);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        'has_new' => true,
        'job_id' => $row['id'],
        // Κάνουμε decode το JSON από τη βάση για να το στείλουμε ως αντικείμενο στην JS
        'results' => json_decode($row['results_json'], true)
    ]);
} else {
    echo json_encode(['has_new' => false]);
}
?>