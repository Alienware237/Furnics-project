document.addEventListener("DOMContentLoaded", function () {
    handleImageDrop();
    handleSizeQuantityFields();
    handleFormSubmission();
    handleArticleDeletion();
});

function handleImageDrop() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-input');

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', function () {
        dropZone.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', function () {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        const existingFilesCount = fileInput.files.length;
        const newFilesCount = files.length;
        const totalFilesCount = existingFilesCount + newFilesCount;

        if (totalFilesCount > 3) {
            alert('You can only upload a maximum of 3 images.');
            return;
        }

        const dataTransfer = new DataTransfer();
        Array.from(fileInput.files).forEach(file => dataTransfer.items.add(file)); // Preserve existing files

        for (const file of files) {
            if (file.type.startsWith('image/')) {
                let img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.classList.add('dropped-image');

                let fileName = document.createElement('p');
                fileName.textContent = file.name;

                let removeButton = document.createElement('button');
                removeButton.textContent = 'Remove';
                removeButton.addEventListener('click', function () {
                    img.remove();
                    fileName.remove();
                    removeButton.remove();
                    updateFileInput(file);
                });

                let imageContainer = document.createElement('div');
                imageContainer.classList.add('image-container');
                imageContainer.appendChild(img);
                imageContainer.appendChild(fileName);
                imageContainer.appendChild(removeButton);

                dropZone.appendChild(imageContainer);
                dataTransfer.items.add(file);
            } else {
                alert('Only image files are allowed!');
            }
        }
        fileInput.files = dataTransfer.files;
    }

    function updateFileInput(fileToRemove) {
        const dataTransfer = new DataTransfer();
        Array.from(fileInput.files).forEach(file => {
            if (file !== fileToRemove) {
                dataTransfer.items.add(file);
            }
        });
        fileInput.files = dataTransfer.files;
    }
}

function handleSizeQuantityFields() {
    const addButton = document.getElementById('add-size');
    const removeButton = document.getElementById('remove-size');
    let sizeIndex = document.querySelectorAll('#size-container input[type="text"]').length;

    addButton.addEventListener('click', function (e) {
        e.preventDefault();
        addSizeQuantityFields(sizeIndex++);
    });

    removeButton.addEventListener('click', function (e) {
        e.preventDefault();
        removeLastSizeQuantityFields();
        sizeIndex--;
    });
}

function addSizeQuantityFields(index, value = { size: '', quantity: '' }) {
    const sizeContainer = document.getElementById('size-container');

    const newSizeInput = document.createElement('input');
    newSizeInput.type = 'text';
    newSizeInput.name = `sizeAndQuantities[${index}][size]`;
    newSizeInput.placeholder = 'Size';
    newSizeInput.value = value.size;

    const newQuantityInput = document.createElement('input');
    newQuantityInput.type = 'number';
    newQuantityInput.name = `sizeAndQuantities[${index}][quantity]`;
    newQuantityInput.placeholder = 'Quantity';
    newQuantityInput.value = value.quantity;

    sizeContainer.appendChild(newSizeInput);
    sizeContainer.appendChild(newQuantityInput);
}

function removeLastSizeQuantityFields() {
    const sizeContainer = document.getElementById('size-container');
    const removeButton = document.getElementById('remove-size');
    const inputs = sizeContainer.querySelectorAll('input');
    if (inputs.length >= 2) {
        inputs[inputs.length - 1].remove();
        inputs[inputs.length - 2].remove();
    }
}

