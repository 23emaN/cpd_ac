<?php
// app/views/backoffice/tasks.php
$selected_year = $_GET['year'] ?? '2569';
$company_name = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';
?>



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
                            <p class="page-subtitle">ภาพรวมระบบ - ตั้งค่างานที่ต้องทำ - ปี
                                <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <button type="button" class="btn-add-action" onclick="modal_addtasks()">
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
                                <span
                                    class="stat-val"><?php echo number_format($data['req_amount_count'] ?? 0); ?></span>
                                <span class="stat-label">ต้องระบุจำนวนเงิน</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-subtract-line"></i>
                            </div>
                            <div class="stat-info">
                                <span
                                    class="stat-val"><?php echo number_format($data['no_req_amount_count'] ?? 0); ?></span>
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
    <div class="modal-dialog modal-dialog-centered mw-550px">
        <div class="modal-content modal-content-keen">
            <div class="modal-header modal-header-keen">
                <h4 class="modal-title" id="addTasksModalLabel" style="font-weight: 600;">เพิ่มงานใหม่</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-keen py-8">
                <form id="addTasksForm">
                    <!-- ส่ง fiscal_id ปัจจุบันไปด้วย -->
                    <input type="hidden" name="fiscal_id"
                        value="<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>">

                    <div class="mb-4">
                        <label class="required form-label form-label-keen" for="task_name">ชื่องาน</label>
                        <input class="form-control form-control-solid" type="text" id="task_name" name="task_name"
                            placeholder="เช่น ภ.ง.ด.1">
                        <div class="invalid-feedback"
                            style="font-size: 0.85rem; color: #ef4444; font-weight: 500; margin-top: 6px;">
                            กรุณาระบุชื่องาน
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="required form-label form-label-keen"
                            for="is_notify_amount">ต้องระบุจำนวนเงินสำหรับแจ้งยอดผ่าน LINE ลูกค้า</label>
                        <select class="form-select form-select-solid" id="is_notify_amount" name="is_notify_amount">
                            <option value="1">YES</option>
                            <option value="0">NO</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer modal-footer-keen">
                <button type="button" class="btn btn-light-keen" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary-keen" onclick="submitAddTasks()">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal แก้ไขงาน -->
<div class="modal fade" id="editTasksModal" tabindex="-1" aria-labelledby="editTasksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-550px">
        <div class="modal-content modal-content-keen">
            <div class="modal-header modal-header-keen">
                <h4 class="modal-title" id="editTasksModalLabel" style="font-weight: 600;">แก้ไขงาน</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-keen py-8">
                <form id="editTasksForm">
                    <input type="hidden" id="edit_tasks_id" name="tasks_id">

                    <div class="mb-4">
                        <label class="required form-label form-label-keen" for="edit_task_name">ชื่องาน</label>
                        <input class="form-control form-control-solid" type="text" id="edit_task_name" name="task_name"
                            placeholder="เช่น ภ.ง.ด.1">
                        <div class="invalid-feedback"
                            style="font-size: 0.85rem; color: #ef4444; font-weight: 500; margin-top: 6px;">
                            กรุณาระบุชื่องาน
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="required form-label form-label-keen"
                            for="edit_is_notify_amount">ต้องระบุจำนวนเงินสำหรับแจ้งยอดผ่าน LINE ลูกค้า</label>
                        <select class="form-select form-select-solid" id="edit_is_notify_amount"
                            name="is_notify_amount">
                            <option value="1">YES</option>
                            <option value="0">NO</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer modal-footer-keen">
                <button type="button" class="btn btn-light-keen" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary-keen" onclick="submitEditTask()">บันทึกข้อมูล</button>
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
            success: function (response) {
                if (response.result === 1) {
                    location.reload(); // รีเฟรชหน้าเว็บเพื่อดูการเรียงลำดับใหม่
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: response.msg });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function () {
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
            success: function (response) {
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
            error: function () {
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
            success: function (response) {
                if (response.result === 1) {
                    $('#editTasksModal').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        sessionStorage.setItem('toast_msg', 'บันทึกสำเร็จ');
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload(); // รีเฟรชทันที
                    } else {
                        alert('บันทึกสำเร็จ');
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
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
            error: function () {
                if (typeof Swal !== 'undefined') {
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
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'ลบงานนี้?',
                text: taskName + ' จะถูกลบออกจากปีทำงานนี้',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ลบข้อมูล',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    processDeleteTask(taskId);
                }
            });
        } else {
            if (confirm('คุณแน่ใจหรือไม่ที่จะลบงาน ' + taskName + '?')) {
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
            success: function (response) {
                if (response.result === 1) {
                    sessionStorage.setItem('toast_msg', 'ลบข้อมูลสำเร็จ');
                    sessionStorage.setItem('toast_icon', 'success');
                    location.reload();
                } else {
                    if (typeof Swal !== 'undefined') {
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
            error: function () {
                alert('เกิดข้อผิดพลาดในการลบข้อมูล');
            }
        });
    }

    let isSubmittingTask = false;

    function modal_addtasks() {
        // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
        const form = document.getElementById('addTasksForm');
        if (form) {
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
            success: function (response) {
                isSubmittingTask = false;
                submitBtn.prop('disabled', false).text(originalBtnText);

                if (response.result === 1) {
                    // ถ้าบันทึกสำเร็จ แจ้งเตือน Toast และปิด Modal
                    if (typeof Swal !== 'undefined') {
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
                    if (typeof Swal !== 'undefined') {
                        Toast.fire({
                            icon: 'error',
                            title: response.msg
                        });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function (err) {
                isSubmittingTask = false;
                submitBtn.prop('disabled', false).text(originalBtnText);
                console.error("AJAX Error:", err);
                if (typeof Swal !== 'undefined') {
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