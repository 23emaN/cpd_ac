<?php

/**
 * ส่งไฟล์ให้ดาวน์โหลด — ทางเดียวที่ไฟล์ในคลังออกจากเซิร์ฟเวอร์ได้
 *
 * ไฟล์อยู่นอก document root จึงไม่มีทางโหลดตรงทาง URL ได้เลย ทุกใบต้องผ่านที่นี่
 * ซึ่งตรวจสิทธิ์และบันทึกร่องรอยก่อนเสมอ
 *
 * รับผ่าน GET เพราะต้องเปิดจาก window.location / <a href> ได้ (ดาวน์โหลดไฟล์
 * ผ่าน AJAX แล้วแปลงเป็น blob ทำได้แต่กินหน่วยความจำเท่าขนาดไฟล์ในเบราว์เซอร์)
 * user_id จึงมาทาง query string เหมือนหน้าอื่นของโปรเจกต์
 *
 * ?mode=inline ใช้กับการดูตัวอย่างเท่านั้น และเฉพาะชนิดที่ปลอดภัยจริง
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_GET['customer_id'] ?? 0);
$user_id     = (int) ($_GET['user_id'] ?? 0);
$node_id     = (int) ($_GET['node_id'] ?? 0);
$version     = (int) ($_GET['version'] ?? 0);
$inline      = ($_GET['mode'] ?? '') === 'inline';

/** จบคำขอด้วยข้อความธรรมดา — ตรงนี้ตอบเป็นไฟล์ ไม่ใช่ JSON */
function cd_dl_stop(int $code, string $msn): void
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    echo $msn;
    exit;
}

if (!cd_customer($customer_id) || !cd_can($user_id, 'cdrive_view')) {
    cd_dl_stop(403, 'ไม่มีสิทธิ์เปิดไฟล์นี้');
}

$node = cd_find_node($customer_id, $node_id);

if (!$node || $node['kind'] !== 'file') {
    cd_dl_stop(404, 'ไม่พบไฟล์');
}

$ext = (string) $node['ext'];

// ดูตัวอย่างในเว็บได้เฉพาะรูปภาพกับ PDF — Excel/Word เปิดในเบราว์เซอร์ไม่ได้อยู่แล้ว
// และ HTML/SVG ไม่อยู่ในรายการอนุญาตตั้งแต่ต้นเพราะรันสคริปต์ได้
if ($inline && !cd_is_previewable($ext)) {
    cd_dl_stop(400, 'ไฟล์ชนิดนี้เปิดดูในเว็บไม่ได้ กรุณาดาวน์โหลด');
}

$path = $version > 0 ? cd_version_path($node, $version) : cd_current_path($node);

if (!is_file($path)) {
    cd_dl_stop(404, 'ไม่พบไฟล์บนเซิร์ฟเวอร์ — อาจถูกลบไปแล้วหรือยังอัปโหลดไม่สำเร็จ');
}

$name = (string) $node['name'];

if ($version > 0) {
    // แนบเลขเวอร์ชันเข้าไปในชื่อ เพื่อไม่ให้ไฟล์เก่ากับใหม่ทับกันในโฟลเดอร์ดาวน์โหลด
    $base = $ext !== '' ? mb_substr($name, 0, mb_strlen($name, 'UTF-8') - mb_strlen($ext, 'UTF-8') - 1, 'UTF-8') : $name;
    $name = $base . ' (v' . $version . ')' . ($ext !== '' ? '.' . $ext : '');
}

// เขียนร่องรอยก่อนเริ่มส่งไฟล์ — พอส่ง body ไปแล้วจะเขียนอะไรไม่ได้อีก
cd_activity($customer_id, $inline ? 'preview' : 'download', $node_id, $name, 'staff', $user_id);

while (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . cd_mime($ext));
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment')
    . '; filename="' . rawurlencode($name) . '"'
    . "; filename*=UTF-8''" . rawurlencode($name));

if ($inline) {
    // PDF ที่มาจากคนนอกต้องเรนเดอร์ในกล่องปิด ห้ามให้รันสคริปต์หรือยิงคำขอออกไปไหน
    header("Content-Security-Policy: sandbox; default-src 'none'; object-src 'self'; plugin-types application/pdf;");
}

readfile($path);
exit;
