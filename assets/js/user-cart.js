$(document).ready(function() {
    const container = $('div[data-update-url][data-remove-url]');
    const updateUrl = container.data('update-url');
    const removeUrl = container.data('remove-url');

    $('.js-btn-minus, .js-btn-plus').on('click', function() {
        let articleId = $(this).data('product-id');
        let action = $(this).hasClass('js-btn-minus') ? 'decrease' : 'increase';

        $.ajax({
            url: updateUrl,
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ articleId: articleId }),
            success: function(data) {
                if (data.success) {
                    // Update the quantity and price in the DOM
                    let quantityInput = $(`input[data-product-id="${articleId}"]`);
                    quantityInput.val(data.newQuantity);
                    // Update the total price
                    let priceCell = quantityInput.closest('tr').find('.product-total-price');
                    priceCell.text(`$${data.newTotalPrice}`);
                }
            }
        });
    });

    $('.js-remove-product').on('click', function(event) {
        event.preventDefault();
        let articleId = $(this).data('product-id');

        $.ajax({
            url: removeUrl,
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ articleId: articleId }),
            success: function(data) {
                if (data.success) {
                    // Remove the product row from the table
                    let row = $(`tr:has(input[data-product-id="${articleId}"])`);
                    row.remove();
                }
            }
        });
    });
});
