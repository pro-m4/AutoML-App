$(document).ready(function() {

    
    $('#registerForm').on('keypress', function(e) {
        if (e.which == 13) { e.preventDefault(); $('#regbtn').click(); }
    });

    $('#regbtn').click(function() {
        const btn = $(this);
        const fname = $('#fname').val().trim();
        const lname = $('#lname').val().trim();
        const email = $('#email').val().trim();
        const pass  = $('#pass').val();
        const pass_confirm = $('#pass_confirm').val();

        
        if (!fname || !email || !pass) {
            showAlert('Please fill in the required fields.', 'warning');
            return;
        }

        
        if (pass !== pass_confirm) {
            showAlert('The passwords do not match!', 'danger');
            return;
        }

        
       
const strongPass = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        if (!strongPass.test(pass)) {
            showAlert('The password is weak! It must be at least 8 characters long, include an uppercase letter,a lowercase letter, a number, and a symbol.', 'danger');
            return;
        }

        
        const originalText = btn.html();
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm"></span> Παρακαλώ περιμένετε...');

        $.ajax({
            url: '/automl/server/php/register_process.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ fname, lname, email, pass }),
            success: function(response) {
                showAlert(response.message, 'success');
                $('#registerForm')[0].reset();
                btn.prop('disabled', false);
                btn.html(originalText);
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                btn.html(originalText);
                let msg = xhr.responseJSON ? xhr.responseJSON.errormesg : "Registration error";
                showAlert(msg, 'danger');
            }
        });
    });

    function showAlert(text, type) {
        $('#alertPlaceholder').html(`<div class="alert alert-${type}">${text}</div>`);
    }
});