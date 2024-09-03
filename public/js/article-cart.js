$(document).ready(function() {
    //const redirectUrl = "{{ path('app_index') }}"; // Correctly render the path in a JavaScript variable

    $('.pseudo-text-effect').off('click').on('click', function(event) {
        event.preventDefault(); // Prevent the default action of the <a> tag

        var $this = $(this);
        var url = $this.data('add-url'); // Get the URL from the data-add-url attribute
        var articleId = $this.data('article-id'); // Get the article ID from the data-article-id attribute
        var csrfToken = $this.data('csrf-token'); // Get the CSRF token from the data-csrf-token attribute

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                article_id: articleId,
                _csrf_token: csrfToken
            },
            success: function(response) {
                // Handle successful response
                console.log('Article added to cart');

                // Update the cart item count
                var $count = $('.count');
                var currentCount = parseInt($count.data('article-number'));
                var newCount = currentCount + 1;
                $count.data('article-number', newCount);
                $count.text(newCount);
            },
            error: function(xhr, status, error) {
                // Handle error response
                console.error('Failed to add article to cart');
            }
        });
    });
});