document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('product-form');
    const stars = document.querySelectorAll('.rating .fa-star');
    let rating = parseInt(document.querySelector('.rating').dataset.rating);
    $("#size-0").removeClass('btn-success');
    $("#size-0").addClass('btn-secondary');

    // Handle form submission
    $(form).off('submit').on('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        var $this = $(this);
        var url = $this.data('add-url'); // Get the URL from the data-add-url attribute
        var articleId = $this.data('article-id'); // Get the article ID from the data-article-id attribute
        var csrfToken = $this.data('csrf-token'); // Get the CSRF token from the data-csrf-token attribute

        var size = document.getElementById('product-size').value;
        var quantity = document.getElementById('product-quantity').value;


        $.ajax({
            type: 'POST',
            url: url,
            data: {
                article_id: articleId,
                _csrf_token: csrfToken,
                size: size,
                quantity: quantity
            },
            success: function(response) {
                // Handle successful response
                console.log('Article added to cart');

                // Update the cart item count
                var $count = $('.count');
                var currentCount = parseInt($count.data('article-number'));
                var newCount = currentCount + parseInt(quantity);
                $count.data('article-number', newCount);
                $count.text(newCount);
            },
            error: function(xhr, status, error) {
                // Handle error response
                console.error('Failed to add article to cart');
            }
        });
    });

    // Product detail
    $('.product-links-wap a').click(function(){
        var this_src = $(this).children('img').attr('src');
        $('#product-detail').attr('src',this_src);
        return false;
    });

    // Handle quantity change
    $('#btn-minus').off('click').click(function() {
        var val = $("#var-value").html();
        val = (val == '1') ? val : val - 1;
        $("#var-value").html(val);
        $("#product-quantity").val(val);
        return false;
    });

    $('#btn-plus').off('click').click(function() {
        var val = $("#var-value").html();
        val++;
        $("#var-value").html(val);
        $("#product-quantity").val(val);
        return false;
    });

    // Handle size change
    $('.btn-size').click(function() {
        var this_val = $(this).html();
        $("#product-size").val(this_val);
        $(".btn-size").removeClass('btn-secondary');
        $(".btn-size").addClass('btn-success');
        $(this).removeClass('btn-success');
        $(this).addClass('btn-secondary');
        return false;
    });

    // Initial setting of stars based on the current rating
    resetStars();

    stars.forEach((star, index) => {
        star.addEventListener('mouseover', () => {
            // Highlight the stars up to the hovered one
            highlightStars(index);
        });

        star.addEventListener('mouseout', () => {
            // Reset stars to the current rating
            resetStars();
        });

        star.addEventListener('click', () => {
            // Set the new rating based on the clicked star
            rating = index + 1;
            document.querySelector('.rating').dataset.rating = rating;
            resetStars();
        });
    });

    function highlightStars(index) {
        // Reset all stars
        stars.forEach(star => {
            star.classList.remove('fa', 'text-warning');
            star.classList.add('far');
        });

        // Highlight the stars up to the current index
        for (let i = 0; i <= index; i++) {
            stars[i].classList.remove('far');
            stars[i].classList.add('fa', 'text-warning');
        }
    }

    function resetStars() {
        stars.forEach((star, index) => {
            star.classList.remove('fa', 'text-warning');
            star.classList.add('far');
            if (index < rating) {
                star.classList.remove('far');
                star.classList.add('fa', 'text-warning');
            }
        });
    }
});
