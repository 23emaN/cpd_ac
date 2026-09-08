<?php

/**
 * รับไฟล์จากแขก
 *
 * **ห้ามลอกโค้ดจาก main/ajax/customer_drive/upload.php มาตรง ๆ** ถึงจะดูคล้ายกันมาก
 * เพราะกติกาสี่ข้อต่างกันโดยสิ้นเชิง
 *
 *   1. **การอัปของแขกห้ามสร้างเวอร์ชันใหม่ทับใบเดิม**
 *      กติกา "ชื่อซ้ำ = เวอร์ชันใหม่" ใช้กับพนักงานเท่านั้น เพราะพนักงานเห็นของเดิม
 *      อยู่ตรงหน้าจึงตั้งใจทับได้ ส่วนแขกในโหมด collect มองไม่เห็นของเดิมเลย
 *      ถ้าปล่อยให้ทับ ไฟล์ของพนักงานจะกลายเป็น v2 โดยที่ทั้งสองฝ่ายไม่รู้ตัว
 *      ซึ่งขัดกับหลักการ "ลูกค้าเป็นแขก ไม่ใช่เจ้าของพื้นที่" ตรง ๆ
 *   2. **sha256 ตรงกัน = ถือว่าได้รับแล้ว** ไม่สร้างแถวใหม่ ไม่นับ used_uploads
 *      เพราะเคสที่เกิดบ่อยที่สุดคือกดส่งซ้ำหรือเน็ตหลุดแล้วส่งใหม่
 *   3. นามสกุลใช้ cd_allowed_ext('guest') ซึ่งแคบกว่าของพนักงาน (ไม่มี zip/csv/txt)
 *   4. โฟลเดอร์ปลายทางมาจาก root_node_id ของลิงก์เท่านั้น **ห้ามรับจากผู้เรียก**
 */

require_once __DIR__ . '/../../config/customer_drive.php';

header('Referrer-Policy: no-referrer');

[$link, $session] = cd_guest_guard();

if ((string) $link['allow_upload'] !== '1') {
    cd_fail('ลิงก์นี้ไม่ได้เปิดให้ส่งไฟล์');
}

$customer_id = (int) $link['customer_id'];
$label       = (string) ($session['guest_label'] ?? '');

$storageError = cd_storage_error();

if ($storageError !== '') {
    // ไม่บอกรายละเอียดของเซิร์ฟเวอร์ให้คนนอกฟัง
    error_log('portal upload: ' . $storageError);
    cd_fail('ระบบรับไฟล์ขัดข้องชั่วคราว กรุณาติดต่อผู้ตรวจสอบบัญชีของท่าน');
}

/*
 * ไฟล์ใหญ่เกิน post_max_size ทำให้ PHP ทิ้งทั้ง body ก่อนโค้ดเราทำงาน
 * $_FILES และ $_POST จึงว่างพร้อมกันโดยไม่มี error — ถ้าไม่ดักตรงนี้
 * ลูกค้าจะเห็นหน้าจอเงียบแล้วคิดว่าส่งแล้ว ส่วนเราไม่ได้ไฟล์และไม่รู้ว่าพลาด
 */
if (!$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    cd_fail('ไฟล์ใหญ่เกินกว่าที่ระบบรับได้ (สูงสุด ' . cd_format_bytes(cd_max_upload_bytes()) . ')');
}

$file = $_FILES['file'] ?? null;

if (!$file || !is_array($file)) {
    cd_fail('ไม่พบไฟล์ที่ส่งมา');
}

if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    $reason = match ((int) $file['error']) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE
            => 'ไฟล์ใหญ่เกินกว่าที่ระบบรับได้ (สูงสุด ' . cd_format_bytes(cd_max_upload_bytes()) . ')',
        UPLOAD_ERR_PARTIAL => 'ไฟล์ถูกส่งมาไม่ครบ กรุณาลองใหม่',
        UPLOAD_ERR_NO_FILE => 'ไม่พบไฟล์ที่ส่งมา',
        default            => 'ส่งไฟล์ไม่สำเร็จ กรุณาลองใหม่',
    };

    cd_fail($reason);
}

$tmp = (string) $file['tmp_name'];

if (!is_uploaded_file($tmp)) {
    cd_fail('ไฟล์ที่ส่งมาไม่ถูกต้อง');
}

/*
 * ตั้งแต่ตรงนี้เป็นต้นไปใช้ตรรกะร่วมกับ upload_chunk.php
 *
 * ทั้งสองทางต่างกันแค่ "ไฟล์มาถึงยังไง" ทุกด่านตรวจหลังจากนั้นต้องเหมือนกันเป๊ะ
 * ถ้าแยกเขียน วันหนึ่งจะมีทางใดทางหนึ่งลืมตรวจ MIME หรือลืมนับโควตา
 */
cd_guest_accept($link, $session, $tmp, (string) ($file['name'] ?? ''));
