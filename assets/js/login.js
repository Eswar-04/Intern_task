$(document).ready(function() {
    // Check if already logged in
    if(localStorage.getItem('session_token')) {
        window.location.href = 'profile.html';
    }

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        let btn = $('#btnLogin');
        let btnText = btn.find('.btn-text');
        let spinner = btn.find('.spinner-border');
        let alertBox = $('#loginAlert');
        
        btn.prop('disabled', true);
        btnText.text('Logging in...');
        spinner.show();
        alertBox.hide().removeClass('alert-success alert-danger');
        
        let formData = {
            email: $('#email').val(),
            password: $('#password').val()
        };
        
        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    // Save token to localStorage
                    localStorage.setItem('session_token', response.token);
                    localStorage.setItem('user_name', response.user.name);
                    localStorage.setItem('user_email', response.user.email);
                    
                    alertBox.addClass('alert-success').text(response.message).show();
                    setTimeout(function() {
                        window.location.href = 'profile.html';
                    }, 1000);
                } else {
                    alertBox.addClass('alert-danger').text(response.message).show();
                    btn.prop('disabled', false);
                    btnText.text('Sign In');
                    spinner.hide();
                }
            },
            error: function(xhr) {
                let msg = 'An error occurred during login.';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alertBox.addClass('alert-danger').text(msg).show();
                btn.prop('disabled', false);
                btnText.text('Sign In');
                spinner.hide();
            }
        });
    });
});
