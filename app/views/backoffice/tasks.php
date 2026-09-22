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

                    <!-- Filter Toolbar -->
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" id="tasksSearchInput" class="search-input" placeholder="ค้นหางาน...">
                        </div>

                        <div class="filter-group">
                            <select id="tasksPerPage" class="filter-select">
                                <option value="25">25 รายการ</option>
                                <option value="50">50 รายการ</option>
                                <option value="75">75 รายการ</option>
                                <option value="100">100 รายการ</option>
                            </select>
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 550px;">
        <div class="modal-content modal-content-custom">
            <!-- Header (Fixed) -->
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title modal-title-custom" id="addTasksModalLabel">เพิ่มงานใหม่</h5>
                <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Body (Scrollable) -->
            <div class="modal-body modal-body-custom">
                <form id="addTasksForm">
                    <!-- ส่ง fiscal_id ปัจจุบันไปด้วย -->
                    <input type="hidden" name="fiscal_id" value="<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>">

                    <div class="mb-4">
                        <label class="modal-form-label" for="task_name">
                            ชื่องาน <span style="color: #ef4444;">*</span>
                        </label>
                        <input class="form-control modal-form-control" type="text" id="task_name" name="task_name" placeholder="เช่น ภ.ง.ด.1">
                        <div class="invalid-feedback" style="font-size: 0.85rem; color: #ef4444; font-weight: 500; margin-top: 6px;">
                            กรุณาระบุชื่องาน
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="modal-form-label" for="is_notify_amount">
                            ต้องระบุจำนวนเงินสำหรับแจ้งยอดผ่าน LINE ลูกค้า <span style="color: #ef4444;">*</span>
                        </label>
                        <select class="form-select modal-form-select" id="is_notify_amount" name="is_notify_amount">
                            <option value="1">YES</option>
                            <option value="0">NO</option>
                        </select>
                    </div>
                </form>
            </div>
            <!-- Footer (Fixed) -->
            <div class="modal-footer modal-footer-custom">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">ยกเลิก</button>
                <button type="button" class="btn" onclick="submitAddTasks()" style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 28px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal แก้ไขงาน -->
<div class="modal fade" id="editTasksModal" tabindex="-1" aria-labelledby="editTasksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 550px;">
        <div class="modal-content modal-content-custom">
            <!-- Header (Fixed) -->
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title modal-title-custom" id="editTasksModalLabel">แก้ไขงาน</h5>
                <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- Body (Scrollable) -->
            <div class="modal-body modal-body-custom">
                <form id="editTasksForm">
                    <input type="hidden" id="edit_tasks_id" name="tasks_id">

                    <div class="mb-4">
                        <label class="modal-form-label" for="edit_task_name">
                            ชื่องาน <span style="color: #ef4444;">*</span>
                        </label>
                        <input class="form-control modal-form-control" type="text" id="edit_task_name" name="task_name" placeholder="เช่น ภ.ง.ด.1">
                        <div class="invalid-feedback" style="font-size: 0.85rem; color: #ef4444; font-weight: 500; margin-top: 6px;">
                            กรุณาระบุชื่องาน
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="modal-form-label" for="edit_is_notify_amount">
                            ต้องระบุจำนวนเงินสำหรับแจ้งยอดผ่าน LINE ลูกค้า <span style="color: #ef4444;">*</span>
                        </label>
                        <select class="form-select modal-form-select" id="edit_is_notify_amount" name="is_notify_amount">
                            <option value="1">YES</option>
                            <option value="0">NO</option>
                        </select>
                    </div>
                </form>
            </div>
            <!-- Footer (Fixed) -->
            <div class="modal-footer modal-footer-custom">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">ยกเลิก</button>
                <button type="button" class="btn" onclick="submitEditTask()" style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 28px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>
