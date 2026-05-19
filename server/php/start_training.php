<?php
session_start();
require_once '../dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $dataset_id = $_POST['dataset_id'];
    $dataset_path = $_POST['dataset_path']; // Το όνομα αρχείου π.χ. 123_iris.csv
    $target = $_POST['target'];
    $features = $_POST['features']; // String: "sepal_length,sepal_width"
    $frameworks = $_POST['frameworks']; // String: "flaml,h2o"
    $time_limit = (int)$_POST['time_limit'];
    $sample_size = isset($_POST['sample_size']) ? (int)$_POST['sample_size'] : null;
    $metric = $_POST['metric'];
    $task_type = $_POST['task_type']; // Classification ή Regression

    $sql = "INSERT INTO jobs (user_id, dataset_id, dataset_path, target_column, selected_features, 
            selected_frameworks, time_limit, sample_size, metric, task_type, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
    
    $stmt = $mysqli->prepare($sql);
    // Προσοχή: άλλαξα το 9ο γράμμα από i σε s
$stmt->bind_param("iissssiiss", $user_id, $dataset_id, $dataset_path, $target, 
                  $features, $frameworks, $time_limit, $sample_size, $metric, $task_type);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "job_id" => $mysqli->insert_id]);
    } else {
        echo json_encode(["status" => "error", "message" => $mysqli->error]);
    }
}
?>