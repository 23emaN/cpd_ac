<?php

/**
 * สร้างโฟลเดอร์ใหม่ในคลังของลูกค้ารายหนึ่ง
 *
 * ชื่อซ้ำในโฟลเดอร์เดียวกันถือว่าผิด ไม่ใช่เรื่องที่ต้องเดาให้ —
 * ต่างจากการอัปไฟล์ตรงที่ผู้ใช้เห็นรายการอยู่ตรงหน้าแล้วตอนกดสร้าง
 * จึงบอกไปตรง ๆ ว่าซ้ำ ดีกว่าเงียบ ๆ สร้าง "เอกสาร (2)" ให้
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$parent_id   = (int) ($_POST['parent_id'] ?? 0);
$name        = cd_safe_name((string) ($_POST['name'] ?? ''));

cd_guard($customer_id, $user_id, 'cdrive_upload');

if ($name === '') {
    cd_fail('กรุณาตั้งชื่อโฟลเดอร์');
}

// โฟลเดอร์แม่ต้องเป็นโฟลเดอร์จริงในคลังนี้ ถ้าส่ง id ของไฟล์มาต้องปฏิเสธ
$parent = null;

if ($parent_id > 0) {
    $parent = cd_folder_at($customer_id, $parent_id);

    if (!$parent) {
        cd_fail('ไม่พบโฟลเดอร์ปลายทาง');
    }
}

$parentId = $parent ? (int) $parent['node_id'] : null;

if (cd_find_by_name($customer_id, $parentId, $name)) {
    cd_json(['result' => '3', 'msn' => 'มีชื่อนี้อยู่แล้วในโฟลเดอร์นี้']);
}

global $conn;

$conn->beginTransaction();

try {
    $conn->prepareAndExecute(
        "INSERT INTO tbl_cd_node
            (customer_id, parent_id, kind, name, list_order, source, create_by)
         VALUES (?, ?, 'folder', ?, ?, 'staff', ?)",
        [$customer_id, $parentId, $name, cd_next_sort($customer_id, $parentId), $user_id]
    );

    $node_id = (int) $conn->get_insert_id();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    cd_fail('สร้างโฟลเดอร์ไม่สำเร็จ: ' . $e->getMessage());
}

// เขียนร่องรอยหลังงานสำเร็จเสมอ — ล้มตรงนี้ต้องไม่ทำให้โฟลเดอร์ที่สร้างแล้วหายไป
cd_activity($customer_id, 'create_folder', $node_id, $name, 'staff', $user_id);
cd_log($user_id, 'สร้างโฟลเดอร์ในคลังไฟล์ลูกค้า: ' . $name);

cd_ok(['msn' => 'สร้างโฟลเดอร์แล้ว', 'node_id' => $node_id]);
