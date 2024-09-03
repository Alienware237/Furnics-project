document.getElementById('add-to-cart-form').addEventListener('submit', function(event) {
    var button = event.target.querySelector('button[type="submit"]');
    button.disabled = true;
});