<?php

/**
 * รายการที่แขกมีสิทธิ์เห็น
 *
 * ขอบเขตทั้งหมดตัดสินโดย cd_guest_items() จากโหมดของลิงก์ —
 * **ไม่มีพารามิเตอร์อะไรจากผู้เรียกมาเปลี่ยนขอบเขตได้เลย** ซึ่งเป็นเจตนา
 * ถ้ารับ folder_id หรือ customer_id จากผู้เรียกได้เมื่อไหร่ แขกจะยัดค่าอื่น
 * เข้ามาแล้วส่องคลังของลูกค้ารายอื่นได้ทันที
 */

require_once __DIR__ . '/../../config/customer_drive.php';

header('Referrer-Policy: no-referrer');

[$link, $session] = cd_guest_guard();

$items = cd_guest_items($link, $session);
$canDownload = (string) $link['allow_download'] === '1';

/*
 * ชื่อโฟลเดอร์ไว้บอก "ไฟล์นี้อยู่ในโฟลเดอร์ไหน"
 *
 * cd_guest_items() คืนรายการแบบแบนทั้งชั้นลูกหลาน และหน้าแขกก็ตั้งใจให้เป็น
 * รายการเดียวจบ ไม่มีการกดเข้าโฟลเดอร์ — ไฟล์ชื่อเดียวกันจากคนละโฟลเดอร์
 * จึงหน้าตาเหมือนกันเป๊ะถ้าไม่บอกที่อยู่กำกับไว้
 */
$folderNames = [];

foreach ($items as $item) {
    if ($item['kind'] === 'folder') {
        $folderNames[(int) $item['node_id']] = (string) $item['name'];
    }
}

// โฟลเดอร์ที่เป็นขอบเขตของลิงก์เอง ไม่ต้องบอกซ้ำ ทุกไฟล์อยู่ใต้มันอยู่แล้ว
$scopeId = cd_guest_target($link);

$out = [];

foreach ($items as $item) {
    /*
     * ตัดแถวโฟลเดอร์ทิ้ง — กดเข้าไม่ได้ ดาวน์โหลดไม่ได้ ลบไม่ได้
     * และของข้างในก็อยู่ในรายการเดียวกันนี้อยู่แล้ว เหลือไว้ก็เป็นแถวตาย
     */
    if ($item['kind'] === 'folder') {
        continue;
    }

    $ext = (string) ($item['ext'] ?? '');
    // ลบได้เฉพาะของที่เข้ามาทางลิงก์ใบนี้ — ขอบเขตเป็น "ลิงก์" ไม่ใช่ "คน"
    $mine = $item['source'] === 'guest'
        && (int) ($item['source_link_id'] ?? 0) === (int) $link['link_id'];

    $parentId = $item['parent_id'] === null ? null : (int) $item['parent_id'];
    $where = ($parentId !== null && $parentId !== $scopeId && isset($folderNames[$parentId]))
        ? $folderNames[$parentId]
        : '';

    $out[] = [
        'id'       => (int) $item['node_id'],
        'name'     => (string) $item['name'],
        'folder'   => false,
        'kind'     => cd_file_kind($ext),
        'size'     => cd_format_bytes((int) $item['size']),
        'when'     => cd_time($item['create_datetime']),
        'where'    => $where,
        // ลบได้เฉพาะไฟล์ที่ตัวเองส่งมาในรอบนี้เท่านั้น
        'canRemove' => $mine,
        'canDownload' => $canDownload,
    ];
}

cd_ok([
    'items' => $out,
    'used'  => (int) $link['used_uploads'],
    'max'   => $link['max_uploads'] === null ? 0 : (int) $link['max_uploads'],
]);
