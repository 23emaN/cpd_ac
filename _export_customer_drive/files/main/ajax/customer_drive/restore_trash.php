<?php

/**
 * กู้คืนรายการจากถังขยะ
 *
 * กู้ทั้งกิ่ง — ลูกหลานที่ถูกทิ้ง "พร้อมกัน" (delete_datetime ตรงกันเป๊ะ) กลับมาด้วย
 * แต่ของที่เคยถูกลบไปก่อนหน้านั้นแยกต่างหากต้องยังอยู่ในถังขยะ มิฉะนั้นการกู้
 * โฟลเดอร์หนึ่งจะปลุกไฟล์ที่ตั้งใจลบทิ้งไปตั้งแต่เดือนก่อนกลับมาด้วยโดยไม่มีใครสั่ง
 *
 * ถ้าโฟลเดอร์แม่ยังอยู่ในถังขยะ ให้ย้ายตัวที่กู้ขึ้นชั้นบนสุดแทนการปล่อยให้
 * ลอยอยู่ใต้แม่ที่มองไม่เห็น — ไม่งั้นผู้ใช้จะกดกู้คืนแล้วหาของไม่เจอ
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$node_id     = (int) ($_POST['node_id'] ?? 0);

cd_guard($customer_id, $user_id, 'cdrive_delete');

$node = cd_find_trashed($customer_id, $node_id);

if (!$node) {
    cd_fail('ไม่พบรายการนี้ในถังขยะ');
}

global $conn;

$stamp = (string) $node['delete_datetime'];
$restored = 0;

$conn->beginTransaction();

try {
    // ลูกหลานที่ถูกทิ้งในจังหวะเดียวกันเท่านั้น
    $ids = cd_descendant_ids($customer_id, $node_id, true);
    $ids[] = $node_id;

    foreach (array_unique($ids) as $id) {
        $row = $conn->prepareAndExecute(
            "SELECT delete_datetime FROM tbl_cd_node WHERE customer_id = ? AND node_id = ?",
            [$customer_id, $id]
        )->fetch();

        if (!$row || $row['delete_datetime'] === null) {
            continue;
        }

        if ((string) $row['delete_datetime'] !== $stamp) {
            continue; // ถูกลบคนละครั้ง ต้องอยู่ในถังขยะต่อไป
        }

        $conn->prepareAndExecute(
            "UPDATE tbl_cd_node SET delete_datetime = NULL, delete_by = NULL, update_by = ?
             WHERE customer_id = ? AND node_id = ?",
            [$user_id, $customer_id, $id]
        );

        $restored++;
    }

    // แม่ยังอยู่ในถังขยะ = กู้กลับไปแล้วจะมองไม่เห็น ให้ขึ้นชั้นบนสุดแทน
    $parentId = $node['parent_id'] === null ? null : (int) $node['parent_id'];
    $orphaned = false;

    if ($parentId !== null && !cd_find_node($customer_id, $parentId)) {
        $conn->prepareAndExecute(
            "UPDATE tbl_cd_node SET parent_id = NULL, list_order = ? WHERE customer_id = ? AND node_id = ?",
            [cd_next_sort($customer_id, null), $customer_id, $node_id]
        );

        $orphaned = true;
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    cd_fail('กู้คืนไม่สำเร็จ: ' . $e->getMessage());
}

cd_activity($customer_id, 'restore', $node_id, (string) $node['name'], 'staff', $user_id);
cd_log($user_id, 'กู้คืนรายการจากถังขยะคลังไฟล์ลูกค้า: ' . $node['name']);

$msn = 'กู้คืน "' . $node['name'] . '" แล้ว';

if ($restored > 1) {
    $msn .= ' (รวมของข้างใน ' . ($restored - 1) . ' รายการ)';
}

if ($orphaned) {
    $msn .= ' · โฟลเดอร์เดิมยังอยู่ในถังขยะ จึงย้ายไปไว้ที่ชั้นบนสุดของคลัง';
}

cd_ok(['msn' => $msn, 'trash_count' => cd_trash_count($customer_id)]);
