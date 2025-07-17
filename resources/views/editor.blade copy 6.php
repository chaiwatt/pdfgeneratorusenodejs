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
             <button class="toolbar-button" title="Insert Table"><i class="fa-solid fa-table-cells"></i></button>
             {{-- ส่วนที่แก้ไข: เพิ่ม ID และ input สำหรับอัปโหลดรูป --}}
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.execCommand('defaultParagraphSeparator', false, 'p');

            const editor = document.getElementById('document-editor');
            const exportButton = document.getElementById('export-pdf-button');
            const loadingIndicator = document.getElementById('loading-indicator');

            // --- ฟังก์ชันจัดรูปแบบพื้นฐาน ---
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

            // --- ฟังก์ชันขนาดฟอนต์ ---
            const fontSizeSelect = document.getElementById('font-size-select');
            fontSizeSelect.addEventListener('change', (e) => {
                const size = e.target.value;
                if (!size) return;

                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    if (range.collapsed) {
                        editor.focus(); return;
                    }
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

            // --- ส่วนที่แก้ไข: เพิ่มฟังก์ชันการทำงานกับรูปภาพ ---
            const insertImageBtn = document.getElementById('insert-image-btn');
            const imageUpload = document.getElementById('image-upload');
            let savedRange = null; // ตัวแปรสำหรับเก็บตำแหน่ง cursor

            // 1. เมื่อกดปุ่มแทรกรูป ให้จำตำแหน่ง cursor และเปิดหน้าต่างเลือกไฟล์
            insertImageBtn.addEventListener('click', () => {
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    savedRange = selection.getRangeAt(0).cloneRange();
                }
                imageUpload.click();
            });

            // 2. เมื่อเลือกไฟล์รูปแล้ว
            imageUpload.addEventListener('change', (event) => {
                const file = event.target.files[0];
                if (!file || !savedRange) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    const dataUrl = e.target.result;
                    
                    // สร้าง wrapper และรูปภาพ
                    const wrapper = document.createElement('div');
                    wrapper.className = 'resizable-image-wrapper';
                    wrapper.contentEditable = false; // สำคัญมาก: ทำให้พิมพ์ทับไม่ได้

                    const img = document.createElement('img');
                    img.src = dataUrl;

                    const resizer = document.createElement('div');
                    resizer.className = 'resizer';

                    wrapper.appendChild(img);
                    wrapper.appendChild(resizer);

                    // แทรกรูปภาพ ณ ตำแหน่งที่บันทึกไว้
                    savedRange.insertNode(wrapper);

                    // ทำให้รูปภาพที่เพิ่งแทรกถูกเลือก
                    selectImageWrapper(wrapper);

                    // เพิ่ม event listener สำหรับการปรับขนาด
                    resizer.addEventListener('mousedown', initResize, false);
                };
                reader.readAsDataURL(file);

                // รีเซ็ต input เพื่อให้สามารถเลือกไฟล์เดิมซ้ำได้
                imageUpload.value = '';
            });

            // 3. จัดการการเลือก/ไม่เลือกรูปภาพ
            let selectedImageWrapper = null;
            
            function selectImageWrapper(wrapper) {
                // เอา class 'selected' ออกจากอันเก่า (ถ้ามี)
                if (selectedImageWrapper) {
                    selectedImageWrapper.classList.remove('selected');
                }
                // เพิ่ม class 'selected' ให้อันใหม่
                wrapper.classList.add('selected');
                selectedImageWrapper = wrapper;
            }

            document.addEventListener('click', (e) => {
                const wrapper = e.target.closest('.resizable-image-wrapper');
                if (wrapper) {
                    selectImageWrapper(wrapper);
                } else {
                    // ถ้าคลิกนอกรูป ให้ยกเลิกการเลือก
                    if (selectedImageWrapper) {
                        selectedImageWrapper.classList.remove('selected');
                        selectedImageWrapper = null;
                    }
                }
            });

            // 4. จัดการการลบรูปภาพ
            document.addEventListener('keydown', (e) => {
                if (selectedImageWrapper && e.key === 'Delete') {
                    e.preventDefault();
                    selectedImageWrapper.remove();
                    selectedImageWrapper = null;
                }
            });

            // 5. ฟังก์ชันการปรับขนาด
            let startX, startY, startWidth, startHeight;
            
            function initResize(e) {
                e.preventDefault();
                const wrapper = e.target.parentElement;
                startX = e.clientX;
                startY = e.clientY;
                startWidth = parseInt(document.defaultView.getComputedStyle(wrapper).width, 10);
                startHeight = parseInt(document.defaultView.getComputedStyle(wrapper).height, 10);
                document.documentElement.addEventListener('mousemove', doDrag, false);
                document.documentElement.addEventListener('mouseup', stopDrag, false);
            }

            function doDrag(e) {
                const wrapper = selectedImageWrapper;
                if (!wrapper) return;
                const newWidth = startWidth + (e.clientX - startX);
                // ปรับความสูงตามอัตราส่วนเดิม
                const aspectRatio = startHeight / startWidth;
                wrapper.style.width = newWidth + 'px';
                wrapper.style.height = (newWidth * aspectRatio) + 'px';
            }

            function stopDrag(e) {
                document.documentElement.removeEventListener('mousemove', doDrag, false);
                document.documentElement.removeEventListener('mouseup', stopDrag, false);
            }
            // --- จบส่วนฟังก์ชันรูปภาพ ---


            // --- Page Management (ไม่มีการแก้ไข) ---
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
                const pages = Array.from(editor.querySelectorAll('.page'));
                pages.forEach((page) => {
                    while (isOverflowing(page)) {
                        let nextPage = page.nextElementSibling;
                        if (!nextPage) nextPage = createNewPage();
                        if (page.lastChild) {
                            nextPage.insertBefore(page.lastChild, nextPage.firstChild);
                        } else break;
                    }
                });
            };
            editor.addEventListener('input', () => setTimeout(managePages, 10));

            // --- Export Logic (ปรับปรุงให้รองรับรูปภาพ) ---
            exportButton.addEventListener('click', () => {
                loadingIndicator.style.display = 'inline-block';
                exportButton.disabled = true;

                const editorClone = editor.cloneNode(true);
                // ลบกรอบและจุดปรับขนาดออกจาก HTML ที่จะส่งออก
                editorClone.querySelectorAll('.resizable-image-wrapper').forEach(wrapper => {
                    const img = wrapper.querySelector('img');
                    if (img) {
                        // กำหนด style width/height ให้ img โดยตรง
                        img.style.width = wrapper.style.width;
                        img.style.height = wrapper.style.height;
                        wrapper.parentNode.replaceChild(img.cloneNode(true), wrapper);
                    } else {
                        wrapper.remove(); // ลบ wrapper ที่ไม่มีรูปทิ้ง
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
