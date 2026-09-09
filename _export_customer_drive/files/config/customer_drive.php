<?php

/**
 * คลังไฟล์ลูกค้า (Customer Drive) — ชั้นฟังก์ชันร่วม
 *
 * ทุก endpoint ใน main/ajax/customer_drive/ include ไฟล์นี้ (ซึ่ง require functions.php ต่อให้เอง)
 * แหล่งความจริงของฟีเจอร์นี้คือ docs/CUSTOMER_DRIVE.md — ถ้าจะตัดสินใจต่างจากที่เขียนไว้
 * ให้แก้เอกสารก่อน แล้วค่อยแก้ไฟล์นี้
 *
 * กติกาที่ทุกฟังก์ชันในไฟล์นี้ยึด:
 *   1. ทุก query ที่หา node กรอง customer_id เสมอ — ถ้าลืม การส่ง node_id มาตรง ๆ
 *      จะเปิดคลังไฟล์ของลูกค้ารายอื่นได้ทันที
 *   2. ตรวจสิทธิ์ฝั่งเซิร์ฟเวอร์จาก user_id ที่ส่งมาเสมอ ห้ามเชื่อ flag จากหน้าเว็บ
 *      (ระบบนี้ไม่มี PHP session ตัวตนอยู่ใน localStorage ซึ่งผู้เรียกกำหนดเองได้)
 *   3. อ่านทรีทั้งคลังด้วย query เดียวแล้วจัดชั้นในหน่วยความจำ ห้ามยิงทีละชั้น
 *
 * prefix ของโมดูลนี้คือ cd_ ส่วนโมดูลกระดาษทำการคือ wp_ — ห้ามปนกัน
 * เพราะทั้งสองมีฟังก์ชันชื่อคล้ายกันมาก (cd_flatten / wp_flatten)
 */

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/setting.php';
require_once __DIR__ . '/notification.php';

/*
 * โหลดชั้น Web Push ถ้ามี — noti_add() ตรวจด้วย function_exists() อีกชั้น
 * ระบบจึงทำงานได้ปกติแม้ลบไฟล์นี้ทิ้งหรือยังไม่ได้รัน sql/005
 */
if (is_file(__DIR__ . '/push.php')) {
    require_once __DIR__ . '/push.php';
}

/*
 * ที่เก็บไฟล์คลังลูกค้า — อยู่ใน storage/ ของโปรเจกต์
 *
 *   <รากโปรเจกต์>/storage/customer-files/{customer_id}/{node_id}/...
 *
 * อยู่ในโปรเจกต์เพื่อให้ย้ายเครื่อง สำรอง และดูของได้ในที่เดียวกับโค้ด
 * แลกกับการที่มันอยู่ "ใต้ document root" ซึ่งแปลว่าโดยธรรมชาติแล้ว
 * ใครเดา URL ถูกก็โหลดไฟล์ลูกค้าได้โดยไม่ต้องล็อกอิน
 *
 * จึงต้องมีด่านกันสามชั้น และ **ต้องยิงทดสอบว่ามันทำงานจริงบนเซิร์ฟเวอร์ที่ใช้อยู่**
 * ไม่ใช่เชื่อว่ากันได้เพราะมีไฟล์ครบ:
 *
 *   Apache      storage/.htaccess (Require all denied)
 *   Herd/Valet  LocalValetDriver::isBlocked() ที่รากโปรเจกต์ → 403
 *   nginx จริง  ต้องเพิ่ม location block ที่เซิร์ฟเวอร์เอง — .htaccess ใช้ไม่ได้
 *   ชั้นสุดท้าย  storage/index.php ตอบ 403 กันการไล่ดูรายชื่อไฟล์
 *
 * บทเรียนที่มาจากของจริงในโปรเจกต์นี้: uploads/workpaper/.htaccess เขียน
 * Deny from all ไว้ครบถ้วน แต่ Herd ใช้ nginx ซึ่งไม่อ่าน .htaccess เลย
 * เปิด URL ตรงได้ HTTP 200 พร้อมไฟล์เต็ม — ด่านที่ไม่ได้ทดสอบคือด่านที่ไม่มีอยู่จริง
 * ใช้ cd_storage_exposed() ตรวจได้ทุกเมื่อ ผ่าน main/ajax/setting/check_storage.php
 *
 * ถ้าเซิร์ฟเวอร์ไหนกันไม่ได้ ให้สร้าง config/customer_drive.local.php แล้ว
 * define('CD_FILES_PATH', ...) ชี้ออกไปนอก document root แทน
 */
if (is_file(__DIR__ . '/customer_drive.local.php')) {
    require_once __DIR__ . '/customer_drive.local.php';
}

if (!defined('CD_FILES_PATH')) {
    define('CD_FILES_PATH', realpath(__DIR__ . '/..') . '/storage/customer-files');
}

/** เส้นทาง URL ของที่เก็บไฟล์ เทียบจากรากเว็บ — ใช้ตรวจว่าด่านกันได้จริงไหม */
const CD_STORAGE_URI = '/storage/customer-files';

// ============================================================
// ค่าคงที่
// ============================================================

/**
 * นามสกุลที่ "แขก" อัปได้ — แคบกว่าของพนักงานโดยตั้งใจ
 *
 * jpeg ต้องมีคู่กับ jpg เสมอเพราะเป็นไฟล์ชนิดเดียวกันคนละตัวสะกด
 * กล้องและโปรแกรมจำนวนมากบันทึกเป็น .jpeg ถ้าไม่ใส่ ลูกค้าจะเจอ
 * "ไฟล์ชนิดนี้ส่งไม่ได้" กับรูปภาพธรรมดาแล้วโทรมาถาม
 *
 * xls ต้องมีเพราะลูกค้าที่ใช้โปรแกรมบัญชีเก่ายังส่งมาแบบนั้น
 */
const CD_GUEST_EXT = ['pdf', 'png', 'jpg', 'jpeg', 'xlsx', 'xls', 'doc', 'docx'];

/**
 * นามสกุลที่ "พนักงาน" อัปได้ — กว้างกว่าเพราะเป็นคนในองค์กรที่รู้ว่ากำลังวางอะไร
 *
 * zip เก็บได้แต่ **ห้ามแตกไฟล์ฝั่งเซิร์ฟเวอร์เด็ดขาด** ให้โหลดไปแตกเอง
 */
const CD_STAFF_EXT = ['pdf', 'png', 'jpg', 'jpeg', 'xlsx', 'xls', 'doc', 'docx', 'csv', 'txt', 'zip', 'gif', 'webp'];

/** ชนิดที่เบราว์เซอร์เรนเดอร์เองได้อย่างปลอดภัย (ไม่รวม svg/html เพราะรันสคริปต์ได้) */
const CD_IMAGE_EXT = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

/** เพดานเชิงนโยบายเมื่อยังไม่ได้ตั้งค่าใน tbl_setting (MB) */
const CD_DEFAULT_MAX_UPLOAD_MB = 40;

/** ยาวสุดของชื่อที่ยอมให้ตั้ง — คอลัมน์ name เป็น varchar(255) */
const CD_MAX_NAME_LEN = 200;

/**
 * รายการ action ที่อนุญาตให้บันทึกลง tbl_cd_activity
 *
 * ห้ามคิดคำใหม่นอกรายการนี้โดยไม่แก้ docs/CUSTOMER_DRIVE.md หัวข้อ 5.5 ก่อน —
 * หน้าความเคลื่อนไหวแปลรหัสพวกนี้เป็นข้อความไทย ถ้ามีรหัสแปลกเข้ามาจะแสดงเป็นรหัสดิบ
 */
const CD_ACTIONS = [
    'upload', 'upload_version', 'download', 'preview',
    'create_folder', 'rename', 'move', 'delete', 'restore', 'purge',
    'link_create', 'link_open', 'link_auth_fail', 'link_revoke', 'link_expire',
];

// ============================================================
// พื้นฐาน
// ============================================================

function cd_now(): string
{
    return date('Y-m-d H:i:s');
}

/** htmlspecialchars แบบสั้น — ใช้ทุกจุดที่พ่นค่าจากฐานข้อมูลลง HTML */
function cd_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** ป้ายเวลาแบบสั้นที่ใช้ทั่วโมดูลนี้ */
function cd_time(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }

    $ts = strtotime($datetime);

    return $ts ? date('d/m/Y H:i', $ts) : '';
}

/**
 * ป้ายหัววันสำหรับรายการความเคลื่อนไหว — "วันนี้" / "เมื่อวาน" / "1 ก.ย. 2569"
 *
 * ใช้แทนการพิมพ์วันที่เต็มซ้ำทุกแถว เพราะรายการส่วนใหญ่เกิดวันเดียวกัน
 * ตาจึงต้องอ่านเลขชุดเดิมซ้ำหลายสิบครั้งเพื่อหาว่าอันไหนคนละวัน
 *
 * ปีเป็น พ.ศ. และเดือนเป็นตัวย่อไทย ให้ตรงกับที่คนในสำนักงานใช้พูดกันจริง
 * ส่วน cd_time() ยังเป็น d/m/Y ตามเดิมเพราะใช้ในตารางที่ต้องการความกระชับ
 */
function cd_day_label(int $ts): string
{
    $day = date('Y-m-d', $ts);

    if ($day === date('Y-m-d')) {
        return 'วันนี้';
    }

    if ($day === date('Y-m-d', strtotime('-1 day'))) {
        return 'เมื่อวาน';
    }

    $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
               'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

    return (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . ((int) date('Y', $ts) + 543);
}

/**
 * ชื่อไอคอน Material Symbols ของแต่ละชนิดไฟล์
 *
 * อยู่ตรงนี้เพราะมีสามที่ที่ต้องวาดไอคอนไฟล์ (ตารางหลัก · ถังขยะ · ผลค้นหาข้ามลูกค้า)
 * ก่อนหน้านี้ประกาศไว้ท้าย render_table.php ซึ่งเป็นไฟล์ที่ถูก include เฉพาะตอนวาดตาราง
 * ที่อื่นจึงเอื้อมไม่ถึงแล้วต้องฮาร์ดโค้ด 'draft' ให้ทุกไฟล์ —
 * ผลคือไฟล์ Excel ในถังขยะขึ้นเป็นไอคอนเอกสารเปล่า แยกจาก PDF ไม่ออก
 */
function cd_icon_for(string $kind): string
{
    return match ($kind) {
        'folder'  => 'folder',
        'image'   => 'image',
        'pdf'     => 'picture_as_pdf',
        'sheet'   => 'table',          // ตารางกริด ไม่ใช่หน้ากระดาษ
        'doc'     => 'description',
        'archive' => 'folder_zip',
        'text'    => 'notes',
        default   => 'draft',
    };
}

function cd_format_bytes(int $bytes): string
{
    if ($bytes <= 0) {
        return '0 B';
    }

    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = (int) min(floor(log($bytes, 1024)), count($units) - 1);

    return round($bytes / (1024 ** $i), $i === 0 ? 0 : 1) . ' ' . $units[$i];
}

/** แปลงค่าแบบ "64M" ของ php.ini ให้เป็นจำนวนไบต์ */
function cd_ini_bytes(string $value): int
{
    $value = trim($value);

    // ค่าว่างใน php.ini แปลว่า "ไม่จำกัด" ไม่ใช่ "ศูนย์"
    if ($value === '') {
        return PHP_INT_MAX;
    }

    $unit = strtolower(substr($value, -1));
    $number = (int) $value;

    if ($unit === 'g') {
        return $number * 1024 * 1024 * 1024;
    }

    if ($unit === 'm') {
        return $number * 1024 * 1024;
    }

    if ($unit === 'k') {
        return $number * 1024;
    }

    return $number;
}

/**
 * ขนาดไฟล์สูงสุดที่อัปโหลดได้ "จริง" ในวินาทีนี้
 *
 * นี่คือกับดักที่อันตรายที่สุดของฟีเจอร์นี้ — ไฟล์ที่ใหญ่เกิน php.ini ทำให้
 * $_FILES ว่างเปล่าโดยไม่มี error ใด ๆ ลูกค้าเห็นหน้าจอเงียบแล้วคิดว่าส่งแล้ว
 * ส่วนเราไม่ได้ไฟล์และไม่รู้ว่าพลาด
 *
 * จึงต้องเอา "ค่าที่น้อยที่สุด" ระหว่างนโยบายกับความจริงของเซิร์ฟเวอร์ไปแสดงบนหน้าจอ
 * ห้ามอ้างค่าคงที่ตรง ๆ ที่ไหนทั้งสิ้น
 */
function cd_max_upload_bytes(): int
{
    $policy = set_int('cd_max_upload_mb', 1, 4096) * 1024 * 1024;

    if ($policy <= 0) {
        $policy = CD_DEFAULT_MAX_UPLOAD_MB * 1024 * 1024;
    }

    return min(
        $policy,
        cd_ini_bytes((string) ini_get('upload_max_filesize')),
        cd_ini_bytes((string) ini_get('post_max_size'))
    );
}

/**
 * นามสกุลที่อนุญาตของฝั่งหนึ่ง
 *
 * @param  string  $who  'staff' | 'guest'
 * @return list<string>
 */
function cd_allowed_ext(string $who = 'staff'): array
{
    return $who === 'guest' ? CD_GUEST_EXT : CD_STAFF_EXT;
}

/** นามสกุลของชื่อไฟล์ เป็นตัวเล็กเสมอ คืนค่าว่างเมื่อไม่มี */
function cd_ext_of(string $filename): string
{
    $ext = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));

    return preg_match('/^[a-z0-9]{1,10}$/', $ext) ? $ext : '';
}

