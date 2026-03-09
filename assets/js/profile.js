$(document).ready(function() {
    let token = localStorage.getItem('session_token');
    
    // Redirect to login if no token
    if(!token) {
        window.location.href = 'login.html';
        return;
    }

    // Logout functionality
    $('#btnLogout').on('click', function() {
        localStorage.removeItem('session_token');
        localStorage.removeItem('user_name');
        localStorage.removeItem('user_email');
        window.location.href = 'login.html';
    });

    // Populate user info from localStorage first for immediate display
    let userName = localStorage.getItem('user_name') || 'User';
    let userEmail = localStorage.getItem('user_email') || '';
    $('#displayRoleName').text(userName);
    $('#displayEmail').text(userEmail);
    if(userName.length > 0) {
        $('#userInitial').text(userName.charAt(0).toUpperCase());
    }

    // Load profile data
    function loadProfile() {
        $.ajax({
            url: 'php/profile.php',
            type: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    let data = response.data;
                    $('#displayRoleName').text(data.name);
                    $('#displayEmail').text(data.email);
                    if(data.name && data.name.length > 0) {
                        $('#userInitial').text(data.name.charAt(0).toUpperCase());
                    }
                    
                    // Populate form fields
                    if(data.age) $('#age').val(data.age);
                    if(data.dob) $('#dob').val(data.dob);
                    if(data.contact) $('#contact').val(data.contact);

                    $('#profileLoading').hide();
                    $('#profileContent').show();
                } else {
                    handleAuthError(response.message);
                }
            },
            error: function() {
                handleAuthError('Session expired or unauthorized.');
            }
        });
    }

    function handleAuthError(message) {
        alert(message || 'Session expired. Please login again.');
        localStorage.removeItem('session_token');
        window.location.href = 'login.html';
    }

    // Save profile data
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        let btn = $('#btnSaveProfile');
        let btnText = btn.find('.btn-text');
        let spinner = btn.find('.spinner-border');
        let alertBox = $('#profileAlert');
        
        btn.prop('disabled', true);
        btnText.text('Saving...');
        spinner.show();
        alertBox.hide().removeClass('alert-success alert-danger');
        
        let formData = $(this).serialize();
        
        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    alertBox.addClass('alert-success').text(response.message).show();
                    // Auto hide success message
                    setTimeout(() => alertBox.fadeOut(), 3000);
                } else {
                    alertBox.addClass('alert-danger').text(response.message).show();
                }
                btn.prop('disabled', false);
                btnText.text('Save Changes');
                spinner.hide();
            },
            error: function(xhr) {
                let msg = 'An error occurred while saving.';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                alertBox.addClass('alert-danger').text(msg).show();
                btn.prop('disabled', false);
                btnText.text('Save Changes');
                spinner.hide();
            }
        });
    });

    // Initial load
    loadProfile();
});
