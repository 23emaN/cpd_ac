<?php

/**
 * ดาวน์โหลดหลายรายการเป็นไฟล์ zip ก้อนเดียว
 *
 * เลือกโฟลเดอร์มา = ได้ทั้งกิ่งพร้อมโครงสร้างโฟลเดอร์ข้างใน
 * เลือกไฟล์หลายใบ = ได้ทุกใบวางเรียงกันในราก zip
 *
 * เขียน zip ลงไฟล์ชั่วคราวก่อนแล้วค่อยส่ง ไม่ใช่สตรีมสด — ZipArchive ของ PHP
 * ต้องเขียนลงไฟล์อยู่แล้ว และการรู้ขนาดล่วงหน้าทำให้เบราว์เซอร์แสดง
 * ความคืบหน้าการดาวน์โหลดได้จริง แลกกับพื้นที่ชั่วคราวเท่าขนาด zip
 *
 * ไฟล์ชั่วคราวถูกลบทิ้งเสมอผ่าน register_shutdown_function() แม้คำขอถูกตัดกลางคัน
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_GET['customer_id'] ?? 0);
$user_id     = (int) ($_GET['user_id'] ?? 0);
$raw         = (string) ($_GET['node_ids'] ?? '');

function cd_zip_stop(int $code, string $msn): void
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $msn;
    exit;
}

$customer = cd_customer($customer_id);

if (!$customer || !cd_can($user_id, 'cdrive_view')) {
    cd_zip_stop(403, 'ไม่มีสิทธิ์ดาวน์โหลดจากคลังนี้');
}

if (!class_exists('ZipArchive')) {
    cd_zip_stop(500, 'เซิร์ฟเวอร์ไม่มีส่วนขยาย zip — ดาวน์โหลดทีละไฟล์แทนได้');
}

$node_ids = array_values(array_unique(array_filter(
    array_map('intval', explode(',', $raw)),
    static fn ($id) => $id > 0
)));

if (!$node_ids) {
    cd_zip_stop(400, 'ยังไม่ได้เลือกรายการ');
}

// อ่านทรีทั้งคลังครั้งเดียวแล้วไล่ในหน่วยความจำ
$byId = [];
$byParent = [];

foreach (cd_all_nodes($customer_id) as $n) {
    $byId[(int) $n['node_id']] = $n;
    $byParent[(int) ($n['parent_id'] ?? 0)][] = $n;
}

/**
 * ไล่เก็บไฟล์ใต้ node หนึ่งพร้อม path ที่จะใช้ใน zip
 *
 * @return list<array{path:string,file:string}>
 */
$collect = static function (array $node, string $prefix) use (&$collect, $byParent): array {
    $out = [];
    $name = (string) $node['name'];

    if ($node['kind'] === 'file') {
        $path = cd_current_path($node);

        if (is_file($path)) {
            $out[] = ['path' => $prefix . $name, 'file' => $path];
        }

        return $out;
    }

    foreach ($byParent[(int) $node['node_id']] ?? [] as $child) {
        $out = array_merge($out, $collect($child, $prefix . $name . '/'));
    }

    // โฟลเดอร์ว่างก็ควรอยู่ใน zip ด้วย ไม่งั้นโครงสร้างที่โหลดไปจะไม่เหมือนที่เห็นบนจอ
    if (!$out) {
        $out[] = ['path' => $prefix . $name . '/', 'file' => ''];
    }

    return $out;
};

$entries = [];

foreach ($node_ids as $id) {
    if (!isset($byId[$id])) {
        continue;
    }

    $entries = array_merge($entries, $collect($byId[$id], ''));
}

if (!$entries) {
    cd_zip_stop(404, 'ไม่พบไฟล์ที่จะดาวน์โหลด');
}

$tmp = tempnam(sys_get_temp_dir(), 'cdzip');

if ($tmp === false) {
    cd_zip_stop(500, 'สร้างไฟล์ชั่วคราวไม่ได้');
}

// ลบไฟล์ชั่วคราวทิ้งเสมอ แม้คำขอถูกตัดกลางคันหรือเกิด error
register_shutdown_function(static function () use ($tmp): void {
    if (is_file($tmp)) {
        @unlink($tmp);
    }
});

$zip = new ZipArchive();

if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
    cd_zip_stop(500, 'สร้างไฟล์ zip ไม่ได้');
}

$used = [];

foreach ($entries as $entry) {
    if ($entry['file'] === '') {
        $zip->addEmptyDir(rtrim($entry['path'], '/'));
        continue;
    }

    // ชื่อซ้ำใน zip ทำให้บางโปรแกรมแตกไฟล์ได้ไม่ครบ — เติมเลขกันไว้
    $path = $entry['path'];
    $i = 2;

    while (isset($used[$path])) {
        $ext = cd_ext_of($entry['path']);
        $base = $ext !== '' ? substr($entry['path'], 0, -(strlen($ext) + 1)) : $entry['path'];
        $path = $base . ' (' . $i++ . ')' . ($ext !== '' ? '.' . $ext : '');
    }

    $used[$path] = true;
    $zip->addFile($entry['file'], $path);
}

$zip->close();

$label = count($node_ids) === 1 && isset($byId[$node_ids[0]])
    ? (string) $byId[$node_ids[0]]['name']
    : $customer['customer_name'];

$filename = cd_safe_name($label) . '.zip';

cd_activity($customer_id, 'download', null, 'ดาวน์โหลด zip: ' . $filename . ' (' . count($entries) . ' รายการ)', 'staff', $user_id);
cd_log($user_id, 'ดาวน์โหลด zip จากคลังไฟล์ลูกค้า: ' . $filename);

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: application/zip');
header('Content-Length: ' . filesize($tmp));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"'
    . "; filename*=UTF-8''" . rawurlencode($filename));

readfile($tmp);
exit;
