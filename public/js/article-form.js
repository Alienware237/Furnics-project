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

// ======================= Manager of article list ==============================
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

// ========== Image of article

let images = [];
document.addEventListener("DOMContentLoaded", function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');  // Ensure this ID matches the form type
    const form = document.querySelector('form');

    if (!dropZone || !fileInput || !form) {
        console.error('One or more required elements are missing.');
        return;
    }

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

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        for (const file of files) {
            let img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.classList.add('dropped-image');

            let removeButton = document.createElement('button');
            removeButton.textContent = 'Remove';
            removeButton.addEventListener('click', function() {
                img.remove();
                removeButton.remove();
            });

            let imageContainer = document.createElement('div');
            imageContainer.classList.add('image-container');
            imageContainer.appendChild(img);
            imageContainer.appendChild(removeButton);

            dropZone.appendChild(imageContainer);
        }

        let dataTransfer = new DataTransfer();
        [...files].forEach(file => dataTransfer.items.add(file));
        fileInput.files = dataTransfer.files;
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        let formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData
        }).then(response => {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Network response was not ok.');
        }).then(data => {
            if (data.success) {
                alert('Article created successfully!');
                console.log('Uploaded files:', data.uploadedFiles);
                form.reset();
                document.querySelectorAll('.image-container').forEach(container => container.remove());
            } else if (data.error) {
                alert('Error: ' + data.error);
            }
        }).catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the article.');
        });
    });
});





// Dinamically handle of add or remove input fields for sizeQuantities

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

