document.addEventListener('DOMContentLoaded', function () {
    const form1 = document.getElementById('product-form');
    const form2 = document.getElementById('review-form');
    const stars = document.querySelectorAll('.rating .fa-star');
    let rating = parseInt(document.querySelector('.rating').dataset.rating);
    $("#size-0").removeClass('btn-success');
    $("#size-0").addClass('btn-secondary');

    $(document).ready(function() {

        // Handle form submission of add to cart or by
        $(form1).off('submit').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const $this = $(this);
            const url = $this.data('add-url'); // Get the URL from the data-add-url attribute
            const articleId = $this.data('article-id'); // Get the article ID from the data-article-id attribute
            const csrfToken = $this.data('csrf-token'); // Get the CSRF token from the data-csrf-token attribute

            const size = document.getElementById('product-size').value;
            const quantity = document.getElementById('product-quantity').value;

            // Get the id of the submit button
            const submitterId = event.originalEvent.submitter.id;

            let action = '';
            if (submitterId === 'buy-button') {
                action = 'buy';
            } else if (submitterId === 'add-to-cart-button') {
                action = 'addtocart';
            }

            $.ajax({
                type: 'POST',
                url: url,
                data: {
                    article_id: articleId,
                    _csrf_token: csrfToken,
                    size: size,
                    quantity: quantity,
                    action: action // Include action in the data
                },
                success: function(response) {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    } else {
                        // Handle successful response
                        console.log('Article added to cart');

                        // Update the cart item count
                        const $count = $('.count');
                        const currentCount = parseInt($count.data('article-number'));
                        const newCount = currentCount + parseInt(quantity);
                        $count.data('article-number', newCount);
                        $count.text(newCount);
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    console.error('Failed to add article to cart');
                }
            });
        });

        // Handle form submission for review
        $(form2).off('submit').on('submit', function(event) {
            event.preventDefault(); // Prevent default form submission

            const articleId = $(form).closest('[data-article-id]').data('article-id');
            const userId = $(form).closest('[data-user-id]').data('user-id');
            const reviewUrl = $(form).closest('[data-review-url]').data('review-url');
            const message = document.getElementById('message').value;
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;


            $.ajax({
                type: 'POST',
                url: reviewUrl,
                data: {
                    article_id: articleId,
                    user_id: userId,
                    message: message,
                    name: name,
                    email: email,
                    rating: rating
                },
                success: function(response) {
                    // Handle successful response
                    console.log('Review submitted successfully');
                    alert(response.status);
                    // Optionally, you can reset the form and rating here
                    form.reset();
                    rating = 0;
                    resetStars();
                },
                error: function(xhr, status, error) {
                    // Handle error response
                    console.error('Failed to submit review');
                    alert('There was an error submitting your review. Please try again.');
                }
            });
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
        let val = $("#var-value").html();
        val = (val == '1') ? val : val - 1;
        $("#var-value").html(val);
        $("#product-quantity").val(val);
        return false;
    });

    $('#btn-plus').off('click').click(function() {
        let val = $("#var-value").html();
        val++;
        $("#var-value").html(val);
        $("#product-quantity").val(val);
        return false;
    });

    // Handle size change
    $('.btn-size').click(function() {
        const this_val = $(this).html();
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
