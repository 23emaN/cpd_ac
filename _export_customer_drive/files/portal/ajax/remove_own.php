<?php

/**
 * ลบไฟล์ที่แขกเพิ่งส่งมาในรอบนี้
 *
 * **ลบได้เฉพาะไฟล์ที่เข้ามาทางลิงก์ใบนี้เท่านั้น** — cd_guest_can_see() กรองให้แล้ว
 * ไฟล์ของพนักงานและไฟล์จากลิงก์ใบอื่นแตะไม่ได้
 *
 * ตั้งแต่ตัดช่อง "ชื่อผู้ส่ง" ออก ขอบเขตเปลี่ยนจาก "คน" เป็น "ลิงก์"
 * ถ้าส่งลิงก์เดียวกันให้หลายคน ทุกคนจะลบของกันได้ — ต้องการแยกให้สร้างลิงก์คนละใบ
 *
 * ใช้ soft delete เหมือนฝั่งพนักงาน ของยังอยู่ในถังขยะให้พนักงานกู้คืนได้
 * ลูกค้าที่ลบผิดจึงไม่ได้ทำลายอะไรถาวร
 *
 * **used_uploads ไม่ลดลง** เพราะมันคือโควตาการส่ง ไม่ใช่จำนวนไฟล์ที่มีอยู่ —
 * ถ้าลดได้ ลูกค้าอัป-ลบวนไปก็ทะลุเพดานได้ไม่จำกัด
 */

require_once __DIR__ . '/../../config/customer_drive.php';

header('Referrer-Policy: no-referrer');

[$link, $session] = cd_guest_guard();

$node_id = (int) ($_POST['node_id'] ?? 0);
$customer_id = (int) $link['customer_id'];
$label = (string) ($session['guest_label'] ?? '');

$node = cd_guest_can_see($link, $session, $node_id);

if (!$node || $node['kind'] !== 'file') {
    cd_fail('ไม่พบไฟล์นี้');
}

// ต้องเป็นไฟล์ที่เข้ามาทางลิงก์ใบนี้จริง ๆ ไม่ใช่แค่มองเห็นได้
// (โหมด share เห็นของพนักงานด้วย ซึ่งแขกต้องลบไม่ได้)
$mine = $node['source'] === 'guest'
    && (int) ($node['source_link_id'] ?? 0) === (int) $link['link_id'];

if (!$mine) {
    cd_fail('ลบได้เฉพาะไฟล์ที่ส่งเข้ามาทางลิงก์นี้');
}

global $conn;

try {
    $conn->prepareAndExecute(
        "UPDATE tbl_cd_node SET delete_datetime = NOW(), delete_by = NULL
         WHERE customer_id = ? AND node_id = ? AND delete_datetime IS NULL",
        [$customer_id, $node_id]
    );
} catch (Throwable $e) {
    error_log('portal remove: ' . $e->getMessage());
    cd_fail('ลบไม่สำเร็จ กรุณาลองใหม่');
}

cd_activity($customer_id, 'delete', $node_id, (string) $node['name'], 'guest', null, $label, (int) $link['link_id']);

cd_ok(['msn' => 'ลบแล้ว']);