/**
 * ชื่อที่ปลอดภัยพอจะเก็บลงฐานข้อมูลและแสดงผล
 *
 * **ชื่อนี้ไม่เคยถูกใช้เป็นชื่อไฟล์บนดิสก์** ไฟล์บนดิสก์ชื่อ current.{ext} เสมอ
 * ฟังก์ชันนี้จึงกันแค่อักขระควบคุมกับตัวที่ทำให้ path พัง ไม่ต้องเข้มถึงขั้น slug
 * เพราะชื่อไทยต้องอ่านออกเหมือนที่ลูกค้าตั้งมา
 */
function cd_safe_name(string $name): string
{
    /*
     * ปฏิเสธชื่อที่ไม่ใช่ UTF-8 ตั้งแต่ต้น
     *
     * ถ้าปล่อยผ่าน preg_replace ที่มี /u จะคืน null (PREG_BAD_UTF8_ERROR) แล้วตกไป
     * ใช้ค่าเดิม จากนั้น mb_substr จะแทนไบต์เสียด้วย ? — ผลคือชื่อไฟล์ของลูกค้า
     * กลายเป็น "?͡??û?Сͺ" ที่กู้กลับไม่ได้และไม่มีใครรู้ว่าเกิดตอนไหน
     * คืนค่าว่างแทน แล้วให้ผู้เรียกตอบว่า "ชื่อไฟล์ไม่ถูกต้อง" ซึ่งแก้ได้
     */
    if (!mb_check_encoding($name, 'UTF-8')) {
        return '';
    }

    $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
    $name = preg_replace('/[\\\\\/:?<>|"*]/u', '_', $name) ?? '';
    $name = trim($name);

    // "." และ ".." เป็นชื่อโฟลเดอร์พิเศษของระบบไฟล์ ห้ามให้หลุดเข้าไปแม้จะไม่ได้ใช้
    // ชื่อจากฐานข้อมูลไปแตะดิสก์ก็ตาม — กันไว้เผื่อวันหนึ่งมีคนเอาไปต่อ path
    if ($name === '.' || $name === '..') {
        return '';
    }

    return mb_substr($name, 0, CD_MAX_NAME_LEN, 'UTF-8');
}

// ============================================================
// ผู้ใช้ / สิทธิ์
// ============================================================

/**
 * ผู้ใช้ทั้งหมด แปลงเป็นรูปที่ตารางใช้ได้ทันที
 *
 * ตารางแสดงชื่อผู้สร้าง/ผู้แก้ไขในทุกแถว การหาแบบ query ต่อแถวจะกลายเป็น N+1 ทันที
 * จึงโหลดครั้งเดียวแล้วเก็บไว้ (โครงเดียวกับ wp_users())
 *
 * @return array<int, array{user_id:int,name:string,initial:string,role_name:string,color:string,active:bool}>
 */
function cd_users(): array
{
    global $conn;

    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $sql = "SELECT u.user_id, u.f_name, u.s_name, u.active_status,
                   r.role_name, r.role_theme
            FROM tbl_user u
            LEFT JOIN tbl_role r ON r.role_id = u.role_id
            ORDER BY r.list_order, u.f_name";

    $cache = [];

    foreach ($conn->prepareAndExecute($sql)->fetchAll() as $row) {
        $name = trim($row['f_name'] . ' ' . $row['s_name']);
        $theme = $row['role_theme'] ?: 'secondary';

        $cache[(int) $row['user_id']] = [
            'user_id'   => (int) $row['user_id'],
            'name'      => $name !== '' ? $name : ('#' . $row['user_id']),
            'initial'   => mb_strtoupper(mb_substr($name !== '' ? $name : '?', 0, 1, 'UTF-8'), 'UTF-8'),
            'role_name' => (string) ($row['role_name'] ?? 'ยังไม่กำหนด'),
            'color'     => 'var(--bs-' . $theme . ')',
            'active'    => $row['active_status'] === '1',
        ];
    }

    return $cache;
}

function cd_user(?int $user_id): ?array
{
    if (!$user_id) {
        return null;
    }

    return cd_users()[$user_id] ?? null;
}

function cd_user_name(?int $user_id): string
{
    $user = cd_user($user_id);

    return $user ? $user['name'] : '—';
}

/** ตัวตรวจสิทธิ์เดียวของโมดูลนี้ — อ่านจากฐานข้อมูลเสมอ ไม่เชื่อค่าที่ส่งมา */
function cd_can(?int $user_id, string $code): bool
{
    return $user_id ? hasPermission($user_id, $code) : false;
}

/**
 * สรุปสิทธิ์ทั้งชุดเป็น array ส่งให้ JS ใช้ตัดสินว่าจะโชว์เมนูอะไร
 *
 * เป็นแค่การซ่อนปุ่มให้หน้าจอสะอาด **ไม่ใช่ด่านความปลอดภัย** —
 * endpoint ทุกตัวต้องตรวจซ้ำฝั่งเซิร์ฟเวอร์เสมอ
 */
function cd_perm_summary(?int $user_id): array
{
    $codes = [
        'cdrive_view',
        'cdrive_upload',
        'cdrive_edit',
        'cdrive_delete',
        'cdrive_manage_link',
        'cdrive_view_activity',
    ];

    $out = [];

    foreach ($codes as $code) {
        $out[$code] = cd_can($user_id, $code);
    }

    return $out;
}

// ============================================================
// ลูกค้า
// ============================================================

/**
 * แถวลูกค้าหนึ่งราย — คืน null เมื่อไม่มีหรือถูกลบไปแล้ว
 *
 * ต่างจาก wp_task() ตรงที่ไม่มีแนวคิด "งานที่ปิดแล้ว" เพราะคลังไฟล์ผูกกับลูกค้า
 * ไม่ใช่กับปีบัญชี งานปิดแล้วคลังไฟล์ยังต้องเปิดอยู่ (เหตุผลอยู่ใน CUSTOMER_DRIVE.md หัวข้อ 4)
 */
function cd_customer(int $customer_id): ?array
{
    global $conn;

    if ($customer_id <= 0) {
        return null;
    }

    $row = $conn->prepareAndExecute(
        "SELECT customer_id, customer_code, customer_name, customer_size,
                fiscal_start_date, fiscal_start_month, active_status
         FROM tbl_customer
         WHERE customer_id = ? AND delete_status = '0'",
        [$customer_id]
    )->fetch();

    return $row ?: null;
}

// ============================================================
// ทรี
// ============================================================

/**
 * ทุก node ที่ยังไม่ถูกทิ้ง — query เดียวทั้งคลัง
 *
 * ORDER BY ตรงนี้เป็นแค่ลำดับตั้งต้นให้ผลลัพธ์คงที่ ลำดับที่ผู้ใช้เห็นจริง
 * ตัดสินด้วย cd_node_cmp() ตอน cd_flatten()
 *
 * @return list<array<string,mixed>>
 */
function cd_all_nodes(int $customer_id): array
{
    global $conn;

    return $conn->prepareAndExecute(
        "SELECT * FROM tbl_cd_node
         WHERE customer_id = ? AND delete_datetime IS NULL
         ORDER BY list_order, name",
        [$customer_id]
    )->fetchAll();
}

/**
 * ลำดับของพี่น้องสองตัวในโฟลเดอร์เดียวกัน
 *
 * list_order = 0 แปลว่า "กลุ่มนี้ยังไม่เคยถูกสั่งเรียงเอง" ทั้งกลุ่มจึงเรียงตามชื่อ
 * พอสั่งย้ายขึ้น/ลงครั้งแรก reorder.php จะเขียน 10,20,30… ให้ทั้งกลุ่ม
 *
 * โฟลเดอร์มาก่อนไฟล์เสมอ — เป็นสิ่งที่ทุกคนคาดหวังจากไดรฟ์ และทำให้
 * การไล่โครงสร้างด้วยสายตาเร็วกว่ามาก
 *
 * เทียบชื่อด้วย strnatcasecmp ไม่ใช่เทียบตัวอักษรล้วน มิฉะนั้น "งวด 10" จะมาก่อน "งวด 2"
 */
function cd_node_cmp(array $a, array $b): int
{
    $order = ((int) $a['list_order']) <=> ((int) $b['list_order']);

    if ($order !== 0) {
        // 0 แปลว่ายังไม่เคยเรียงเอง ต้องไปท้ายสุดของกลุ่มที่เรียงแล้ว ไม่ใช่มาก่อน
        $az = ((int) $a['list_order']) === 0;
        $bz = ((int) $b['list_order']) === 0;

        if ($az !== $bz) {
            return $az ? 1 : -1;
        }

        return $order;
    }

    $af = $a['kind'] === 'folder';
    $bf = $b['kind'] === 'folder';

    if ($af !== $bf) {
        return $af ? -1 : 1;
    }

    return strnatcasecmp((string) $a['name'], (string) $b['name']);
}

/**
 * จัดเป็นลำดับชั้นพร้อมความลึก สำหรับวาดตาราง
 *
 * ทำในหน่วยความจำจากผลของ cd_all_nodes() ครั้งเดียว ไม่วนยิง query ทีละชั้น
 *
 * @return list<array<string,mixed>> แต่ละตัวมี depth และ has_children ติดมาด้วย
 */
function cd_flatten(int $customer_id): array
{
    $byParent = [];

    foreach (cd_all_nodes($customer_id) as $node) {
        $byParent[(int) ($node['parent_id'] ?? 0)][] = $node;
    }

    // เรียงทีละโฟลเดอร์ ไม่ใช่ทั้งคลังรวดเดียว — ลำดับมีความหมายเฉพาะในหมู่พี่น้องกันเอง
    foreach ($byParent as &$group) {
        usort($group, 'cd_node_cmp');
    }

    unset($group);

    $out = [];

    $walk = static function (int $parent, int $depth) use (&$walk, &$out, $byParent): void {
        foreach ($byParent[$parent] ?? [] as $node) {
            $node['depth'] = $depth;
            $node['has_children'] = isset($byParent[(int) $node['node_id']]);
            $out[] = $node;

            $walk((int) $node['node_id'], $depth + 1);
        }
    };

    $walk(0, 0);

    return $out;
}

/**
 * ลูกโดยตรงของโฟลเดอร์หนึ่ง เรียงแล้ว — ใช้วาดตารางแบบทีละชั้น (มุมมองปกติ)
 *
 * @param  int|null  $parent_id  null = ชั้นบนสุดของคลัง
 * @return list<array<string,mixed>>
 */
function cd_children(int $customer_id, ?int $parent_id): array
{
    $out = [];

    foreach (cd_all_nodes($customer_id) as $node) {
        $p = $node['parent_id'] === null ? null : (int) $node['parent_id'];

        if ($p === $parent_id) {
            $out[] = $node;
        }
    }

    usort($out, 'cd_node_cmp');

    return $out;
}

/** หา node หนึ่งตัว — กรอง customer_id เสมอ นี่คือด่านกันข้ามคลัง */
function cd_find_node(int $customer_id, int $node_id, bool $include_trashed = false): ?array
{
    global $conn;

    if ($customer_id <= 0 || $node_id <= 0) {
        return null;
    }

    $sql = "SELECT * FROM tbl_cd_node WHERE customer_id = ? AND node_id = ?";

    if (!$include_trashed) {
        $sql .= " AND delete_datetime IS NULL";
    }

    $row = $conn->prepareAndExecute($sql, [$customer_id, $node_id])->fetch();

    return $row ?: null;
}

/**
 * id ของลูกหลานทั้งหมดใต้ node หนึ่ง
 *
 * ไล่ในหน่วยความจำจากตารางความสัมพันธ์ที่อ่านมาครั้งเดียว ไม่ใช่ recursive query
 * เพราะ MySQL 8 มี CTE ก็จริง แต่ validateSQL() ของโปรเจกต์ทำให้ query ซับซ้อน
 * เสี่ยงโดนบล็อกโดยไม่จำเป็น และคลังหนึ่งมี node ไม่กี่พันแถวเท่านั้น
 *
 * @return list<int>
 */
