<?php
    $selected_year          = $_GET['year'] ?? '2569';
    $company_name           = $_GET['company'] ?? 'TEST ACCOUNTING';
    $show_company_workspace = true;

    // 1. นำ Header เข้ามา
    require_once dirname(__DIR__) . '/main/header.php';

    // 2. นำ Sidebar เข้ามา
    require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<style>
    /* Custom Styles for Task Assignment Dashboard */
    .status-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    .status-critical { background-color: #fee2e2; color: #ef4444; } /* แดง */
    .status-warning { background-color: #fef3c7; color: #f59e0b; } /* ส้ม/เหลือง */
    .status-normal { background-color: #e0f2fe; color: #0284c7; } /* ฟ้า */
    .status-success { background-color: #dcfce3; color: #22c55e; } /* เขียว */

    .due-date-critical { color: #ef4444; font-weight: bold; }
    .due-date-warning { color: #f59e0b; font-weight: bold; }

    .assignee-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: #e2e8f0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #475569;
        font-size: 0.85rem;
        margin-right: 8px;
    }

    .table-container-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        padding: 20px;
        margin-top: 20px;
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">

                <div class="main-card-wrapper">

                    <!-- Header -->
                    <div class="page-header-box d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2 class="page-title">ติดตามและมอบหมายงาน</h2>
                            <p class="page-subtitle">โดยจะแจ้งเตือนให้ผู้รับผิดชอบล่วงหน้า 5 วัน</p>
                        </div>
                        <button type="button" class="btn-add-action" onclick="modal_assign_task()">
                            <i class="ri-add-line"></i>
                            <span>เพิ่มงานใหม่</span>
                        </button>
                    </div>

                    <!-- Stats Grid -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card stat-card" style="border-left: 4px solid #3b82f6;">
                                <div class="card-body">
                                    <h6 class="text-muted">งานทั้งหมด</h6>
                                    <h3><?php echo htmlspecialchars($data['stats']['total'] ?? 0); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card" style="border-left: 4px solid #f59e0b;">
                                <div class="card-body">
                                    <h6 class="text-muted">ใกล้ถึงกำหนด</h6>
                                    <h3 class="text-warning"><?php echo htmlspecialchars($data['stats']['critical'] ?? 0); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card" style="border-left: 4px solid #ef4444;">
                                <div class="card-body">
                                    <h6 class="text-muted">เลยกำหนด</h6>
                                    <h3 class="text-danger"><?php echo htmlspecialchars($data['stats']['overdue'] ?? 0); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card stat-card" style="border-left: 4px solid #22c55e;">
                                <div class="card-body">
                                    <h6 class="text-muted">เสร็จสิ้น</h6>
                                    <h3 class="text-success"><?php echo htmlspecialchars($data['stats']['completed'] ?? 0); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter / Search -->
                    <div class="d-flex justify-content-between mb-3 align-items-center">
                        <form method="GET" action="" id="filterForm" class="d-flex gap-2">
                            <select class="form-select" style="width: 200px;" name="assignee_id" onchange="document.getElementById('filterForm').submit();">
                                <option value="">ผู้รับผิดชอบทั้งหมด</option>
                                <?php if (!empty($data['employees'])): ?>
                                    <?php foreach ($data['employees'] as $emp): ?>
                                        <option value="<?php echo htmlspecialchars($emp['user_id']); ?>" <?php echo ($data['filters']['assignee_id'] == $emp['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($emp['user_firstname'] . ' ' . $emp['user_lastname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <select class="form-select" style="width: 150px;" name="status" onchange="document.getElementById('filterForm').submit();">
                                <option value="">สถานะทั้งหมด</option>
                                <option value="critical" <?php echo ($data['filters']['status'] == 'critical') ? 'selected' : ''; ?>>ใกล้ถึงกำหนด</option>
                                <option value="overdue" <?php echo ($data['filters']['status'] == 'overdue') ? 'selected' : ''; ?>>เลยกำหนด</option>
                                <option value="0" <?php echo ($data['filters']['status'] == '0') ? 'selected' : ''; ?>>รอดำเนินการ</option>
                                <option value="1" <?php echo ($data['filters']['status'] == '1') ? 'selected' : ''; ?>>เสร็จสิ้น</option>
                            </select>
                        </form>
                    </div>

                    <!-- Main Table -->
                    <?php require_once __DIR__ . '/table/assign_table.php'; ?>
                    
                    <!-- Pagination -->
                    <?php if (isset($data['pagination']) && $data['pagination']['total_pages'] > 0): ?>
                        <div class="mt-4">
                            <?php 
                                $paginationParams = $data['filters'];
                                require_once __DIR__ . '/_pagination.php'; 
                            ?>
                        </div>
                    <?php endif; ?>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<!-- Modal มอบหมายงานใหม่ -->
<div class="modal fade" id="assignTaskModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">มอบหมายงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="assignTaskForm">

                    <div class="mb-3">
                        <label class="form-label">ชื่องาน<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="assign_title" id="assign_title" required placeholder="ระบุชื่องาน">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">รายละเอียดงาน</label>
                        <textarea class="form-control" name="assign_detail" id="assign_detail" rows="3" placeholder="ระบุรายละเอียดเพิ่มเติม..."></textarea>
                    </div>

                    <div class="mb-3">
  <label class="form-label">กำหนดส่ง (Due Date) <span class="text-danger">*</span></label>
  <div class="position-relative">
    <input type="text" class="form-control pe-5" name="due_date" id="due_date" required placeholder="วว/ดด/ปปปป">
    <i class="ri-calendar-line position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="pointer-events: none;"></i>
  </div>
</div>

                    <div class="mb-3">
                        <label class="form-label">ผู้รับผิดชอบ <span class="text-danger">*</span></label>
                        <select class="form-select" name="user_id" id="user_id" required>
                            <option value="">-- เลือกพนักงาน --</option>
                            <?php if (!empty($data['employees'])): ?>
                                <?php foreach ($data['employees'] as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['user_id']); ?>">
                                        <?php echo htmlspecialchars($emp['user_firstname'] . ' ' . $emp['user_lastname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                </form>
            </div>
            <div class="modal-footer">      
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="saveAssignTask()">บันทึกมอบหมายงาน</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#due_date", {
                dateFormat: "Y-m-d", // รูปแบบที่จะส่งเข้า Database (POST)
                altInput: true,      // สร้างช่อง Input จำลองขึ้นมาแสดงผลให้สวยงาม
                altFormat: "d/m/Y",  // รูปแบบที่แสดงให้ User เห็น (วว/ดด/ปปปป)
                locale: "th",
                allowInput: true,
                static: true
            });
        }
    });

    function modal_assign_task() {
        document.getElementById('assignTaskForm').reset();
        
        var assignModal = new bootstrap.Modal(document.getElementById('assignTaskModal'));
        assignModal.show();
    }
    
    function modal_edit_assign(taskId) {
        // สามารถดึงข้อมูลเดิมมาแสดงใน Modal ได้
        var assignModal = new bootstrap.Modal(document.getElementById('assignTaskModal'));
        assignModal.show();
    }
    
    function saveAssignTask() {
        if (!$('#assignTaskForm')[0].checkValidity()) {
            $('#assignTaskForm')[0].reportValidity();
            return;
        }

        var formData = $('#assignTaskForm').serialize();
        var submitBtn = $('button[onclick="saveAssignTask()"]');
        submitBtn.prop('disabled', true).text('กำลังบันทึก...');

        $.ajax({
            type: "POST",
            url: "<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/assign_task/save",
            data: formData,
            dataType: "json",
            success: function (response) {
                submitBtn.prop('disabled', false).text('บันทึกมอบหมายงาน');
                
                if (response.result === 1) {
                    var modalInstance = bootstrap.Modal.getInstance(document.getElementById('assignTaskModal'));
                    if (modalInstance) modalInstance.hide();

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: response.msg || 'มอบหมายงานสำเร็จ',
                            showConfirmButton: true,
                            confirmButtonText: 'ตกลง'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        alert(response.msg || 'มอบหมายงานสำเร็จ');
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาด',
                            text: response.msg || 'ไม่สามารถบันทึกข้อมูลได้',
                            showConfirmButton: true
                        });
                    } else {
                        alert(response.msg || 'ไม่สามารถบันทึกข้อมูลได้');
                    }
                }
            },
            error: function () {
                submitBtn.prop('disabled', false).text('บันทึกมอบหมายงาน');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'ข้อผิดพลาดระบบ',
                        text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                        showConfirmButton: true
                    });
                } else {
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                }
            }
        });
    }
</script>

<?php require_once dirname(__DIR__) . '/main/footer.php'; ?>