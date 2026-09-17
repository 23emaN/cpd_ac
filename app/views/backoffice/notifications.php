<?php
    // app/views/backoffice/customer.php
    $selected_year          = $_GET['year'] ?? '2569';
    $company_name           = $_GET['company'] ?? 'TEST ACCOUNTING';
    $show_company_workspace = false;

    // 1. นำ Header เข้ามา
    require_once dirname(__DIR__) . '/main/header.php';

    // 2. นำ Sidebar เข้ามา
    require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<style>
    .notif-page-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 0;
        margin-top: 20px;
    }
    .table-container {
        width: 100%;
        overflow-x: auto;
    }
    .notif-table {
        width: 100%;
        margin-bottom: 0;
    }
    .notif-table th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        padding: 15px 20px;
        border-bottom: 2px solid #e9ecef;
        white-space: nowrap;
    }
    .notif-table td {
        padding: 15px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #e9ecef;
        color: #495057;
    }
    .notif-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    .notif-table tbody tr.unread {
        background-color: #f0f7ff;
        font-weight: 500;
    }
    .notif-table tbody tr.unread:hover {
        background-color: #e2effa;
    }
    .btn-mark-all-read {
        color: #6366f1;
        background-color: #fff;
        border: 1px solid #6366f1;
        border-radius: 6px;
        padding: 8px 16px;
        font-weight: 500;
        transition: all 0.2s;
    }
    .btn-mark-all-read:hover {
        background-color: #f5f3ff;
    }
    .page-header-box {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
    }
    .pagination-container {
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">

                    <div class="notif-page-card">
                        <!-- Header with Integrated Filter -->
                        <div class="page-header-box d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
                            <div>
                                <h3 class="mb-1 fw-bold" style="color: #333;">ประวัติการแจ้งเตือนทั้งหมด</h3>
                                <p class="text-muted small mb-0">
                                    <i class="bi bi-info-circle me-1"></i> แสดงเฉพาะการแจ้งเตือนจากเมนู Post-it และรายการงานที่ได้รับมอบหมาย
                                </p>
                            </div>

                            <!-- Filter Section -->
                            <div class="d-flex align-items-center">
                                <form method="GET" action="" id="notifFilterForm" class="m-0">
                                    <div class="input-group input-group-sm shadow-sm" style="width: 200px;">
                                        <select class="form-select border-start-0 ps-1" 
                                                id="read_status" 
                                                name="read_status" 
                                                style="width: 160px; cursor: pointer;"
                                                onchange="document.getElementById('notifFilterForm').submit();">
                                            <option value="">ทั้งหมด</option>
                                            <option value="0" <?php echo (isset($_GET['read_status']) && $_GET['read_status'] === '0') ? 'selected' : ''; ?>>
                                                ยังไม่ได้รับทราบ
                                            </option>
                                            <option value="1" <?php echo (isset($_GET['read_status']) && $_GET['read_status'] === '1') ? 'selected' : ''; ?>>
                                                รับทราบแล้ว
                                            </option>
                                        </select>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Main Table -->
                        <div id="notificationTableContainer">
                            <?php require_once 'table/notification_table.php'; ?>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
function markPageNotificationRead(notifId) {
    const baseUrl = "<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>";
    $.ajax({
        url: baseUrl + "/notification/read",
        method: "POST",
        data: { notif_id: notifId },
        dataType: "json",
        success: function(res) {
            if (res && res.result === 1) {
                // Update UI on page
                let row = document.getElementById('page-notif-item-' + notifId);
                if (row) {
                    row.classList.remove('unread');

                    // Replace link with "-"
                    let statusCell = row.querySelector('.notif-status-cell');
                    if (statusCell) {
                        statusCell.innerHTML = '-';
                    }
                }

                // Update the notification bell badge in header
                if (typeof loadNotifications === 'function') {
                    loadNotifications();
                }
            } else {
                alert('ไม่สามารถอัปเดตสถานะการแจ้งเตือนได้');
            }
        },
        error: function() {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        }
    });
}

function readAllPageNotifications() {
    const baseUrl = "<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>";
    $.ajax({
        url: baseUrl + "/notification/read-all",
        method: "POST",
        dataType: "json",
        success: function(res) {
            if (res && res.result === 1) {
                // Reload the page to reflect all changes
                window.location.reload();
            }
        },
        error: function() {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        }
    });
}
</script>

<?php require_once dirname(__DIR__) . '/main/footer.php'; ?>
