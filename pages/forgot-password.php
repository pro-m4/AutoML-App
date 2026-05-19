<?php
session_start();
if (isset($_SESSION['user_id'])) { header("Location: /automl/dashboard"); exit(); }

// 1. Σωστά paths για τα αρχεία (Ανεβαίνουμε ένα επίπεδο με ../)
require '../server/php/PHPMailer/src/Exception.php';
require '../server/php/PHPMailer/src/PHPMailer.php';
require '../server/php/PHPMailer/src/SMTP.php';
require_once "../server/php/mailer_config.php";
require_once "../server/dbconnect.php"; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ΑΥΤΟ ΤΟ ΚΟΜΜΑΤΙ ΤΡΕΧΕΙ ΟΤΑΝ ΓΙΝΕΙ ΤΟ AJAX CALL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    $query = "SELECT id FROM users WHERE email = ?";
    $st = $mysqli->prepare($query);
    $st->bind_param("s", $email);
    $st->execute();
    $res = $st->get_result()->fetch_assoc();

    if ($res) {
        $user_id = $res['id'];
        $token = bin2hex(random_bytes(16));

        $mysqli->query("DELETE FROM verify_account WHERE id = $user_id");
        $query = "INSERT INTO verify_account (id, verif_key, creation_time) VALUES (?, ?, NOW())";
        $st = $mysqli->prepare($query);
        $st->bind_param("is", $user_id, $token);
        $st->execute();

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = $username; 
            $mail->Password   = $password; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($username, 'AutoML Support');
            $mail->addAddress($email);

            $reset_link = "https://kclusterhub.iee.ihu.gr/automl/server/php/pass-reset.php?verif_key=" . $token;

            // --- ΑΡΧΗ TEMPLATE ---
            $primary_color = "#01ABF5";
            $base_url = "https://kclusterhub.iee.ihu.gr/automl";

            $mail->isHTML(true);
            $mail->Subject = 'Reset Password - AutoML';
            $mail->addEmbeddedImage(__DIR__ . '/../src/img/logo2.png', 'logo_img');
            $mail->Body = "
            <div style='background-color: #f8f9fa; padding: 40px 0; font-family: Arial, sans-serif; text-align: center;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 4px solid {$primary_color};'>
                    
                    <div style='padding: 30px;'>
                        <img src='cid:logo_img' alt='AutoML Logo' style='height: 50px; vertical-align: text-bottom;'>
                        <span style='font-size: 24px; font-weight: bold; color: #333; vertical-align: middle; margin-left: 10px;'>
                            Auto<span style='color: {$primary_color};'>ML</span>
                        </span>
                    </div>

                    <div style='padding: 40px; text-align: center;'>
                        <h1 style='font-size: 24px; color: #333; margin-bottom: 20px;'>Password Reset</h1>
                        <p style='font-size: 16px; color: #666; line-height: 1.6; margin-bottom: 30px;'>
                            We received a request to reset the password for your AutoML account.<br>
                            Click the button below to set a new password.
                        </p>

                        <a href='{$reset_link}' style='background-color: {$primary_color}; color: #ffffff; padding: 14px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 16px;'>
                            Reset Password
                        </a>

                        <p style='font-size: 13px; color: #999; margin-top: 40px;'>
                            If you didn't request this, you can safely ignore this email.<br>
                            This link will expire in 2 hours.
                        </p>
                    </div>

                    <div style='background-color: #f1f1f1; padding: 20px; font-size: 12px; color: #777;'>
                        &copy; " . date('Y') . " AutoML. All rights reserved.
                    </div>
                </div>
            </div>";
            // --- ΤΕΛΟΣ TEMPLATE ---

            $mail->send();
            echo "<div class='alert alert-success'>The link has been sent! Check your email.</div>";
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error: {$mail->ErrorInfo}</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Email not found.</div>";
    }
    exit; // ΣΗΜΑΝΤΙΚΟ: Σταματάμε την εκτέλεση εδώ για να μην ξαναστείλει την HTML από κάτω
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - AutoML App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/automl/src/css/login.css">
    <link rel="icon" href="/automl/src/img/favicon.ico" type="image/x-icon">
</head>
<body class="bg-light">

<div class="container-fluid vh-100">
    <div class="row h-100">

        <div class="col-lg-6 d-flex flex-column align-items-center min-vh-100 py-5 login-bg-mobile">
            
            <div class="logo mb-auto"> 
                <a class="navbar-brand mobile_logo fw-bold fs-4" href="/automl">
                    <img class="imgicon" src="/automl/src/img/logo2.png" alt="Logo" width="100" class="me-2">
                    <span class="logo-color">Auto<span class="text-primary">ML</span></span>
                </a>
            </div>

            <div class="w-75 mt-auto mb-auto mobile-form">
                <h2 class="text-center mb-4">Reset Password</h2>
                
                <div id="messageAlert"></div>

                <form id="forgotForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="name@example.com" required>
                    </div>
                    
                    <button type="submit" id="submitBtn" class="btn btn-primary w-100 py-2">
                        Send Link
                    </button>
                </form>

                <div class="mt-4 text-center">
                    <a href="/automl/login" class="text-decoration-none">← Back to Login</a>
                </div>
            </div>

            <div class="mt-auto" style="height: 40px;"></div>
        </div>

        <div class="col-lg-6 d-none d-lg-block p-0">
            <div class="login-bg h-100">
                <div class="login-banner">
                    <h1 class="display-1 fw-bolder mb-4 tracking-tight">
                        All your AutoML tools in one place. <br> 
                    </h1>

                    <p class="lead text-muted mb-5 max-width-700 fs-4">
                        Fast, simple, and powerful model selection.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="/automl/src/js/login.js"></script> 
</body>
</html>

<script>
document.getElementById('forgotForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('email').value;
    const alertBox = document.getElementById('messageAlert');
    const btn = document.getElementById('submitBtn');

    btn.disabled = true;
    btn.innerHTML = 'Sending...';

    // Στέλνουμε τα δεδομένα ΣΤΟ ΙΔΙΟ ΑΡΧΕΙΟ ('' σημαίνει τρέχουσα σελίδα)
    fetch('', { 
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'email=' + encodeURIComponent(email)
    })
    .then(res => res.text())
    .then(data => {
        alertBox.innerHTML = data; 
        btn.disabled = false;
        btn.innerHTML = 'Send Link';
    })
    .catch(err => {
        alertBox.innerHTML = "<div class='alert alert-danger'>Something went wrong.</div>";
        btn.disabled = false;
        btn.innerHTML = 'Send Link';
    });
});
</script>
</body>
</html>