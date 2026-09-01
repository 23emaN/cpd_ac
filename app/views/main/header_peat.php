<!DOCTYPE html>
<html lang="th">


<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?php echo htmlspecialchars($data['title'] ?? 'CPD ACC - ระบบบริหารสำนักงานบัญชี') ?></title>

    <link rel="icon" type="image/png" href="/cpd_ac/public/assets/images/am-group-logo.png">
    <link rel="apple-touch-icon" href="/cpd_ac/public/assets/images/am-group-logo.png">

    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/font.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/sidebar-menu.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/simplebar.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/apexcharts.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/prism.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/rangeslider.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/google-icon.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/remixicon.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/swiper-bundle.min.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/fullcalendar.main.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/jsvectormap.min.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/lightpick.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/select2.min.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/style.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/toastr.min.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/sweetalert2.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="stylesheet"
        href="/cpd_ac/public/template/assets/css/custom.css?ver=<?php echo @filemtime(dirname(__DIR__, 2) . '/public/template/assets/css/custom.css') ?: time(); ?>">
    <link rel="stylesheet" href="/cpd_ac/public/template/assets/css/web.css">
    <link rel="stylesheet"
        href="/cpd_ac/public/template/assets/css/ui.css?ver=<?php echo @filemtime(dirname(__DIR__, 2) . '/public/template/assets/css/ui.css') ?: time(); ?>">

    <style>
        /* --- CPD ACC Modern Header & Company Tabs --- */
        .acc-topbar {
            background-color: #ffffff;
            border-bottom: 1px solid #edf2f7;
            padding: 10px 28px;
            min-height: 68px;
            position: sticky;
            top: 0;
            z-index: 1000;
            display: flex !important;
            flex-grow: 1 !important;
            justify-content: space-between;
            align-items: center;
            font-family: 'Kanit', sans-serif;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
        }

        .acc-brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            flex-shrink: 0;
            padding-right: 18px;
            border-right: 1px solid #e2e8f0;
        }

        .acc-brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .acc-brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .acc-brand-info .acc-brand-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.2;
            margin: 0;
            letter-spacing: -0.2px;
        }

        .acc-brand-info .acc-brand-subtitle {
            font-size: 0.72rem;
            color: #64748b;
            margin: 0;
            line-height: 1.2;
            font-weight: 500;
        }

        /* Company Tab Bar Container */
        .acc-company-container {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            max-width: calc(100vw - 480px);
            padding: 4px 6px 4px 16px;
            scrollbar-width: thin;
        }

        .acc-company-container::-webkit-scrollbar {
            height: 3px;
        }

        .acc-company-container::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        /* Sleek Modern Company Pill Tab */
        .acc-company-btn {
            height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 5px 14px 5px 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
            flex-shrink: 0;
            white-space: nowrap;
        }

        .acc-company-btn:hover {
            border-color: #3b82f6;
            background-color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.08);
        }

        .acc-company-btn.active {
            border-color: #2563eb;
            background-color: #ffffff;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.12);
        }

        .acc-company-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .acc-company-btn:hover .acc-company-icon {
            background-color: #eff6ff;
            border-color: #bfdbfe;
            color: #2563eb;
        }

        .acc-company-btn.active .acc-company-icon {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        .acc-company-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #334155;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            transition: color 0.2s ease;
        }

        .acc-company-btn:hover .acc-company-name,
        .acc-company-btn.active .acc-company-name {
            color: #0f172a;
        }

        /* Add Workspace Button */
        .acc-add-workspace-btn {
            height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 5px 14px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            color: #475569;
            flex-shrink: 0;
        }

        .acc-add-workspace-btn:hover {
            border-color: #2563eb;
            background-color: #eff6ff;
            color: #2563eb;
            transform: translateY(-1px);
        }

        .acc-add-workspace-icon {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            background-color: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .acc-add-workspace-btn:hover .acc-add-workspace-icon {
            background-color: #2563eb;
            color: #ffffff;
        }

        .acc-add-workspace-text {
            font-size: 0.80rem;
            font-weight: 700;
            letter-spacing: 0.2px;
        }

        /* Right Actions & User Profile */
        .acc-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .acc-user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            padding: 4px 10px;
            border-radius: 10px;
            background-color: #f8fafc;
            border: 1px solid #f1f5f9;
            transition: all 0.2s ease;
        }

        .acc-user-profile:hover {
            background-color: #f1f5f9;
            border-color: #e2e8f0;
        }

        .acc-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
            flex-shrink: 0;
        }

        .acc-user-info {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
            text-align: left;
        }

        .acc-user-name {
            font-size: 0.82rem;
            font-weight: 700;
            color: #1e293b;
            white-space: nowrap;
        }

        .acc-user-role {
            font-size: 0.68rem;
            color: #64748b;
            font-weight: 500;
            white-space: nowrap;
        }

        .acc-logout-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background-color: #fff1f2;
            border: 1px solid #ffe4e6;
            color: #e11d48;
            font-size: 18px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .acc-logout-btn:hover {
            background-color: #e11d48;
            color: #ffffff;
            border-color: #e11d48;
            box-shadow: 0 3px 8px rgba(225, 29, 72, 0.25);
        }
    </style>
