<?php 
// app/views/main/index.php
require_once __DIR__ . '/header.php'; 
?>

<style>
    /* --- Year Selection Page Styles --- */
    body {
        background-color: #f8fafc;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .main-page-wrapper {
        padding: 28px 36px;
        min-height: calc(100vh - 72px);
    }

    /* Page Header */
    .page-title-section {
        margin-bottom: 24px;
    }

    .page-main-heading {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 3px;
        letter-spacing: -0.2px;
    }

    .page-breadcrumb-sub {
        font-size: 0.82rem;
        color: #64748b;
        margin: 0;
    }

    /* Main Container Card */
    .year-container-card {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 12px rgba(16, 24, 40, 0.03);
        padding: 32px;
    }

    /* Section Header */
    .section-header-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
    }

    .section-title-box {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .section-icon-badge {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background-color: #ebf5ff;
        color: #1e70eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .section-title-text {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }

    .section-subtitle-text {
        font-size: 0.82rem;
        color: #64748b;
        margin: 0;
    }

    .btn-add-year {
        background-color: #0066fe;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 9px 20px;
        font-size: 0.90rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        box-shadow: 0 2px 8px rgba(0, 102, 254, 0.2);
    }

    .btn-add-year:hover {
        background-color: #0052cc;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(0, 102, 254, 0.3);
    }

    /* Information Notice Banner */
    .year-notice-banner {
        background-color: #f4f8ff;
        border: 1px dashed #93c5fd;
        border-radius: 12px;
        padding: 18px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
    }

    .notice-main-text {
        font-size: 0.88rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .notice-sub-text {
        font-size: 0.80rem;
        color: #64748b;
        margin: 0;
    }

    .notice-selected-box {
        text-align: right;
        flex-shrink: 0;
        margin-left: 20px;
    }

    .notice-selected-label {
        font-size: 0.72rem;
        color: #94a3b8;
        margin-bottom: 2px;
        display: block;
    }

    .notice-selected-value {
        font-size: 1.05rem;
        font-weight: 800;
        color: #0066fe;
        display: block;
    }

    /* Year Selection Grid & Add Card */
    .year-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(290px, 320px));
        gap: 24px;
    }

    /* Empty Dashed Add Year Card */
    .year-add-card {
        background-color: #ffffff;
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        min-height: 230px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        padding: 24px;
    }

    .year-add-card:hover {
        border-color: #0066fe;
        background-color: #f4f8ff;
        box-shadow: 0 4px 16px rgba(0, 102, 254, 0.08);
    }

    .year-add-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        transition: all 0.2s ease;
        margin-bottom: 4px;
    }

    .year-add-card:hover .year-add-icon {
        background-color: #0066fe;
        color: #ffffff;
    }

    .year-add-text {
        font-size: 1.05rem;
        font-weight: 700;
        color: #334155;
        transition: color 0.2s ease;
    }

    .year-add-card:hover .year-add-text {
        color: #0066fe;
    }

    .year-add-subtext {
        font-size: 0.78rem;
        color: #94a3b8;
        transition: color 0.2s ease;
    }

    .year-add-card:hover .year-add-subtext {
        color: #60a5fa;
    }
</style>

