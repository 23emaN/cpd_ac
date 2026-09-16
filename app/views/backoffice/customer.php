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



<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">

                <div class="main-card-wrapper">

                    <!-- Page Header Section -->
                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title">ลูกค้า</h2>
                            <?php $fy_display = ! empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
                            <p class="page-subtitle">ภาพรวมระบบ - ลูกค้า - ปี <?php echo htmlspecialchars($fy_display); ?></p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-excel-action" onclick="alert('Import Excel')">
                                <i class="ri-file-upload-line"></i>
                                <span>Import Excel</span>
                            </button>
                            <button type="button" class="btn-excel-action" onclick="exportCustomerExcel()">
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
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหาชื่อลูกค้า ผู้ดูแล ทีม">
                        </div>

                        <div class="filter-group" style="display: flex; align-items: center; gap: 10px; flex-wrap: nowrap;">
                            <select class="filter-select" id="filter_status" onchange="triggerFilterDebounced()">
                                <option value="">ทุกสถานะ</option>
                                <option value="1">ใช้บริการอยู่</option>
                                <option value="0">เลิกจ้าง</option>
                            </select>

                            <select class="filter-select" name="user_id_filter" id="user_id_filter" onchange="triggerFilterDebounced()">
                                <option value="" data-team-id="" data-team-name="" selected>ทั้งหมด</option>
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

                    <div id="customerTableContainer">
                        <?php require_once 'table/customer_table.php'; ?>
                    </div>

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
                <form id="addCustomerForm" autocomplete="off">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="fiscal_id" value="<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>">
                    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($data['active_company_id'] ?? ''); ?>">
                    <input type="hidden" name="customer_id" id="edit_customer_id" value="">

                    <!-- Section: ข้อมูลทั่วไป -->
                    <div class="mb-4">
                        <h6 class="modal-section-title">ข้อมูลทั่วไป</h6>

                        <!-- ชื่อบริษัท / กิจการ -->
                        <div class="mb-3">
                            <label class="form-label modal-form-label" for="customer_name">
                                ชื่อบริษัท / กิจการ <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control modal-form-control" name="customer_name" id="customer_name" placeholder="ระบุชื่อบริษัท / กิจการ">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อบริษัท / กิจการ</div>
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
                                <input type="text" class="form-control modal-form-control team-name-disabled" id="team_name_display" placeholder="ไม่มีทีม" readonly disabled>
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

                        <!-- แถวที่ 2: จด VAT / มีพนักงาน / ประกันสังคม / cpd / cpa -->
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

                        <!-- แถวที่ 3: ผู้ทำบัญชี (CPD) / ผู้สอบบัญชี (CPA) -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ผู้ทำบัญชี (CPD)</label>
                                <input type="text" class="form-control modal-form-control" name="cpd_name" id="cpd_name" placeholder="ชื่อผู้ทำบัญชี">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ผู้สอบบัญชี (CPA)</label>
                                <input type="text" class="form-control modal-form-control" name="cpa_name" id="cpa_name" placeholder="ชื่อผู้สอบบัญชี">
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
                                <input type="number" class="form-control modal-form-control" name="contact_tel" placeholder="">
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
                        <!-- <div class="mb-3">
                            <label class="form-label modal-form-label">URL เก็บไฟล์เอกสารลูกค้า</label>
                            <input type="text" class="form-control modal-form-control" name="doc_url" placeholder="เช่น https://drive.google.com/...">
                        </div> -->

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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="modal-section-title mb-0">ระบบราชการ</h6>
                            <button type="button" class="btn btn-sm modal-video-btn" onclick="addAccountRow()">
                                <i class="ri-add-line"></i> เพิ่มข้อมูล
                            </button>
                        </div>

                        <div id="accountsList" class="gov-accounts-list">
                            <!-- Dynamic rows will be added here -->
                        </div>

                        <div id="accountsEmptyState" class="gov-accounts-empty">
                            <i class="ri-shield-keyhole-line"></i>
                            <span>ยังไม่มีข้อมูลระบบราชการ กด "เพิ่มข้อมูล" เพื่อเริ่มเพิ่ม</span>
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
<script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
<script>

    $(document).ready(function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#fiscal_closing_date", {
                dateFormat: "d/m/Y",
                locale: "th",
                allowInput: true,
                static: true
            });
        }

        // Initialize Select2
        if ($.fn.select2) {
            $('#filter_status, #user_id_filter').select2({
                width: '100%'
            });
            $('#addCustomerModal select').select2({
                dropdownParent: $('#addCustomerModal'),
                width: '100%'
            });
        }

        // Clear validation on input
        $('#customer_name').on('input change', function() {
            if ($(this).val().trim()) {
                $(this).removeClass('is-invalid border border-danger');
            }
        });
        updateAccountsEmptyState();
    });

    function togglePasswordVisibility(icon) {
        const input = $(icon).siblings('input')[0];
        if (input) {
            if (input.type === 'password') {
                input.type = 'text';
                $(icon).removeClass('ri-eye-off-line').addClass('ri-eye-line');
            } else {
            input.type = 'password';
            $(icon).removeClass('ri-eye-line').addClass('ri-eye-off-line');
            }
        }
    }

    let filterDebounceTimer = null;

    function triggerFilterDebounced() {
        clearTimeout(filterDebounceTimer);
        filterDebounceTimer = setTimeout(function () {
            loadCustomerTable();
        }, 400);
    }

    function loadCustomerTable(page) {
        var baseUrl = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>';
        var payload = {
            keyword: $('#search_input').val().trim(),
            status: $('#filter_status').val(),
            user_id: $('#user_id_filter').val(),
            page: page || 1
        };

        $('#customerTableContainer').html(`
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-start" style="width: 25%;">ชื่อลูกค้า</th>
                            <th class="text-center" style="width: 14%;">สถานะ</th>
                            <th class="text-center" style="width: 12%;">วันสิ้นรอบ</th>
                            <th class="text-center" style="width: 15%;">ค่าบัญชี</th>
                            <th class="text-center" style="width: 12%;">ผู้ดูแล</th>
                            <th class="text-center" style="width: 10%;">ติดต่อ</th>
                            <th class="text-center" style="width: 12%;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <span class="fw-medium" style="color: #64748b; font-size: 0.9rem;">กำลังโหลด...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `);

        $.ajax({
            url: baseUrl + '/customer/filter',
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function(response) {
                if (response.result === 1) {
                    $('#customerTableContainer').html(response.html);
                } else {
                    console.error('Filter error:', response.msg);
                }
            },
            error: function(xhr, status, error) {
                console.error('FILTER AJAX ERROR', status, error);
            }
        });
    }

    function exportCustomerExcel() {
        var baseUrl = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>';
        var keyword = ($('#search_input').val() || '').trim();
        var status = $('#filter_status').val() || '';
        var userId = $('#user_id_filter').val() || '';

        var params = new URLSearchParams();
        if (keyword) params.append('keyword', keyword);
        if (status !== '') params.append('status', status);
        if (userId) params.append('user_id', userId);

        var queryString = params.toString();
        window.location.href = baseUrl + '/report/customer' + (queryString ? '?' + queryString : '');
    }

    // เพิ่มข้อมูลลูกค้า
    function modal_add_customer() {
        const form = document.getElementById('addCustomerForm');
        if(form) {
            form.reset();
            document.getElementById('edit_customer_id').value = '';
            document.getElementById('addCustomerModalLabel').innerText = 'เพิ่มลูกค้าใหม่';
            document.querySelectorAll('input[name="monthly_skip[]"]').forEach(cb => cb.checked = false);
            $('#customer_name').removeClass('is-invalid');

            // Reset Select2s
            if ($.fn.select2) {
                $('#addCustomerModal select').trigger('change.select2');
            }
            $('#team_id_hidden').val('');
            $('#team_name_display').val('');

            // Reset password inputs and icons
            document.getElementById('accountsList').innerHTML = '';
            addAccountRow('กรมพัฒนาธุรกิจการค้า', '', '', true);
            addAccountRow('กรมสรรพากร', '', '', true);
            updateAccountsEmptyState();
        }
        const modalElement = document.getElementById('addCustomerModal');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }

    function updateTeamInfo() {
        const select = document.getElementById('user_id_select');
        if (!select || select.selectedIndex < 0) {
            $('#team_id_hidden').val('');
            $('#team_name_display').val('');
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) {
            $('#team_id_hidden').val('');
            $('#team_name_display').val('');
            return;
        }

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

        let errorMessages = [];

        // Validate customer_name
        if (!customerName) {
            $('#customer_name').addClass('is-invalid border border-danger');
            errorMessages.push('ชื่อลูกค้า');
            isValid = false;
        } else {
            $('#customer_name').removeClass('is-invalid border border-danger');
        }

        // Validate required accounts
        $('input[name="account_name[]"]').each(function(index) {
            let name = $(this).val();
            if (name === 'กรมพัฒนาธุรกิจการค้า' || name === 'กรมสรรพากร') {
                let user = $('input[name="account_user_name[]"]').eq(index).val().trim();
                let pass = $('input[name="account_password[]"]').eq(index).val().trim();
                
                if (!user) {
                    $('input[name="account_user_name[]"]').eq(index).addClass('border border-danger');
                    errorMessages.push(`Username/ID ของ${name}`);
                    isValid = false;
                }
                
                if (!pass) {
                    $('input[name="account_password[]"]').eq(index).addClass('border border-danger');
                    errorMessages.push(`รหัสผ่าน ของ${name}`);
                    isValid = false;
                }
            }
        });

        if (!isValid) {
            let htmlMsg = '<div style="text-align: left; padding-left: 2rem;">';
            errorMessages.forEach(msg => {
                htmlMsg += `<div class="mb-1 text-danger">- ${msg}</div>`;
            });
            htmlMsg += '</div>';

            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                html: htmlMsg,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'ตกลง'
            });
            return; // หยุดการทำงานถ้ากรอกไม่ครบ
        }

        isSubmittingCustomer = true;
        const submitBtn = $('#addCustomerModal .modal-footer button:last-child');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('กำลังบันทึก...');

        var formData = $('#addCustomerForm').serialize();
        var customerId = $('#edit_customer_id').val();
        var baseUrl = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>';
        var targetUrl = customerId ? baseUrl + '/customer/edit' : baseUrl + '/customer/add';

        $.ajax({
            url: targetUrl,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                isSubmittingCustomer = false;
                submitBtn.prop('disabled', false).text(originalBtnText);

                if (response.result === 1) {
                    $('#addCustomerModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: customerId ? 'แก้ไขข้อมูลลูกค้าสำเร็จ' : 'เพิ่มลูกค้าสำเร็จ',
                        confirmButtonColor: '#3b82f6',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: response.msg || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล',
                        confirmButtonColor: '#3b82f6',
                        confirmButtonText: 'ตกลง'
                    });
                }
            },
            error: function(err) {
                isSubmittingCustomer = false;
                submitBtn.prop('disabled', false).text(originalBtnText);
                console.error("AJAX Error:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'ผิดพลาด',
                    text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                    confirmButtonColor: '#3b82f6',
                    confirmButtonText: 'ตกลง'
                });
            }
        });
    }

    // เปิดคลังไฟล์ของลูกค้ารายนี้
    function viewCustomerDrive(customer_id) {
        window.location.href = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/customer_drive?id=' + customer_id;
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
            $('#customer_name').removeClass('is-invalid');
        }

        // Fetch existing data
        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/customer/get?id=' + customer_id,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.result === 1) {
                    var data = response.data;
                    $('#customer_name').val(data.customer_name).removeClass('is-invalid');
                    $('#service_start_date').val(data.service_start_date).trigger('change.select2');
                    $('#service_start_end').val(data.service_start_end).trigger('change.select2');
                    $('select[name="active_status"]').val(data.active_status).trigger('change.select2');

                    if(data.user_id && !data.user_delete_at) {
                        $('#user_id_select').val(data.user_id).trigger('change.select2');
                        updateTeamInfo();
                    } else {
                        $('#user_id_select').val('').trigger('change.select2');
                        $('#team_id_hidden').val('');
                        $('#team_name_display').val('');
                    }

                    $('#closing_status').val(data.closing_status).trigger('change.select2');
                    if(data.fiscal_closing_date) {
                        $('#fiscal_closing_date').val(data.fiscal_closing_date);
                    }

                    $('#accounts_amount').val(data.f_accounts_amount || data.accounts_amount || 0);

                    $('select[name="is_vat"]').val(data.is_vat || 0).trigger('change.select2');
                    $('select[name="is_employees"]').val(data.is_employees || 0).trigger('change.select2');
                    $('select[name="is_social_security"]').val(data.is_social_security || 0).trigger('change.select2');

                    $('input[name="contact_tel"]').val(data.customer_phone);
                    $('input[name="contact_email"]').val(data.customer_email);
                    $('input[name="contact_line_id"]').val(data.line_id);
                    $('input[name="line_token"]').val(data.line_group_token);
                    $('input[name="doc_url"]').val(data.doc_folder_url);

                    $('#cpd_name').val(data.cpd_name || '');
                    $('#cpa_name').val(data.cpa_name || '');

                    // Clear and load accounts
                    document.getElementById('accountsList').innerHTML = '';
                    let hasDBD = false;
                    let hasRD = false;
                    if (data.accounts && data.accounts.length > 0) {
                        data.accounts.forEach(acc => {
                            if (acc.account_name === 'กรมพัฒนาธุรกิจการค้า') hasDBD = true;
                            if (acc.account_name === 'กรมสรรพากร') hasRD = true;
                            let isDef = (acc.account_name === 'กรมพัฒนาธุรกิจการค้า' || acc.account_name === 'กรมสรรพากร');
                            addAccountRow(acc.account_name, acc.account_user_name, acc.account_password, isDef);
                        });
                    } else {
                        // Compatibility with old data if they don't have accounts but have old fields
                        if (data.rn_user || data.rn_password) {
                            addAccountRow('กรมพัฒนาธุรกิจการค้า', data.rn_user || '', data.rn_password || '', true);
                            hasDBD = true;
                        }
                    }

                    if (!hasDBD) addAccountRow('กรมพัฒนาธุรกิจการค้า', '', '', true);
                    if (!hasRD) addAccountRow('กรมสรรพากร', '', '', true);

                    // check monthly skip
                    if(data.monthly_skip && data.monthly_skip.length > 0) {
                        data.monthly_skip.forEach(function(taskId) {
                            $('input[name="monthly_skip[]"][value="'+taskId+'"]').prop('checked', true);
                        });
                    }

                    // 2. สั่งโชว์ Modal
                    const modalElement = document.getElementById('addCustomerModal');
                    const myModal = new bootstrap.Modal(modalElement);
                    myModal.show();
                } else {
                    Swal.fire('ผิดพลาด', response.msg || 'ไม่สามารถโหลดข้อมูลได้', 'error');
                }
            },
            error: function() {
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการโหลดข้อมูลลูกค้า', 'error');
            }
        });
    }

    function deleteCustomer(customer_id) {
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
    }

    function processDeleteCustomer(customer_id) {
        const fiscalId = $('input[name="fiscal_id"]').val() || '';
        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/customer/delete',
            method: 'POST',
            data: { 
                customer_id: customer_id,
                fiscal_id: fiscalId
            },
            dataType: 'json',
            success: function(response) {
                if (response.result === 1) {
                    Swal.fire('สำเร็จ!', response.msg, 'success').then(() => location.reload());
                } else {
                    Swal.fire('ผิดพลาด', response.msg || 'ไม่สามารถลบข้อมูลได้', 'error');
                }
            },
            error: function() {
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการลบข้อมูลลูกค้า', 'error');
            }
        });
    }

    function updateAccountsEmptyState() {
        const list = document.getElementById('accountsList');
        const empty = document.getElementById('accountsEmptyState');
        if (list && empty) {
            empty.classList.toggle('show', list.children.length === 0);
        }
    }

    function addAccountRow(name = '', user = '', pass = '', isDefault = false) {
        const list = document.getElementById('accountsList');
        const card = document.createElement('div');
        card.className = 'gov-account-card';
        
        const nameAttr = isDefault ? 'readonly style="background-color: #f8f9fa;"' : '';
        const requiredAsterisk = isDefault ? '<span class="text-danger position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); z-index: 5; pointer-events: none;">*</span>' : '';
        const requiredAsteriskPwd = isDefault ? '<span class="text-danger position-absolute" style="right: 35px; top: 50%; transform: translateY(-50%); z-index: 5; pointer-events: none;">*</span>' : '';
        const removeBtn = isDefault ? '' : `
            <button type="button" class="gov-account-remove" onclick="this.closest('.gov-account-card').remove(); updateAccountsEmptyState();" title="ลบข้อมูล">
                <i class="ri-delete-bin-line"></i>
            </button>
        `;

        card.innerHTML = `
            <div class="gov-account-fields">
                <input type="text" class="form-control modal-form-control" name="account_name[]" value="${name}"
                   placeholder="เช่น กรมสรรพากร" ${nameAttr}
                   autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                   data-lpignore="true" data-1p-ignore data-form-type="other">
                <div class="position-relative">
                    <input type="text" class="form-control modal-form-control w-100" name="account_user_name[]" value="${user}"
                       placeholder="Username/ID"
                       autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                       data-lpignore="true" data-1p-ignore data-form-type="other" oninput="this.classList.remove('border', 'border-danger')">
                    ${requiredAsterisk}
                </div>
                <div class="modal-input-icon-wrap position-relative">
                    <input type="password" class="form-control modal-form-control modal-input-with-icon" name="account_password[]" value="${pass}"
                       placeholder="รหัสผ่าน"
                       autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                       data-lpignore="true" data-1p-ignore data-form-type="other" readonly onfocus="this.removeAttribute('readonly')" oninput="this.classList.remove('border', 'border-danger')">
                    <i class="ri-eye-off-line modal-input-icon modal-input-icon-clickable" onclick="togglePasswordVisibility(this)"></i>
                    ${requiredAsteriskPwd}
                </div>
            </div>
            ${removeBtn}
        `;
        list.appendChild(card);
        updateAccountsEmptyState();
    }
</script>
<?php
    // 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>