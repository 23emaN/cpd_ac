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
                            <?php $fy_display = ! empty($data['active_fiscal_year']) ? $data['active_fiscal_year'] : 'ไม่ได้เลือกปี'; ?>
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
                        $employees         = $data['employees'] ?? [];
                        $totalEmployees    = count($employees);
                        $activeEmployees   = 0;
                        $inactiveEmployees = 0;
                        $uniqueTeams       = [];
                        foreach ($employees as $emp) {
                            if ($emp['user_status'] == '1') {
                                $activeEmployees++;
                            } else {
                                $inactiveEmployees++;
                            }
                            if (! empty($emp['team_name'])) {
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
                            <input type="text" id="employeeSearchInput" class="search-input" placeholder="ค้นหาชื่อ ตำแหน่ง ทีม">
                        </div>

                        <div class="filter-group">
                            <select id="employeePerPage" class="filter-select">
                                <option value="25">25 รายการ</option>
                                <option value="50">50 รายการ</option>
                                <option value="75">75 รายการ</option>
                                <option value="100">100 รายการ</option>
                            </select>
                            
                            <select id="employeeStatusFilter" class="filter-select">
                                <option value="">ทุกสถานะ</option>
                                <option value="1" selected>ยังทำงานอยู่</option>
                                <option value="0">เลิกจ้าง</option>
                            </select>

                            <select id="employeeTeamFilter" class="filter-select">
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
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" style="max-width: 640px;">
        <div class="modal-content modal-content-custom">

            <!-- Header (Fixed) -->
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title modal-title-custom" id="addEmployeeModalLabel">เพิ่มพนักงานใหม่</h5>
                <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body (Scrollable) -->
            <div class="modal-body modal-body-custom">
                <form id="addEmployeeForm">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="fiscal_id" value="<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>">
                    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($data['active_company_id'] ?? ''); ?>">

                    <!-- Section: ข้อมูลทั่วไป -->
                    <div class="mb-4">
                        <!-- ชื่อผู้ใช้ / รหัสผ่าน -->
                        <div class="mb-3">
                            <label class="modal-form-label" for="user_name">
                               ชื่อผู้ใช้ <span style="color: #ef4444;">*</span>
                            </label>
                            <div class="position-relative dropdown-autocomplete">
                                <input type="text" class="form-control modal-form-control autocomplete-input" name="user_name" id="user_name" placeholder="ระบุชื่อผู้ใช้ (ค้นหาจากพนักงานเดิมได้)" autocomplete="off">
                                <ul class="dropdown-menu autocomplete-list user-autocomplete-list w-100 shadow-sm" style="max-height: 200px; overflow-y: auto; padding: 0; margin-top: 4px; border: 1px solid #e2e8f0; border-radius: 8px; position: absolute; z-index: 1050; display: none;">
                                </ul>
                            </div>
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อผู้ใช้</div>
                            <small class="text-muted" id="user_name_hint" style="display:none; font-size: 0.8rem; margin-top:4px;">* ผู้ใช้เดิมในระบบ จะถูกเพิ่มเข้าบริษัทนี้โดยไม่ต้องกำหนดรหัสผ่านใหม่</small>
                        </div>
                        <div class="mb-3">
                            <label class="modal-form-label" for="user_password">
                               รหัสผ่าน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control modal-form-control" name="user_password" id="user_password" placeholder="ระบุรหัสผ่าน">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุรหัสผ่าน</div>
                        </div>

                        <div class="mb-3">
                            <label class="modal-form-label" for="user_firstname">
                               ชื่อพนักงาน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control modal-form-control" name="user_firstname" id="user_firstname" placeholder="ระบุชื่อพนักงาน">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อพนักงาน</div>
                        </div>
                        <div class="mb-3">
                            <label class="modal-form-label" for="user_lastname">
                               นามสกุล <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control modal-form-control" name="user_lastname" id="user_lastname" placeholder="ระบุนามสกุล">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุนามสกุล</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="modal-form-label" for="user_position">
                                   ตำแหน่ง
                                </label>
                                <input type="text" class="form-control modal-form-control" name="user_position" id="user_position" placeholder="เช่น Senior Accountant">
                            </div>
                            <div class="col-md-6">
                                <label class="modal-form-label" for="team_name">
                                   ทีม
                                </label>
                                <div class="position-relative dropdown-autocomplete">
                                    <input type="text" class="form-control modal-form-control autocomplete-input" name="team_name" id="team_name" placeholder="เช่น ทีมบัญชี A" autocomplete="off">
                                    <ul class="dropdown-menu autocomplete-list w-100 shadow-sm" style="max-height: 200px; overflow-y: auto; padding: 0; margin-top: 4px; border: 1px solid #e2e8f0; border-radius: 8px; position: absolute; z-index: 1050;">
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer (Fixed) -->
            <div class="modal-footer modal-footer-custom">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">ยกเลิก</button>
                <button type="button" class="btn" onclick="submit_addemployee()" style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 28px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal แก้ไขพนักงาน -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" style="max-width: 640px;">
        <div class="modal-content modal-content-custom">

            <!-- Header (Fixed) -->
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title modal-title-custom" id="editEmployeeModalLabel">แก้ไขข้อมูลพนักงาน</h5>
                <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body (Scrollable) -->
            <div class="modal-body modal-body-custom">
                <form id="editEmployeeForm">
                    <input type="hidden" name="user_id" id="edit_user_id">

                    <div class="mb-4">
                        <div class="mb-3">
                            <label class="modal-form-label" for="edit_user_name">
                               ชื่อผู้ใช้ <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control modal-form-control" name="user_name" id="edit_user_name" placeholder="ระบุชื่อผู้ใช้" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="modal-form-label" for="edit_user_firstname">
                               ชื่อพนักงาน <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control modal-form-control" name="user_firstname" id="edit_user_firstname" placeholder="ระบุชื่อพนักงาน">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อพนักงาน</div>
                        </div>
                        <div class="mb-3">
                            <label class="modal-form-label" for="edit_user_lastname">
                               นามสกุล <span style="color: #ef4444;">*</span>
                            </label>
                            <input type="text" class="form-control modal-form-control" name="user_lastname" id="edit_user_lastname" placeholder="ระบุนามสกุล">
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุนามสกุล</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="modal-form-label" for="edit_user_position">
                                   ตำแหน่ง
                                </label>
                                <input type="text" class="form-control modal-form-control" name="user_position" id="edit_user_position" placeholder="เช่น Senior Accountant">
                            </div>
                            <div class="col-md-6">
                                <label class="modal-form-label" for="edit_team_name">
                                   ทีม
                                </label>
                                <div class="position-relative dropdown-autocomplete">
                                    <input type="text" class="form-control modal-form-control autocomplete-input" name="team_name" id="edit_team_name" placeholder="ระบุชื่อทีม" autocomplete="off">
                                    <ul class="dropdown-menu autocomplete-list w-100 shadow-sm" style="max-height: 200px; overflow-y: auto; padding: 0; margin-top: 4px; border: 1px solid #e2e8f0; border-radius: 8px; position: absolute; z-index: 1050;">
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="modal-form-label" for="edit_user_status">
                               สถานะ <span style="color: #ef4444;">*</span>
                            </label>
                            <select class="form-select modal-form-control" name="user_status" id="edit_user_status">
                                <option value="1">ยังทำงานอยู่</option>
                                <option value="0">เลิกจ้าง</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer (Fixed) -->
            <div class="modal-footer modal-footer-custom">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 12px; padding: 10px 24px; border: none; font-size: 0.92rem; transition: all 0.2s ease;">ยกเลิก</button>
                <button type="button" class="btn" onclick="submit_editemployee()" style="background-color: #007aff; color: #ffffff; font-weight: 700; border-radius: 12px; padding: 10px 28px; border: none; font-size: 0.92rem; box-shadow: 0 4px 14px rgba(0,122,255,0.25); transition: all 0.2s ease;">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>


<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const employeesData = <?php echo json_encode($data['employees'] ?? []); ?>;
    const teamsData = <?php echo json_encode($data['teams'] ?? []); ?>;
    const allUsersData = <?php echo json_encode($data['all_users'] ?? []); ?>;
    let isSubmittingTask = false;

    $(document).ready(function() {
        $('#edit_user_status').select2({
        dropdownParent: $('#editEmployeeModal'),
        width: '100%',
        minimumResultsForSearch: Infinity
        });
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

        // Click item (Team)
        $(document).on('click', '.autocomplete-list li:not(.user-autocomplete-item)', function() {
            let list = $(this).closest('.autocomplete-list');
            let input = list.siblings('.autocomplete-input');
            input.val($(this).text());
            list.hide();
        });

        // Setup User Autocomplete (For Add Employee Modal)
        function renderUserAutocomplete(inputElem, listElem, query) {
            listElem.empty();
            let matches = allUsersData.filter(u => u.user_name.toLowerCase().includes(query.toLowerCase()));
            matches = matches.slice(0, 5);

            if (matches.length > 0) {
                matches.forEach(match => {
                    listElem.append(`
                        <li class="user-autocomplete-item"
                            data-fname="${match.user_firstname}"
                            data-lname="${match.user_lastname}"
                            data-pos="${match.position || ''}"
                            data-team="${match.team_name || ''}">
                            <div class="fw-semibold text-dark">${match.user_name}</div>
                            <div class="text-muted" style="font-size:0.8rem;">${match.user_firstname} ${match.user_lastname}</div>
                        </li>
                    `);
                });
                listElem.show();
            } else {
                listElem.hide();
            }
        }

        $('#user_name').on('keyup focus', function() {
            const listElem = $(this).siblings('.user-autocomplete-list');
            if ($(this).val().trim() !== '') {
                renderUserAutocomplete($(this), listElem, $(this).val().trim());
            } else {
                listElem.hide();
                resetAddUserFormState();
            }
        });

        // Handle selection of User Autocomplete
        $(document).on('click', '.user-autocomplete-item', function() {
            const userName = $(this).find('.text-dark').text();
            const fname = $(this).data('fname');
            const lname = $(this).data('lname');
            const pos = $(this).data('pos');
            const team = $(this).data('team');

            const inputElem = $(this).closest('.dropdown-autocomplete').find('.autocomplete-input');
            inputElem.val(userName);
            $(this).closest('.autocomplete-list').hide();

            // Autofill the form
            $('#user_firstname').val(fname).prop('readonly', true).css('background-color', '#e2e8f0');
            $('#user_lastname').val(lname).prop('readonly', true).css('background-color', '#e2e8f0');
            $('#user_position').val(pos).prop('readonly', true).css('background-color', '#e2e8f0');
            $('#team_name').val(team).prop('readonly', true).css('background-color', '#e2e8f0');

            // Hide password requirement
            $('#user_password').val('').prop('disabled', true).closest('.mb-3').hide();
            $('#user_name_hint').show();
        });

        function resetAddUserFormState() {
            $('#user_firstname').prop('readonly', false).css('background-color', '');
            $('#user_lastname').prop('readonly', false).css('background-color', '');
            $('#user_position').prop('readonly', false).css('background-color', '');
            $('#team_name').prop('readonly', false).css('background-color', '');
            $('#user_password').prop('disabled', false).closest('.mb-3').show();
            $('#user_name_hint').hide();
        }

        // When Add modal hides, reset everything
        $('#addEmployeeModal').on('hidden.bs.modal', function () {
            resetAddUserFormState();
        });

        // Hide when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.dropdown-autocomplete').length) {
                $('.autocomplete-list').hide();
            }
        });

        let filterTimeout;
        let currentPage = 1;

        // Global function for pagination click
        window.GetData = function(page) {
            currentPage = page;
            filterEmployeeTable();
        };

        $('#employeePerPage').on('change', function() {
            currentPage = 1; // Reset to first page when changing per page
            filterEmployeeTable();
        });

        function filterEmployeeTable() {
            const tbody = $('.table-wrap table tbody');
            const perPage = parseInt($('#employeePerPage').val()) || 25;
            
            // Show loading row and hide others
            tbody.find('tr').hide();
            tbody.find('.loading-row, .no-result-row').remove();
            
            tbody.append(`
                <tr class="loading-row">
                    <td colspan="6" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 2rem; height: 2rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </td>
                </tr>
            `);

            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                const searchText   = $('#employeeSearchInput').val().trim().toLowerCase();
                const statusFilter = $('#employeeStatusFilter').val();
                const teamFilter   = $('#employeeTeamFilter').val();

                tbody.find('.loading-row').remove();

                let matchedRows = [];

                $('.table-wrap table tbody tr[data-name]').each(function() {
                    const row = $(this);
                    const name     = row.data('name') || '';
                    const position = row.data('position') || '';
                    const team     = (row.data('team') || '').toString();
                    const statusData = row.data('status');
                    const status   = (statusData !== null && statusData !== undefined && statusData !== '') ? statusData.toString() : '';

                    const matchSearch = searchText === ''
                        || name.includes(searchText)
                        || position.includes(searchText)
                        || team.toLowerCase().includes(searchText);

                    const matchStatus = statusFilter === '' || status === statusFilter;
                    const matchTeam   = teamFilter === '' || team === teamFilter;

                    if (matchSearch && matchStatus && matchTeam) {
                        matchedRows.push(row);
                    }
                });

                const totalRows = matchedRows.length;
                const totalPages = Math.ceil(totalRows / perPage) || 1;
                
                if (currentPage > totalPages) {
                    currentPage = totalPages;
                }

                const startIndex = (currentPage - 1) * perPage;
                const endIndex = startIndex + perPage;

                // Show only the current page's rows
                matchedRows.forEach((row, index) => {
                    if (index >= startIndex && index < endIndex) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });

                renderClientPagination(totalRows, totalPages, currentPage, startIndex, Math.min(endIndex, totalRows));
                checkEmptyResult(totalRows);
            }, 300);
        }

        function renderClientPagination(totalRows, totalPages, currentPage, startIndex, endIndex) {
            const paginationContainer = $('.d-flex.justify-content-between.align-items-center.px-1.py-3');
            if (paginationContainer.length === 0) return;

            if (totalRows === 0) {
                paginationContainer.hide();
                return;
            } else {
                paginationContainer.show();
            }

            // Update text
            const textSpan = paginationContainer.find('.text-secondary');
            if (textSpan.length) {
                textSpan.text(`แสดง ${totalRows > 0 ? startIndex + 1 : 0}-${endIndex} จาก ${totalRows} รายการ`);
            }

            // Update pagination buttons
            const ul = paginationContainer.find('.pagination');
            if (ul.length === 0) return;

            let html = '';
            
            // First & Prev
            html += `<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="GetData(1)">หน้าแรก</a>
                     </li>`;
            html += `<li class="page-item ${currentPage <= 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="GetData(${currentPage - 1})">ก่อนหน้า</a>
                     </li>`;

            let wStart = Math.max(1, currentPage - 2);
            let wEnd = Math.min(totalPages, currentPage + 2);

            if (wStart > 1) {
                html += `<li class="page-item"><a class="page-link" href="javascript:void(0);" onclick="GetData(1)">1</a></li>`;
                if (wStart > 2) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
            }

            for (let i = wStart; i <= wEnd; i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" onclick="GetData(${i})">${i}</a>
                         </li>`;
            }

            if (wEnd < totalPages) {
                if (wEnd < totalPages - 1) html += `<li class="page-item disabled"><span class="page-link">…</span></li>`;
                html += `<li class="page-item"><a class="page-link" href="javascript:void(0);" onclick="GetData(${totalPages})">${totalPages}</a></li>`;
            }

            // Next & Last
            html += `<li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="GetData(${currentPage + 1})">ถัดไป</a>
                     </li>`;
            html += `<li class="page-item ${currentPage >= totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" onclick="GetData(${totalPages})">หน้าสุดท้าย</a>
                     </li>`;

            ul.html(html);
        }
    
    // แสดงข้อความ "ไม่พบข้อมูล" ถ้ากรองแล้วไม่เจอเลย
    function checkEmptyResult(visibleRows) {
        const tbody = $('.table-wrap table tbody');
        tbody.find('.no-result-row').remove();

        if (visibleRows === 0) {
            tbody.append(`
                <tr class="no-result-row">
                    <td colspan="6" class="text-center py-5 text-muted fw-medium">
                        ไม่พบข้อมูล
                    </td>
                </tr>
            `);
        }
    }

    // Bind events
    $('#employeeSearchInput').on('keyup', filterEmployeeTable);
    $('#employeeStatusFilter').on('change', filterEmployeeTable);
    $('#employeeTeamFilter').on('change', filterEmployeeTable);

    // Apply default filters on page load
    filterEmployeeTable();
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

        // Validate user_password (only if not disabled)
        if (!$('#user_password').prop('disabled')) {
            if (!userPassword) {
                $('#user_password').addClass('is-invalid');
                isValid = false;
            } else {
                $('#user_password').removeClass('is-invalid');
            }
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
                        Swal.fire({
                            icon: 'error',
                            title: response.msg,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#0066fe',
                            showConfirmButton: true
                        });
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
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#0066fe',
                        showConfirmButton: true
                    });
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
            $('#edit_user_status').val(emp.user_status);

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
        var userStatus = $('#edit_user_status').val();

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
                team_name: userTeamName,
                user_status: userStatus
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
                        Swal.fire({
                            icon: 'error',
                            title: response.msg,
                            confirmButtonText: 'ตกลง',
                            confirmButtonColor: '#0066fe',
                            showConfirmButton: true
                        });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function(err) {
                submitBtn.prop('disabled', false).text(originalBtnText);
                console.error(err);
                if(typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                        confirmButtonText: 'ตกลง',
                        confirmButtonColor: '#0066fe',
                        showConfirmButton: true
                    });
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
                reverseButtons: true,
                allowEnterKey: false
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