<div class="main-page-wrapper">
    <!-- 2. Main Content Card -->
    <div class="year-container-card">
        <div class="page-title-section">
        <h2 class="page-main-heading">เลือกปีทำงาน</h2>
        <p class="page-breadcrumb-sub">ภาพรวมระบบ • เลือกปีทำงาน</p>
    </div>
        <!-- Section Header (Title & Add Button) -->
        <div class="section-header-wrap">
            <div class="section-title-box">
                <div class="section-icon-badge">
                    <i class="ri-calendar-2-line"></i>
                </div>
                <div>
                    <h3 class="section-title-text">เลือกปีที่ต้องการทำงาน</h3>
                    <p class="section-subtitle-text">Accounting</p>
                </div>
            </div>
        </div>

        <!-- Info Notice Banner -->
        <div class="year-notice-banner">
            <div>
                <div class="notice-main-text">ปีทำงานจะเป็นตัวกรองหลักของข้อมูลสำนักงาน</div>
                <p class="notice-sub-text">เมื่อเลือกปีแล้ว ระบบจะใช้ปีนั้นกับหน้าลูกค้า พนักงาน งานรายเดือน และรายงานที่จะเพิ่มต่อไป</p>
            </div>
            <div class="notice-selected-box">
                <span class="notice-selected-label">ปีที่เลือก</span>
                <span class="notice-selected-value">ยังไม่ได้เลือก</span>
            </div>
        </div>

        <!-- Year Card Grid (Empty Add Year State) -->
        <div class="year-card-grid">
            <!-- วนลูปแสดงการ์ดของแต่ละปีที่มีในระบบ -->
            <?php if (isset($data['fiscal_years']) && !empty($data['fiscal_years'])): ?>
                <?php foreach ($data['fiscal_years'] as $fy): ?>
                    <button type="button" class="year-add-card fiscal-year-card" style="border-color: #e2e8f0; background-color: #fff;" onclick="selectYear('<?php echo htmlspecialchars($fy['year_id']); ?>')">
                        <div class="year-add-icon" style="background-color: #f1f5f9; color: #64748b;">
                            <i class="ri-folder-open-line"></i>
                        </div>
                        <span class="year-add-text">ปี <?php echo htmlspecialchars($fy['year']); ?></span>
                        <span class="year-add-subtext">สถานะ: <?php echo $fy['status'] === 'active' ? 'เปิดใช้งาน' : 'ปิดการใช้งาน'; ?></span>
                    </button>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- ปุ่มเพิ่มปีทำงานใหม่ -->
            <button type="button" class="year-add-card" id="btn-add-year-card" onclick="Addyear()">
                <div class="year-add-icon">
                    <i class="ri-add-line"></i>
                </div>
                <span class="year-add-text">เพิ่มปี</span>
                <span class="year-add-subtext">คลิกเพื่อสร้างปีทำงานใหม่</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal เพิ่มปีทำงาน -->
