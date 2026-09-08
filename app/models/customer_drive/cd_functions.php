<?php
// app/models/customer_drive/cd_functions.php
//
// Customer Drive (phase 1: staff drive + phase 2: share-link guest portal)
// — ported from _export_customer_drive/files/config/customer_drive.php.
//
// Differences from the source, on purpose:
//   - DB access goes through cd_query()/cd_pdo() (this project's real PDO
//     singleton) instead of a custom $conn wrapper.
//   - No granular permission system (cd_can()/hasPermission() removed) —
//     any authenticated user can do everything EXCEPT manage share links
//     (create/revoke/reset password), which requires is_super_admin —
//     see cd_guard()'s $requireSuperAdmin param. Callers must still call
//     checkAuth() before requiring this file.
//   - No settings table, no notifications.

require_once __DIR__ . '/cd_db.php';

// ============================================================
// ค่าคงที่
// ============================================================

const CD_STAFF_EXT = ['pdf', 'png', 'jpg', 'jpeg', 'xlsx', 'xls', 'doc', 'docx', 'csv', 'txt', 'zip', 'gif', 'webp'];
const CD_GUEST_EXT = ['pdf', 'png', 'jpg', 'jpeg', 'xlsx', 'xls', 'doc', 'docx'];
const CD_IMAGE_EXT = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
const CD_DEFAULT_MAX_UPLOAD_MB = 40;
const CD_MAX_NAME_LEN = 200;

const CD_ACTIONS = [
    'upload', 'upload_version', 'download', 'preview',
    'create_folder', 'rename', 'move', 'delete', 'restore', 'purge',
    'link_create', 'link_open', 'link_auth_fail', 'link_revoke', 'link_expire',
];

if (!defined('CD_FILES_PATH')) {
    define('CD_FILES_PATH', dirname(__DIR__, 3) . '/storage/customer-files');
}

const CD_STORAGE_URI = '/storage/customer-files';

// ============================================================
// ลิงก์แชร์และรอบเข้าใช้ของแขก (เฟส 2)
// ============================================================

const CD_GUEST_TTL = 7200;          // อายุรอบเข้าใช้แบบ sliding นับจากใช้งานล่าสุด (2 ชม.)
const CD_GUEST_MAX_AGE = 28800;     // เพดานอายุรอบเข้าใช้นับจากตอนกรอกรหัส ไม่ว่าจะใช้ต่อเนื่องแค่ไหน (8 ชม.)
const CD_LINK_FAIL_LOCK = 5;        // กรอกรหัสผิดกี่ครั้งแล้วล็อก
const CD_LINK_LOCK_MINUTES = 15;    // ล็อกนานเท่าไหร่
const CD_LINK_FAIL_FLAG = 20;       // ผิดสะสมถึงเท่าไหร่แล้วขึ้นธงแดงให้พนักงานเห็น (ไม่เพิกถอนอัตโนมัติ)
const CD_LINK_PASSWORD_LEN = 6;     // ยาวเท่านี้พอดี ไม่ใช่อย่างน้อย — หน้ากรอกของแขกเป็นช่องแยกตัวละกล่อง
const CD_GUEST_COOKIE = 'cd_guest';
const CD_GUEST_DENY = 'ลิงก์นี้ใช้ไม่ได้แล้ว กรุณาติดต่อผู้ตรวจสอบบัญชีของท่าน';

// ไม่มีตาราง settings ในโปรเจกต์นี้ — ค่าเริ่มต้นตอนสร้างลิงก์ตั้งตายตัวไว้ตรงนี้แทน
const CD_LINK_DEFAULT_DAYS = 30;
const CD_LINK_DEFAULT_MAX_UPLOADS = 100;

// ============================================================
// พื้นฐาน
// ============================================================

function cd_now(): string
{
    return date('Y-m-d H:i:s');
}

