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
    .status-info { background-color: #e0e7ff; color: #4f46e5; } /* ม่วงคราม (รอตรวจ) */

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
                    <div class="d-flex justify-content-between mb-6 align-items-center">
                        <form method="GET" action="" id="filterForm" class="d-flex gap-2">
                            <select class="form-select" style="width: 300px;" name="assignee_id" id="filter_assignee_id">
                                <option value="">ผู้รับผิดชอบทั้งหมด</option>
                                <?php if (!empty($data['employees'])): ?>
                                    <?php foreach ($data['employees'] as $emp): ?>
                                        <option value="<?php echo htmlspecialchars($emp['user_id']); ?>" <?php echo ($data['filters']['assignee_id'] == $emp['user_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($emp['user_firstname'] . ' ' . $emp['user_lastname']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <select class="form-select" style="width: 200px;" name="status" id="filter_status">
                                <option value="">สถานะงานทั้งหมด</option>
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
                    <input type="hidden" name="assign_id" id="assign_id">

                    <div class="mb-3">
                        <label class="form-label">ชื่องาน<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="assign_title" id="assign_title" required placeholder="ระบุชื่องาน">
                        <div class="invalid-feedback">กรุณาระบุชื่องาน</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">รายละเอียดงาน</label>
                        <textarea class="form-control" name="assign_detail" id="assign_detail" rows="3" placeholder="ระบุรายละเอียดเพิ่มเติม..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">กำหนดส่ง <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control pe-5" name="due_date" id="due_date" required placeholder="วว/ดด/ปปปป">
                                <i class="ri-calendar-line position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="pointer-events: none;"></i>
                            </div>
                            <div class="text-danger mt-1" id="due_date_error" style="font-size: 0.875rem; display: none;">กรุณาระบุกำหนดส่ง</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ลูกค้า</label>
                        <select class="form-select" name="customer_id" id="customer_id">
                            <option value="">ไม่ระบุลูกค้า</option>
                            <?php if (!empty($data['customers'])): ?>
                                <?php foreach ($data['customers'] as $cust): ?>
                                    <option value="<?php echo htmlspecialchars($cust['customer_id']); ?>">
                                        <?php echo htmlspecialchars($cust['customer_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ผู้รับผิดชอบ <span class="text-danger">*</span></label>
                        <select class="form-select" name="user_id" id="user_id" required>
                            <option value="">เลือกพนักงาน</option>
                            <?php if (!empty($data['employees'])): ?>
                                <?php foreach ($data['employees'] as $emp): ?>
                                    <option value="<?php echo htmlspecialchars($emp['user_id']); ?>">
                                        <?php echo htmlspecialchars($emp['user_firstname'] . ' ' . $emp['user_lastname']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <div class="text-danger mt-1" id="user_id_error" style="font-size: 0.875rem; display: none;">กรุณาเลือกผู้รับผิดชอบ</div>
                    </div>

                    <div class="mb-3" id="status_container" style="display: none;">
                        <label class="form-label">สถานะงาน</label>
                        <select class="form-select select2-modal" name="assign_status" id="assign_status">
                            <option value="0">ดำเนินการ</option>
                            <option value="1">รอตรวจ</option>
                            <option value="3">ปิดงาน</option>
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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 === 'function') {
            // Modal Selects
            $('#customer_id, #user_id, #assign_status').select2({
                dropdownParent: $('#assignTaskModal .modal-content'),
                width: '100%'
            });

            // Filter Selects
            $('#filter_assignee_id').select2({ width: '300px' });
            $('#filter_status').select2({ width: '200px' });

            $('#filter_assignee_id, #filter_status').on('change', function() {
                $('#filterForm').submit();
            });
        }

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
        $('#customer_id').val('').trigger('change');
        $('#user_id').val('').trigger('change');
        
        document.getElementById('status_container').style.display = 'none';
        $('#assign_status').val('0').trigger('change');
        
        var assignModal = new bootstrap.Modal(document.getElementById('assignTaskModal'));
        assignModal.show();
    }
    
    function modal_edit_assign(task) {
        // เคลียร์ฟอร์มก่อน
        document.getElementById('assignTaskForm').reset();
        
        // ใส่ข้อมูลเดิมลงในฟอร์ม
        $('#assign_id').val(task.assign_id);
        $('#assign_title').val(task.assign_title);
        $('#assign_detail').val(task.assign_detail);
        
        // จัดการ DatePicker (Flatpickr)
        if (typeof flatpickr !== 'undefined') {
            const fp = document.getElementById("due_date")._flatpickr;
            if (fp) {
                fp.setDate(task.due_date);
            } else {
                $('#due_date').val(task.due_date);
            }
        } else {
            $('#due_date').val(task.due_date);
        }
        
        $('#user_id').val(task.user_id || task.assignee_id).trigger('change');
        $('#customer_id').val(task.customer_id || '').trigger('change');
        
        // สถานะงาน
        document.getElementById('status_container').style.display = 'block';
        $('#assign_status').val(task.assign_status !== undefined ? task.assign_status : '0').trigger('change');
        
        // แสดง Modal
        var assignModal = new bootstrap.Modal(document.getElementById('assignTaskModal'));
        assignModal.show();
    }
    
    function saveAssignTask() {
        let isValid = true;
        
        const title = $('#assign_title').val().trim();
        if (title === '') {
            $('#assign_title').addClass('is-invalid');
            isValid = false;
        } else {
            $('#assign_title').removeClass('is-invalid');
        }

        const dueDate = $('#due_date').val().trim();
        if (dueDate === '') {
            const fpInput = $('#due_date').siblings('.flatpickr-input, .form-control');
            if (fpInput.length) {
                fpInput.addClass('is-invalid');
            } else {
                $('#due_date').addClass('is-invalid');
            }
            $('#due_date_error').show();
            isValid = false;
        } else {
            const fpInput = $('#due_date').siblings('.flatpickr-input, .form-control');
            if (fpInput.length) {
                fpInput.removeClass('is-invalid');
            } else {
                $('#due_date').removeClass('is-invalid');
            }
            $('#due_date_error').hide();
        }

        const userId = $('#user_id').val();
        if (!userId || userId === '') {
            $('#user_id').next('.select2-container').find('.select2-selection').css('border-color', '#dc3545');
            $('#user_id_error').show();
            isValid = false;
        } else {
            $('#user_id').next('.select2-container').find('.select2-selection').css('border-color', '');
            $('#user_id_error').hide();
        }

        if (!isValid) {
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

<?php if (isset($_GET['assign_id']) && !empty($_GET['assign_id'])): ?>
    <?php
        $autoOpenTask = null;
        if (!empty($data['tasks'])) {
            foreach ($data['tasks'] as $task) {
                if ($task['assign_id'] == $_GET['assign_id']) {
                    $autoOpenTask = $task;
                    break;
                }
            }
        }
        
        if (!$autoOpenTask) {
            require_once dirname(__DIR__, 2) . '/models/AssignTaskModel.php';
            $assignTaskModel = new \AssignTaskModel();
            $autoOpenTask = $assignTaskModel->getAssignTaskById($_GET['assign_id']);
        }
    ?>
    <?php if ($autoOpenTask): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var taskData = <?php echo json_encode($autoOpenTask, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
                setTimeout(function() {
                    if (typeof modal_edit_assign === 'function') {
                        modal_edit_assign(taskData);
                    }
                }, 500);
            });
        </script>
    <?php endif; ?>
<?php endif; ?>

<?php require_once dirname(__DIR__) . '/main/footer.php'; ?>
