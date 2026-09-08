<?php

/**
 * เปลี่ยนชื่อไฟล์หรือโฟลเดอร์
 *
 * ชื่อบนดิสก์ไม่เปลี่ยนตาม เพราะไฟล์จริงชื่อ current.{ext} อยู่ใต้โฟลเดอร์ที่ตั้งชื่อ
 * ด้วย node_id — นั่นคือเหตุผลที่โมดูลนี้ไม่พังเมื่อผู้ใช้เปลี่ยนชื่อ ต่างจาก
 * uploads/{ชื่อลูกค้า}/ ยุคเก่าที่ path เดิมพังทันทีที่ลูกค้าเปลี่ยนชื่อ
 *
 * **ห้ามให้เปลี่ยนนามสกุลของไฟล์** เพราะนามสกุลผูกกับชื่อไฟล์จริงบนดิสก์
 * และผูกกับด่านตรวจ MIME ตอนอัปโหลดไปแล้ว
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$node_id     = (int) ($_POST['node_id'] ?? 0);
$name        = cd_safe_name((string) ($_POST['name'] ?? ''));

cd_guard($customer_id, $user_id, 'cdrive_edit');

$node = cd_guard_node($customer_id, $node_id);

if ($name === '') {
    cd_fail('กรุณาตั้งชื่อ');
}

if ($name === (string) $node['name']) {
    cd_ok(['msn' => 'ชื่อเดิม ไม่มีอะไรเปลี่ยน']);
}

// ไฟล์ต้องคงนามสกุลเดิม — ถ้าผู้ใช้พิมพ์ชื่อโดยไม่ใส่นามสกุล ให้เติมกลับให้
if ($node['kind'] === 'file') {
    $ext = (string) $node['ext'];

    if ($ext !== '' && cd_ext_of($name) !== $ext) {
        $name = cd_safe_name($name . '.' . $ext);
    }
}

$parentId = $node['parent_id'] === null ? null : (int) $node['parent_id'];

if (cd_find_by_name($customer_id, $parentId, $name, $node_id)) {
    cd_json(['result' => '3', 'msn' => 'มีชื่อนี้อยู่แล้วในโฟลเดอร์นี้']);
}

global $conn;

$old = (string) $node['name'];

try {
    $conn->prepareAndExecute(
        "UPDATE tbl_cd_node SET name = ?, update_by = ? WHERE customer_id = ? AND node_id = ?",
        [$name, $user_id, $customer_id, $node_id]
    );
} catch (Throwable $e) {
    cd_fail('เปลี่ยนชื่อไม่สำเร็จ: ' . $e->getMessage());
}

cd_activity($customer_id, 'rename', $node_id, $old . ' → ' . $name, 'staff', $user_id);
cd_log($user_id, 'เปลี่ยนชื่อในคลังไฟล์ลูกค้า: ' . $old . ' → ' . $name);

cd_ok(['msn' => 'เปลี่ยนชื่อแล้ว', 'name' => $name]);
