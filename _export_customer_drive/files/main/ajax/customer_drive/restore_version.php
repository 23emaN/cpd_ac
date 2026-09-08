<?php

/**
 * ย้อนคืนเวอร์ชันเก่าขึ้นมาเป็นเวอร์ชันล่าสุด
 *
 * **ไม่ลบเวอร์ชันไหนทิ้ง** — คัดลอกไฟล์ของเวอร์ชันที่เลือกขึ้นมาเป็นเวอร์ชันใหม่
 * ประวัติจึงเดินหน้าอย่างเดียว ไม่มีทางที่การกดผิดจะทำให้ของหาย
 * (ถ้าย้อนแล้วผิดใจ ก็ย้อนกลับไปหาเวอร์ชันก่อนหน้าได้อีก)
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$node_id     = (int) ($_POST['node_id'] ?? 0);
$version     = (int) ($_POST['version'] ?? 0);

cd_guard($customer_id, $user_id, 'cdrive_edit');

$node = cd_guard_node($customer_id, $node_id);

if ($node['kind'] !== 'file') {
    cd_fail('โฟลเดอร์ไม่มีเวอร์ชัน');
}

if ($version <= 0) {
    cd_fail('ไม่ได้ระบุเวอร์ชัน');
}

if ($version === (int) $node['version']) {
    cd_ok(['msn' => 'เวอร์ชันนี้เป็นเวอร์ชันล่าสุดอยู่แล้ว']);
}

$source = cd_version_path($node, $version);

if (!is_file($source)) {
    cd_fail('ไม่พบไฟล์ของเวอร์ชัน v' . $version . ' บนเซิร์ฟเวอร์');
}

$result = cd_commit_version($node, $source, 'restore', $user_id);

if (!$result['ok']) {
    cd_fail('ย้อนคืนไม่สำเร็จ: ' . $result['msn']);
}

cd_activity(
    $customer_id,
    'upload_version',
    $node_id,
    $node['name'] . ' — ย้อนคืน v' . $version . ' เป็น v' . $result['version'],
    'staff',
    $user_id
);
cd_log($user_id, 'ย้อนคืนเวอร์ชันในคลังไฟล์ลูกค้า: ' . $node['name'] . ' v' . $version);

cd_ok(['msn' => 'ย้อนคืน v' . $version . ' ขึ้นมาเป็น v' . $result['version'] . ' แล้ว', 'version' => $result['version']]);