function cd_descendant_ids(int $customer_id, int $node_id, bool $include_trashed = false): array
{
    global $conn;

    $sql = "SELECT node_id, parent_id FROM tbl_cd_node WHERE customer_id = ?";

    if (!$include_trashed) {
        $sql .= " AND delete_datetime IS NULL";
    }

    $byParent = [];

    foreach ($conn->prepareAndExecute($sql, [$customer_id])->fetchAll() as $row) {
        $byParent[(int) ($row['parent_id'] ?? 0)][] = (int) $row['node_id'];
    }

    $out = [];
    $queue = [$node_id];

    while ($queue) {
        $current = array_shift($queue);

        foreach ($byParent[$current] ?? [] as $child) {
            // กันลูปในกรณีข้อมูลเพี้ยน (parent ชี้วนกลับมาหาตัวเอง)
            if (in_array($child, $out, true)) {
                continue;
            }

            $out[] = $child;
            $queue[] = $child;
        }
    }

    return $out;
}

/**
 * ทางเดินจากรากลงมาถึง node นี้ สำหรับวาด breadcrumb
 *
 * @return list<array{node_id:int,name:string}> เรียงจากบนลงล่าง ไม่รวมตัว node เอง
 */
function cd_breadcrumb(int $customer_id, ?int $node_id): array
{
    if (!$node_id) {
        return [];
    }

    $byId = [];

    foreach (cd_all_nodes($customer_id) as $node) {
        $byId[(int) $node['node_id']] = $node;
    }

    $trail = [];
    $current = $node_id;
    $guard = 0;

    while ($current && isset($byId[$current]) && $guard++ < 100) {
        $node = $byId[$current];

        array_unshift($trail, [
            'node_id' => (int) $node['node_id'],
            'name'    => (string) $node['name'],
        ]);

        $current = $node['parent_id'] === null ? 0 : (int) $node['parent_id'];
    }

    return $trail;
}

/**
 * โฟลเดอร์ที่ node_id ชี้ถึง — คืน null เมื่อเป็นรากหรือหาไม่เจอ
 *
 * ใช้แปลง ?folder=<id> ที่มาจาก URL ให้เป็นแถวจริง ถ้าส่ง id ของ "ไฟล์" มา
 * ต้องได้ null ไม่ใช่แถวไฟล์ มิฉะนั้นจะเกิดโฟลเดอร์ซ้อนในไฟล์
 */
function cd_folder_at(int $customer_id, ?int $node_id): ?array
{
    if (!$node_id) {
        return null;
    }

    $node = cd_find_node($customer_id, $node_id);

    return ($node && $node['kind'] === 'folder') ? $node : null;
}

/**
 * ลำดับถัดไปของกลุ่มพี่น้อง
 *
 * คืน 0 เมื่อกลุ่มนี้ยังไม่เคยถูกสั่งเรียงเอง เพื่อให้ยังเรียงตามชื่ออยู่
 * (ถ้าใส่เลขให้ทันที ของใหม่จะไปโผล่ท้ายสุดเสมอแม้ชื่อควรอยู่ต้น ๆ)
 */
function cd_next_sort(int $customer_id, ?int $parent_id): int
{
    global $conn;

    $sql = "SELECT MAX(list_order) AS m FROM tbl_cd_node
            WHERE customer_id = ? AND delete_datetime IS NULL AND parent_id ";

    if ($parent_id === null) {
        $row = $conn->prepareAndExecute($sql . "IS NULL", [$customer_id])->fetch();
    } else {
        $row = $conn->prepareAndExecute($sql . "= ?", [$customer_id, $parent_id])->fetch();
    }

    $max = (int) ($row['m'] ?? 0);

    return $max > 0 ? $max + 10 : 0;
}

/**
 * หา node ชื่อนี้ในโฟลเดอร์นี้ — ใช้ตัดสินว่าชื่อชนหรือไม่
 *
 * เทียบชื่อแบบไม่สนตัวพิมพ์ (collation utf8mb4_unicode_ci ทำให้เอง)
 */
function cd_find_by_name(int $customer_id, ?int $parent_id, string $name, ?int $except_id = null): ?array
{
    global $conn;

    $sql = "SELECT * FROM tbl_cd_node
            WHERE customer_id = ? AND delete_datetime IS NULL AND name = ? AND parent_id ";
    $params = [$customer_id, $name];

    if ($parent_id === null) {
        $sql .= "IS NULL";
    } else {
        $sql .= "= ?";
        $params[] = $parent_id;
    }

    if ($except_id !== null) {
        $sql .= " AND node_id <> ?";
        $params[] = $except_id;
    }

    $row = $conn->prepareAndExecute($sql . " LIMIT 1", $params)->fetch();

    return $row ?: null;
}

/**
 * ชื่อที่ไม่ชนกับใครในโฟลเดอร์นี้ — เติม (2) (3) ต่อท้ายชื่อ ไม่ใช่ต่อท้ายนามสกุล
 *
 * ใช้กับการอัปของ "แขก" เป็นหลัก เพราะแขกในโหมด collect มองไม่เห็นของเดิม
 * จึงไม่มีทางตั้งใจทับไฟล์ของพนักงาน — ถ้าปล่อยให้ทับ ไฟล์ของพนักงาน
 * จะกลายเป็นเวอร์ชันใหม่โดยที่ทั้งสองฝ่ายไม่รู้ตัว
 * (กติกา "ชื่อซ้ำ = เวอร์ชันใหม่" ใช้กับการอัปของพนักงานเท่านั้น)
 */
function cd_unique_name(int $customer_id, ?int $parent_id, string $name): string
{
    if (!cd_find_by_name($customer_id, $parent_id, $name)) {
        return $name;
    }

    $ext = cd_ext_of($name);
    $base = $ext !== '' ? mb_substr($name, 0, mb_strlen($name, 'UTF-8') - mb_strlen($ext, 'UTF-8') - 1, 'UTF-8') : $name;
    $tail = $ext !== '' ? '.' . $ext : '';

    for ($i = 2; $i <= 999; $i++) {
        $candidate = $base . ' (' . $i . ')' . $tail;

        if (!cd_find_by_name($customer_id, $parent_id, $candidate)) {
            return $candidate;
        }
    }

    // ไม่ควรถึงตรงนี้ แต่ถ้าถึง ให้ต่อท้ายด้วยเวลาแทนการล้มเหลว
    return $base . ' (' . date('YmdHis') . ')' . $tail;
}

/**
 * ห้ามย้ายโฟลเดอร์เข้าไปในลูกหลานของตัวเอง
 *
 * ถ้าปล่อยให้ทำได้ กิ่งนั้นจะหลุดจากทรีทั้งกิ่ง — มองไม่เห็นจากหน้าจอ
 * แต่ยังกินพื้นที่และยังอยู่ในฐานข้อมูล เป็นบั๊กที่หาสาเหตุยากมาก
 */
function cd_is_descendant(int $customer_id, int $node_id, ?int $target_id): bool
{
    if ($target_id === null) {
        return false;
    }

    if ($node_id === $target_id) {
        return true;
    }

    return in_array($target_id, cd_descendant_ids($customer_id, $node_id, true), true);
}

// ============================================================
// ถังขยะ
// ============================================================

/**
 * รายการในถังขยะ — แสดงเฉพาะ "หัว" ของสิ่งที่ถูกลบ
 *
 * ถ้าลบทั้งโฟลเดอร์ จะไม่ไล่แสดงไฟล์ข้างในซ้ำ เพราะผู้ใช้ลบไปทีเดียวหนึ่งครั้ง
 * และจะกู้คืนทีเดียวหนึ่งครั้งเหมือนกัน
 *
 * **ถังขยะของโมดูลนี้ไม่มีวันหมดอายุ** ต่างจากกระดาษทำการที่เก็บ 30 วัน —
 * ตัดสินใจแล้วว่าเอกสารลูกค้าเก็บถาวร ลบถาวรได้ด้วยการสั่งเองเท่านั้น
 *
 * @return list<array<string,mixed>> แต่ละตัวมี child_count ติดมาด้วย
 */
function cd_trash_items(int $customer_id): array
{
    global $conn;

    $trashed = $conn->prepareAndExecute(
        "SELECT * FROM tbl_cd_node
         WHERE customer_id = ? AND delete_datetime IS NOT NULL
         ORDER BY delete_datetime DESC, node_id DESC",
        [$customer_id]
    )->fetchAll();

    if (!$trashed) {
        return [];
    }

    $trashedIds = array_map(static fn ($n) => (int) $n['node_id'], $trashed);
    $tops = [];

    foreach ($trashed as $node) {
        $parent = $node['parent_id'] === null ? 0 : (int) $node['parent_id'];

        // แม่ก็อยู่ในถังขยะด้วย = ตัวนี้ถูกลบพ่วงไป ไม่ใช่รายการที่ผู้ใช้สั่งลบเอง
        if ($parent !== 0 && in_array($parent, $trashedIds, true)) {
            continue;
        }

        $node['child_count'] = count(cd_descendant_ids($customer_id, (int) $node['node_id'], true));

        $tops[] = $node;
    }

    return $tops;
}

function cd_trash_count(int $customer_id): int
{
    global $conn;

    $row = $conn->prepareAndExecute(
        "SELECT COUNT(*) AS n FROM tbl_cd_node WHERE customer_id = ? AND delete_datetime IS NOT NULL",
        [$customer_id]
    )->fetch();

    return (int) ($row['n'] ?? 0);
}

/**
 * หา node ที่อยู่ในถังขยะ — คืน null ถ้ามันยังไม่ถูกทิ้ง
 *
 * เงื่อนไข "ต้องอยู่ในถังขยะ" สำคัญกว่าที่เห็น เพราะการลบถาวรใช้ตัวนี้เป็นด่านเดียว
 * ก่อนลงมือ ถ้าไม่กรอง การส่ง node_id ของไฟล์ที่ยังใช้งานอยู่เข้ามาจะลบไฟล์
 * ทุกเวอร์ชันทิ้งถาวรทันที
 */
function cd_find_trashed(int $customer_id, int $node_id): ?array
{
    global $conn;

    $row = $conn->prepareAndExecute(
        "SELECT * FROM tbl_cd_node
         WHERE customer_id = ? AND node_id = ? AND delete_datetime IS NOT NULL",
        [$customer_id, $node_id]
    )->fetch();

    return $row ?: null;
}

/**
 * ลบถาวร — ลบเฉพาะ "แถว" ในฐานข้อมูล **ยังไม่แตะไฟล์บนดิสก์**
 *
 * แถวใน tbl_cd_version หายตาม foreign key ส่วน tbl_cd_activity **ไม่หาย**
 * เพราะไม่ได้ผูก FK ไว้โดยตั้งใจ — การลบถาวรเป็นการกระทำเดียวในระบบ
 * ที่ทำลายหลักฐาน จึงต้องเหลือร่องรอยว่าใครสั่งเมื่อไร
 *
 * **ทำไมถึงไม่ลบไฟล์ตรงนี้** — ฟังก์ชันนี้ถูกเรียกอยู่ใน transaction
 * ถ้าลบไฟล์ก่อนแล้ว DELETE ล้มทีหลัง transaction จะย้อนแถวกลับมาครบ
 * แต่ไฟล์บนดิสก์ย้อนกลับไม่ได้ ผลคือรายการในถังขยะที่กู้คืนมาแล้วเปิดไม่ได้
 * โดยไม่มีใครรู้ตัว (เจอจริงตอนทดสอบ: FK `fk_cd_link_root` ขวาง DELETE
 * เพราะมีลิงก์แชร์ชี้มาที่โฟลเดอร์นั้น แถวกลับมา 4 แถว แต่ไฟล์หายไป 2 ใบ)
 *
 * ผู้เรียกต้องเอา id ที่คืนไปส่งให้ cd_forget_files() **หลัง commit เท่านั้น**
 *
 * @return int[] node_id ทั้งหมดที่ถูกลบ รวมลูกหลาน
 */
function cd_purge_node(int $customer_id, int $node_id): array
{
    global $conn;

    $ids = cd_descendant_ids($customer_id, $node_id, true);
    $ids[] = $node_id;
    $ids = array_values(array_unique($ids));

    /*
     * ปลด root_node_id ของลิงก์ที่ "เพิกถอนไปแล้ว" ออกก่อน
     *
     * `fk_cd_link_root` เป็น RESTRICT ซึ่งไม่สนใจว่าลิงก์ยังใช้ได้หรือไม่
     * ถ้าไม่ปลดตรงนี้ ลิงก์ที่ตายไปแล้วจะขวางการลบโฟลเดอร์นั้นไปตลอดกาล
     * โดยที่ผู้ใช้ทำอะไรไม่ได้เลย (เพิกถอนไปแล้วก็ยังลบไม่ได้)
     *
     * ปลดเฉพาะลิงก์ที่เพิกถอนแล้วเท่านั้น — เหตุผลที่ FK เป็น RESTRICT
     * ไม่ใช่ SET NULL คือกันลิงก์ "ที่ยังใช้ได้" ขยายขอบเขตจากโฟลเดอร์เดียว
     * เป็นคลังทั้งใบเงียบ ๆ ซึ่งไม่เป็นปัญหากับลิงก์ที่เข้าไม่ได้แล้ว
     * และการเก็บแถวไว้ (แทนที่จะลบทิ้ง) ทำให้ประวัติว่าเคยมีลิงก์นี้ยังอยู่ครบ
     *
     * ลิงก์ที่ยังใช้ได้ถูกดักไว้ตั้งแต่ cd_links_pointing_at() ก่อนเปิด transaction
     */
    $place = implode(',', array_fill(0, count($ids), '?'));

    $conn->prepareAndExecute(
        "UPDATE tbl_cd_link SET root_node_id = NULL
         WHERE customer_id = ? AND revoked = '1' AND root_node_id IN ($place)",
        array_merge([$customer_id], $ids)
    );

    // ลบจากลูกขึ้นมาหาแม่ เพื่อไม่ให้ FK ของ parent_id ขัดจังหวะ
    // (ถึงจะตั้ง CASCADE ไว้ก็ตาม การลบตามลำดับทำให้ผลลัพธ์คาดเดาได้กว่า)
    foreach (array_reverse($ids) as $id) {
        $conn->prepareAndExecute(
            "DELETE FROM tbl_cd_node WHERE customer_id = ? AND node_id = ?",
            [$customer_id, $id]
        );
    }

    return $ids;
}

