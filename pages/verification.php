<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Verification - AutoML</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/automl/src/css/login.css">
    <link rel="icon" href="/automl/src/img/favicon.ico" type="image/x-icon">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
        /* Κεντράρισμα της κάρτας στην οθόνη */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa;
            margin: 0;
        }
        .verify-card {
            max-width: 450px;
            width: 90%;
            padding: 50px 30px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            text-align: center;
        }
        #status-icon {
            font-size: 60px;
            margin-bottom: 25px;
            display: flex;
            justify-content: center;
        }
        .btn-primary {
            background-color: #01ABF5 !important;
            border-color: #01ABF5 !important;
        }
        .btn-primary:hover {
            background-color: #018fd1 !important;
        }
    </style>
</head>
<div class="verify-card">
    <div class="mb-4">
        <a class="navbar-brand fw-bold fs-4 text-decoration-none" href="/automl">
            <img src="/automl/src/img/logo2.png" alt="Logo" width="80" class="imgicon me-2">
            Auto<span class="text-primary">ML</span>
        </a>
    </div>

    <div id="status-icon">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
    
    <h2 id="status-title" class="fw-bold mb-3">Verifying Account</h2>
    <p id="status-msg" class="text-muted mb-4 fs-6">Please wait while we activate your account and set everything up.</p>
    
    <div id="action-area" style="display:none;">
        <a href="/automl/login" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
            Go to Login
        </a>
    </div>
</div>

    <script>
    $(document).ready(function() {
        // Παίρνουμε το κλειδί από το URL
        const urlParams = new URLSearchParams(window.location.search);
        const key = urlParams.get('verif_key');

        if (!key) {
            $('#status-icon').html('<span style="color: #dc3545;">⚠️</span>');
            $('#status-title').text('Invalid Link');
            $('#status-msg').text('We could not find a verification key. Please check your email link.');
            $('#action-area').show();
            $('#action-area a').text('Back to Login').addClass('btn-secondary').removeClass('btn-primary');
            return;
        }

        // AJAX κλήση για την ενεργοποίηση
        $.ajax({
            url: '../server/php/verify_process.php?verif_key=' + key,
            method: 'GET',
         success: function(response) {
    try {
        const res = (typeof response === 'object') ? response : JSON.parse(response);
        
        // Χρήση του δικού σου μπλε χρώματος (text-primary)
        $('#status-icon').html('<div class="text-primary" style="font-size: 5rem; line-height: 1;">✓</div>');
        $('#status-title').text('Account Verified!');
        $('#status-msg').removeClass('text-muted').addClass('text-primary fw-bold').text(res.message || "Your account is now active.");
        $('#action-area').fadeIn();
    } catch(e) {
        $('#status-icon').html('<div class="text-primary" style="font-size: 5rem;">✓</div>');
        $('#status-title').text('Success!');
        $('#action-area').fadeIn();
    }
},
error: function(xhr) {
    // Χρήση του κλασικού danger red για σφάλματα
    $('#status-icon').html('<div class="text-danger" style="font-size: 5rem; line-height: 1;">✕</div>');
    $('#status-title').text('Verification Failed');
    
    let msg = 'Invalid or expired link.';
    try {
        const res = JSON.parse(xhr.responseText);
        msg = res.errormesg;
    } catch(e) {}
    
    $('#status-msg').removeClass('text-muted').addClass('text-danger').text(msg);
    $('#action-area').fadeIn();
    $('#action-area a').text('Back to Login').addClass('btn-secondary').removeClass('btn-primary');
}
        });
    });
</script>
</body>
</html>