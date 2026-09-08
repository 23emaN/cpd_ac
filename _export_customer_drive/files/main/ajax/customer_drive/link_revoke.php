<?php

/**
 * เพิกถอนลิงก์แชร์ — ปิดทางเข้าของคนนอกทันที
 *
 * ไม่ลบแถวทิ้ง เพราะ tbl_cd_node.source_link_id ชี้กลับมาที่ลิงก์นี้เพื่อบอกว่า
 * ไฟล์ใบไหนมาจากลิงก์ไหน ร่องรอยนั้นต้องอยู่ตลอดไปแม้ลิงก์จะเลิกใช้แล้ว
 *
 * รอบเข้าใช้ที่ยังค้างอยู่ต้องถูกตัดด้วย มิฉะนั้นลูกค้าที่เปิดหน้าค้างไว้
 * จะอัปไฟล์ต่อได้อีกสองชั่วโมงหลังเราสั่งเพิกถอนไปแล้ว
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$link_id     = (int) ($_POST['link_id'] ?? 0);

cd_guard($customer_id, $user_id, 'cdrive_manage_link');

$link = cd_link_by_id($customer_id, $link_id);

if (!$link) {
    cd_fail('ไม่พบลิงก์นี้');
}

if ((string) $link['revoked'] === '1') {
    cd_ok(['msn' => 'ลิงก์นี้ถูกเพิกถอนไปแล้ว']);
}

global $conn;

$conn->beginTransaction();

try {
    $conn->prepareAndExecute(
        "UPDATE tbl_cd_link SET revoked = '1' WHERE customer_id = ? AND link_id = ?",
        [$customer_id, $link_id]
    );

    // ตัดรอบเข้าใช้ที่ยังค้าง — เพิกถอนแล้วต้องเข้าไม่ได้เดี๋ยวนี้ ไม่ใช่รออีกสองชั่วโมง
    $conn->prepareAndExecute(
        "DELETE FROM tbl_cd_link_session WHERE link_id = ?",
        [$link_id]
    );

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    cd_fail('เพิกถอนไม่สำเร็จ: ' . $e->getMessage());
}

cd_activity($customer_id, 'link_revoke', null, (string) $link['title'], 'staff', $user_id, null, $link_id);
cd_log($user_id, 'เพิกถอนลิงก์แชร์คลังไฟล์ลูกค้า: ' . $link['title']);

cd_ok(['msn' => 'เพิกถอนลิงก์แล้ว ลูกค้าเข้าไม่ได้อีก']);