/**
 * ลิงก์แชร์ที่ยังไม่ถูกเพิกถอนและชี้มาที่ node เหล่านี้
 *
 * ใช้เตือนก่อนลบถาวร เพราะ `fk_cd_link_root` เป็น ON DELETE RESTRICT โดยตั้งใจ
 * (ปล่อยให้เป็น SET NULL ไม่ได้ เพราะลิงก์ที่เคยจำกัดอยู่โฟลเดอร์เดียว
 * จะกลายเป็นลิงก์ที่เห็นคลังทั้งใบเงียบ ๆ)
 *
 * ถ้าไม่ตรวจก่อน ผู้ใช้จะเจอข้อความ SQL ดิบ ๆ ซึ่งอ่านไม่รู้เรื่องและบอกวิธีแก้ไม่ได้
 *
 * @param int[] $ids
 * @return string[] ชื่อเรื่องของลิงก์ที่ขวางอยู่
 */
function cd_links_pointing_at(int $customer_id, array $ids): array
{
    global $conn;

    if (!$ids) {
        return [];
    }

    $place = implode(',', array_fill(0, count($ids), '?'));

    $rows = $conn->prepareAndExecute(
        "SELECT title FROM tbl_cd_link
         WHERE customer_id = ? AND revoked = '0' AND root_node_id IN ($place)",
        array_merge([$customer_id], $ids)
    )->fetchAll();

    return array_column($rows, 'title');
}

// ============================================================
// ไฟล์บนดิสก์
// ============================================================

/**
 * รากของที่เก็บไฟล์คลังลูกค้า (ดู CD_FILES_PATH ต้นไฟล์)
 *
 *   {root}/{customer_id}/{node_id}/current.{ext}   ไฟล์ล่าสุด
 *   {root}/{customer_id}/{node_id}/v{n}.{ext}      เวอร์ชันย้อนหลัง
 *
 * ใช้ node_id เป็นชื่อโฟลเดอร์ ไม่ใช่ชื่อไฟล์ — เปลี่ยนชื่อหรือย้ายในทรีแล้ว
 * ไฟล์บนดิสก์ไม่ต้องขยับตาม (ต่างจาก uploads/{ชื่อลูกค้า}/ ยุคเก่าที่พังทันที
 * เมื่อลูกค้าเปลี่ยนชื่อ — เห็นได้จากโฟลเดอร์ซ้ำใน uploads/ ทุกวันนี้)
 */
function cd_root(): string
{
    return CD_FILES_PATH;
}

/**
 * ตรวจว่าที่เก็บไฟล์ใช้งานได้จริง — คืนข้อความว่างเมื่อไม่มีปัญหา
 *
 * ล้มตรงนี้พร้อมบอกเหตุผล ดีกว่าปล่อยให้อัปโหลดล้มเงียบ ๆ ตอนผู้ใช้กำลังทำงาน
 * แล้วไม่มีใครรู้ว่าต้องไปแก้สิทธิ์โฟลเดอร์ที่ไหน
 */
function cd_storage_error(): string
{
    $root = cd_root();

    if (!is_dir($root) && !@mkdir($root, 0775, true)) {
        return 'สร้างโฟลเดอร์เก็บไฟล์ไม่ได้: ' . $root
            . ' — ให้สร้างเองแล้วตั้งสิทธิ์ให้เว็บเซิร์ฟเวอร์เขียนได้';
    }

    if (!is_writable($root)) {
        return 'โฟลเดอร์เก็บไฟล์เขียนไม่ได้: ' . $root
            . ' — ตั้งสิทธิ์ให้เว็บเซิร์ฟเวอร์เขียนได้ (ปกติ 775)';
    }

    return '';
}

/**
 * ที่เก็บไฟล์โหลดตรงทาง URL ได้จริงไหม — ทดสอบด้วยของจริง ไม่ใช่เดาจากคอนฟิก
 *
 * วางไฟล์ล่อที่มีเนื้อหาสุ่ม แล้วยิง HTTP กลับมาหาตัวเองเพื่อดูว่าได้เนื้อไฟล์คืนไหม
 * ถ้าได้ = ด่านไม่ทำงาน เอกสารการเงินของลูกค้าโหลดได้ด้วยการเดา URL
 *
 * ทำไมต้องทดสอบแทนที่จะดูว่ามี .htaccess ไหม: เพราะ .htaccess ที่มีอยู่ครบถ้วน
 * ไม่ได้แปลว่ามันทำงาน — nginx ไม่อ่านมันเลย และเราจะไม่มีทางรู้จนกว่าจะยิงดู
 * (ของจริงในโปรเจกต์นี้: uploads/workpaper/.htaccess เขียน Deny from all ไว้
 *  แต่เปิด URL ตรงได้ HTTP 200 พร้อมไฟล์เต็ม)
 *
 * ลบไฟล์ล่อทิ้งเสมอไม่ว่าผลจะเป็นอย่างไร
 *
 * @return array{tested:bool,exposed:bool,status:int,url:string,msn:string}
 */
function cd_storage_exposed(): array
{
    $out = ['tested' => false, 'exposed' => false, 'status' => 0, 'url' => '', 'msn' => ''];

    // ที่เก็บไฟล์ถูกย้ายออกไปนอกโปรเจกต์แล้ว ไม่มี URL ให้ยิง จึงไม่ต้องตรวจ
    if (!str_starts_with(str_replace('\\', '/', cd_root()), str_replace('\\', '/', realpath(__DIR__ . '/..')))) {
        $out['msn'] = 'ที่เก็บไฟล์อยู่นอกรากโปรเจกต์ จึงไม่มี URL ให้เข้าถึงตรงตั้งแต่ต้น';

        return $out;
    }

    if (!function_exists('curl_init')) {
        $out['msn'] = 'ตรวจไม่ได้เพราะเซิร์ฟเวอร์ไม่มีส่วนขยาย curl — ให้ทดสอบด้วยการเปิด URL เองในเบราว์เซอร์';

        return $out;
    }

    $error = cd_storage_error();

    if ($error !== '') {
        $out['msn'] = $error;

        return $out;
    }

    $name = '_probe-' . bin2hex(random_bytes(8)) . '.txt';
    $secret = bin2hex(random_bytes(16));
    $file = cd_root() . '/' . $name;

    if (@file_put_contents($file, $secret) === false) {
        $out['msn'] = 'สร้างไฟล์ทดสอบไม่ได้ที่ ' . cd_root();

        return $out;
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url = $scheme . '://' . $host . CD_STORAGE_URI . '/' . $name;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_FOLLOWLOCATION => false,
        // ใบรับรองของเครื่องพัฒนามักเป็น self-signed — เรากำลังยิงหาตัวเอง ไม่ใช่ปลายทางภายนอก
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);

    $body = (string) curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    @unlink($file);

    $out['url'] = $url;
    $out['status'] = $status;

    if ($status === 0) {
        $out['msn'] = 'ยิงทดสอบไม่สำเร็จ (' . $curlError . ') — ให้ทดสอบด้วยการเปิด URL เองในเบราว์เซอร์';

        return $out;
    }

    $out['tested'] = true;

    // ตัดสินจาก "ได้เนื้อไฟล์คืนมาไหม" ไม่ใช่จากรหัสสถานะ
    // เพราะบางเซิร์ฟเวอร์ตอบ 200 พร้อมหน้า index แทนไฟล์ ซึ่งไม่ใช่การรั่ว
    $out['exposed'] = str_contains($body, $secret);

    $out['msn'] = $out['exposed']
        ? 'ไฟล์ในที่เก็บโหลดได้ตรงทาง URL — เอกสารของลูกค้าเปิดได้โดยไม่ต้องล็อกอิน ต้องแก้ก่อนใช้งานจริง'
        : 'ด่านทำงานถูกต้อง เปิด URL ตรงแล้วไม่ได้เนื้อไฟล์ (HTTP ' . $status . ')';

    return $out;
}

