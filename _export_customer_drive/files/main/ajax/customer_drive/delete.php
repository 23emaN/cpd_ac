<?php

/**
 * ทิ้งรายการลงถังขยะ (soft delete)
 *
 * ไม่มีการลบไฟล์บนดิสก์ที่นี่ — กู้คืนได้เสมอจนกว่าจะสั่งลบถาวรที่ trash_purge.php
 * และ **ถังขยะของโมดูลนี้ไม่มีวันหมดอายุ** ต่างจากกระดาษทำการที่เก็บ 30 วัน
 * (ตัดสินใจแล้วว่าเอกสารลูกค้าเก็บถาวร ดู CUSTOMER_DRIVE.md หัวข้อ 13 ข้อ 2)
 *
 * ลบโฟลเดอร์ = ลูกหลานทั้งกิ่งถูกทิ้งตามในเวลาเดียวกัน เพื่อให้หน้าถังขยะ
 * แสดงแค่ "หัว" ที่ผู้ใช้สั่งลบจริง ๆ ไม่ใช่ไล่แสดงไฟล์ข้างในซ้ำทุกใบ
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$raw         = (string) ($_POST['node_ids'] ?? '');

cd_guard($customer_id, $user_id, 'cdrive_delete');

$node_ids = array_values(array_unique(array_filter(
    array_map('intval', explode(',', $raw)),
    static fn ($id) => $id > 0
)));

if (!$node_ids) {
    cd_fail('ยังไม่ได้เลือกรายการที่จะลบ');
}

global $conn;

$now = cd_now();
$deleted = 0;
$names = [];

$conn->beginTransaction();

try {
    foreach ($node_ids as $id) {
        $node = cd_find_node($customer_id, $id);

        if (!$node) {
            continue; // ถูกลบไปแล้วโดยคนอื่น ไม่ใช่ความผิดพลาดที่ต้องหยุดทั้งชุด
        }

        // ทิ้งทั้งกิ่ง — ลูกหลานต้องมี delete_datetime ค่าเดียวกันเป๊ะ
        // เพื่อให้ cd_trash_items() แยกออกว่าอะไรคือ "หัว" ที่ผู้ใช้สั่งลบ
        $ids = cd_descendant_ids($customer_id, $id);
        $ids[] = $id;

        foreach (array_unique($ids) as $target) {
            $conn->prepareAndExecute(
                "UPDATE tbl_cd_node
                 SET delete_datetime = ?, delete_by = ?
                 WHERE customer_id = ? AND node_id = ? AND delete_datetime IS NULL",
                [$now, $user_id, $customer_id, $target]
            );
        }

        $deleted++;
        $names[] = (string) $node['name'];
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    cd_fail('ทิ้งลงถังขยะไม่สำเร็จ: ' . $e->getMessage());
}

if ($deleted === 0) {
    cd_fail('ไม่พบรายการที่จะลบ');
}

cd_activity($customer_id, 'delete', null, implode(', ', array_slice($names, 0, 5)), 'staff', $user_id);
cd_log($user_id, 'ทิ้งรายการในคลังไฟล์ลูกค้าลงถังขยะ ' . $deleted . ' รายการ');

cd_ok([
    'msn'         => 'ทิ้ง ' . $deleted . ' รายการลงถังขยะแล้ว',
    'trash_count' => cd_trash_count($customer_id),
]);
