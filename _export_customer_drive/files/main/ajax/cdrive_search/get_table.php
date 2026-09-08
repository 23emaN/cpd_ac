<?php

/**
 * ผลการค้นหาไฟล์ข้ามลูกค้าทุกราย
 *
 * ต่างจาก customer_drive/render_table.php ตรงที่ไม่ผูกกับลูกค้ารายเดียว —
 * จึงต้อง JOIN tbl_customer มาเพื่อบอกว่าแต่ละผลลัพธ์เป็นของใคร และต้องกรอง
 * ลูกค้าที่ถูกลบออกด้วย (delete_status = '0')
 *
 * เหตุผลที่เลือกค้นในฐานข้อมูลแทนการอ่านทรีทั้งหมดมาไล่ในหน่วยความจำแบบหน้าคลัง:
 * หน้านั้นอ่านคลังเดียวซึ่งมีไม่กี่ร้อยแถว ส่วนหน้านี้ครอบทั้ง 443 ลูกค้า
 * การดึงทุกแถวขึ้นมาแล้วค่อยกรองจะโตขึ้นเรื่อย ๆ ตามอายุระบบ
 *
 * ส่วน "เส้นทางโฟลเดอร์" ยังต้องประกอบในหน่วยความจำอยู่ เพราะ MySQL
 * ทำ recursive CTE ได้ก็จริง แต่ validateSQL() ของโปรเจกต์เสี่ยงบล็อกโดยไม่จำเป็น
 * — ดึงเฉพาะ (node_id, parent_id, name) ของลูกค้าที่มีผลลัพธ์เท่านั้นมาไล่
 */

include('../../../config/customer_drive.php');

$user_id = (int) ($_POST['user_id'] ?? 0);
$keyword = trim((string) ($_POST['keyword'] ?? ''));

if (!cd_can($user_id, 'cdrive_view')) {
    echo '<div class="alert alert-warning mb-0">คุณไม่มีสิทธิ์ค้นหาไฟล์ลูกค้า (<code>cdrive_view</code>)</div>';
    exit;
}

if (mb_strlen($keyword, 'UTF-8') < 2) {
    echo '<div class="text-center text-muted py-5">พิมพ์อย่างน้อย 2 ตัวอักษร</div>';
    exit;
}

global $conn;

$limit = 200;

/*
 * ค้น "ลูกค้า" ก่อน แล้วค่อยค้น "ไฟล์"
 *
 * เพราะคำถามที่คนถามบ่อยที่สุดคือ "คลังของลูกค้ารายนี้อยู่ไหน" ไม่ใช่ "ไฟล์นี้อยู่ไหน"
 * ถ้ามีแต่ผลไฟล์ ลูกค้าที่คลังยังว่างจะหาไม่เจอเลยทั้งที่เป็นรายที่กำลังจะเอาไฟล์ไปใส่
 *
 * ค้นทั้งชื่อและรหัสลูกค้า เพราะพนักงานจำรหัสได้พอ ๆ กับชื่อ
 */
$customers = $conn->prepareAndExecute(
    "SELECT c.customer_id, c.customer_name, c.customer_code, c.active_status,
            (SELECT COUNT(*) FROM tbl_cd_node n
             WHERE n.customer_id = c.customer_id AND n.delete_datetime IS NULL) AS node_count,
            (SELECT COALESCE(SUM(n2.size), 0) FROM tbl_cd_node n2
             WHERE n2.customer_id = c.customer_id AND n2.kind = 'file') AS used_size
     FROM tbl_customer c
     WHERE c.delete_status = '0'
       AND (c.customer_name LIKE ? OR c.customer_code LIKE ?)
     ORDER BY c.customer_name
     LIMIT 30",
    ['%' . $keyword . '%', '%' . $keyword . '%']
)->fetchAll();