function cd_node_dir(int $customer_id, int $node_id, bool $create = true): string
{
    $dir = cd_root() . '/' . $customer_id . '/' . $node_id;

    if ($create && !is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    return $dir;
}

function cd_current_path(array $node): string
{
    return cd_node_dir((int) $node['customer_id'], (int) $node['node_id'])
        . '/current.' . ($node['ext'] ?: 'bin');
}

function cd_version_path(array $node, int $version): string
{
    return cd_node_dir((int) $node['customer_id'], (int) $node['node_id'])
        . '/v' . $version . '.' . ($node['ext'] ?: 'bin');
}

/**
 * ลบไฟล์ทุกเวอร์ชันของ node นี้ทิ้ง — เรียกจากการลบถาวรเท่านั้น
 *
 * ประกอบ path เองแทนการเรียก cd_node_dir() แบบสร้างโฟลเดอร์
 * เพราะไม่ควรสร้างโฟลเดอร์เปล่าทิ้งไว้ระหว่างที่กำลังลบของ
 */
function cd_forget_files(int $customer_id, int $node_id): void
{
    $dir = cd_root() . '/' . $customer_id . '/' . $node_id;

    if (!is_dir($dir)) {
        return;
    }

    foreach (glob($dir . '/*') ?: [] as $file) {
        if (is_file($file)) {
            @unlink($file);
        }
    }

    @rmdir($dir);
}

/**
 * เขียนทับไฟล์แบบที่คนกำลังอ่านอยู่จะไม่เจอไฟล์ครึ่งใบ
 *
 * copy() เขียนทับปลายทางตรง ๆ ถ้ามีคนกดดาวน์โหลดจังหวะนั้นพอดีจะได้ไฟล์
 * ที่ยังเขียนไม่เสร็จ และ .xlsx/.docx ที่ขาดครึ่งคือ zip เสียเปิดไม่ได้
 * จึงเขียนลงไฟล์ชั่วคราวข้าง ๆ ก่อนแล้วค่อย rename ทับ
 *
 * วินโดวส์: rename ล้มถ้าปลายทางถูกโปรเซสอื่นเปิดค้างอยู่ จึงต้องมีทางถอย
 */
function cd_atomic_copy(string $from, string $to): bool
{
    $tmp = $to . '.tmp-' . bin2hex(random_bytes(4));

    if (!@copy($from, $tmp)) {
        return false;
    }

    if (!@rename($tmp, $to)) {
        if (!@copy($tmp, $to)) {
            @unlink($tmp);

            return false;
        }

        @unlink($tmp);
    }

    return true;
}

/** ประเภทสำหรับเลือกไอคอน/วิธีดูตัวอย่าง: image | pdf | sheet | doc | archive | text | file */
function cd_file_kind(?string $ext): string
{
    $ext = strtolower((string) $ext);

    if (in_array($ext, CD_IMAGE_EXT, true)) {
        return 'image';
    }

    if ($ext === 'pdf') {
        return 'pdf';
    }

    if (in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
        return 'sheet';
    }

    if (in_array($ext, ['doc', 'docx'], true)) {
        return 'doc';
    }

    if ($ext === 'zip') {
        return 'archive';
    }

    if ($ext === 'txt') {
        return 'text';
    }

    return 'file';
}

/**
 * เปิดดูในเว็บได้ไหม — รูปภาพกับ PDF เท่านั้น
 *
 * Excel/Word เปิดดูในเว็บไม่ได้ถ้าไม่มีตัวแปลงไฟล์ ส่วน HTML/SVG ไม่อยู่ใน
 * รายการอนุญาตอยู่แล้วเพราะรันสคริปต์ได้
 */
function cd_is_previewable(?string $ext): bool
{
    return in_array(cd_file_kind($ext), ['image', 'pdf'], true);
}

function cd_mime(?string $ext): string
{
    switch (strtolower((string) $ext)) {
        case 'pdf':
            return 'application/pdf';
        case 'png':
            return 'image/png';
        case 'jpg':
        case 'jpeg':
            return 'image/jpeg';
        case 'gif':
            return 'image/gif';
        case 'webp':
            return 'image/webp';
        case 'xlsx':
            return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        case 'xls':
            return 'application/vnd.ms-excel';
        case 'csv':
            return 'text/csv; charset=utf-8';
        case 'doc':
            return 'application/msword';
        case 'docx':
            return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        case 'txt':
            return 'text/plain; charset=utf-8';
        case 'zip':
            return 'application/zip';
        default:
            return 'application/octet-stream';
    }
}

/**
 * MIME ที่อ่านจาก "เนื้อไฟล์จริง" สอดคล้องกับนามสกุลไหม
 *
 * ห้ามเชื่อ $_FILES['type'] เพราะเบราว์เซอร์ส่งมาอะไรก็ได้ ผู้โจมตีตั้งเองได้ทั้งหมด
 * ตรวจด้วย finfo แทน แล้วเทียบกับรายการที่ยอมรับของนามสกุลนั้น
 *
 * ยอมรับ application/octet-stream และ application/zip สำหรับไฟล์ Office สมัยใหม่
 * เพราะ .xlsx/.docx คือ zip จริง ๆ finfo บางเวอร์ชันจึงตอบแบบนั้น
 */
function cd_mime_matches(string $path, string $ext): bool
{
    if (!function_exists('finfo_open')) {
        // ไม่มี fileinfo ก็ต้องทำงานต่อได้ แต่บันทึกไว้ให้รู้ว่าด่านนี้ไม่ทำงาน
        error_log('cd_mime_matches: ไม่มีส่วนขยาย fileinfo — ข้ามการตรวจ MIME');

        return true;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    if ($finfo === false) {
        return true;
    }

    $actual = (string) finfo_file($finfo, $path);
    finfo_close($finfo);

    $accept = [
        'pdf'  => ['application/pdf'],
        'png'  => ['image/png'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'gif'  => ['image/gif'],
        'webp' => ['image/webp'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
        'xls'  => ['application/vnd.ms-excel', 'application/msword', 'application/x-ole-storage', 'application/octet-stream'],
        'doc'  => ['application/msword', 'application/x-ole-storage', 'application/octet-stream'],
        'csv'  => ['text/csv', 'text/plain', 'application/csv', 'application/octet-stream'],
        'txt'  => ['text/plain', 'application/octet-stream'],
        'zip'  => ['application/zip', 'application/octet-stream'],
    ];

    $allowed = $accept[strtolower($ext)] ?? [];

    return $allowed === [] ? false : in_array($actual, $allowed, true);
}

// ============================================================
// เวอร์ชัน
// ============================================================

/**
 * บันทึกไฟล์ที่อัปโหลดเข้ามาเป็นเวอร์ชันใหม่ของ node ที่มีอยู่
 *
 * **ห้ามคำนวณเลขเวอร์ชันจากค่าที่อ่านมาก่อนหน้า** — ต้องล็อกแถว node ด้วย
 * FOR UPDATE ก่อนอ่านเลขปัจจุบันเสมอ สองคำขอที่มาพร้อมกันจะได้เลขเดียวกัน
 * แล้วเขียนทับไฟล์ของกันและกัน เวอร์ชันหายไปเงียบ ๆ
 * unique (node_id, version) ในตารางเป็นตาข่ายชั้นสุดท้าย
 *
 * @param  string  $source  path ของไฟล์ต้นทาง
 * @param  string  $via     staff_upload | guest_upload | restore
 * @return array{ok:bool,version:int,msn:string}
 */
function cd_commit_version(array $node, string $source, string $via, ?int $user_id, ?string $guest_label = null): array
{
    global $conn;

    if (!is_file($source)) {
        return ['ok' => false, 'version' => 0, 'msn' => 'ไม่พบไฟล์ต้นทาง'];
    }

    $customer_id = (int) $node['customer_id'];
    $node_id = (int) $node['node_id'];
    $size = (int) filesize($source);
    $sha = hash_file('sha256', $source) ?: null;

    $conn->beginTransaction();

    try {
        // ล็อกแถว node ไว้ก่อน คำขออื่นของ node เดียวกันจะเข้าคิวรอตรงนี้
        $locked = $conn->prepareAndExecute(
            "SELECT node_id, version, ext FROM tbl_cd_node
             WHERE customer_id = ? AND node_id = ? FOR UPDATE",
            [$customer_id, $node_id]
        )->fetch();

        if (!$locked) {
            throw new Exception('ไม่พบไฟล์ปลายทาง');
        }

        $next = (int) $locked['version'] + 1;

        $conn->prepareAndExecute(
            "INSERT INTO tbl_cd_version (node_id, version, size, sha256, via, create_by, guest_label)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$node_id, $next, $size, $sha, $via, $user_id, $guest_label]
        );

        // เขียนไฟล์ให้เสร็จก่อน commit — ถ้าเขียนไม่ผ่านจะได้ย้อนแถวคืนได้
        $node['ext'] = $locked['ext'];

        if (!cd_atomic_copy($source, cd_version_path($node, $next))) {
            throw new Exception('บันทึกไฟล์เวอร์ชันใหม่ไม่สำเร็จ');
        }

        if (!cd_atomic_copy($source, cd_current_path($node))) {
            throw new Exception('บันทึกไฟล์ล่าสุดไม่สำเร็จ');
        }

        $conn->prepareAndExecute(
            "UPDATE tbl_cd_node
             SET version = ?, size = ?, sha256 = ?, update_by = ?
             WHERE customer_id = ? AND node_id = ?",
            [$next, $size, $sha, $user_id, $customer_id, $node_id]
        );

        $conn->commit();

        return ['ok' => true, 'version' => $next, 'msn' => ''];
    } catch (Throwable $e) {
        $conn->rollback();

        return ['ok' => false, 'version' => 0, 'msn' => $e->getMessage()];
    }
}

/**
 * ประวัติเวอร์ชันของไฟล์ใบหนึ่ง ใหม่สุดขึ้นก่อน
 *
 * @return list<array<string,mixed>>
 */
function cd_versions(int $node_id): array
{
    global $conn;

    return $conn->prepareAndExecute(
        "SELECT * FROM tbl_cd_version WHERE node_id = ? ORDER BY version DESC",
        [$node_id]
    )->fetchAll();
}

// ============================================================
// พื้นที่ดิสก์
// ============================================================

/**
 * สถานะพื้นที่ดิสก์สำหรับแถบท้ายเมนู
 *
 * ⚠️ ค่านี้คือดิสก์ของ "เซิร์ฟเวอร์" ไม่ใช่โควตาของบัญชี hosting
 * บน shared hosting disk_free_space() มักรายงานพื้นที่ของดิสก์ทั้งลูกที่ใช้ร่วมกัน
 * แถบอาจขึ้นว่าเหลือเยอะทั้งที่บัญชีถูกจำกัดไว้น้อยกว่านั้นมาก
 * **ตัดสินใจแล้วว่ายอมรับข้อจำกัดนี้** (ดู CUSTOMER_DRIVE.md หัวข้อ 14)
 *
 * @return array{ok:bool,free:int,total:int,used:int,percent:int,warn:bool}
 */
function cd_disk_usage(): array
{
    $path = is_dir(cd_root()) ? cd_root() : dirname(cd_root());

    $free = @disk_free_space($path);
    $total = @disk_total_space($path);

    if (!is_float($free) || !is_float($total) || $total <= 0) {
        return ['ok' => false, 'free' => 0, 'total' => 0, 'used' => 0, 'percent' => 0, 'warn' => false];
    }

    $used = (int) ($total - $free);
    $percent = (int) round(($used / $total) * 100);

    return [
        'ok'      => true,
        'free'    => (int) $free,
        'total'   => (int) $total,
        'used'    => $used,
        'percent' => $percent,
        'warn'    => $percent >= set_int('cd_disk_warn_percent', 1, 99),
    ];
}

/**
 * ขนาดรวมของไฟล์ในคลังของลูกค้ารายหนึ่ง (รวมของในถังขยะ เพราะยังกินพื้นที่จริง)
 */
function cd_customer_usage(int $customer_id): int
{
    global $conn;

    $row = $conn->prepareAndExecute(
        "SELECT COALESCE(SUM(size), 0) AS s FROM tbl_cd_node WHERE customer_id = ? AND kind = 'file'",
        [$customer_id]
    )->fetch();

    return (int) ($row['s'] ?? 0);
}

// ============================================================
// ความเคลื่อนไหว
// ============================================================

/**
 * บันทึกความเคลื่อนไหวหนึ่งรายการ
 *
 * คลังนี้มีคุณค่าตรงที่ตอบได้ว่า "ลูกค้าส่งอะไรมาเมื่อไหร่" ถ้า Activity ไม่ครบ
 * ฟีเจอร์นี้ก็ไม่มีความหมาย จึงต้องเรียกทุกการกระทำโดยไม่มีข้อยกเว้น
 *
 * **เรียกหลังงานสำเร็จเสมอ** และการเขียนล้มเหลวต้องไม่ทำให้งานที่สำเร็จแล้ว
 * กลายเป็นล้มเหลว — จึงกลืน exception ทิ้งแล้วบันทึกลง error_log แทน
 *
 * @param  string  $action  ต้องอยู่ใน CD_ACTIONS
 */
function cd_activity(
    int $customer_id,
    string $action,
    ?int $node_id = null,
    ?string $detail = null,
    string $actor_type = 'staff',
    ?int $actor_user_id = null,
    ?string $actor_label = null,
    ?int $link_id = null
): void {
    global $conn;

    if (!in_array($action, CD_ACTIONS, true)) {
        error_log('cd_activity: action ไม่อยู่ในรายการที่อนุญาต — ' . $action);

        return;
    }

    try {
        $conn->prepareAndExecute(
            "INSERT INTO tbl_cd_activity
                (customer_id, node_id, link_id, actor_type, actor_user_id, actor_label, action, detail, ip)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $customer_id,
                $node_id,
                $link_id,
                $actor_type,
                $actor_user_id,
                $actor_label !== null ? mb_substr($actor_label, 0, 120, 'UTF-8') : null,
                $action,
                $detail !== null ? mb_substr($detail, 0, 500, 'UTF-8') : null,
                cd_client_ip(),
            ]
        );
    } catch (Throwable $e) {
        error_log('cd_activity: ' . $e->getMessage());
    }
}

/**
 * IP ของผู้เรียกในรูป binary (varbinary(16)) — คืน null เมื่อแปลงไม่ได้
 *
 * เก็บเป็น binary ไม่ใช่ข้อความ เพื่อให้รองรับ IPv6 และเทียบ/สร้าง index ได้ดีกว่า
 * ไม่อ่าน X-Forwarded-For เพราะปลอมได้ง่าย และตอนนี้ยังไม่มี proxy ที่ไว้ใจได้
 */
function cd_client_ip(): ?string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if ($ip === '') {
        return null;
    }

    $packed = @inet_pton($ip);

    return $packed === false ? null : $packed;
}

/** IP กลับเป็นข้อความสำหรับแสดงผล */
function cd_ip_text($packed): string
{
    if (!$packed) {
        return '';
    }

    $text = @inet_ntop($packed);

    return $text === false ? '' : $text;
}

/**
 * ความเคลื่อนไหวล่าสุดของลูกค้ารายหนึ่ง
 *
 * @return list<array<string,mixed>>
 */
// ============================================================
// ที่พักชิ้นส่วนของการอัปโหลดแบบแบ่งก้อน (เฟส 5)
// ============================================================

/**
 * อายุของชิ้นส่วนที่ยังประกอบไม่เสร็จ (วินาที)
 *
 * 6 ชั่วโมง — ยาวพอให้ลูกค้าที่เน็ตหลุดกลับมาต่อได้ในวันเดียวกัน
 * แต่ไม่ยาวจนของค้างสะสมเป็นสัปดาห์
 */
const CD_CHUNK_TTL = 21600;

/** โฟลเดอร์เก็บชิ้นส่วน — อยู่ใต้ที่เก็บไฟล์เดียวกัน จึงถูกด่านเดียวกันคุ้มครอง */
function cd_chunk_dir(): string
{
    $dir = cd_root() . '/_chunks';

    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    return $dir;
}

/**
 * เส้นทางไฟล์ชิ้นส่วนของ upload_id หนึ่ง
 *
 * $uploadId ถูกบังคับรูปแบบเป็น hex 64 ตัวมาแล้วจากผู้เรียก จึงไม่มีทาง
 * มี / หรือ .. ปนมาทำให้เขียนไฟล์นอกโฟลเดอร์ได้ — แต่ยังกรองซ้ำที่นี่
 * เพราะฟังก์ชันนี้อาจถูกเรียกจากที่อื่นในอนาคต
 */
function cd_chunk_path(string $uploadId): string
{
    if (!preg_match('/^[0-9a-f]{64}$/', $uploadId)) {
        // ไม่โยน exception เพราะผู้เรียกอยู่ในเส้นทางของแขก — คืนเส้นทางที่ใช้ไม่ได้แทน
        return cd_chunk_dir() . '/invalid';
    }

    return cd_chunk_dir() . '/' . $uploadId . '.part';
}

/**
 * ลบชิ้นส่วนที่ค้างเกินอายุ
 *
 * **ไม่พึ่ง cron** — ตัดสินใจไว้แล้วว่าระบบนี้ตั้ง cron ไม่ได้ (ดู CUSTOMER_DRIVE.md
 * หัวข้อ 11) จึงเก็บกวาดตอนมีคนเรียกใช้แทน ซึ่งเพียงพอเพราะของค้างเกิดขึ้น
 * เฉพาะเมื่อมีคนเริ่มอัปแล้วหายไป และคนถัดไปที่อัปคือคนที่มาล้างให้
 *
 * เรียกจาก action=status เท่านั้น ซึ่งเกิดครั้งเดียวต่อไฟล์ตอนเริ่ม —
 * ไม่เรียกทุกก้อน เพราะการ scandir ทุกก้อนคือการเสียเวลาซ้ำโดยไม่ได้อะไร
 */
function cd_chunk_sweep(): int
{
    $dir = cd_chunk_dir();
    $cut = time() - CD_CHUNK_TTL;
    $removed = 0;

    foreach (glob($dir . '/*.part') ?: [] as $f) {
        if (@filemtime($f) < $cut && @unlink($f)) {
            $removed++;
        }
    }

    return $removed;
}

/**
 * รับไฟล์หนึ่งใบจากแขกให้จบกระบวนการ — ตรวจ บันทึก เขียนดิสก์ แจ้งเตือน
 *
 * ------------------------------------------------------------
 * ทำไมต้องเป็นฟังก์ชันร่วม
 * ------------------------------------------------------------
 * มีสองทางที่ไฟล์ของแขกเข้าระบบได้
 *   1. portal/ajax/upload.php        ส่งทีเดียวจบ (ไฟล์เล็ก)
 *   2. portal/ajax/upload_chunk.php  ส่งทีละก้อนแล้วประกอบ (ไฟล์ใหญ่/เน็ตไม่ดี)
 *
 * ถ้าปล่อยให้แต่ละทางเขียนด่านตรวจของตัวเอง วันหนึ่งจะมีทางใดทางหนึ่ง
 * ลืมตรวจ MIME หรือลืมนับโควตา — ซึ่งเป็นวิธีที่ chunked upload กลายเป็นรูรั่ว
 * ในระบบอื่นมานักต่อนัก ทางที่สองต่างจากทางแรกแค่ "ไฟล์มาถึงยังไง" เท่านั้น
 * ทุกอย่างหลังจากนั้นต้องเหมือนกันเป๊ะ
 *
 * ⚠️ ฟังก์ชันนี้ **จบคำขอเสมอ** ด้วย cd_ok() หรือ cd_fail() ไม่คืนค่ากลับ
 *
 * @param string $tmp          ไฟล์บนดิสก์ที่พร้อมใช้ (ผ่าน is_uploaded_file แล้วถ้ามาจาก $_FILES)
 * @param string $originalName ชื่อที่ลูกค้าตั้ง ยังไม่ผ่าน cd_safe_name()
 */
function cd_guest_accept(array $link, array $session, string $tmp, string $originalName): void
{
    global $conn;

    $customer_id = (int) $link['customer_id'];
    $label = (string) ($session['guest_label'] ?? '');

    $size = (int) @filesize($tmp);
    $max  = cd_max_upload_bytes();

    if ($size <= 0) {
        cd_fail('ไฟล์ว่างเปล่า');
    }

    if ($size > $max) {
        cd_fail('ไฟล์ใหญ่เกิน ' . cd_format_bytes($max));
    }

    $original = cd_safe_name($originalName);
    $ext      = cd_ext_of($original);

    if ($original === '') {
        cd_fail('ชื่อไฟล์ไม่ถูกต้อง');
    }

    if ($ext === '' || !in_array($ext, cd_allowed_ext('guest'), true)) {
        cd_fail('ไฟล์ชนิดนี้ส่งไม่ได้ — รองรับเฉพาะ ' . implode(', ', cd_allowed_ext('guest')));
    }

    /*
     * ตรวจ MIME จากเนื้อไฟล์ที่ประกอบเสร็จแล้ว ไม่ใช่จากก้อนแรก
     *
     * ก้อนแรกของ .xlsx คือส่วนหัว zip ซึ่งดูเหมือน zip ธรรมดา การตรวจตอนยังไม่ครบ
     * จึงทั้งผิดพลาดง่ายและถูกหลอกง่าย — ต้องตรวจตอนไฟล์สมบูรณ์แล้วเท่านั้น
     */
    if (!cd_mime_matches($tmp, $ext)) {
        cd_fail('เนื้อไฟล์ไม่ตรงกับนามสกุล .' . $ext . ' — ไฟล์อาจเสียหาย');
    }

    // ------------------------------------------------------------ เพดานจำนวนไฟล์

    if ($link['max_uploads'] !== null && (int) $link['used_uploads'] >= (int) $link['max_uploads']) {
        cd_json([
            'result' => '4',
            'msn'    => 'ลิงก์นี้ส่งไฟล์ครบจำนวนที่กำหนดแล้ว กรุณาติดต่อผู้ตรวจสอบบัญชีของท่าน',
        ]);
    }

    $parentId = cd_guest_target($link);

    // ------------------------------------------------------------ ส่งซ้ำไฟล์เดิม

    $sha = hash_file('sha256', $tmp) ?: '';
    $existing = cd_find_by_name($customer_id, $parentId, $original);

    if ($existing && $sha !== '' && $sha === (string) $existing['sha256']) {
        // เนื้อไฟล์เหมือนเดิมเป๊ะ = กดส่งซ้ำหรือเน็ตหลุดแล้วส่งใหม่
        // สำหรับลูกค้ามันสำเร็จไปแล้วจริง ๆ จึงไม่ใช่ error และไม่นับเพิ่ม
        cd_ok(['msn' => 'ไฟล์นี้ได้รับแล้ว', 'skipped' => true]);
    }

    /*
     * ชื่อชนแต่เนื้อต่าง = คนละไฟล์จริง ๆ → เปลี่ยนชื่อเป็น (2)
     * ห้ามทับเป็นเวอร์ชันใหม่เด็ดขาด
     */
    $name = cd_unique_name($customer_id, $parentId, $original);

    $conn->beginTransaction();

    try {
        $conn->prepareAndExecute(
            "INSERT INTO tbl_cd_node
                (customer_id, parent_id, kind, name, ext, list_order,
                 source, source_link_id, guest_label, create_by)
             VALUES (?, ?, 'file', ?, ?, ?, 'guest', ?, ?, NULL)",
            [
                $customer_id,
                $parentId,
                $name,
                $ext,
                cd_next_sort($customer_id, $parentId),
                (int) $link['link_id'],
                $label !== '' ? $label : null,
            ]
        );

        $node_id = (int) $conn->get_insert_id();

        $conn->prepareAndExecute(
            "UPDATE tbl_cd_link SET used_uploads = used_uploads + 1 WHERE link_id = ?",
            [(int) $link['link_id']]
        );

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        error_log('cd_guest_accept insert: ' . $e->getMessage());
        cd_fail('บันทึกไฟล์ไม่สำเร็จ กรุณาลองใหม่');
    }

    $node   = cd_find_node($customer_id, $node_id);
    $result = cd_commit_version($node, $tmp, 'guest_upload', null, $label !== '' ? $label : null);

    if (!$result['ok']) {
        // เขียนไฟล์ไม่ผ่าน = ต้องเก็บกวาดแถวที่เพิ่งสร้าง ไม่งั้นเหลือรายการที่ไม่มีไฟล์จริง
        cd_forget_files($customer_id, $node_id);
        $conn->prepareAndExecute("DELETE FROM tbl_cd_node WHERE customer_id = ? AND node_id = ?", [$customer_id, $node_id]);
        $conn->prepareAndExecute("UPDATE tbl_cd_link SET used_uploads = GREATEST(used_uploads - 1, 0) WHERE link_id = ?", [(int) $link['link_id']]);

        error_log('cd_guest_accept commit: ' . $result['msn']);
        cd_fail('บันทึกไฟล์ไม่สำเร็จ กรุณาลองใหม่');
    }

    cd_activity($customer_id, 'upload', $node_id, $name, 'guest', null, $label, (int) $link['link_id']);

    /*
     * แจ้งพนักงานที่เปิดสวิตช์รับแจ้งเตือนไว้
     *
     * อยู่ **หลัง** cd_activity() และหลังไฟล์ลงดิสก์เรียบร้อยแล้วโดยตั้งใจ —
     * cd_notify() กลืน exception ไว้เองทุกกรณี ถ้ามันล้มเหลว ลูกค้าจะยังเห็นว่า
     * ส่งไฟล์สำเร็จ ซึ่งถูกต้อง เพราะไฟล์เข้าระบบแล้วจริง ๆ
     *
     * **จุดยึดของการนับคือใบแจ้งเตือนที่ยังไม่ถูกอ่าน ไม่ใช่กรอบเวลาตายตัว**
     * ถ้านับด้วยกรอบเวลา ไฟล์ที่แจ้งไปแล้วจะถูกนับซ้ำ ตัวเลขบานขึ้นเรื่อย ๆ
     */
    $recent = 1;

    try {
        $anchor = $conn->prepareAndExecute(
            "SELECT MIN(create_datetime) AS since FROM tbl_notification
             WHERE topic = ? AND read_datetime IS NULL
               AND create_datetime > DATE_SUB(NOW(), INTERVAL ? SECOND)",
            ['cdrive:' . $customer_id, NOTI_COLLAPSE_SECONDS]
        )->fetch();

        if (!empty($anchor['since'])) {
            $row = $conn->prepareAndExecute(
                "SELECT COUNT(*) AS n FROM tbl_cd_node
                 WHERE customer_id = ? AND source_link_id = ? AND delete_datetime IS NULL
                   AND create_datetime >= ?",
                [$customer_id, (int) $link['link_id'], $anchor['since']]
            )->fetch();

            $recent = max(1, (int) ($row['n'] ?? 1));
        }
    } catch (Throwable $e) {
        error_log('cd_guest_accept count: ' . $e->getMessage());
    }

    cd_notify(
        $customer_id,
        $recent > 1 ? ('ส่งไฟล์เข้ามา ' . $recent . ' ไฟล์') : 'ส่งไฟล์เข้ามาในคลัง',
        $recent > 1 ? ($name . ' และอีก ' . ($recent - 1) . ' ไฟล์') : $name,
        null
    );

    cd_ok([
        'msn'     => 'ส่งเรียบร้อยแล้ว',
        'node_id' => $node_id,
        'name'    => $name,
        'renamed' => $name !== $original,
    ]);
}

/**
 * แจ้งเตือนพนักงานว่าลูกค้าเคลื่อนไหวในคลังไฟล์
 *
 * ------------------------------------------------------------
 * กติกาที่ห้ามละเมิด
 * ------------------------------------------------------------
 * ฟังก์ชันนี้ **ต้องไม่ทำให้ผู้เรียกล้มเหลวไม่ว่ากรณีใด** — มันถูกเรียกท้าย
 * การอัปโหลดของลูกค้า ถ้ามันโยน exception ลูกค้าจะเห็นว่าส่งไฟล์ไม่สำเร็จ
 * ทั้งที่ไฟล์เข้าระบบเรียบร้อยแล้ว แล้วลูกค้าจะส่งซ้ำจนกว่าจะยอมแพ้
 *
 * `cd_activity()` เป็นคนละเรื่องกัน — มันต้องถูกเขียน **เสมอ** ไม่ว่าจะมี
 * ผู้รับแจ้งเตือนหรือไม่ เพราะเป็นบันทึกว่าเกิดอะไรขึ้น ไม่ใช่การบอกใคร
 *
 * ------------------------------------------------------------
 * ใครได้รับ
 * ------------------------------------------------------------
 *   active_status = '1'  และ  มีสิทธิ์ cdrive_view  และ  notify_cdrive = '1'
 *   ยกเว้นผู้ก่อเหตุเอง
 *
 * ไม่มีรายชื่อผู้รับที่ตั้งไว้ล่วงหน้า และไม่ผูกกับ head_user_id เพราะตรวจ
 * ข้อมูลจริงแล้วพบว่ามีลูกค้า 88 รายจาก 443 ที่ไม่มี head_user_id เลย
 * ถ้าใช้เกณฑ์นั้น ลูกค้ากลุ่มนั้นอัปไฟล์เข้ามาแล้วจะเงียบสนิทโดยไม่มีใครรู้
 *
 * ค่าปริยายของ notify_cdrive คือ '0' จึงเป็นสวิตช์ปิดทั้งระบบไปในตัว —
 * วันที่ระบบขึ้นจะไม่มีใครได้รับอะไรจนกว่าจะเปิดเอง
 *
 * ------------------------------------------------------------
 * การยุบข้อความ
 * ------------------------------------------------------------
 * topic = "cdrive:<customer_id>" ทำให้ลูกค้ารายเดียวกันที่อัปติด ๆ กัน
 * ภายใน 10 นาทีรวมเป็นใบเดียว — ไม่งั้นอัป 20 ไฟล์ = แจ้ง 20 ครั้ง
 * แล้วพนักงานจะปิดสวิตช์ทิ้ง ซึ่งแย่กว่าไม่มีระบบแจ้งเตือนเลย
 *
 * @param string      $what    สิ่งที่เกิดขึ้น เช่น "ส่งไฟล์เข้ามา" — จะถูกต่อท้ายชื่อลูกค้า
 * @param string|null $detail  รายละเอียดบรรทัดรอง เช่น ชื่อไฟล์
 * @return int จำนวนคนที่ได้รับ (0 = ยังไม่มีใครเปิดสวิตช์ ไม่ใช่ข้อผิดพลาด)
 */
function cd_notify(int $customer_id, string $what, ?string $detail = null, ?int $exceptUserId = null): int
{
    try {
        $recipients = noti_recipients('cdrive_view', 'notify_cdrive', $exceptUserId);

        // ไม่มีใครเปิดสวิตช์ = ไม่ต้องยิง query หาชื่อลูกค้าให้เปลือง
        if (!$recipients) {
            return 0;
        }

        /*
         * ประกอบหัวข้อที่นี่ ไม่ให้ผู้เรียกทำเอง
         *
         * ผู้เรียกหลักคือ portal/ajax/upload.php ซึ่งรู้แค่ link กับ customer_id
         * ถ้าให้มันไปดึงชื่อลูกค้าเอง ทุกจุดที่เรียกจะต้องเขียนโค้ดชุดเดียวกันซ้ำ
         * และมีโอกาสที่รูปแบบข้อความจะเพี้ยนกันไปทีละจุด
         */
        $customer = cd_customer($customer_id);
        $name = $customer ? (string) $customer['customer_name'] : ('ลูกค้า #' . $customer_id);

        return noti_add_many(
            $recipients,
            $name . ' ' . $what,
            $detail,
            'customer_drive.php?id=' . $customer_id,
            'cdrive:' . $customer_id
        );
    } catch (Throwable $e) {
        error_log('cd_notify: ' . $e->getMessage());

        return 0;
    }
}

function cd_activity_feed(int $customer_id, int $limit = 100): array
{
    global $conn;

    $limit = max(1, min(500, $limit));

    return $conn->prepareAndExecute(
        "SELECT * FROM tbl_cd_activity
         WHERE customer_id = ?
         ORDER BY create_datetime DESC, activity_id DESC
         LIMIT " . $limit,
        [$customer_id]
    )->fetchAll();
}

/** ข้อความไทยของ action หนึ่งตัว */
function cd_action_label(string $action): string
{
    $map = [
        'upload'         => 'อัปโหลดไฟล์',
        'upload_version' => 'อัปโหลดเวอร์ชันใหม่',
        'download'       => 'ดาวน์โหลด',
        'preview'        => 'เปิดดู',
        'create_folder'  => 'สร้างโฟลเดอร์',
        'rename'         => 'เปลี่ยนชื่อ',
        'move'           => 'ย้าย',
        'delete'         => 'ทิ้งลงถังขยะ',
        'restore'        => 'กู้คืน',
        'purge'          => 'ลบถาวร',
        'link_create'    => 'สร้างลิงก์แชร์',
        'link_open'      => 'เปิดลิงก์แชร์',
        'link_auth_fail' => 'กรอกรหัสผ่านผิด',
        'link_revoke'    => 'เพิกถอนลิงก์แชร์',
        'link_expire'    => 'ลิงก์แชร์หมดอายุ',
    ];

    return $map[$action] ?? $action;
}

// ============================================================
// ด่านมาตรฐานของ endpoint + การตอบกลับ
// ============================================================

function cd_json($payload): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/** ล้มเหลวพร้อมเหตุผลเป็นภาษาไทย — result เป็นสตริงเสมอ */
function cd_fail(string $msn): void
{
    cd_json(['result' => '0', 'msn' => $msn]);
}

/** ไม่มีสิทธิ์ — แยกรหัสออกจาก '0' เพื่อให้ JS แยกข้อความได้ */
function cd_deny(string $msn = 'คุณไม่มีสิทธิ์ทำรายการนี้'): void
{
    cd_json(['result' => '2', 'msn' => $msn]);
}

function cd_ok(array $extra = []): void
{
    cd_json(array_merge(['result' => '1', 'msn' => ''], $extra));
}

/**
 * ด่านที่ทุก endpoint ฝั่งพนักงานต้องผ่านก่อนทำอะไร
 *
 * คืนแถวลูกค้า หรือจบคำขอด้วย JSON error ไปเลย
 *
 * **ตรวจสิทธิ์จากฐานข้อมูลด้วย user_id เสมอ ห้ามเชื่อ user_status ที่ส่งมา**
 * เพราะระบบนี้ไม่มี PHP session ค่าที่มากับ POST เป็นค่าที่ผู้เรียกกำหนดเองได้ทั้งหมด
 *
 * ใครมีสิทธิ์ cdrive_view เข้าได้ทุกลูกค้า ไม่จำกัดรายบุคคล — สำนักงานมีคน 13 คน
 * การกั้นรายลูกค้าสร้างความยุ่งยากมากกว่าความปลอดภัยที่ได้ และทุกการเปิดดู/ดาวน์โหลด
 * ถูกบันทึกลง tbl_cd_activity อยู่แล้ว ซึ่งเป็นการควบคุมแบบตรวจสอบย้อนหลังได้
 */
function cd_guard(int $customer_id, int $user_id, ?string $permission = 'cdrive_view'): array
{
    if ($customer_id <= 0 || $user_id <= 0) {
        cd_fail('ข้อมูลไม่ครบ');
    }

    $customer = cd_customer($customer_id);

    if (!$customer) {
        cd_fail('ไม่พบข้อมูลลูกค้า');
    }

    // ต้องดูคลังได้ก่อนเสมอ แล้วค่อยตรวจสิทธิ์เฉพาะของคำสั่งนั้นซ้ำ
    if (!cd_can($user_id, 'cdrive_view')) {
        cd_deny();
    }

    if ($permission !== null && $permission !== 'cdrive_view' && !cd_can($user_id, $permission)) {
        cd_deny();
    }

    return $customer;
}

/** หา node หรือจบคำขอ — ใช้คู่กับ cd_guard() เสมอ */
function cd_guard_node(int $customer_id, int $node_id, bool $include_trashed = false): array
{
    $node = cd_find_node($customer_id, $node_id, $include_trashed);

    if (!$node) {
        cd_fail('ไม่พบรายการนี้ในคลังไฟล์');
    }

    return $node;
}

/**
 * บันทึกลง tbl_system_log — คนละชั้นกับ cd_activity()
 *
 * tbl_system_log คือ log ของระบบทั้งระบบ ส่วน tbl_cd_activity คือประวัติ
 * ที่ผู้ใช้อ่านรู้เรื่องในหน้าคลังไฟล์ การกระทำสำคัญบันทึกทั้งสองที่
 *
 * task_id เป็น null เสมอเพราะคลังไฟล์ไม่ผูกกับงานรายปี
 */
function cd_log(int $user_id, string $action): void
{
    global $conn;

    try {
        $conn->prepareAndExecute(
            "INSERT INTO tbl_system_log (user_id, task_id, action) VALUES (?, NULL, ?)",
            [$user_id, mb_substr($action, 0, 250, 'UTF-8')]
        );
    } catch (Throwable $e) {
        // การบันทึก log ล้มเหลวต้องไม่ทำให้คำสั่งที่ทำสำเร็จแล้วกลายเป็นล้มเหลว
        error_log('cd_log: ' . $e->getMessage());
    }
}

// ============================================================
// ลิงก์แชร์และรอบเข้าใช้ของแขก (เฟส 2)
// ============================================================

/*
 * ทุกอย่างใต้หัวข้อนี้คือ "เส้นทางของคนนอก" ซึ่งเป็นทางเข้าเดียวในระบบทั้งหมด
 * ที่ไม่ต้องล็อกอิน กติกาจึงเข้มกว่าฝั่งพนักงานทุกข้อ และห้ามลอกโค้ดฝั่งพนักงาน
 * มาใช้ตรง ๆ แม้จะดูคล้ายกันมาก
 *
 * หลักที่ยึด (docs/CUSTOMER_DRIVE.md หัวข้อ 8)
 *   1. token อย่างเดียวต้องไม่พอเปิดไฟล์ — ต้องผ่านรหัสผ่านเสมอ
 *   2. ข้อความผิดพลาดเหมือนกันทุกกรณี ห้ามบอกว่าลิงก์ไม่มี รหัสผิด หรือหมดอายุ
 *   3. ทุก query กรองด้วย "ขอบเขตของลิงก์" ไม่ใช่แค่ customer_id
 *   4. fail closed เสมอ — ไม่แน่ใจเมื่อไหร่ให้ปฏิเสธ
 */

/** อายุรอบเข้าใช้นับจากการใช้งานครั้งล่าสุด (วินาที) */
const CD_GUEST_TTL = 7200;          // 2 ชั่วโมง

/** เพดานอายุรอบเข้าใช้นับจากตอนกรอกรหัสผ่าน ไม่ว่าจะใช้ต่อเนื่องแค่ไหน */
const CD_GUEST_MAX_AGE = 28800;     // 8 ชั่วโมง

/** กรอกรหัสผิดกี่ครั้งแล้วล็อก และล็อกนานเท่าไหร่ */
const CD_LINK_FAIL_LOCK = 5;
const CD_LINK_LOCK_MINUTES = 15;

/** ผิดสะสมถึงเท่าไหร่แล้วขึ้นธงแดงให้พนักงานเห็น (ไม่เพิกถอนอัตโนมัติ) */
const CD_LINK_FAIL_FLAG = 20;

/** ความยาวรหัสผ่านขั้นต่ำที่ยอมรับ */
/**
 * ความยาวรหัสผ่านของลิงก์ — **ยาวเท่านี้พอดี ไม่ใช่อย่างน้อย**
 *
 * เดิมเป็นค่าต่ำสุด แต่หน้ากรอกของแขกเปลี่ยนเป็นช่องแยกตัวละกล่องแบบรหัส OTP
 * ซึ่งต้องรู้จำนวนช่องล่วงหน้า และจะบอกความยาวรหัสให้แขกรู้ก่อนกรอกไม่ได้
 * (การบอกความยาวคือการช่วยคนที่กำลังเดารหัส) ความยาวจึงต้องคงที่ทั้งระบบ
 *
 * ความแข็งแรงไม่ได้ลดลง เพราะชุดอักขระมี 56 ตัว → 56^6 ≈ 3 หมื่นล้านแบบ
 * และด่านจริงคือการล็อก 15 นาทีหลังผิด 5 ครั้ง ซึ่งจำกัดการเดาไว้ที่
 * ~480 ครั้งต่อวันอยู่แล้ว ไม่ใช่ความยาวรหัส
 */
const CD_LINK_PASSWORD_LEN = 6;

/** ชื่อเดิม เก็บไว้ไม่ให้โค้ดที่อ้างอยู่พัง — ความหมายคือ "ยาวเท่านี้พอดี" แล้ว */
const CD_LINK_PASSWORD_MIN = CD_LINK_PASSWORD_LEN;

/** ชื่อ cookie ของรอบเข้าใช้ — ตั้งให้ไม่ซ้ำกับอะไรในระบบ */
const CD_GUEST_COOKIE = 'cd_guest';

/**
 * ข้อความเดียวที่แขกได้เห็นเมื่อเข้าไม่ได้ ไม่ว่าจะด้วยเหตุผลอะไร
 *
 * ห้ามแยกว่า "ไม่พบลิงก์" กับ "รหัสผิด" กับ "หมดอายุ" เด็ดขาด —
 * การแยกบอกเท่ากับยืนยันให้คนที่กำลังลองสุ่ม token ว่าอันไหนมีอยู่จริง
 * และห้ามเผยชื่อลูกค้าในทุกกรณี
 */
const CD_GUEST_DENY = 'ลิงก์นี้ใช้ไม่ได้แล้ว กรุณาติดต่อผู้ตรวจสอบบัญชีของท่าน';

/**
 * URL เต็มของหน้าแขกสำหรับ token หนึ่ง
 *
 * ประกอบเส้นทางเองแทนการใช้ getBaseUrl() ของโปรเจกต์ เพราะตัวนั้นคืนแค่
 * scheme://host ซึ่งจะผิดทันทีถ้าวันหนึ่งระบบถูกวางในโฟลเดอร์ย่อย
 * (เช่น /audit/) — ตัด /main/... ออกจาก SCRIPT_NAME จึงได้รากของแอปจริง ๆ
 *
 * ผลลัพธ์คือลิงก์ที่พนักงานคัดลอกไปส่งลูกค้า ผิดแล้วลูกค้าเปิดไม่ได้เลย
 */
function cd_portal_url(string $token): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // SCRIPT_NAME เป็น path ของ HTTP เสมอ จึงใช้ / อยู่แล้วไม่ต้องแปลง
    $script = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $pos = strpos($script, '/main/');
    $base = $pos === false ? '' : substr($script, 0, $pos);

    return ($https ? 'https' : 'http') . '://' . $host . $base . '/portal/?t=' . $token;
}

/**
 * สุ่ม token สำหรับ URL — 32 ไบต์ เข้ารหัส base64url ได้ 43 ตัวอักษร
 *
 * ห้ามใช้ rand() / uniqid() / randomCode() ที่มีอยู่ในโปรเจกต์
 * (randomCode() ใช้ rand() ซึ่งเดาได้ ใช้กับสิ่งที่กันคนนอกไม่ได้เด็ดขาด)
 */
function cd_token(): string
{
    return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
}

/**
 * รหัสผ่านสุ่มที่อ่านออกเสียงและบอกต่อทางโทรศัพท์ได้
 *
 * ตัดตัวที่สับสนออก (0 O o 1 l I) เพราะรหัสนี้ถูกอ่านให้ลูกค้าฟังทางโทรศัพท์
 * หรือพิมพ์ต่อใน LINE — ตัวที่แยกไม่ออกทำให้ลูกค้ากรอกผิดแล้วโดนล็อกฟรี ๆ
 */
function cd_random_password(int $length = 6): string
{
    $pool = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $max = strlen($pool) - 1;
    $out = '';

    for ($i = 0; $i < $length; $i++) {
        $out .= $pool[random_int(0, $max)];
    }

    return $out;
}

/**
 * ลิงก์ใช้งานได้อยู่ไหม — คืนรหัสเหตุผลไว้ให้ "ฝั่งพนักงาน" อ่าน
 *
 * ⚠️ ห้ามเอาค่าที่คืนจากฟังก์ชันนี้ไปแสดงให้แขกเห็น แขกเห็นได้แค่ CD_GUEST_DENY
 * ค่านี้มีไว้ให้หน้าจัดการลิงก์ของพนักงานบอกสถานะเท่านั้น
 *
 * @return string '' = ใช้ได้ · revoked | expired | locked | scope_gone
 */
function cd_link_state(array $link): string
{
    if ((string) $link['revoked'] === '1') {
        return 'revoked';
    }

    if ($link['expires_datetime'] !== null && strtotime((string) $link['expires_datetime']) < time()) {
        return 'expired';
    }

    if ($link['locked_until'] !== null && strtotime((string) $link['locked_until']) > time()) {
        return 'locked';
    }

    /*
     * ขอบเขตของลิงก์ถูกทิ้งลงถังขยะ = ลิงก์ต้องใช้ไม่ได้ทันที
     *
     * FOREIGN KEY แบบ RESTRICT กันได้แค่ตอน "ลบถาวร" เพราะการทิ้งลงถังขยะ
     * เป็นแค่ UPDATE ฐานข้อมูลจึงไม่รู้เรื่อง ถ้าไม่ตรวจตรงนี้ ลิงก์ที่เคยจำกัด
     * ขอบเขตไว้จะกลายเป็นเห็นทั้งคลังทันทีที่มีคนลบโฟลเดอร์นั้น — fail closed
     */
    if ($link['root_node_id'] !== null) {
        $root = cd_find_node((int) $link['customer_id'], (int) $link['root_node_id']);

        if (!$root) {
            return 'scope_gone';
        }
    }

    return '';
}

/** หาลิงก์จาก token — คืน null เมื่อไม่มี ไม่บอกว่าเพราะอะไร */
function cd_link_by_token(string $token): ?array
{
    global $conn;

    if (strlen($token) !== 43 || !preg_match('/^[A-Za-z0-9_-]+$/', $token)) {
        return null;
    }

    $row = $conn->prepareAndExecute(
        "SELECT * FROM tbl_cd_link WHERE token = ?",
        [$token]
    )->fetch();

    return $row ?: null;
}

function cd_link_by_id(int $customer_id, int $link_id): ?array
{
    global $conn;

    $row = $conn->prepareAndExecute(
        "SELECT * FROM tbl_cd_link WHERE customer_id = ? AND link_id = ?",
        [$customer_id, $link_id]
    )->fetch();

    return $row ?: null;
}

/**
 * ตั้ง cookie ของรอบเข้าใช้
 *
 * `Secure` ตั้งอัตโนมัติตามว่าตอนนี้อยู่บน HTTPS ไหม — ไม่ใช่ค่าคงที่
 * เพราะถ้าบังคับ Secure ตายตัว เบราว์เซอร์จะทิ้ง cookie ทันทีบนเครื่องพัฒนา
 * ที่รันบน http แล้วทดสอบเฟสนี้ไม่ได้เลย ส่วนบน production ที่เป็น https
 * มันจะเปิดเองโดยไม่มี flag ให้ใครลืมตั้ง
 *
 * Path=/portal ทำให้ cookie นี้ไม่ถูกส่งไปยังหน้าของพนักงานเลย
 */
function cd_guest_cookie(string $value, int $lifetime): void
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    setcookie(CD_GUEST_COOKIE, $value, [
        'expires'  => $lifetime > 0 ? time() + $lifetime : 1,
        'path'     => '/portal',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * สร้างรอบเข้าใช้ใหม่หลังกรอกรหัสผ่านถูก
 *
 * @return string session_token ที่จะเอาไปใส่ cookie
 */
function cd_guest_start(array $link, string $label): string
{
    global $conn;

    $token = cd_token();

    $conn->prepareAndExecute(
        "INSERT INTO tbl_cd_link_session
            (link_id, session_token, guest_label, ip, user_agent, expires_datetime)
         VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? SECOND))",
        [
            (int) $link['link_id'],
            $token,
            mb_substr($label, 0, 120, 'UTF-8'),
            cd_client_ip(),
            mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255, 'UTF-8'),
            CD_GUEST_TTL,
        ]
    );

    cd_guest_cookie($token, CD_GUEST_TTL);

    return $token;
}