</head>

<body class="boxed-size">

    <div class="preloader" id="preloader">
        <script>
            if (sessionStorage.getItem('cpdth_show_preloader') === '1') {
                sessionStorage.removeItem('cpdth_show_preloader');
            } else {
                var _p = document.getElementById('preloader');
                if (_p) { _p.style.display = 'none'; }
            }
        </script>
        <div class="preloader">
            <div class="waviy position-relative">
                <span class="d-inline-block">C</span>
                <span class="d-inline-block">P</span>
                <span class="d-inline-block">D</span>
                <span class="d-inline-block">T</span>
                <span class="d-inline-block">H</span>
            </div>
        </div>
    </div>

    <header class="acc-topbar">
        <div class="d-flex align-items-center flex-grow-1">
            <!-- Brand Logo & Title -->
            <a href="/cpd_ac/public/main" class="acc-brand-wrap">
                <div class="acc-brand-logo">
                    <img src="/cpd_ac/public/assets/images/G_AM_logo-01.jpg" alt="Cpd Acc Logo">
                </div>
                <div class="acc-brand-info">
                    <h1 class="acc-brand-title">CPD ACC</h1>
                    <p class="acc-brand-subtitle">ระบบบริหารสำนักงานบัญชี</p>
                </div>
            </a>

            <!-- Company List Pills Container with Dropdowns -->
            <div class="acc-company-container">
                <?php if (isset($data['companies']) && !empty($data['companies'])): ?>
                    <?php foreach ($data['companies'] as $index => $company): 
                        $companyId   = $company['company_id'] ?? $company['id'] ?? '';
                        $companyName = htmlspecialchars($company['company_name'] ?? 'ไม่มีชื่อบริษัท');
                        $isActive    = ($index === 0) ? 'active' : '';
                    ?>
                        <div class="dropdown">
                            <button type="button" 
                                class="acc-company-btn <?= $isActive ?>" 
                                id="dropdownCompany_<?= $companyId ?>"
                                data-bs-toggle="dropdown" 
                                data-bs-popper-config='{"strategy":"fixed"}'
                                aria-expanded="false"
                                data-company-id="<?= $companyId ?>"
                                data-company-name="<?= $companyName ?>"
                                title="<?= $companyName ?>"
                                onclick="selectCompany(this, '<?= $companyId ?>')">
                                <div class="acc-company-icon">
                                    <i class="ri-building-4-line"></i>
                                </div>
                                <span class="acc-company-name"><?= $companyName ?></span>
                                <i class="ri-arrow-down-s-line text-muted ms-1" style="font-size: 15px;"></i>
                            </button>

                            <!-- Dropdown Menu แต่ละบริษัท -->
                            <ul class="dropdown-menu shadow-lg border-0 mt-2 py-2" aria-labelledby="dropdownCompany_<?= $companyId ?>"
                                style="border-radius: 12px; min-width: 220px; border: 1px solid #edf2f7 !important; z-index: 9999;">
                                <li class="px-3 py-1 mb-1 text-muted" style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                                    <?= $companyName ?>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2" href="javascript:void(0);"
                                        onclick="selectCompanyById('<?= $companyId ?>')">
                                        <i class="ri-checkbox-circle-line me-2 text-primary"></i> เลือกบริษัทนี้
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center py-2 text-muted" href="javascript:void(0);">
                                        <i class="ri-calendar-line me-2 text-muted"></i> จัดการปีทำงาน
                                    </a>
                                </li>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- ปุ่มเพิ่มบริษัท -->
                <button type="button" class="acc-add-workspace-btn" onclick="Getmodal_add()" title="เพิ่มบริษัท">
                    <div class="acc-add-workspace-icon">
                        <i class="ri-add-line"></i>
                    </div>
                    <span class="acc-add-workspace-text">เพิ่มบริษัท</span>
                </button>
            </div>
        </div>

        <div class="acc-actions">
            <div class="acc-user-profile" title="ข้อมูลผู้ใช้งาน">
                <div class="acc-user-avatar">
                    <i class="ri-user-3-fill"></i>
                </div>
                <div class="acc-user-info d-none d-sm-flex">
                    <span class="acc-user-name">
                        <?php echo htmlspecialchars(trim(($data['firstname'] ?? $_SESSION['user_firstname'] ?? '') . ' ' . ($data['lastname'] ?? $_SESSION['user_lastname'] ?? 'ผู้ใช้งาน'))) ?>
                    </span>
                    <span class="acc-user-role">
                        <?php echo (!empty($data['is_super_admin'] ?? $_SESSION['is_super_admin'] ?? null) && ($data['is_super_admin'] ?? $_SESSION['is_super_admin']) === '1') ? 'ผู้ดูแลระบบ' : 'ผู้ใช้งานระบบ' ?>
                    </span>
                </div>
            </div>

            <!-- Logout Button -->
            <a href="/cpd_ac/public/logout" class="acc-logout-btn" title="ออกจากระบบ">
                <i class="ri-logout-box-r-line"></i>
            </a>
        </div>
    </header>
    <!-- Modal เพิ่มบริษัท -->
    <div class="modal fade" id="addCompanyModal" tabindex="-1" aria-labelledby="addCompanyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCompanyModalLabel">เพิ่มบริษัทใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCompanyForm">
                        <div class="mb-3">
                            <label class="form-label">ชื่อบริษัท</label>
                            <input type="text" class="form-control" name="company_name" placeholder="กรอกชื่อบริษัท">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" onclick="addCompany()">บันทึก</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentSelectedCompanyId = '<?= isset($data['companies'][0]) ? ($data['companies'][0]['company_id'] ?? $data['companies'][0]['id'] ?? '') : '' ?>';

        function selectCompany(element, companyId) {
            if (!element) return;

            // 1. ถอด active ออกจากทุกปุ่ม
            document.querySelectorAll('.acc-company-btn').forEach(function (b) {
                b.classList.remove('active');
            });

            // 2. กำหนด active ค้างไว้ที่ปุ่มที่เลือก
            element.classList.add('active');
            currentSelectedCompanyId = companyId;

            // 3. ดึงชื่อบริษัท
            const name = element.getAttribute('data-company-name') || (element.querySelector('.acc-company-name') ? element.querySelector('.acc-company-name').textContent.trim() : '');
            const workspaceNameEl = document.getElementById('currentWorkspaceName');
            if (name && workspaceNameEl) {
                workspaceNameEl.textContent = name;
            }

            // 4. เลื่อน scroll มาที่ปุ่มที่เลือกอย่างนุ่มนวล
            element.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });

            // 5. ส่ง Event หรือเรียก Callback ไปยังหน้า index หรือหน้าที่กำลังใช้งานอยู่
            if (typeof window.onCompanyChange === 'function') {
                window.onCompanyChange(companyId, name);
            }
            document.dispatchEvent(new CustomEvent('companyChanged', { 
                detail: { companyId: companyId, companyName: name } 
            }));
        }

        function selectCompanyById(companyId) {
            const btn = document.querySelector('.acc-company-btn[data-company-id="' + companyId + '"]');
            if (btn) {
                selectCompany(btn, companyId);
            }
        }

        function Getmodal_add() {
            // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
            const form = document.getElementById('addCompanyForm');
            if (form) {
                form.reset();
            }
            // 2. สั่งโชว์ Modal ผ่าน Vanilla JS ของ Bootstrap
            const modalElement = document.getElementById('addCompanyModal');
            const myModal = new bootstrap.Modal(modalElement);
            myModal.show();
        }

        function addCompany() {
            var formData = $('#addCompanyForm').serialize(); // ดึงข้อมูลจากฟอร์ม
            $.ajax({
                type: "POST",
                url: "/cpd_ac/public/company/add",
                data: formData,
                dataType: "json",
                success: function (response) {
                    if (response.result === 1) {
                        // ซ่อน Modal เมื่อบันทึกสำเร็จ
                        const modalElement = document.getElementById('addCompanyModal');
                        if (modalElement) {
                            const modalInstance = bootstrap.Modal.getInstance(modalElement);
                            if (modalInstance) modalInstance.hide();
                        }

                        // แจ้งเตือน Toast สำเร็จ
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.msg || 'บันทึกสำเร็จ',
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            alert(response.msg);
                            location.reload();
                        }
                    } else {
                        // ถ้าบันทึกไม่สำเร็จ แจ้งเตือน Toast Error
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'error',
                                title: response.msg || 'ผิดพลาด',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true
                            });
                        } else {
                            alert(response.msg);
                        }
                    }
                },
                error: function (err) {
                    console.error("AJAX Error:", err);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    } else {
                        alert("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
                    }
                }
            });
        }
    </script>