<?php
    $selected_year          = $_GET['year'] ?? '2569';
    $company_name           = $_GET['company'] ?? 'TEST ACCOUNTING';
    $show_company_workspace = false;

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
                    
                    <div class="page-header-box d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div>
                            <h3 class="mb-1 fw-bold" style="color: #333;">ตั้งค่าระบบ</h3>
                            <p class="text-muted small mb-0">
                                <i class="bi bi-info-circle me-1"></i> ตั้งค่าระบบต่างๆ สำหรับผู้ดูแลระบบ
                            </p>
                        </div>
                    </div>

                    <style>
                        .settings-wrapper {
                            display: flex;
                            gap: 24px;
                        }
                        .settings-sidebar {
                            width: 260px;
                            flex-shrink: 0;
                        }
                        .settings-content {
                            flex-grow: 1;
                        }
                        .settings-nav .nav-link {
                            color: #475569;
                            font-weight: 500;
                            padding: 12px 18px;
                            border-radius: 10px;
                            margin-bottom: 8px;
                            transition: all 0.2s ease-in-out;
                            text-align: left;
                        }
                        .settings-nav .nav-link:hover {
                            background-color: #f1f5f9;
                            color: #2563eb;
                        }
                        .settings-nav .nav-link.active {
                            background-color: #eff6ff;
                            color: #2563eb;
                            font-weight: 600;
                        }
                        .settings-nav .nav-link i {
                            margin-right: 10px;
                            font-size: 1.2rem;
                            vertical-align: middle;
                        }
                        .setting-card {
                            background: #ffffff;
                            border-radius: 12px;
                            box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
                            padding: 28px;
                            margin-bottom: 24px;
                            border: 1px solid #f1f5f9;
                        }
                        .setting-card-title {
                            font-size: 1.15rem;
                            font-weight: 600;
                            color: #1e293b;
                            margin-bottom: 20px;
                            padding-bottom: 14px;
                            border-bottom: 1px solid #e2e8f0;
                            display: flex;
                            align-items: center;
                            gap: 10px;
                        }
                        .setting-card-title i {
                            color: #64748b;
                        }
                        .form-label {
                            font-weight: 500;
                            color: #334155;
                            font-size: 0.95rem;
                        }
                        .form-control:focus, .form-select:focus {
                            border-color: #93c5fd;
                            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.15);
                        }
                        .btn-primary {
                            background-color: #2563eb;
                            border-color: #2563eb;
                            font-weight: 500;
                            padding: 8px 20px;
                            border-radius: 8px;
                        }
                        .btn-primary:hover {
                            background-color: #1d4ed8;
                            border-color: #1d4ed8;
                        }
                    </style>

                    <div class="settings-wrapper">
                        <!-- Sidebar -->
                        <div class="settings-sidebar">
                            <div class="nav flex-column nav-pills settings-nav" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                <button class="nav-link active" id="v-pills-profile-tab" data-bs-toggle="pill" data-bs-target="#v-pills-profile" type="button" role="tab" aria-controls="v-pills-profile" aria-selected="true">
                                    <i class="ri-user-settings-line"></i> ข้อมูลผู้ดูแลระบบ
                                </button>
                                <button class="nav-link" id="v-pills-options-tab" data-bs-toggle="pill" data-bs-target="#v-pills-options" type="button" role="tab" aria-controls="v-pills-options" aria-selected="false">
                                    <i class="ri-list-settings-line"></i> ตัวเลือกในระบบ
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="settings-content">
                            <div class="tab-content" id="v-pills-tabContent">
                                
                                <!-- ข้อมูลผู้ดูแลระบบ -->
                                <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                    <div class="setting-card">
                                        <h5 class="setting-card-title"><i class="ri-user-settings-line"></i> ข้อมูลส่วนตัว (Super Admin Profile)</h5>
                                        <form>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">ชื่อ</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['firstname']); ?>">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">นามสกุล</label>
                                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['lastname']); ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">อีเมล / Username</label>
                                                    <input type="email" class="form-control" value="" placeholder="admin@example.com">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">เบอร์โทรศัพท์</label>
                                                    <input type="text" class="form-control" value="" placeholder="08X-XXX-XXXX">
                                                </div>
                                            </div>
                                            <div class="text-end mt-3 pt-3 border-top">
                                                <button type="button" class="btn btn-primary px-4"><i class="ri-save-3-line me-1"></i> บันทึกข้อมูลส่วนตัว</button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="setting-card">
                                        <h5 class="setting-card-title"><i class="ri-lock-password-line"></i> เปลี่ยนรหัสผ่าน (Change Password)</h5>
                                        <form>
                                            <div class="mb-3">
                                                <label class="form-label">รหัสผ่านปัจจุบัน</label>
                                                <input type="password" class="form-control" placeholder="••••••••">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">รหัสผ่านใหม่</label>
                                                    <input type="password" class="form-control" placeholder="••••••••">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                                    <input type="password" class="form-control" placeholder="••••••••">
                                                </div>
                                            </div>
                                            <div class="text-end mt-3 pt-3 border-top">
                                                <button type="button" class="btn btn-primary px-4"><i class="ri-save-3-line me-1"></i> เปลี่ยนรหัสผ่าน</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- ตัวเลือกในระบบ -->
                                <div class="tab-pane fade" id="v-pills-options" role="tabpanel" aria-labelledby="v-pills-options-tab">
                                    <div class="setting-card">
                                        <h5 class="setting-card-title"><i class="ri-list-settings-line"></i> ตัวเลือกการตั้งค่าในระบบ (System Options)</h5>
                                        <p class="text-muted small mb-4">จัดการรายการตัวเลือกต่างๆ ที่ใช้ในระบบ (เช่น ประเภทงาน, สถานะ, ค่าเริ่มต้นต่างๆ)</p>
                                        
                                        <!-- สามารถใช้ Accordion หรือ List group เพื่อแยกหมวดหมู่ตัวเลือกย่อยๆ -->
                                        <div class="accordion" id="accordionOptions">
                                            <!-- Option 1 -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingOne">
                                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                                        <i class="ri-file-list-3-line me-2"></i> ตั้งค่าประเภทงาน (Job Types)
                                                    </button>
                                                </h2>
                                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionOptions">
                                                    <div class="accordion-body">
                                                        <div class="d-flex justify-content-between mb-3">
                                                            <span>รายการประเภทงานในระบบ</span>
                                                            <button class="btn btn-sm btn-outline-primary"><i class="ri-add-line"></i> เพิ่มประเภทงาน</button>
                                                        </div>
                                                        <table class="table table-bordered table-sm">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th>ชื่อประเภทงาน</th>
                                                                    <th width="100">สถานะ</th>
                                                                    <th width="100" class="text-center">จัดการ</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>ทำบัญชีรายเดือน</td>
                                                                    <td><span class="badge bg-success">ใช้งาน</span></td>
                                                                    <td class="text-center">
                                                                        <button class="btn btn-sm btn-light text-primary"><i class="ri-edit-line"></i></button>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td>ปิดงบการเงินประจำปี</td>
                                                                    <td><span class="badge bg-success">ใช้งาน</span></td>
                                                                    <td class="text-center">
                                                                        <button class="btn btn-sm btn-light text-primary"><i class="ri-edit-line"></i></button>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Option 2 -->
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingTwo">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                        <i class="ri-notification-3-line me-2"></i> ตั้งค่าระบบแจ้งเตือน (Notifications Option)
                                                    </button>
                                                </h2>
                                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionOptions">
                                                    <div class="accordion-body">
                                                        <form>
                                                            <div class="mb-3">
                                                                <label class="form-label">LINE Notify Token สำหรับสำนักงาน</label>
                                                                <input type="text" class="form-control" placeholder="ใส่ Token ของ LINE Notify">
                                                            </div>
                                                            <div class="text-end">
                                                                <button type="button" class="btn btn-sm btn-primary">บันทึก</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/main/footer.php'; ?>