/**
 * ด่านที่ทุก endpoint ฝั่งแขกต้องผ่าน — คืน [link, session] หรือจบคำขอไปเลย
 *
 * ตรวจสามชั้นตามลำดับ: มี session ที่ยังไม่หมดอายุ → ลิงก์ยังใช้ได้ → ต่ออายุ session
 *
 * **รอบเข้าใช้ผูกกับ link_id เท่านั้น** ไม่มีทางข้ามไปลิงก์อื่นหรือลูกค้ารายอื่น
 * เพราะทุกอย่างที่ทำต่อจากนี้อ่านค่ามาจาก $link ที่ join กลับมาจาก session
 * ไม่ใช่จากค่าที่ผู้เรียกส่งมา
 *
 * @return array{0: array, 1: array} [ลิงก์, รอบเข้าใช้]
 */
function cd_guest_guard(): array
{
    global $conn;

    $token = (string) ($_COOKIE[CD_GUEST_COOKIE] ?? '');

    if ($token === '') {
        cd_guest_stop();
    }

    $row = $conn->prepareAndExecute(
        "SELECT s.*, l.link_id AS l_link_id
         FROM tbl_cd_link_session s
         JOIN tbl_cd_link l ON l.link_id = s.link_id
         WHERE s.session_token = ?",
        [$token]
    )->fetch();

    if (!$row) {
        cd_guest_stop();
    }

    // หมดอายุแบบ sliding หรือชนเพดานอายุรวมนับจากตอนกรอกรหัส
    $expires = strtotime((string) $row['expires_datetime']);
    $created = strtotime((string) $row['create_datetime']);

    if ($expires < time() || ($created + CD_GUEST_MAX_AGE) < time()) {
        cd_guest_stop();
    }

    $link = cd_link_by_id_any((int) $row['link_id']);

    if (!$link || cd_link_state($link) !== '') {
        cd_guest_stop();
    }

    /*
     * ต่ออายุแบบ sliding — ลูกค้าที่สแกนเอกสารไปอัปไปใช้เวลาเกินสองชั่วโมงได้
     * แต่ยังชนเพดาน 8 ชั่วโมงอยู่ดี จึงไม่กลายเป็นรอบเข้าใช้ที่ไม่มีวันหมดอายุ
     */
    $conn->prepareAndExecute(
        "UPDATE tbl_cd_link_session
         SET expires_datetime = DATE_ADD(NOW(), INTERVAL ? SECOND)
         WHERE session_id = ?",
        [CD_GUEST_TTL, (int) $row['session_id']]
    );

    cd_guest_cookie($token, CD_GUEST_TTL);

    return [$link, $row];
}

