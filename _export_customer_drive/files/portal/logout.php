<?php

/**
 * ออกจากรอบเข้าใช้ของแขก
 *
 * ลบแถว session ทิ้งจริง ๆ ไม่ใช่แค่ล้าง cookie — ถ้าลบแต่ cookie
 * ตัว session_token ที่เคยหลุดไปแล้ว (เช่นในประวัติของเครื่องสาธารณะ)
 * จะยังใช้เข้าได้อีกสองชั่วโมง
 */

require_once __DIR__ . '/../config/customer_drive.php';

header('Referrer-Policy: no-referrer');

$token = (string) ($_COOKIE[CD_GUEST_COOKIE] ?? '');

if ($token !== '') {
    global $conn;

    try {
        $conn->prepareAndExecute("DELETE FROM tbl_cd_link_session WHERE session_token = ?", [$token]);
    } catch (Throwable $e) {
        error_log('portal logout: ' . $e->getMessage());
    }
}

cd_guest_cookie('', 0);

header('Location: index.php');
exit;