$rows = $conn->prepareAndExecute(
    "SELECT n.node_id, n.customer_id, n.parent_id, n.kind, n.name, n.ext,
            n.size, n.version, n.source, n.guest_label, n.create_by,
            n.update_datetime, n.create_datetime,
            c.customer_name, c.customer_code
     FROM tbl_cd_node n
     JOIN tbl_customer c ON c.customer_id = n.customer_id
     WHERE n.delete_datetime IS NULL
       AND c.delete_status = '0'
       AND n.name LIKE ?
     ORDER BY n.update_datetime DESC, n.node_id DESC
     LIMIT " . ($limit + 1),
    ['%' . $keyword . '%']
)->fetchAll();

$truncated = count($rows) > $limit;

if ($truncated) {
    array_pop($rows);
}

if (!$rows && !$customers) {
    echo '<div class="text-center text-muted py-5">'
        . '<span class="material-symbols-outlined cd-empty-icon">search_off</span>'
        . '<div class="fw-medium mt-2">ไม่พบลูกค้า ไฟล์ หรือโฟลเดอร์ที่มีคำว่า “' . cd_e($keyword) . '”</div>'
        . '<div class="fs-13">ค้นจากชื่อและรหัสลูกค้าเท่านั้น ไม่ได้ค้นเนื้อหาข้างในไฟล์</div>'
        . '</div>';
    exit;
}

/*
 * ประกอบเส้นทางโฟลเดอร์ของผลลัพธ์
 *
 * ดึงเฉพาะโครง (node_id, parent_id, name) ของลูกค้าที่มีผลลัพธ์เท่านั้น
 * ไม่ใช่ทั้งระบบ
 */
$customerIds = array_values(array_unique(array_map(static fn ($r) => (int) $r['customer_id'], $rows)));

$byId = [];

// เจอลูกค้าแต่ไม่เจอไฟล์เลยเป็นเรื่องปกติ — ต้องข้าม query นี้ไป
// ไม่งั้นจะได้ IN () ซึ่งเป็น SQL syntax error
if ($customerIds) {
    $place = implode(',', array_fill(0, count($customerIds), '?'));

    foreach ($conn->prepareAndExecute(
        "SELECT node_id, customer_id, parent_id, name FROM tbl_cd_node
         WHERE customer_id IN ($place) AND kind = 'folder' AND delete_datetime IS NULL",
        $customerIds
    )->fetchAll() as $f) {
        $byId[(int) $f['node_id']] = $f;
    }
}

/** เส้นทางโฟลเดอร์ของ node หนึ่ง (ไม่รวมตัวมันเอง) */
function cds_path(array $byId, $parent_id): string
{
    $path = [];
    $cur = $parent_id === null ? 0 : (int) $parent_id;
    $guard = 0;

    while ($cur && isset($byId[$cur]) && $guard++ < 100) {
        array_unshift($path, (string) $byId[$cur]['name']);
        $cur = $byId[$cur]['parent_id'] === null ? 0 : (int) $byId[$cur]['parent_id'];
    }

    return $path ? ('คลังไฟล์ / ' . implode(' / ', $path)) : 'คลังไฟล์';
}
?>

<?php if ($customers): ?>
    <div class="mb-4">
        <div class="text-muted fs-13 mb-2">ลูกค้า <?php echo count($customers); ?> ราย</div>

        <div class="cd-cust-grid">
            <?php foreach ($customers as $c): ?>
                <a class="cd-cust" href="customer_drive.php?id=<?php echo (int) $c['customer_id']; ?>">
                    <span class="cd-cust-icon material-symbols-outlined">folder_shared</span>
                    <span class="min-w-0 flex-grow-1">
                        <span class="cd-cust-name"><?php echo cd_e($c['customer_name']); ?></span>
                        <span class="cd-cust-meta">
                            <?php echo cd_e($c['customer_code'] ?: 'ไม่มีรหัส'); ?>
                            ·
                            <?php if ((int) $c['node_count'] > 0): ?>
                                <?php echo (int) $c['node_count']; ?> รายการ · <?php echo cd_e(cd_format_bytes((int) $c['used_size'])); ?>
                            <?php else: ?>
                                คลังยังว่าง
                            <?php endif; ?>
                            <?php if ($c['active_status'] !== '1'): ?>
                                · <span class="text-warning">ปิดใช้งาน</span>
                            <?php endif; ?>
                        </span>
                    </span>
                    <span class="material-symbols-outlined cd-cust-go">chevron_right</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (!$rows): ?>
    <div class="text-muted fs-13 py-3 border-top">
        ไม่มีไฟล์หรือโฟลเดอร์ที่ชื่อมีคำว่า “<?php echo cd_e($keyword); ?>”
    </div>
