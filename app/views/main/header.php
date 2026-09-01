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
            /* --- CPD ACC Balanced Top Header Styles --- */
            .acc-topbar {
                background-color: #ffffff;
                border-bottom: 1px solid #edf0f5;
                padding: 12px 36px;
                min-height: 72px;
                position: sticky;
                top: 0;
                z-index: 1000;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
                display: flex !important;
                flex-grow: 1 !important;
                justify-content: space-between;
                align-items: center;
                font-family: 'Kanit', sans-serif;
            }

            .acc-brand-wrap {
                display: flex;
                align-items: center;
                gap: 12px;
                text-decoration: none;
            }

            .acc-brand-logo {
                width: 44px;
                height: 44px;
                border-radius: 11px;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                flex-shrink: 0;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            }

            .acc-brand-logo img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }

            .acc-brand-info .acc-brand-title {
                font-size: 1.15rem;
                font-weight: 700;
                color: #1e293b;
                line-height: 1.2;
                margin: 0;
            }

            .acc-brand-info .acc-brand-subtitle {
                font-size: 0.75rem;
                color: #64748b;
                margin: 0;
                line-height: 1.2;
            }

            /* Add Workspace Button Card */
            .acc-add-workspace-btn {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                background-color: #ffffff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 8px 18px;
                cursor: pointer;
                margin-left: 32px;
                text-decoration: none;
                transition: all 0.2s ease;
                box-shadow: 0 1px 3px rgba(16, 24, 40, 0.03);
            }

            .acc-add-workspace-btn:hover {
                border-color: #0066fe;
                background-color: #f4f8ff;
                box-shadow: 0 4px 16px rgba(0, 102, 254, 0.08);
            }

            .acc-add-workspace-icon {
                width: 34px;
                height: 34px;
                border-radius: 50%;
                background-color: #ebf5ff;
                color: #0066fe;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                transition: all 0.2s ease;
                flex-shrink: 0;
            }

            .acc-add-workspace-btn:hover .acc-add-workspace-icon {
                background-color: #0066fe;
                color: #ffffff;
            }

            .acc-add-workspace-text {
                font-size: 0.82rem;
                font-weight: 700;
                color: #1e293b;
                letter-spacing: 0.6px;
                text-transform: uppercase;
                transition: color 0.2s ease;
            }

            .acc-add-workspace-btn:hover .acc-add-workspace-text {
                color: #0066fe;
            }

            /* Right Actions */
            .acc-actions {
                display: flex;
                align-items: center;
                gap: 16px;
            }

            .acc-action-btn {
                background: transparent;
                border: none;
                color: #94a3b8;
                font-size: 20px;
                cursor: pointer;
                padding: 6px;
                border-radius: 50%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                transition: all 0.2s;
            }

            /* .acc-action-btn:hover {
                color: #0f172a;
                background-color: #f1f5f9;
            } */

            /* User Profile in Header */
            .acc-user-profile {
                display: flex;
                align-items: center;
                gap: 10px;
                text-decoration: none;
                padding: 4px 8px;
                border-radius: 10px;
                transition: all 0.2s ease;
            }

            .acc-user-profile:hover {
                background-color: #f8fafc;
            }

            .acc-user-avatar {
                width: 38px;
                height: 38px;
                border-radius: 50%;
                background-color: #cbd5e1;
                color: #ffffff;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                cursor: pointer;
                transition: all 0.2s ease;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
                flex-shrink: 0;
            }

            .acc-user-avatar:hover {
                background-color: #94a3b8;
            }

            .acc-user-info {
                display: flex;
                flex-direction: column;
                line-height: 1.15;
                text-align: left;
            }

            .acc-user-name {
                font-size: 0.88rem;
                font-weight: 700;
                color: #1e293b;
                white-space: nowrap;
            }

            .acc-user-role {
                font-size: 0.70rem;
                color: #64748b;
                white-space: nowrap;
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
            <div class="d-flex align-items-center">
                <a href="/cpd_ac/public/main" class="acc-brand-wrap">
                    <div class="acc-brand-logo">
                        <img src="/cpd_ac/public/assets/images/G_AM_logo-01.jpg" alt="Cpd Acc Logo">
                    </div>
                    <div class="acc-brand-info">
                        <h1 class="acc-brand-title">CPD ACC</h1>
                        <p class="acc-brand-subtitle">ระบบบริหารสำนักงานบัญชี</p>
                    </div>
                </a>

                <!-- Workspace Selector Dropdown -->
                <div class="dropdown ms-4 me-3">
                    <button class="btn bg-white d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 6px 14px 6px 8px; box-shadow: 0 1px 3px rgba(16, 24, 40, 0.03); gap: 10px; transition: all 0.2s;">
                        <div style="width: 34px; height: 34px; border-radius: 50%; background-color: #ebf5ff; color: #1e70eb; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                            <i class="ri-bank-line"></i>
                        </div>
                        <div class="text-start" style="line-height: 1.1;">
                            <div style="font-size: 0.6rem; font-weight: 800; color: #94a3b8; letter-spacing: 0.5px; text-transform: uppercase;">WORKSPACE</div>
                            <div style="font-size: 0.9rem; font-weight: 800; color: #1e293b; margin-top: 1px;">
                                <?php 
                                // แสดงชื่อบริษัทแรกถ้ามี หรือข้อความเริ่มต้น
                                echo isset($data['companies'][0]) ? htmlspecialchars($data['companies'][0]['company_name']) : 'ยังไม่มีบริษัท'; 
                                ?>
                            </div>
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: 500; margin-top: 1px;">ปีทำงาน 2569</div>
                        </div>
                        <i class="ri-arrow-down-s-line ms-1" style="color: #94a3b8; font-size: 1rem;"></i>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" style="border-radius: 12px; min-width: 260px; padding: 12px; border: 1px solid #edf2f7 !important;">
                        <li class="px-2 pb-2 mb-2 border-bottom">
                            <span class="text-muted" style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">เลือกบริษัท</span>
                        </li>
                        <?php if (isset($data['companies']) && !empty($data['companies'])): ?>
                            <?php foreach ($data['companies'] as $company): ?>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center rounded py-2 mb-1" href="#" style="transition: all 0.2s; font-weight: 600; color: #334155;">
                                        <div style="width: 32px; height: 32px; border-radius: 8px; background-color: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                            <i class="ri-building-4-line"></i>
                                        </div>
                                        <?php echo htmlspecialchars($company['company_name'] ?? 'ไม่มีชื่อบริษัท'); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li><span class="dropdown-item text-muted">ไม่พบข้อมูลบริษัท</span></li>
                        <?php endif; ?>
                        
                        <li><hr class="dropdown-divider my-2"></li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center rounded py-2 text-primary" href="javascript:void(0);" onclick="Getmodal_add()" style="font-weight: 700;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background-color: #ebf5ff; color: #1e70eb; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                    <i class="ri-add-line fs-5"></i>
                                </div>
                                สร้างบริษัทใหม่
                            </a>
                        </li>
                    </ul>
                </div>

                <!--ปุ่มเพิ่มบริษัท -->
                <button type="button" class="acc-add-workspace-btn flex-shrink-0" onclick="Getmodal_add()" title="เพิ่มบริษัท">
                    <div class="acc-add-workspace-icon">
                        <i class="ri-add-line"></i>
                    </div>
                    <span class="acc-add-workspace-text">เพิ่มบริษัท</span>
                </button>
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

                <!-- Logout Icon -->
                <a href="/cpd_ac/public/logout" class="acc-action-btn text-danger" title="ออกจากระบบ">
                    <i class="ri-logout-box-r-line"></i>
                </a>
            </div>
        </header>
        <!-- Modal เพิ่มบริษัท -->
        <div class="modal fade" id="addCompanyModal" tabindex="-1" aria-labelledby="addCompanyModalLabel" aria-hidden="true">
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
            function Getmodal_add() {
                // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
                const form = document.getElementById('addCompanyForm');
                if(form) {
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
                    success: function(response) {
                        if(response.result === 1) {
                            // ถ้าบันทึกสำเร็จ แจ้งเตือนและปิด Modal
                            if(typeof Swal !== 'undefined') {
                                Swal.fire(
                                    'สำเร็จ',
                                    response.msg,
                                    'success'
                                ).then(() => {
                                    location.reload(); 
                                });
                            } else {
                                alert(response.msg);
                                location.reload();
                            }
                        } else {
                            // ถ้าบันทึกไม่สำเร็จ แจ้งเตือน Error
                            if(typeof Swal !== 'undefined') {
                                Swal.fire(
                                    'ผิดพลาด',
                                    response.msg,
                                    'error'
                                );
                            } else {
                                alert(response.msg);
                            }
                        }
                    },
                    error: function(err) {
                        console.error("AJAX Error:", err);
                        alert("เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์");
                    }
                });
            }
        </script>

        <?php
        // if (file_exists(__DIR__ . '/sidebar.php')) {
        //     include __DIR__ . '/sidebar.php';
        // }
        ?>
