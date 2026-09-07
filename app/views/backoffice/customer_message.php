<?php
// app/views/backoffice/customer_message.php
$selected_year = $_GET['year'] ?? '2569';
$company_name  = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

$thai_months = [
    'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
];

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<style>
    /* ==================================================
       --- Customer Message Module Custom Styling ---
       ================================================== */
    
    /* Top Line Banner Card */
    .line-banner-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.03);
        transition: all 0.25s ease;
    }
    
    .line-banner-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
    }

    /* Message Cards */
    .msg-card {
        background: #ffffff;
        border: 1px solid #edf2f7;
        border-radius: 18px;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .msg-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
        transform: translateY(-2px);
    }

    /* Icon Badges */
    .msg-icon-badge {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }

    .msg-icon-badge.blue {
        color: #2563eb;
    }

    .msg-icon-badge.green {
       
        color: #16a34a;
        
    }

    .msg-icon-badge.yellow {
        color: #ca8a04;
    }

    .msg-icon-badge.purple {
        color: #9333ea;
    }

    /* Month Selector */
    .msg-month-select {
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 6px 32px 6px 12px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' height='16' fill='%2364748b'%3E%3Cpath d='M11.9997 13.1716L16.9495 8.22168L18.3637 9.63589L11.9997 16L5.63574 9.63589L7.04996 8.22168L11.9997 13.1716Z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        transition: all 0.2s ease;
    }

    .msg-month-select:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
    }

    /* Textarea */
    .msg-textarea {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 14px;
        font-size: 0.88rem;
        color: #1e293b;
        padding: 14px 16px;
        font-weight: 500;
        line-height: 1.55;
        resize: vertical;
        transition: all 0.2s ease;
    }

    .msg-textarea:focus {
        background-color: #ffffff;
        border-color: #007aff;
        box-shadow: 0 0 0 4px rgba(0, 122, 255, 0.1);
        outline: none;
    }

    /* File Upload Container */
    .upload-box {
        background-color: #f8fafc;
        border: none;
        border-radius: 14px;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 28px;
        cursor: pointer;
        user-select: none;
        transition: all 0.2s ease;
    }

    .upload-box:hover {
        background-color: #f1f5f9;
    }

    

    

    /* Action Send Button */
    .btn-msg-send {
        background: linear-gradient(135deg, #007aff 0%, #0062cc 100%);
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 9px 28px;
        font-size: 0.88rem;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(0, 122, 255, 0.22);
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-msg-send:hover {
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(0, 122, 255, 0.32);
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
                            <h2 class="page-title">ส่งข้อความถึงลูกค้า</h2>
                            
                            <p class="page-subtitle">ภาพรวมระบบ &bull; ส่งข้อความถึงลูกค้า &bull; ปี -</p>
                        </div>
                    </div>

                    <!-- แถบแจ้งเตือน Line Messaging API บนสุด -->
                    <div class="line-banner-card p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="line-banner-icon">
                                    <i class="ri-chat-3-line"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1 fw-bold" style="color: #0f172a; font-size: 1.05rem;">ส่งข้อความถึงลูกค้า</h5>
                                    <p class="mb-0 text-muted" style="font-size: 0.84rem;">ส่งผ่าน LINE Messaging API ไปยัง Line Group ID ของลูกค้า</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <span style="background-color: #fef9c3; color: #ca8a04; font-size: 0.78rem; font-weight: 700; padding: 7px 14px; border-radius: 10px;">ยังไม่ได้ตั้งค่า Line Token</span>
                                <button type="button" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center gap-2" style="border-radius: 12px; padding: 8px 18px; font-weight: 700; font-size: 0.85rem; border-color: #cbd5e1; color: #334155; background-color: #ffffff; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                                    <i class="ri-settings-4-line" style="font-size: 16px;"></i>
                                    <span>ตั้งค่าระบบ</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Message Cards Grid (2x2) -->
                    <div class="row g-4">
                        
                        <!-- Card 1: ข้อความทวงเอกสาร -->
                        <div class="col-lg-6">
                            <div class="msg-card p-4">
                                <div>
                                    <div class="d-flex align-items-start justify-content-between mb-3 gap-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="msg-icon-badge blue">
                                                <i class="ri-file-text-line blue"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold" style="color: #0f172a; font-size: 0.98rem;">ข้อความทวงเอกสาร</h6>
                                                <small class="text-muted" style="font-size: 0.78rem;">ลูกค้าที่ยังไม่ได้ส่งเอกสารในเดือน - จำนวน - ราย</small>
                                            </div>
                                        </div>
                                        <select class="msg-month-select">
                                            <?php foreach ($thai_months as $m): ?>
                                                <option value="<?php echo $m; ?>" <?php echo ($m === 'กันยายน') ? 'selected' : ''; ?>><?php echo $m; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control msg-textarea" rows="4"></textarea>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: #f8fafc !important;">
                                    <span class="text-muted" style="font-size: 0.78rem; font-weight: 500;">พร้อมส่ง - ราย &bull; ไม่มี Line Group ID - ราย</span>
                                    <button type="button" class="btn btn-msg-send">ส่ง</button>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: ข้อความเตือนให้จ่ายภาษี -->
                        <div class="col-lg-6">
                            <div class="msg-card p-4">
                                <div>
                                    <div class="d-flex align-items-start justify-content-between mb-3 gap-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="msg-icon-badge green">
                                                <i class="ri-coin-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold" style="color: #0f172a; font-size: 0.98rem;">ข้อความเตือนให้จ่ายภาษี</h6>
                                                <small class="text-muted" style="font-size: 0.78rem;">ลูกค้าที่อยู่ในรอบบริการเดือน - จำนวน - ราย</small>
                                            </div>
                                        </div>
                                        <select class="msg-month-select">
                                            <?php foreach ($thai_months as $m): ?>
                                                <option value="<?php echo $m; ?>" <?php echo ($m === 'กันยายน') ? 'selected' : ''; ?>><?php echo $m; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control msg-textarea" rows="4"></textarea>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: #f8fafc !important;">
                                    <span class="text-muted" style="font-size: 0.78rem; font-weight: 500;">พร้อมส่ง - ราย &bull; ไม่มี Line Group ID - ราย</span>
                                    <button type="button" class="btn btn-msg-send">ส่ง</button>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: ข้อความทวงค่าทำบัญชี -->
                        <div class="col-lg-6">
                            <div class="msg-card p-4">
                                <div>
                                    <div class="d-flex align-items-start justify-content-between mb-3 gap-2">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="msg-icon-badge yellow">
                                                <i class="ri-wallet-3-line"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold" style="color: #0f172a; font-size: 0.98rem;">ข้อความทวงค่าทำบัญชี</h6>
                                                <small class="text-muted" style="font-size: 0.78rem;">ลูกค้าที่ยังไม่ได้ชำระค่าบริการในเดือน - จำนวน - ราย</small>
                                            </div>
                                        </div>
                                        <select class="msg-month-select">
                                            <?php foreach ($thai_months as $m): ?>
                                                <option value="<?php echo $m; ?>" <?php echo ($m === 'กันยายน') ? 'selected' : ''; ?>><?php echo $m; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control msg-textarea" rows="4"></textarea>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: #f8fafc !important;">
                                    <span class="text-muted" style="font-size: 0.78rem; font-weight: 500;">พร้อมส่ง - ราย &bull; ไม่มี Line Group ID - ราย</span>
                                    <button type="button" class="btn btn-msg-send">ส่ง</button>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: ข้อความประชาสัมพันธ์ -->
                        <div class="col-lg-6">
                            <div class="msg-card p-4">
                                <div>
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="msg-icon-badge purple">
                                            <i class="ri-notification-3-line"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold" style="color: #0f172a; font-size: 0.98rem;">ข้อความประชาสัมพันธ์</h6>
                                            <small class="text-muted" style="font-size: 0.78rem;">ลูกค้าสถานะใช้บริการอยู่ จำนวน 2 ราย</small>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <textarea class="form-control msg-textarea" rows="4"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label mb-2 " style="font-size: 0.84rem; color: #1e293b;">แนบรูปภาพ</label>
                                        <input type="file" class="d-none" id="announce_file" accept="image/*" onchange="document.getElementById('file_name_display').textContent = this.files[0] ? this.files[0].name : 'ไม่ได้เลือกไฟล์ใด'">
                                        <div class="upload-box" onclick="document.getElementById('announce_file').click()">
                                            <span>เลือกไฟล์</span>
                                            <span  id="file_name_display">ไม่ได้เลือกไฟล์ใด</span>
                                        </div>
                                        <small class="text-muted d-block mt-2" style="font-size: 0.74rem;">รองรับ JPG, PNG, GIF, WEBP ขนาดไม่เกิน 5MB</small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: #f8fafc !important;">
                                    <span class="text-muted" style="font-size: 0.78rem; font-weight: 500;">พร้อมส่ง - ราย &bull; ไม่มี Line Group ID - ราย</span>
                                    <button type="button" class="btn btn-msg-send">ส่ง</button>
                                </div>
                            </div>
                        </div>

                    </div>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<?php 
// 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php'; 
?>
