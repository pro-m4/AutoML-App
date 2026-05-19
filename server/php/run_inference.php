<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 0); 

header('Content-Type: application/json');

try {
    if (!file_exists(__DIR__ . '/../dbconnect.php')) {
        throw new Exception("Το αρχείο dbconnect.php δεν βρέθηκε.");
    }
    require_once __DIR__ . '/../dbconnect.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid Request Method");
    }

    // --- Προετοιμασία Μονοπατιών (Διορθωμένο για Linux) ---
    $model_filename = $_POST['model_path'] ?? ''; 
    $framework = $_POST['framework'] ?? '';
    $base_models_dir = "/var/www/html/webkmeans/kclusterhub/automl/models";

    // Καθαρισμός του filename από περίεργα slashes
    $model_filename = str_replace(['\\', '/'], '/', $model_filename);
    $full_model_path = $base_models_dir . '/' . ltrim($model_filename, '/');

    if (is_dir($full_model_path) && strtolower($framework) === 'flaml') {
        $full_model_path = rtrim($full_model_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'flaml_model.joblib';
    }

    if (!file_exists($full_model_path)) {
        throw new Exception("Το μοντέλο δεν βρέθηκε.");
    }

    // --- Διαχείριση Ανεβασμένου CSV ---
    $upload_dir = __DIR__ . '/../../uploads/inference/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (!isset($_FILES['csvFile'])) {
        throw new Exception("Δεν ανέβηκε αρχείο CSV.");
    }

    $original_name = $_FILES['csvFile']['name'];
    $temp_file = $upload_dir . time() . '_' . $original_name;
    move_uploaded_file($_FILES['csvFile']['tmp_name'], $temp_file);

    $output_filename = 'pred_' . time() . '.csv';
    $output_file = $upload_dir . $output_filename;

    // --- Εκτέλεση Python ---
    $python_path = "/var/www/html/webkmeans/kclusterhub/automl/server/python/venv/bin/python3"; 
    $script_path = realpath(__DIR__ . '/../python/predict.py');
    
    // ΠΡΟΣΟΧΗ: escapeshellarg και ΟΧΙ escaphellarg
    $cmd = sprintf(
        '%s %s %s %s %s %s 2>&1',
        $python_path,
        escapeshellarg($script_path),
        escapeshellarg($full_model_path),
        escapeshellarg($temp_file),
        escapeshellarg($output_file),
        escapeshellarg($framework)
    );

   $python_output = shell_exec($cmd);

    // --- ΝΕΑ ΣΤΡΑΤΗΓΙΚΗ ΚΑΘΑΡΙΣΜΟΥ (Bulletproof) ---
    
    // 1. Βρίσκουμε πού ξεκινάει το JSON (ψάχνουμε το "status":"SUCCESS" ή "status":"ERROR")
    $start_pos = strrpos($python_output, '{"status":'); 
// 1. Βρίσκουμε πού ξεκινάει το JSON
    $start_pos = strrpos($python_output, '{"status":'); 

    if ($start_pos !== false) {
        $json_data = substr($python_output, $start_pos);
        
        // --- ΝΕΑ ΠΡΟΣΘΗΚΗ: Βρίσκουμε πού τελειώνει πραγματικά το JSON ---
        $end_pos = strrpos($json_data, '}'); 
        if ($end_pos !== false) {
            // Κόβουμε οτιδήποτε υπάρχει μετά την τελευταία αγκύλη
            $json_data = substr($json_data, 0, $end_pos + 1);
        }
        
        $json_data = trim($json_data);
        $response_from_python = json_decode($json_data, true);
    } else {
        throw new Exception("Η Python δεν επέστρεψε JSON.");
    }

    // Έλεγχος αν το decoding ήταν επιτυχές
    if ($response_from_python === null) {
        throw new Exception("JSON Decode Failed. Raw segment: " . $json_data);
    }

    if (file_exists($output_file) && isset($response_from_python['status']) && $response_from_python['status'] === 'SUCCESS') {
        $u_id = $_SESSION['user_id'] ?? 1;
        $out_file_name = basename($output_file);
        
        $metrics_to_save = json_encode($response_from_python['metrics'] ?? []);

        if (isset($mysqli)) {
            $sql = "INSERT INTO user_predictions (user_id, model_id, input_file, output_file, framework, metrics, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $mysqli->prepare($sql);
            
            if ($stmt) {
                $stmt->bind_param("isssss", $u_id, $model_filename, $original_name, $out_file_name, $framework, $metrics_to_save);
                $stmt->execute();
                $stmt->close();
            }
        }

        echo json_encode([
            'status' => 'success',
            'download_url' => 'uploads/inference/' . $out_file_name,
            'metrics' => $response_from_python['metrics']
        ]);
    } else {
        $error_msg = $response_from_python['message'] ?? "Unknown Error";
        throw new Exception("Python Error: " . $error_msg);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}