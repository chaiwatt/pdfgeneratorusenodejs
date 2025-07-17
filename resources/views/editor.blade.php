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
            <!-- *** NEW: Load Template Button *** -->
            <button class="toolbar-button" id="load-template-btn" title="Load Template">
                <i class="fa-solid fa-cloud-arrow-down"></i>
            </button>
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

    <!-- Context Menu for Tables -->
    <div id="table-context-menu" class="context-menu" style="display: none;">
        <div class="context-menu-item" id="insert-row-above">เพิ่มแถว (บน)</div>
        <div class="context-menu-item" id="insert-row-below">เพิ่มแถว (ล่าง)</div>
        <div class="context-menu-item" id="insert-row-above-bordered">เพิ่มแถวมีเส้นขอบ (บน)</div>
        <div class="context-menu-item" id="insert-row-below-bordered">เพิ่มแถวมีเส้นขอบ (ล่าง)</div>
        <div class="context-menu-separator"></div>
        <div class="context-menu-item" id="insert-col-left">เพิ่มคอลัมน์ (ซ้าย)</div>
        <div class="context-menu-item" id="insert-col-right">เพิ่มคอลัมน์ (ขวา)</div>
        <div class="context-menu-item" id="insert-col-left-bordered">เพิ่มคอลัมน์มีเส้นขอบ (ซ้าย)</div>
        <div class="context-menu-item" id="insert-col-right-bordered">เพิ่มคอลัมน์มีเส้นขอบ (ขวา)</div>
        <div class="context-menu-separator"></div>
        <div class="context-menu-item" id="delete-row">ลบแถว</div>
        <div class="context-menu-item" id="delete-col">ลบคอลัมน์</div>
        <div class="context-menu-separator"></div>
        <div class="context-menu-item" id="merge-cells">รวมเซลล์</div>
        <div class="context-menu-item" id="unmerge-cells">ยกเลิกการรวมเซลล์</div>
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
                    const imgSrc = e.target.result;

                    const tempImg = new Image();
                    tempImg.onload = () => {
                        const page = savedRange.startContainer.closest('.page');
                        const pageContentWidth = page ? page.clientWidth : 600;

                        let initialWidth = tempImg.naturalWidth;
                        let initialHeight = tempImg.naturalHeight;

                        if (initialWidth > pageContentWidth) {
                            const ratio = pageContentWidth / initialWidth;
                            initialWidth = pageContentWidth;
                            initialHeight = initialHeight * ratio;
                        }

                        const wrapper = document.createElement('div');
                        wrapper.className = 'resizable-image-wrapper';
                        wrapper.contentEditable = false;
                        wrapper.style.width = `${initialWidth}px`;
                        wrapper.style.height = `${initialHeight}px`;

                        const img = document.createElement('img');
                        img.src = imgSrc;
                        
                        const resizer = document.createElement('div');
                        resizer.className = 'resizer';
                        
                        wrapper.appendChild(img);
                        wrapper.appendChild(resizer);
                        
                        savedRange.insertNode(wrapper);
                        selectImageWrapper(wrapper);
                        resizer.addEventListener('mousedown', initResize, false);
                    };
                    tempImg.src = imgSrc;
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
                            const td = createNewCell();
                            tr.appendChild(td);
                        }
                        tbody.appendChild(tr);
                    }
                    table.appendChild(tbody);
                    const p = document.createElement('p');
                    p.appendChild(document.createElement('br'));
                    savedRange.insertNode(p);
                    savedRange.insertNode(table);
                    
                    makeTableResizable(table);

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

            // --- Table Context Menu & Cell Selection Logic ---
            const tableContextMenu = document.getElementById('table-context-menu');
            let currentCell = null;
            let selectedCells = []; 
            let isMouseDown = false;
            let hasDragged = false;
            let startCell = null;

            function clearSelection() {
                if (selectedCells.length > 0) {
                    selectedCells.forEach(cell => cell.classList.remove('selected-table-cell'));
                    selectedCells = [];
                }
            }

            function highlightCells(start, end) {
                clearSelection();
                const table = start.closest('table');
                if (!table || !end || end.closest('table') !== table) return;

                const startCoords = getCellCoordinates(start);
                const endCoords = getCellCoordinates(end);

                const minRow = Math.min(startCoords.rowIndex, endCoords.rowIndex);
                const maxRow = Math.max(startCoords.rowIndex, endCoords.rowIndex);
                const minCol = Math.min(startCoords.colIndex, endCoords.colIndex);
                const maxCol = Math.max(startCoords.colIndex, endCoords.colIndex);

                Array.from(table.querySelectorAll('td, th')).forEach(cell => {
                    const cellCoords = getCellCoordinates(cell);
                    if (cellCoords.rowIndex >= minRow && cellCoords.rowIndex <= maxRow &&
                        cellCoords.colIndex >= minCol && cellCoords.colIndex <= maxCol) {
                        cell.classList.add('selected-table-cell');
                        selectedCells.push(cell);
                    }
                });
            }

            editor.addEventListener('mousedown', function(e) {
                if (e.button !== 0) return; 

                const cell = e.target.closest('td, th');
                if (cell) {
                    isMouseDown = true;
                    hasDragged = false;
                    startCell = cell;
                } else {
                    clearSelection();
                }
            });

            editor.addEventListener('mousemove', function(e) {
                if (!isMouseDown || !startCell) return;
                if (!hasDragged) {
                    hasDragged = true;
                    window.getSelection().removeAllRanges();
                }
                const endCell = e.target.closest('td, th');
                highlightCells(startCell, endCell);
            });

            document.addEventListener('mouseup', function(e) {
                if (!isMouseDown) return;
                if (isMouseDown && !hasDragged) {
                    clearSelection();
                }
                isMouseDown = false;
                hasDragged = false;
                startCell = null;
            });

            editor.addEventListener('contextmenu', function(e) {
                const cell = e.target.closest('td, th');
                if (cell) {
                    e.preventDefault();
                    currentCell = cell;
                    if (!cell.classList.contains('selected-table-cell')) {
                        clearSelection();
                        cell.classList.add('selected-table-cell');
                        selectedCells.push(cell);
                    }
                    showContextMenu(e.pageX, e.pageY);
                } else {
                    hideContextMenu();
                }
            });
            
            function showContextMenu(x, y) {
                tableContextMenu.style.left = `${x}px`;
                tableContextMenu.style.top = `${y}px`;
                tableContextMenu.style.display = 'block';
            }

            function hideContextMenu() {
                tableContextMenu.style.display = 'none';
            }

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.context-menu')) {
                    hideContextMenu();
                }
            });
            
            function getCellCoordinates(cell) {
                const row = cell.closest('tr');
                const table = row.closest('table');
                let rowIndex = -1;
                let colIndex = -1;

                const tableMap = [];
                for (let i = 0; i < table.rows.length; i++) {
                    tableMap.push([]);
                }

                for (let r = 0; r < table.rows.length; r++) {
                    for (let c = 0; c < table.rows[r].cells.length; c++) {
                        const currentCellInLoop = table.rows[r].cells[c];
                        let mapCol = 0;
                        while (tableMap[r][mapCol]) {
                            mapCol++;
                        }
                        
                        const rowSpan = parseInt(currentCellInLoop.getAttribute('rowspan') || 1);
                        const colSpan = parseInt(currentCellInLoop.getAttribute('colspan') || 1);

                        for (let rs = 0; rs < rowSpan; rs++) {
                            for (let cs = 0; cs < colSpan; cs++) {
                                if (tableMap[r + rs]) {
                                    tableMap[r + rs][mapCol + cs] = currentCellInLoop;
                                }
                            }
                        }

                        if (currentCellInLoop === cell) {
                            rowIndex = r;
                            colIndex = mapCol;
                        }
                    }
                }
                return { rowIndex, colIndex };
            }

            function createNewCell() {
                const td = document.createElement('td');
                td.style.fontSize = '16pt';
                td.appendChild(document.createElement('br'));
                return td;
            }

            // --- Context Menu Actions ---
            document.getElementById('insert-row-above').addEventListener('click', function() {
                if (!currentCell) return;
                const row = currentCell.closest('tr');
                const newRow = row.cloneNode(false);
                for (let i = 0; i < row.cells.length; i++) {
                    newRow.appendChild(createNewCell());
                }
                row.parentNode.insertBefore(newRow, row);
                hideContextMenu();
            });

            document.getElementById('insert-row-below').addEventListener('click', function() {
                if (!currentCell) return;
                const row = currentCell.closest('tr');
                const newRow = row.cloneNode(false);
                for (let i = 0; i < row.cells.length; i++) {
                    newRow.appendChild(createNewCell());
                }
                row.parentNode.insertBefore(newRow, row.nextSibling);
                hideContextMenu();
            });
            
            document.getElementById('delete-row').addEventListener('click', function() {
                if (!currentCell) return;
                const row = currentCell.closest('tr');
                if (row.parentElement.rows.length > 1) {
                    row.remove();
                } else {
                    row.closest('table').remove();
                }
                hideContextMenu();
            });
            
            document.getElementById('delete-col').addEventListener('click', function() {
                if (!currentCell) return;
                const table = currentCell.closest('table');
                const { colIndex } = getCellCoordinates(currentCell);

                if (currentCell.parentElement.cells.length > 1) {
                    Array.from(table.rows).forEach(row => {
                        const cellToRemove = Array.from(row.cells).find(cell => getCellCoordinates(cell).colIndex === colIndex);
                        if (cellToRemove) cellToRemove.remove();
                    });
                } else {
                    table.remove();
                }
                hideContextMenu();
            });

            document.getElementById('merge-cells').addEventListener('click', function() {
                if (selectedCells.length <= 1) {
                    showCustomAlert('โปรดเลือกอย่างน้อย 2 เซลล์เพื่อรวม');
                    hideContextMenu();
                    return;
                }

                const table = selectedCells[0].closest('table');
                if (!table) return;

                let minRow = Infinity, maxRow = -1, minCol = Infinity, maxCol = -1;
                let mergedContent = '';
                
                selectedCells.forEach(cell => {
                    const coords = getCellCoordinates(cell);
                    minRow = Math.min(minRow, coords.rowIndex);
                    maxRow = Math.max(maxRow, coords.rowIndex);
                    minCol = Math.min(minCol, coords.colIndex);
                    maxCol = Math.max(maxCol, coords.colIndex);
                    
                    const cellContent = cell.innerHTML.replace(/<br\s*\/?>/gi, '').trim();
                    if(cellContent) {
                        mergedContent += (mergedContent ? ' ' : '') + cell.innerHTML;
                    }
                });

                const rowSpan = maxRow - minRow + 1;
                const colSpan = maxCol - minCol + 1;

                const topLeftCell = selectedCells.find(cell => {
                    const coords = getCellCoordinates(cell);
                    return coords.rowIndex === minRow && coords.colIndex === minCol;
                });

                if (!topLeftCell) {
                    showCustomAlert('ไม่สามารถรวมเซลล์ที่เลือกได้');
                    hideContextMenu();
                    return;
                }

                selectedCells.forEach(cell => {
                    if (cell !== topLeftCell) {
                        cell.remove();
                    }
                });

                topLeftCell.setAttribute('rowspan', rowSpan);
                topLeftCell.setAttribute('colspan', colSpan);
                topLeftCell.innerHTML = mergedContent || '<br>';

                clearSelection();
                hideContextMenu();
            });

            document.getElementById('unmerge-cells').addEventListener('click', function() {
                if (!currentCell) return;
                const cell = currentCell;
                const table = cell.closest('table');
                const originalRowSpan = parseInt(cell.getAttribute('rowspan') || 1);
                const originalColSpan = parseInt(cell.getAttribute('colspan') || 1);

                if (originalRowSpan <= 1 && originalColSpan <= 1) {
                    showCustomAlert('เซลล์นี้ไม่ได้ถูกรวม');
                    hideContextMenu();
                    return;
                }

                const { rowIndex, colIndex } = getCellCoordinates(cell);
                cell.removeAttribute('rowspan');
                cell.removeAttribute('colspan');

                for (let r = 0; r < originalRowSpan; r++) {
                    const targetRow = table.rows[rowIndex + r];
                    if (targetRow) {
                        for (let c = 0; c < originalColSpan; c++) {
                            if (r === 0 && c === 0) continue;
                            const newCell = createNewCell();
                            const insertBeforeCell = Array.from(targetRow.cells).find(c => getCellCoordinates(c).colIndex >= colIndex + c);
                            targetRow.insertBefore(newCell, insertBeforeCell || null);
                        }
                    }
                }
                hideContextMenu();
            });

            // --- Table Column Resizing Logic ---
            function makeTableResizable(table) {
                const colgroup = table.querySelector('colgroup') || document.createElement('colgroup');
                const firstRow = table.querySelector('tr');
                if (!firstRow) return;

                while (colgroup.firstChild) {
                    colgroup.removeChild(colgroup.firstChild);
                }

                const cols = Array.from(firstRow.children);
                cols.forEach(() => {
                    const col = document.createElement('col');
                    colgroup.appendChild(col);
                });
                
                if (!table.querySelector('colgroup')) {
                    table.prepend(colgroup);
                }

                cols.forEach((colCell, index) => {
                    if (index === cols.length - 1) return;
                    
                    const oldResizer = colCell.querySelector('.col-resizer');
                    if (oldResizer) oldResizer.remove();

                    const resizer = document.createElement('div');
                    resizer.className = 'col-resizer';
                    colCell.appendChild(resizer);
                    
                    resizer.addEventListener('mousedown', initColResize);
                });
            }

            let currentResizer;
            let resizeStartX;
            let startWidthCol;
            let nextColStartWidth;
            let tableBeingResized;
            let colBeingResized;
            let nextCol;

            function initColResize(e) {
                e.preventDefault();
                e.stopPropagation();

                currentResizer = e.target;
                tableBeingResized = currentResizer.closest('table');
                const th = currentResizer.parentElement;
                const colIndex = Array.from(th.parentElement.children).indexOf(th);
                
                const colgroup = tableBeingResized.querySelector('colgroup');
                colBeingResized = colgroup.children[colIndex];
                nextCol = colgroup.children[colIndex + 1];

                resizeStartX = e.clientX;
                startWidthCol = colBeingResized.offsetWidth;
                nextColStartWidth = nextCol.offsetWidth;

                document.addEventListener('mousemove', doColResize);
                document.addEventListener('mouseup', stopColResize);
            }

            function doColResize(e) {
                if (!colBeingResized || !nextCol) return;

                const diffX = e.clientX - resizeStartX;
                const minWidth = 20;

                let newWidth = startWidthCol + diffX;
                let newNextWidth = nextColStartWidth - diffX;

                if (newWidth < minWidth || newNextWidth < minWidth) {
                    return;
                }

                colBeingResized.style.width = `${newWidth}px`;
                nextCol.style.width = `${newNextWidth}px`;
            }

            function stopColResize() {
                document.removeEventListener('mousemove', doColResize);
                document.removeEventListener('mouseup', stopColResize);
            }

            document.querySelectorAll('.page table').forEach(makeTableResizable);

            // --- *** NEW: Load Template Logic *** ---
            const loadTemplateBtn = document.getElementById('load-template-btn');
            loadTemplateBtn.addEventListener('click', () => {
                loadingIndicator.style.display = 'inline-block';
                loadTemplateBtn.disabled = true;
                
                fetch("{{ route('template.load') }}")
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('ไม่สามารถโหลดเทมเพลตได้');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const firstPage = editor.querySelector('.page');
                        if (firstPage) {
                            firstPage.innerHTML = data.html;
                            const newTable = firstPage.querySelector('table');
                            if (newTable) {
                                makeTableResizable(newTable);
                            }
                        }
                        loadingIndicator.style.display = 'none';
                        loadTemplateBtn.disabled = false;
                    })
                    .catch(error => {
                        console.error('Load Template Error:', error);
                        showCustomAlert(error.message);
                        loadingIndicator.style.display = 'none';
                        loadTemplateBtn.disabled = false;
                    });
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
                editorClone.querySelectorAll('.selected-table-cell').forEach(cell => {
                    cell.classList.remove('selected-table-cell');
                });
                editorClone.querySelectorAll('.col-resizer').forEach(resizer => resizer.remove());

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
                    showCustomAlert(error.message || 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ');
                    loadingIndicator.style.display = 'none';
                    exportButton.disabled = false;
                });
            });

            // Custom Alert Function
            function showCustomAlert(message) {
                const alertModal = document.createElement('div');
                alertModal.className = 'modal-backdrop';
                alertModal.innerHTML = `
                    <div class="modal">
                        <div class="modal-header">
                            <h3>ข้อผิดพลาด</h3>
                            <button class="modal-close-btn">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button class="modal-button primary">ตกลง</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(alertModal);

                const closeBtn = alertModal.querySelector('.modal-close-btn');
                const okBtn = alertModal.querySelector('.modal-button.primary');

                const closeModal = () => {
                    alertModal.remove();
                };

                closeBtn.addEventListener('click', closeModal);
                okBtn.addEventListener('click', closeModal);
            }
        });
    </script>
</body>
</html>
