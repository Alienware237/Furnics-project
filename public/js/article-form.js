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
    const sizeContainer = document.getElementById('size-container');
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

    function addSizeQuantityFields(index) {
        const newSizeInput = document.createElement('input');
        newSizeInput.type = 'text';
        newSizeInput.name = `sizeAndQuantities[${index}][size]`;
        newSizeInput.placeholder = 'Size';

        const newQuantityInput = document.createElement('input');
        newQuantityInput.type = 'number';
        newQuantityInput.name = `sizeAndQuantities[${index}][quantity]`;
        newQuantityInput.placeholder = 'Quantity';

        sizeContainer.appendChild(newSizeInput);
        sizeContainer.appendChild(newQuantityInput);
    }

    function removeLastSizeQuantityFields() {
        const inputs = sizeContainer.querySelectorAll('input');
        if (inputs.length >= 2) {
            inputs[inputs.length - 1].remove();
            inputs[inputs.length - 2].remove();
        }
    }
}

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

        // Log the FormData object for debugging purposes
        for (let pair of formData.entries()) {
            console.log(pair[0] + ', ' + pair[1]);
        }

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
