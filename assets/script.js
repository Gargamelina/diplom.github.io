document.addEventListener('DOMContentLoaded', () => {
    console.log("DOM fully loaded, initializing script...");

    // Validate critical elements
    const openUploadBtn = document.getElementById('openUploadModal');
    if (openUploadBtn) {
        console.log("Found openUploadModal button.");
    } else {
        console.error("openUploadModal button not found!");
    }
    
    // Modal elements for upload and full description
    const uploadModal = document.getElementById('uploadModal');
    const descModal = document.getElementById('descModal');
    const fullDescText = document.getElementById('fullDescText');

    // Open the upload modal when user clicks the designated button
    if (openUploadBtn && uploadModal) {
        openUploadBtn.addEventListener('click', () => {
            console.log("openUploadModal button clicked, opening upload modal.");
            uploadModal.style.display = 'block';
        });
    }

    // Close modal windows when clicking the close button
    document.querySelectorAll('.close-button').forEach((btn) => {
        btn.addEventListener('click', () => {
            const modalId = btn.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            console.log("Close button clicked for modal:", modalId);
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Close any modal if user clicks outside the modal content
    window.addEventListener('click', (event) => {
        document.querySelectorAll('.modal').forEach((modal) => {
            if (event.target === modal) {
                console.log("Clicked outside modal content, closing modal:", modal.id);
                modal.style.display = 'none';
            }
        });
    });

    // Handle upload form submission in the modal
    const modalUploadForm = document.getElementById('modalUploadForm');
    if (modalUploadForm) {
        modalUploadForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            console.log("Modal upload form submitted.");
            const formData = new FormData(modalUploadForm);
            try {
                const response = await fetch('upload.php', {
                    method: 'POST',
                    body: formData
                });
                const raw = await response.text();
                console.log("Raw response from upload.php:", raw);
                const result = JSON.parse(raw);
                showMessage(result.error || result.success, result.error ? 'error' : 'success');
            } catch (error) {
                console.error("Upload error:", error);
                showMessage("Ошибка загрузки файла.", "error");
            }
            if (uploadModal) {
                uploadModal.style.display = 'none';
            }
            fetchFiles(); // Refresh file list without page reload
        });
    } else {
        console.error("Modal upload form (modalUploadForm) not found!");
    }

    // Delegate events on the file list container for deletion and "Подробнее"
    const fileListContainer = document.getElementById('fileList') || document.querySelector('.file-list');
    if (fileListContainer) {
        console.log("File list container found.");
        fileListContainer.addEventListener('click', async (e) => {
            // Handle file deletion
            if (e.target && e.target.classList.contains('btn-delete')) {
                e.preventDefault();
                console.log("Delete button clicked.", e.target);
                const form = e.target.closest('form');
                if (!form) {
                    console.error("Delete form not found.");
                    return;
                }
                try {
                    const formData = new FormData(form);
                    const response = await fetch('delete.php', {
                        method: 'POST',
                        body: formData
                    });
                    const raw = await response.text();
                    console.log("Raw response from delete.php:", raw);
                    const result = JSON.parse(raw);
                    showMessage(result.error || result.success, result.error ? 'error' : 'success');
                } catch (error) {
                    console.error("Delete error:", error);
                    showMessage("Ошибка при удалении файла.", "error");
                }
                fetchFiles(); // Refresh file list after deletion
                return;
            }
            // Handle "Подробнее" button click to show full description modal
            if (e.target && e.target.classList.contains('btn-readmore')) {
                const fullDesc = e.target.getAttribute('data-full-desc');
                console.log("Read more button clicked. Full description:", fullDesc);
                if (descModal && fullDescText) {
                    fullDescText.textContent = fullDesc;
                    descModal.style.display = 'block';
                }
            }
        });
    } else {
        console.error("File list container not found!");
    }

    // (Optional) Legacy support: if an old upload button exists
    const uploadButton = document.getElementById('uploadButton');
    if (uploadButton) {
        console.log("Legacy uploadButton found, adding click handler.");
        uploadButton.addEventListener('click', async (e) => {
            e.preventDefault();
            uploadButton.disabled = true;
            const form = document.getElementById('uploadForm');
            if (!form) {
                console.error("Legacy upload form (uploadForm) not found.");
                uploadButton.disabled = false;
                return;
            }
            try {
                const response = await fetch('upload.php', { method: 'POST', body: new FormData(form) });
                const raw = await response.text();
                console.log("Raw response from legacy upload:", raw);
                const result = JSON.parse(raw);
                showMessage(result.error || result.success, result.error ? 'error' : 'success');
            } catch (error) {
                console.error("Legacy upload error:", error);
                showMessage("Ошибка загрузки файла.", "error");
            }
            uploadButton.disabled = false;
            fetchFiles();
        });
    } else {
        console.log("No legacy uploadButton found, using modal upload only.");
    }

    // Initial fetch to load file list dynamically
    fetchFiles();

    // Slider functionality for product images
    const slider = document.querySelector('.slider');
    if (slider) {
        let slideIndex = 0;
        const slides = slider.querySelectorAll('.slides img');
        if (slides.length > 0) {
            slides[slideIndex].classList.add('active');
        }
        // Expose plusSlides globally so that inline onclick attributes can work
        window.plusSlides = function(n) {
            slides[slideIndex].classList.remove('active');
            slideIndex = (slideIndex + n + slides.length) % slides.length;
            slides[slideIndex].classList.add('active');
        }
    }
});

async function fetchFiles() {
    try {
        console.log("Fetching file list...");
        const response = await fetch('fetch_files.php');
        const raw = await response.text();
        console.log("Raw fetch_files response:", raw);
        const files = JSON.parse(raw);
        const fileList = document.getElementById('fileList') || document.querySelector('.file-list');
        if (!fileList) {
            console.error("File list container not found when fetching files.");
            return;
        }
        // Dynamically build the file list HTML
        let html = '';
        files.forEach(file => {
            const filePath = "uploads/" + encodeURIComponent(file.filename);
            const fileExt = file.filename.split('.').pop().toLowerCase();
            let previewContent = '';
            if (['jpg', 'jpeg', 'png', 'gif', 'jfif', 'webp', 'tiff', 'svg', 'bmp', 'ico'].includes(fileExt)) {
                previewContent = `<img src="${filePath}" alt="${file.filename}" style="max-width:100%; max-height:150px; border-radius:8px; margin-bottom:10px; object-fit:cover;">`;
            } else if (['mp4', 'webm', 'mov', 'avi', 'mkv', 'flv', '3gp'].includes(fileExt)) {
                previewContent = `<video controls style="max-width:100%; max-height:150px; border-radius:8px; margin-bottom:10px; object-fit:cover;">
                                    <source src="${filePath}" type="video/${fileExt}">
                                    Ваш браузер не поддерживает видео.
                                  </video>`;
            } else if (fileExt === 'pdf') {
                previewContent = `<embed src="${filePath}" type="application/pdf" width="100%" height="150px" style="border-radius:8px; margin-bottom:10px;">`;
            } else {
                // Document or other file: show an icon placeholder
                previewContent = `<div class="document-preview">
                                    <i class="document-icon file"></i>
                                    <p>${file.filename}</p>
                                  </div>`;
            }

            // Truncate description if needed
            let descHtml = '';
            if (file.description) {
                const limit = 150;
                let shortDesc = file.description;
                if (file.description.length > limit) {
                    shortDesc = file.description.substring(0, limit) + '...';
                    descHtml = `<p class="file-desc">${shortDesc} <button type="button" class="btn btn-readmore" data-full-desc="${file.description}">Подробнее</button></p>`;
                } else {
                    descHtml = `<p class="file-desc">${file.description}</p>`;
                }
            }

            html += `<div class="file-item">
                        ${previewContent}
                        <p>${file.filename}</p>
                        <p>Загружено пользователем: ${file.uploader}</p>
                        ${descHtml}
                        <a href="${filePath}" download>Загрузить</a>
                        <form action="delete.php" method="post" style="display:inline;">
                            <input type="hidden" name="file_id" value="${file.id}">
                            <button type="button" class="btn btn-delete">Удалить</button>
                        </form>
                     </div>`;
        });
        fileList.innerHTML = html;
        console.log("File list updated. Total files:", files.length);
    } catch (error) {
        console.error("Error fetching files:", error);
    }
}

function showMessage(msg, type) {
    let messageBox = document.getElementById('messageBox');
    if (!messageBox) {
        messageBox = document.createElement('div');
        messageBox.id = 'messageBox';
        messageBox.className = 'message-box';
        // Position the message just under the header (e.g., 70px from the top)
        messageBox.style.top = '70px';
        document.body.appendChild(messageBox);
    }
    messageBox.textContent = msg;
    messageBox.className = 'message-box visible ' + (type === 'error' ? 'error' : 'success');
    setTimeout(() => {
        messageBox.classList.remove('visible');
    }, 5000);
}