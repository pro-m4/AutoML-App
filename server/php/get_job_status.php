<?php
require_once '../dbconnect.php';
header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "No ID provided"]);
    exit;
}

$job_id = $_GET['id'];

// 1. Παίρνουμε τα δεδομένα του Job
$sql = "SELECT * FROM jobs WHERE id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    echo json_encode(["status" => "error", "message" => "Job not found"]);
    exit;
}

// 2. Αν το Job ολοκληρώθηκε, το μεταφέρουμε στα Models (αν δεν έχει γίνει ήδη)
if ($job['status'] === 'completed' && !empty($job['results_json'])) {
    
    // Έλεγχος αν έχει ήδη καταχωρηθεί για να αποφύγουμε διπλότυπα
    $check = $mysqli->prepare("SELECT id FROM trained_models WHERE id = ?");
    $check->bind_param("i", $job_id); // Χρησιμοποιούμε το ίδιο ID για ταύτιση
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        
        $results = json_decode($job['results_json'], true);
        
        // Βρίσκουμε το καλύτερο framework και score από το JSON
        $best_fw = "";
        $best_score = 0;
        $model_path = "N/A";

        foreach ($results as $fw_name => $data) {
            if (isset($data['best_score']) && $data['best_score'] > $best_score) {
                $best_score = $data['best_score'];
                $best_fw = $fw_name;
                // Υποθέτουμε ότι η Python επιστρέφει ένα path ή όνομα μοντέλου
                $model_path = $data['best_model'] ?? 'model.pkl'; 
            }
        }

        // INSERT στον πίνακα trained_models
        $insertSql = "INSERT INTO trained_models 
            (id, user_id, dataset_id, dataset_name, target_column, framework, task_type, metric_used, score, model_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $ins = $mysqli->prepare($insertSql);
        // Χρησιμοποιούμε το όνομα αρχείου από το dataset_path (π.χ. iris.csv)
        $clean_name = basename($job['dataset_path']); 
        
        $ins->bind_param("iiisssssds", 
            $job['id'],
            $job['user_id'], 
            $job['dataset_id'], 
            $clean_name, 
            $job['target_column'], 
            $best_fw, 
            $job['task_type'], 
            $job['metric'], 
            $best_score, 
            $model_path
        );
        $ins->execute();
    }
}

// Επιστροφή του status στο Frontend
echo json_encode($job);