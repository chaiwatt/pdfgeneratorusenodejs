// generate-pdf.js
// หมายเหตุ: ไฟล์นี้ควรอยู่ที่โฟลเดอร์หลักของโปรเจกต์ Laravel

const puppeteer = require('puppeteer');
const path = require('path');

// ใช้ IIFE (Immediately Invoked Function Expression) เพื่อให้สามารถใช้ async/await ได้
(async () => {
    // --- 1. รับ Path ของไฟล์จาก Command Line ---
    // process.argv[2] คือ argument ตัวแรกที่ส่งเข้ามา (ตัวที่ 0 คือ node, ตัวที่ 1 คือชื่อสคริปต์)
    const htmlFilePath = process.argv[2];
    const outputPdfPath = process.argv[3];

    // ตรวจสอบว่าได้รับ path ครบถ้วนหรือไม่
    if (!htmlFilePath || !outputPdfPath) {
        console.error('Usage: node generate-pdf.js <html_file_path> <output_pdf_path>');
        process.exit(1); // ออกจากโปรแกรมพร้อมแจ้งข้อผิดพลาด
    }

    let browser;
    try {
        // --- 2. เปิดเบราว์เซอร์ Chromium แบบไร้หน้าจอ ---
        browser = await puppeteer.launch({
            headless: true, // รันในโหมดไร้หน้าจอ (สำคัญสำหรับเซิร์ฟเวอร์)
            args: [
                '--no-sandbox',                 // ปิด sandbox mode เพื่อความเข้ากันได้บน Linux Server
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage'       // แก้ปัญหา memory บน Docker หรือ CI/CD
            ]
        });

        const page = await browser.newPage();

        // --- 3. ไปยังไฟล์ HTML ในเครื่อง ---
        // ใช้ 'file://' เพื่อเปิดไฟล์จาก local disk
        // ใช้ path.resolve เพื่อแปลง path ให้เป็น path เต็มที่ถูกต้องเสมอ
        await page.goto('file://' + path.resolve(htmlFilePath), {
            waitUntil: 'networkidle0' // รอจนกว่า network จะสงบ (เพื่อให้แน่ใจว่าฟอนต์และรูปภาพโหลดเสร็จ)
        });

        // --- 4. สร้างไฟล์ PDF ---
        await page.pdf({
            path: path.resolve(outputPdfPath), // Path ที่จะบันทึกไฟล์ PDF
            format: 'A4',                      // กำหนดขนาดกระดาษเป็น A4
            printBackground: true,             // สั่งให้พิมพ์สีพื้นหลังและรูปภาพด้วย
            margin: { top: 0, right: 0, bottom: 0, left: 0 } // Margin ของ PDF เป็น 0 เพราะเราควบคุมจาก CSS ใน HTML แล้ว
        });

    } catch (error) {
        // --- 5. จัดการข้อผิดพลาด ---
        // หากมีปัญหาเกิดขึ้น ให้แสดง error ออกมาที่ console
        console.error("Puppeteer error:", error);
        process.exit(1); // ออกจากโปรแกรมพร้อมแจ้งข้อผิดพลาด
    } finally {
        // --- 6. ปิดเบราว์เซอร์เสมอ ---
        // ไม่ว่าจะสำเร็จหรือล้มเหลว ก็ต้องปิดเบราว์เซอร์เพื่อไม่ให้โปรเซสค้าง
        if (browser) {
            await browser.close();
        }
    }
})();