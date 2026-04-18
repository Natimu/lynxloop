const imageInput = document.getElementById('images');
const previewContainer = document.getElementById('image-preview-container');
const dropZone = document.getElementById('drop-zone');
const maxImages = 6;

if (imageInput && previewContainer && dropZone) {
    let selectedFiles = [];

    const isDuplicateFile = (file) => selectedFiles.some((existingFile) => (
        existingFile.name === file.name
        && existingFile.size === file.size
        && existingFile.lastModified === file.lastModified
    ));

    const updateFileInput = () => {
        const dataTransfer = new DataTransfer();

        selectedFiles.forEach((file) => {
            dataTransfer.items.add(file);
        });

        imageInput.files = dataTransfer.files;
    };

    const renderPreviews = () => {
        previewContainer.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();

            reader.addEventListener('load', (event) => {
                const wrapper = document.createElement('div');
                wrapper.classList.add('preview-item');

                const image = document.createElement('img');
                image.src = event.target?.result ?? '';
                image.alt = 'Selected image preview';

                const removeButton = document.createElement('button');
                removeButton.type = 'button';
                removeButton.textContent = 'Remove';
                removeButton.classList.add('remove-image-btn');
                removeButton.addEventListener('click', () => {
                    selectedFiles.splice(index, 1);
                    updateFileInput();
                    renderPreviews();
                });

                wrapper.appendChild(image);
                wrapper.appendChild(removeButton);
                previewContainer.appendChild(wrapper);
            });

            reader.readAsDataURL(file);
        });
    };

    const handleFiles = (files) => {
        for (const file of files) {
            if (!file.type.startsWith('image/')) {
                continue;
            }

            if (isDuplicateFile(file)) {
                continue;
            }

            if (selectedFiles.length >= maxImages) {
                alert('Maximum 6 images allowed.');
                break;
            }

            selectedFiles.push(file);
        }

        updateFileInput();
        renderPreviews();
    };

    imageInput.addEventListener('change', function () {
        handleFiles(Array.from(this.files));
    });

    dropZone.addEventListener('click', () => {
        imageInput.click();
    });

    dropZone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropZone.classList.remove('drag-over');
        handleFiles(Array.from(event.dataTransfer.files));
    });
}
