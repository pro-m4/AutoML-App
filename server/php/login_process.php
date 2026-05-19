<?php
session_start(); // Ξεκινάμε το session για να "θυμόμαστε" τον χρήστη
header('Content-Type: application/json');
require_once "../dbconnect.php";

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'] ?? '';
$pass  = $input['pass'] ?? '';

if(empty($email) || empty($pass)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['errormesg' => "Συμπληρώστε email και κωδικό."]);
    exit;
}

try {
    // 1. Αναζήτηση χρήστη
    $stmt = $mysqli->prepare("SELECT id, fname, password, email_verif FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // 2. Έλεγχος αν υπάρχει ο χρήστης
    if (!$user) {
        header("HTTP/1.1 401 Unauthorized");
        echo json_encode(['errormesg' => "Το email δεν βρέθηκε."]);
        exit;
    }

    // 3. Έλεγχος κωδικού (συγκρίνει το plain text με το hash στη βάση)
    if (!password_verify($pass, $user['password'])) {
        header("HTTP/1.1 401 Unauthorized");
        echo json_encode(['errormesg' => "Λάθος κωδικός πρόσβασης."]);
        exit;
    }

    // 4. ΕΛΕΓΧΟΣ ΕΝΕΡΓΟΠΟΙΗΣΗΣ
    if ($user['email_verif'] == 0) {
        header("HTTP/1.1 403 Forbidden");
        echo json_encode(['errormesg' => "Ο λογαριασμός δεν έχει ενεργοποιηθεί. Ελέγξτε το email σας!"]);
        exit;
    }

    // 5. ΕΠΙΤΥΧΙΑ: Αποθήκευση στο Session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['fname'];

    echo json_encode(['message' => "Επιτυχής σύνδεση!"]);

} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['errormesg' => "Σφάλμα συστήματος."]);
}
?>