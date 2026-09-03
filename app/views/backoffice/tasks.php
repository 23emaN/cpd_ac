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
        padding-top: 0px !important;
        padding: 24px 32px;
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
                            <?php $fy_display = !empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
                            <p class="page-subtitle">ภาพรวมระบบ - ตั้งค่างานที่ต้องทำ - ปี <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <button type="button" class="btn-add-task" onclick="modal_addtasks()">
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
                                <span class="stat-val"><?php echo number_format($data['total_tasks'] ?? 0); ?></span>
                                <span class="stat-label">งานทั้งหมด</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($data['req_amount_count'] ?? 0); ?></span>
                                <span class="stat-label">ต้องระบุจำนวนเงิน</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-subtract-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($data['no_req_amount_count'] ?? 0); ?></span>
                                <span class="stat-label">ไม่ต้องระบุจำนวนเงิน</span>
                            </div>
                        </div>
                    </div>

                    <!-- Main Table -->
                    <div class="table-container-card">
                        <?php include 'table/task_table.php'; ?>
                    </div>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>
<!-- Modal เพิ่มงาน -->
<div class="modal fade" id="addTasksModal" tabindex="-1" aria-labelledby="addTasksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid #b4b0b0ff; padding: 24px 32px;">
                <h5 class="modal-title" id="addTasksModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem;">เพิ่มงานใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.85rem; opacity: 0.4;"></button>
            </div>
            <div class="modal-body" style="padding: 24px 32px;">
                <form id="addTasksForm">
                    <!-- ส่ง fiscal_id ปัจจุบันไปด้วย -->
                    <input type="hidden" name="fiscal_id" value="<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>">
                            
                            <div class="mb-4">
                                <label class="form-label" for="task_name" style="font-weight: 700; color: #334155; font-size: 0.95rem; margin-bottom: 10px;">
                                    ชื่องาน <span style="color: #ef4444;">*</span>
                                </label>
                                <input class="form-control" type="text" id="task_name" name="task_name" placeholder="เช่น ภ.ง.ด.1" style="background-color: #f8fafc; border-radius: 12px; border: none; padding: 14px 18px; font-weight: 600; font-size: 0.95rem; color: #475569; box-shadow: none;">
                                <div class="invalid-feedback" style="font-size: 0.85rem; color: #ef4444; font-weight: 500; margin-top: 8px;">
                                    กรุณาระบุชื่องาน
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <label class="form-label" for="is_notify_amount" style="font-weight: 700; color: #334155; font-size: 0.95rem; margin-bottom: 10px;">
                                    ต้องระบุจำนวนเงินสำหรับแจ้งยอดผ่าน LINE ลูกค้า <span style="color: #ef4444;">*</span>
                                </label>
                                <select class="form-select" id="is_notify_amount" name="is_notify_amount" style="background-color: #f8fafc; border-radius: 12px; border: none; padding: 14px 18px; font-weight: 600; font-size: 0.95rem; color: #475569; box-shadow: none;">
                                    <option value="1">YES</option>
                                    <option value="0">NO</option>
                                </select>
                            </div> 
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #b4b0b0ff; padding: 24px 32px; gap: 12px; justify-content: flex-end; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 10px; padding: 12px 24px; border: none; font-size: 0.95rem;">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="submitAddTasks()" style="background-color: #1b84ff; font-weight: 700; border-radius: 10px; padding: 12px 28px; border: none; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(27,132,255,0.2);">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal แก้ไขงงาน -->
<div class="modal fade" id="editTasksModal" tabindex="-1" aria-labelledby="editTasksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid #b4b0b0ff; padding: 24px 32px;">
                <h5 class="modal-title" id="editTasksModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem;">แก้ไขงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.85rem; opacity: 0.4;"></button>
            </div>
            <div class="modal-body" style="padding: 24px 32px;">
                <form id="editTasksForm">
                    <input type="hidden" id="edit_tasks_id" name="tasks_id">
                    
                    <div class="mb-4">
                        <label class="form-label" for="edit_task_name" style="font-weight: 700; color: #334155; font-size: 0.95rem; margin-bottom: 10px;">
                            ชื่องาน <span style="color: #ef4444;">*</span>
                        </label>
                        <input class="form-control" type="text" id="edit_task_name" name="task_name" placeholder="เช่น ภ.ง.ด.1" style="background-color: #f8fafc; border-radius: 12px; border: none; padding: 14px 18px; font-weight: 600; font-size: 0.95rem; color: #475569; box-shadow: none;">
                        <div class="invalid-feedback" style="font-size: 0.85rem; color: #ef4444; font-weight: 500; margin-top: 8px;">
                            กรุณาระบุชื่องาน
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label" for="edit_is_notify_amount" style="font-weight: 700; color: #334155; font-size: 0.95rem; margin-bottom: 10px;">
                            ต้องระบุจำนวนเงินสำหรับแจ้งยอดผ่าน LINE ลูกค้า <span style="color: #ef4444;">*</span>
                        </label>
                        <select class="form-select" id="edit_is_notify_amount" name="is_notify_amount" style="background-color: #f8fafc; border-radius: 12px; border: none; padding: 14px 18px; font-weight: 600; font-size: 0.95rem; color: #475569; box-shadow: none;">
                            <option value="1">YES</option>
                            <option value="0">NO</option>
                        </select>
                    </div> 
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #b4b0b0ff; padding: 24px 32px; gap: 12px; justify-content: flex-end; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 10px; padding: 12px 24px; border: none; font-size: 0.95rem;">ยกเลิก</button>
                <button type="button" class="btn btn-warning" onclick="submitEditTask()" style="background-color: #1b84ff; color: #fff; font-weight: 700; border-radius: 10px; padding: 12px 28px; border: none; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(245,158,11,0.2);">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>
