$(document).ready(function() {
    const container = $('div[data-update-url][data-remove-url]');
    const updateUrl = container.data('update-url');
    let removeUrl = container.data('remove-url');

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
                    //console.log(totalPrice);

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

        //console.log(articleId);

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

                    //console.log(row);
                    row.remove();

                    // Update the total price
                    let totalPrice = 0;
                    const containerP = document.getElementsByClassName('product-total-price');
                    for (i= 0; i<containerP.length; i++) {
                        totalPrice += parseInt(containerP[i].innerHTML.split("$")[1]);
                    }
                    document.getElementById('subTotalPriceElmText').innerHTML = totalPrice;
                    document.getElementById('totalPriceElmText').innerHTML = totalPrice;
                    console.log(totalPrice);

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
});

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('place-order-button').addEventListener('click', function(event) {
        event.preventDefault();
        document.getElementById('checkout-form').submit();
    });
});
