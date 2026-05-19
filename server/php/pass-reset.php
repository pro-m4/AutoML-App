<?php
require_once "../dbconnect.php"; 

$invalid_link = false;
$verif_key = $_GET['verif_key'] ?? '';

if (empty($verif_key)) {
    $invalid_link = true;
} else {
    $query = 'SELECT id FROM verify_account WHERE verif_key = ? AND creation_time > (NOW() - INTERVAL 2 HOUR)';
    $st = $mysqli->prepare($query);
    $st->bind_param('s', $verif_key);
    $st->execute();
    $res = $st->get_result()->fetch_assoc();

    if (!$res) {
        $invalid_link = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - AutoML</title>
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
                <?php if ($invalid_link): ?>
                    <div class="text-center">
                        <div class="alert alert-danger shadow-sm rounded-4">
                            <h4 class="alert-heading fw-bold">Invalid Link</h4>
                            <p class="mb-0">The reset link has expired or is invalid.</p>
                        </div>
                        <a href="/automl/forgot-password" class="btn btn-primary mt-3 px-4 rounded-pill">Request New Link</a>
                    </div>
                <?php else: ?>
                    <h2 class="text-center mb-4 fw-bold text-dark">New Password</h2>
                    
                    <div id="alertBox"></div>

                    <form id="resetForm">
                        <input type="hidden" id="verif_key" value="<?php echo htmlspecialchars($verif_key); ?>">
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label fw-semibold">New Password</label>
                            <input type="password" id="new_password" class="form-control py-2" placeholder="At least 8 characters" required minlength="8">
                            <div class="form-text mt-2">
                                <small>Must include uppercase, lowercase, number, and symbol.</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                            <input type="password" id="confirm_password" class="form-control py-2" placeholder="Re-enter password" required>
                        </div>

                        <button type="button" id="updateBtn" class="btn btn-primary w-100 py-2 fw-bold mt-2">
    Update Password
</button>
                    </form>
                <?php endif; ?>

                <div class="mt-4 text-center">
                    <a href="/automl/login" class="text-decoration-none fw-semibold">← Back to Login</a>
                </div>
            </div>

            <div class="mt-auto" style="height: 40px;"></div>
        </div>

        <div class="col-lg-6 d-none d-lg-block p-0">
            <div class="login-bg h-100">
                <div class="login-banner">
                    <h1 class="display-1 fw-bolder mb-4 tracking-tight">
                        All your AutoML tools in one place.
                    </h1>
                    <p class="lead text-muted mb-5 fs-4">
                        Fast, simple, and powerful model selection.
                    </p>
                </div>
            </div>
        </div>

    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
$(document).ready(function() {

    // Υποστήριξη για το πλήκτρο Enter
    $('#resetForm').on('keypress', function(e) {
        if (e.which == 13) { e.preventDefault(); $('#updateBtn').click(); }
    });

    $('#updateBtn').click(function() {
        const btn = $(this);
        const pass = $('#new_password').val();
        const pass_confirm = $('#confirm_password').val();
        const key = $('#verif_key').val();

        // 1. Έλεγχος αν είναι κενά
        if (!pass || !pass_confirm) {
            showAlert('Please fill in all fields.', 'warning');
            return;
        }

        // 2. Έλεγχος αν ταιριάζουν
        if (pass !== pass_confirm) {
            showAlert('The passwords do not match!', 'danger');
            return;
        }

        // 3. Έλεγχος ισχύος (Ίδιο Regex με το register.js)
        const strongPass = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        if (!strongPass.test(pass)) {
            showAlert('The password is weak! It must be at least 8 characters long, include an uppercase letter, a lowercase letter, a number, and a symbol.', 'danger');
            return;
        }

        // 4. Loading State
        const originalText = btn.html();
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm"></span> Παρακαλώ περιμένετε...');

        // 5. AJAX Call (Όπως στο register σου)
        $.ajax({
            url: '/automl/server/php/update_password.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ verif_key: key, new_password: pass }),
            success: function(response) {
                showAlert('Password updated successfully! Redirecting to login...', 'success');
                setTimeout(() => {
                    window.location.href = '/automl/login';
                }, 2000);
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                btn.html(originalText);
                let msg = xhr.responseJSON ? xhr.responseJSON.errormesg : "Update error";
                showAlert(msg, 'danger');
            }
        });
    });

    function showAlert(text, type) {
        $('#alertBox').html(`<div class="alert alert-${type}">${text}</div>`);
    }
});
</script>
</body>
</html>