let editArticleId = null;
function handleFormSubmission() {
    const container = $('div[data-insert-url]');
    const insertUrl = container.data('insert-url');
    $('#article-form').on('submit', function (event) {
        event.preventDefault(); // Prevent the default form submission

        // Create a FormData object to handle the file uploads
        var formData = new FormData(this);

        // Collect dynamic size and quantity fields
        $('#size-container input').each(function () {
            formData.append(this.name, this.value);
        });

        // Append the editArticleId to formData if it exists
        if (editArticleId != null) {
            formData.append('articleId', editArticleId);
        }

        editArticleId = null;

        $.ajax({
            url: insertUrl, // Adjust the path to your route
            type: 'POST',
            data: formData,
            processData: false, // Don't process the files
            contentType: false, // Set content type to false to let jQuery set it
            success: function (response) {
                // Handle the success response here
                console.log('Form submitted successfully:', response);
                alert('Form submitted successfully!');
                // Close the Bootstrap modal
                $('#editProductModal').modal('hide');

                // Remove the modal backdrop manually
                $('.modal-backdrop').remove();

                // Reload the page
                location.reload();
            },
            error: function (xhr, status, error) {
                // Handle the error response here
                console.error('Form submission failed:', error);
                alert('Form submission failed: ' + error);
            }
        });
    });
}

function handleArticleDeletion() {
    document.querySelectorAll('.delete-article').forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            const articleId = this.dataset.articleId;
            fetch(`/article/delete/${articleId}`, {
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
}


/* ============================================================================================================= */
document.addEventListener('DOMContentLoaded', function() {
    const sizeContainer = document.getElementById('size-container');
    let articles;

    const container = $('div[data-articles-url]');
    const getArticleUrl = container.data('articles-url');

    $.ajax({
        url: getArticleUrl,
        method: 'GET',
        contentType: 'application/json',
        dataType: 'json',
        success: function(data) {
            //console.log("Fetched Articles:", data);
            articles = data;
        },
        error: function(xhr, status, error) {
            console.error('Error fetching articles:', error);
        }
    })

    //const formContainer = document.getElementById('article-form-container');
    //formContainer.style.display = 'none';

    // Event listener for modal close event
    $('#editProductModal').on('hidden.bs.modal', function (e) {
        clearSizeContainer(); // Clear size container when modal is closed
    });

// Clear size container function (remove input elements only)
    function clearSizeContainer() {
        // Select all input elements inside sizeContainer and remove them
        const inputs = sizeContainer.querySelectorAll('input');
        inputs.forEach(input => {
            input.parentNode.removeChild(input); // Remove each input element
        });
    }

    function populateForm(articleId) {
        let article = articles.filter(elm => elm.articleId == articleId);
        let articleDescriptions = (JSON.parse(article[0].descriptions));
        let descriptions = articleDescriptions.description ? articleDescriptions.description : '';
        let sizeAndQuantity = articleDescriptions.sizeAndQuantity ? articleDescriptions.sizeAndQuantity : '';
        article['description'] = descriptions;
        article['sizeAndQuantity'] = sizeAndQuantity
        document.getElementById('articleName').value = article.articleName;
        document.getElementById('articleName').value = article[0].articleName;
        document.getElementById('description').value = article.description; // Adjust if needed
        document.getElementById('articlePrice').value = article[0].articlePrice;
        document.getElementById('articleCategory').value = article[0].articleCategory;
        document.getElementById('categoryDescription').value = articleDescriptions.categoryDescription;

        let sizeIndex = 0;

        if (sizeAndQuantity) {
            for (let i= 0; i<sizeAndQuantity.length; i++) {

                // Adding a new field with provided values
                addSizeQuantityFields(sizeIndex++, { size: sizeAndQuantity[i].size, quantity: sizeAndQuantity[i].quantity });
            }
        }
    }

    document.querySelectorAll('.js-edit-product').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            editArticleId = button.dataset.articleId;

            populateForm(editArticleId);
            $('#editProductModal').modal('show');
        });
    });
});


// Delete Article in the list

$(document).ready(function() {
    $('.js-remove-product').on('click', function(e) {
        e.preventDefault();

        let deleteArticleUrl = $(this).data('delete-url');
        var articleId = $(this).data('article-id');
        var row = $(this).closest('tr');

        if (confirm('Are you sure you want to delete this article?')) {
            $.ajax({
                url: deleteArticleUrl,
                type: 'DELETE',
                success: function(response) {
                    // Remove the row from the table
                    row.remove();
                    alert('Article deleted successfully!');
                },
                error: function(xhr) {
                    alert('Failed to delete article: ' + xhr.responseText);
                }
            });
        }
    });
});

