<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Χρήση __DIR__ για σωστά paths
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

function send_mail($recipient, $r_name, $subject, $body_content, $altbody) {
    // Φόρτωση ρυθμίσεων
    require __DIR__ . '/mailer_config.php'; 
    
    $mail = new PHPMailer(true);

    try {
        // SMTP Ρυθμίσεις
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $username; 
        $mail->Password   = $password; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($username, 'AutoML App');
        $mail->addAddress($recipient, $r_name);

        // --- ΕΝΣΩΜΑΤΩΣΗ LOGO (Για να φαίνεται και σε localhost) ---
        $mail->addEmbeddedImage(__DIR__ . '/../../src/img/logo2.png', 'logo_img');

        // --- STYLE & TEMPLATE ΟΠΩΣ ΤΟ PASSWORD RESET ---
        $primary_color = "#01ABF5";
        
        $styled_body = "
        <div style='background-color: #f8f9fa; padding: 40px 0; font-family: Arial, sans-serif; text-align: center;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-top: 4px solid {$primary_color}; text-align: left;'>
                
                <div style='padding: 30px; text-align: center; border-bottom: 1px solid #f1f1f1;'>
                    <img src='cid:logo_img' alt='AutoML Logo' style='height: 50px; vertical-align: middle;'>
                    <span style='font-size: 24px; font-weight: bold; color: #333; vertical-align: middle; margin-left: 10px;'>
                        Auto<span style='color: {$primary_color};'>ML</span>
                    </span>
                </div>

                <div style='padding: 40px; color: #444; line-height: 1.6; font-size: 16px;'>
                    <h2 style='color: #333; margin-top: 0; text-align: center;'>{$subject}</h2>
                    <div style='margin-top: 20px;'>
                        {$body_content}
                    </div>
                </div>

                <div style='background-color: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #eee;'>
                    &copy; " . date('Y') . " AutoML App. All rights reserved.
                </div>
            </div>
        </div>";

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $styled_body;
        $mail->AltBody = $altbody;

        $mail->send();
        return true; 
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
?>