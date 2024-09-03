$(document).ready(function() {
    const container = $('div[data-update-url][data-remove-url][data-update-size]');
    const updateUrl = container.data('update-url');
    let removeUrl = container.data('remove-url');
    const updateSize = container.data('update-size');

    // Collect all data-size-choice values
    let sizeChoices = [];
    $('tr[data-size-choice]').each(function() {
        sizeChoices.push($(this).data('size-choice'));
    });

    sizeChoices.forEach(el =>{
        $(`#size-${el}`).removeClass('btn-success');
        $(`#size-${el}`).addClass('btn-secondary');
    })

    $('.js-btn-minus, .js-btn-plus').off('click').on('click', function() {
        const action = $(this).hasClass('js-btn-minus') ? 'decrease' : 'increase';
        const articleId = $(this).data('article-id');
        const cartItemId = $(this).data('cart-item-id');

        const price = $(this).data('article-price');
        const $count = $('.count');
        const currentCount = parseInt($count.data('article-number'));

        // Avoid if the user try to decrease the number to 0
        if (currentCount < 1) {
            return "number of article can not inferior to 1!";
        }
        $.ajax({
            url: updateUrl,
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ articleId: articleId, action: action, cartItemId : cartItemId }),
            success: function(data) {
                if (data.success) {
                    // Update the quantity and price in the DOM
                    let quantityInput = $(`input[data-article-id="${articleId}"]`);
                    let totalPrice = 0;
                    quantityInput.val(data.newQuantity);
                    // Update the total price
                    let priceCell = quantityInput.closest('tr').find('.product-total-price');
                    priceCell.text(`$${data.newQuantity*price}`);
                    const containerP = document.getElementsByClassName('product-total-price');
                    for (i= 0; i<containerP.length; i++) {
                        totalPrice += parseInt(containerP[i].innerHTML.split("$")[1]);
                    }
                    document.getElementById('subTotalPriceElmText').innerHTML = totalPrice;
                    document.getElementById('totalPriceElmText').innerHTML = totalPrice;

                    // Update the cart item count
                    var newCount = 0;
                    if (action === "increase") {
                        newCount = currentCount + 1;
                    } else { newCount = currentCount - 1; }
                    $count.data('article-number', newCount);
                    $count.text(newCount);
                }
            }
        });
    });

    $('.js-remove-product').off('click').on('click', function(event) {
        event.preventDefault();
        let articleId = $(this).data('article-id');

        // Get the quantity of the item to be removed
        let quantityInput = $(`input[data-article-id="${articleId}"]`);
        let quantityToRemove = parseInt(quantityInput.val());

        if (removeUrl === undefined) {
            const container = $('div[data-remove-url]');
            removeUrl = container.data('remove-url');
        }


        $.ajax({
            url: removeUrl,
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ articleId: articleId }),
            success: function(data) {
                if (data.success) {
                    // Remove the product row from the table
                    let row = $(`tr:has(input[data-article-id="${articleId}"])`);

                    row.remove();

                    // Update the total price
                    let totalPrice = 0;
                    const containerP = document.getElementsByClassName('product-total-price');
                    for (i= 0; i<containerP.length; i++) {
                        totalPrice += parseInt(containerP[i].innerHTML.split("$")[1]);
                    }
                    document.getElementById('subTotalPriceElmText').innerHTML = totalPrice;
                    document.getElementById('totalPriceElmText').innerHTML = totalPrice;

                    // Decrement the count by the quantity of the removed item
                    let $count = $('.count');
                    let currentCount = parseInt($count.data('article-number'));
                    let newCount = currentCount - quantityToRemove;
                    $count.data('article-number', newCount);
                    $count.text(newCount);
                }
            }
        });
    });
<<<<<<< HEAD
<<<<<<< HEAD
=======
=======

    // Event listener for size buttons
    $('.btn-size').off('click').on('click', function() {
        const size = $(this).data('size');
        const cartItemId = $(this).data('cart-item-id');

        $.ajax({
            url: updateSize, // Your route URL
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                size: size,
                cartItemId: cartItemId
            }),
            success: function(response) {
                if (response.success) {
                    console.log('Size updated successfully!');

                    // Handle size change

                    let notSizeChoices = [];
                    $(`.size-${cartItemId}`).each(function() {
                        notSizeChoices.push($(this)); // Or use $(this).data('size') if size is a data attribute
                    });

                    const this_val = $(this).html();
                    $("#product-size").val(this_val);
                   notSizeChoices.forEach(el =>{
                       el.removeClass('btn-secondary');
                       el.addClass('btn-success');
                   })

                    $(`#size-${cartItemId}-${size}`).removeClass('btn-success');
                    $(`#size-${cartItemId}-${size}`).addClass('btn-secondary');

                    // Optionally update the UI or alert the user
                } else {
                    console.error('Failed to update size');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX request failed:', error);
            }
        });
    });
});
>>>>>>> 81c2f77de4f19d374190ed90d5e02a09bf0fba5b

    // Event listener for size buttons
    $('.btn-size').off('click').on('click', function() {
        const size = $(this).data('size');
        const cartItemId = $(this).data('cart-item-id');

        $.ajax({
            url: updateSize, // Your route URL
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                size: size,
                cartItemId: cartItemId
            }),
            success: function(response) {
                if (response.success) {
                    console.log('Size updated successfully!');

                    // Handle size change

                    let notSizeChoices = [];
                    $(`.size-${cartItemId}`).each(function() {
                        notSizeChoices.push($(this)); // Or use $(this).data('size') if size is a data attribute
                    });

                    const this_val = $(this).html();
                    $("#product-size").val(this_val);
                   notSizeChoices.forEach(el =>{
                       el.removeClass('btn-secondary');
                       el.addClass('btn-success');
                   })

                    $(`#size-${cartItemId}-${size}`).removeClass('btn-success');
                    $(`#size-${cartItemId}-${size}`).addClass('btn-secondary');

                    // Optionally update the UI or alert the user
                } else {
                    console.error('Failed to update size');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX request failed:', error);
            }
        });
    });
});
>>>>>>> 81c2f77de4f19d374190ed90d5e02a09bf0fba5b

    // Event listener for size buttons
    $('.btn-size').off('click').on('click', function() {
        const size = $(this).data('size');
        const cartItemId = $(this).data('cart-item-id');

        $.ajax({
            url: updateSize, // Your route URL
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                size: size,
                cartItemId: cartItemId
            }),
            success: function(response) {
                if (response.success) {
                    console.log('Size updated successfully!');

                    // Handle size change

                    let notSizeChoices = [];
                    $(`.size-${cartItemId}`).each(function() {
                        notSizeChoices.push($(this)); // Or use $(this).data('size') if size is a data attribute
                    });

                    const this_val = $(this).html();
                    $("#product-size").val(this_val);
                   notSizeChoices.forEach(el =>{
                       el.removeClass('btn-secondary');
                       el.addClass('btn-success');
                   })

                    $(`#size-${cartItemId}-${size}`).removeClass('btn-success');
                    $(`#size-${cartItemId}-${size}`).addClass('btn-secondary');

                    // Optionally update the UI or alert the user
                } else {
                    console.error('Failed to update size');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX request failed:', error);
            }
        });
    });
});

$(document).ready(function() {
    // Ensuring the click event is bound only once
    $('#place-order-button').off('click').on('click', function(event) {
        event.preventDefault();
        $('#checkout-form').submit();  // Submitting the form
    });
});
