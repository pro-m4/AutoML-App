<?php
header('Content-Type: application/json');

require_once "../dbconnect.php"; 
require_once "global_functions.php";
require_once "phpmailer_func.php";

$json = file_get_contents('php://input');
$input = json_decode($json, true);

$fname = trim($input['fname'] ?? '');
$lname = trim($input['lname'] ?? '');
$email = trim($input['email'] ?? '');
$pass  = $input['pass'] ?? '';

// 1. Βασικός έλεγχος αν είναι άδεια
if(empty($fname) || empty($email) || empty($pass)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['errormesg' => "Συμπληρώστε όλα τα υποχρεωτικά πεδία."]);
    exit;
}

// 2. Έλεγχος εγκυρότητας Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['errormesg' => "Το email δεν είναι έγκυρο."]);
    exit;
}

// 3. ΕΛΕΓΧΟΣ ΙΣΧΥΟΣ ΚΩΔΙΚΟΥ (PHP REGEX)
// Τουλάχιστον 8 χαρακτήρες, 1 κεφαλαίο, 1 μικρό, 1 αριθμό, 1 σύμβολο

$regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

if (!preg_match($regex, $pass)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['errormesg' => "Ο κωδικός δεν πληροί τις προϋποθέσεις ασφαλείας (8+ χαρακτήρες, κεφαλαίο, αριθμό, σύμβολο)."]);
    exit;
}

try {
    // 4. Έλεγχος αν υπάρχει ήδη ο χρήστης
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['errormesg' => "Το email αυτό χρησιμοποιείται ήδη."]);
        exit;
    }

    // 5. Εισαγωγή χρήστη στη βάση (Hash password)
    $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare("INSERT INTO users (fname, lname, email, password, email_verif) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $fname, $lname, $email, $hashed_pass);
    $stmt->execute();
    $user_id = $mysqli->insert_id;

    // 6. Δημιουργία Verification Key
    $verif_key = md5(time() . $email);
    $stmt = $mysqli->prepare("INSERT INTO verify_account (id, verif_key) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $verif_key);
    $stmt->execute();

    // 7. Αποστολή Email
    $domain = getdomain(); 
    $verification_link = $domain . "/automl/verification?verif_key=" . $verif_key;

    $subject = "Επιβεβαίωση Λογαριασμού - AutoML Platform";
    $body = "<h2>Καλώς ήρθες $fname!</h2>
             <p>Πατήστε στον παρακάτω σύνδεσμο για να ενεργοποιήσετε τον λογαριασμό σας:</p>
             <a href='$verification_link'>Ενεργοποίηση Λογαριασμού</a>";
    $altBody = "Link ενεργοποίησης: $verification_link";

    $mail_status = send_mail($email, $fname, $subject, $body, $altBody);

    if($mail_status === true) {
        echo json_encode(['message' => "Η εγγραφή έγινε! Σου στείλαμε email επιβεβαίωσης."]);
    } else {
        echo json_encode(['message' => "Η εγγραφή έγινε, αλλά η αποστολή email απέτυχε. Σφάλμα: " . $mail_status]);
    }

} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['errormesg' => "Σφάλμα συστήματος: " . $e->getMessage()]);
}
?>