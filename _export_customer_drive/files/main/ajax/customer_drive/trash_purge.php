<?php

/**
 * ลบถาวรจากถังขยะ — ไฟล์ทุกเวอร์ชันบนดิสก์หายไปโดยกู้คืนไม่ได้
 *
 * ด่านเดียวก่อนลงมือคือ cd_find_trashed() ซึ่งบังคับว่า node ต้องอยู่ในถังขยะจริง
 * ถ้าไม่กรอง การส่ง node_id ของไฟล์ที่ยังใช้งานอยู่เข้ามาจะลบไฟล์ทิ้งถาวรทันที
 *
 * แถวใน tbl_cd_activity **ไม่ถูกลบ** เพราะไม่ได้ผูก FK ไว้โดยตั้งใจ —
 * การลบถาวรเป็นการกระทำเดียวในระบบที่ทำลายหลักฐาน จึงต้องเหลือร่องรอยว่าใครสั่งเมื่อไร
 *
 * รองรับ node_id = 'all' สำหรับปุ่ม "ล้างถังขยะทั้งหมด"
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$raw         = (string) ($_POST['node_id'] ?? '');

cd_guard($customer_id, $user_id, 'cdrive_delete');

$targets = [];

if ($raw === 'all') {
    foreach (cd_trash_items($customer_id) as $item) {
        $targets[] = $item;
    }

    if (!$targets) {
        cd_fail('ถังขยะว่างอยู่แล้ว');
    }
} else {
    $node = cd_find_trashed($customer_id, (int) $raw);

    if (!$node) {
        cd_fail('ไม่พบรายการนี้ในถังขยะ');
    }

    $targets[] = $node;
}

global $conn;

/*
 * ตรวจลิงก์แชร์ที่ชี้มาก่อนลงมือ
 *
 * `fk_cd_link_root` เป็น RESTRICT โดยตั้งใจ ถ้าไม่ตรวจตรงนี้ DELETE จะระเบิด
 * กลางคันแล้วผู้ใช้ได้ข้อความ SQL ดิบซึ่งบอกไม่ได้ว่าต้องทำอะไรต่อ
 */
$blocking = [];

foreach ($targets as $node) {
    $ids = cd_descendant_ids($customer_id, (int) $node['node_id'], true);
    $ids[] = (int) $node['node_id'];

    $blocking = array_merge($blocking, cd_links_pointing_at($customer_id, array_values(array_unique($ids))));
}

if ($blocking) {
    $blocking = array_values(array_unique($blocking));

    cd_json([
        'result' => '4',
        'msn'    => 'ลบถาวรไม่ได้ เพราะยังมีลิงก์แชร์ชี้มาที่โฟลเดอร์นี้: '
            . implode(', ', array_slice($blocking, 0, 3))
            . (count($blocking) > 3 ? ' และอีก ' . (count($blocking) - 3) . ' ลิงก์' : '')
            . ' — เพิกถอนลิงก์เหล่านั้นก่อนแล้วลองใหม่',
    ]);
}

$names = [];
$doomed = [];   // node_id ที่ลบแถวสำเร็จแล้ว รอลบไฟล์หลัง commit

$conn->beginTransaction();

try {
    foreach ($targets as $node) {
        $doomed = array_merge($doomed, cd_purge_node($customer_id, (int) $node['node_id']));
        $names[] = (string) $node['name'];
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();

    // ไม่ส่งข้อความจากฐานข้อมูลออกหน้าเว็บ — มันเปิดโครงสร้างตารางให้คนนอกอ่าน
    error_log('cd trash_purge: ' . $e->getMessage());
    cd_fail('ลบถาวรไม่สำเร็จ กรุณาลองใหม่');
}

/*
 * ลบไฟล์บนดิสก์ **หลัง commit เท่านั้น**
 *
 * ถึงตรงนี้แถวหายจากฐานข้อมูลแน่นอนแล้ว ถ้าลบไฟล์ไม่สำเร็จบางใบ
 * ผลที่แย่ที่สุดคือมีไฟล์กำพร้าค้างบนดิสก์ ซึ่งดีกว่าสลับกัน —
 * แถวยังอยู่แต่ไฟล์หาย แปลว่ากู้คืนมาแล้วเปิดไม่ได้และไม่มีใครรู้
 */
$purged = count(array_unique($doomed));

foreach (array_unique($doomed) as $id) {
    cd_forget_files($customer_id, (int) $id);
}

cd_activity(
    $customer_id,
    'purge',
    null,
    'ลบถาวร: ' . implode(', ', array_slice($names, 0, 5)) . (count($names) > 5 ? ' และอีก ' . (count($names) - 5) . ' รายการ' : ''),
    'staff',
    $user_id
);
cd_log($user_id, 'ลบถาวรในคลังไฟล์ลูกค้า ' . $purged . ' รายการ');

cd_ok([
    'msn'         => 'ลบถาวรแล้ว ' . $purged . ' รายการ',
    'trash_count' => cd_trash_count($customer_id),
]);
