<?php
// app/views/backoffice/tasks.php
$selected_year = $_GET['year'] ?? '2569';
$company_name  = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Kanit', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-page-wrapper {
        padding-top:-1px;
        padding-bottom: 32px;
        padding-left: 20px;
        min-height: calc(100vh - 72px);
    }

    /* --- Master Card Wrapper --- */
    .main-card-wrapper {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 12px rgba(16, 24, 40, 0.03);
        padding: 32px;
    }

    /* --- Header Section --- */
    .page-header-box {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 24px;
    }

    .page-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 4px;
        letter-spacing: -0.2px;
    }

    .page-subtitle {
        font-size: 0.85rem;
        color: #94a3b8;
        font-weight: 500;
        margin: 0;
    }

    .btn-add-task {
        background-color: #3b82f6;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 0.90rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 10px rgba(59, 130, 246, 0.2);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-add-task:hover {
        background-color: #2563eb;
        transform: translateY(-1px);
    }

    /* --- Stats Grid --- */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }

    .stat-card {
        background-color: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 12px;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .stat-icon.blue {
        background-color: #eff6ff;
        color: #3b82f6;
        border: 1px solid #dbeafe;
    }

    .stat-icon.green {
        background-color: #f0fdf4;
        color: #22c55e;
        border: 1px solid #dcfce7;
    }

    .stat-icon.purple {
        background-color: #faf5ff;
        color: #a855f7;
        border: 1px solid #f3e8ff;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-val {
        font-size: 1.4rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        margin-bottom: 2px;
    }

    .stat-label {
        font-size: 0.82rem;
        color: #64748b;
        font-weight: 500;
    }

    /* --- Main Table Card --- */
    .table-container-card {
        background-color: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
    }

    .table-header-wrap {
        margin-bottom: 20px;
    }

    .table-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .table-subtitle {
        font-size: 0.85rem;
        color: #94a3b8;
        font-weight: 500;
        margin: 0;
    }

    /* --- Table Styles --- */
    .task-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .task-table th {
        font-size: 0.85rem;
        font-weight: 800;
        color: #1e293b;
        text-align: center;
        padding: 14px 10px;
        border-bottom: 1px dashed #e2e8f0;
    }
    
    .task-table th:nth-child(2) {
        text-align: left;
    }

    .task-table td {
        font-size: 0.90rem;
        font-weight: 700;
        color: #475569;
        text-align: center;
        padding: 16px 10px;
        border-bottom: 1px dashed #f1f5f9;
        vertical-align: middle;
    }

    .task-table td:nth-child(2) {
        text-align: left;
        color: #1e293b;
    }

    .task-table tr:last-child td {
        border-bottom: none;
    }

    /* Badge */
    .badge-yes {
        background-color: #dcfce7;
        color: #16a34a;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
        display: inline-block;
    }

    /* Action Buttons (Arrows & Edit/Delete) */
    .action-btn-group {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        background-color: transparent;
    }

    .btn-arrow {
        color: #64748b;
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
    }
    .btn-arrow:hover {
        background-color: #f1f5f9;
        color: #1e293b;
    }

    .btn-edit {
        color: #64748b;
        border: 1px solid #f1f5f9;
    }
    .btn-edit:hover {
        color: #3b82f6;
        background-color: #eff6ff;
        border-color: #bfdbfe;
    }

    .btn-delete {
        color: #ef4444;
        background-color: #fef2f2;
    }
    .btn-delete:hover {
        background-color: #fee2e2;
        color: #dc2626;
    }

</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                
                <div class="main-card-wrapper">
                    <!-- Header -->
        <div class="page-header-box">
            <div>
                <h2 class="page-title">ตั้งค่างานที่ต้องทำ</h2>
                <p class="page-subtitle">ภาพรวมระบบ - ตั้งค่างานที่ต้องทำ - ปี 2569</p>
            </div>
            <button type="button" class="btn-add-task">
                <i class="ri-add-line"></i> เพิ่มงาน
            </button>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="ri-checkbox-circle-line"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-val">14</span>
                    <span class="stat-label">งานทั้งหมด</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="ri-wallet-3-line"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-val">8</span>
                    <span class="stat-label">ต้องระบุจำนวนเงิน</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="ri-subtract-line"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-val">6</span>
                    <span class="stat-label">ไม่ต้องระบุจำนวนเงิน</span>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="table-container-card">
            <div class="table-header-wrap">
                <h3 class="table-title">รายการงานประจำเดือน</h3>
                <p class="table-subtitle">ทั้งหมด 14 รายการ</p>
            </div>

            <table class="task-table">
                <thead>
                    <tr>
                        <th width="10%">ลำดับ</th>
                        <th width="40%">งาน</th>
                        <th width="20%">ระบุจำนวนเงิน</th>
                        <th width="15%">เลื่อน</th>
                        <th width="15%">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Mock Data based on the screenshot
                    $tasks = [
                        ['id' => 1, 'name' => 'ภ.ง.ด.1', 'req_amount' => true],
                        ['id' => 2, 'name' => 'ภ.ง.ด.3', 'req_amount' => true],
                        ['id' => 3, 'name' => 'ภ.ง.ด.53', 'req_amount' => true],
                        ['id' => 4, 'name' => 'ภ.ง.ด.54', 'req_amount' => true],
                        ['id' => 5, 'name' => 'ภ.พ.30', 'req_amount' => true],
                        ['id' => 6, 'name' => 'ภ.พ.36', 'req_amount' => true],
                        ['id' => 7, 'name' => 'ประกันสังคม', 'req_amount' => true],
                    ];
                    
                    foreach ($tasks as $task): 
                    ?>
                    <tr>
                        <td><?= $task['id'] ?></td>
                        <td><?= $task['name'] ?></td>
                        <td>
                            <?php if ($task['req_amount']): ?>
                                <span class="badge-yes">YES</span>
                            <?php else: ?>
                                <span class="badge-no">NO</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btn-group">
                                <button type="button" class="btn-icon btn-arrow" title="เลื่อนขึ้น"><i class="ri-arrow-up-s-line"></i></button>
                                <button type="button" class="btn-icon btn-arrow" title="เลื่อนลง"><i class="ri-arrow-down-s-line"></i></button>
                            </div>
                        </td>
                        <td>
                            <div class="action-btn-group">
                                <button type="button" class="btn-icon btn-edit" title="แก้ไข"><i class="ri-pencil-line"></i></button>
                                <button type="button" class="btn-icon btn-delete" title="ลบ"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
                    </div>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<?php 
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php'; 
?>