<div class="modal fade" id="addYearModal" tabindex="-1" aria-labelledby="addYearModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid #f1f5f9; padding: 20px 24px;">
                <h5 class="modal-title" id="addYearModalLabel" style="font-weight: 800; color: #1e293b; font-size: 1.15rem;">เพิ่มปีทำงาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem; opacity: 0.5;"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <form id="addYearForm">
                    <!-- ส่ง company_id ปัจจุบันไปด้วย -->
                    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($data['companies'][0]['id'] ?? ''); ?>">

                    <!-- ปี พ.ศ. -->
                    <div class="mb-4">
                        <label class="form-label" style="font-weight: 700; font-size: 0.9rem; color: #334155;">ปี พ.ศ. <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="working_year" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; padding: 12px 16px; font-weight: 600; color: #1e293b; box-shadow: none;">
                        <div class="form-text" style="font-size: 0.75rem; color: #94a3b8; margin-top: 6px;">กรอกปี พ.ศ. ระหว่าง 2500 ถึง 2600</div>
                    </div>

                    <!-- คัดลอกข้อมูลจากปี (แสดงต่อเมื่อมีปีทำงานเก่าให้คัดลอก) -->
                    <?php if (isset($data['fiscal_years']) && !empty($data['fiscal_years'])): ?>
                        <div class="mb-4">
                            <label class="form-label" style="font-weight: 700; font-size: 0.9rem; color: #334155;">คัดลอกข้อมูลจากปี</label>
                            <select class="form-select" name="copy_from_year" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 10px; padding: 12px 16px; font-weight: 600; color: #475569; cursor: pointer; box-shadow: none;">
                                <?php foreach ($data['fiscal_years'] as $fy): ?>
                                    <option value="<?php echo htmlspecialchars($fy['year_id'] ?? $fy['year']); ?>">ปี <?php echo htmlspecialchars($fy['year']); ?></option>
                                <?php endforeach; ?>
                                <option value="">ไม่คัดลอก (เริ่มใหม่ทั้งหมด)</option>
                            </select>
                        </div>

                        <!-- ข้อมูลที่ต้องการคัดลอก -->
                        <div class="copy-options-box" style="border: 1px dashed #cbd5e1; border-radius: 12px; padding: 20px; background-color: #ffffff;">
                            <label class="form-label" style="font-weight: 700; font-size: 0.9rem; color: #1e293b; margin-bottom: 16px;">ข้อมูลที่ต้องการคัดลอก</label>
                            
                            <div class="form-check mb-3 d-flex align-items-center">
                                <input class="form-check-input flex-shrink-0" type="checkbox" value="customers" id="chkCustomers" checked style="width: 22px; height: 22px; border-radius: 6px; cursor: pointer; border-color: #0066fe; background-color: #0066fe; margin-top: 0;">
                                <label class="form-check-label ms-3" for="chkCustomers" style="font-weight: 600; color: #64748b; cursor: pointer; font-size: 0.9rem;">
                                    ข้อมูลลูกค้าที่ใช้งานอยู่
                                </label>
                            </div>
                            
                            <div class="form-check mb-3 d-flex align-items-center">
                                <input class="form-check-input flex-shrink-0" type="checkbox" value="employees" id="chkEmployees" checked style="width: 22px; height: 22px; border-radius: 6px; cursor: pointer; border-color: #0066fe; background-color: #0066fe; margin-top: 0;">
                                <label class="form-check-label ms-3" for="chkEmployees" style="font-weight: 600; color: #64748b; cursor: pointer; font-size: 0.9rem;">
                                    ข้อมูลพนักงานที่ใช้งานอยู่
                                </label>
                            </div>
                            
                            <div class="form-check mb-0 d-flex align-items-center">
                                <input class="form-check-input flex-shrink-0" type="checkbox" value="monthly_jobs" id="chkJobs" checked style="width: 22px; height: 22px; border-radius: 6px; cursor: pointer; border-color: #0066fe; background-color: #0066fe; margin-top: 0;">
                                <label class="form-check-label ms-3" for="chkJobs" style="font-weight: 600; color: #64748b; cursor: pointer; font-size: 0.9rem;">
                                    ตั้งค่างานรายเดือน
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #f1f5f9; padding: 20px 24px; gap: 12px; justify-content: flex-end; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <button type="button" class="btn" data-bs-dismiss="modal" style="background-color: #f8fafc; color: #334155; font-weight: 700; border-radius: 10px; padding: 10px 24px; border: none; font-size: 0.95rem;">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="submitAddYear()" style="background-color: #0066fe; font-weight: 700; border-radius: 10px; padding: 10px 24px; border: none; box-shadow: 0 4px 12px rgba(0,102,254,0.25); font-size: 0.95rem;">สร้างปี</button>
            </div>
        </div>
    </div>
</div>