/** หาลิงก์ด้วย link_id อย่างเดียว — ใช้เฉพาะใน cd_guest_guard() ที่ id มาจาก session แล้ว */
function cd_link_by_id_any(int $link_id): ?array
{
    global $conn;

    $row = $conn->prepareAndExecute("SELECT * FROM tbl_cd_link WHERE link_id = ?", [$link_id])->fetch();

    return $row ?: null;
}

/** จบคำขอฝั่งแขกด้วยข้อความกลาง ๆ พร้อมล้าง cookie ทิ้ง */
function cd_guest_stop(): void
{
    cd_guest_cookie('', 0);
    cd_json(['result' => '0', 'msn' => CD_GUEST_DENY, 'expired' => true]);
}

/**
 * โฟลเดอร์ปลายทางที่แขกอัปไฟล์เข้าได้ — คืน node_id หรือ null (ชั้นบนสุดของคลัง)
 *
 * ยึดจาก root_node_id ของลิงก์เท่านั้น **ห้ามรับค่าจากผู้เรียก**
 * ถ้ารับได้เมื่อไหร่ แขกจะยัด node_id ของโฟลเดอร์อื่นเข้ามาแล้ววางไฟล์ผิดที่
 */
function cd_guest_target(array $link): ?int
{
    return $link['root_node_id'] === null ? null : (int) $link['root_node_id'];
}

