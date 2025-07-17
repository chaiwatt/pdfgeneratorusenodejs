<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfGeneratorController extends Controller
{
    /**
     * แสดงหน้า Editor หลัก
     */
    public function showEditor()
    {
        return view('editor');
    }

    /**
     * รับ HTML จากหน้าจอ, สร้างเป็นไฟล์ PDF, และส่งกลับให้ผู้ใช้
     */
   public function exportPdf(Request $request)
    {
        // 1. ตรวจสอบและรับข้อมูล HTML จาก request
        $request->validate(['html_content' => 'required|string']);
        $htmlContent = $request->input('html_content');

        // 2. สร้างชื่อไฟล์ชั่วคราวที่ไม่ซ้ำกัน
        $uniqueId = Str::random(16);
        $tempHtmlFileName = "{$uniqueId}.html";
        $outputPdfFileName = "document_{$uniqueId}.pdf";

        // 3. สร้าง Path ที่สมบูรณ์และเชื่อถือได้ด้วย storage_path()
        $tempHtmlPath = storage_path("app/temp/{$tempHtmlFileName}");
        $outputPdfPath = storage_path("app/temp/{$outputPdfFileName}");

        // 4. สร้างไฟล์ HTML ที่สมบูรณ์แบบสำหรับ Puppeteer
        $fullHtml = view('pdf_template', ['content' => $htmlContent])->render();

        try {
            // 5. บันทึก HTML ลงในไฟล์ชั่วคราว
            Storage::disk('temp')->put($tempHtmlFileName, $fullHtml);

            // 6. กำหนด Path ไปยัง Node.js script และตัวโปรแกรม Node
            $nodeScriptPath = base_path('generate-pdf.js');
            $nodeExecutable = '"C:\Program Files\nodejs\node.exe"';

            // *** ส่วนที่แก้ไข: เปลี่ยนมาใช้ shell_exec() ***
            // 7. สร้างคำสั่งทั้งหมดให้เป็น string เดียว
            $command = "{$nodeExecutable} {$nodeScriptPath} {$tempHtmlPath} {$outputPdfPath}";

            // 8. สั่งรันคำสั่งผ่าน shell_exec()
            //    shell_exec จะคืนค่า output ออกมา (ถ้ามี)
            $output = shell_exec($command);

            // 9. ตรวจสอบว่าไฟล์ PDF ถูกสร้างขึ้นจริงหรือไม่
            if (!Storage::disk('temp')->exists($outputPdfFileName)) {
                // ถ้าไฟล์ไม่ถูกสร้าง ให้โยน error พร้อมกับ output ที่ได้จาก node.js (ถ้ามี)
                throw new \Exception('Node.js script failed to create PDF file. Output: ' . $output);
            }

            // 10. อ่านเนื้อหาของไฟล์ PDF ที่สร้างเสร็จแล้ว
            $pdfContent = Storage::disk('temp')->get($outputPdfFileName);

            // 11. ส่งข้อมูล PDF กลับไปให้เบราว์เซอร์
            return response($pdfContent)->header('Content-Type', 'application/pdf');

        } catch (\Exception $e) {
            return response("เกิดข้อผิดพลาดในการสร้าง PDF: " . $e->getMessage(), 500);
        } finally {
            // 12. ลบไฟล์ชั่วคราวทิ้งเสมอ
            Storage::disk('temp')->delete([$tempHtmlFileName, $outputPdfFileName]);
        }
    }
}