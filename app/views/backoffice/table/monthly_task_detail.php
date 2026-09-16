<div class="main-card-wrapper">
    <div class="page-header-box border-bottom pb-3 mb-4">
        <div>
            <h2 class="page-title">อัปเดตงานรายเดือน</h4>
                <div class="page-subtitle mt-1" id="manageDetailSubtitle">
                    <!-- Subtitle will be set dynamically via JavaScript -->
                </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" onclick="hideTaskDetail()"
            style="border-radius: 8px;">
            <i class="ri-arrow-left-line me-1"></i> ย้อนกลับ
        </button>
    </div>

    <div>
        <form id="formManageTask">
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-secondary" style="font-size: 0.95rem;">ระบบราชการ</span>
                    <span id="detailAccountCount" class="text-muted fw-semibold" style="font-size: 0.8rem;">-
                        บัญชี</span>
                </div>
                <div id="detailAccountList" class="row g-3 p-3">
                    <div class="col-12 text-center text-muted py-3" style="font-size: 0.85rem;">กำลังโหลด...</div>
                </div>
            </div>



            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label fw-semibold text-secondary mb-0"
                        style="font-size: 0.85rem;">รายการงานประจำเดือน</label>
                    <span id="detailTaskCount" class="text-muted fw-semibold" style="font-size: 0.8rem;">-งาน</span>
                </div>

                <div id="detailTaskList" class="task-list-container"
                    style="max-height: 400px; overflow-y: auto; padding: 0 16px; border-radius: 10px; border: 1px solid #e2e8f0; background-color: #ffffff;">
                    <!-- Task items will be populated by JS -->
                </div>
            </div>


            <div class="row mb-4">

                <!-- <div class="col-md-6 mt-3 mt-md-0">
                    <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">วันที่ทำเสร็จ</label>
                    <input type="text" class="form-control bg-light border-0 py-2 text-muted flatpickr-date" id="detail_completed_date" placeholder="วัน/เดือน/ปี" style="border-radius: 8px; font-size: 0.9rem;">
                </div> -->
            </div>

            <!-- Row 3 & 4: Reviewer & Status (Two Columns Layout) -->
            <div class="row">
                <!-- Left Column: Reviews -->
                <div class="col-md-6 pe-md-4 border-end">
                    <h6 class="fw-bold mb-3" style="font-size: 0.95rem; color: #334155;">การสอบทาน (Review)</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">ผู้สอบทาน
                            (รีวิว 1)</label>
                        <select class="form-select bg-light border-0 py-2 text-muted fw-semibold detail-search-select"
                            id="detail_review1_user_id" style="border-radius: 8px; font-size: 0.9rem;">
                            <option value="">เลือกผู้สอบทาน</option>
                            <?php foreach (($data['review_users'] ?? []) as $reviewUser): ?>
                                <option value="<?php echo (int) $reviewUser['user_id']; ?>">
                                    <?php echo htmlspecialchars(trim($reviewUser['user_firstname'] . ' ' . $reviewUser['user_lastname'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">ผู้สอบทาน
                            (รีวิว 2)</label>
                        <select class="form-select bg-light border-0 py-2 text-muted fw-semibold detail-search-select"
                            id="detail_review2_user_id" style="border-radius: 8px; font-size: 0.9rem;">
                            <option value="">เลือกผู้สอบทาน</option>
                            <?php foreach (($data['review_users'] ?? []) as $reviewUser): ?>
                                <option value="<?php echo (int) $reviewUser['user_id']; ?>">
                                    <?php echo htmlspecialchars(trim($reviewUser['user_firstname'] . ' ' . $reviewUser['user_lastname'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.85rem;">ผู้สอบทาน
                            (รีวิว 3)</label>
                        <select class="form-select bg-light border-0 py-2 text-muted fw-semibold detail-search-select"
                            id="detail_review3_user_id" style="border-radius: 8px; font-size: 0.9rem;">
                            <option value="">เลือกผู้สอบทาน</option>
                            <?php foreach (($data['review_users'] ?? []) as $reviewUser): ?>
                                <option value="<?php echo (int) $reviewUser['user_id']; ?>">
                                    <?php echo htmlspecialchars(trim($reviewUser['user_firstname'] . ' ' . $reviewUser['user_lastname'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Right Column: Status & Dates -->
                <div class="col-md-6 ps-md-4 mt-4 mt-md-0">
                    <h6 class="fw-bold mb-3" style="font-size: 0.95rem; color: white;">สถานะเพิ่มเติม</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary"
                            style="font-size: 0.85rem;">วันที่ได้รับเอกสาร</label>
                        <input type="text" class="form-control bg-light border-0 py-2 text-muted flatpickr-date"
                            id="detail_doc_date" placeholder="วัน/เดือน/ปี"
                            style="border-radius: 8px; font-size: 0.9rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary"
                            style="font-size: 0.85rem;">สถานะการเก็บเงิน</label>
                        <select class="form-select bg-light border-0 py-2 text-muted fw-semibold detail-search-select"
                            id="detail_payment_status" style="border-radius: 8px; font-size: 0.9rem;">
                            <option value="0" selected>ยังไม่ได้รับ</option>
                            <option value="1">ได้รับเงินแล้ว</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary"
                            style="font-size: 0.85rem;">สถานะการยื่นภาษี</label>
                        <select class="form-select bg-light border-0 py-2 text-muted fw-semibold detail-search-select"
                            id="detail_tax_status" style="border-radius: 8px; font-size: 0.9rem;">
                            <option value="0" selected>ยังไม่ได้ยื่นภาษี</option>
                            <option value="1">ยื่นภาษีแล้ว</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Row 5: Tax Rounds -->
            <div class="row mt-4 pt-3 border-top">
                <div class="col-md-6 pe-md-4 border-end">
                    <h6 class="fw-bold mb-3" style="font-size: 0.95rem; color: #334155;">ข้อมูลรอบที่ 1</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary"
                            style="font-size: 0.85rem;">วันที่ยื่นภาษีรอบที่ 1</label>
                        <input type="text"
                            class="form-control bg-light border-0 py-2 text-muted fw-semibold flatpickr-date"
                            id="detail_tax_date_1" placeholder="วัน/เดือน/ปี"
                            style="border-radius: 8px; font-size: 0.9rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary"
                            style="font-size: 0.85rem;">วันที่เสร็จรอบที่ 1</label>
                        <input type="text"
                            class="form-control bg-light border-0 py-2 text-muted fw-semibold flatpickr-date"
                            id="detail_completed_date_1" placeholder="วัน/เดือน/ปี"
                            style="border-radius: 8px; font-size: 0.9rem;">
                    </div>
                </div>

                <div class="col-md-6 ps-md-4 mt-4 mt-md-0">
                    <h6 class="fw-bold mb-3" style="font-size: 0.95rem; color: #334155;">ข้อมูลรอบที่ 2</h6>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary"
                            style="font-size: 0.85rem;">วันที่ยื่นภาษีรอบที่ 2</label>
                        <input type="text"
                            class="form-control bg-light border-0 py-2 text-muted fw-semibold flatpickr-date"
                            id="detail_tax_date_2" placeholder="วัน/เดือน/ปี"
                            style="border-radius: 8px; font-size: 0.9rem;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary"
                            style="font-size: 0.85rem;">วันที่เสร็จรอบที่ 2</label>
                        <input type="text"
                            class="form-control bg-light border-0 py-2 text-muted fw-semibold flatpickr-date"
                            id="detail_completed_date_2" placeholder="วัน/เดือน/ปี"
                            style="border-radius: 8px; font-size: 0.9rem;">
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-4 border-top">
        <button type="button" class="btn btn-light px-4 fw-semibold" onclick="hideTaskDetail()"
            style="border-radius: 8px; color: #475569; background-color: #f8fafc;">ยกเลิก</button>
        <button type="button" class="btn btn-primary px-4 fw-semibold" onclick="saveTaskDetail()"
            style="border-radius: 8px; background-color: #2563eb; border-color: #2563eb; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);">บันทึกข้อมูล</button>
    </div>
</div>