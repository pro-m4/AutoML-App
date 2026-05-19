<?php
session_start();
error_reporting(0); 
header('Content-Type: application/json');

require_once '../dbconnect.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Μη εξουσιοδοτημένη πρόσβαση."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $ds_id = (int)$_POST['id'];
    $user_id = $_SESSION['user_id'];

    // 1. Παίρνουμε το ΟΝΟΜΑ του αρχείου από τη βάση
    $query = "SELECT file_path FROM datasets WHERE id = ? AND user_id = ? AND is_public = 0";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $ds_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Εδώ το file_path είναι πλέον σκέτο το όνομα (π.χ. 1775125325_iris.csv)
        $file_name = $row['file_path'];

        // 2. Ορίζουμε πού βρίσκεται ο φάκελος των αρχείων στον δίσκο
        // Προσοχή στα ../ ανάλογα με το πού βρίσκεται αυτό το PHP αρχείο
        $target_dir = "../../uploads/datasets/";
        $full_path_to_delete = $target_dir . $file_name;

        // 3. Διαγραφή από τη Βάση Δεδομένων
        $delQuery = "DELETE FROM datasets WHERE id = ?";
        $delStmt = $mysqli->prepare($delQuery);
        $delStmt->bind_param("i", $ds_id);
        
        if ($delStmt->execute()) {
            // 4. Διαγραφή από το Φάκελο (δίσκο) χρησιμοποιώντας το ΠΛΗΡΕΣ path
            if (!empty($file_name) && file_exists($full_path_to_delete)) {
                unlink($full_path_to_delete);
            }
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Αποτυχία διαγραφής από τη βάση."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Το αρχείο είναι Public ή δεν σας ανήκει."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Μη έγκυρο αίτημα."]);
}

$mysqli->close();
?>