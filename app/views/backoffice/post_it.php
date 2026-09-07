<?php
// app/views/backoffice/post_it.php
$show_company_workspace = true;
$stats = $data['stats'] ?? ['total' => 0, 'pending' => 0, 'done' => 0, 'overdue' => 0];
$items = $data['items'] ?? [];
$assignees = $data['assignees'] ?? [];
$filters = $data['filters'] ?? [];

$isDraft = !empty($data['is_draft']);
$base = defined('BASE_URL') ? BASE_URL : '/cpd_ac/public';

$colorMap = [
    'yellow' => ['bg' => '#FFFBEB', 'border' => '#FDE68A', 'text' => '#A16207'],
    'pink' => ['bg' => '#FCE7F3', 'border' => '#F9A8D4', 'text' => '#BE185D'],
    'blue' => ['bg' => '#DBEAFE', 'border' => '#93C5FD', 'text' => '#1D4ED8'],
    'green' => ['bg' => '#DCFCE7', 'border' => '#86EFAC', 'text' => '#15803D'],
    'purple' => ['bg' => '#F3E8FF', 'border' => '#D8B4FE', 'text' => '#7E22CE'],
    'orange' => ['bg' => '#FFEDD5', 'border' => '#FDBA74', 'text' => '#C2410C'],
];

$statusLabel = static function (string $status, ?string $dueDate): array {
    if ($status === '1') {
        return ['text' => 'ดำเนินการแล้ว', 'class' => 'status-done'];
    }
    if ($dueDate && $dueDate < date('Y-m-d')) {
        return ['text' => 'เลยกำหนด', 'class' => 'status-overdue'];
    }
    return ['text' => 'รอดำเนินการ', 'class' => 'status-pending'];
};

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