<script>
    function moveTask(taskId, direction, btn) {
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
                    // แอนิเมชั่นสลับแถวและโชว์ Swal โทสต์
                    if (btn) {
                        const tr = $(btn).closest('tr');
                        if (direction === 'up') {
                            const prevTr = tr.prev('tr');
                            if (prevTr.length) {
                                const tempSeq = tr.find('td:first').text();
                                tr.find('td:first').text(prevTr.find('td:first').text());
                                prevTr.find('td:first').text(tempSeq);
                                tr.insertBefore(prevTr);
                            }
                        } else if (direction === 'down') {
                            const nextTr = tr.next('tr');
                            if (nextTr.length) {
                                const tempSeq = tr.find('td:first').text();
                                tr.find('td:first').text(nextTr.find('td:first').text());
                                nextTr.find('td:first').text(tempSeq);
                                tr.insertAfter(nextTr);
                            }
                        }
                    }

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'เลื่อนลำดับงานสำเร็จ',
                            showConfirmButton: true,
                            confirmButtonColor: '#0066fe',
                            confirmButtonText: 'ตกลง'
                        });
                    }
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
                reverseButtons: true,
                focusConfirm: false,
                focusCancel: false,
                allowEnterKey: false,
                didOpen: () => {
                    if (Swal.getConfirmButton()) Swal.getConfirmButton().blur();
                    if (Swal.getCancelButton()) Swal.getCancelButton().blur();
                }
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
            url: "<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/task/add_task",
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
    // Pagination & Search Logic
    let currentTasksPage = 1;
    let filterTasksTimeout = null;

    $(document).ready(function() {
        filterTasksTable();

        $('#tasksPerPage').on('change', function() {
            currentTasksPage = 1;
            filterTasksTable();
        });

        $('#tasksSearchInput').on('keyup', function() {
            currentTasksPage = 1;
            clearTimeout(filterTasksTimeout);
            filterTasksTimeout = setTimeout(filterTasksTable, 300);
        });
    });

    window.GetData = function(page) {
        currentTasksPage = page;
        filterTasksTable();
    };

    function filterTasksTable() {
        const tbody = $('.table-container-card table tbody');
        const perPage = parseInt($('#tasksPerPage').val()) || 25;
        const searchText = ($('#tasksSearchInput').val() || '').trim().toLowerCase();
        
        const allRows = tbody.find('tr').not(':has(td[colspan="5"])');
        
        if (allRows.length === 0) return;

        let matchedRows = [];

        allRows.each(function() {
            const rowText = $(this).text().toLowerCase();
            if (searchText === '' || rowText.includes(searchText)) {
                matchedRows.push($(this));
            } else {
                $(this).hide();
            }
        });

        const totalRows = matchedRows.length;
        const totalPages = Math.ceil(totalRows / perPage) || 1;

        if (currentTasksPage > totalPages) {
            currentTasksPage = totalPages;
        }

        const startIndex = (currentTasksPage - 1) * perPage;
        const endIndex = startIndex + perPage;

        matchedRows.forEach(function(row, index) {
            if (index >= startIndex && index < endIndex) {
                row.show();
                // Update sequence number visually based on filtered index
                row.find('td:first').text(index + 1);
            } else {
                row.hide();
            }
        });

        $('#tasksTotalCount').text(totalRows.toLocaleString());

        // Manage Empty State
        tbody.find('.no-result-row').remove();
        if (totalRows === 0) {
            tbody.append(`
                <tr class="no-result-row">
                    <td colspan="5" class="text-center py-5 text-muted fw-medium">
                        <div class="list-empty-icon mb-2">
                            <span class="material-symbols-outlined" aria-hidden="true" style="font-size: 48px; color: #ccc;">search_off</span>
                        </div>
                        <div class="list-empty-title text-muted" style="font-weight: 500;">ไม่พบข้อมูลงานที่ค้นหา</div>
                    </td>
                </tr>
            `);
        }

        renderTasksPagination(totalRows, totalPages, currentTasksPage, startIndex, Math.min(endIndex, totalRows));
    }

    function renderTasksPagination(totalRows, totalPages, currentPage, startIndex, endIndex) {
        const container = $('#tasksPaginationContainer');
        if (container.length === 0) return;

        let html = `
            <span class="text-secondary">แสดง ${startIndex + 1}-${endIndex} จาก ${totalRows} รายการ</span>
            <nav aria-label="pagination">
                <ul class="pagination mb-0">
                    <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="window.GetData(1)">หน้าแรก</a>
                    </li>
                    <li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="window.GetData(${currentPage - 1})">ก่อนหน้า</a>
                    </li>
        `;

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="javascript:void(0);" onclick="window.GetData(1)">1</a></li>`;
            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="javascript:void(0);" onclick="window.GetData(${i})">${i}</a>
                </li>
            `;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
            }
            html += `<li class="page-item"><a class="page-link" href="javascript:void(0);" onclick="window.GetData(${totalPages})">${totalPages}</a></li>`;
        }

        html += `
                    <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="window.GetData(${currentPage + 1})">ถัดไป</a>
                    </li>
                    <li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="window.GetData(${totalPages})">หน้าสุดท้าย</a>
                    </li>
                </ul>
            </nav>
        `;

        container.html(html);
    }
</script>


<?php
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>