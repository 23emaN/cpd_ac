<?php
// app/views/backoffice/dashboard_month.php

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';

// หาชื่อบริษัทและปีบัญชีที่กำลังใช้งานอยู่ (pattern เดียวกับ main/header.php)
$active_company_name = '';
$active_fiscal_year  = '';
foreach (($data['companies'] ?? []) as $company) {
    $cId = $company['company_id'] ?? $company['id'] ?? '';
    if ($cId == ($data['active_company_id'] ?? '')) {
        $active_company_name = $company['company_name'] ?? '';
        foreach (($company['fiscal_years'] ?? []) as $fy) {
            $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
            if ($fy_id == ($data['fiscal_id'] ?? '')) {
                $active_fiscal_year = $fy['fiscal_years'] ?? '';
                break;
            }
        }
        break;
    }
}

$tasks          = $data['tasks'] ?? [];
$customer_count = (int) ($data['customer_count'] ?? 0);
$task_count     = count($tasks);

// ชื่อเดือนภาษาไทย
$thai_months = [
    1 => 'มกราคม',
    2 => 'กุมภาพันธ์',
    3 => 'มีนาคม',
    4 => 'เมษายน',
    5 => 'พฤษภาคม',
    6 => 'มิถุนายน',
    7 => 'กรกฎาคม',
    8 => 'สิงหาคม',
    9 => 'กันยายน',
    10 => 'ตุลาคม',
    11 => 'พฤศจิกายน',
    12 => 'ธันวาคม',
];
$thai_months_short = [
    1 => 'ม.ค.',
    2 => 'ก.พ.',
    3 => 'มี.ค.',
    4 => 'เม.ย.',
    5 => 'พ.ค.',
    6 => 'มิ.ย.',
    7 => 'ก.ค.',
    8 => 'ส.ค.',
    9 => 'ก.ย.',
    10 => 'ต.ค.',
    11 => 'พ.ย.',
    12 => 'ธ.ค.',
];

// เดือนที่เลือกอยู่ (ถ้ามีการส่งมาจาก query string / controller ให้ใช้ค่านั้น)
$selected_month = (int) ($data['selected_month'] ?? $_GET['month'] ?? date('n'));
if ($selected_month < 1 || $selected_month > 12) {
    $selected_month = (int) date('n');
}
$selected_month_short = $thai_months_short[$selected_month];

/*
 * TODO: ตัวเลขต่อไปนี้เป็น placeholder (0) เพราะฐานข้อมูล (tbl_customer_tasks)
 * ยังไม่มีคอลัมน์เก็บสถานะรายเดือนต่อ task/ลูกค้า (เช่น รับเอกสารแล้ว/ทำเสร็จ/
 * สอบทานแล้ว/ยื่นภาษีแล้ว/เก็บเงินแล้ว) เมื่อ schema พร้อมค่อยคำนวณจริง
 * แล้วส่งมาผ่าน $data ในรูปแบบเดียวกันนี้จาก Controller
 */
$stat_customers_this_month = (int) ($data['stat_customers_this_month'] ?? $customer_count);
$stat_doc_received         = (int) ($data['stat_doc_received'] ?? 0);
$stat_completed            = (int) ($data['stat_completed'] ?? 0);
$stat_tax_filed            = (int) ($data['stat_tax_filed'] ?? 0);
$stat_payment_collected    = (int) ($data['stat_payment_collected'] ?? 0);
$stat_reviewed             = (int) ($data['stat_reviewed'] ?? 0);

// แถวสรุปความคืบหน้า: [ label, total(รวม), month_value(เดือนที่เลือก) ]
$progress_rows = [
    ['label' => 'จำนวนลูกค้า',       'total' => $customer_count, 'month' => $stat_customers_this_month],
    ['label' => 'ได้รับเอกสารแล้ว',  'total' => $customer_count, 'month' => $stat_doc_received],
    ['label' => 'ทำเสร็จแล้ว',       'total' => $customer_count, 'month' => $stat_completed],
    ['label' => 'สอบทานแล้ว',        'total' => $customer_count, 'month' => $stat_reviewed],
    ['label' => 'ยื่นภาษีแล้ว',      'total' => $customer_count, 'month' => $stat_tax_filed],
    ['label' => 'เก็บเงินลูกค้าแล้ว', 'total' => $customer_count, 'month' => $stat_payment_collected],
];

function dm_percent(int $part, int $whole): int
{
    if ($whole <= 0) {
        return 0;
    }
    return (int) round(($part / $whole) * 100);
}
?>

<link rel="stylesheet" href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/css/sidebar-menu.css">

