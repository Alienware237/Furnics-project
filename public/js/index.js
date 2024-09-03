$(document).ready(function () {
    const container = $('header[data-app-index]');
    const indexUrl = container.data('app-index');
    console.log(indexUrl);
    $('#login-form').on('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        $.ajax({
            type: 'POST',
            url: indexUrl,
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    window.location.href = response.redirectUrl;
                } else {
                    $('#login-error-message').text(response.message).show();
                }
            },
            error: function () {
                $('#login-error-message').text('An error occurred. Please try again.').show();
            }
        });
    });
});