<script>
    function moveTask(taskId, direction) {
        const fiscalId = $('input[name="fiscal_id"]').val();
        if (!fiscalId || !taskId) return;

        $.ajax({
            url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/task/move_task',
            type: 'POST',
            data: { 
                tasks_id: taskId,
                direction: direction,
                fiscal_id: fiscalId
            },
            dataType: 'json',
            success: function(response) {
                if(response.result === 1) {
                    location.reload(); // รีเฟรชหน้าเว็บเพื่อดูการเรียงลำดับใหม่
                } else {
                    if(typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: response.msg });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            }
        });
    }

    function modal_edit(taskId) {
        if (!taskId) return;
        
        // Fetch task data
        $.ajax({
            url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/task/get_task',
            type: 'POST',
            data: { tasks_id: taskId },
            dataType: 'json',
            success: function(response) {
                if (response.result === 1 && response.data) {
                    $('#edit_tasks_id').val(response.data.tasks_id);
                    $('#edit_task_name').val(response.data.tasks_name);
                    $('#edit_is_notify_amount').val(response.data.is_notify_amount);
                    
                    $('#edit_task_name').removeClass('is-invalid');
                    var modal = new bootstrap.Modal(document.getElementById('editTasksModal'));
                    modal.show();
                } else {
                    alert('ไม่พบข้อมูลงาน');
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการดึงข้อมูล');
            }
        });
    }

    function submitEditTask() {
        var taskId = $('#edit_tasks_id').val();
        var taskName = $('#edit_task_name').val().trim();
        var isNotify = $('#edit_is_notify_amount').val();

        if (taskName === '') {
            $('#edit_task_name').addClass('is-invalid');
            return;
        }
        $('#edit_task_name').removeClass('is-invalid');

        $.ajax({
            url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/task/edit_task',
            type: 'POST',
            data: { 
                tasks_id: taskId,
                task_name: taskName,
                is_notify_amount: isNotify
            },
            dataType: 'json',
            success: function(response) {
                if(response.result === 1) {
                    $('#editTasksModal').modal('hide');
                    if(typeof Swal !== 'undefined') {
                        sessionStorage.setItem('toast_msg', 'บันทึกสำเร็จ');
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload(); // รีเฟรชทันที
                    } else {
                        alert('บันทึกสำเร็จ');
                        location.reload();
                    }
                } else {
                    if(typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        Toast.fire({ icon: 'error', title: response.msg });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function() {
                if(typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({ icon: 'error', title: 'เกิดข้อผิดพลาดในการบันทึกข้อมูล' });
                } else {
                    alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                }
            }
        });
    }

    function delete_task(taskId, taskName) {
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'ลบงานนี้?',
                text: taskName + ' จะถูกลบออกจากปีทำงานนี้',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ลบข้อมูล',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    processDeleteTask(taskId);
                }
            });
        } else {
            if(confirm('คุณแน่ใจหรือไม่ที่จะลบงาน ' + taskName + '?')) {
                processDeleteTask(taskId);
            }
        }
    }

    function processDeleteTask(taskId) {
        $.ajax({
            url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/task/delete_task',
            type: 'POST',
            data: { tasks_id: taskId },
            dataType: 'json',
            success: function(response) {
                if(response.result === 1) {
                    sessionStorage.setItem('toast_msg', 'ลบข้อมูลสำเร็จ');
                    sessionStorage.setItem('toast_icon', 'success');
                    location.reload();
                } else {
                    if(typeof Swal !== 'undefined') {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        Toast.fire({ icon: 'error', title: response.msg });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการลบข้อมูล');
            }
        });
    }

    let isSubmittingTask = false;

    function modal_addtasks() {
        // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
        const form = document.getElementById('addTasksForm');
        if(form) {
            form.reset();
        }
        // 2. สั่งโชว์ Modal ผ่าน Vanilla JS ของ Bootstrap
        const modalElement = document.getElementById('addTasksModal');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }   

    
    function submitAddTasks() {

        const task_name = $('input[name="task_name"]').val().trim();
        const taskNameInput = $('#task_name');
        
        if (!task_name) {
            taskNameInput.addClass('is-invalid');
            return;
        } else {
            taskNameInput.removeClass('is-invalid');
        }

        if (isSubmittingTask) return;
        isSubmittingTask = true;
        const submitBtn = $('#addTasksModal .btn-primary');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('กำลังบันทึก...');

        var formData = $('#addTasksForm').serialize();
        
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        $.ajax({
            type: "POST",
            url: "/cpd_ac/public/task/add_task",
            data: formData,
            dataType: "json",
            success: function(response) {
                isSubmittingTask = false;
                submitBtn.prop('disabled', false).text(originalBtnText);

                if(response.result === 1) {
                    // ถ้าบันทึกสำเร็จ แจ้งเตือน Toast และปิด Modal
                    if(typeof Swal !== 'undefined') {
                        const modalElement = document.getElementById('addTasksModal');
                        if (modalElement) {
                            const modalInstance = bootstrap.Modal.getInstance(modalElement);
                            if (modalInstance) modalInstance.hide();
                        }
                        sessionStorage.setItem('toast_msg', response.msg);
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload(); 
                    } else {
                        alert(response.msg);
                        location.reload();
                    }
                } else {
                    // ถ้าบันทึกไม่สำเร็จ แจ้งเตือน Error
                    if(typeof Swal !== 'undefined') {
                        Toast.fire({
                            icon: 'error',
                            title: response.msg
                        });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function(err) {
                isSubmittingTask = false;
                submitBtn.prop('disabled', false).text(originalBtnText);
                console.error("AJAX Error:", err);
                if(typeof Swal !== 'undefined') {
                    Toast.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์'
                    });
                } else {
                    alert("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
                }
            }
        });
    }
    </script>


<?php 
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php'; 
?>