function cd_e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function cd_time(?string $datetime): string
{
    if (!$datetime) {
        return '';
    }

    $ts = strtotime($datetime);

    return $ts ? date('d/m/Y H:i', $ts) : '';
}

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
 * คืนชื่อคลาส Remix Icon (ไม่ใช่ Material Symbols) — โปรเจกต์นี้ใช้ Remix Icon
 * (self-hosted, ไม่พึ่งเน็ตนอก) เป็นชุดไอคอนหลักอยู่แล้วทั้งแอป
 */
function cd_icon_for(string $kind): string
{
    return match ($kind) {
        'folder'  => 'ri-folder-fill',
        'image'   => 'ri-image-line',
        'pdf'     => 'ri-file-pdf-line',
        'sheet'   => 'ri-file-excel-2-line',
        'doc'     => 'ri-file-word-line',
        'archive' => 'ri-file-zip-line',
        'text'    => 'ri-file-text-line',
        default   => 'ri-file-line',
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

function cd_ini_bytes(string $value): int
{
    $value = trim($value);

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

/** No settings table in this project — always uses CD_DEFAULT_MAX_UPLOAD_MB. */
function cd_max_upload_bytes(): int
{
    $policy = CD_DEFAULT_MAX_UPLOAD_MB * 1024 * 1024;

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

function cd_ext_of(string $filename): string
{
    $ext = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));

    return preg_match('/^[a-z0-9]{1,10}$/', $ext) ? $ext : '';
}

function cd_safe_name(string $name): string
{
    if (!mb_check_encoding($name, 'UTF-8')) {
        return '';
    }

    $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
    $name = preg_replace('/[\\\\\/:?<>|"*]/u', '_', $name) ?? '';
    $name = trim($name);

    if ($name === '.' || $name === '..') {
        return '';
    }

    return mb_substr($name, 0, CD_MAX_NAME_LEN, 'UTF-8');
}

// ============================================================
// ผู้ใช้
// ============================================================

/** ไม่มี tbl_role ในโปรเจกต์นี้ — เอาแค่ชื่อผู้ใช้ที่ยัง active */
function cd_users(): array
{
    static $cache = null;

    if ($cache !== null) {
        return $cache;
    }

    $sql = "SELECT user_id, user_firstname, user_lastname
            FROM tbl_user
            WHERE user_status = '1'
            ORDER BY user_firstname";

    $cache = [];

    foreach (cd_query($sql)->fetchAll() as $row) {
        $name = trim($row['user_firstname'] . ' ' . $row['user_lastname']);

        $cache[(int) $row['user_id']] = [
            'user_id' => (int) $row['user_id'],
            'name'    => $name !== '' ? $name : ('#' . $row['user_id']),
            'initial' => mb_strtoupper(mb_substr($name !== '' ? $name : '?', 0, 1, 'UTF-8'), 'UTF-8'),
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

// ============================================================
// ลูกค้า
// ============================================================

/** ปรับ SQL ให้ตรงกับ tbl_customers จริงของโปรเจกต์นี้ (plural, delete_at) */
function cd_customer(int $customer_id): ?array
{
    if ($customer_id <= 0) {
        return null;
    }

    $row = cd_query(
        "SELECT customer_id, customer_name, active_status
         FROM tbl_customers
         WHERE customer_id = ? AND delete_at IS NULL",
        [$customer_id]
    )->fetch();

    return $row ?: null;
}

// ============================================================
// ทรี
// ============================================================

function cd_all_nodes(int $customer_id): array
{
    return cd_query(
        "SELECT * FROM tbl_cd_node
         WHERE customer_id = ? AND delete_datetime IS NULL
         ORDER BY list_order, name",
        [$customer_id]
    )->fetchAll();
}

function cd_node_cmp(array $a, array $b): int
{
    $order = ((int) $a['list_order']) <=> ((int) $b['list_order']);

    if ($order !== 0) {
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

function cd_flatten(int $customer_id): array
{
    $byParent = [];

    foreach (cd_all_nodes($customer_id) as $node) {
        $byParent[(int) ($node['parent_id'] ?? 0)][] = $node;
    }

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

function cd_find_node(int $customer_id, int $node_id, bool $include_trashed = false): ?array
{
    if ($customer_id <= 0 || $node_id <= 0) {
        return null;
    }

    $sql = "SELECT * FROM tbl_cd_node WHERE customer_id = ? AND node_id = ?";

    if (!$include_trashed) {
        $sql .= " AND delete_datetime IS NULL";
    }

    $row = cd_query($sql, [$customer_id, $node_id])->fetch();

    return $row ?: null;
}

function cd_descendant_ids(int $customer_id, int $node_id, bool $include_trashed = false): array
{
    $sql = "SELECT node_id, parent_id FROM tbl_cd_node WHERE customer_id = ?";

    if (!$include_trashed) {
        $sql .= " AND delete_datetime IS NULL";
    }

    $byParent = [];

    foreach (cd_query($sql, [$customer_id])->fetchAll() as $row) {
        $byParent[(int) ($row['parent_id'] ?? 0)][] = (int) $row['node_id'];
    }

    $out = [];
    $queue = [$node_id];

    while ($queue) {
        $current = array_shift($queue);

        foreach ($byParent[$current] ?? [] as $child) {
            if (in_array($child, $out, true)) {
                continue;
            }

            $out[] = $child;
            $queue[] = $child;
        }
    }

    return $out;
}

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

function cd_folder_at(int $customer_id, ?int $node_id): ?array
{
    if (!$node_id) {
        return null;
    }

    $node = cd_find_node($customer_id, $node_id);

    return ($node && $node['kind'] === 'folder') ? $node : null;
}

function cd_next_sort(int $customer_id, ?int $parent_id): int
{
    $sql = "SELECT MAX(list_order) AS m FROM tbl_cd_node
            WHERE customer_id = ? AND delete_datetime IS NULL AND parent_id ";

    if ($parent_id === null) {
        $row = cd_query($sql . "IS NULL", [$customer_id])->fetch();
    } else {
        $row = cd_query($sql . "= ?", [$customer_id, $parent_id])->fetch();
    }

    $max = (int) ($row['m'] ?? 0);

    return $max > 0 ? $max + 10 : 0;
}

function cd_find_by_name(int $customer_id, ?int $parent_id, string $name, ?int $except_id = null): ?array
{
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

    $row = cd_query($sql . " LIMIT 1", $params)->fetch();

    return $row ?: null;
}

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

    return $base . ' (' . date('YmdHis') . ')' . $tail;
}

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

function cd_trash_items(int $customer_id): array
{
    $trashed = cd_query(
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
    $row = cd_query(
        "SELECT COUNT(*) AS n FROM tbl_cd_node WHERE customer_id = ? AND delete_datetime IS NOT NULL",
        [$customer_id]
    )->fetch();

    return (int) ($row['n'] ?? 0);
}

function cd_find_trashed(int $customer_id, int $node_id): ?array
{
    $row = cd_query(
        "SELECT * FROM tbl_cd_node
         WHERE customer_id = ? AND node_id = ? AND delete_datetime IS NOT NULL",
        [$customer_id, $node_id]
    )->fetch();

    return $row ?: null;
}

/**
 * ลบถาวร — ลบเฉพาะ "แถว" ในฐานข้อมูล ยังไม่แตะไฟล์บนดิสก์
 *
 * ปลด root_node_id ของลิงก์ที่ "เพิกถอนไปแล้ว" ออกก่อน เพราะ fk_cd_link_root
 * เป็น RESTRICT ซึ่งไม่สนใจว่าลิงก์ยังใช้ได้หรือไม่ — ถ้าไม่ปลดตรงนี้ ลิงก์ที่ตาย
 * ไปแล้วจะขวางการลบโฟลเดอร์นั้นไปตลอดกาล ลิงก์ที่ยังใช้ได้ถูกดักไว้ตั้งแต่
 * cd_links_pointing_at() ก่อนเรียกฟังก์ชันนี้แล้ว
 */
function cd_purge_node(int $customer_id, int $node_id): array
{
    $ids = cd_descendant_ids($customer_id, $node_id, true);
    $ids[] = $node_id;
    $ids = array_values(array_unique($ids));

    $place = implode(',', array_fill(0, count($ids), '?'));

    cd_query(
        "UPDATE tbl_cd_link SET root_node_id = NULL
         WHERE customer_id = ? AND revoked = '1' AND root_node_id IN ($place)",
        array_merge([$customer_id], $ids)
    );

    foreach (array_reverse($ids) as $id) {
        cd_query(
            "DELETE FROM tbl_cd_node WHERE customer_id = ? AND node_id = ?",
            [$customer_id, $id]
        );
    }

    return $ids;
}

/**
 * ลิงก์แชร์ที่ยังไม่ถูกเพิกถอนและชี้มาที่ node เหล่านี้
 *
 * ใช้เตือนก่อนลบถาวร เพราะ fk_cd_link_root เป็น ON DELETE RESTRICT โดยตั้งใจ
 *
 * @param int[] $ids
 * @return string[] ชื่อเรื่องของลิงก์ที่ขวางอยู่
 */
function cd_links_pointing_at(int $customer_id, array $ids): array
{
    if (!$ids) {
        return [];
    }

    $place = implode(',', array_fill(0, count($ids), '?'));

    $rows = cd_query(
        "SELECT title FROM tbl_cd_link
         WHERE customer_id = ? AND revoked = '0' AND root_node_id IN ($place)",
        array_merge([$customer_id], $ids)
    )->fetchAll();

    return array_column($rows, 'title');
}

// ============================================================
// ไฟล์บนดิสก์
// ============================================================

function cd_root(): string
{
    return CD_FILES_PATH;
}

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
 * ที่เก็บไฟล์โหลดตรงทาง URL ได้จริงไหม
 *
 * ปรับจากต้นฉบับ: เทียบ cd_root() กับโฟลเดอร์ public/ จริงของโปรเจกต์นี้
 * (ไม่ใช่ค่าที่ต้นฉบับสมมติไว้) — storage/customer-files อยู่นอก public/
 * (เอกสาร root ของ Herd) จึงควรตัดจบด้วย "ไม่มี URL ให้ยิง" เสมอบนเซิร์ฟเวอร์นี้
 */
function cd_storage_exposed(): array
{
    $out = ['tested' => false, 'exposed' => false, 'status' => 0, 'url' => '', 'msn' => ''];

    $publicRoot = str_replace('\\', '/', (string) realpath(dirname(__DIR__, 3) . '/public'));
    $storageRoot = str_replace('\\', '/', cd_root());

    if ($publicRoot === '' || !str_starts_with($storageRoot, $publicRoot)) {
        $out['msn'] = 'ที่เก็บไฟล์อยู่นอกโฟลเดอร์ public/ จึงไม่มี URL ให้เข้าถึงตรงตั้งแต่ต้น';

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

function cd_mime_matches(string $path, string $ext): bool
{
    if (!function_exists('finfo_open')) {
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
 * ล็อกแถว node ด้วย FOR UPDATE ก่อนอ่านเลขเวอร์ชันปัจจุบันเสมอ กันสองคำขอ
 * พร้อมกันได้เลขเดียวกันแล้วเขียนทับไฟล์ของกันและกัน
 *
 * @return array{ok:bool,version:int,msn:string}
 */
function cd_commit_version(array $node, string $source, string $via, ?int $user_id, ?string $guest_label = null): array
{
    if (!is_file($source)) {
        return ['ok' => false, 'version' => 0, 'msn' => 'ไม่พบไฟล์ต้นทาง'];
    }

    $customer_id = (int) $node['customer_id'];
    $node_id = (int) $node['node_id'];
    $size = (int) filesize($source);
    $sha = hash_file('sha256', $source) ?: null;

    $pdo = cd_pdo();
    $pdo->beginTransaction();

    try {
        $locked = cd_query(
            "SELECT node_id, version, ext FROM tbl_cd_node
             WHERE customer_id = ? AND node_id = ? FOR UPDATE",
            [$customer_id, $node_id]
        )->fetch();

        if (!$locked) {
            throw new Exception('ไม่พบไฟล์ปลายทาง');
        }

        $next = (int) $locked['version'] + 1;

        cd_query(
            "INSERT INTO tbl_cd_version (node_id, version, size, sha256, via, create_by, guest_label)
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$node_id, $next, $size, $sha, $via, $user_id, $guest_label]
        );

        $node['ext'] = $locked['ext'];

        if (!cd_atomic_copy($source, cd_version_path($node, $next))) {
            throw new Exception('บันทึกไฟล์เวอร์ชันใหม่ไม่สำเร็จ');
        }

        if (!cd_atomic_copy($source, cd_current_path($node))) {
            throw new Exception('บันทึกไฟล์ล่าสุดไม่สำเร็จ');
        }

        cd_query(
            "UPDATE tbl_cd_node
             SET version = ?, size = ?, sha256 = ?, update_by = ?
             WHERE customer_id = ? AND node_id = ?",
            [$next, $size, $sha, $user_id, $customer_id, $node_id]
        );

        $pdo->commit();

        return ['ok' => true, 'version' => $next, 'msn' => ''];
    } catch (Throwable $e) {
        $pdo->rollBack();

        return ['ok' => false, 'version' => 0, 'msn' => $e->getMessage()];
    }
}

function cd_versions(int $node_id): array
{
    return cd_query(
        "SELECT * FROM tbl_cd_version WHERE node_id = ? ORDER BY version DESC",
        [$node_id]
    )->fetchAll();
}

// ============================================================
// พื้นที่ดิสก์
// ============================================================

/** ไม่มี tbl_setting ในโปรเจกต์นี้ — เพดานเตือนตั้งตายตัวที่ 90% */
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
        'warn'    => $percent >= 90,
    ];
}

function cd_customer_usage(int $customer_id): int
{
    $row = cd_query(
        "SELECT COALESCE(SUM(size), 0) AS s FROM tbl_cd_node WHERE customer_id = ? AND kind = 'file'",
        [$customer_id]
    )->fetch();

    return (int) ($row['s'] ?? 0);
}

// ============================================================
// ความเคลื่อนไหว
// ============================================================

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
    if (!in_array($action, CD_ACTIONS, true)) {
        error_log('cd_activity: action ไม่อยู่ในรายการที่อนุญาต — ' . $action);

        return;
    }

    try {
        cd_query(
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

function cd_client_ip(): ?string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if ($ip === '') {
        return null;
    }

    $packed = @inet_pton($ip);

    return $packed === false ? null : $packed;
}

function cd_ip_text($packed): string
{
    if (!$packed) {
        return '';
    }

    $text = @inet_ntop($packed);

    return $text === false ? '' : $text;
}

function cd_activity_feed(int $customer_id, int $limit = 100): array
{
    $limit = max(1, min(500, $limit));

    return cd_query(
        "SELECT * FROM tbl_cd_activity
         WHERE customer_id = ?
         ORDER BY create_datetime DESC, activity_id DESC
         LIMIT " . $limit,
        [$customer_id]
    )->fetchAll();
}

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

function cd_fail(string $msg): void
{
    cd_json(['result' => 0, 'msg' => $msg]);
}

function cd_ok(array $extra = []): void
{
    cd_json(array_merge(['result' => 1, 'msg' => ''], $extra));
}

/**
 * ด่านที่ทุก endpoint ฝั่งพนักงานต้องผ่านก่อนทำอะไร — คืนแถวลูกค้าหรือจบคำขอ
 *
 * ไม่มีการตรวจสิทธิ์ละเอียดในโปรเจกต์นี้ (ดู checkAuth() ที่ controller
 * เรียกไปแล้วก่อนถึงจุดนี้เสมอ) ยกเว้นจุดเดียวคือการจัดการลิงก์แชร์
 * (สร้าง/เพิกถอน/ตั้งรหัสใหม่) ซึ่งเปิดทางให้คนนอกเข้าถึงเอกสารลูกค้าได้
 * จึงจำกัดเฉพาะ is_super_admin — ผู้เรียกส่ง $requireSuperAdmin/$isSuperAdmin มาเอง
 */
function cd_guard(int $customer_id, bool $requireSuperAdmin = false, bool $isSuperAdmin = false): array
{
    if ($customer_id <= 0) {
        cd_fail('ข้อมูลไม่ครบ');
    }

    if ($requireSuperAdmin && !$isSuperAdmin) {
        cd_fail('ต้องเป็นผู้ดูแลระบบเท่านั้นจึงจะจัดการลิงก์แชร์ได้');
    }

    $customer = cd_customer($customer_id);

    if (!$customer) {
        cd_fail('ไม่พบข้อมูลลูกค้า');
    }

    return $customer;
}

function cd_guard_node(int $customer_id, int $node_id, bool $include_trashed = false): array
{
    $node = cd_find_node($customer_id, $node_id, $include_trashed);

    if (!$node) {
        cd_fail('ไม่พบรายการนี้ในคลังไฟล์');
    }

    return $node;
}

// ============================================================
// ที่พักชิ้นส่วนของการอัปโหลดแบบแบ่งก้อน (เฟส 2)
// ============================================================

const CD_CHUNK_TTL = 21600; // 6 ชั่วโมง — ยาวพอให้ลูกค้าที่เน็ตหลุดกลับมาต่อได้ในวันเดียวกัน

function cd_chunk_dir(): string
{
    $dir = cd_root() . '/_chunks';

    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    return $dir;
}

/** $uploadId ถูกบังคับรูปแบบเป็น hex 64 ตัวมาแล้วจากผู้เรียก แต่กรองซ้ำที่นี่ */
function cd_chunk_path(string $uploadId): string
{
    if (!preg_match('/^[0-9a-f]{64}$/', $uploadId)) {
        return cd_chunk_dir() . '/invalid';
    }

    return cd_chunk_dir() . '/' . $uploadId . '.part';
}

/** ลบชิ้นส่วนที่ค้างเกินอายุ — ไม่พึ่ง cron เรียกจาก action=status เท่านั้น */
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

// ============================================================
// ลิงก์แชร์และรอบเข้าใช้ของแขก (เฟส 2)
// ============================================================

/*
 * ทุกอย่างใต้หัวข้อนี้คือ "เส้นทางของคนนอก" ซึ่งเป็นทางเข้าเดียวในระบบทั้งหมด
 * ที่ไม่ต้องล็อกอิน กติกาจึงเข้มกว่าฝั่งพนักงานทุกข้อ
 *
 * หลักที่ยึด
 *   1. token อย่างเดียวต้องไม่พอเปิดไฟล์ — ต้องผ่านรหัสผ่านเสมอ
 *   2. ข้อความผิดพลาดเหมือนกันทุกกรณี ห้ามบอกว่าลิงก์ไม่มี รหัสผิด หรือหมดอายุ
 *   3. ทุก query กรองด้วย "ขอบเขตของลิงก์" ไม่ใช่แค่ customer_id
 *   4. fail closed เสมอ — ไม่แน่ใจเมื่อไหร่ให้ปฏิเสธ
 */

/**
 * URL เต็มของหน้าแขกสำหรับ token หนึ่ง
 */
function cd_portal_url(string $token): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return ($https ? 'https' : 'http') . '://' . $host . BASE_URL . '/portal?t=' . $token;
}

/** สุ่ม token สำหรับ URL — 32 ไบต์ เข้ารหัส base64url ได้ 43 ตัวอักษร */
function cd_token(): string
{
    return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
}

/**
 * รหัสผ่านสุ่มที่อ่านออกเสียงและบอกต่อทางโทรศัพท์ได้
 * ตัดตัวที่สับสนออก (0 O o 1 l I)
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
 * ห้ามเอาค่าที่คืนจากฟังก์ชันนี้ไปแสดงให้แขกเห็น แขกเห็นได้แค่ CD_GUEST_DENY
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

    // ขอบเขตของลิงก์ถูกทิ้งลงถังขยะ = ลิงก์ต้องใช้ไม่ได้ทันที (fail closed)
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
    if (strlen($token) !== 43 || !preg_match('/^[A-Za-z0-9_-]+$/', $token)) {
        return null;
    }

    $row = cd_query("SELECT * FROM tbl_cd_link WHERE token = ?", [$token])->fetch();

    return $row ?: null;
}

function cd_link_by_id(int $customer_id, int $link_id): ?array
{
    $row = cd_query(
        "SELECT * FROM tbl_cd_link WHERE customer_id = ? AND link_id = ?",
        [$customer_id, $link_id]
    )->fetch();

    return $row ?: null;
}

/** หาลิงก์ด้วย link_id อย่างเดียว — ใช้เฉพาะใน cd_guest_guard() ที่ id มาจาก session แล้ว */
function cd_link_by_id_any(int $link_id): ?array
{
    $row = cd_query("SELECT * FROM tbl_cd_link WHERE link_id = ?", [$link_id])->fetch();

    return $row ?: null;
}

/**
 * ตั้ง cookie ของรอบเข้าใช้
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
    $token = cd_token();

    cd_query(
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
 * @return array{0: array, 1: array} [ลิงก์, รอบเข้าใช้]
 */
function cd_guest_guard(): array
{
    $token = (string) ($_COOKIE[CD_GUEST_COOKIE] ?? '');

    if ($token === '') {
        cd_guest_stop();
    }

    $row = cd_query(
        "SELECT s.*, l.link_id AS l_link_id
         FROM tbl_cd_link_session s
         JOIN tbl_cd_link l ON l.link_id = s.link_id
         WHERE s.session_token = ?",
        [$token]
    )->fetch();

    if (!$row) {
        cd_guest_stop();
    }

    $expires = strtotime((string) $row['expires_datetime']);
    $created = strtotime((string) $row['create_datetime']);

    if ($expires < time() || ($created + CD_GUEST_MAX_AGE) < time()) {
        cd_guest_stop();
    }

    $link = cd_link_by_id_any((int) $row['link_id']);

    if (!$link || cd_link_state($link) !== '') {
        cd_guest_stop();
    }

    // ต่ออายุแบบ sliding
    cd_query(
        "UPDATE tbl_cd_link_session
         SET expires_datetime = DATE_ADD(NOW(), INTERVAL ? SECOND)
         WHERE session_id = ?",
        [CD_GUEST_TTL, (int) $row['session_id']]
    );

    cd_guest_cookie($token, CD_GUEST_TTL);

    return [$link, $row];
}

/** จบคำขอฝั่งแขกด้วยข้อความกลาง ๆ พร้อมล้าง cookie ทิ้ง */
function cd_guest_stop(): void
{
    cd_guest_cookie('', 0);
    cd_json(['result' => 0, 'msg' => CD_GUEST_DENY, 'expired' => true]);
}

/**
 * โฟลเดอร์ปลายทางที่แขกอัปไฟล์เข้าได้ — คืน node_id หรือ null (ชั้นบนสุดของคลัง)
 * ยึดจาก root_node_id ของลิงก์เท่านั้น ห้ามรับค่าจากผู้เรียก
 */
function cd_guest_target(array $link): ?int
{
    return $link['root_node_id'] === null ? null : (int) $link['root_node_id'];
}

/**
 * รายการที่แขกมีสิทธิ์เห็น ตามโหมดของลิงก์
 *   collect — เห็นเฉพาะไฟล์ที่ตัวเองอัปในรอบนี้ ไม่เห็นของเดิมในโฟลเดอร์เลย
 *   share   — เห็นทุกอย่างใต้ root_node_id
 *
 * @return list<array<string,mixed>>
 */
function cd_guest_items(array $link, array $session): array
{
    $customer_id = (int) $link['customer_id'];

    if ($link['mode'] === 'collect') {
        return cd_query(
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
        return cd_query(
            "SELECT * FROM tbl_cd_node
             WHERE customer_id = ? AND delete_datetime IS NULL
             ORDER BY kind DESC, name",
            [$customer_id]
        )->fetchAll();
    }

    $place = implode(',', array_fill(0, count($ids), '?'));

    return cd_query(
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

/**
 * รับไฟล์หนึ่งใบจากแขกให้จบกระบวนการ — ตรวจ บันทึก เขียนดิสก์ จบคำขอเอง
 *
 * ใช้ร่วมกันทั้ง portal/ajax/upload (ส่งทีเดียวจบ) และ upload_chunk (ประกอบเสร็จ)
 * เพื่อไม่ให้ทางใดทางหนึ่งลืมตรวจ MIME หรือลืมนับโควตา
 *
 * @param string $tmp          ไฟล์บนดิสก์ที่พร้อมใช้
 * @param string $originalName ชื่อที่ลูกค้าตั้ง ยังไม่ผ่าน cd_safe_name()
 */
function cd_guest_accept(array $link, array $session, string $tmp, string $originalName): void
{
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

    if (!cd_mime_matches($tmp, $ext)) {
        cd_fail('เนื้อไฟล์ไม่ตรงกับนามสกุล .' . $ext . ' — ไฟล์อาจเสียหาย');
    }

    if ($link['max_uploads'] !== null && (int) $link['used_uploads'] >= (int) $link['max_uploads']) {
        cd_fail('ลิงก์นี้ส่งไฟล์ครบจำนวนที่กำหนดแล้ว กรุณาติดต่อผู้ตรวจสอบบัญชีของท่าน');
    }

    $parentId = cd_guest_target($link);

    // ส่งซ้ำไฟล์เดิม — เนื้อไฟล์เหมือนเดิมเป๊ะ = กดส่งซ้ำหรือเน็ตหลุดแล้วส่งใหม่
    $sha = hash_file('sha256', $tmp) ?: '';
    $existing = cd_find_by_name($customer_id, $parentId, $original);

    if ($existing && $sha !== '' && $sha === (string) $existing['sha256']) {
        cd_ok(['msg' => 'ไฟล์นี้ได้รับแล้ว', 'skipped' => true]);
    }

    // ชื่อชนแต่เนื้อต่าง = คนละไฟล์จริง ๆ → เปลี่ยนชื่อเป็น (2) ห้ามทับเป็นเวอร์ชันใหม่
    $name = cd_unique_name($customer_id, $parentId, $original);

    $pdo = cd_pdo();
    $pdo->beginTransaction();

    try {
        cd_query(
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

        $node_id = (int) $pdo->lastInsertId();

        cd_query(
            "UPDATE tbl_cd_link SET used_uploads = used_uploads + 1 WHERE link_id = ?",
            [(int) $link['link_id']]
        );

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('cd_guest_accept insert: ' . $e->getMessage());
        cd_fail('บันทึกไฟล์ไม่สำเร็จ กรุณาลองใหม่');
    }

    $node   = cd_find_node($customer_id, $node_id);
    $result = cd_commit_version($node, $tmp, 'guest_upload', null, $label !== '' ? $label : null);

    if (!$result['ok']) {
        cd_forget_files($customer_id, $node_id);
        cd_query("DELETE FROM tbl_cd_node WHERE customer_id = ? AND node_id = ?", [$customer_id, $node_id]);
        cd_query("UPDATE tbl_cd_link SET used_uploads = GREATEST(used_uploads - 1, 0) WHERE link_id = ?", [(int) $link['link_id']]);

        error_log('cd_guest_accept commit: ' . $result['msn']);
        cd_fail('บันทึกไฟล์ไม่สำเร็จ กรุณาลองใหม่');
    }

    cd_activity($customer_id, 'upload', $node_id, $name, 'guest', null, $label, (int) $link['link_id']);

    cd_ok([
        'msg'     => 'ส่งเรียบร้อยแล้ว',
        'node_id' => $node_id,
        'name'    => $name,
        'renamed' => $name !== $original,
    ]);
}
