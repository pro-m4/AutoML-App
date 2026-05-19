<?php
require_once "../dbconnect.php";
require_once "global_functions.php";

$method = $_SERVER['REQUEST_METHOD'];

if($method != "GET") {
    header("HTTP/1.1 405 Method Not Allowed");
    echo json_encode(['errormesg'=>"Method not allowed."]);
    exit;
}

if(!isset($_GET['verif_key'])){
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['errormesg'=>"Verification key is missing."]);
    exit;
}

$verif_key = $_GET['verif_key'];

// 1. Έλεγχος αν υπάρχει το κλειδί
if(!verif_key_exists($verif_key)){
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['errormesg'=>"Invalid verification key. It may have been used already."]);
    exit;
}

// 2. Έλεγχος αν έληξε (π.χ. μετά από 15 λεπτά)
if(verif_key_expired($verif_key)){
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['errormesg'=>"This verification link has expired. Please register again."]);
    exit;
}

// 3. Ενεργοποίηση του χρήστη
// Κάνουμε join τους δύο πίνακες για να βρούμε ποιος χρήστης αντιστοιχεί στο κλειδί
$query = 'UPDATE users u JOIN verify_account va ON u.id = va.id SET u.email_verif = 1 WHERE va.verif_key = ?';
$st = $mysqli->prepare($query);
$st->bind_param('s', $verif_key);
$st->execute();

// 4. Διαγραφή του κλειδιού (για να μην ξαναχρησιμοποιηθεί)
$query2 = 'DELETE FROM verify_account WHERE verif_key = ?';
$st2 = $mysqli->prepare($query2);
$st2->bind_param('s', $verif_key);
$st2->execute();

echo json_encode(['message'=>"Your account has been successfully verified! You can now log in."]);
?>