<style>
    .content-wrapper {
        padding: 24px 32px 32px 32px;
        min-height: calc(100vh - 140px);
        font-family: 'Kanit', 'Segoe UI', Tahoma, sans-serif;
    }

    .main-card-wrapper {
        background: #ffffff;
        border-radius: 18px;
        border: 1px solid #edf2f7;
        box-shadow: 0 1px 4px rgba(16, 24, 40, 0.02);
        padding: 28px;
    }

    .dashboard-header-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .dashboard-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 2px 0;
    }

    .dashboard-sub {
        font-size: 0.82rem;
        color: #94a3b8;
        margin: 0;
    }

    .dashboard-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .month-select-wrap {
        position: relative;
    }

    .month-select-wrap select {
        appearance: none;
        -webkit-appearance: none;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 9px 36px 9px 14px;
        font-family: inherit;
        font-size: 0.85rem;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(16, 24, 40, 0.03);
    }

    .month-select-wrap::after {
        content: '';
        position: absolute;
        right: 14px;
        top: 50%;
        width: 8px;
        height: 8px;
        border-right: 2px solid #64748b;
        border-bottom: 2px solid #64748b;
        transform: translateY(-65%) rotate(45deg);
        pointer-events: none;
    }

    .export-excel-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background-color: #eaf2ff;
        color: #0066fe;
        border: none;
        border-radius: 10px;
        padding: 9px 16px;
        font-family: inherit;
        font-size: 0.85rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: background-color .15s ease;
    }

    .export-excel-btn:hover {
        background-color: #dbe9ff;
        color: #0052cc;
    }

    .stat-cards-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 18px;
        margin-bottom: 24px;
    }

    .stat-card-item {
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        padding: 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stat-icon-circle {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .stat-icon-blue {
        background-color: #ebf5ff;
        color: #0066fe;
    }

    .stat-icon-green {
        background-color: #e9f9ef;
        color: #16a34a;
    }

    .stat-icon-purple {
        background-color: #f3e8ff;
        color: #9333ea;
    }

    .stat-icon-yellow {
        background-color: #fff8e1;
        color: #d97706;
    }

    .stat-icon-teal {
        background-color: #e6f8f4;
        color: #0d9488;
    }

    .stat-number {
        font-size: 1.4rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        margin: 0;
    }

    .stat-label-title {
        font-size: 0.78rem;
        font-weight: 600;
        color: #64748b;
        margin: 3px 0 0 0;
    }

    .status-box-card {
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #edf2f7;
        padding: 24px;
    }

    .status-box-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 8px;
    }

    .status-header-h4 {
        font-size: 1.05rem;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    .status-header-p {
        font-size: 0.75rem;
        color: #94a3b8;
        margin: 2px 0 0 0;
    }

    .status-header-badge {
        background-color: #eaf2ff;
        color: #0066fe;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 999px;
        white-space: nowrap;
    }

    .progress-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }

    .progress-table thead th {
        text-align: left;
        color: #94a3b8;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0 0 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }

    .progress-table thead th.col-num {
        text-align: right;
    }

    .progress-table tbody td {
        padding: 14px 0;
        border-bottom: 1px dashed #f1f5f9;
        color: #334155;
    }

    .progress-table tbody tr:last-child td {
        border-bottom: none;
    }

    .progress-table td.col-num {
        text-align: right;
        font-weight: 700;
        color: #0f172a;
    }

    .progress-percent-badge {
        display: inline-block;
        min-width: 46px;
        text-align: center;
        padding: 3px 10px;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.78rem;
        background-color: #eef2f7;
        color: #64748b;
    }

    .progress-percent-badge.is-complete {
        background-color: #e9f9ef;
        color: #16a34a;
    }

    .empty-state-text {
        color: #94a3b8;
        font-size: 0.85rem;
        text-align: center;
        padding: 20px 0;
    }

    @media (max-width: 1200px) {
        .stat-cards-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stat-cards-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .progress-table thead {
            display: none;
        }

        .progress-table,
        .progress-table tbody,
        .progress-table tr,
        .progress-table td {
            display: block;
            width: 100%;
        }

        .progress-table tbody tr {
            padding: 10px 0;
            border-bottom: 1px dashed #f1f5f9;
        }

        .progress-table td {
            border-bottom: none !important;
            padding: 3px 0;
        }

        .progress-table td.col-num,
        .progress-table td:first-child {
            text-align: left;
        }
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">

        <div class="content-wrapper">
            <div class="main-card-wrapper">
                <!-- Header Row -->
                <div class="dashboard-header-row">
                    <div>
                        <h2 class="dashboard-title">แดชบอร์ด – งานรายเดือน</h2>
                        <p class="dashboard-sub">
                            ภาพรวมระบบ - แดชบอร์ดรายเดือน - <?php echo htmlspecialchars($thai_months[$selected_month]); ?>
                            <?php if ($active_fiscal_year): ?>
                                ปี <?php echo htmlspecialchars($active_fiscal_year); ?>
                            <?php endif; ?>
                            <?php if ($active_company_name): ?>
                                &nbsp;|&nbsp; <?php echo htmlspecialchars($active_company_name); ?>
                            <?php endif; ?>
                        </p>
                    </div>

                    <div class="dashboard-header-actions">
                        <form method="get" class="month-select-wrap" id="monthFilterForm">
                            <select name="month" onchange="document.getElementById('monthFilterForm').submit()">
                                <?php foreach ($thai_months as $m_num => $m_name): ?>
                                    <option value="<?php echo $m_num; ?>" <?php echo $m_num === $selected_month ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($m_name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>

                        <a href="<?php echo (defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'); ?>/backoffice/dashboard-month/export?month=<?php echo $selected_month; ?>"
                            class="export-excel-btn">
                            <i class="ri-upload-2-line"></i>
                            ส่งออก Excel
                        </a>
                    </div>
                </div>

                <!-- Stat Cards -->
                <div class="stat-cards-grid">
                    <div class="stat-card-item">
                        <div class="stat-icon-circle stat-icon-blue">
                            <i class="ri-user-line"></i>
                        </div>
                        <div>
                            <h3 class="stat-number"><?php echo $stat_customers_this_month; ?></h3>
                            <h4 class="stat-label-title">ลูกค้าในเดือนนี้</h4>
                        </div>
                    </div>

                    <div class="stat-card-item">
                        <div class="stat-icon-circle stat-icon-green">
                            <i class="ri-file-text-line"></i>
                        </div>
                        <div>
                            <h3 class="stat-number"><?php echo $stat_doc_received; ?></h3>
                            <h4 class="stat-label-title">ได้รับเอกสารแล้ว</h4>
                        </div>
                    </div>

                    <div class="stat-card-item">
                        <div class="stat-icon-circle stat-icon-purple">
                            <i class="ri-checkbox-circle-line"></i>
                        </div>
                        <div>
                            <h3 class="stat-number"><?php echo $stat_completed; ?></h3>
                            <h4 class="stat-label-title">ทำเสร็จแล้ว</h4>
                        </div>
                    </div>

                    <div class="stat-card-item">
                        <div class="stat-icon-circle stat-icon-yellow">
                            <i class="ri-mail-send-line"></i>
                        </div>
                        <div>
                            <h3 class="stat-number"><?php echo $stat_tax_filed; ?></h3>
                            <h4 class="stat-label-title">ยื่นภาษีแล้ว</h4>
                        </div>
                    </div>

                    <div class="stat-card-item">
                        <div class="stat-icon-circle stat-icon-teal">
                            <i class="ri-wallet-3-line"></i>
                        </div>
                        <div>
                            <h3 class="stat-number"><?php echo $stat_payment_collected; ?></h3>
                            <h4 class="stat-label-title">เก็บเงินลูกค้าแล้ว</h4>
                        </div>
                    </div>
                </div>

                <!-- สรุปความคืบหน้า -->
                <div class="status-box-card">
                    <div class="status-box-header">
                        <div>
                            <h4 class="status-header-h4">สรุปความคืบหน้า</h4>
                            <p class="status-header-p">
                                <?php echo htmlspecialchars($thai_months[$selected_month]); ?>
                                <?php if ($active_fiscal_year): ?>
                                    ปี <?php echo htmlspecialchars($active_fiscal_year); ?>
                                <?php endif; ?>
                                &middot; <?php echo $customer_count; ?> ลูกค้า
                            </p>
                        </div>
                        <span class="status-header-badge">
                            <?php echo (int) ($data['staff_count'] ?? 1); ?> ผู้ดูแล
                        </span>
                    </div>

                    <?php if ($customer_count === 0): ?>
                        <p class="empty-state-text">ยังไม่มีรายการงานสำหรับปีบัญชีนี้</p>
                    <?php else: ?>
                        <table class="progress-table">
                            <thead>
                                <tr>
                                    <th>รายการ</th>
                                    <th class="col-num">%</th>
                                    <th class="col-num">รวม</th>
                                    <th class="col-num"><?php echo htmlspecialchars($selected_month_short); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($progress_rows as $row): ?>
                                    <?php $pct = dm_percent((int) $row['month'], (int) $row['total']); ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['label']); ?></td>
                                        <td class="col-num">
                                            <span class="progress-percent-badge <?php echo $pct >= 100 ? 'is-complete' : ''; ?>">
                                                <?php echo $pct; ?>%
                                            </span>
                                        </td>
                                        <td class="col-num"><?php echo (int) $row['total']; ?></td>
                                        <td class="col-num"><?php echo (int) $row['month']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Footer เข้ามา -->
        <?php require_once dirname(__DIR__) . '/main/footer.php'; ?>

    </div>
</div>