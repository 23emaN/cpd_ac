<?php

/**
 * เนื้อ modal "ย้ายไป…" — ทรีโฟลเดอร์ทั้งคลังให้เลือกปลายทาง
 *
 * โฟลเดอร์ที่เป็นตัวมันเองหรือลูกหลานของสิ่งที่กำลังย้าย ต้องกดไม่ได้
 * เพราะการย้ายเข้าไปในตัวเองทำให้กิ่งนั้นหลุดจากทรีทั้งกิ่ง —
 * ปิดตั้งแต่บนหน้าจอ แล้วให้ move.php ตรวจซ้ำอีกชั้นฝั่งเซิร์ฟเวอร์
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$raw         = (string) ($_POST['node_ids'] ?? '');


$customer = cd_customer($customer_id);

if (!$customer || !cd_can($user_id, 'cdrive_edit')) {
    echo '<div class="modal-header"><h5 class="modal-title">ย้ายไป…</h5>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body"><div class="alert alert-warning mb-0">คุณไม่มีสิทธิ์ย้ายรายการ</div></div>';
    exit;
}

$moving = array_values(array_unique(array_filter(
    array_map('intval', explode(',', $raw)),
    static fn ($id) => $id > 0
)));

// รวม id ที่ห้ามเลือกเป็นปลายทาง: ตัวที่กำลังย้าย + ลูกหลานทั้งหมดของมัน
$blocked = [];

foreach ($moving as $id) {
    $blocked[$id] = true;

    foreach (cd_descendant_ids($customer_id, $id, true) as $child) {
        $blocked[$child] = true;
    }
}

$nodes = cd_flatten($customer_id);

/*
 * ป้ายบอกว่า "กำลังย้ายอะไรอยู่" — ใช้ชื่อจริง ไม่ใช่แค่จำนวน
 *
 * ของเดิมหัวเรื่องเขียนว่า "ย้าย 1 รายการไปที่…" ซึ่งไม่ช่วยให้ผู้ใช้มั่นใจว่า
 * เลือกถูกใบ โดยเฉพาะตอนเลือกหลายรายการด้วย Ctrl/Shift แล้วเผลอคลิกโดนใบอื่น
 * การย้ายเป็นการกระทำที่ไม่มีปุ่มเลิกทำ ต้องย้ายกลับเอง จึงคุ้มที่จะยืนยันตรงนี้
 *
 * $moving เก็บเป็น node_id ล้วน จึงต้องไปดึงชื่อจากทรีที่อ่านมาแล้ว
 * ไม่ยิง query เพิ่ม (cd_flatten อ่านทั้งคลังมาด้วย query เดียวอยู่แล้ว)
 */
$byId = [];

foreach ($nodes as $node) {
    $byId[(int) $node['node_id']] = (string) $node['name'];
}

$movingNames = [];

foreach ($moving as $id) {
    if (isset($byId[$id])) {
        $movingNames[] = $byId[$id];
    }
}

if (count($movingNames) === 1) {
    $movingLabel = $movingNames[0];
} elseif (count($movingNames) > 1) {
    $movingLabel = implode(' · ', array_slice($movingNames, 0, 2))
        . (count($movingNames) > 2 ? ' และอีก ' . (count($movingNames) - 2) . ' รายการ' : '');
} else {
    $movingLabel = count($moving) . ' รายการ';
}
?>
<div class="modal-header">
    <div class="d-flex align-items-start gap-2">
        <span class="material-symbols-outlined">drive_file_move</span>
        <div>
            <h5 class="modal-title">ย้ายไปโฟลเดอร์</h5>
            <span class="cd-modal-sub"><?php echo cd_e($movingLabel); ?></span>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
</div>

<div class="modal-body">
    <div class="cd-picker" id="cd-picker">
        <button type="button" class="cd-pick active" data-folder="">
            <span class="material-symbols-outlined fs-18">folder_open</span>
            <span>คลังไฟล์ (ชั้นบนสุด)</span>
        </button>

        <?php
        $shown = 0;

        foreach ($nodes as $node) {
            if ($node['kind'] !== 'folder') {
                continue;
            }

            $id = (int) $node['node_id'];
            $off = isset($blocked[$id]);
            $shown++;
        ?>
            <button type="button" class="cd-pick<?php echo $off ? ' disabled' : ''; ?>"
                data-folder="<?php echo $id; ?>"
                style="padding-left: <?php echo 12 + ((int) $node['depth'] + 1) * 18; ?>px"
                <?php echo $off ? 'disabled title="ย้ายเข้าไปในตัวเองหรือโฟลเดอร์ย่อยของตัวเองไม่ได้"' : ''; ?>>
                <span class="material-symbols-outlined fs-18">folder</span>
                <span><?php echo cd_e($node['name']); ?></span>
            </button>
        <?php
        }
        ?>

        <?php if ($shown === 0): ?>
            <div class="text-muted fs-13 px-2 py-3">ยังไม่มีโฟลเดอร์ในคลังนี้ ย้ายได้เฉพาะไปชั้นบนสุด</div>
        <?php endif; ?>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
    <button type="button" class="btn btn-primary" data-cd="pick-confirm">ย้ายมาที่นี่</button>
    <!-- ปุ่มยกเลิกอยู่ก่อนหน้าโดยตั้งใจ ให้ปุ่มที่ทำจริงอยู่ขวาสุดตามที่ตาไทยกวาดไปหยุด -->
</div>
