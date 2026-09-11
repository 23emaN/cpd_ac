<?php
// app/views/backoffice/customer_drive/link_list.php
// Ported from _export_customer_drive/files/main/ajax/customer_drive/link_list.php
// $customer_id/$userId/$isSuperAdmin set by CustomerDriveController::linkList().

$customer = cd_customer($customer_id);

if (!$customer): ?>
    <div class="modal-header modal-header-custom">
        <h5 class="modal-title modal-title-custom">ลิงก์แชร์</h5>
        <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="ปิด"></button>
    </div>
    <div class="modal-body modal-body-custom">
        <div class="alert alert-warning mb-0">ไม่พบข้อมูลลูกค้า</div>
    </div>
    <div class="modal-footer modal-footer-custom">
        <button type="button" class="btn modal-btn-cancel" data-bs-dismiss="modal">ยกเลิก</button>
    </div>
<?php exit; endif; ?>

<?php if (!$isSuperAdmin): ?>
    <div class="modal-header modal-header-custom">
        <h5 class="modal-title modal-title-custom">ลิงก์แชร์</h5>
        <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="ปิด"></button>
    </div>
    <div class="modal-body modal-body-custom">
        <div class="alert alert-warning mb-0">
            คุณไม่มีสิทธิ์จัดการลิงก์แชร์<br>
            <small>สิทธิ์นี้เปิดทางให้คนนอกเข้าถึงเอกสารลูกค้าได้ จึงเปิดให้เฉพาะผู้ดูแลระบบเท่านั้น</small>
        </div>
    </div>
    <div class="modal-footer modal-footer-custom">
        <button type="button" class="btn modal-btn-cancel" data-bs-dismiss="modal">ยกเลิก</button>
    </div>
<?php exit; endif; ?>

<?php
$links = cd_query(
    "SELECT * FROM tbl_cd_link WHERE customer_id = ? ORDER BY revoked, create_datetime DESC",
    [$customer_id]
)->fetchAll();

$folders = cd_flatten($customer_id);
?>

<div class="modal-header modal-header-custom">
    <div class="d-flex align-items-center gap-3">
        <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 44px; height: 44px; background-color: #eff6ff; color: #007aff; flex-shrink: 0; font-size: 22px;">
            <i class="cd-ic ri-link-m"></i>
        </div>
        <div>
            <h5 class="modal-title modal-title-custom">ลิงก์แชร์</h5>
            <span class="cd-modal-sub" style="font-size: 0.82rem; color: #64748b; font-weight: 500;"><?php echo cd_e($customer['customer_name']); ?></span>
        </div>
    </div>
    <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="ปิด"></button>
</div>

