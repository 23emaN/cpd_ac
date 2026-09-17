<?php
// app/views/backoffice/system_setting.php

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<style>
    .setting-card {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        height: 100%;
    }

    .setting-icon-box {
        width: 48px;
        height: 48px;
        background-color: #eff6ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #3b82f6;
        font-size: 24px;
        margin-right: 16px;
    }

    .setting-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .setting-subtitle {
        font-size: 0.85rem;
        color: #64748b;
    }

    .form-label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .form-label .text-danger {
        color: #ef4444;
    }

    .form-control {
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        padding: 10px 16px;
        font-size: 0.95rem;
    }

    .form-control:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .help-text {
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 6px;
        display: block;
    }

    .btn-video {
        background-color: #eff6ff;
        color: #3b82f6;
        border: none;
        border-radius: 6px;
        padding: 4px 12px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .info-box {
        background-color: #f8fafc;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .info-box-blue {
        background-color: #eff6ff;
        border-radius: 8px;
        padding: 16px;
    }

    .info-label {
        font-size: 0.85rem;
        color: #64748b;
        margin-bottom: 6px;
        font-weight: 500;
    }

    .info-value {
        font-size: 0.95rem;
        color: #0f172a;
        font-weight: 600;
        word-break: break-all;
    }

    .badge-warning-soft {
        background-color: #fef3c7;
        color: #d97706;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
    }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                
                <div class="main-card-wrapper">
                    <!-- Page Header Section -->
                    <div class="page-header-box mb-4">
                        <div>
                            <h2 class="page-title">ตั้งค่าระบบ</h2>
                            <p class="page-subtitle">ภาพรวมระบบ - ตั้งค่าระบบ</p>
                        </div>
                    </div>
                    <!-- Main Content -->
                    <div class="row g-4">
                        <!-- Left Column (Form) -->
                        <div class="col-lg-12">
                            <div class="setting-card">
                                <form>
                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">ชื่อสำนักงานบัญชี <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" value="Accounting">
                                        </div>
                                        <div class="col-md-6 mt-3 mt-md-0">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label class="form-label mb-0">Line Token ID</label>
                                            </div>
                                            <input type="text" class="form-control" placeholder="เว้นว่างได้">
                                            <span class="help-text">เว้นว่างได้</span>
                                        </div>
                                    </div>

                                    <div class="row mb-4">
                                        <div class="col-md-6">
                                            <label class="form-label">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" value="TBacc">
                                            <span class="help-text">ใช้สำหรับ login จากลิงก์สำนักงานบัญชี</span>
                                        </div>
                                    </div>

                                    <div class="row mb-5">
                                        <div class="col-md-6">
                                            <label class="form-label">รหัสผ่านใหม่</label>
                                            <input type="password" class="form-control">
                                            <span class="help-text">เว้นว่างไว้ถ้าไม่ต้องการเปลี่ยนรหัสผ่าน</span>
                                        </div>
                                        <div class="col-md-6 mt-3 mt-md-0">
                                            <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                            <input type="password" class="form-control">
                                        </div>
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

<?php
// นำ Footer เข้ามา (ถ้ามี)
?>
