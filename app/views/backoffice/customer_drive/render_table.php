<?php
// app/views/backoffice/customer_drive/render_table.php
// Ported from _export_customer_drive/files/main/ajax/customer_drive/render_table.php
// Controller already ran cd_guard() before requiring this — $customer_id/$userId/
// $folder_id/$keyword are set by CustomerDriveController::renderTable().

$customer = cd_customer($customer_id);

if (!$customer) {
    echo '<div class="alert alert-warning mb-0">ไม่พบข้อมูลลูกค้า</div>';
    exit;
}

$searching = $keyword !== '';
$folderId  = $folder_id ?: null;

if ($searching) {
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

$canEdit = true;
?>

<?php if (!$rows): ?>
    <div class="cd-empty text-center py-5" data-cd-drop="<?php echo $folderId === null ? '' : (int) $folderId; ?>">
        <?php if ($searching): ?>
            <i class="cd-ic cd-empty-icon ri-search-line"></i>
            <div class="fw-medium mt-2">ไม่พบรายการที่ตรงกับ “<?php echo cd_e($keyword); ?>”</div>
            <div class="text-muted fs-13">ลองพิมพ์คำสั้นลง หรือกดล้างช่องค้นหาเพื่อกลับไปดูทั้งโฟลเดอร์</div>
        <?php else: ?>
            <i class="cd-ic cd-empty-icon ri-folder-open-line"></i>
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

                    $parent = $row['parent_id'] === null ? 0 : (int) $row['parent_id'];

                    if (!$searching && $folderId !== null && $parent === $folderId) {
                        $parent = 0;
                    }

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
                                        <i class="cd-ic ri-arrow-down-s-line"></i>
                                    </button>
                                <?php else: ?>
                                    <span class="cd-caret cd-caret-none"></span>
                                <?php endif; ?>

                                <i class="cd-icon cd-icon-<?php echo cd_e($kind); ?> cd-ic <?php echo cd_e(cd_icon_for($kind)); ?>"></i>

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
                                    <i class="cd-ic fs-14 ri-user-line"></i>
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
                                <i class="cd-ic fs-20 ri-more-2-fill"></i>
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
