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

            // --- Table Context Menu Logic ---
            const tableContextMenu = document.getElementById('table-context-menu');
            let currentCell = null; // Store the cell that was right-clicked
            let selectedCells = []; // Store currently selected cells for merging

            // Function to show the context menu
            function showContextMenu(x, y) {
                tableContextMenu.style.left = `${x}px`;
                tableContextMenu.style.top = `${y}px`;
                tableContextMenu.style.display = 'block';
            }

            // Function to hide the context menu
            function hideContextMenu() {
                tableContextMenu.style.display = 'none';
            }

            // Right-click listener on the editor to detect table cells
            // *** MODIFIED ***: Logic to handle selection on right-click
            editor.addEventListener('contextmenu', function(e) {
                const target = e.target;
                const cell = target.closest('td, th'); // Check if the right-clicked element is a table cell

                if (cell) {
                    e.preventDefault(); // Prevent default browser context menu
                    currentCell = cell; // Save the current cell

                    // If the right-clicked cell is NOT part of the current selection,
                    // clear the old selection and select only the current cell.
                    if (!selectedCells.includes(cell)) {
                        clearSelection();
                        cell.classList.add('selected-table-cell');
                        selectedCells.push(cell);
                    }
                    
                    showContextMenu(e.clientX, e.clientY);
                } else {
                    hideContextMenu(); // Hide if clicked outside a cell
                }
            });

            // Hide context menu when clicking anywhere else
            document.addEventListener('click', function(e) {
                if (!tableContextMenu.contains(e.target)) {
                    hideContextMenu();
                }
            });

            // Helper function to get cell coordinates (row, col)
            function getCellCoordinates(cell) {
                const row = cell.closest('tr');
                const table = row.closest('table');
                let rowIndex = -1;
                let colIndex = -1;

                // Create a map of the table to handle rowspans and colspans correctly
                const tableMap = [];
                for (let i = 0; i < table.rows.length; i++) {
                    tableMap.push([]);
                }

                for (let r = 0; r < table.rows.length; r++) {
                    for (let c = 0; c < table.rows[r].cells.length; c++) {
                        const currentCell = table.rows[r].cells[c];
                        let mapCol = 0;
                        while (tableMap[r][mapCol]) {
                            mapCol++;
                        }
                        
                        const rowSpan = parseInt(currentCell.getAttribute('rowspan') || 1);
                        const colSpan = parseInt(currentCell.getAttribute('colspan') || 1);

                        for (let rs = 0; rs < rowSpan; rs++) {
                            for (let cs = 0; cs < colSpan; cs++) {
                                tableMap[r + rs][mapCol + cs] = currentCell;
                            }
                        }

                        if (currentCell === cell) {
                            rowIndex = r;
                            colIndex = mapCol;
                        }
                    }
                }
                return { rowIndex, colIndex };
            }

            // Helper function to create a new cell
            function createNewCell(hasBorders) {
                const td = document.createElement('td');
                td.appendChild(document.createElement('br'));
                return td;
            }

            // --- Context Menu Actions ---

            // Insert Row Above
            document.getElementById('insert-row-above').addEventListener('click', function() {
                if (!currentCell) return;
                const row = currentCell.closest('tr');
                const table = row.closest('table');
                const newRow = document.createElement('tr');
                const numCols = row.children.length;
                for (let i = 0; i < numCols; i++) {
                    newRow.appendChild(createNewCell(table.classList.contains('table-bordered')));
                }
                row.parentNode.insertBefore(newRow, row);
                hideContextMenu();
            });

            // Insert Row Below
            document.getElementById('insert-row-below').addEventListener('click', function() {
                if (!currentCell) return;
                const row = currentCell.closest('tr');
                const table = row.closest('table');
                const newRow = document.createElement('tr');
                const numCols = row.children.length;
                for (let i = 0; i < numCols; i++) {
                    newRow.appendChild(createNewCell(table.classList.contains('table-bordered')));
                }
                row.parentNode.insertBefore(newRow, row.nextSibling);
                hideContextMenu();
            });

            // Insert Row Above with Borders
            document.getElementById('insert-row-above-bordered').addEventListener('click', function() {
                if (!currentCell) return;
                const row = currentCell.closest('tr');
                const table = row.closest('table');
                table.classList.add('table-bordered'); // Ensure table has borders
                const newRow = document.createElement('tr');
                const numCols = row.children.length;
                for (let i = 0; i < numCols; i++) {
                    newRow.appendChild(createNewCell(true));
                }
                row.parentNode.insertBefore(newRow, row);
                hideContextMenu();
            });

            // Insert Row Below with Borders
            document.getElementById('insert-row-below-bordered').addEventListener('click', function() {
                if (!currentCell) return;
                const row = currentCell.closest('tr');
                const table = row.closest('table');
                table.classList.add('table-bordered'); // Ensure table has borders
                const newRow = document.createElement('tr');
                const numCols = row.children.length;
                for (let i = 0; i < numCols; i++) {
                    newRow.appendChild(createNewCell(true));
                }
                row.parentNode.insertBefore(newRow, row.nextSibling);
                hideContextMenu();
            });

            // Insert Column Left
            document.getElementById('insert-col-left').addEventListener('click', function() {
                if (!currentCell) return;
                const table = currentCell.closest('table');
                const cellIndex = Array.from(currentCell.parentNode.children).indexOf(currentCell);
                Array.from(table.rows).forEach(row => {
                    const newCell = createNewCell(table.classList.contains('table-bordered'));
                    row.insertBefore(newCell, row.children[cellIndex]);
                });
                hideContextMenu();
            });

            // Insert Column Right
            document.getElementById('insert-col-right').addEventListener('click', function() {
                if (!currentCell) return;
                const table = currentCell.closest('table');
                const cellIndex = Array.from(currentCell.parentNode.children).indexOf(currentCell);
                Array.from(table.rows).forEach(row => {
                    const newCell = createNewCell(table.classList.contains('table-bordered'));
                    row.insertBefore(newCell, row.children[cellIndex + 1]);
                });
                hideContextMenu();
            });

            // Insert Column Left with Borders
            document.getElementById('insert-col-left-bordered').addEventListener('click', function() {
                if (!currentCell) return;
                const table = currentCell.closest('table');
                table.classList.add('table-bordered'); // Ensure table has borders
                const cellIndex = Array.from(currentCell.parentNode.children).indexOf(currentCell);
                Array.from(table.rows).forEach(row => {
                    const newCell = createNewCell(true);
                    row.insertBefore(newCell, row.children[cellIndex]);
                });
                hideContextMenu();
            });

            // Insert Column Right with Borders
            document.getElementById('insert-col-right-bordered').addEventListener('click', function() {
                if (!currentCell) return;
                const table = currentCell.closest('table');
                table.classList.add('table-bordered'); // Ensure table has borders
                const cellIndex = Array.from(currentCell.parentNode.children).indexOf(currentCell);
                Array.from(table.rows).forEach(row => {
                    const newCell = createNewCell(true);
                    row.insertBefore(newCell, row.children[cellIndex + 1]);
                });
                hideContextMenu();
            });

            // Delete Row
            document.getElementById('delete-row').addEventListener('click', function() {
                if (!currentCell) return;
                const row = currentCell.closest('tr');
                const table = row.closest('table');
                if (table.rows.length > 1) { // Prevent deleting the last row
                    row.remove();
                } else {
                    // If only one row left, remove the entire table
                    table.remove();
                }
                hideContextMenu();
            });

            // Delete Column
            document.getElementById('delete-col').addEventListener('click', function() {
                if (!currentCell) return;
                const table = currentCell.closest('table');
                const cellIndex = Array.from(currentCell.parentNode.children).indexOf(currentCell);
                if (currentCell.parentNode.children.length > 1) { // Prevent deleting the last column
                    Array.from(table.rows).forEach(row => {
                        if (row.children[cellIndex]) {
                            row.children[cellIndex].remove();
                        }
                    });
                } else {
                    // If only one column left, remove the entire table
                    table.remove();
                }
                hideContextMenu();
            });

            // --- Cell Selection Logic (Dragging) ---
            let isDragging = false;
            let startCell = null;
            let endCell = null;

            function clearSelection() {
                selectedCells.forEach(cell => cell.classList.remove('selected-table-cell'));
                selectedCells = [];
            }

            function highlightCells(start, end) {
                clearSelection();
                const table = start.closest('table');
                if (!table) return;

                const startCoords = getCellCoordinates(start);
                const endCoords = getCellCoordinates(end);

                const minRow = Math.min(startCoords.rowIndex, endCoords.rowIndex);
                const maxRow = Math.max(startCoords.rowIndex, endCoords.rowIndex);
                const minCol = Math.min(startCoords.colIndex, endCoords.colIndex);
                const maxCol = Math.max(startCoords.colIndex, endCoords.colIndex);

                for (let r = minRow; r <= maxRow; r++) {
                    const row = table.rows[r];
                    if (row) {
                        for (let c = minCol; c <= maxCol; c++) {
                            // This logic needs to be smarter to handle existing spans
                            // For now, we iterate through cells and check if they fall in the range
                            Array.from(row.cells).forEach(cell => {
                                const cellCoords = getCellCoordinates(cell);
                                if (cellCoords.rowIndex >= minRow && cellCoords.rowIndex <= maxRow &&
                                    cellCoords.colIndex >= minCol && cellCoords.colIndex <= maxCol) {
                                    if (!selectedCells.includes(cell)) {
                                        cell.classList.add('selected-table-cell');
                                        selectedCells.push(cell);
                                    }
                                }
                            });
                        }
                    }
                }
            }
            
            // *** MODIFIED ***: Only start selection on left-click
            editor.addEventListener('mousedown', function(e) {
                // Only handle left-clicks for starting a selection
                if (e.button !== 0) {
                    return;
                }
                const cell = e.target.closest('td, th');
                if (cell) {
                    e.preventDefault(); // Prevent text selection
                    isDragging = true;
                    startCell = cell;
                    endCell = cell;
                    highlightCells(startCell, endCell);
                }
            });

            editor.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                const cell = e.target.closest('td, th');
                if (cell && cell.closest('table') === startCell.closest('table')) { // Ensure same table
                    endCell = cell;
                    highlightCells(startCell, endCell);
                }
            });

            editor.addEventListener('mouseup', function() {
                isDragging = false;
            });

            // Clear selection when clicking outside table or pressing escape
            document.addEventListener('click', function(e) {
                // *** MODIFIED ***: Also check if the click is on the context menu
                if (!e.target.closest('table') && !e.target.closest('.context-menu')) {
                    clearSelection();
                }
            });
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    clearSelection();
                }
            });


            // --- Merge Cells Logic ---
            // *** MODIFIED ***: Rewrote the merge logic to be more robust
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
                
                // Find boundaries and collect content
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

                // Find the top-left cell to preserve
                const topLeftCell = selectedCells.find(cell => {
                    const coords = getCellCoordinates(cell);
                    return coords.rowIndex === minRow && coords.colIndex === minCol;
                });

                if (!topLeftCell) {
                    showCustomAlert('ไม่สามารถรวมเซลล์ที่เลือกได้');
                    hideContextMenu();
                    return;
                }

                // Remove other selected cells
                selectedCells.forEach(cell => {
                    if (cell !== topLeftCell) {
                        cell.remove();
                    }
                });

                // Apply spans and content to the top-left cell
                topLeftCell.setAttribute('rowspan', rowSpan);
                topLeftCell.setAttribute('colspan', colSpan);
                topLeftCell.innerHTML = mergedContent || '<br>'; // Ensure cell is not empty

                clearSelection();
                hideContextMenu();
            });

            // --- Unmerge Cells Logic ---
            document.getElementById('unmerge-cells').addEventListener('click', function() {
                if (!currentCell) return; // Unmerge applies to the right-clicked cell

                const cell = currentCell;
                const row = cell.closest('tr');
                const table = cell.closest('table');

                const originalRowSpan = parseInt(cell.getAttribute('rowspan') || 1);
                const originalColSpan = parseInt(cell.getAttribute('colspan') || 1);

                if (originalRowSpan <= 1 && originalColSpan <= 1) {
                    showCustomAlert('เซลล์นี้ไม่ได้ถูกรวม');
                    hideContextMenu();
                    return;
                }

                const { rowIndex, colIndex } = getCellCoordinates(cell);
                const hasBorders = table.classList.contains('table-bordered');

                // Remove rowspan and colspan attributes
                cell.removeAttribute('rowspan');
                cell.removeAttribute('colspan');

                // Add new cells back to the table
                for (let r = 0; r < originalRowSpan; r++) {
                    const targetRow = table.rows[rowIndex + r];
                    if (targetRow) {
                        for (let c = 0; c < originalColSpan; c++) {
                            if (r === 0 && c === 0) continue; // Skip the original cell
                            
                            const newCell = createNewCell(hasBorders);
                            const insertBeforeCell = targetRow.cells[colIndex + c];
                            if (insertBeforeCell) {
                                targetRow.insertBefore(newCell, insertBeforeCell);
                            } else {
                                targetRow.appendChild(newCell);
                            }
                        }
                    }
                }
                hideContextMenu();
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
                // Remove selected-table-cell class before export
                editorClone.querySelectorAll('.selected-table-cell').forEach(cell => {
                    cell.classList.remove('selected-table-cell');
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
                    showCustomAlert(error.message || 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ');
                    loadingIndicator.style.display = 'none';
                    exportButton.disabled = false;
                });
            });

            // Custom Alert Function (replaces window.alert)
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