<script>
    function Addyear() {
        // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
        const form = document.getElementById('addYearForm');
        if(form) {
            form.reset();
        }
        // 2. สั่งโชว์ Modal ผ่าน Vanilla JS ของ Bootstrap
        const modalElement = document.getElementById('addYearModal');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }
    
    function submitAddYear() {
        var formData = $('#addYearForm').serialize();
        
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
            url: "/cpd_ac/public/fiscal_years/add",
            data: formData,
            dataType: "json",
            success: function(response) {
                if(response.result === 1) {
                    // ถ้าบันทึกสำเร็จ แจ้งเตือน Toast และปิด Modal
                    if(typeof Swal !== 'undefined') {
                        $('#addYearModal').modal('hide');
                        Toast.fire({
                            icon: 'success',
                            title: response.msg
                        }).then(() => {
                            location.reload(); 
                        });
                    } else {
                        alert(response.msg);
                        location.reload();
                    }
                } else {
                    // ถ้าบันทึกไม่สำเร็จ แจ้งเตือน Error
                    if(typeof Swal !== 'undefined') {
                        Toast.fire({
                            icon: 'error',
                            title: response.msg
                        });
                    } else {
                        alert(response.msg);
                    }
                }
            },
            error: function(err) {
                console.error("AJAX Error:", err);
                if(typeof Swal !== 'undefined') {
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
    function loadFiscalYears(companyId) {
        // อัปเดต input ซ่อนในฟอร์มเพิ่มปี
        $('input[name="company_id"]').val(companyId);
        
        // เคลียร์การ์ดเก่าออก ยกเว้นการ์ดปุ่ม "เพิ่มปี"
        $('.year-card-grid .fiscal-year-card').remove();
        
        // เพิ่ม Spinner ถ้าระบบโหลดนาน (Optional)
        // $('.year-card-grid').prepend('<div class="spinner-border text-primary fiscal-year-card" role="status"></div>');

        $.ajax({
            url: '/cpd_ac/public/fiscal_years/get?company_id=' + companyId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                $('.year-card-grid .spinner-border').remove();
                if (response.result === 1 && response.data.length > 0) {
                    let optionsHtml = '';
                    response.data.forEach(function(fy) {
                        let yearId = fy.year_id || fy.id;
                        let yearName = fy.fiscal_years || fy.working_year || fy.year;
                        let statusText = fy.status === 'active' ? 'เปิดใช้งาน' : 'ปิดการใช้งาน';
                        
                        let cardHtml = `
                            <button type="button" class="year-add-card fiscal-year-card" style="border-color: #e2e8f0; background-color: #fff;" onclick="selectYear('${yearId}')">
                                <div class="year-add-icon" style="background-color: #f1f5f9; color: #64748b;">
                                    <i class="ri-folder-open-line"></i>
                                </div>
                                <span class="year-add-text">ปี ${yearName}</span>
                                <span class="year-add-subtext">สถานะ: ${statusText}</span>
                            </button>
                        `;
                        // แทรกก่อนปุ่ม "เพิ่มปี"
                        $('#btn-add-year-card').before(cardHtml);
                        
                        optionsHtml += `<option value="${yearId}">ปี ${yearName}</option>`;
                    });
                    
                    // อัปเดต Select คัดลอกข้อมูล
                    $('select[name="copy_from_year"]').html(optionsHtml + '<option value="">ไม่คัดลอก (เริ่มใหม่ทั้งหมด)</option>');
                } else {
                    $('select[name="copy_from_year"]').html('<option value="">ไม่คัดลอก (เริ่มใหม่ทั้งหมด)</option>');
                }
            },
            error: function() {
                $('.year-card-grid .spinner-border').remove();
            }
        });
    }

    function selectYear(fiscal_id) {
        $.ajax({
            url: '<?php echo BASE_URL; ?>/fiscal_years/set_context',
            type: 'POST',
            data: { fiscal_id: fiscal_id },
            success: function(response) {
                // เซิร์ฟเวอร์จำค่าสำเร็จ เปลี่ยนหน้าแบบคลีนๆ ได้เลย
                window.location.href = '<?php echo BASE_URL; ?>/backoffice';
            },
            error: function() {
                alert('เกิดข้อผิดพลาดในการเลือกปีทำงาน');
            }
        });
    }

    $(document).ready(function() {
        let savedCompany = localStorage.getItem('bo_selected_company');
        if (savedCompany) {
            selectCompanyById(savedCompany);
        } else {
            // ค้นหาปุ่มบริษัทแรกแล้วกดคลิก
            let firstCompany = document.querySelector('.acc-company-btn');
            if (firstCompany) {
                let companyId = firstCompany.getAttribute('data-company-id');
                selectCompany(firstCompany, companyId);
            }
        }
    });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>