require_once dirname(__DIR__) . '/main/header.php';
require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<style>
    .stat-icon.red {
        background-color: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    /* --- Select2 Custom Styling for Header Filter Group --- */
    .filter-group .select2-container {
        min-width: 130px;
    }

    .select2-container--default .select2-selection--single {
        background-color: #f8fafc !important;
        border: 1px solid #f1f5f9 !important;
        border-radius: 10px !important;
        height: 40px !important;
        display: flex !important;
        align-items: center !important;
        transition: all 0.2s ease !important;
        box-shadow: none !important;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--open .select2-selection--single {
        background-color: #ffffff !important;
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #334155 !important;
        font-size: 0.84rem !important;
        font-weight: 600 !important;
        padding-left: 14px !important;
        padding-right: 32px !important;
        line-height: 38px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        width: 28px !important;
        right: 8px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .select2-dropdown {
        border: 1px solid #edf2f7 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08) !important;
        overflow: hidden !important;
        z-index: 9999 !important;
        font-size: 0.84rem !important;
        background-color: #ffffff !important;
    }

    .select2-container--default .select2-results__option {
        padding: 8px 14px !important;
        font-weight: 500 !important;
        color: #475569 !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #2563eb !important;
        color: #ffffff !important;
    }

    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #eff6ff !important;
        color: #1d4ed8 !important;
        font-weight: 700 !important;
    }

    .select2-search--dropdown {
        padding: 8px 10px !important;
    }

    .select2-search--dropdown .select2-search__field {
        border: 1px solid #e2e8f0 !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        outline: none !important;
        font-size: 0.84rem !important;
    }

    /* Sticky Note Card */
    .postit-note {
        border-radius: 16px;
        border: 1px solid #fde68a;
        background: #fffbeb;
        padding: 20px;
        min-height: 180px;
        display: flex;
        flex-direction: column;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .postit-note {
        position: relative;
    }

    .postit-note:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
    }

    .postit-actions {
        position: absolute;
        top: 14px;
        right: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
        z-index: 10;
    }

    .postit-note:hover .postit-actions {
        opacity: 1;
        visibility: visible;
    }

    .postit-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.15s ease;
        padding: 0;
        line-height: 1;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .postit-action-btn:hover {
        transform: scale(1.08);
    }

    .btn-postit-done {
        background-color: #dcfce7;
        color: #16a34a;
    }

    .btn-postit-done:hover {
        background-color: #bbf7d0;
        color: #15803d;
    }

    .btn-postit-done.active {
        background-color: #16a34a;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(22, 163, 74, 0.35);
    }

    .btn-postit-done.active:hover {
        background-color: #15803d;
        color: #ffffff;
    }

    .btn-postit-edit {
        background-color: #ffffff;
        color: #334155;
    }

    .btn-postit-edit:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }

    .btn-postit-delete {
        background-color: #fce7f3;
        color: #ef4444;
    }

    .btn-postit-delete:hover {
        background-color: #fbcfe8;
        color: #dc2626;
    }

    .postit-note-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 6px;
        padding-right: 105px;
    }

    .postit-note-assignee {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 12px;
    }

    .postit-note-body {
        font-size: 0.9rem;
        color: #334155;
        line-height: 1.5;
        flex: 1;
        margin-bottom: 16px;
        margin-left: 20px;
        /* white-space: pre-wrap; */
    }

    .postit-note-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.8rem;
        color: #94a3b8;
        margin-bottom: 8px;
    }

    .status-pending {
        color: #d97706;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .status-done {
        color: #16a34a;
        font-weight: 700;
        font-size: 0.85rem;
    }

    .status-overdue {
        color: #dc2626;
        font-weight: 700;
        font-size: 0.85rem;
    }

    /* Datepicker Clear button & wrap */
    .postit-date-wrap {
        position: relative;
    }

    .postit-date-wrap .form-control {
        padding-right: 44px;
    }

    .postit-date-wrap.has-date .form-control {
        padding-right: 78px;
    }

    .postit-date-wrap i {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
        font-size: 1.05rem;
        z-index: 2;
    }

    .postit-date-wrap.has-date i {
        right: 48px;
    }

    .postit-date-clear {
        display: none;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 600;
        padding: 0;
        z-index: 3;
        line-height: 1;
    }

    .postit-date-wrap.has-date .postit-date-clear {
        display: inline-block;
    }

    .postit-date-clear:hover {
        color: #2563eb;
    }

    /* Flatpickr Custom Styling */
    .flatpickr-calendar.postit-calendar {
        border: none;
        border-radius: 16px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.14);
        padding: 12px 12px 14px;
        width: 278px;
        z-index: 2000;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-months {
        align-items: center;
        margin-bottom: 8px;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-current-month {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-day {
        border-radius: 8px;
        border: none;
        color: #334155;
        font-weight: 500;
        height: 36px;
        line-height: 36px;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-day.selected {
        background: #e2e8f0;
        color: #1e293b;
    }

    /* Color picker radio buttons */
    .postit-color-list {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .postit-color-item {
        position: relative;
        width: 28px;
        height: 28px;
        margin: 0;
        cursor: pointer;
    }

    .postit-color-item input {
        position: absolute;
        opacity: 0;
        inset: 0;
        cursor: pointer;
    }

    .postit-color-dot {
        display: block;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid transparent;
        box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.25);
        cursor: pointer;
    }

    .postit-color-item input:checked+.postit-color-dot {
        box-shadow: 0 0 0 2px #fff, 0 0 0 4px #2563eb;
    }

    /* Form validation error */
    .postit-field-wrap {
        position: relative;
    }

    .postit-field-error-icon {
        display: none;
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #ef4444;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        line-height: 20px;
        text-align: center;
        pointer-events: none;
    }

    .postit-field-error-text {
        display: none;
        margin: 6px 0 0;
        font-size: 0.84rem;
        font-weight: 600;
        color: #ef4444;
    }

    .postit-field-wrap.is-invalid .form-control {
        border-color: #fecaca;
        background: #fff;
    }

    .postit-field-wrap.is-invalid .postit-field-error-icon,
    .postit-field-wrap.is-invalid+.postit-field-error-text {
        display: block;
    }

    .filter-toolbar {
        justify-content: start !;
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">

                <!-- Master Card Wrapper (Inherited from header.php) -->
                <div class="main-card-wrapper">

                    <!-- Header Box (Inherited from header.php) -->
                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title">Post-it แจ้งเตือน</h2>
                            <p class="page-subtitle">ภาพรวมระบบ • Post-it แจ้งเตือน</p>
                        </div>
                        <a href="javascript:void(0);" class="btn-add-action" id="btnCreatePostIt" data-bs-toggle="modal"
                            data-bs-target="#createPostItModal">
                            <i class="ri-add-line"></i> สร้าง Post-it
                        </a>
                    </div>

                    <!-- Stats Grid (Inherited from header.php) -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue"><i class="ri-sticky-note-line"></i></div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo (int) $stats['total']; ?></span>
                                <span class="stat-label">Post-it ทั้งหมด</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon yellow"><i class="ri-time-line"></i></div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo (int) $stats['pending']; ?></span>
                                <span class="stat-label">รอดำเนินการ</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green"><i class="ri-checkbox-circle-line"></i></div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo (int) $stats['done']; ?></span>
                                <span class="stat-label">ดำเนินการแล้ว</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon red"><i class="ri-calendar-close-line"></i></div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo (int) $stats['overdue']; ?></span>
                                <span class="stat-label">เลยกำหนด</span>
                            </div>
                        </div>
                    </div>

                    <!-- Inner Board Container Card (Inherited from header.php) -->
                    <div class="table-container-card p-3 p-md-4">
                        <div class="mb-3">
                            <h3 class="page-title fs-5">บอร์ด Post-it</h3>
                            <p class="page-subtitle">ทั้งหมด <?php echo count($items); ?> รายการ</p>
                        </div>

                        <!-- Filter Toolbar (Inherited from header.php) -->
                        <form method="get" action="<?php echo htmlspecialchars($base); ?>/post_it" id="postitFilterForm"
                            class="filter-toolbar mb-4">
                            <div class="search-box-wrap">
                                <i class="ri-search-line"></i>
                                <input type="text" class="search-input" name="q"
                                    value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>"
                                    placeholder="ค้นหาหัวข้อ ข้อความ ผู้รับผิดชอบ">
                            </div>

                            <div class="filter-group">
                                <select class="filter-select select2" name="user_id">
                                    <option value="">ทุกผู้รับผิดชอบ</option>
                                    <?php foreach ($assignees as $person): ?>
                                        <?php
                                        $label = trim($person['full_name'] ?? '');
                                        if ($label === '') {
                                            $label = $person['user_name'] ?? '';
                                        }
                                        ?>
                                        <option value="<?php echo htmlspecialchars((string) ($person['user_id'] ?? '')); ?>"
                                            <?php echo ((string) ($filters['user_id'] ?? '') === (string) ($person['user_id'] ?? '')) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <select class="filter-select select2" name="status">
                                    <option value="">ทุกสถานะ</option>
                                    <option value="0" <?php echo (($filters['status'] ?? '') === '0') ? 'selected' : ''; ?>>รอดำเนินการ</option>
                                    <option value="1" <?php echo (($filters['status'] ?? '') === '1') ? 'selected' : ''; ?>>ดำเนินการแล้ว</option>
                                </select>

                                <select class="filter-select select2" name="color">
                                    <option value="">ทุกสี</option>
                                    <option value="yellow" <?php echo (($filters['color_code'] ?? '') === 'yellow') ? 'selected' : ''; ?>>เหลือง</option>
                                    <option value="pink" <?php echo (($filters['color_code'] ?? '') === 'pink') ? 'selected' : ''; ?>>ชมพู</option>
                                    <option value="blue" <?php echo (($filters['color_code'] ?? '') === 'blue') ? 'selected' : ''; ?>>ฟ้า</option>
                                    <option value="green" <?php echo (($filters['color_code'] ?? '') === 'green') ? 'selected' : ''; ?>>เขียว</option>
                                    <option value="purple" <?php echo (($filters['color_code'] ?? '') === 'purple') ? 'selected' : ''; ?>>ม่วง</option>
                                    <option value="orange" <?php echo (($filters['color_code'] ?? '') === 'orange') ? 'selected' : ''; ?>>ส้ม</option>
                                </select>

                                <select class="filter-select select2" name="due">
                                    <option value="">ทุกกำหนดส่ง</option>
                                    <option value="today" <?php echo (($filters['due'] ?? '') === 'today') ? 'selected' : ''; ?>>วันนี้</option>
                                    <option value="week" <?php echo (($filters['due'] ?? '') === 'week') ? 'selected' : ''; ?>>สัปดาห์นี้</option>
                                    <option value="overdue" <?php echo (($filters['due'] ?? '') === 'overdue') ? 'selected' : ''; ?>>เลยกำหนด</option>
                                    <option value="none" <?php echo (($filters['due'] ?? '') === 'none') ? 'selected' : ''; ?>>ไม่มีกำหนด</option>
                                </select>

                                <select class="filter-select select2" name="sort">
                                    <option value="created_desc" <?php echo (($filters['sort'] ?? '') === 'created_desc') ? 'selected' : ''; ?>>เรียงตามวันที่สร้าง</option>
                                    <option value="created_asc" <?php echo (($filters['sort'] ?? '') === 'created_asc') ? 'selected' : ''; ?>>วันที่สร้างเก่าสุด</option>
                                    <option value="due_asc" <?php echo (($filters['sort'] ?? '') === 'due_asc') ? 'selected' : ''; ?>>กำหนดส่งใกล้สุด</option>
                                    <option value="due_desc" <?php echo (($filters['sort'] ?? '') === 'due_desc') ? 'selected' : ''; ?>>กำหนดส่งไกลสุด</option>
                                </select>
                            </div>


                        </form>

                        <!-- Post-it Notes Grid -->
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3 mb-4"
                            style="min-height: 180px;">
                            <?php if (empty($items)): ?>
                                <div class="col-12 text-center py-5 text-muted">
                                    <i class="ri-sticky-note-line fs-1 d-block mb-2 text-secondary"></i>
                                    <span>ยังไม่มี Post-it</span>
                                </div>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <?php
                                    $colorKey = $item['color_code'] ?? 'yellow';
                                    $palette = $colorMap[$colorKey] ?? $colorMap['yellow'];
                                    if (is_string($colorKey) && str_starts_with($colorKey, '#')) {
                                        $palette = ['bg' => $colorKey, 'border' => $colorKey, 'text' => '#854d0e'];
                                    }
                                    $st = $statusLabel((string) ($item['status'] ?? '0'), $item['due_date'] ?? null);
                                    ?>
                                    <div class="col">
                                        <article class="postit-note"
                                            style="background: <?php echo htmlspecialchars($palette['bg']); ?>; border-color: <?php echo htmlspecialchars($palette['border']); ?>;">
                                            <div class="postit-actions">
                                                <button type="button" class="postit-action-btn btn-postit-done <?php echo $item['status'] === '1' ? 'active' : ''; ?>"
                                                    data-id="<?php echo (int) $item['post_id']; ?>" title="สลับสถานะ"
                                                    onclick="changeStatusPostIt(<?php echo (int) $item['post_id']; ?>)">
                                                    <i class="ri-check-line"></i>
                                                </button>
                                                <?php if (($item['status'] ?? '0') !== '1'): ?>
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
                                                <button type="button" class="postit-action-btn btn-postit-delete"
                                                    data-id="<?php echo (int) $item['post_id']; ?>" title="ลบ">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                            <h3 class="postit-note-title"><?php echo htmlspecialchars($item['title'] ?? ''); ?>
                                            </h3>
                                            <div class="postit-note-assignee">
                                                <i class="ri-user-line"></i>
                                                <span><?php echo htmlspecialchars($displayName($item)); ?></span>
                                            </div>
                                            <div class="postit-note-body" style="text-align: left; font-size: 12px; padding-top: 0px !important;">
                                                <?php echo nl2br(htmlspecialchars($item['content'] ?? '')); ?>
                                            </div>
                                            <div class="postit-note-meta">
                                                <i class="ri-time-line"></i>
                                                <span>สร้างเมื่อ
                                                    <?php echo htmlspecialchars($formatThaiDate($item['created_at'] ?? null)); ?></span>
                                            </div>
                                            <div class="<?php echo htmlspecialchars($st['class']); ?>">
                                                <?php echo htmlspecialchars($st['text']); ?>
                                            </div>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>


                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Post-It Modal (Using Keen/Metronic standard modal classes from header.php) -->
<div class="modal fade" id="createPostItModal" tabindex="-1" aria-labelledby="createPostItModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-600px">
        <div class="modal-content modal-content-keen">
            <div class="modal-header modal-header-keen">
                <h4 class="modal-title modal-title-keen" id="createPostItModalLabel">สร้าง Post-it ใหม่</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createPostItForm" autocomplete="off" novalidate>
                <input type="hidden" id="postitId" name="post_id" value="">
                <div class="modal-body modal-body-keen">
                    <div class="mb-4">
                        <label class="form-label-keen required" for="postitTitle">หัวข้อ</label>
                        <div class="postit-field-wrap" id="postitTitleWrap">
                            <input type="text" class="form-control form-control-solid" id="postitTitle" name="title"
                                placeholder="หัวข้อ Post-it">
                            <span class="postit-field-error-icon">!</span>
                        </div>
                        <p class="postit-field-error-text">กรุณาระบุหัวข้อ Post-it</p>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label-keen" for="postitAssignee">ผู้รับผิดชอบ</label>
                            <select class="form-select form-select-solid select2-modal" id="postitAssignee"
                                name="user_id">
                                <option value="">ยังไม่ระบุผู้รับผิดชอบ</option>
                                <?php foreach ($assignees as $person): ?>
                                    <?php
                                    $personId = (string) ($person['user_id'] ?? '');
                                    if ($personId === '' || $personId === '0') {
                                        continue;
                                    }
                                    $label = trim($person['full_name'] ?? '');
                                    if ($label === '') {
                                        $label = $person['user_name'] ?? '';
                                    }
                                    ?>
                                    <option value="<?php echo htmlspecialchars($personId); ?>">
                                        <?php echo htmlspecialchars($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-keen" for="postitDueDate">กำหนดส่ง</label>
                            <div class="postit-date-wrap" id="postitDateWrap">
                                <input type="text" class="form-control form-control-solid" id="postitDueDate"
                                    name="due_date" value="" placeholder="เลือกวันที่" readonly>
                                <i class="ri-calendar-line"></i>
                                <button type="button" class="postit-date-clear" id="postitDueDateClear">ล้าง</button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-keen" for="postitStatus">สถานะ</label>
                        <select class="form-select form-select-solid select2-modal" id="postitStatus" name="status">
                            <option value="0" selected>รอดำเนินการ</option>
                            <option value="1">ดำเนินการแล้ว</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label-keen" for="postitContent">ข้อความ</label>
                        <textarea class="form-control form-control-solid" id="postitContent" name="content" rows="4"
                            placeholder="รายละเอียด..."></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label-keen">สี</label>
                        <div class="postit-color-list">
                            <label class="postit-color-item">
                                <input type="radio" name="color_code" value="yellow" checked>
                                <span class="postit-color-dot" style="background:#FDE68A;"></span>
                            </label>
                            <label class="postit-color-item">
                                <input type="radio" name="color_code" value="pink">
                                <span class="postit-color-dot" style="background:#F9A8D4;"></span>
                            </label>
                            <label class="postit-color-item">
                                <input type="radio" name="color_code" value="blue">
                                <span class="postit-color-dot" style="background:#93C5FD;"></span>
                            </label>
                            <label class="postit-color-item">
                                <input type="radio" name="color_code" value="green">
                                <span class="postit-color-dot" style="background:#86EFAC;"></span>
                            </label>
                            <label class="postit-color-item">
                                <input type="radio" name="color_code" value="purple">
                                <span class="postit-color-dot" style="background:#D8B4FE;"></span>
                            </label>
                            <label class="postit-color-item">
                                <input type="radio" name="color_code" value="orange">
                                <span class="postit-color-dot" style="background:#FDBA74;"></span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-keen">
                    <button type="button" class="btn-light-keen btn-postit-cancel"
                        data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn-primary-keen btn-postit-save">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/main/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
<script>
    // Select2 Initialization
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function ($) {
            if (typeof $.fn.select2 === 'function') {
                $('.select2').select2({
                    width: 'auto'
                }).on('change', function () {
                    if (this.form) {
                        this.form.submit();
                    }
                });

                $('#createPostItModal .select2-modal').select2({
                    dropdownParent: $('#createPostItModal'),
                    width: '100%'
                });
            }
        });
    }

    document.querySelector('.search-input[name="q"]')?.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('postitFilterForm').submit();
        }
    });

    const postitTitleInput = document.getElementById('postitTitle');
    const postitTitleWrap = document.getElementById('postitTitleWrap');

    function setPostItTitleError(show) {
        postitTitleWrap?.classList.toggle('is-invalid', show);
    }

    postitTitleInput?.addEventListener('input', function () {
        if (this.value.trim()) {
            setPostItTitleError(false);
        }
    });

    let postitPicker = null;

    if (typeof flatpickr === 'function') {
        const dateWrap = document.getElementById('postitDateWrap');
        const clearBtn = document.getElementById('postitDueDateClear');

        function togglePostItClear(hasDate) {
            dateWrap?.classList.toggle('has-date', !!hasDate);
        }

        postitPicker = flatpickr('#postitDueDate', {
            locale: 'th',
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            allowInput: false,
            disableMobile: true,
            appendTo: document.body,
            monthSelectorType: 'static',
            onReady: function (selectedDates, dateStr, instance) {
                instance.calendarContainer.classList.add('postit-calendar');
                if (instance.altInput) {
                    instance.altInput.className = 'form-control form-control-solid';
                    instance.altInput.setAttribute('placeholder', 'เลือกวันที่');
                }
                togglePostItClear(selectedDates.length > 0);
            },
            onChange: function (selectedDates) {
                togglePostItClear(selectedDates.length > 0);
            },
            onOpen: function (selectedDates, dateStr, instance) {
                const cal = instance.calendarContainer;
                const modal = document.querySelector('#createPostItModal .modal-content');
                if (!cal || !modal) return;
                requestAnimationFrame(function () {
                    const modalRect = modal.getBoundingClientRect();
                    const calRect = cal.getBoundingClientRect();
                    const overflow = calRect.right - (modalRect.right - 16);
                    if (overflow > 0) {
                        const currentLeft = parseFloat(cal.style.left || '0');
                        cal.style.left = (currentLeft - overflow) + 'px';
                    }
                });
            }
        });

        clearBtn?.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            postitPicker.clear();
            togglePostItClear(false);
        });
    }

    document.getElementById('createPostItModal')?.addEventListener('hidden.bs.modal', function () {
        setPostItTitleError(false);
        document.getElementById('postitId').value = '';
        document.getElementById('createPostItModalLabel').textContent = 'สร้าง Post-it ใหม่';
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 === 'function') {
            $('#createPostItModal .select2-modal').val('').trigger('change.select2');
            $('#postitStatus').val('0').trigger('change.select2');
        }
    });

    // Handle Create Button click to reset modal
    document.getElementById('btnCreatePostIt')?.addEventListener('click', function () {
        document.getElementById('createPostItForm')?.reset();
        document.getElementById('postitId').value = '';
        document.getElementById('createPostItModalLabel').textContent = 'สร้าง Post-it ใหม่';
        document.querySelector('input[name="color_code"][value="yellow"]')?.click();
        if (postitPicker) postitPicker.clear();
        document.getElementById('postitDateWrap')?.classList.remove('has-date');
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 === 'function') {
            $('#createPostItModal .select2-modal').val('').trigger('change.select2');
            $('#postitStatus').val('0').trigger('change.select2');
        }
    });

    // Handle Edit Button Click
    document.querySelectorAll('.btn-postit-edit').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const id = this.dataset.id;
            const title = this.dataset.title || '';
            const userId = this.dataset.userId || '';
            const dueDate = this.dataset.dueDate || '';
            const status = this.dataset.status || '0';
            const content = this.dataset.content || '';
            const color = this.dataset.color || 'yellow';

            document.getElementById('postitId').value = id;
            document.getElementById('createPostItModalLabel').textContent = 'แก้ไข Post-it';
            if (postitTitleInput) postitTitleInput.value = title;
            document.getElementById('postitContent').value = content;

            // Set Color Radio
            const colorRadio = document.querySelector('input[name="color_code"][value="' + color + '"]');
            if (colorRadio) {
                colorRadio.checked = true;
            }

            // Set Select2 values
            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 === 'function') {
                $('#postitAssignee').val(userId).trigger('change.select2');
                $('#postitStatus').val(status).trigger('change.select2');
            } else {
                if (document.getElementById('postitAssignee')) document.getElementById('postitAssignee').value = userId;
                if (document.getElementById('postitStatus')) document.getElementById('postitStatus').value = status;
            }

            // Set Flatpickr Date
            if (postitPicker) {
                if (dueDate) {
                    postitPicker.setDate(dueDate, true);
                    togglePostItClear(true);
                } else {
                    postitPicker.clear();
                    togglePostItClear(false);
                }
            } else {
                const dateInput = document.getElementById('postitDueDate');
                if (dateInput) dateInput.value = dueDate;
            }

            // Open Modal
            const modalElement = document.getElementById('createPostItModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                modalInstance.show();
            }
        });
    });

    // Handle Done (Toggle Status) Button Click
    document.querySelectorAll('.btn-postit-done').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const postId = this.dataset.id;
            if (!postId) return;

            const formData = new FormData();
            formData.append('post_id', postId);

            fetch('<?php echo htmlspecialchars($base); ?>/post_it/toggle_status', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (res) { return res.json(); })
                .then(function (response) {
                    if (response.result === 1) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.msg || 'อัปเดตสถานะสำเร็จ',
                                showConfirmButton: false,
                                timer: 1200
                            }).then(function () { location.reload(); });
                        } else {
                            location.reload();
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: response.msg || 'เกิดข้อผิดพลาด', showConfirmButton: false, timer: 2000 });
                        }
                    }
                });
        });
    });

    // Handle Delete Button Click
    document.querySelectorAll('.btn-postit-delete').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const postId = this.dataset.id;
            if (!postId) return;

            const doDelete = function () {
                const formData = new FormData();
                formData.append('post_id', postId);

                fetch('<?php echo htmlspecialchars($base); ?>/post_it/delete', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (response) {
                        if (response.result === 1) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: response.msg || 'ลบ Post-it สำเร็จ',
                                    showConfirmButton: false,
                                    timer: 1200
                                }).then(function () { location.reload(); });
                            } else {
                                location.reload();
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: response.msg || 'เกิดข้อผิดพลาด', showConfirmButton: false, timer: 2000 });
                            }
                        }
                    });
            };

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยืนยันการลบ Post-it?',
                    text: "คุณจะไม่สามารถย้อนกลับรายการนี้ได้!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'ใช่, ลบเลย!',
                    cancelButtonText: 'ยกเลิก'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        doDelete();
                    }
                });
            } else {
                if (confirm('คุณต้องการลบ Post-it นี้ใช่หรือไม่?')) {
                    doDelete();
                }
            }
        });
    });

    document.getElementById('createPostItForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        const form = this;
        const title = postitTitleInput?.value.trim();
        if (!title) {
            setPostItTitleError(true);
            postitTitleInput?.focus();
            return;
        }
        setPostItTitleError(false);

        const submitBtn = form.querySelector('.btn-postit-save');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'กำลังบันทึก...';
        }

        const formData = new FormData(form);
        const postId = document.getElementById('postitId')?.value;
        const targetUrl = postId ? '<?php echo htmlspecialchars($base); ?>/post_it/update' : '<?php echo htmlspecialchars($base); ?>/post_it/store';

        fetch(targetUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (res) { return res.json(); })
            .then(function (response) {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'บันทึกข้อมูล';
                }

                if (response.result === 1) {
                    const modalElement = document.getElementById('createPostItModal');
                    if (modalElement && typeof bootstrap !== 'undefined') {
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) modalInstance.hide();
                    }

                    form.reset();
                    document.getElementById('postitId').value = '';
                    document.querySelector('input[name="color_code"][value="yellow"]')?.click();
                    if (postitPicker) postitPicker.clear();
                    document.getElementById('postitDateWrap')?.classList.remove('has-date');

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: response.msg || 'บันทึกสำเร็จ',
                            showConfirmButton: false,
                            timer: 1500,
                            timerProgressBar: true
                        }).then(function () {
                            location.reload();
                        });
                    } else {
                        alert(response.msg || 'บันทึกสำเร็จ');
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: response.msg || 'บันทึกไม่สำเร็จ',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        alert(response.msg || 'บันทึกไม่สำเร็จ');
                    }
                }
            })
            .catch(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'บันทึกข้อมูล';
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    alert('เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ');
                }
            });
    });

    function changeStatusPostIt(id){
        const formData = new FormData();
        formData.append('post_id', id);

        fetch('<?php echo BASE_URL; ?>/post_it/toggle', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(response => {
            if (response.result === 1) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: response.msg || 'เปลี่ยนสถานะสำเร็จ',
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                }).then(function () { location.reload(); });
            } else {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: response.msg || 'เกิดข้อผิดพลาด',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        })
        .catch(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'เชื่อมต่อเซิร์ฟเวอร์ไม่สำเร็จ',
                showConfirmButton: false,
                timer: 3000
            });
        });
    }
</script>
