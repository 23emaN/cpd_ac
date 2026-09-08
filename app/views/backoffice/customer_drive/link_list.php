<?php
// app/views/backoffice/customer_drive/link_list.php
// Ported from _export_customer_drive/files/main/ajax/customer_drive/link_list.php
// $customer_id/$userId/$isSuperAdmin set by CustomerDriveController::linkList().
// Gated by controller-level is_super_admin check before this view is required.

$customer = cd_customer($customer_id);

if (!$customer) {
    echo '<div class="cd-modal"><div class="modal-header"><h5 class="modal-title">ลิงก์แชร์</h5>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body"><div class="alert alert-warning mb-0">ไม่พบข้อมูลลูกค้า</div></div></div>';
    exit;
}

if (!$isSuperAdmin) {
    echo '<div class="cd-modal"><div class="modal-header"><h5 class="modal-title">ลิงก์แชร์</h5>'
        . '<button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>'
        . '<div class="modal-body"><div class="alert alert-warning mb-0">'
        . 'คุณไม่มีสิทธิ์จัดการลิงก์แชร์<br>'
        . '<small>สิทธิ์นี้เปิดทางให้คนนอกเข้าถึงเอกสารลูกค้าได้ จึงเปิดให้เฉพาะผู้ดูแลระบบเท่านั้น</small>'
        . '</div></div></div>';
    exit;
}

$links = cd_query(
    "SELECT * FROM tbl_cd_link WHERE customer_id = ? ORDER BY revoked, create_datetime DESC",
    [$customer_id]
)->fetchAll();

$folders = cd_flatten($customer_id);
?>
<div class="cd-modal">
<div class="modal-header">
    <div class="d-flex align-items-start gap-2">
        <i class="cd-ic ri-link"></i>
        <div>
            <h5 class="modal-title">ลิงก์แชร์</h5>
            <span class="cd-modal-sub"><?php echo cd_e($customer['customer_name']); ?></span>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
</div>

