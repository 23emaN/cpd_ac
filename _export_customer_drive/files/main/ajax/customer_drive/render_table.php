<?php

/**
 * ทรีรายการในคลัง หรือผลการค้นหา
 *
 * ถูกเรียกซ้ำทุกครั้งที่ข้อมูลเปลี่ยน (อัปโหลด ย้าย ลบ เปลี่ยนชื่อ) จึงต้องเบา
 * และต้องวาดครบพอที่ JS ไม่ต้องรู้อะไรเพิ่มนอกจาก data-* ที่ติดมากับแถว
 *
 * โหมดการแสดงผลสองแบบ
 *   ทรี   — ทุกชั้นใต้โฟลเดอร์ที่เปิดอยู่ วาดครั้งเดียวจาก cd_flatten()
 *           แล้วให้ JS ย่อ/ขยายในหน่วยความจำ ไม่ต้องยิงเซิร์ฟเวอร์ซ้ำ
 *   ค้นหา — รายการแบน ๆ ที่ชื่อตรงคำค้น พร้อมบอกว่าอยู่โฟลเดอร์ไหน (ไม่มีทรี)
 *
 * ทำไมส่ง "ทุกชั้น" มาทีเดียวแทนที่จะโหลดตอนกางโฟลเดอร์:
 * cd_flatten() อ่านทรีทั้งคลังด้วย query เดียวอยู่แล้ว การส่งมาทั้งก้อน
 * ทำให้กางแล้วเห็นทันทีโดยไม่มีจังหวะรอ และไม่ต้องมี endpoint เพิ่ม
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$folder_id   = (int) ($_POST['folder_id'] ?? 0);
$keyword     = trim((string) ($_POST['keyword'] ?? ''));

$customer = cd_customer($customer_id);

if (!$customer || !cd_can($user_id, 'cdrive_view')) {
    echo '<div class="alert alert-warning mb-0">ไม่มีสิทธิ์ดูคลังไฟล์นี้</div>';
    exit;
}

$perm      = cd_perm_summary($user_id);
$searching = $keyword !== '';
$folderId  = $folder_id ?: null;

if ($searching) {
    // ค้นทุกชั้นในคลังนี้ แล้วเตรียม "ที่อยู่" ของแต่ละผลลัพธ์ไว้แสดงใต้ชื่อ
    $all = cd_all_nodes($customer_id);
    $byId = [];

    foreach ($all as $n) {
        $byId[(int) $n['node_id']] = $n;
    }

    $rows = [];

    foreach ($all as $n) {
        if (mb_stripos((string) $n['name'], $keyword, 0, 'UTF-8') === false) {
            continue;
        }

        $path = [];
        $cur = $n['parent_id'] === null ? 0 : (int) $n['parent_id'];
        $guard = 0;

        while ($cur && isset($byId[$cur]) && $guard++ < 100) {
            array_unshift($path, (string) $byId[$cur]['name']);
            $cur = $byId[$cur]['parent_id'] === null ? 0 : (int) $byId[$cur]['parent_id'];
        }

        $n['cd_path']      = $path ? ('คลังไฟล์ / ' . implode(' / ', $path)) : 'คลังไฟล์';
        $n['depth']        = 0;
        $n['has_children'] = false;

        $rows[] = $n;
    }

    usort($rows, 'cd_node_cmp');
} else {
    /*
     * ตัดเอาเฉพาะกิ่งที่อยู่ใต้โฟลเดอร์ที่เปิดอยู่
     *
     * cd_flatten() คืนทั้งคลังพร้อม depth นับจากราก — พอเปิดโฟลเดอร์ย่อย
     * ต้องกรองให้เหลือลูกหลานของมัน แล้วลด depth ลงให้เริ่มที่ 0 ใหม่
     * มิฉะนั้นแถวแรกจะเยื้องเข้าไปลึกโดยไม่มีเหตุผล
     */
    $flat = cd_flatten($customer_id);

    if ($folderId === null) {
        $rows = $flat;
    } else {
        $inside = array_flip(cd_descendant_ids($customer_id, $folderId));
        $base   = null;
        $rows   = [];

        foreach ($flat as $n) {
            if (!isset($inside[(int) $n['node_id']])) {
                continue;
            }

            if ($base === null) {
                $base = (int) $n['depth'];
            }

            $n['depth'] = (int) $n['depth'] - $base;
            $rows[] = $n;
        }
    }
}

