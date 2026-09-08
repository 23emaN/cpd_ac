<?php
// app/views/backoffice/customer.php
$selected_year = $_GET['year'] ?? '2569';
$company_name  = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<style>
    /* Autocomplete Style */
    .autocomplete-input:focus {
        border-color: #10b981 !important; /* Green border like the image */
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.1) !important;
    }
    .autocomplete-list li {
        padding: 10px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.9rem;
        color: #475569;
        display: flex;
        align-items: center;
    }
    .autocomplete-list li::before {
        content: "•";
        color: #94a3b8;
        font-weight: bold;
        display: inline-block; 
        width: 1em;
        margin-right: 8px;
    }
    .autocomplete-list li:hover {
        background-color: #f8fafc;
        color: #0f172a;
    }
    .autocomplete-list li:last-child {
        border-bottom: none;
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                
                <div class="main-card-wrapper">
                    
                    <!-- Page Header Section -->
                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title">พนักงาน</h2>
                            <?php $fy_display = !empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
                            <p class="page-subtitle">ภาพรวมระบบ - พนักงาน - ปี <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <div class="d-flex align-items-end">
                            <button type="button" class="btn-add-action" onclick="modal_addemployee()">
                                <i class="ri-add-line"></i>
                                <span>เพิ่มพนักงาน</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid (4 กล่องสถิติ) -->
                    <?php
                        $employees = $data['employees'] ?? [];
                        $totalEmployees = count($employees);
                        $activeEmployees = 0;
                        $inactiveEmployees = 0;
                        $uniqueTeams = [];
                        foreach ($employees as $emp) {
                            if ($emp['user_status'] == '1') {
                                $activeEmployees++;
                            } else {
                                $inactiveEmployees++;
                            }
                            if (!empty($emp['team_name'])) {
                                $uniqueTeams[$emp['team_name']] = true;
                            }
                        }
                        $totalTeams = count($uniqueTeams);
                    ?>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="ri-checkbox-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $totalEmployees; ?></span>
                                <span class="stat-label">พนักงานทั้งหมด</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $activeEmployees; ?></span>
                                <span class="stat-label">ยังทำงานอยู่</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon purple">
                                <i class="ri-subtract-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $inactiveEmployees; ?></span>
                                <span class="stat-label">เลิกจ้าง</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-group-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo $totalTeams; ?></span>
                                <span class="stat-label">ทีมทั้งหมด</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Toolbar (ค้นหา & ตัวกรองสถานะ) -->
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" placeholder="ค้นหาชื่อ ตำแหน่ง ทีม">
                        </div>

                        <div class="filter-group">
                            <select class="filter-select">
                                <option value="">ทุกสถานะ</option>
                                <option value="1">ใช้งานอยู่</option>
                                <option value="0">เลิกจ้าง</option>
                            </select>

                            <select class="filter-select">
                                <option value="">ทุกทีม</option>
                                <?php foreach (array_keys($uniqueTeams) as $teamName): ?>
                                    <option value="<?php echo htmlspecialchars($teamName); ?>"><?php echo htmlspecialchars($teamName); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <?php require_once 'table/employee_table.php'; ?>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มพนักงานใหม่ -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
        <div class="modal-content" style="border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background-color: #ffffff;">
            
            <!-- Header -->
            <div class="modal-header" style="border-bottom: none; padding: 24px 28px 12px 28px;">
                <h5 class="modal-title" id="addCustomerModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem;">เพิ่มลูกค้าใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.9rem; opacity: 0.4;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 12px 28px 24px 28px;">
                <form id="addEmployeeForm">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="fiscal_id" value="<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>">
                    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($data['active_company_id'] ?? ''); ?>">

                    <!-- Section: ข้อมูลทั่วไป -->
                    <div class="mb-4">
                        <!-- ชื่อผู้ใช้ / รหัสผ่าน -->
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               ชื่อผู้ใช้ <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_name" id="user_name" placeholder="ระบุชื่อผู้ใช้" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อผู้ใช้</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               รหัสผ่าน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_password" id="user_password" placeholder="ระบุรหัสผ่าน" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุรหัสผ่าน</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               ชื่อพนักงาน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_firstname" id="user_firstname" placeholder="ระบุชื่อพนักงาน" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อพนักงาน</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               นามสกุล <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_lastname" id="user_lastname" placeholder="ระบุนามสกุล" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุนามสกุล</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                   ตำแหน่ง
                                </label>
                                <input type="text" class="form-control" name="user_position" id="user_position" placeholder="เช่น Senior Accountant" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                   ทีม
                                </label>
                                <div class="position-relative dropdown-autocomplete">
                                    <input type="text" class="form-control autocomplete-input" name="team_name" id="team_name" placeholder="เช่น ทีมบัญชี A" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-weight: 500; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;" autocomplete="off">
                                    <ul class="dropdown-menu autocomplete-list w-100 shadow-sm" style="max-height: 200px; overflow-y: auto; padding: 0; margin-top: 4px; border: 1px solid #e2e8f0; border-radius: 8px; position: absolute; z-index: 1050;">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: none; padding: 12px 28px 28px 28px; gap: 12px; justify-content: flex-end;">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">ยกเลิก</button>
                <button type="button" class="btn" onclick="submit_addemployee()" style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 28px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal แก้ไขพนักงาน -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 820px;">
        <div class="modal-content" style="border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background-color: #ffffff;">
            
            <!-- Header -->
            <div class="modal-header" style="border-bottom: none; padding: 24px 28px 12px 28px;">
                <h5 class="modal-title" id="editEmployeeModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.25rem;">แก้ไขข้อมูลพนักงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.9rem; opacity: 0.4;"></button>
            </div>

            <!-- Body -->
            <div class="modal-body" style="padding: 12px 28px 24px 28px;">
                <form id="editEmployeeForm">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="mb-4">
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               ชื่อผู้ใช้ <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_name" id="edit_user_name" placeholder="ระบุชื่อผู้ใช้" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               ชื่อพนักงาน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_firstname" id="edit_user_firstname" placeholder="ระบุชื่อพนักงาน" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อพนักงาน</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                               นามสกุล <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control" name="user_lastname" id="edit_user_lastname" placeholder="ระบุนามสกุล" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุนามสกุล</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                   ตำแหน่ง
                                </label>
                                <input type="text" class="form-control" name="user_position" id="edit_user_position" placeholder="เช่น Senior Accountant" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 16px; font-weight: 600; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" style="font-weight: 700; font-size: 0.88rem; color: #1e293b; margin-bottom: 8px;">
                                   ทีม
                                </label>
                                <div class="position-relative dropdown-autocomplete">
                                    <input type="text" class="form-control autocomplete-input" name="team_name" id="edit_team_name" placeholder="ระบุชื่อทีม" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; font-weight: 500; color: #1e293b; font-size: 0.92rem; outline: none; box-shadow: none;" autocomplete="off">
                                    <ul class="dropdown-menu autocomplete-list w-100 shadow-sm" style="max-height: 200px; overflow-y: auto; padding: 0; margin-top: 4px; border: 1px solid #e2e8f0; border-radius: 8px; position: absolute; z-index: 1050;">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 20px 28px; display: flex; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">ยกเลิก</button>
                <button type="button" class="btn" onclick="submit_editemployee()" style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 28px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>


<script>
    const employeesData = <?php echo json_encode($data['employees'] ?? []); ?>;
    const teamsData = <?php echo json_encode($data['teams'] ?? []); ?>;
    let isSubmittingTask = false;

    $(document).ready(function() {
        // Setup Autocomplete
        function renderAutocomplete(inputElem, listElem, query) {
            listElem.empty();
            let matches = teamsData.filter(t => t.team_name.toLowerCase().includes(query.toLowerCase()));
            
            // Limit to 5 items
            matches = matches.slice(0, 5);
            
            if (matches.length > 0) {
                matches.forEach(match => {
                    listElem.append(`<li>${match.team_name}</li>`);
                });
                listElem.show();
            } else {
                listElem.hide();
            }
        }

        $('.autocomplete-input').on('keyup focus', function() {
            let input = $(this);
            let list = input.siblings('.autocomplete-list');
            renderAutocomplete(input, list, input.val());
        });

        // Click item
        $(document).on('click', '.autocomplete-list li', function() {
            let list = $(this).closest('.autocomplete-list');
            let input = list.siblings('.autocomplete-input');
            input.val($(this).text());
            list.hide();
        });

        // Hide when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown-autocomplete').length) {
                $('.autocomplete-list').hide();
            }
        });
    });



    function modal_addemployee() {
        // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
        const form = document.getElementById('addEmployeeForm');
        if(form) {
            form.reset();
        }
        // 2. สั่งโชว์ Modal ผ่าน Vanilla JS ของ Bootstrap
        const modalElement = document.getElementById('addEmployeeModal');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }   

    let isSubmittingEmployee = false;
    function submit_addemployee() {
        if (isSubmittingEmployee) return;

        // Get fields
        const userName = $('#user_name').val().trim();
        const userPassword = $('#user_password').val().trim();
        const userFirstname = $('#user_firstname').val().trim();
        const userLastname = $('#user_lastname').val().trim();

        let isValid = true;

        // Validate user_name
        if (!userName) {
            $('#user_name').addClass('is-invalid');
            isValid = false;
        } else {
            $('#user_name').removeClass('is-invalid');
        }

        // Validate user_password
        if (!userPassword) {
            $('#user_password').addClass('is-invalid');
            isValid = false;
        } else {
            $('#user_password').removeClass('is-invalid');
        }

        // Validate user_firstname
        if (!userFirstname) {
            $('#user_firstname').addClass('is-invalid');
            isValid = false;
        } else {
            $('#user_firstname').removeClass('is-invalid');
        }

        // Validate user_lastname
        if (!userLastname) {
            $('#user_lastname').addClass('is-invalid');
            isValid = false;
        } else {
            $('#user_lastname').removeClass('is-invalid');
        }

        if (!isValid) {
            return; // หยุดการทำงานถ้ากรอกไม่ครบ
        }

        isSubmittingEmployee = true;
        const submitBtn = $('#addEmployeeModal .modal-footer button:last-child');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('กำลังบันทึก...');

        var formData = $('#addEmployeeForm').serialize();

        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/employee/add', // หรือ route ที่คุณต้องการใช้
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                isSubmittingEmployee = false;
                submitBtn.prop('disabled', false).text(originalBtnText);

                if (response.result === 1) {
                    $('#addEmployeeModal').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        sessionStorage.setItem('toast_msg', 'เพิ่มพนักงานสำเร็จ');
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload();
                    } else {
                        alert('เพิ่มพนักงานสำเร็จ');
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
            error: function(err) {
                isSubmittingEmployee = false;
                submitBtn.prop('disabled', false).text(originalBtnText);
                console.error("AJAX Error:", err);
                if (typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({ icon: 'error', title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
                } else {
                    alert("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
                }
            }
        });
    }

       function edit_employee(userId) {
        // หาข้อมูลพนักงานจาก employeesData
        const emp = employeesData.find(e => e.user_id == userId);
        if (emp) {
            $('#edit_user_id').val(emp.user_id);
            $('#edit_user_name').val(emp.user_name);
            $('#edit_user_firstname').val(emp.user_firstname);
            $('#edit_user_lastname').val(emp.user_lastname);
            $('#edit_user_position').val(emp.position);
            $('#edit_team_name').val(emp.team_name);
            
            const modalElement = document.getElementById('editEmployeeModal');
            const myModal = new bootstrap.Modal(modalElement);
            myModal.show();
        } else {
            console.error("ไม่พบข้อมูลพนักงาน");
        }
    }

    function submit_editemployee() {
        var userId = $('#edit_user_id').val();
        var userFirstname = $('#edit_user_firstname').val().trim();
        var userLastname = $('#edit_user_lastname').val().trim();
        var userPosition = $('#edit_user_position').val().trim();
        var userTeamName = $('#edit_team_name').val().trim();

        let isValid = true;

        if (userFirstname === '') {
            $('#edit_user_firstname').addClass('is-invalid');
            isValid = false;
        } else {
            $('#edit_user_firstname').removeClass('is-invalid');
        }

        if (userLastname === '') {
            $('#edit_user_lastname').addClass('is-invalid');
            isValid = false;
        } else {
            $('#edit_user_lastname').removeClass('is-invalid');
        }

        if (!isValid) {
            return;
        }

        const submitBtn = $('#editEmployeeModal .modal-footer button:last-child');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('กำลังอัปเดต...');

        $.ajax({
            url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/employee/edit',
            type: 'POST',
            data: { 
                user_id: userId,
                user_firstname: userFirstname,
                user_lastname: userLastname,
                user_position: userPosition,
                team_name: userTeamName
            },
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).text(originalBtnText);
                if(response.result === 1) {
                    $('#editEmployeeModal').modal('hide');
                    if(typeof Swal !== 'undefined') {
                        sessionStorage.setItem('toast_msg', 'อัปเดตข้อมูลพนักงานสำเร็จ');
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload(); 
                    } else {
                        alert('อัปเดตข้อมูลพนักงานสำเร็จ');
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
            error: function(err) {
                submitBtn.prop('disabled', false).text(originalBtnText);
                console.error(err);
                if(typeof Swal !== 'undefined') {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({ icon: 'error', title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
                } else {
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                }
            }
        });
    }

    function delete_employee(userId, userFirstname) {
        if(typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'ลบข้อมูลพนักงาน?',
                text: userFirstname + ' จะถูกลบออกจากระบบ',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ลบข้อมูล',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    execute_delete(userId);
                }
            });
        } else {
            if(confirm('คุณต้องการลบพนักงาน ' + userFirstname + ' ออกจากระบบหรือไม่?')) {
                execute_delete(userId);
            }
        }
    }

    function execute_delete(userId) {
        $.ajax({
            url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/employee/delete',
            type: 'POST',
            data: { user_id: userId },
            dataType: 'json',
            success: function(response) {
                if(response.result === 1) {
                    if(typeof Swal !== 'undefined') {
                        sessionStorage.setItem('toast_msg', 'ลบพนักงานสำเร็จ');
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload(); 
                    } else {
                        alert('ลบพนักงานสำเร็จ');
                        location.reload();
                    }
                } else {
                    if(typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'ผิดพลาด',
                            text: response.msg
                        });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function(err) {
                console.error(err);
                if(typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์'
                    });
                } else {
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                }
            }
        });
    }

</script>

<?php 
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php'; 
?>
