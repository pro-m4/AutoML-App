<?php
require_once "../dbconnect.php";
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
if($method != "POST") {
    http_response_code(405);
    echo json_encode(['errormesg'=>"Method not allowed."]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$key = $input['verif_key'] ?? '';
$pass = $input['new_password'] ?? '';

if(empty($key) || empty($pass)) {
    echo json_encode(['errormesg'=>"Missing data."]);
    exit;
}

// 1. Βρες το ID
$query = 'SELECT id FROM verify_account WHERE verif_key = ?';
$st = $mysqli->prepare($query);
$st->bind_param('s', $key);
$st->execute();
$res = $st->get_result()->fetch_assoc();

if(!$res) {
    echo json_encode(['errormesg'=>"Invalid key."]);
    exit;
}

$user_id = $res['id'];
$hashed_pass = password_hash($pass, PASSWORD_DEFAULT);

// 2. Update στον πίνακα users (στήλη password)
$query = 'UPDATE users SET password = ? WHERE id = ?';
$st = $mysqli->prepare($query);
$st->bind_param('si', $hashed_pass, $user_id);

if($st->execute()) {
    // 3. Διαγραφή κλειδιού
    $mysqli->query("DELETE FROM verify_account WHERE id = $user_id");
    echo json_encode(['message'=>"Password updated!"]);
} else {
    echo json_encode(['errormesg'=>"DB Error."]);
}