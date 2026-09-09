<?php
/**
 * Post-it Notes Grid View Partial (app/views/backoffice/table/postit_table.php)
 */

$items = $data['items'] ?? $items ?? [];

if (!isset($colorMap)) {
    $colorMap = [
        'yellow' => ['bg' => '#FFFBEB', 'border' => '#FDE68A', 'text' => '#A16207'],
        'pink'   => ['bg' => '#FCE7F3', 'border' => '#F9A8D4', 'text' => '#BE185D'],
        'blue'   => ['bg' => '#DBEAFE', 'border' => '#93C5FD', 'text' => '#1D4ED8'],
        'green'  => ['bg' => '#DCFCE7', 'border' => '#86EFAC', 'text' => '#15803D'],
        'purple' => ['bg' => '#F3E8FF', 'border' => '#D8B4FE', 'text' => '#7E22CE'],
        'orange' => ['bg' => '#FFEDD5', 'border' => '#FDBA74', 'text' => '#C2410C'],
    ];
}

if (!isset($statusLabel)) {
    $statusLabel = static function (string $status, ?string $dueDate): array {
        if ($status === '1') {
            return ['text' => 'ดำเนินการแล้ว', 'class' => 'status-done'];
        }
        if ($dueDate && $dueDate < date('Y-m-d')) {
            return ['text' => 'เลยกำหนด', 'class' => 'status-overdue'];
        }
        return ['text' => 'รอดำเนินการ', 'class' => 'status-pending'];
    };
}

if (!isset($formatThaiDate)) {
    $formatThaiDate = static function (?string $datetime): string {
        if (!$datetime) {
            return '-';
        }
        $ts = strtotime($datetime);
        if (!$ts) {
            return '-';
        }
        $d = (int) date('j', $ts);
        $m = (int) date('n', $ts);
        $y = (int) date('Y', $ts) + 543;
        return "{$d}/{$m}/{$y}";
    };
}

if (!isset($displayName)) {
    $displayName = static function (array $item): string {
        $name = trim($item['assignee_name'] ?? '');
        if ($name !== '') {
            return $name;
        }
        $username = trim($item['assignee_username'] ?? '');
        if ($username !== '') {
            return $username;
        }
        return 'ยังไม่ระบุ';
    };
}
?>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3 mb-4" style="min-height: 180px;">
    <?php if (empty($items)): ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="ri-sticky-note-line fs-1 d-block mb-2 text-secondary"></i>
            <span>ยังไม่มี Post-it</span>
        </div>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
            <?php
                $colorKey = $item['color_code'] ?? 'yellow';
                $palette  = $colorMap[$colorKey] ?? $colorMap['yellow'];
                if (is_string($colorKey) && str_starts_with($colorKey, '#')) {
                    $palette = ['bg' => $colorKey, 'border' => $colorKey, 'text' => '#854d0e'];
                }
                $st = $statusLabel((string) ($item['status'] ?? '0'), $item['due_date'] ?? null);
            ?>
            <div class="col">
                <article class="postit-note"
                    style="background: <?php echo htmlspecialchars($palette['bg']); ?>; border-color: <?php echo htmlspecialchars($palette['border']); ?>;">
                    <div class="postit-actions">
                        <?php if (($item['status'] ?? '0') !== '1'): ?>
                            <button type="button" class="postit-action-btn btn-postit-done"
                                data-id="<?php echo (int) $item['post_id']; ?>" title="สลับสถานะ"
                                onclick="changeStatusPostIt(<?php echo (int) $item['post_id']; ?>)">
                                <i class="ri-check-line"></i>
                            </button>
                            <button type="button" class="postit-action-btn btn-postit-edit"
                                data-id="<?php echo (int) $item['post_id']; ?>"
                                data-title="<?php echo htmlspecialchars($item['title'] ?? ''); ?>"
                                data-user-id="<?php echo htmlspecialchars((string) ($item['user_id'] ?? '')); ?>"
                                data-due-date="<?php echo htmlspecialchars($item['due_date'] ?? ''); ?>"
                                data-status="<?php echo htmlspecialchars((string) ($item['status'] ?? '0')); ?>"
                                data-content="<?php echo htmlspecialchars($item['content'] ?? ''); ?>"
                                data-color="<?php echo htmlspecialchars($item['color_code'] ?? 'yellow'); ?>"
                                title="แก้ไข">
                                <i class="ri-pencil-line"></i>
                            </button>
                            <button type="button" class="postit-action-btn btn-postit-delete" title="ลบ"
                                onclick="delete_portit(<?php echo (int) $item['post_id']; ?>, '<?php echo htmlspecialchars($item['title'] ?? '', ENT_QUOTES); ?>')">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <h3 class="postit-note-title"><?php echo htmlspecialchars($item['title'] ?? ''); ?></h3>
                    <div class="postit-note-assignee">
                        <i class="ri-user-line"></i>
                        <span><?php echo htmlspecialchars($displayName($item)); ?></span>
                    </div>
                    <div class="postit-note-body" style="text-align: left; font-size: 12px; padding-top: 0px !important;">
                        <?php echo nl2br(htmlspecialchars($item['content'] ?? '')); ?>
                    </div>
                    <div class="postit-note-meta">
                        <i class="ri-time-line"></i>
                        <span>สร้างเมื่อ <?php echo htmlspecialchars($formatThaiDate($item['created_at'] ?? null)); ?></span>
                    </div>
                    <div class="<?php echo htmlspecialchars($st['class']); ?>">
                        <?php echo htmlspecialchars($st['text']); ?>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
