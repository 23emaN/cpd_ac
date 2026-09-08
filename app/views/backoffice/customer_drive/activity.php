<?php
// app/views/backoffice/customer_drive/activity.php
// Ported from _export_customer_drive/files/main/ajax/customer_drive/activity.php
// $customer_id/$userId are set by CustomerDriveController::activity().

$customer = cd_customer($customer_id);

if (!$customer) {
    echo '<div class="cd-modal"><div class="modal-header"><h5 class="modal-title">ความเคลื่อนไหว</h5>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body"><div class="alert alert-warning mb-0">ไม่พบข้อมูลลูกค้า</div></div></div>';
    exit;
}

$rows = cd_activity_feed($customer_id, 200);
?>
<div class="cd-modal">
<div class="modal-header">
    <div class="d-flex align-items-start gap-2">
        <i class="cd-ic ri-history-line"></i>
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
            <i class="cd-ic cd-empty-icon ri-history-line"></i>
            <div class="cd-empty-title">ยังไม่มีความเคลื่อนไหวในคลังนี้</div>
        </div>
    <?php else: ?>
        <?php
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
</div>
