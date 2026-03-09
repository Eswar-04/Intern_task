$(document).ready(function() {
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        let btn = $('#btnRegister');
        let btnText = btn.find('.btn-text');
        let spinner = btn.find('.spinner-border');
        let alertBox = $('#registerAlert');
        
        // Show loading state
        btn.prop('disabled', true);
        btnText.text('Please wait...');
        spinner.show();
        alertBox.hide().removeClass('alert-success alert-danger');
        
        let formData = {
            name: $('#name').val(),
            email: $('#email').val(),
            password: $('#password').val()
        };
        
        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    alertBox.addClass('alert-success').text(response.message).show();
                    setTimeout(function() {
                        window.location.href = 'login.html';
                    }, 1500);
                } else {
                    alertBox.addClass('alert-danger').text(response.message).show();
                    // Reset button
                    btn.prop('disabled', false);
                    btnText.text('Sign Up');
                    spinner.hide();
                }
            },
            error: function(xhr) {
                let msg = 'An error occurred during registration.';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alertBox.addClass('alert-danger').text(msg).show();
                btn.prop('disabled', false);
                btnText.text('Sign Up');
                spinner.hide();
            }
        });
    });
});
