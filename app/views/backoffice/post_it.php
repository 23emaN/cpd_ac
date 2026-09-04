<?php
// app/views/backoffice/post_it.php
$show_company_workspace = true;
$stats = $data['stats'] ?? ['total' => 0, 'pending' => 0, 'done' => 0, 'overdue' => 0];
$items = $data['items'] ?? [];
$assignees = $data['assignees'] ?? [];
$filters = $data['filters'] ?? [];
$pagination = $data['pagination'] ?? [
    'page' => 1,
    'per_page' => 25,
    'total' => 0,
    'total_pages' => 1,
    'from' => 0,
    'to' => 0,
];
$isDraft = !empty($data['is_draft']);
$base = defined('BASE_URL') ? BASE_URL : '/cpd_ac/public';

$colorMap = [
    'yellow' => ['bg' => '#FFF8DC', 'border' => '#F5D76E', 'text' => '#A16207'],
    'pink' => ['bg' => '#FCE7F3', 'border' => '#F9A8D4', 'text' => '#BE185D'],
    'blue' => ['bg' => '#DBEAFE', 'border' => '#93C5FD', 'text' => '#1D4ED8'],
    'green' => ['bg' => '#DCFCE7', 'border' => '#86EFAC', 'text' => '#15803D'],
    'purple' => ['bg' => '#F3E8FF', 'border' => '#D8B4FE', 'text' => '#7E22CE'],
    'orange' => ['bg' => '#FFEDD5', 'border' => '#FDBA74', 'text' => '#C2410C'],
];

