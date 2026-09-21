<?php
    $selected_year          = $_GET['year'] ?? '2569';
    $company_name           = $_GET['company'] ?? 'TEST ACCOUNTING';
    $show_company_workspace = false;

    // 1. นำ Header เข้ามา
    require_once dirname(__DIR__) . '/main/header.php';

    // 2. นำ Sidebar เข้ามา
    require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<style>
.settings-wrapper { display:flex; gap:24px; align-items:flex-start; }
.settings-sidebar { width:240px; flex-shrink:0; }
.settings-content { flex-grow:1; min-width:0; }
.s-tab-btn {
    display:flex; align-items:center; gap:10px; width:100%; text-align:left;
    padding:12px 18px; border:none; border-radius:10px; background:transparent;
    color:#475569; font-weight:500; font-size:0.9rem; cursor:pointer; margin-bottom:6px; transition:all 0.2s;
}
.s-tab-btn:hover { background:#f1f5f9; color:#1e293b; }
.s-tab-btn.s-active { background:linear-gradient(135deg,#eff6ff,#dbeafe); color:#1d4ed8; font-weight:600; }
.s-tab-btn i { font-size:1.1rem; }
.s-panel { display:none; }
.s-panel.s-active { display:block; }
.setting-card { background:#fff; border-radius:14px; padding:28px; box-shadow:0 1px 3px rgba(0,0,0,0.07); margin-bottom:20px; }
.setting-card-title { font-size:1rem; font-weight:700; color:#1e293b; margin-bottom:20px; padding-bottom:12px; border-bottom:2px solid #f1f5f9; }
.setting-card-title i { color:#3b82f6; margin-right:6px; }
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">

                    <div class="page-header-box d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div>
                            <h3 class="mb-1 fw-bold" style="color: #333;">ตั้งค่าระบบ</h3>
                            <p class="text-muted small mb-0">
                                <i class="ri-settings-3-line me-1"></i> ตั้งค่าระบบต่างๆ สำหรับผู้ดูแลระบบ
                            </p>
                        </div>
                    </div>

                    <div class="settings-wrapper">

                        <!-- Sidebar Nav -->
                        <div class="settings-sidebar">
                            <button class="s-tab-btn s-active" onclick="switchTab('panel-profile', this)">
                                <i class="ri-user-settings-line"></i> ข้อมูลผู้ดูแลระบบ
                            </button>
                            <button class="s-tab-btn" onclick="switchTab('panel-options', this)">
                                <i class="ri-list-settings-line"></i> ตัวเลือกในระบบ
                            </button>
                        </div>

                        <!-- Content -->
                        <div class="settings-content">

                            <!-- Panel: ข้อมูลผู้ดูแลระบบ -->
                            <div class="s-panel s-active" id="panel-profile">

                                <div class="setting-card">
                                    <h5 class="setting-card-title">
                                        <i class="ri-user-settings-line"></i> ข้อมูลส่วนตัว (Super Admin Profile)
                                    </h5>
                                    <form id="formUpdateProfile">
                                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($data['user_id'] ?? ''); ?>">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">ชื่อ (user_firstname)</label>
                                                <input type="text" class="form-control" name="user_firstname" placeholder="ชื่อ" value="<?php echo htmlspecialchars($data['firstname'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">นามสกุล (user_lastname)</label>
                                                <input type="text" class="form-control" name="user_lastname" placeholder="นามสกุล" value="<?php echo htmlspecialchars($data['lastname'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">ชื่อผู้ใช้งาน (username)</label>
                                                <input type="text" class="form-control" name="user_name" placeholder="username" value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">อีเมล (email)</label>
                                                <input type="email" class="form-control" name="email" placeholder="admin@example.com" value="<?php echo htmlspecialchars($data['user_email'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        <div class="text-end mt-3 pt-3 border-top">
                                            <button type="button" class="btn btn-primary px-4">
                                                <i class="ri-save-3-line me-1"></i> บันทึกข้อมูลส่วนตัว
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <div class="setting-card">
                                    <h5 class="setting-card-title">
                                        <i class="ri-lock-password-line"></i> เปลี่ยนรหัสผ่าน (Change Password)
                                    </h5>
                                    <form id="formChangePassword">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">รหัสผ่านใหม่</label>
                                                <div class="position-relative">
                                                    <input type="password" class="form-control pe-5" name="new_password" id="new_password" placeholder="กรอกรหัสผ่านใหม่">
                                                    <span onclick="togglePassword('new_password', this)"
                                                          style="position:absolute;top:50%;right:12px;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:1.1rem;line-height:1;">
                                                        <i class="ri-eye-off-line"></i>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                                <div class="position-relative">
                                                    <input type="password" class="form-control pe-5" name="confirm_password" id="confirm_password" placeholder="กรอกอีกครั้ง">
                                                    <span onclick="togglePassword('confirm_password', this)"
                                                          style="position:absolute;top:50%;right:12px;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:1.1rem;line-height:1;">
                                                        <i class="ri-eye-off-line"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-end mt-3 pt-3 border-top">
                                            <button type="button" class="btn btn-primary px-4">
                                                <i class="ri-save-3-line me-1"></i> เปลี่ยนรหัสผ่าน
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>

                            <div class="s-panel" id="panel-options">

                                <div class="setting-card">
                                    <h5 class="setting-card-title">
                                        <i class="ri-list-settings-line"></i> ตัวเลือกการตั้งค่าในระบบ (System Options)
                                    </h5>
                                    <p class="text-muted small mb-4">จัดการรายการตัวเลือกต่างๆ ที่ใช้ในระบบ</p>

                                    <div class="accordion" id="accordionOptions">

                                        <!-- Tax Options -->

                                        <div class="accordion-item border rounded-3 mb-2">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button fw-semibold"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseOne"
                                                aria-expanded="true">
                                                    <i class="ri-file-list-3-line me-2 text-primary"></i>
                                                    ตั้งค่าตัวเลือกการยื่นภาษี
                                                </button>
                                            </h2>

                                            <div id="collapseOne"
                                                class="accordion-collapse collapse show"
                                                data-bs-parent="#accordionOptions">

                                                <div class="accordion-body">

                                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                                        <span class="text-muted small">
                                                            รายการตัวเลือกภาษีในระบบ
                                                        </span>

                                                        <button type="button"
                                                            class="btn btn-sm btn-primary"
                                                            id="btnOpenAddTax">
                                                            <i class="ri-add-line"></i>
                                                            เพิ่มตัวเลือกภาษี
                                                        </button>
                                                    </div>

                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm align-middle mb-0">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th width="80" class="text-center">
                                                                        ลำดับ
                                                                    </th>

                                                                    <th>
                                                                        ชื่อตัวเลือกภาษี
                                                                    </th>

                                                                    <!-- <th width="120" class="text-center">
                                                                        สถานะ
                                                                    </th> -->

                                                                    <th width="120" class="text-center">
                                                                        จัดการ
                                                                    </th>
                                                                </tr>
                                                            </thead>

                                                            <tbody id="taxOptionsTableBody">
                                                                <?php if (! empty($data['tax_options'])): ?>
                                                                    <?php foreach ($data['tax_options'] as $i => $opt): ?>
                                                                        <tr>
                                                                            <td class="text-center"><?php echo $i + 1; ?></td>
                                                                            <td><?php echo htmlspecialchars($opt['option_name'] ?? ''); ?></td>
                                                                            <!-- <td class="text-center">
                                                                                <span class="badge bg-success">ใช้งาน</span>
                                                                            </td> -->
                                                                            <td class="text-center">
                                                                                <div class="action-btn-group">
                                                                                    <button type="button"
                                                                                        class="btn-action-edit btn-edit-tax"
                                                                                        title="แก้ไข"
                                                                                        data-id="<?php echo (int) $opt['option_id']; ?>"
                                                                                        data-name="<?php echo htmlspecialchars($opt['option_name'] ?? ''); ?>"
                                                                                        data-order="<?php echo (int) ($opt['list_order'] ?? 0); ?>">
                                                                                        <i class="ri-pencil-line"></i>
                                                                                    </button>
                                                                                    <button type="button"
                                                                                        class="btn-action-delete btn-delete-tax"
                                                                                        title="ลบ"
                                                                                        data-id="<?php echo (int) $opt['option_id']; ?>">
                                                                                        <i class="ri-delete-bin-line"></i>
                                                                                    </button>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    <?php endforeach; ?>
                                                                <?php else: ?>
                                                                    <tr>
                                                                        <td colspan="3" class="text-center text-muted py-3">
                                                                            ยังไม่มีข้อมูลตัวเลือกภาษี
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
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

<!-- Modal: Tax Option -->
<div class="modal fade" id="taxOptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taxOptionModalTitle">เพิ่มตัวเลือกภาษี</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="taxOptionForm">
                    <input type="hidden" name="option_id" id="tax_option_id">
                    <div class="mb-3">
                        <label class="form-label">ชื่อภาษี <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="option_name" id="tax_option_name">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnSaveTaxOption">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>

<script>
/* ===== Toggle Password Visibility ===== */
function togglePassword(fieldId, spanEl) {
    var input = document.getElementById(fieldId);
    var icon  = spanEl.querySelector('i');
    if (input.type === 'password') {
        // ซ่อนอยู่ → แสดง: ตาเปิด + สีแดง
        input.type = 'text';
        icon.classList.remove('ri-eye-off-line');
        icon.classList.add('ri-eye-line');
        spanEl.style.color = '#ef4444';
    } else {
        // แสดงอยู่ → ซ่อน: ตาปิด + สีเทา
        input.type = 'password';
        icon.classList.remove('ri-eye-line');
        icon.classList.add('ri-eye-off-line');
        spanEl.style.color = '#94a3b8';
    }
}

/* ===== Tab Switch ===== */
function switchTab(panelId, btn) {
    document.querySelectorAll('.s-panel').forEach(function(p) { p.classList.remove('s-active'); });
    document.querySelectorAll('.s-tab-btn').forEach(function(b) { b.classList.remove('s-active'); });
    document.getElementById(panelId).classList.add('s-active');
    btn.classList.add('s-active');
}

/* ===== jQuery ===== */
$(document).ready(function () {

    /* ===== บันทึกข้อมูลส่วนตัว ===== */
    $('#formUpdateProfile').closest('.setting-card').find('button.btn-primary').on('click', function () {
        var $btn = $(this);
        var firstname = $('input[name="user_firstname"]').val().trim();
        var username  = $('input[name="user_name"]').val().trim();

        if (!firstname) {
            Swal.fire('แจ้งเตือน', 'กรุณากรอกชื่อ', 'warning');
            return;
        }
        if (!username) {
            Swal.fire('แจ้งเตือน', 'กรุณากรอกชื่อผู้ใช้งาน', 'warning');
            return;
        }

        $btn.prop('disabled', true).html('<i class="ri-loader-4-line me-1"></i> กำลังบันทึก...');

        $.ajax({
            url: '<?php echo BASE_URL; ?>/backoffice/updateProfile',
            type: 'POST',
            data: new FormData(document.getElementById('formUpdateProfile')),
            processData: false,
            contentType: false,
            success: function (res) {
                try {
                    var r = JSON.parse(res);
                    if (r.status === 'success') {
                        Swal.fire({ title: 'สำเร็จ!', text: r.message, icon: 'success', timer: 1800, showConfirmButton: false });
                    } else {
                        Swal.fire('ข้อผิดพลาด', r.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ข้อผิดพลาด', 'ระบบตอบกลับผิดพลาด', 'error');
                }
            },
            error: function () {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="ri-save-3-line me-1"></i> บันทึกข้อมูลส่วนตัว');
            }
        });
    });

    /* ===== เปลี่ยนรหัสผ่าน ===== */
    $('#formChangePassword').closest('.setting-card').find('button.btn-primary').on('click', function () {
        var $btn      = $(this);
        var newPass   = $('input[name="new_password"]').val();
        var confPass  = $('input[name="confirm_password"]').val();

        if (!newPass) {
            Swal.fire('แจ้งเตือน', 'กรุณากรอกรหัสผ่านใหม่', 'warning');
            return;
        }
        if (newPass.length < 6) {
            Swal.fire('แจ้งเตือน', 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร', 'warning');
            return;
        }
        if (newPass !== confPass) {
            Swal.fire('แจ้งเตือน', 'รหัสผ่านใหม่ไม่ตรงกัน', 'warning');
            return;
        }

        $btn.prop('disabled', true).html('<i class="ri-loader-4-line me-1"></i> กำลังบันทึก...');

        $.ajax({
            url: '<?php echo BASE_URL; ?>/backoffice/changePassword',
            type: 'POST',
            data: {
                new_password:     newPass,
                confirm_password: confPass
            },
            success: function (res) {
                try {
                    var r = JSON.parse(res);
                    if (r.status === 'success') {
                        Swal.fire({ title: 'สำเร็จ!', text: r.message, icon: 'success', timer: 1800, showConfirmButton: false })
                            .then(function () { $('#formChangePassword')[0].reset(); });
                    } else {
                        Swal.fire('ข้อผิดพลาด', r.message, 'error');
                    }
                } catch (e) {
                    Swal.fire('ข้อผิดพลาด', 'ระบบตอบกลับผิดพลาด', 'error');
                }
            },
            error: function () {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="ri-save-3-line me-1"></i> เปลี่ยนรหัสผ่าน');
            }
        });
    });

    $('#btnOpenAddTax').on('click', function () {
        $('#taxOptionForm')[0].reset();
        $('#tax_option_id').val('');
        $('#taxOptionModalTitle').text('เพิ่มตัวเลือกภาษี');
        $('#taxOptionModal').modal('show');
    });


    $('#taxOptionModal').on('hidden.bs.modal', function () {
        $('#taxOptionForm')[0].reset();
        $('#tax_option_id').val('');
        $('#taxOptionModalTitle').text('เพิ่มตัวเลือกภาษี');
    });

    $(document).on('click', '.btn-edit-tax', function () {
        $('#tax_option_id').val($(this).data('id'));
        $('#tax_option_name').val($(this).data('name'));
        $('#tax_list_order').val($(this).data('order'));
        $('#taxOptionModalTitle').text('แก้ไขตัวเลือกภาษี');
        $('#taxOptionModal').modal('show');
    });

    $('#btnSaveTaxOption').on('click', function () {
        if (!$('#tax_option_name').val().trim()) {
            Swal.fire('แจ้งเตือน', 'กรุณากรอกชื่อภาษี', 'warning');
            return;
        }
        $.ajax({
            url: '<?php echo BASE_URL; ?>/backoffice/saveTaxOption',
            type: 'POST',
            data: new FormData(document.getElementById('taxOptionForm')),
            processData: false,
            contentType: false,
            success: function (res) {
                try {
                    var r = JSON.parse(res);
                    if (r.status === 'success') {
                        Swal.fire({ title: 'สำเร็จ!', icon: 'success', timer: 1500, showConfirmButton: false })
                            .then(function () { location.reload(); });
                    } else { Swal.fire('ข้อผิดพลาด', r.message, 'error'); }
                } catch (e) { Swal.fire('ข้อผิดพลาด', 'ระบบตอบกลับผิดพลาด', 'error'); }
            },
            error: function () { Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error'); }
        });
    });

    $(document).on('click', '.btn-delete-tax', function () {
        var id = $(this).data('id');
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: 'คุณต้องการลบตัวเลือกภาษีนี้ใช่หรือไม่!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then(function (result) {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo BASE_URL; ?>/backoffice/deleteTaxOption',
                    type: 'POST',
                    data: { option_id: id },
                    success: function (res) {
                        try {
                            var r = JSON.parse(res);
                            if (r.status === 'success') {
                                Swal.fire({ title: 'ลบสำเร็จ!', icon: 'success', timer: 1500, showConfirmButton: false })
                                    .then(function () { location.reload(); });
                            } else { Swal.fire('ข้อผิดพลาด', r.message, 'error'); }
                        } catch (e) { Swal.fire('ข้อผิดพลาด', 'ระบบตอบกลับผิดพลาด', 'error'); }
                    },
                    error: function () { Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error'); }
                });
            }
        });
    });

});
</script>

<?php require_once dirname(__DIR__) . '/main/footer.php'; ?>