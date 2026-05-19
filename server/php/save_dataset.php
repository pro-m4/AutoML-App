<?php
session_start();
header('Content-Type: application/json');

require_once '../dbconnect.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvFile'])) {
    $user_id = $_SESSION['user_id'];
    $is_public = isset($_POST['privacy']) ? (int)$_POST['privacy'] : 0; 
    $file = $_FILES['csvFile'];
    $file_name = basename($file['name']);

    $max_size = 10 * 1024 * 1024; 
    if ($file['size'] > $max_size) {
        echo json_encode(["status" => "error", "message" => "Το αρχείο είναι πολύ μεγάλο (Όριο: 10MB)"]);
        exit();
    }

    // Έλεγχος Public
    $checkPublic = $mysqli->prepare("SELECT id FROM datasets WHERE file_name = ? AND is_public = 1");
    $checkPublic->bind_param("s", $file_name);
    $checkPublic->execute();
    if ($checkPublic->get_result()->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Αυτό το αρχείο υπάρχει ήδη στην Public βιβλιοθήκη!"]);
        exit();
    }

    // Έλεγχος Private
    $checkMine = $mysqli->prepare("SELECT id FROM datasets WHERE file_name = ? AND user_id = ? AND is_public = 0");
    $checkMine->bind_param("si", $file_name, $user_id);
    $checkMine->execute();
    if ($checkMine->get_result()->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Έχετε ήδη ανεβάσει αυτό το αρχείο στα Private σας!"]);
        exit();
    }

    $target_dir = "../../uploads/datasets/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    // Το όνομα που θα έχει το αρχείο στον δίσκο
    $new_filename = time() . '_' . $file_name;
    // Η πλήρης διαδρομή ΜΟΝΟ για τη μεταφορά του αρχείου
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        
        $query = "INSERT INTO datasets (user_id, file_name, file_path, is_public, upload_date) VALUES (?, ?, ?, ?, NOW())";
        
       if ($stmt = $mysqli->prepare($query)) {
            $stmt->bind_param("issi", $user_id, $file_name, $new_filename, $is_public);
            
            if ($stmt->execute()) {
                $last_id = $mysqli->insert_id; 
                
                // Καθαρισμός buffer για σιγουριά
                if (ob_get_length()) ob_clean(); 

                echo json_encode([
                    "status" => "success", 
                    "message" => "Το αρχείο αποθηκεύτηκε επιτυχώς!",
                    "dataset_id" => $last_id,
                    "file_path" => $new_filename
                ]);
                exit();
            } else {
                echo json_encode(["status" => "error", "message" => "Σφάλμα SQL: " . $stmt->error]);
            }
            $stmt->close();
        } // <--- ΑΥΤΗ Η ΑΓΚΥΛΗ ΕΚΛΕΙΝΕ ΤΟ prepare
    } else { // <--- ΑΥΤΗ Η ΑΓΚΥΛΗ ΕΚΛΕΙΝΕ ΤΟ move_uploaded_file
        echo json_encode(["status" => "error", "message" => "Αποτυχία μεταφόρτωσης."]);
    }
} else { // <--- ΑΥΤΗ Η ΑΓΚΥΛΗ ΕΚΛΕΙΝΕ ΤΟ αρχικό if ($_SERVER['REQUEST_METHOD']...)
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}

$mysqli->close();
?>
