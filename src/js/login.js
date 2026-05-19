$(document).ready(function() {
    
    $('#loginForm').on('keypress', function(e) {
        if (e.which == 13) { e.preventDefault(); $('#loginBtn').click(); }
    });

    $('#loginBtn').click(function() {
        const btn = $(this);
        const email = $('#loginEmail').val();
        const pass = $('#loginPass').val();

        
        const originalText = btn.html(); 
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm"></span> Please wait...');

        $.ajax({
            url: '/automl/server/php/login_process.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ email: email, pass: pass }),
            success: function(response) {
                window.location.href = '/automl/dashboard';
            },
            error: function(xhr) {
                // 2. Επαναφορά κουμπιού αν υπάρξει λάθος
                btn.prop('disabled', false);
                btn.html(originalText);

                let msg = "Connection error";
                try {
                    const res = JSON.parse(xhr.responseText);
                    msg = res.errormesg;
                } catch(e) { msg = xhr.responseText; }
                $('#loginAlert').html('<div class="alert alert-danger">' + msg + '</div>');
            }
        });
    });
});