$statusLabel = static function (string $status, ?string $dueDate): array {
    if ($status === '1') {
        return ['text' => 'ดำเนินการแล้ว', 'class' => 'done'];
    }
    if ($dueDate && $dueDate < date('Y-m-d')) {
        return ['text' => 'เลยกำหนด', 'class' => 'overdue'];
    }
    return ['text' => 'รอดำเนินการ', 'class' => 'pending'];
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
    .content-wrapper {
        padding: 24px 32px 32px;
        min-height: calc(100vh - 140px);
        font-family: 'Kanit', 'Segoe UI', Tahoma, sans-serif;
        background: #f8fafc;
    }

    .postit-page-card {
        background: #ffffff;
        border: 1px solid #eef2f7;
        border-radius: 16px;
        padding: 22px 24px 24px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .postit-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 0 0 22px;
        padding: 0;
        background: transparent;
        border: none;
        border-radius: 0;
        box-shadow: none;
    }

    .postit-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 4px;
        letter-spacing: -0.2px;
    }

    .postit-breadcrumb {
        margin: 0;
        font-size: 0.84rem;
        color: #64748b;
    }

    .btn-create-postit {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        font-size: 0.92rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
        white-space: nowrap;
    }

    .btn-create-postit:hover {
        background: #1d4ed8;
        color: #fff;
    }

    .postit-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .postit-stat-card {
        background: #ffffff !important;
        border: 1px solid #eef2f7;
        border-radius: 14px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }

    .postit-stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

    .postit-stat-icon.total { background: #eff6ff; color: #2563eb; }
    .postit-stat-icon.pending { background: #fefce8; color: #ca8a04; }
    .postit-stat-icon.done { background: #f0fdf4; color: #16a34a; }
    .postit-stat-icon.overdue { background: #fef2f2; color: #dc2626; }

    .postit-stat-value {
        font-size: 1.55rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
    }

    .postit-stat-label {
        font-size: 0.82rem;
        color: #64748b;
        margin-top: 2px;
    }

    .postit-board-card {
        background: #ffffff !important;
        border: 1px solid #e8eef5;
        border-radius: 16px;
        box-shadow: none;
        padding: 20px 20px 16px;
    }

    .postit-board-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .postit-board-heading {
        flex: 0 0 auto;
        min-width: 140px;
    }

    .postit-board-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 2px;
    }

    .postit-board-sub {
        margin: 0;
        font-size: 0.82rem;
        color: #64748b;
    }

    .postit-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
        margin-bottom: 0;
        flex: 1 1 520px;
    }

    .postit-search {
        position: relative;
        flex: 1 1 180px;
        min-width: 160px;
        max-width: 260px;
    }

    .postit-search i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1rem;
    }

    .postit-search input,
    .postit-filters select {
        height: 40px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #fff;
        color: #334155;
        font-size: 0.86rem;
        outline: none;
    }

    .postit-search input {
        width: 100%;
        padding: 0 12px 0 36px;
    }

    .postit-filters select {
        padding: 0 34px 0 12px;
        min-width: 118px;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
    }

    .postit-search input:focus,
    .postit-filters select:focus {
        border-color: #93c5fd;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .postit-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 16px;
        min-height: 160px;
    }

    .postit-note {
        border-radius: 12px;
        border: 1px solid #f5d76e;
        background: #fff8dc;
        padding: 16px 16px 14px;
        min-height: 170px;
        display: flex;
        flex-direction: column;
        box-shadow: 0 2px 8px rgba(161, 98, 7, 0.08);
    }

    .postit-note-title {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 8px;
    }

    .postit-note-assignee {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.84rem;
        color: #475569;
        margin-bottom: 10px;
    }

    .postit-note-assignee i {
        font-size: 0.95rem;
        color: #64748b;
    }

    .postit-note-body {
        font-size: 0.9rem;
        color: #334155;
        line-height: 1.45;
        flex: 1;
        margin-bottom: 12px;
        white-space: pre-wrap;
    }

    .postit-note-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #64748b;
        margin-bottom: 8px;
    }

    .postit-note-status {
        font-size: 0.82rem;
        font-weight: 600;
    }

    .postit-note-status.pending { color: #ca8a04; }
    .postit-note-status.done { color: #16a34a; }
    .postit-note-status.overdue { color: #dc2626; }

    .postit-empty {
        grid-column: 1 / -1;
        text-align: center;
        padding: 48px 16px;
        color: #94a3b8;
    }

    .postit-draft-badge {
        display: inline-block;
        margin-left: 8px;
        padding: 2px 8px;
        border-radius: 999px;
        background: #fff7ed;
        color: #c2410c;
        font-size: 0.72rem;
        font-weight: 600;
        vertical-align: middle;
    }

    .postit-pager {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 20px;
        padding-top: 14px;
        border-top: 1px solid #f1f5f9;
        font-size: 0.84rem;
        color: #64748b;
    }

    .postit-pager-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .postit-pager-left select {
        height: 34px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 0 28px 0 10px;
        background: #fff;
        color: #334155;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
    }

    .postit-pager-nav {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .postit-page-btn {
        min-width: 34px;
        height: 34px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        color: #475569;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 0.84rem;
        font-weight: 600;
    }

    .postit-page-btn.active {
        background: #2563eb;
        border-color: #2563eb;
        color: #fff;
    }

    .postit-page-btn.disabled {
        opacity: 0.45;
        pointer-events: none;
    }

    @media (max-width: 1100px) {
        .postit-stats-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .content-wrapper {
            padding: 18px 16px 24px;
        }

        .postit-header-row {
            flex-direction: column;
            align-items: stretch;
            margin: 0 0 18px;
            padding: 0;
        }

        .postit-filters {
            justify-content: flex-start;
        }

        .postit-search {
            max-width: none;
        }

        .postit-stats-grid {
            grid-template-columns: 1fr;
        }
    }

    #createPostItModal .modal-content {
        border: none;
        border-radius: 16px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.16);
    }

    #createPostItModal .modal-header {
        border-bottom: 1px solid #f1f5f9;
        padding: 18px 24px;
    }

    #createPostItModal .modal-title {
        font-size: 1.12rem;
        font-weight: 800;
        color: #1e293b;
    }

    #createPostItModal .modal-body {
        padding: 22px 24px 8px;
    }

    #createPostItModal .modal-footer {
        border-top: 1px solid #f1f5f9;
        padding: 16px 24px 20px;
        gap: 10px;
    }

    .postit-form-label {
        display: block;
        font-size: 0.9rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }

    .postit-form-control,
    .postit-form-select,
    .postit-form-textarea {
        width: 100%;
        background: #f8fafc;
        border: 1px solid #eef2f7;
        border-radius: 10px;
        color: #334155;
        font-size: 0.92rem;
        box-shadow: none;
        outline: none;
    }

    .postit-form-control,
    .postit-form-select {
        height: 44px;
        padding: 0 14px;
    }

    .postit-form-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 36px;
    }

    .postit-form-textarea {
        min-height: 118px;
        padding: 12px 14px;
        resize: vertical;
    }

    .postit-form-control:focus,
    .postit-form-select:focus,
    .postit-form-textarea:focus {
        border-color: #93c5fd;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }

    .postit-date-wrap {
        position: relative;
    }

    .postit-date-wrap .postit-form-control {
        padding-right: 44px;
    }

    .postit-date-wrap.has-date .postit-form-control {
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

    .postit-date-wrap .flatpickr-input,
    .postit-date-wrap .flatpickr-alt-input {
        cursor: pointer;
        background: #f8fafc;
    }

    #createPostItModal .modal-dialog {
        max-width: 640px;
    }

    #createPostItModal .modal-content,
    #createPostItModal .modal-body {
        overflow: visible;
    }

    .flatpickr-calendar.postit-calendar {
        border: none;
        border-radius: 16px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.14);
        padding: 12px 12px 14px;
        width: 278px;
        font-family: 'Kanit', 'Segoe UI', Tahoma, sans-serif;
        z-index: 2000;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-months {
        align-items: center;
        margin-bottom: 8px;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-month {
        overflow: visible;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-current-month {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 0;
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-calendar.postit-calendar .numInputWrapper {
        font-weight: 700;
        color: #1e293b;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-prev-month,
    .flatpickr-calendar.postit-calendar .flatpickr-next-month {
        padding: 6px;
        color: #64748b;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-weekdays {
        height: 32px;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-weekday {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.82rem;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-days {
        width: 100%;
    }

    .flatpickr-calendar.postit-calendar .dayContainer {
        width: 100%;
        min-width: 100%;
        max-width: 100%;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-day {
        border-radius: 8px;
        border: none;
        color: #334155;
        font-weight: 500;
        max-width: none;
        height: 36px;
        line-height: 36px;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-day.selected,
    .flatpickr-calendar.postit-calendar .flatpickr-day.selected:hover {
        background: #e2e8f0;
        color: #1e293b;
        border: none;
        box-shadow: none;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-day.prevMonthDay,
    .flatpickr-calendar.postit-calendar .flatpickr-day.nextMonthDay {
        color: #cbd5e1;
        font-weight: 400;
    }

    .flatpickr-calendar.postit-calendar .flatpickr-day.today:not(.selected) {
        border: none;
        color: #2563eb;
        font-weight: 700;
    }

    .postit-color-list {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 2px;
    }

    .postit-color-item {
        position: relative;
        width: 28px;
        height: 28px;
        margin: 0;
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

    .postit-color-item input:checked + .postit-color-dot {
        box-shadow: 0 0 0 2px #fff, 0 0 0 4px #2563eb;
    }

    .btn-postit-cancel {
        background: #f1f5f9;
        color: #334155;
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 700;
        font-size: 0.92rem;
    }

    .btn-postit-save {
        background: #2563eb;
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 10px 18px;
        font-weight: 700;
        font-size: 0.92rem;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }

    .btn-postit-save:hover {
        background: #1d4ed8;
        color: #fff;
    }

    .postit-field-wrap {
        position: relative;
    }

    .postit-field-wrap .postit-form-control {
        padding-right: 42px;
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
        margin: 8px 0 0;
        font-size: 0.84rem;
        font-weight: 600;
        color: #ef4444;
    }

    .postit-field-wrap.is-invalid .postit-form-control {
        border-color: #fecaca;
        background: #fff;
    }

    .postit-field-wrap.is-invalid .postit-field-error-icon,
    .postit-field-wrap.is-invalid + .postit-field-error-text {
        display: block;
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="postit-page-card">

            <div class="postit-header-row">
                <div>
                    <h1 class="postit-title">Post-it แจ้งเตือน</h1>
                    <p class="postit-breadcrumb">ภาพรวมระบบ &gt; Post-it แจ้งเตือน</p>
                </div>
                <a href="javascript:void(0);" class="btn-create-postit" id="btnCreatePostIt"
                    data-bs-toggle="modal" data-bs-target="#createPostItModal">
                    <i class="ri-add-line"></i> สร้าง Post-it
                </a>
            </div>

            <div class="postit-stats-grid">
                <div class="postit-stat-card">
                    <div class="postit-stat-icon total"><i class="ri-sticky-note-line"></i></div>
                    <div>
                        <div class="postit-stat-value"><?php echo (int) $stats['total']; ?></div>
                        <div class="postit-stat-label">Post-it ทั้งหมด</div>
                    </div>
                </div>
                <div class="postit-stat-card">
                    <div class="postit-stat-icon pending"><i class="ri-time-line"></i></div>
                    <div>
                        <div class="postit-stat-value"><?php echo (int) $stats['pending']; ?></div>
                        <div class="postit-stat-label">รอดำเนินการ</div>
                    </div>
                </div>
                <div class="postit-stat-card">
                    <div class="postit-stat-icon done"><i class="ri-checkbox-circle-line"></i></div>
                    <div>
                        <div class="postit-stat-value"><?php echo (int) $stats['done']; ?></div>
                        <div class="postit-stat-label">ดำเนินการแล้ว</div>
                    </div>
                </div>
                <div class="postit-stat-card">
                    <div class="postit-stat-icon overdue"><i class="ri-calendar-close-line"></i></div>
                    <div>
                        <div class="postit-stat-value"><?php echo (int) $stats['overdue']; ?></div>
                        <div class="postit-stat-label">เลยกำหนด</div>
                    </div>
                </div>
            </div>

            <div class="postit-board-card">
                <div class="postit-board-header">
                    <div class="postit-board-heading">
                        <h2 class="postit-board-title">บอร์ด Post-it</h2>
                        <p class="postit-board-sub">ทั้งหมด <?php echo (int) $pagination['total']; ?> รายการ</p>
                    </div>

                    <form method="get" action="<?php echo htmlspecialchars($base); ?>/post_it" class="postit-filters" id="postitFilterForm">
                    <div class="postit-search">
                        <i class="ri-search-line"></i>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($filters['q'] ?? ''); ?>"
                            placeholder="ค้นหาหัวข้อ ข้อความ ผู้รับผิดชอบ">
                    </div>

                    <select name="user_id" onchange="this.form.submit()">
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

                    <select name="status" onchange="this.form.submit()">
                        <option value="">ทุกสถานะ</option>
                        <option value="0" <?php echo (($filters['status'] ?? '') === '0') ? 'selected' : ''; ?>>รอดำเนินการ</option>
                        <option value="1" <?php echo (($filters['status'] ?? '') === '1') ? 'selected' : ''; ?>>ดำเนินการแล้ว</option>
                    </select>

                    <select name="color" onchange="this.form.submit()">
                        <option value="">ทุกสี</option>
                        <option value="yellow" <?php echo (($filters['color_code'] ?? '') === 'yellow') ? 'selected' : ''; ?>>เหลือง</option>
                        <option value="pink" <?php echo (($filters['color_code'] ?? '') === 'pink') ? 'selected' : ''; ?>>ชมพู</option>
                        <option value="blue" <?php echo (($filters['color_code'] ?? '') === 'blue') ? 'selected' : ''; ?>>ฟ้า</option>
                        <option value="green" <?php echo (($filters['color_code'] ?? '') === 'green') ? 'selected' : ''; ?>>เขียว</option>
                        <option value="purple" <?php echo (($filters['color_code'] ?? '') === 'purple') ? 'selected' : ''; ?>>ม่วง</option>
                        <option value="orange" <?php echo (($filters['color_code'] ?? '') === 'orange') ? 'selected' : ''; ?>>ส้ม</option>
                    </select>

                    <select name="due" onchange="this.form.submit()">
                        <option value="">ทุกกำหนดส่ง</option>
                        <option value="today" <?php echo (($filters['due'] ?? '') === 'today') ? 'selected' : ''; ?>>วันนี้</option>
                        <option value="week" <?php echo (($filters['due'] ?? '') === 'week') ? 'selected' : ''; ?>>สัปดาห์นี้</option>
                        <option value="overdue" <?php echo (($filters['due'] ?? '') === 'overdue') ? 'selected' : ''; ?>>เลยกำหนด</option>
                        <option value="none" <?php echo (($filters['due'] ?? '') === 'none') ? 'selected' : ''; ?>>ไม่มีกำหนด</option>
                    </select>

                    <select name="sort" onchange="this.form.submit()">
                        <option value="created_desc" <?php echo (($filters['sort'] ?? '') === 'created_desc') ? 'selected' : ''; ?>>เรียงตามวันที่สร้าง</option>
                        <option value="created_asc" <?php echo (($filters['sort'] ?? '') === 'created_asc') ? 'selected' : ''; ?>>วันที่สร้างเก่าสุด</option>
                        <option value="due_asc" <?php echo (($filters['sort'] ?? '') === 'due_asc') ? 'selected' : ''; ?>>กำหนดส่งใกล้สุด</option>
                        <option value="due_desc" <?php echo (($filters['sort'] ?? '') === 'due_desc') ? 'selected' : ''; ?>>กำหนดส่งไกลสุด</option>
                    </select>

                    <input type="hidden" name="per_page" value="<?php echo (int) $pagination['per_page']; ?>">
                    </form>
                </div>

                <div class="postit-grid">
                    <?php if (empty($items)): ?>
                        <div class="postit-empty">
                            <i class="ri-sticky-note-line" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
                            ยังไม่มี Post-it
                        </div>
                    <?php else: ?>
                        <?php foreach ($items as $item): ?>
                            <?php
                            $colorKey = $item['color_code'] ?? 'yellow';
                            $palette = $colorMap[$colorKey] ?? $colorMap['yellow'];
                            // รองรับกรณีเก็บเป็น hex ใน DB
                            if (is_string($colorKey) && str_starts_with($colorKey, '#')) {
                                $palette = ['bg' => $colorKey, 'border' => $colorKey, 'text' => '#854d0e'];
                            }
                            $st = $statusLabel((string) ($item['status'] ?? '0'), $item['due_date'] ?? null);
                            ?>
                            <article class="postit-note" style="background: <?php echo htmlspecialchars($palette['bg']); ?>; border-color: <?php echo htmlspecialchars($palette['border']); ?>;">
                                <h3 class="postit-note-title"><?php echo htmlspecialchars($item['title'] ?? ''); ?></h3>
                                <div class="postit-note-assignee">
                                    <i class="ri-user-line"></i>
                                    <span><?php echo htmlspecialchars($displayName($item)); ?></span>
                                </div>
                                <div class="postit-note-body"><?php echo nl2br(htmlspecialchars($item['content'] ?? '')); ?></div>
                                <div class="postit-note-meta">
                                    <i class="ri-time-line"></i>
                                    <span>สร้างเมื่อ <?php echo htmlspecialchars($formatThaiDate($item['created_at'] ?? null)); ?></span>
                                </div>
                                <div class="postit-note-status <?php echo htmlspecialchars($st['class']); ?>">
                                    <?php echo htmlspecialchars($st['text']); ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="postit-pager">
                    <div class="postit-pager-left">
                        <span>แสดง</span>
                        <select id="postitPerPage" onchange="document.querySelector('#postitFilterForm [name=per_page]').value=this.value; document.getElementById('postitFilterForm').submit();">
                            <?php foreach ([10, 25, 50, 100] as $n): ?>
                                <option value="<?php echo $n; ?>" <?php echo ((int) $pagination['per_page'] === $n) ? 'selected' : ''; ?>><?php echo $n; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span>รายการต่อหน้า</span>
                    </div>

                    <div>
                        รายการที่ <?php echo (int) $pagination['from']; ?>-<?php echo (int) $pagination['to']; ?>
                        จาก <?php echo (int) $pagination['total']; ?>
                    </div>

                    <div class="postit-pager-nav">
                        <?php
                        $qs = $_GET;
                        $buildPageUrl = static function (int $p) use ($base, $qs): string {
                            $qs['page'] = $p;
                            return $base . '/post_it?' . http_build_query($qs);
                        };
                        $current = (int) $pagination['page'];
                        $totalPages = (int) $pagination['total_pages'];
                        ?>
                        <a class="postit-page-btn <?php echo $current <= 1 ? 'disabled' : ''; ?>" href="<?php echo htmlspecialchars($buildPageUrl(1)); ?>">&laquo;</a>
                        <a class="postit-page-btn <?php echo $current <= 1 ? 'disabled' : ''; ?>" href="<?php echo htmlspecialchars($buildPageUrl(max(1, $current - 1))); ?>">&lsaquo;</a>
                        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                            <a class="postit-page-btn <?php echo $p === $current ? 'active' : ''; ?>" href="<?php echo htmlspecialchars($buildPageUrl($p)); ?>"><?php echo $p; ?></a>
                        <?php endfor; ?>
                        <a class="postit-page-btn <?php echo $current >= $totalPages ? 'disabled' : ''; ?>" href="<?php echo htmlspecialchars($buildPageUrl(min($totalPages, $current + 1))); ?>">&rsaquo;</a>
                        <a class="postit-page-btn <?php echo $current >= $totalPages ? 'disabled' : ''; ?>" href="<?php echo htmlspecialchars($buildPageUrl($totalPages)); ?>">&raquo;</a>
                    </div>
                </div>
            </div>
            </div>

            <div class="modal fade" id="createPostItModal" tabindex="-1" aria-labelledby="createPostItModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="createPostItModalLabel">สร้าง Post-it ใหม่</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="createPostItForm" autocomplete="off" novalidate>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="postit-form-label" for="postitTitle">หัวข้อ <span class="text-danger">*</span></label>
                                    <div class="postit-field-wrap" id="postitTitleWrap">
                                        <input type="text" class="postit-form-control" id="postitTitle" name="title" placeholder="หัวข้อ Post-it">
                                        <span class="postit-field-error-icon">!</span>
                                    </div>
                                    <p class="postit-field-error-text">กรุณาระบุหัวข้อ Post-it</p>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="postit-form-label" for="postitAssignee">ผู้รับผิดชอบ</label>
                                        <select class="postit-form-select" id="postitAssignee" name="user_id">
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
                                        <label class="postit-form-label" for="postitDueDate">กำหนดส่ง</label>
                                        <div class="postit-date-wrap" id="postitDateWrap">
                                            <input type="text" class="postit-form-control" id="postitDueDate" name="due_date" value="" placeholder="เลือกวันที่" readonly>
                                            <i class="ri-calendar-line"></i>
                                            <button type="button" class="postit-date-clear" id="postitDueDateClear">ล้าง</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="postit-form-label" for="postitStatus">สถานะ</label>
                                    <select class="postit-form-select" id="postitStatus" name="status">
                                        <option value="0" selected>รอดำเนินการ</option>
                                        <option value="1">ดำเนินการแล้ว</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="postit-form-label" for="postitContent">ข้อความ</label>
                                    <textarea class="postit-form-textarea" id="postitContent" name="content" placeholder="รายละเอียด..."></textarea>
                                </div>

                                <div class="mb-2">
                                    <label class="postit-form-label">สี</label>
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
                            <div class="modal-footer">
                                <button type="button" class="btn-postit-cancel" data-bs-dismiss="modal">ยกเลิก</button>
                                <button type="submit" class="btn-postit-save">บันทึกข้อมูล</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php require_once dirname(__DIR__) . '/main/footer.php'; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
<script>
document.querySelector('.postit-search input')?.addEventListener('keydown', function (e) {
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
                instance.altInput.className = 'postit-form-control';
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

    fetch('<?php echo htmlspecialchars($base); ?>/post_it/store', {
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
</script>
