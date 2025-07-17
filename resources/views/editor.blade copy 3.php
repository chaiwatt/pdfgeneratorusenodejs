<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document Editor</title>
    {{-- ลิงก์ไปยัง CSS ที่แก้ไขแล้ว --}}
    <link rel="stylesheet" href="{{ asset('css/editor.css') }}">
    {{-- เพิ่ม Font Awesome 6 CDN สำหรับไอคอน --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>

    {{-- Toolbar ใหม่ที่ใช้ไอคอนแทนปุ่ม --}}
    <div id="toolbar">
        <div class="toolbar-group">
            <button class="toolbar-button" title="Bold"><i class="fa-solid fa-bold"></i></button>
            <button class="toolbar-button" title="Italic"><i class="fa-solid fa-italic"></i></button>
            <button class="toolbar-button" title="Strikethrough"><i class="fa-solid fa-strikethrough"></i></button>
            <button class="toolbar-button" title="Subscript"><i class="fa-solid fa-subscript"></i></button>
            <button class="toolbar-button" title="Superscript"><i class="fa-solid fa-superscript"></i></button>
        </div>
        <div class="toolbar-separator"></div>
        <div class="toolbar-group">
            <button class="toolbar-button" title="Align Left"><i class="fa-solid fa-align-left"></i></button>
            <button class="toolbar-button" title="Align Center"><i class="fa-solid fa-align-center"></i></button>
            <button class="toolbar-button" title="Align Right"><i class="fa-solid fa-align-right"></i></button>
            <button class="toolbar-button" title="Align Justify"><i class="fa-solid fa-align-justify"></i></button>
        </div>
        <div class="toolbar-separator"></div>
        <div class="toolbar-group">
             <button class="toolbar-button" title="Insert Table"><i class="fa-solid fa-table-cells"></i></button>
             <button class="toolbar-button" title="Insert Image"><i class="fa-solid fa-image"></i></button>
        </div>
        <div class="toolbar-separator"></div>
        <div class="toolbar-group">
            {{-- ไอคอนสำหรับ Export PDF (มีฟังก์ชันการทำงาน) --}}
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
            // คำสั่งนี้สำคัญมาก คงไว้เหมือนเดิม
            document.execCommand('defaultParagraphSeparator', false, 'p');

            const editor = document.getElementById('document-editor');
            // เปลี่ยนเป้าหมายมาที่ปุ่มไอคอนใหม่
            const exportButton = document.getElementById('export-pdf-button');
            const loadingIndicator = document.getElementById('loading-indicator');

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
                pages.forEach((page, index) => {
                    while (isOverflowing(page)) {
                        let nextPage = page.nextElementSibling;
                        if (!nextPage) {
                            nextPage = createNewPage();
                        }
                        if (page.lastChild) {
                            nextPage.insertBefore(page.lastChild, nextPage.firstChild);
                        } else {
                            break;
                        }
                    }
                });
            };

            editor.addEventListener('input', () => {
                setTimeout(managePages, 10);
            });

            // --- Export Logic (ไม่มีการแก้ไข แค่เปลี่ยนตัวแปรที่อ้างอิง) ---
            exportButton.addEventListener('click', () => {
                loadingIndicator.style.display = 'inline-block';
                exportButton.disabled = true;

                const editorClone = editor.cloneNode(true);
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
