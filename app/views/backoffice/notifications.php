<?php
// app/views/backoffice/customer.php
$selected_year = $_GET['year'] ?? '2569';
$company_name  = $_GET['company'] ?? 'TEST ACCOUNTING';
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
                        <!-- Header -->
                        <div class="page-header-box d-flex justify-content-between align-items-center">
                            <h3 class="mb-0 fw-bold" style="color: #333;">ประวัติการแจ้งเตือนทั้งหมด</h3>
                            <button type="button" class="btn-mark-all-read" onclick="readAllPageNotifications()">ทำเครื่องหมายว่าอ่านแล้วทั้งหมด</button>
                        </div>

                        <!-- Main Table -->
                        <div class="table-container">
                            <table class="table notif-table">
                                <thead>
                                    <tr>
                                        <th class="text-center" width="5%">ลำดับ</th>
                                        <th width="15%">หัวข้อ</th>
                                        <th width="45%">รายละเอียด</th>
                                        <th class="text-center" width="10%">ประเภท</th>
                                        <th class="text-center" width="15%">เวลา</th>
                                        <th class="text-center" width="10%">สถานะ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($data['notifications'])): ?>
                                        <?php 
                                            $page = $data['pagination']['current_page'] ?? 1;
                                            $perPage = 20;
                                            $startNo = (($page - 1) * $perPage) + 1;
                                            
                                            // Calculate display range for footer
                                            $totalItems = $data['pagination']['total_items'] ?? 0;
                                            $endNo = min($startNo + count($data['notifications']) - 1, $totalItems);
                                        ?>
                                        <?php foreach ($data['notifications'] as $index => $notif): ?>
                                            <?php 
                                                $isUnread = ($notif['is_read'] == 0);
                                                $type = $notif['task_type'];
                                                
                                                if ($type === 'post_it') {
                                                    $title = 'แจ้งเตือนงานใหม่';
                                                    $typeText = 'มอบหมายงาน';
                                                } else {
                                                    $title = 'แจ้งเตือนระบบ';
                                                    $typeText = 'ทั่วไป';
                                                }
                                            ?>
                                            <tr class="<?php echo $isUnread ? 'unread' : ''; ?>" id="page-notif-item-<?php echo $notif['notif_id']; ?>">
                                                <td class="text-center"><?php echo $startNo + $index; ?></td>
                                                <td><?php echo $title; ?></td>
                                                <td><?php echo htmlspecialchars($notif['message']); ?></td>
                                                <td class="text-center fw-bold"><?php echo $typeText; ?></td>
                                                <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($notif['created_at'])); ?></td>
                                                <td class="text-center notif-status-cell">
                                                    <?php if ($isUnread): ?>
                                                        <a href="javascript:void(0)" onclick="markPageNotificationRead(<?php echo $notif['notif_id']; ?>)" class="text-primary text-decoration-none">คลิกเพื่ออ่าน</a>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">ไม่มีการแจ้งเตือนในขณะนี้</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if (!empty($data['notifications'])): ?>
                            <div class="pagination-container">
                                <div class="text-muted">
                                    แสดง <?php echo $startNo; ?>-<?php echo $endNo; ?> จาก <?php echo $totalItems; ?> รายการ
                                </div>
                                
                                <?php if (isset($data['pagination']) && $data['pagination']['total_pages'] > 1): ?>
                                    <nav>
                                        <ul class="pagination mb-0">
                                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=1">หน้าแรก</a>
                                            </li>
                                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo max(1, $page - 1); ?>">ก่อนหน้า</a>
                                            </li>
                                            
                                            <!-- Show a few pages around current -->
                                            <?php 
                                                $totalPages = $data['pagination']['total_pages'];
                                                $startPage = max(1, $page - 2);
                                                $endPage = min($totalPages, $startPage + 4);
                                                if ($endPage - $startPage < 4) {
                                                    $startPage = max(1, $endPage - 4);
                                                }
                                                for ($i = $startPage; $i <= $endPage; $i++): 
                                            ?>
                                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo min($totalPages, $page + 1); ?>">ถัดไป</a>
                                            </li>
                                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $totalPages; ?>">หน้าสุดท้าย</a>
                                            </li>
                                        </ul>
                                    </nav>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

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
