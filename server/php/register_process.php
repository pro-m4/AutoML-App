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

// 1. Basic check if empty
if(empty($fname) || empty($email) || empty($pass)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['errormesg' => "Please fill in all required fields."]);
    exit;
}

// 2. Email validity check
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['errormesg' => "The email address is not valid."]);
    exit;
}

// 3. PASSWORD STRENGTH CHECK (PHP REGEX)
// At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 symbol
$regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';

if (!preg_match($regex, $pass)) {
    header("HTTP/1.1 400 Bad Request");
    echo json_encode(['errormesg' => "The password does not meet the security requirements (8+ characters, uppercase, number, symbol)."]);
    exit;
}

try {
    // 4. Check if user already exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        header("HTTP/1.1 400 Bad Request");
        echo json_encode(['errormesg' => "This email address is already in use."]);
        exit;
    }

    // 5. Insert user into DB (Hash password)
    $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);
    $stmt = $mysqli->prepare("INSERT INTO users (fname, lname, email, password, email_verif) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("ssss", $fname, $lname, $email, $hashed_pass);
    $stmt->execute();
    $user_id = $mysqli->insert_id;

    // 6. Create Verification Key
    $verif_key = md5(time() . $email);
    $stmt = $mysqli->prepare("INSERT INTO verify_account (id, verif_key) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $verif_key);
    $stmt->execute();

    // 7. Send Email
    $domain = getdomain(); 
    $verification_link = $domain . "/pages/verification.php?verif_key=" . $verif_key;

   $subject = "Account Verification - AutoML App";

$body = "
    <div style='text-align: center; width: 100%;'>
        <h2 style='color: #333;'>Welcome, $fname!</h2>
        <p style='color: #666;'>Please click the link below to activate your account and get started:</p>
        
        <div style='margin: 30px 0;'>
            <a href='$verification_link' style='background-color: #01ABF5; color: white; padding: 14px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block; box-shadow: 0 4px 6px rgba(0,0,0,0.1);'>
                Activate Account
            </a>
        </div>
        
        <p style='font-size: 13px; color: #777; margin-top: 30px;'>
            If you cannot click the button, copy and paste this link into your browser:
        </p>
        <p style='font-size: 12px; color: #01ABF5; word-break: break-all;'>
            $verification_link
        </p>
    </div>";

$altBody = "To activate your account, please visit the following link: $verification_link";

$mail_status = send_mail($email, $fname, $subject, $body, $altBody);

if($mail_status === true) {
    echo json_encode(['message' => "Registration successful! We have sent you a verification email."]);
} else {
    echo json_encode(['message' => "Account created, but the confirmation email could not be sent. Error: " . $mail_status]);
}

} catch (Exception $e) {
    header("HTTP/1.1 500 Internal Server Error");
    echo json_encode(['errormesg' => "System Error: " . $e->getMessage()]);
}
?>