const deleteButtons = document.querySelectorAll('.delete-article');
deleteButtons.forEach(button => {
    button.addEventListener('click', function(event) {
        event.preventDefault();
        const articleId = this.dataset.articleId;
        fetch(`/article/delete/${articleId}`, { // Updated URL
            method: 'DELETE',
        })
            .then(response => {
                if (response.ok) {
                    const row = document.getElementById(`article_${articleId}`);
                    if (row) {
                        row.remove();
                    }
                } else {
                    console.error('Failed to delete article');
                }
            })
            .catch(error => {
                console.error('Error deleting article:', error);
            });
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.delete-article');
    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const articleId = this.dataset.articleId;
            const rowId = 'article_' + articleId;
            const row = document.getElementById(rowId);
            if (row) {
                // Make an AJAX request to delete the article
                fetch(this.href, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                })
                    .then(response => {
                        if (response.ok) {
                            // If deletion is successful, remove the row from the table
                            row.remove();
                        } else {
                            // Handle errors if necessary
                            console.error('Failed to delete the article');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        });
    });
});

// Handle drag and drop

document.addEventListener("DOMContentLoaded", function() {
    const dropZone = document.getElementById('drop-zone');

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', function() {
        dropZone.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        handleFiles(files);
    });

    const fileInput = document.getElementById('file-input');

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        for (const file of files) {
            // Display the selected image
            let img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.classList.add('dropped-image'); // Add a class to the image element

            // Create a button to remove the image
            let removeButton = document.createElement('button');
            removeButton.textContent = 'Remove';
            removeButton.addEventListener('click', function() {
                img.remove(); // Remove the image when the button is clicked
                removeButton.remove(); // Remove the button itself
            });

            // Create a container div to hold the image and the remove button
            let imageContainer = document.createElement('div');
            imageContainer.classList.add('image-container');
            imageContainer.appendChild(img);
            imageContainer.appendChild(removeButton);

            // Append the container to the drop zone
            dropZone.appendChild(imageContainer);
        }
    }
});

// Dynamically handle of add or remove input fields for sizeQuantities

document.addEventListener("DOMContentLoaded", function() {
    const addButton = document.getElementById('add-size');
    const removeButton = document.getElementById('remove-size');
    const sizeContainer = document.getElementById('size-container');

    addButton.addEventListener('click', function(e) {
        e.preventDefault();
        const newSizeInput = document.createElement('input');
        newSizeInput.type = 'text';
        newSizeInput.name = 'Size';
        newSizeInput.placeholder = 'Size';

        const newQuantityInput = document.createElement('input');
        newQuantityInput.type = 'number';
        newQuantityInput.name = 'Quantity';
        newQuantityInput.placeholder = 'Quantity';

        sizeContainer.appendChild(newSizeInput);
        sizeContainer.appendChild(newQuantityInput);
    });

    removeButton.addEventListener('click', function(e) {
        e.preventDefault();
        const inputs = sizeContainer.querySelectorAll('input');
        const lastSizeInput = inputs[inputs.length - 1];
        const lastQuantityInput = inputs[inputs.length -2]
        if (lastSizeInput && lastQuantityInput) {
            lastSizeInput.remove();
            lastQuantityInput.remove();
        }
    });
});