/**
 * รายการที่แขกมีสิทธิ์เห็น ตามโหมดของลิงก์
 *
 *   collect — เห็นเฉพาะไฟล์ที่ตัวเองอัปในรอบนี้ ไม่เห็นของเดิมในโฟลเดอร์เลย
 *   share   — เห็นทุกอย่างใต้ root_node_id
 *
 * ทำไม collect ถึงไม่ให้เห็นของเดิม: โฟลเดอร์ปลายทางอาจมีเอกสารที่พนักงาน
 * เตรียมไว้ หรือของที่ลูกค้ารายเดียวกันส่งมารอบก่อน การเปิดให้เห็นทั้งหมด
 * เปลี่ยน "กล่องรับเอกสาร" ให้กลายเป็น "การแชร์โฟลเดอร์" โดยที่คนสร้างลิงก์ไม่ได้ตั้งใจ
 *
 * @return list<array<string,mixed>>
 */
function cd_guest_items(array $link, array $session): array
{
    global $conn;

    $customer_id = (int) $link['customer_id'];

    if ($link['mode'] === 'collect') {
        /*
         * ขอบเขตของแขกคือ "ลิงก์" ไม่ใช่ "คน"
         *
         * เดิมแยกด้วย guest_label ที่แขกกรอกชื่อตัวเองเข้ามา แต่หน้ากรอกตัดช่องชื่อ
         * ออกแล้ว (ตัดสินใจแล้ว: ลูกค้าควรกรอกแค่รหัส) จึงไม่มีอะไรแยกคนได้อีก
         *
         * ผลที่ตามมาและยอมรับแล้ว: ถ้าส่งลิงก์เดียวกันให้หลายคนในบริษัทเดียวกัน
         * ทุกคนจะเห็นและลบไฟล์ของกันได้ — ถ้าต้องแยก ให้สร้างลิงก์คนละใบ
         * ซึ่งเป็นวิธีที่ถูกต้องกว่าอยู่แล้ว เพราะเพิกถอนทีละคนได้ด้วย
         */
        return $conn->prepareAndExecute(
            "SELECT * FROM tbl_cd_node
             WHERE customer_id = ? AND delete_datetime IS NULL
               AND source = 'guest' AND source_link_id = ?
             ORDER BY create_datetime DESC, node_id DESC",
            [$customer_id, (int) $link['link_id']]
        )->fetchAll();
    }

    $root = cd_guest_target($link);
    $ids = $root === null ? null : cd_descendant_ids($customer_id, $root);

    if ($ids !== null && !$ids) {
        return [];
    }

    if ($ids === null) {
        return $conn->prepareAndExecute(
            "SELECT * FROM tbl_cd_node
             WHERE customer_id = ? AND delete_datetime IS NULL
             ORDER BY kind DESC, name",
            [$customer_id]
        )->fetchAll();
    }

    $place = implode(',', array_fill(0, count($ids), '?'));

    return $conn->prepareAndExecute(
        "SELECT * FROM tbl_cd_node
         WHERE customer_id = ? AND delete_datetime IS NULL AND node_id IN ($place)
         ORDER BY kind DESC, name",
        array_merge([$customer_id], $ids)
    )->fetchAll();
}

/** แขกเห็น node ใบนี้ได้ไหม — ใช้ก่อนดาวน์โหลด/ลบทุกครั้ง */
function cd_guest_can_see(array $link, array $session, int $node_id): ?array
{
    foreach (cd_guest_items($link, $session) as $item) {
        if ((int) $item['node_id'] === $node_id) {
            return $item;
        }
    }

    return null;
}