$canEdit = $perm['cdrive_edit'];
?>

<?php if (!$rows): ?>
    <div class="cd-empty text-center py-5" data-cd-drop="<?php echo $folderId === null ? '' : (int) $folderId; ?>">
        <?php if ($searching): ?>
            <span class="material-symbols-outlined cd-empty-icon">search_off</span>
            <div class="fw-medium mt-2">ไม่พบรายการที่ตรงกับ “<?php echo cd_e($keyword); ?>”</div>
            <div class="text-muted fs-13">ลองพิมพ์คำสั้นลง หรือกดล้างช่องค้นหาเพื่อกลับไปดูทั้งโฟลเดอร์</div>
        <?php else: ?>
            <span class="material-symbols-outlined cd-empty-icon">folder_open</span>
            <div class="fw-medium mt-2"><?php echo $folderId === null ? 'คลังไฟล์ของลูกค้ารายนี้ยังว่างอยู่' : 'โฟลเดอร์นี้ยังว่างอยู่'; ?></div>
            <div class="text-muted fs-13 mb-3">ลากไฟล์มาวางที่นี่ หรือกดปุ่มอัปโหลดด้านบน</div>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table cd-table align-middle mb-0<?php echo $searching ? '' : ' cd-tree'; ?>">
            <thead>
                <tr>
                    <th>ชื่อ</th>
                    <th class="cd-col-source">ที่มา</th>
                    <th class="cd-col-size text-end">ขนาด</th>
                    <th class="cd-col-ver text-center">เวอร์ชัน</th>
                    <th class="cd-col-date">แก้ไขล่าสุด</th>
                    <th class="cd-col-act"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row):
                    $id       = (int) $row['node_id'];
                    $isFolder = $row['kind'] === 'folder';
                    $ext      = (string) ($row['ext'] ?? '');
                    $kind     = $isFolder ? 'folder' : cd_file_kind($ext);
                    $isGuest  = $row['source'] === 'guest';
                    $depth    = (int) ($row['depth'] ?? 0);
                    $hasKids  = !empty($row['has_children']);

                    // parent = 0 หมายถึงอยู่ชั้นบนสุดของมุมมองนี้ — JS ใช้ไล่ว่าแถวไหน
                    // ต้องซ่อนตามเมื่อแม่ถูกย่อ
                    $parent = $row['parent_id'] === null ? 0 : (int) $row['parent_id'];

                    if (!$searching && $folderId !== null && $parent === $folderId) {
                        $parent = 0;
                    }

                    /*
                     * ที่มาแสดง "ชื่อคน" ไม่ใช่คำว่าพนักงาน
                     *
                     * คำว่า "พนักงาน" ไม่ได้บอกอะไรเลยเพราะไฟล์ส่วนใหญ่ในคลังก็เป็นของพนักงาน
                     * สิ่งที่คนอยากรู้จริง ๆ คือ "ใครเอามาวาง" — ชื่อจึงมีค่ากว่า
                     * ส่วนของลูกค้ายังต้องขึ้นคำว่า "ลูกค้า" นำหน้าเสมอ เพราะการแยกออกว่า
                     * อันไหนมาจากคนนอกในสายตาเดียวคือเหตุผลที่คอลัมน์นี้มีอยู่
                     */
                    $creator = cd_user_name($row['create_by'] ? (int) $row['create_by'] : null);
                    $who = $isGuest
                        ? ('ลูกค้า' . ($row['guest_label'] ? ' · ' . $row['guest_label'] : ''))
                        : $creator;
                ?>
                    <tr class="cd-row"
                        data-id="<?php echo $id; ?>"
                        data-parent="<?php echo $parent; ?>"
                        data-depth="<?php echo $depth; ?>"
                        data-kind="<?php echo cd_e($row['kind']); ?>"
                        data-name="<?php echo cd_e($row['name']); ?>"
                        data-ext="<?php echo cd_e($ext); ?>"
                        data-preview="<?php echo (!$isFolder && cd_is_previewable($ext)) ? '1' : '0'; ?>"
                        data-version="<?php echo (int) $row['version']; ?>"
                        data-kids="<?php echo $hasKids ? '1' : '0'; ?>"
                        <?php echo $canEdit ? 'draggable="true"' : ''; ?>>

                        <td class="cd-cell-name">
                            <div class="d-flex align-items-center gap-1" style="padding-left: <?php echo $depth * 20; ?>px">
                                <?php if ($hasKids): ?>
                                    <button type="button" class="cd-caret" data-cd="toggle" aria-label="ย่อ/ขยาย">
                                        <span class="material-symbols-outlined">expand_more</span>
                                    </button>
                                <?php else: ?>
                                    <span class="cd-caret cd-caret-none"></span>
                                <?php endif; ?>

                                <span class="cd-icon cd-icon-<?php echo cd_e($kind); ?> material-symbols-outlined">
                                    <?php echo cd_e(cd_icon_for($kind)); ?>
                                </span>

                                <div class="min-w-0">
                                    <span class="cd-name" title="<?php echo cd_e($row['name']); ?>"><?php echo cd_e($row['name']); ?></span>
                                    <?php if ($searching): ?>
                                        <div class="cd-path text-muted fs-12"><?php echo cd_e($row['cd_path']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>

                        <td class="cd-col-source">
                            <?php if ($isFolder): ?>
                                <span class="text-muted">—</span>
                            <?php elseif ($isGuest): ?>
                                <span class="badge cd-chip cd-chip-guest" title="<?php echo cd_e($who . ' · ' . cd_time($row['create_datetime'])); ?>">
                                    <span class="material-symbols-outlined fs-14">person</span>
                                    <span class="cd-who">ลูกค้า<?php echo $row['guest_label'] ? ' · ' . cd_e($row['guest_label']) : ''; ?></span>
                                </span>
                            <?php else: ?>
                                <span class="cd-chip cd-chip-staff" title="<?php echo cd_e($creator . ' · ' . cd_time($row['create_datetime'])); ?>">
                                    <span class="cd-who"><?php echo cd_e($creator !== '—' ? $creator : 'พนักงาน'); ?></span>
                                </span>
                            <?php endif; ?>
                        </td>

                        <td class="cd-col-size text-end">
                            <?php echo $isFolder ? '<span class="text-muted">—</span>' : cd_e(cd_format_bytes((int) $row['size'])); ?>
                        </td>

                        <td class="cd-col-ver text-center">
                            <?php if ($isFolder || (int) $row['version'] <= 0): ?>
                                <span class="text-muted">—</span>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-link p-0 cd-ver" data-cd="versions">
                                    v<?php echo (int) $row['version']; ?>
                                </button>
                            <?php endif; ?>
                        </td>

                        <td class="cd-col-date">
                            <span class="fs-13"><?php echo cd_e(cd_time($row['update_datetime'] ?: $row['create_datetime'])); ?></span>
                        </td>

                        <td class="cd-col-act text-end">
                            <button type="button" class="btn btn-sm btn-link p-0 cd-dots" data-cd="menu" aria-label="เมนู">
                                <span class="material-symbols-outlined fs-20">more_vert</span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="cd-foot d-flex justify-content-between align-items-center flex-wrap gap-2 pt-3 text-muted fs-13">
        <span><?php echo count($rows); ?> รายการ<?php echo $searching ? ' ที่ตรงกับคำค้น' : ''; ?></span>
        <span class="cd-hint d-none d-lg-inline">
            <kbd>ดับเบิลคลิก</kbd> เปิด ·
            <kbd>คลิกขวา</kbd> เมนู ·
            <kbd>Ctrl</kbd>+คลิก เลือกเพิ่ม ·
            <kbd>Shift</kbd>+คลิก เลือกช่วง ·
            <kbd>←</kbd><kbd>→</kbd> ย่อ/ขยาย ·
            <kbd>F2</kbd> เปลี่ยนชื่อ ·
            <kbd>Del</kbd> ทิ้ง
        </span>
        <span>ไฟล์ละไม่เกิน <?php echo cd_e(cd_format_bytes(cd_max_upload_bytes())); ?></span>
    </div>
<?php endif; ?>

<?php

// cd_icon_for() ย้ายไปอยู่ที่ config/customer_drive.php แล้ว เพราะมีหลายหน้าที่ต้องใช้