<div class="modal-body modal-body-custom">

    <!-- ฟอร์มสร้างลิงก์ใหม่ (ซ่อนไว้จนกว่าจะกดสร้าง) -->
    <!-- ฟอร์มสร้างลิงก์ใหม่ (ปรับให้เรียบเนียน ไม่ทำตัวเป็นกรอบซ้อน) -->
    <div class="cd-linkform d-none mb-4 pb-4 border-bottom">
        <!-- <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <i class="ri-add-circle-line text-primary fs-18"></i>
            <h6 class="modal-section-title mb-0" style="font-size: 1rem; font-weight: 700; color: #1e293b;">สร้างลิงก์แชร์ใหม่</h6>
        </div> -->

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label modal-form-label fw-bold" style="color: #334155;">ชื่อเรื่อง <span class="text-danger">*</span></label>
                <input type="text" class="form-control modal-form-control" id="lk-title" maxlength="200"
                    placeholder="เช่น ขอเอกสารปิดงบปี <?php echo (int) date('Y') + 543; ?>">
            </div>

            <div class="col-12">
                <label class="form-label modal-form-label fw-bold" style="color: #334155;">ข้อความถึงลูกค้า <span class="text-muted fw-normal" style="font-size: 0.82rem;">(ไม่บังคับ)</span></label>
                <textarea class="form-control modal-form-control" id="lk-message" rows="2" maxlength="2000"
                    placeholder="ข้อความนี้ลูกค้าจะเห็นบนหน้าอัปโหลด"></textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label modal-form-label fw-bold" style="color: #334155;">โหมดการใช้งาน</label>
                <select class="form-select modal-form-select" id="lk-mode">
                    <option value="collect">รับไฟล์อย่างเดียว — ลูกค้าไม่เห็นของที่มีอยู่</option>
                    <option value="share">ให้ดู/ดาวน์โหลดด้วย</option>
                </select>
                <div class="form-text" style="font-size: 0.78rem; color: #64748b; margin-top: 6px;">โหมดรับไฟล์อย่างเดียวคือกล่องรับเอกสาร ลูกค้าเห็นเฉพาะที่ตัวเองส่งมาในรอบนั้น</div>

                <div class="form-check mt-3 d-none" id="lk-uprow">
                    <input class="form-check-input" type="checkbox" id="lk-upload" style="cursor: pointer;">
                    <label class="form-check-label fw-semibold" for="lk-upload" style="font-size: 0.85rem; color: #475569; cursor: pointer;">
                        ให้ลูกค้าส่งไฟล์กลับเข้าโฟลเดอร์นี้ได้ด้วย
                    </label>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label modal-form-label fw-bold" style="color: #334155;">โฟลเดอร์ปลายทาง</label>
                <select class="form-select modal-select" id="lk-root">
                    <option value="">คลังไฟล์ (ชั้นบนสุด)</option>
                    <?php foreach ($folders as $f): ?>
                        <?php if ($f['kind'] !== 'folder') continue; ?>
                        <option value="<?php echo (int) $f['node_id']; ?>">
                            <?php echo str_repeat('— ', (int) $f['depth']) . cd_e($f['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- แถวรหัสผ่าน / อายุลิงก์ / เพดานไฟล์ -->
            <div class="col-md-6">
                <label class="form-label modal-form-label fw-bold" style="color: #334155;">รหัสผ่านเข้าใช้งาน <span class="text-danger">*</span></label>
                <div class="d-flex align-items-stretch">
                    <input type="text" class="form-control modal-form-control flex-grow-1" id="lk-password"
                        maxlength="<?php echo CD_LINK_PASSWORD_LEN; ?>"
                        minlength="<?php echo CD_LINK_PASSWORD_LEN; ?>" autocomplete="off"
                        style="border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;"
                        placeholder="<?php echo CD_LINK_PASSWORD_LEN; ?> ตัวอักษรพอดี">
                    <button type="button" class="btn btn-action-edit d-inline-flex align-items-center justify-content-center px-3" data-cd="lk-random"
                        style="width: auto; height: auto; min-height: 44px; border-radius: 0 12px 12px 0; border-left: none; font-weight: 600; font-size: 0.88rem; flex-shrink: 0;"
                        title="สุ่มรหัสผ่านใหม่">
                        <i class="ri-refresh-line me-1"></i> สุ่ม
                    </button>
                </div>
                <div class="form-text" style="font-size: 0.78rem; color: #64748b; margin-top: 6px;">แสดงได้ครั้งเดียวตอนสร้าง เก็บเป็นค่าเข้ารหัสจึงเอาคืนไม่ได้</div>
            </div>

            <div class="col-md-3 col-6">
                <label class="form-label modal-form-label fw-bold" style="color: #334155;">อายุลิงก์</label>
                <div class="d-flex align-items-stretch">
                    <input type="number" class="form-control modal-form-control flex-grow-1" id="lk-days" min="1" max="365"
                        style="border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;"
                        value="<?php echo CD_LINK_DEFAULT_DAYS; ?>">
                    <span class="d-inline-flex align-items-center px-3"
                        style="background-color: #f1f5f9; border: 1px solid #e2e8f0; border-left: none; border-radius: 0 12px 12px 0; font-size: 0.85rem; color: #64748b; font-weight: 600; flex-shrink: 0;">วัน</span>
                </div>
            </div>

            <div class="col-md-3 col-6">
                <label class="form-label modal-form-label fw-bold" style="color: #334155;">เพดานไฟล์</label>
                <div class="d-flex align-items-stretch">
                    <input type="number" class="form-control modal-form-control flex-grow-1" id="lk-max" min="1" max="10000"
                        style="border-top-right-radius: 0 !important; border-bottom-right-radius: 0 !important;"
                        value="<?php echo CD_LINK_DEFAULT_MAX_UPLOADS; ?>">
                    <span class="d-inline-flex align-items-center px-3"
                        style="background-color: #f1f5f9; border: 1px solid #e2e8f0; border-left: none; border-radius: 0 12px 12px 0; font-size: 0.85rem; color: #64748b; font-weight: 600; flex-shrink: 0;">ไฟล์</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ผลลัพธ์หลังสร้าง — แสดงครั้งเดียว -->
    <div class="cd-linkresult d-none mb-4"></div>

    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom cd-linkhead">
        <div class="d-flex align-items-center gap-2">
            <span class="badge-info"><?php echo count($links); ?> ลิงก์ที่สร้างไว้</span>
        </div>
        <button type="button" class="btn btn-add-action" data-cd="lk-new">
            <i class="cd-ic ri-add-line fs-16"></i> <span>สร้างลิงก์ใหม่</span>
        </button>
    </div>

    <?php if (!$links): ?>
        <div class="cd-empty cd-linklist text-center py-5" style="border: 2px dashed #e2e8f0; border-radius: 16px; background-color: #f8fafc;">
            <i class="cd-ic cd-empty-icon ri-link-unlink-m" style="font-size: 42px; color: #cbd5e1;"></i>
            <div class="cd-empty-title mt-2 fw-bold" style="color: #475569; font-size: 1rem;">ยังไม่เคยสร้างลิงก์ให้ลูกค้ารายนี้</div>
            <div class="text-muted" style="font-size: 0.82rem;">กดปุ่ม "สร้างลิงก์ใหม่" ด้านบนเพื่อส่งเอกสารหรือรับไฟล์จากลูกค้า</div>
        </div>
    <?php else: ?>
        <div class="cd-linklist d-flex flex-column gap-3">
            <?php foreach ($links as $link):
                $state = cd_link_state($link);
                $flag  = (int) $link['fail_count'] >= CD_LINK_FAIL_FLAG;

                $badgeClass = match ($state) {
                    ''           => 'badge-active',
                    'revoked'    => 'badge-inactive',
                    'expired'    => 'badge-inactive',
                    'locked'     => 'badge-warning',
                    'scope_gone' => 'badge bg-danger',
                    default      => 'badge-inactive',
                };

                $badgeLabel = match ($state) {
                    ''           => 'ใช้งานได้',
                    'revoked'    => 'ถูกเพิกถอน',
                    'expired'    => 'หมดอายุ',
                    'locked'     => 'ถูกล็อกชั่วคราว',
                    'scope_gone' => 'โฟลเดอร์ปลายทางถูกลบ',
                    default      => $state,
                };

                $rootName = 'คลังไฟล์ (ชั้นบนสุด)';
                if ($link['root_node_id'] !== null) {
                    $r = cd_find_node($customer_id, (int) $link['root_node_id'], true);
                    $rootName = $r ? $r['name'] : '(โฟลเดอร์ถูกลบ)';
                }

                $modeText = $link['mode'] === 'share'
                    ? ('ให้ดู/ดาวน์โหลด' . ((string) $link['allow_upload'] === '1' ? ' + ส่งกลับได้' : ' (อ่านอย่างเดียว)'))
                    : 'รับไฟล์อย่างเดียว';

                $dead = $state !== '' && $state !== 'locked';
            ?>
                <div class="cd-link<?php echo $dead ? ' cd-link-dead' : ''; ?>" data-link-id="<?php echo (int) $link['link_id']; ?>"
                    style="border: 1px solid #edf2f7; border-radius: 14px; padding: 18px 20px; background-color: #ffffff; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02); transition: all 0.2s ease;">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap mb-2">
                        <div class="min-w-0 d-flex align-items-center gap-2">
                            <i class="ri-links-line text-primary fs-18"></i>
                            <div class="cd-link-title fw-bold text-dark" style="font-size: 0.98rem;"><?php echo cd_e($link['title']); ?></div>
                        </div>

                        <span class="<?php echo $badgeClass; ?>"><?php echo cd_e($badgeLabel); ?></span>
                    </div>

                    <dl class="cd-link-meta d-flex flex-wrap gap-3 mb-0" style="font-size: 0.82rem;">
                        <div class="d-flex flex-column">
                            <dt style="color: #94a3b8; font-weight: 600; font-size: 0.75rem;">โหมด</dt>
                            <dd class="mb-0 fw-semibold" style="color: #334155;"><?php echo cd_e($modeText); ?></dd>
                        </div>
                        <div class="d-flex flex-column">
                            <dt style="color: #94a3b8; font-weight: 600; font-size: 0.75rem;">ปลายทาง</dt>
                            <dd class="mb-0 fw-semibold" style="color: #334155;"><?php echo cd_e($rootName); ?></dd>
                        </div>
                        <?php if ((string) $link['allow_upload'] === '1'): ?>
                            <div class="d-flex flex-column">
                                <dt style="color: #94a3b8; font-weight: 600; font-size: 0.75rem;">ส่งเข้ามาแล้ว</dt>
                                <dd class="mb-0 fw-semibold" style="color: #334155;"><?php echo (int) $link['used_uploads']; ?> / <?php echo (int) $link['max_uploads']; ?> ไฟล์</dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($link['expires_datetime']): ?>
                            <div class="d-flex flex-column">
                                <dt style="color: #94a3b8; font-weight: 600; font-size: 0.75rem;">หมดอายุ</dt>
                                <dd class="mb-0 fw-semibold" style="color: #334155;"><?php echo cd_e(cd_time($link['expires_datetime'])); ?></dd>
                            </div>
                        <?php endif; ?>
                        <div class="d-flex flex-column">
                            <dt style="color: #94a3b8; font-weight: 600; font-size: 0.75rem;">เปิดล่าสุด</dt>
                            <dd class="mb-0 fw-semibold" style="color: #334155;"><?php echo $link['last_open_datetime'] ? cd_e(cd_time($link['last_open_datetime'])) : 'ยังไม่เคยเปิด'; ?></dd>
                        </div>
                        <div class="d-flex flex-column">
                            <dt style="color: #94a3b8; font-weight: 600; font-size: 0.75rem;">สร้างโดย</dt>
                            <dd class="mb-0 fw-semibold" style="color: #334155;"><?php echo cd_e(cd_user_name((int) $link['create_by'])); ?> · <?php echo cd_e(cd_time($link['create_datetime'])); ?></dd>
                        </div>
                    </dl>

                    <?php if ($flag): ?>
                        <div class="alert alert-danger fs-13 mt-3 mb-0 d-flex gap-2 align-items-start" style="border-radius: 10px;">
                            <i class="cd-ic fs-18 ri-error-warning-line"></i>
                            <div>
                                <strong>มีคนพยายามเดารหัสผ่าน <?php echo (int) $link['fail_count']; ?> ครั้ง</strong><br>
                                ระบบไม่เพิกถอนให้อัตโนมัติ เพราะถ้าทำแบบนั้นใครก็ปิดลิงก์ของลูกค้าได้ด้วยการเดาผิด —
                                ถ้าไม่มั่นใจให้กด "เพิกถอน" แล้วสร้างลิงก์ใหม่
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2 flex-wrap mt-3 pt-3 border-top">
                        <button type="button" class="btn btn-excel-action" data-cd="lk-copy"
                            data-url="<?php echo cd_e(cd_portal_url((string) $link['token'])); ?>">
                            <i class="ri-file-copy-line"></i> คัดลอกลิงก์
                        </button>
                        <?php if ($state !== 'revoked'): ?>
                            <button type="button" class="btn btn-action-edit" style="width: auto; padding: 6px 14px; font-weight: 600; font-size: 0.80rem;" data-cd="lk-reset">
                                <i class="ri-key-2-line me-1"></i> ตั้งรหัสใหม่
                            </button>
                            <button type="button" class="btn btn-action-delete" style="width: auto; padding: 6px 14px; font-weight: 600; font-size: 0.80rem;" data-cd="lk-revoke">
                                <i class="ri-close-circle-line me-1"></i> เพิกถอน
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal-footer modal-footer-custom">
    <button type="button" class="btn modal-btn-cancel" data-bs-dismiss="modal">ยกเลิก</button>
</div>