<?php

/**
 * ย้ายรายการหนึ่งหรือหลายรายการไปโฟลเดอร์อื่น
 *
 * รับหลาย node_id พร้อมกันเพราะหน้าจอเลือกได้ทีละหลายรายการและลากทีเดียว —
 * ถ้ายิงทีละคำขอ ผู้ใช้จะเห็นตารางกระพริบทีละแถว และถ้าล้มกลางทางจะเหลือ
 * สภาพครึ่ง ๆ ที่อธิบายไม่ได้ จึงทำทั้งชุดใน transaction เดียว
 *
 * สองด่านที่ขาดไม่ได้
 *   1. **ห้ามย้ายโฟลเดอร์เข้าไปในลูกหลานของตัวเอง** ถ้าปล่อยให้ทำได้ กิ่งนั้น
 *      จะหลุดจากทรีทั้งกิ่ง มองไม่เห็นจากหน้าจอแต่ยังกินพื้นที่อยู่
 *   2. ทุก node ต้องอยู่ในคลังของลูกค้ารายนี้ (cd_guard_node กรอง customer_id ให้)
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$target_id   = (int) ($_POST['target_id'] ?? 0);
$raw         = (string) ($_POST['node_ids'] ?? '');

cd_guard($customer_id, $user_id, 'cdrive_edit');

$node_ids = array_values(array_unique(array_filter(
    array_map('intval', explode(',', $raw)),
    static fn ($id) => $id > 0
)));

if (!$node_ids) {
    cd_fail('ยังไม่ได้เลือกรายการที่จะย้าย');
}

// ปลายทางว่าง = ย้ายขึ้นชั้นบนสุดของคลัง
$target = null;

if ($target_id > 0) {
    $target = cd_folder_at($customer_id, $target_id);

    if (!$target) {
        cd_fail('ปลายทางต้องเป็นโฟลเดอร์');
    }
}

$targetId = $target ? (int) $target['node_id'] : null;

global $conn;

$moved = 0;
$renamed = [];

$conn->beginTransaction();

try {
    foreach ($node_ids as $id) {
        $node = cd_find_node($customer_id, $id);

        if (!$node) {
            throw new Exception('ไม่พบรายการบางรายการในคลังไฟล์นี้');
        }

        $currentParent = $node['parent_id'] === null ? null : (int) $node['parent_id'];

        if ($currentParent === $targetId) {
            continue; // อยู่ที่นั่นอยู่แล้ว
        }

        if ($node['kind'] === 'folder' && cd_is_descendant($customer_id, $id, $targetId)) {
            throw new Exception('ย้ายโฟลเดอร์ "' . $node['name'] . '" เข้าไปในตัวเองหรือโฟลเดอร์ย่อยของตัวเองไม่ได้');
        }

        /*
         * ชื่อชนที่ปลายทางให้เปลี่ยนชื่ออัตโนมัติ ไม่ใช่ล้มทั้งชุด
         *
         * การย้าย 20 ไฟล์แล้วล้มเพราะไฟล์เดียวชื่อชน คือการทำให้ผู้ใช้ต้องมานั่ง
         * หาว่าใบไหนชน ทั้งที่ระบบรู้อยู่แล้ว — บอกทีหลังว่าเปลี่ยนชื่ออะไรบ้างพอ
         */
        $name = cd_unique_name($customer_id, $targetId, (string) $node['name']);

        if ($name !== (string) $node['name']) {
            $renamed[] = $node['name'] . ' → ' . $name;
        }

        $conn->prepareAndExecute(
            "UPDATE tbl_cd_node
             SET parent_id = ?, name = ?, list_order = ?, update_by = ?
             WHERE customer_id = ? AND node_id = ?",
            [$targetId, $name, cd_next_sort($customer_id, $targetId), $user_id, $customer_id, $id]
        );

        $moved++;
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    cd_json(['result' => '4', 'msn' => $e->getMessage()]);
}

if ($moved === 0) {
    cd_ok(['msn' => 'รายการที่เลือกอยู่ในโฟลเดอร์นี้อยู่แล้ว']);
}

$where = $target ? $target['name'] : 'คลังไฟล์ (ชั้นบนสุด)';

cd_activity($customer_id, 'move', null, 'ย้าย ' . $moved . ' รายการไปที่ ' . $where, 'staff', $user_id);
cd_log($user_id, 'ย้ายรายการในคลังไฟล์ลูกค้า ' . $moved . ' รายการ');

$msn = 'ย้าย ' . $moved . ' รายการแล้ว';

if ($renamed) {
    $msn .= ' · เปลี่ยนชื่อเพราะชนกับของเดิม: ' . implode(', ', array_slice($renamed, 0, 3));

    if (count($renamed) > 3) {
        $msn .= ' และอีก ' . (count($renamed) - 3) . ' รายการ';
    }
}

cd_ok(['msn' => $msn, 'moved' => $moved]);