<?php else: ?>
<div class="text-muted fs-13 mb-2">ไฟล์และโฟลเดอร์</div>
<div class="table-responsive">
    <table class="table cd-table align-middle mb-0">
        <thead>
            <tr>
                <th>ชื่อ</th>
                <th>ลูกค้า</th>
                <th class="cd-col-source">ที่มา</th>
                <th class="cd-col-size text-end">ขนาด</th>
                <th class="cd-col-date">แก้ไขล่าสุด</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row):
                $isFolder = $row['kind'] === 'folder';
                $ext = (string) ($row['ext'] ?? '');
                $kind = $isFolder ? 'folder' : cd_file_kind($ext);
                $isGuest = $row['source'] === 'guest';

                // คลิกแล้วเข้าคลังของลูกค้ารายนั้น ที่โฟลเดอร์ที่ของชิ้นนี้อยู่
                $openAt = $isFolder ? (int) $row['node_id'] : (int) ($row['parent_id'] ?? 0);
                $href = 'customer_drive.php?id=' . (int) $row['customer_id']
                    . ($openAt ? '&folder=' . $openAt : '');
            ?>
                <tr class="cd-row" onclick="window.location.href='<?php echo cd_e($href); ?>'" style="cursor:pointer">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="cd-icon cd-icon-<?php echo cd_e($kind); ?> material-symbols-outlined">
                                <?php echo $isFolder ? 'folder' : 'draft'; ?>
                            </span>
                            <div class="min-w-0">
                                <span class="cd-name"><?php echo cd_e($row['name']); ?></span>
                                <div class="cd-path text-muted fs-12"><?php echo cd_e(cds_path($byId, $row['parent_id'])); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="fs-13"><?php echo cd_e($row['customer_name']); ?></span>
                        <div class="text-muted fs-12"><?php echo cd_e($row['customer_code'] ?: '—'); ?></div>
                    </td>
                    <td class="cd-col-source">
                        <?php
                        // ที่มาแสดงชื่อคน เหมือนหน้าคลัง — เหตุผลอยู่ใน customer_drive/render_table.php
                        $creator = cd_user_name($row['create_by'] ? (int) $row['create_by'] : null);
                        ?>
                        <?php if ($isFolder): ?>
                            <span class="text-muted">—</span>
                        <?php elseif ($isGuest): ?>
                            <span class="badge cd-chip cd-chip-guest">
                                <span class="material-symbols-outlined fs-14">person</span>
                                <span class="cd-who">ลูกค้า<?php echo $row['guest_label'] ? ' · ' . cd_e($row['guest_label']) : ''; ?></span>
                            </span>
                        <?php else: ?>
                            <span class="cd-chip cd-chip-staff">
                                <span class="cd-who"><?php echo cd_e($creator !== '—' ? $creator : 'พนักงาน'); ?></span>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="cd-col-size text-end">
                        <?php echo $isFolder ? '<span class="text-muted">—</span>' : cd_e(cd_format_bytes((int) $row['size'])); ?>
                    </td>
                    <td class="cd-col-date">
                        <span class="fs-13"><?php echo cd_e(cd_time($row['update_datetime'] ?: $row['create_datetime'])); ?></span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="cd-foot d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 text-muted fs-13">
    <span>พบ <?php echo count($rows); ?> รายการ</span>
    <?php if ($truncated): ?>
        <span class="text-warning">แสดงแค่ <?php echo $limit; ?> รายการแรก — พิมพ์คำที่เจาะจงกว่านี้เพื่อให้ผลแคบลง</span>
    <?php endif; ?>
</div>
<?php endif; ?>
