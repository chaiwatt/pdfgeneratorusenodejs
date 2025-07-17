<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PdfGeneratorController extends Controller
{
    /**
     * แสดงหน้า Editor หลัก (ไม่มีการแก้ไข)
     */
    public function showEditor()
    {
        return view('editor');
    }

    /**
     * สร้างไฟล์ PDF โดยสร้าง HTML ทั้งหมดใน Controller โดยตรง
     */
    public function exportPdf(Request $request)
    {
        // 1. ตรวจสอบและรับข้อมูล HTML
        $request->validate(['html_content' => 'required|string']);
        $htmlContent = $request->input('html_content');

        // 2. อ่านไฟล์ CSS สำหรับ PDF
        $pdfCssPath = public_path('css/pdf.css');
        $finalCss = '';
        if (File::exists($pdfCssPath)) {
            $cssContent = File::get($pdfCssPath);
            // แปลง path ของฟอนต์ให้เป็น Absolute Path ที่ Puppeteer เข้าใจ
            $fontPath = public_path('fonts/THSarabunNew.ttf');
            $fontUrlPath = 'file:///' . str_replace('\\', '/', $fontPath);
            $finalCss = str_replace("url('/fonts/THSarabunNew.ttf')", "url('{$fontUrlPath}')", $cssContent);
        }

        // --- ส่วนที่แก้ไข: สร้าง HTML ทั้งหมดขึ้นมาเป็น String โดยตรง ---
        // วิธีนี้จะเหมือนกับการใช้ file_get_contents แล้วนำมาต่อกัน ทำให้มั่นใจได้ 100%
        $fullHtml = "<!DOCTYPE html>
<html lang='th'>
<head>
    <meta charset='UTF-8'>
    <title>Document</title>
    <style>
        {$finalCss}
    </style>
</head>
<body>
    {$htmlContent}
</body>
</html>";
        // --- จบส่วนที่แก้ไข ---

        // 5. สร้างชื่อและ path ของไฟล์ชั่วคราว
        $uniqueId = Str::random(16);
        $tempHtmlFileName = "{$uniqueId}.html";
        $outputPdfFileName = "document_{$uniqueId}.pdf";
        $tempHtmlPath = storage_path("app/temp/{$tempHtmlFileName}");
        $outputPdfPath = storage_path("app/temp/{$outputPdfFileName}");

        try {
            // 6. บันทึกไฟล์ HTML ที่สร้างขึ้นลงไฟล์ชั่วคราว
            Storage::disk('temp')->put($tempHtmlFileName, $fullHtml);

            // 7. รันคำสั่งเพื่อสร้าง PDF
            $nodeScriptPath = base_path('generate-pdf.js');
            $nodeExecutable = '"C:\Program Files\nodejs\node.exe"'; // อาจต้องเปลี่ยนตาม OS
            $command = "{$nodeExecutable} {$nodeScriptPath} \"{$tempHtmlPath}\" \"{$outputPdfPath}\"";
            $output = shell_exec($command);

            // 8. ตรวจสอบผลลัพธ์
            if (!Storage::disk('temp')->exists($outputPdfFileName)) {
                throw new \Exception('Node.js script failed to create PDF file. Output: ' . $output);
            }

            // 9. ส่งไฟล์ PDF กลับไป
            $pdfContent = Storage::disk('temp')->get($outputPdfFileName);
            return response($pdfContent)->header('Content-Type', 'application/pdf');

        } catch (\Exception $e) {
            return response("เกิดข้อผิดพลาดในการสร้าง PDF: " . $e->getMessage(), 500);
        } finally {
            // 10. ลบไฟล์ชั่วคราว
            Storage::disk('temp')->delete([$tempHtmlFileName, $outputPdfFileName]);
        }
    }
}
