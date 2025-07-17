<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PDF Generator Editor</title>
    <link rel="stylesheet" href="{{ asset('css/editor.css') }}">
</head>
<body>

    <div id="menubar">
        <button id="export-pdf-button">Export to PDF</button>
        <div id="loading-indicator" style="display: none;">กำลังสร้าง PDF...</div>
    </div>

    <div id="editor-container">
        <div id="document-editor">
            <div class="page" contenteditable="true"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // =================================================================
            // ## ส่วนที่แก้ไข: เพิ่มโค้ด 1 บรรทัดนี้ ##
            // สั่งให้ Editor ใช้ Tag <p> เป็นค่าเริ่มต้นสำหรับการขึ้นย่อหน้าใหม่
            // ซึ่งจะแก้ปัญหาการพิมพ์เองแล้ว format ไม่ถูกต้อง
            document.execCommand('defaultParagraphSeparator', false, 'p');
            // =================================================================

            const editor = document.getElementById('document-editor');
            const exportButton = document.getElementById('export-pdf-button');
            const loadingIndicator = document.getElementById('loading-indicator');

            // --- Page Management ---
            const isOverflowing = (el) => el.scrollHeight > el.clientHeight + 1;

            const createNewPage = () => {
                const newPage = document.createElement('div');
                newPage.className = 'page';
                newPage.setAttribute('contenteditable', 'true');
                // เมื่อสร้างหน้าใหม่ ให้ focus และตั้งค่า default paragraph อีกครั้ง
                editor.appendChild(newPage);
                newPage.focus();
                return newPage;
            };

            const managePages = () => {
                const pages = Array.from(editor.querySelectorAll('.page'));
                pages.forEach((page, index) => {
                    while (isOverflowing(page)) {
                        let nextPage = page.nextElementSibling;
                        if (!nextPage) {
                            nextPage = createNewPage();
                        }
                        // ย้ายโหนดสุดท้ายของหน้าที่ล้นไปไว้บนสุดของหน้าถัดไป
                        if (page.lastChild) {
                            nextPage.insertBefore(page.lastChild, nextPage.firstChild);
                        } else {
                            break; // หยุดถ้าไม่มีอะไรให้ย้าย
                        }
                    }
                });
            };

            // --- Event Listeners ---
            editor.addEventListener('input', () => {
                // ใช้ setTimeout เพื่อให้เบราว์เซอร์วาดหน้าจอเสร็จก่อนคำนวณ
                setTimeout(managePages, 10);
            });

            // --- Export Logic ---
            exportButton.addEventListener('click', () => {
                loadingIndicator.style.display = 'block';
                exportButton.disabled = true;

                // Clone editor content to avoid modifying the live one
                const editorClone = editor.cloneNode(true);
                // Remove contenteditable attribute from all pages in the clone
                editorClone.querySelectorAll('.page').forEach(page => {
                    page.removeAttribute('contenteditable');
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
                    window.open(url, '_blank'); // เปิด PDF ในแท็บใหม่
                    loadingIndicator.style.display = 'none';
                    exportButton.disabled = false;
                })
                .catch(error => {
                    console.error('Export Error:', error);
                    alert(error.message); // ควรเปลี่ยนเป็น UI ที่สวยงามกว่านี้
                    loadingIndicator.style.display = 'none';
                    exportButton.disabled = false;
                });
            });
        });
    </script>
</body>
</html>