<?php

/**
 * ตั้งรหัสผ่านใหม่ให้ลิงก์เดิม
 *
 * มีไว้แทน "ดูรหัสเดิม" ซึ่งทำไม่ได้เพราะเก็บเป็น password_hash()
 * ข้อดีคือ URL เดิมยังใช้ได้ ลูกค้าไม่ต้องรับลิงก์ใหม่ ส่งแค่รหัสไปให้
 *
 * ล้างสถานะล็อกและตัวนับผิดด้วย เพราะการตั้งรหัสใหม่คือการเริ่มต้นใหม่ —
 * ถ้าไม่ล้าง ลูกค้าที่เพิ่งได้รหัสถูกจะยังกรอกไม่ได้เพราะติดล็อกของคนอื่น
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$link_id     = (int) ($_POST['link_id'] ?? 0);
$password    = (string) ($_POST['password'] ?? '');

cd_guard($customer_id, $user_id, 'cdrive_manage_link');

$link = cd_link_by_id($customer_id, $link_id);

if (!$link) {
    cd_fail('ไม่พบลิงก์นี้');
}

if ((string) $link['revoked'] === '1') {
    cd_json(['result' => '4', 'msn' => 'ลิงก์นี้ถูกเพิกถอนแล้ว ตั้งรหัสใหม่ไม่ได้ — สร้างลิงก์ใหม่แทน']);
}

/*
 * ยาวเท่านี้พอดี ไม่ใช่อย่างน้อย — หน้ากรอกของแขกเป็นช่องแยกตัวละกล่อง
 * ซึ่งมีจำนวนช่องตายตัว รหัสที่ยาวกว่านั้นจะกรอกไม่ได้เลย
 */
if (mb_strlen($password, 'UTF-8') !== CD_LINK_PASSWORD_LEN) {
    cd_fail('รหัสผ่านต้องยาว ' . CD_LINK_PASSWORD_LEN . ' ตัวอักษรพอดี');
}

global $conn;

try {
    $conn->prepareAndExecute(
        "UPDATE tbl_cd_link
         SET password_hash = ?, fail_count = 0, locked_until = NULL
         WHERE customer_id = ? AND link_id = ?",
        [password_hash($password, PASSWORD_DEFAULT), $customer_id, $link_id]
    );
} catch (Throwable $e) {
    cd_fail('ตั้งรหัสใหม่ไม่สำเร็จ: ' . $e->getMessage());
}

cd_log($user_id, 'ตั้งรหัสผ่านใหม่ให้ลิงก์แชร์คลังไฟล์ลูกค้า: ' . $link['title']);

cd_ok([
    'msn'      => 'ตั้งรหัสใหม่แล้ว ลิงก์เดิมยังใช้ได้',
    'url'      => cd_portal_url((string) $link['token']),
    'password' => $password,
]);