<div class="modal-body">

    <!-- ฟอร์มสร้างลิงก์ใหม่ (ซ่อนไว้จนกว่าจะกดสร้าง) -->
    <div class="cd-linkform d-none mb-4">
        <h6 class="mb-3">สร้างลิงก์ใหม่</h6>

        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-medium">ชื่อเรื่อง</label>
                <input type="text" class="form-control" id="lk-title" maxlength="200"
                    placeholder="เช่น ขอเอกสารปิดงบปี <?php echo (int) date('Y') + 543; ?>">
            </div>

            <div class="col-12">
                <label class="form-label fw-medium">ข้อความถึงลูกค้า <span class="text-muted fw-normal">(ไม่บังคับ)</span></label>
                <textarea class="form-control" id="lk-message" rows="2" maxlength="2000"
                    placeholder="ข้อความนี้ลูกค้าจะเห็นบนหน้าอัปโหลด"></textarea>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-medium">โหมด</label>
                <select class="form-select" id="lk-mode">
                    <option value="collect">รับไฟล์อย่างเดียว — ลูกค้าไม่เห็นของที่มีอยู่</option>
                    <option value="share">ให้ดู/ดาวน์โหลดด้วย</option>
                </select>
                <div class="form-text">โหมดรับไฟล์อย่างเดียวคือกล่องรับเอกสาร ลูกค้าเห็นเฉพาะที่ตัวเองส่งมาในรอบนั้น</div>

                <div class="form-check mt-2 d-none" id="lk-uprow">
                    <input class="form-check-input" type="checkbox" id="lk-upload">
                    <label class="form-check-label fs-13" for="lk-upload">
                        ให้ลูกค้าส่งไฟล์กลับเข้าโฟลเดอร์นี้ได้ด้วย
                    </label>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-medium">โฟลเดอร์ปลายทาง</label>
                <select class="form-select" id="lk-root">
                    <option value="">คลังไฟล์ (ชั้นบนสุด)</option>
                    <?php foreach ($folders as $f): ?>
                        <?php if ($f['kind'] !== 'folder') continue; ?>
                        <option value="<?php echo (int) $f['node_id']; ?>">
                            <?php echo str_repeat('— ', (int) $f['depth']) . cd_e($f['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label fw-medium">รหัสผ่าน</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="lk-password"
                        maxlength="<?php echo CD_LINK_PASSWORD_LEN; ?>"
                        minlength="<?php echo CD_LINK_PASSWORD_LEN; ?>" autocomplete="off"
                        placeholder="<?php echo CD_LINK_PASSWORD_LEN; ?> ตัวอักษรพอดี">
                    <button type="button" class="btn btn-outline-secondary" data-cd="lk-random">สุ่ม</button>
                </div>
                <div class="form-text">แสดงได้ครั้งเดียวตอนสร้าง เก็บเป็นค่าเข้ารหัสจึงเอาคืนไม่ได้</div>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-medium">อายุลิงก์</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="lk-days" min="1" max="365"
                        value="<?php echo CD_LINK_DEFAULT_DAYS; ?>">
                    <span class="input-group-text">วัน</span>
                </div>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-medium">เพดานไฟล์</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="lk-max" min="1" max="10000"
                        value="<?php echo CD_LINK_DEFAULT_MAX_UPLOADS; ?>">
                    <span class="input-group-text">ไฟล์</span>
                </div>
            </div>

            <div class="col-12 cd-formactions">
                <button type="button" class="cd-btn cd-btn-ghost" data-cd="lk-cancel">ยกเลิก</button>
                <button type="button" class="cd-btn cd-btn-main" data-cd="lk-save">
                    <i class="cd-ic ri-link-m" aria-hidden="true"></i>
                    <span class="cd-btn-label">สร้างลิงก์</span>
                </button>
            </div>
        </div>

        <hr class="my-4">
    </div>

    <!-- ผลลัพธ์หลังสร้าง — แสดงครั้งเดียว -->
    <div class="cd-linkresult d-none mb-4"></div>

    <div class="d-flex justify-content-between align-items-center mb-3 cd-linkhead">
        <span class="text-muted fs-13"><?php echo count($links); ?> ลิงก์</span>
        <button type="button" class="btn btn-primary btn-sm d-flex align-items-center gap-1" data-cd="lk-new">
            <i class="cd-ic fs-18 ri-link-m"></i> สร้างลิงก์ใหม่
        </button>
    </div>

    <?php if (!$links): ?>
        <div class="cd-empty cd-linklist">
            <i class="cd-ic cd-empty-icon ri-link-unlink-m"></i>
            <div class="cd-empty-title">ยังไม่เคยสร้างลิงก์ให้ลูกค้ารายนี้</div>
        </div>
    <?php else: ?>
        <div class="cd-linklist">
            <?php foreach ($links as $link):
                $state = cd_link_state($link);
                $flag  = (int) $link['fail_count'] >= CD_LINK_FAIL_FLAG;

                $badge = match ($state) {
                    ''           => ['bg-success', 'ใช้งานได้'],
                    'revoked'    => ['bg-secondary', 'ถูกเพิกถอน'],
                    'expired'    => ['bg-secondary', 'หมดอายุ'],
                    'locked'     => ['bg-warning text-dark', 'ถูกล็อกชั่วคราว'],
                    'scope_gone' => ['bg-danger', 'โฟลเดอร์ปลายทางถูกลบ'],
                    default      => ['bg-secondary', $state],
                };
            ?>
                <?php
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
                <div class="cd-link<?php echo $dead ? ' cd-link-dead' : ''; ?>" data-link-id="<?php echo (int) $link['link_id']; ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                        <div class="min-w-0">
                            <div class="cd-link-title"><?php echo cd_e($link['title']); ?></div>
                        </div>

                        <span class="badge <?php echo $badge[0]; ?>"><?php echo cd_e($badge[1]); ?></span>
                    </div>

                    <dl class="cd-link-meta">
                        <div>
                            <dt>โหมด</dt>
                            <dd><?php echo cd_e($modeText); ?></dd>
                        </div>
                        <div>
                            <dt>ปลายทาง</dt>
                            <dd><?php echo cd_e($rootName); ?></dd>
                        </div>
                        <?php if ((string) $link['allow_upload'] === '1'): ?>
                            <div>
                                <dt>ส่งเข้ามาแล้ว</dt>
                                <dd><?php echo (int) $link['used_uploads']; ?> / <?php echo (int) $link['max_uploads']; ?> ไฟล์</dd>
                            </div>
                        <?php endif; ?>
                        <?php if ($link['expires_datetime']): ?>
                            <div>
                                <dt>หมดอายุ</dt>
                                <dd><?php echo cd_e(cd_time($link['expires_datetime'])); ?></dd>
                            </div>
                        <?php endif; ?>
                        <div>
                            <dt>เปิดล่าสุด</dt>
                            <dd><?php echo $link['last_open_datetime'] ? cd_e(cd_time($link['last_open_datetime'])) : 'ยังไม่เคยเปิด'; ?></dd>
                        </div>
                        <div>
                            <dt>สร้างโดย</dt>
                            <dd><?php echo cd_e(cd_user_name((int) $link['create_by'])); ?> · <?php echo cd_e(cd_time($link['create_datetime'])); ?></dd>
                        </div>
                    </dl>

                    <?php if ($flag): ?>
                        <div class="alert alert-danger fs-13 mt-2 mb-0 d-flex gap-2 align-items-start">
                            <i class="cd-ic fs-18 ri-error-warning-line"></i>
                            <div>
                                <strong>มีคนพยายามเดารหัสผ่าน <?php echo (int) $link['fail_count']; ?> ครั้ง</strong><br>
                                ระบบไม่เพิกถอนให้อัตโนมัติ เพราะถ้าทำแบบนั้นใครก็ปิดลิงก์ของลูกค้าได้ด้วยการเดาผิด —
                                ถ้าไม่มั่นใจให้กด "เพิกถอน" แล้วสร้างลิงก์ใหม่
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex gap-2 flex-wrap mt-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-cd="lk-copy"
                            data-url="<?php echo cd_e(cd_portal_url((string) $link['token'])); ?>">คัดลอกลิงก์</button>
                        <?php if ($state !== 'revoked'): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary" data-cd="lk-reset">ตั้งรหัสใหม่</button>
                            <button type="button" class="btn btn-sm btn-outline-danger" data-cd="lk-revoke">เพิกถอน</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
</div>
</div>
