<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document Editor</title>
    <link rel="stylesheet" href="{{ asset('css/editor.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

    <div id="toolbar">
        <div class="toolbar-group">
            <button id="bold-btn" class="toolbar-button" title="Bold"><i class="fa-solid fa-bold"></i></button>
            <button id="italic-btn" class="toolbar-button" title="Italic"><i class="fa-solid fa-italic"></i></button>
            <button id="strikethrough-btn" class="toolbar-button" title="Strikethrough"><i class="fa-solid fa-strikethrough"></i></button>
            <button id="subscript-btn" class="toolbar-button" title="Subscript"><i class="fa-solid fa-subscript"></i></button>
            <button id="superscript-btn" class="toolbar-button" title="Superscript"><i class="fa-solid fa-superscript"></i></button>
        </div>
        <div class="toolbar-separator"></div>
        <div class="toolbar-group">
            <select id="font-size-select" class="toolbar-select" title="Font Size">
                <option value="" disabled selected>Font Size</option>
                <option value="12">12 pt</option>
                <option value="14">14 pt</option>
                <option value="16">16 pt</option>
                <option value="18">18 pt</option>
                <option value="20">20 pt</option>
                <option value="22">22 pt</option>
                <option value="24">24 pt</option>
                <option value="26">26 pt</option>
                <option value="28">28 pt</option>
            </select>
        </div>
        <div class="toolbar-separator"></div>
        <div class="toolbar-group">
            <button id="align-left-btn" class="toolbar-button" title="Align Left"><i class="fa-solid fa-align-left"></i></button>
            <button id="align-center-btn" class="toolbar-button" title="Align Center"><i class="fa-solid fa-align-center"></i></button>
            <button id="align-right-btn" class="toolbar-button" title="Align Right"><i class="fa-solid fa-align-right"></i></button>
        </div>
        <div class="toolbar-separator"></div>
        <div class="toolbar-group">
             <button id="insert-table-btn" class="toolbar-button" title="Insert Table"><i class="fa-solid fa-table-cells"></i></button>
             <button id="insert-image-btn" class="toolbar-button" title="Insert Image"><i class="fa-solid fa-image"></i></button>
             <input type="file" id="image-upload" accept="image/*" style="display: none;" />
        </div>
        <div class="toolbar-separator"></div>
        <div class="toolbar-group">
            <button class="toolbar-button" id="export-pdf-button" title="Export to PDF">
                <i class="fa-regular fa-file-pdf"></i>
            </button>
            <div id="loading-indicator" style="display: none;">
                <i class="fa-solid fa-spinner fa-spin"></i>
            </div>
        </div>
    </div>

    <div id="editor-container">
        <div id="document-editor">
            <div class="page" contenteditable="true"></div>
        </div>
    </div>

    <div id="table-modal" class="modal-backdrop" style="display: none;">
        <div class="modal">
            <div class="modal-header">
                <h3>Insert Table</h3>
                <button id="close-table-modal" class="modal-close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="table-rows">Rows:</label>
                    <input type="number" id="table-rows" value="3" min="1">
                </div>
                <div class="form-group">
                    <label for="table-cols">Columns:</label>
                    <input type="number" id="table-cols" value="3" min="1">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" id="table-border-checkbox" checked>
                        Add borders
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button id="create-table-btn" class="modal-button primary">Create</button>
                <button id="cancel-table-btn" class="modal-button">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.execCommand('defaultParagraphSeparator', false, 'p');

            const editor = document.getElementById('document-editor');
            const exportButton = document.getElementById('export-pdf-button');
            const loadingIndicator = document.getElementById('loading-indicator');

            // --- Formatting Functions ---
            const formatButtons = [
                { id: 'bold-btn', command: 'bold' },
                { id: 'italic-btn', command: 'italic' },
                { id: 'strikethrough-btn', command: 'strikeThrough' },
                { id: 'subscript-btn', command: 'subscript' },
                { id: 'superscript-btn', command: 'superscript' },
                { id: 'align-left-btn', command: 'justifyLeft' },
                { id: 'align-center-btn', command: 'justifyCenter' },
                { id: 'align-right-btn', command: 'justifyRight' },
            ];
            formatButtons.forEach(btnInfo => {
                const button = document.getElementById(btnInfo.id);
                if (button) {
                    button.addEventListener('click', () => {
                        const selection = window.getSelection();
                        if (selection.rangeCount > 0) {
                            const range = selection.getRangeAt(0).cloneRange();
                            document.execCommand(btnInfo.command, false, null);
                            range.collapse(false);
                            selection.removeAllRanges();
                            selection.addRange(range);
                        }
                        editor.focus();
                    });
                }
            });

            // --- Font Size Function ---
            const fontSizeSelect = document.getElementById('font-size-select');
            fontSizeSelect.addEventListener('change', (e) => {
                const size = e.target.value;
                if (!size) return;
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    if (range.collapsed) { editor.focus(); return; }
                    document.execCommand('fontSize', false, '7');
                    const fontElements = editor.querySelectorAll("font[size='7']");
                    let lastSpan = null;
                    fontElements.forEach(fontElement => {
                        const span = document.createElement('span');
                        span.style.fontSize = `${size}pt`;
                        span.innerHTML = fontElement.innerHTML;
                        fontElement.parentNode.replaceChild(span, fontElement);
                        lastSpan = span;
                    });
                    if (lastSpan) {
                        const newRange = document.createRange();
                        newRange.setStartAfter(lastSpan);
                        newRange.collapse(true);
                        selection.removeAllRanges();
                        selection.addRange(newRange);
                    }
                }
                e.target.selectedIndex = 0;
                editor.focus();
            });

            // --- Image Functions ---
            const insertImageBtn = document.getElementById('insert-image-btn');
            const imageUpload = document.getElementById('image-upload');
            let savedRange = null; 
            insertImageBtn.addEventListener('click', () => {
                const selection = window.getSelection();
                if (selection.rangeCount > 0) savedRange = selection.getRangeAt(0).cloneRange();
                imageUpload.click();
            });
            imageUpload.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (!file || !savedRange) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'resizable-image-wrapper';
                    wrapper.contentEditable = false;
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    const resizer = document.createElement('div');
                    resizer.className = 'resizer';
                    wrapper.appendChild(img);
                    wrapper.appendChild(resizer);
                    savedRange.insertNode(wrapper);
                    selectImageWrapper(wrapper);
                    resizer.addEventListener('mousedown', initResize, false);
                };
                reader.readAsDataURL(file);
                imageUpload.value = '';
            });
            let selectedImageWrapper = null;
            function selectImageWrapper(wrapper) {
                if (selectedImageWrapper) selectedImageWrapper.classList.remove('selected');
                wrapper.classList.add('selected');
                selectedImageWrapper = wrapper;
            }
            document.addEventListener('click', (e) => {
                const wrapper = e.target.closest('.resizable-image-wrapper');
                if (wrapper) selectImageWrapper(wrapper);
                else if (selectedImageWrapper) {
                    selectedImageWrapper.classList.remove('selected');
                    selectedImageWrapper = null;
                }
            });
            document.addEventListener('keydown', (e) => {
                if (selectedImageWrapper && e.key === 'Delete') {
                    e.preventDefault();
                    selectedImageWrapper.remove();
                    selectedImageWrapper = null;
                }
            });
            let startX, startY, startWidth, startHeight;
            function initResize(e) {
                e.preventDefault();
                const wrapper = e.target.parentElement;
                startX = e.clientX; startY = e.clientY;
                startWidth = parseInt(document.defaultView.getComputedStyle(wrapper).width, 10);
                startHeight = parseInt(document.defaultView.getComputedStyle(wrapper).height, 10);
                document.documentElement.addEventListener('mousemove', doDrag, false);
                document.documentElement.addEventListener('mouseup', stopDrag, false);
            }
            function doDrag(e) {
                const wrapper = selectedImageWrapper;
                if (!wrapper) return;
                const newWidth = startWidth + (e.clientX - startX);
                const aspectRatio = startHeight / startWidth;
                wrapper.style.width = newWidth + 'px';
                wrapper.style.height = (newWidth * aspectRatio) + 'px';
            }
            function stopDrag(e) {
                document.documentElement.removeEventListener('mousemove', doDrag, false);
                document.documentElement.removeEventListener('mouseup', stopDrag, false);
            }

            // --- Table Functions ---
            const tableModal = document.getElementById('table-modal');
            const insertTableBtn = document.getElementById('insert-table-btn');
            const closeTableModalBtn = document.getElementById('close-table-modal');
            const cancelTableBtn = document.getElementById('cancel-table-btn');
            const createTableBtn = document.getElementById('create-table-btn');
            const showTableModal = () => {
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    savedRange = selection.getRangeAt(0).cloneRange();
                }
                tableModal.style.display = 'flex';
            };
            const hideTableModal = () => {
                tableModal.style.display = 'none';
            };
            insertTableBtn.addEventListener('click', showTableModal);
            closeTableModalBtn.addEventListener('click', hideTableModal);
            cancelTableBtn.addEventListener('click', hideTableModal);
            createTableBtn.addEventListener('click', () => {
                const rows = parseInt(document.getElementById('table-rows').value, 10);
                const cols = parseInt(document.getElementById('table-cols').value, 10);
                const hasBorders = document.getElementById('table-border-checkbox').checked;
                if (rows > 0 && cols > 0 && savedRange) {
                    const table = document.createElement('table');
                    if (hasBorders) {
                        table.className = 'table-bordered';
                    }
                    const tbody = document.createElement('tbody');
                    for (let i = 0; i < rows; i++) {
                        const tr = document.createElement('tr');
                        for (let j = 0; j < cols; j++) {
                            const td = document.createElement('td');
                            td.appendChild(document.createElement('br'));
                            tr.appendChild(td);
                        }
                        tbody.appendChild(tr);
                    }
                    table.appendChild(tbody);
                    const p = document.createElement('p');
                    p.appendChild(document.createElement('br'));
                    savedRange.insertNode(p);
                    savedRange.insertNode(table);
                    hideTableModal();
                    editor.focus();
                }
            });

            // --- Page Management (Add/Remove) ---
            const isOverflowing = (el) => el.scrollHeight > el.clientHeight + 1;
            const createNewPage = () => {
                const newPage = document.createElement('div');
                newPage.className = 'page';
                newPage.setAttribute('contenteditable', 'true');
                editor.appendChild(newPage);
                newPage.focus();
                return newPage;
            };
            const managePages = () => {
                let pages = Array.from(editor.querySelectorAll('.page'));
                pages.forEach((page) => {
                    while (isOverflowing(page)) {
                        let nextPage = page.nextElementSibling;
                        if (!nextPage) nextPage = createNewPage();
                        if (page.lastChild) {
                            nextPage.insertBefore(page.lastChild, nextPage.firstChild);
                        } else break;
                    }
                });
                pages = Array.from(editor.querySelectorAll('.page'));
                if (pages.length > 1) {
                    for (let i = 1; i < pages.length; i++) {
                        const page = pages[i];
                        const isEmpty = page.textContent.trim() === '' && page.children.length === 0;
                        if (isEmpty) {
                            page.remove();
                        }
                    }
                }
            };
            editor.addEventListener('input', () => setTimeout(managePages, 10));
            editor.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' || e.key === 'Delete') {
                    setTimeout(managePages, 10);
                }
            });

            // --- Export Logic ---
            exportButton.addEventListener('click', () => {
                loadingIndicator.style.display = 'inline-block';
                exportButton.disabled = true;
                const editorClone = editor.cloneNode(true);
                editorClone.querySelectorAll('.resizable-image-wrapper').forEach(wrapper => {
                    const img = wrapper.querySelector('img');
                    if (img) {
                        img.style.width = wrapper.style.width;
                        img.style.height = wrapper.style.height;
                        wrapper.parentNode.replaceChild(img.cloneNode(true), wrapper);
                    } else {
                        wrapper.remove();
                    }
                });
                const htmlContent = editorClone.innerHTML;
                fetch("{{ route('pdf.export') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ html_content: htmlContent })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => { throw new Error('เกิดข้อผิดพลาดจาก Server: ' + text) });
                    }
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    window.open(url, '_blank');
                    loadingIndicator.style.display = 'none';
                    exportButton.disabled = false;
                })
                .catch(error => {
                    console.error('Export Error:', error);
                    alert(error.message);
                    loadingIndicator.style.display = 'none';
                    exportButton.disabled = false;
                });
            });
        });
    </script>
</body>
</html>
