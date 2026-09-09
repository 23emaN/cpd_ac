<?php

/**
 * ให้แขกดาวน์โหลดไฟล์ — เฉพาะโหมด share ที่เปิด allow_download เท่านั้น
 *
 * โหมด collect เข้าถึงตรงนี้ไม่ได้เลย เพราะ collect คือ "กล่องรับเอกสาร"
 * ถ้าโหลดได้ด้วยมันก็คือการแชร์โฟลเดอร์ ซึ่งเป็นคนละอย่างกับที่คนสร้างลิงก์ตั้งใจ
 *
 * ส่งเป็น attachment เสมอ ไม่มี inline preview ให้แขก —
 * ลดพื้นที่ที่ไฟล์จากคนนอกจะถูกเรนเดอร์ในเบราว์เซอร์ของคนอื่น
 */

require_once __DIR__ . '/../../config/customer_drive.php';

[$link, $session] = cd_guest_guard();

function pt_stop(int $code, string $msn): void
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    header('Referrer-Policy: no-referrer');
    echo $msn;
    exit;
}

if ($link['mode'] !== 'share' || (string) $link['allow_download'] !== '1') {
    pt_stop(403, 'ลิงก์นี้ไม่ได้เปิดให้ดาวน์โหลด');
}

$node_id = (int) ($_GET['node_id'] ?? 0);
$node = cd_guest_can_see($link, $session, $node_id);

if (!$node || $node['kind'] !== 'file') {
    pt_stop(404, 'ไม่พบไฟล์');
}

$path = cd_current_path($node);

if (!is_file($path)) {
    pt_stop(404, 'ไม่พบไฟล์');
}

$name = (string) $node['name'];

cd_activity(
    (int) $link['customer_id'],
    'download',
    $node_id,
    $name,
    'guest',
    null,
    (string) ($session['guest_label'] ?? ''),
    (int) $link['link_id']
);

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . cd_mime((string) $node['ext']));
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Content-Disposition: attachment; filename="' . rawurlencode($name) . '"'
    . "; filename*=UTF-8''" . rawurlencode($name));

readfile($path);
exit;
