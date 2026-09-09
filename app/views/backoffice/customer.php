<?php
    // app/views/backoffice/customer.php
    $selected_year          = $_GET['year'] ?? '2569';
    $company_name           = $_GET['company'] ?? 'TEST ACCOUNTING';
    $show_company_workspace = true;

    // 1. นำ Header เข้ามา
    require_once dirname(__DIR__) . '/main/header.php';

    // 2. นำ Sidebar เข้ามา
    require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<style>
    .modal-dialog-custom {
        max-width: 1000px;
    }
    .modal-form-control-highlight {
        background-color: #eff6ff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        font-weight: 600;
        color: #1e293b;
        font-size: 0.92rem;
        outline: none;
        box-shadow: none;
    }
    .modal-input-icon-wrap {
        position: relative;
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
                            <h2 class="page-title">ลูกค้า</h2>
                            <?php $fy_display = !empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
                            <p class="page-subtitle">ภาพรวมระบบ - ลูกค้า - ปี <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-excel-action" onclick="alert('Import Excel')">
                                <i class="ri-file-upload-line"></i>
                                <span>Import Excel</span>
                            </button>
                            <button type="button" class="btn-excel-action" onclick="alert('ส่งออก Excel')">
                                <i class="ri-upload-2-line"></i>
                                <span>ส่งออก Excel</span>
                            </button>
                            <button type="button" class="btn-add-action" onclick="modal_add_customer()">
                                <i class="ri-add-line"></i>
                                <span>เพิ่มลูกค้า</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stats Grid (4 กล่องสถิติ) -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon blue">
                                <i class="ri-user-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($data['stats']['total_customers'] ?? 0); ?></span>
                                <span class="stat-label">ลูกค้าทั้งหมด</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon green">
                                <i class="ri-checkbox-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($data['stats']['active_customers'] ?? 0); ?></span>
                                <span class="stat-label">ใช้บริการอยู่</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon red">
                                <i class="ri-close-circle-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($data['stats']['inactive_customers'] ?? 0); ?></span>
                                <span class="stat-label">เลิกจ้าง</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon yellow">
                                <i class="ri-wallet-3-line"></i>
                            </div>
                            <div class="stat-info">
                                <span class="stat-val"><?php echo number_format($data['stats']['total_accounts_amount'] ?? 0, 2); ?></span>
                                <span class="stat-label">ค่าบัญชีต่อเดือน</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Toolbar (ค้นหา & ตัวกรองสถานะ) -->
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" placeholder="ค้นหาชื่อลูกค้า ผู้ดูแล ทีม">
                        </div>

                        <div class="filter-group">
                            <select class="filter-select">
                                <option value="">ทุกสถานะ</option>
                                <option value="1">ใช้บริการอยู่</option>
                                <option value="0">เลิกจ้าง</option>
                            </select>

                            <select class="filter-select">
                                <option value="">ทุกผู้ดูแล</option>
                                <?php if (! empty($data['caretakers'])): ?>
                                    <?php foreach ($data['caretakers'] as $caretaker): ?>
                                        <option value="<?php echo htmlspecialchars($caretaker['user_id'] ?? ''); ?>">
                                            <?php echo htmlspecialchars(($caretaker['user_firstname'] ?? '') . ' ' . ($caretaker['lastname'] ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <?php require_once 'table/customer_table.php'; ?>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่มลูกค้าใหม่ (Header/Footer Fixed, มีแต่ Body ที่ Scroll) -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-dialog-custom">
        <div class="modal-content modal-content-custom">

            <!-- Header (Fixed) -->
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title modal-title-custom" id="addCustomerModalLabel">เพิ่มลูกค้าใหม่</h5>
                <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body (Scrollable) -->
            <div class="modal-body modal-body-custom">
                <form id="addCustomerForm">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="fiscal_id" value="<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>">
                    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($data['active_company_id'] ?? ''); ?>">
                    <input type="hidden" name="customer_id" id="edit_customer_id" value="">

                    <!-- Section: ข้อมูลทั่วไป -->
                    <div class="mb-4">
                        <h6 class="modal-section-title">ข้อมูลทั่วไป</h6>

                        <!-- ชื่อบริษัท / กิจการ -->
                        <div class="mb-3">
                            <label class="form-label">
                                ชื่อบริษัท / กิจการ <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control modal-form-control" name="customer_name" id="customer_name" required placeholder="">
                        </div>

                        <!-- 3 คอลัมน์: เดือนที่เริ่มให้บริการ / เดือนสิ้นสุด / สถานะลูกค้า -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">เดือนที่เริ่มให้บริการ</label>
                                <select class="form-select modal-form-select" name="service_start_date" id="service_start_date">
                                    <option value="1" selected>มกราคม</option>
                                    <option value="2">กุมภาพันธ์</option>
                                    <option value="3">มีนาคม</option>
                                    <option value="4">เมษายน</option>
                                    <option value="5">พฤษภาคม</option>
                                    <option value="6">มิถุนายน</option>
                                    <option value="7">กรกฎาคม</option>
                                    <option value="8">สิงหาคม</option>
                                    <option value="9">กันยายน</option>
                                    <option value="10">ตุลาคม</option>
                                    <option value="11">พฤศจิกายน</option>
                                    <option value="12">ธันวาคม</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">เดือนสิ้นสุดการให้บริการ</label>
                                <select class="form-select modal-form-select" name="service_start_end" id="service_start_end">
                                    <option value="0" selected>ยังให้บริการอยู่</option>
                                    <option value="1">มกราคม</option>
                                    <option value="2">กุมภาพันธ์</option>
                                    <option value="3">มีนาคม</option>
                                    <option value="4">เมษายน</option>
                                    <option value="5">พฤษภาคม</option>
                                    <option value="6">มิถุนายน</option>
                                    <option value="7">กรกฎาคม</option>
                                    <option value="8">สิงหาคม</option>
                                    <option value="9">กันยายน</option>
                                    <option value="10">ตุลาคม</option>
                                    <option value="11">พฤศจิกายน</option>
                                    <option value="12">ธันวาคม</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">สถานะลูกค้า</label>
                                <select class="form-select modal-form-select" name="active_status">
                                    <option value="1" selected>ใช้บริการอยู่</option>
                                    <option value="0">เลิกจ้าง</option>
                                </select>
                            </div>
                        </div>

                        <!-- 2 คอลัมน์: ผู้ดูแล / ทีม -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ผู้ดูแล</label>
                                <select class="form-select modal-form-select" name="user_id" id="user_id_select" onchange="updateTeamInfo()">
                                    <option value="" data-team-id="" data-team-name="" selected>ยังไม่ระบุผู้ดูแล</option>
                                    <?php if (! empty($data['caretakers'])): ?>
                                        <?php foreach ($data['caretakers'] as $caretaker): ?>
                                            <option value="<?php echo htmlspecialchars($caretaker['user_id'] ?? ''); ?>"
                                                    data-team-id="<?php echo htmlspecialchars($caretaker['team_id'] ?? ''); ?>"
                                                    data-team-name="<?php echo htmlspecialchars($caretaker['team_name'] ?? ''); ?>">
                                                <?php echo htmlspecialchars(($caretaker['user_firstname'] ?? '') . ' ' . ($caretaker['lastname'] ?? '')); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ทีม</label>
                                <input type="hidden" name="team_id" id="team_id_hidden">
                                <input type="text" class="form-control modal-form-control" id="team_name_display" placeholder="เช่น ทีม A" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: ข้อมูลบัญชี -->
                    <div>
                        <h6 class="modal-section-title">ข้อมูลบัญชี</h6>

                        <!-- แถวที่ 1: ปิดงบประจำปี / วันสิ้นรอบบัญชี / ค่าทำบัญชีต่อเดือน -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">ปิดงบประจำปี</label>
                                <select class="form-select modal-form-select" name="closing_status" id="closing_status">
                                    <option value="0" selected>ปิดงบประจำปี</option>
                                    <option value="1">ไม่ปิดงบ</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">วันสิ้นรอบบัญชี</label>
                                <div class="modal-input-icon-wrap">
                                    <input type="text" class="form-control modal-form-control modal-input-with-icon" name="fiscal_closing_date" id="fiscal_closing_date" value="31/12/2026" placeholder="31/12/2026">
                                    <i class="ri-calendar-line modal-input-icon modal-input-icon-static"></i>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">ค่าทำบัญชีต่อเดือน</label>
                                <input type="number" step="0.01" class="form-control modal-form-control" name="accounts_amount" id="accounts_amount" value="2000" placeholder="2000">
                            </div>
                        </div>

                        <!-- แถวที่ 2: จด VAT / มีพนักงาน / ประกันสังคม -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">จด VAT</label>
                                <select class="form-select modal-form-select" name="is_vat">
                                    <option value="0" selected>ไม่จด VAT</option>
                                    <option value="1">จด VAT</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">มีพนักงาน</label>
                                <select class="form-select modal-form-select" name="is_employees">
                                    <option value="0" selected>ไม่มีพนักงาน</option>
                                    <option value="1">มีพนักงาน</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">ประกันสังคม</label>
                                <select class="form-select modal-form-select" name="is_social_security">
                                    <option value="0" selected>ไม่มีประกันสังคม</option>
                                    <option value="1">มีประกันสังคม</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: ข้อมูลติดต่อและเอกสาร -->
                    <div>
                        <h6 class="modal-section-title">ข้อมูลติดต่อและเอกสาร</h6>

                        <!-- แถวที่ 1: เบอร์ติดต่อ / อีเมล / LINE ID -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">เบอร์ติดต่อ</label>
                                <input type="text" class="form-control modal-form-control" name="contact_tel" placeholder="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">อีเมล</label>
                                <input type="email" class="form-control modal-form-control" name="contact_email" placeholder="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">LINE ID</label>
                                <input type="text" class="form-control modal-form-control-highlight" name="contact_line_id" placeholder="TBacc">
                            </div>
                        </div>

                        <!-- URL เก็บไฟล์เอกสารลูกค้า -->
                        <div class="mb-3">
                            <label class="form-label modal-form-label">URL เก็บไฟล์เอกสารลูกค้า</label>
                            <input type="text" class="form-control modal-form-control" name="doc_url" placeholder="เช่น https://drive.google.com/...">
                        </div>

                        <!-- LINE Group ID / Token -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label modal-form-label mb-0">LINE Group ID / Token</label>
                                <button type="button" class="btn btn-sm modal-video-btn">
                                    <i class="ri-play-circle-line" style="margin-right: 4px;"></i> ดูวิดีโอสอน
                                </button>
                            </div>
                            <input type="text" class="form-control modal-form-control" name="line_token" placeholder="กรอก LINE Group ID หรือ Token สำหรับส่งข้อความ">
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: ระบบราชการ -->
                    <div>
                        <h6 class="modal-section-title">ระบบราชการ</h6>

                        <!-- กรมสรรพากร -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">กรมสรรพากร - User</label>
                                <input type="text" class="form-control modal-form-control" name="rd_user" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">กรมสรรพากร - Password</label>
                                <div class="modal-input-icon-wrap">
                                    <input type="password" class="form-control modal-form-control modal-input-with-icon" name="rd_password" placeholder="">
                                    <i class="ri-eye-line modal-input-icon modal-input-icon-clickable" onclick="const input = this.previousElementSibling; if(input.type === 'password'){ input.type='text'; this.classList.remove('ri-eye-line'); this.classList.add('ri-eye-off-line'); } else { input.type='password'; this.classList.remove('ri-eye-off-line'); this.classList.add('ri-eye-line'); }"></i>
                                </div>
                            </div>
                        </div>

                        <!-- กรมพัฒน์ -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">กรมพัฒน์ - User</label>
                                <input type="text" class="form-control modal-form-control" name="dbd_user" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">กรมพัฒน์ - Password</label>
                                <div class="modal-input-icon-wrap">
                                    <input type="password" class="form-control modal-form-control modal-input-with-icon" name="dbd_password" placeholder="">
                                    <i class="ri-eye-line modal-input-icon modal-input-icon-clickable" onclick="const input = this.previousElementSibling; if(input.type === 'password'){ input.type='text'; this.classList.remove('ri-eye-line'); this.classList.add('ri-eye-off-line'); } else { input.type='password'; this.classList.remove('ri-eye-off-line'); this.classList.add('ri-eye-line'); }"></i>
                                </div>
                            </div>
                        </div>

                        <!-- ประกันสังคม -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ประกันสังคม - User</label>
                                <input type="text" class="form-control modal-form-control" name="sso_user" placeholder="">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ประกันสังคม - Password</label>
                                <div class="modal-input-icon-wrap">
                                    <input type="password" class="form-control modal-form-control modal-input-with-icon" name="sso_password" placeholder="">
                                    <i class="ri-eye-line modal-input-icon modal-input-icon-clickable" onclick="const input = this.previousElementSibling; if(input.type === 'password'){ input.type='text'; this.classList.remove('ri-eye-line'); this.classList.add('ri-eye-off-line'); } else { input.type='password'; this.classList.remove('ri-eye-off-line'); this.classList.add('ri-eye-line'); }"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: งานรายเดือนที่ไม่ต้องทำ -->
                    <div>
                        <h6 class="modal-section-title">งานรายเดือนที่ไม่ต้องทำ</h6>

                        <!-- Checkboxes Grid -->
                        <div class="row g-3">
                            <?php if (! empty($data['tasks'])): ?>
                                <?php
                                    $totalTasks = count($data['tasks']);
                                    $half       = ceil($totalTasks / 2);
                                    $leftTasks  = array_slice($data['tasks'], 0, $half);
                                    $rightTasks = array_slice($data['tasks'], $half);
                                ?>
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="d-flex flex-column gap-2">
                                        <?php foreach ($leftTasks as $task): ?>
                                            <label class="d-flex align-items-center modal-checkbox-item">
                                                <input type="checkbox" name="monthly_skip[]" value="<?php echo htmlspecialchars($task['tasks_id'] ?? ''); ?>" class="form-check-input modal-checkbox-input me-2">
                                                <span class="modal-checkbox-label"><?php echo htmlspecialchars($task['tasks_name'] ?? ''); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="d-flex flex-column gap-2">
                                        <?php foreach ($rightTasks as $task): ?>
                                            <label class="d-flex align-items-center modal-checkbox-item">
                                                <input type="checkbox" name="monthly_skip[]" value="<?php echo htmlspecialchars($task['tasks_id'] ?? ''); ?>" class="form-check-input modal-checkbox-input me-2">
                                                <span class="modal-checkbox-label"><?php echo htmlspecialchars($task['tasks_name'] ?? ''); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-12 text-muted">ไม่พบข้อมูลงานรายเดือน</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer (Fixed) -->
            <div class="modal-footer modal-footer-custom">
                <button type="button" class="btn modal-btn-cancel" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn modal-btn-save" onclick="submitAddCustomer()">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script></script></script>
<script>

    function viewCustomerDrive(customer_id) {
        window.location.href = '<?php echo BASE_URL; ?>/customer_drive?id=' + customer_id;
    }

    document.addEventListener("DOMContentLoaded", function() {
    if (typeof flatpickr !== 'undefined') {
            flatpickr("#fiscal_closing_date", {
                dateFormat: "d/m/Y",
                locale: "th",
                allowInput: true,
                static: true
            });
        }
    });

    // เพิ่มข้อมูลลูกค้า

    function modal_add_customer() {
        // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
        const form = document.getElementById('addCustomerForm');
        if(form) {
            form.reset();
            document.getElementById('edit_customer_id').value = '';
            document.getElementById('addCustomerModalLabel').innerText = 'เพิ่มลูกค้าใหม่';
            document.querySelectorAll('input[name="monthly_skip[]"]').forEach(cb => cb.checked = false);
        }
        // 2. สั่งโชว์ Modal ผ่าน Vanilla JS ของ Bootstrap
        const modalElement = document.getElementById('addCustomerModal');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }

    function updateTeamInfo() {
        const select = document.getElementById('user_id_select');
        const selectedOption = select.options[select.selectedIndex];

        const teamId = selectedOption.getAttribute('data-team-id') || '';
        const teamName = selectedOption.getAttribute('data-team-name') || '';

        document.getElementById('team_id_hidden').value = teamId;
        document.getElementById('team_name_display').value = teamName ? teamName : (select.value ? 'ไม่มีทีม' : '');
    }


    let isSubmittingCustomer = false;
    function submitAddCustomer() {
        if (isSubmittingCustomer) return;

        // Get fields
        const customerName = $('#customer_name').val().trim();

        let isValid = true;

        // Validate customer_name
        if (!customerName) {
            $('#customer_name').addClass('is-invalid');
            isValid = false;
        } else {
            $('#customer_name').removeClass('is-invalid');
        }

        if (!isValid) {
            return; // หยุดการทำงานถ้ากรอกไม่ครบ
        }

        isSubmittingCustomer = true;
        const submitBtn = $('#addCustomerModal .modal-footer button:last-child');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('กำลังบันทึก...');

        var formData = $('#addCustomerForm').serialize();
        var customerId = $('#edit_customer_id').val();
        var targetUrl = customerId ? '/cpd_ac/public/customer/edit' : '/cpd_ac/public/customer/add';

        $.ajax({

            url: targetUrl,

            // url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/customer/add',

            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                isSubmittingCustomer = false;
                submitBtn.prop('disabled', false).text(originalBtnText);

                if (response.result === 1) {
                    $('#addCustomerModal').modal('hide');
                    if (typeof Swal !== 'undefined') {
                        sessionStorage.setItem('toast_msg', 'เพิ่มลูกค้าสำเร็จ');
                        sessionStorage.setItem('toast_icon', 'success');
                        location.reload();
                    } else {
                        alert('เพิ่มลูกค้าสำเร็จ');
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
                        Toast.fire({ icon: 'error', title: response.msg || 'ไม่สามารถเพิ่มลูกค้าได้' });
                    } else {
                        alert(response.msg || 'ไม่สามารถเพิ่มลูกค้าได้');
                    }
                }
            },
            error: function(err) {
                isSubmittingCustomer = false;
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

    // แก้ไขข้อมูลลูกค้า
    function editCustomer(customer_id) {
        // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
        const form = document.getElementById('addCustomerForm');
        if(form) {
            form.reset();
            document.getElementById('edit_customer_id').value = customer_id;
            document.getElementById('addCustomerModalLabel').innerText = 'แก้ไขข้อมูลลูกค้า';
            document.querySelectorAll('input[name="monthly_skip[]"]').forEach(cb => cb.checked = false);
        }
        
        // Fetch existing data
        $.ajax({
            url: '/cpd_ac/public/customer/get?id=' + customer_id,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.result === 1) {
                    var data = response.data;
                    $('#customer_name').val(data.customer_name);
                    $('#service_start_date').val(data.service_start_date);
                    $('#service_start_end').val(data.service_start_end);
                    $('#active_status').val(data.active_status);
                    
                    if(data.user_id) {
                        $('#user_id_select').val(data.user_id);
                        updateTeamInfo();
                    } else {
                        $('#user_id_select').val('');
                        $('#team_id_hidden').val('');
                        $('#team_name_display').val('');
                    }

                    $('#closing_status').val(data.closing_status);
                    if(data.fiscal_closing_date) {
                        $('#fiscal_closing_date').val(data.fiscal_closing_date);
                    }
                    
                    $('#accounts_amount').val(data.f_accounts_amount || data.accounts_amount || 0);
                    
                    $('input[name="contact_tel"]').val(data.customer_phone);
                    $('input[name="contact_email"]').val(data.customer_email);
                    $('input[name="contact_line_id"]').val(data.line_id);
                    $('input[name="line_token"]').val(data.line_group_token);
                    $('input[name="doc_url"]').val(data.doc_folder_url);
                    
                    $('input[name="rd_user"]').val(data.rn_user);
                    $('input[name="rd_password"]').val(data.rn_password);
                    $('input[name="dbd_user"]').val(data.dbd_user);
                    $('input[name="dbd_password"]').val(data.dbd_password);
                    $('input[name="sso_user"]').val(data.sso_user);
                    $('input[name="sso_password"]').val(data.sso_password);
                    
                    // check monthly skip
                    if(data.monthly_skip && data.monthly_skip.length > 0) {
                        data.monthly_skip.forEach(function(taskId) {
                            $('input[name="monthly_skip[]"][value="'+taskId+'"]').prop('checked', true);
                        });
                    }
                    
                    if(data.debug_query) {
                        console.log("🔥 DEBUG QUERY:", data.debug_query);
                    }
                    
                    // 2. สั่งโชว์ Modal
                    const modalElement = document.getElementById('addCustomerModal');
                    const myModal = new bootstrap.Modal(modalElement);
                    myModal.show();
                } else {
                    alert(response.msg || 'ไม่สามารถโหลดข้อมูลได้');
                }
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูลลูกค้า');
            }
        });
    }

    function deleteCustomer(customer_id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'ลบข้อมูลลูกค้า?',
                text: 'ข้อมูลลูกค้านี้จะถูกลบออกจากระบบ',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ลบข้อมูล',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    processDeleteCustomer(customer_id);
                }
            });
        } else {
            if (confirm('ต้องการลบข้อมูลลูกค้าหรือไม่?')) {
                processDeleteCustomer(customer_id);
            }
        }
    }

    function processDeleteCustomer(customer_id) {
        $.ajax({
            url: '/cpd_ac/public/customer/delete',
            method: 'POST',
            data: { customer_id: customer_id }, // ส่งผ่าน POST Data เพื่อความปลอดภัยกว่าการต่อ URL ตรงๆ
            dataType: 'json',
            success: function(response) {
                if (response.result === 1) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('สำเร็จ!', response.msg, 'success').then(() => location.reload());
                    } else {
                        location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('ผิดพลาด', response.msg || 'ไม่สามารถลบข้อมูลได้', 'error');
                    } else {
                        alert(response.msg || 'ไม่สามารถลบข้อมูลได้');
                    }
                }
            },
            error: function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการลบข้อมูลลูกค้า', 'error');
                } else {
                    alert('เกิดข้อผิดพลาดในการลบข้อมูลลูกค้า');
                }
            }
        });
    }
</script>
<?php
    // 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>