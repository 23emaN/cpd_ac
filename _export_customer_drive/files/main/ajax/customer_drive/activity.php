<?php

/**
 * เนื้อ modal ความเคลื่อนไหวของคลังลูกค้ารายหนึ่ง
 *
 * ตราบใดที่ยังไม่มีระบบแจ้งเตือน (เฟส 3) หน้านี้คือทางเดียวที่ตอบคำถาม
 * "ลูกค้าส่งอะไรมาเมื่อไหร่" ได้ จึงต้องอ่านรู้เรื่องโดยไม่ต้องแปลรหัสเอง
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);

$customer = cd_customer($customer_id);

if (!$customer || !cd_can($user_id, 'cdrive_view_activity')) {
    echo '<div class="modal-header"><h5 class="modal-title">ความเคลื่อนไหว</h5>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body"><div class="alert alert-warning mb-0">'
        . 'คุณไม่มีสิทธิ์ดูประวัติความเคลื่อนไหว (<code>cdrive_view_activity</code>)</div></div>';
    exit;
}

$rows = cd_activity_feed($customer_id, 200);
?>
<div class="modal-header">
    <div class="d-flex align-items-start gap-2">
        <span class="material-symbols-outlined">history</span>
        <div>
            <h5 class="modal-title">ความเคลื่อนไหว</h5>
            <span class="cd-modal-sub"><?php echo cd_e($customer['customer_name']); ?></span>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
</div>

<div class="modal-body">
    <?php if (!$rows): ?>
        <div class="cd-empty">
            <span class="material-symbols-outlined cd-empty-icon">history</span>
            <div class="cd-empty-title">ยังไม่มีความเคลื่อนไหวในคลังนี้</div>
        </div>
    <?php else: ?>
        <?php
        /*
         * จัดกลุ่มตามวัน แล้วแสดงเฉพาะเวลาในแต่ละแถว
         *
         * ของเดิมพิมพ์วันที่เต็มซ้ำทุกแถว ทั้งที่ 55 รายการส่วนใหญ่เป็นวันเดียวกัน
         * ตาต้องอ่าน "01/09/2026" ซ้ำ 55 ครั้งเพื่อหาว่าอันไหนคนละวัน
         * พอแยกหัววันออกมา ข้อมูลที่ต่างกันจริงคือเวลา จึงเหลือให้อ่านแค่นั้น
         */
        $lastDay = null;

        foreach ($rows as $row):
            $isGuest = $row['actor_type'] === 'guest';
            $who = $isGuest
                ? ('ลูกค้า' . ($row['actor_label'] ? ' · ' . $row['actor_label'] : ''))
                : ($row['actor_type'] === 'system' ? 'ระบบ' : cd_user_name($row['actor_user_id'] ? (int) $row['actor_user_id'] : null));
            $ip = cd_ip_text($row['ip'] ?? null);
            $ts = strtotime((string) $row['create_datetime']);
            $day = date('Y-m-d', $ts);

            if ($day !== $lastDay):
                $lastDay = $day;
        ?>
                <div class="cd-feed-day"><?php echo cd_e(cd_day_label($ts)); ?></div>
        <?php endif; ?>

            <div class="cd-feed-item">
                <div class="cd-feed-dot cd-feed-<?php echo $isGuest ? 'guest' : 'staff'; ?>"></div>
                <div class="cd-feed-time"><?php echo date('H:i', $ts); ?></div>
                <div class="cd-feed-main">
                    <div class="cd-feed-action"><?php echo cd_e(cd_action_label((string) $row['action'])); ?></div>
                    <?php if ($row['detail']): ?>
                        <div class="cd-feed-detail"><?php echo cd_e($row['detail']); ?></div>
                    <?php endif; ?>
                    <div class="cd-feed-who">
                        <?php echo cd_e($who); ?><?php echo $ip !== '' ? ' · ' . cd_e($ip) : ''; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="cd-foot-note">แสดง <?php echo count($rows); ?> รายการล่าสุด</div>
    <?php endif; ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
</div>
