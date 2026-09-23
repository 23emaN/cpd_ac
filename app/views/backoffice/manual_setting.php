<?php
    // 1. นำ Header เข้ามา
    require_once dirname(__DIR__) . '/main/header.php';

    // 2. นำ Sidebar เข้ามา
    require_once dirname(__DIR__) . '/main/sidebar.php';

    // ดึงข้อมูล topics จาก controller
    $topics = $data['topics'] ?? [];
?>
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
.manual-wrapper {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}

.manual-sidebar {
    width: 250px;
    flex-shrink: 0;
}
.manual-sidebar-card {
    background: #fff;
    border-radius: 14px;
    padding: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.07);
}
.manual-sidebar-title {
    font-size: 0.82rem;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 12px;
    padding: 0 4px;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.topic-btn {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    text-align: left;
    padding: 11px 13px;
    border: none;
    border-radius: 9px;
    background: transparent;
    color: #475569;
    font-weight: 500;
    font-size: 0.88rem;
    cursor: pointer;
    margin-bottom: 5px;
    transition: all .2s ease;
}
.topic-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
}
.topic-btn.active {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    color: #1d4ed8;
    font-weight: 600;
}
.topic-btn i {
    font-size: 1.1rem;
    flex-shrink: 0;
}
.topic-add-btn {
    width: 100%;
    margin-top: 10px;
    border: 1px dashed #cbd5e1;
    background: #f8fafc;
    color: #64748b;
    border-radius: 9px;
    padding: 10px;
    font-size: .85rem;
    font-weight: 600;
    transition: all .2s;
}
.topic-add-btn:hover {
    background: #eff6ff;
    border-color: #93c5fd;
    color: #2563eb;
}
.manual-content {
    flex: 1;
    min-width: 0;
}
.topic-panel {
    display: none;
}
.topic-panel.active {
    display: block;
}
.manual-card {
    background: #fff;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.07);
}
.manual-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding-bottom: 16px;
    margin-bottom: 18px;
    border-bottom: 2px solid #f1f5f9;
}
.manual-card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.manual-card-title i {
    color: #3b82f6;
    font-size: 1.2rem;
}
.manual-content-item {
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    padding: 17px 18px;
    margin-bottom: 14px;
    transition: all .2s ease;
}
.manual-content-item:hover {
    border-color: #bfdbfe;
    box-shadow: 0 3px 12px rgba(59,130,246,.06);
}
.manual-content-item:last-child {
    margin-bottom: 0;
}
.content-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 15px;
}
.content-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    color: #1e293b;
    font-size: .93rem;
}
.content-title i {
    color: #3b82f6;
}
.content-actions {
    display: flex;
    gap: 5px;
    flex-shrink: 0;
}
.content-actions button {
    width: 31px;
    height: 31px;
    border: none;
    border-radius: 7px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all .2s;
}
.btn-edit-content {
    background: #eff6ff;
    color: #2563eb;
}
.btn-edit-content:hover {
    background: #dbeafe;
}
.btn-delete-content {
    background: #fef2f2;
    color: #dc2626;
}
.btn-delete-content:hover {
    background: #fee2e2;
}
.content-detail {
    margin-top: 10px;
    color: #64748b;
    font-size: .86rem;
    line-height: 1.75;
    white-space: pre-line;
    padding-left: 26px;
}
.content-image {
    margin-top: 12px;
    padding-left: 26px;
}
.content-image img {
    max-width: 300px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.manual-empty {
    text-align: center;
    color: #94a3b8;
    padding: 55px 20px;
}
.manual-empty i {
    font-size: 2rem;
    display: block;
    margin-bottom: 10px;
}
.manual-modal .modal-content {
    border: none;
    border-radius: 14px;
    box-shadow: 0 15px 45px rgba(15,23,42,.15);
}
.manual-modal .modal-header {
    border-bottom: 1px solid #f1f5f9;
    padding: 18px 22px;
}
.manual-modal .modal-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
}
.manual-modal .modal-body {
    padding: 22px;
}
.manual-modal .modal-footer {
    border-top: 1px solid #f1f5f9;
    padding: 15px 22px;
}
.form-label-custom {
    font-size: .84rem;
    font-weight: 600;
    color: #334155;
    margin-bottom: 7px;
}
.form-control-custom {
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: .88rem;
    padding: 10px 12px;
}
.form-control-custom:focus {
    border-color: #60a5fa;
    box-shadow: 0 0 0 3px rgba(59,130,246,.1);
}
.detail-textarea {
    min-height: 180px;
    resize: vertical;
}
@media (max-width: 768px) {
    .manual-wrapper {
        flex-direction: column;
    }

    .manual-sidebar {
        width: 100%;
    }

    .manual-sidebar-card {
        padding: 12px;
    }

    .topic-btn {
        display: inline-flex;
        width: auto;
        margin-right: 5px;
    }

    .manual-card-header {
        align-items: flex-start;
        flex-direction: column;
    }
}

/* ===== Accordion ===== */
.accordion-list { display: flex; flex-direction: column; gap: 0; }
.acc-item { border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 8px; overflow: hidden; }
.acc-item:last-child { margin-bottom: 0; }
.acc-title {
    display: flex; align-items: center; justify-content: space-between;
    padding: 13px 16px; cursor: pointer; background: #f8fafc;
    transition: background .18s;
    user-select: none;
}
.acc-title:hover { background: #f1f5f9; }
.acc-title.open { background: #eff6ff; }
.acc-title-left { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 0; }
.acc-arrow { font-size: 1.15rem; color: #94a3b8; transition: transform .2s; flex-shrink: 0; }
.acc-title.open .acc-arrow { transform: rotate(90deg); color: #3b82f6; }
.acc-num { font-size: .82rem; font-weight: 700; color: #94a3b8; flex-shrink: 0; }
.acc-name { font-weight: 600; color: #1e293b; font-size: .92rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.acc-body {
    display: none;
    padding: 16px 18px 18px 42px;
    border-top: 1px solid #e2e8f0;
    background: #fff;
}
.acc-body.open { display: block; }
.acc-desc.ql-snow { border: none !important; }
.acc-desc .ql-editor { padding: 0; font-size: .88rem; line-height: 1.75; color: #475569; min-height: unset; }
.acc-desc .ql-editor p { margin-bottom: .4rem; }

/* ===== Inline Form ===== */
.inline-form {
    margin-top: 16px;
    margin-bottom: 30px;
    border: 1.5px dashed #a5b4fc;
    border-radius: 12px;
    padding: 20px 20px 16px;
    background: #f8faff;
    animation: fadeSlideDown .2s ease;
}
@keyframes fadeSlideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.inline-form-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.inline-form-title-label {
    font-weight: 600;
    font-size: .9rem;
    color: #4f46e5;
    display: flex;
    align-items: center;
    gap: 4px;
}
.inline-form-close {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #94a3b8;
    cursor: pointer;
    line-height: 1;
    padding: 0 4px;
    transition: color .15s;
}
.inline-form-close:hover { color: #ef4444; }
.inline-form-footer {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
}
/* Quill inside inline form */
.ifc-editor-slot .ql-container { border-radius: 0 0 8px 8px; }
.ifc-editor-slot .ql-toolbar { border-radius: 8px 8px 0 0; background: #f8fafc; }

.search-input-custom {
    padding-left: 42px !important;
    border-radius: 30px !important;
    border: 1px solid #cbd5e1 !important;
    background-color: #f8fafc !important;
    height: 38px !important;
    font-size: 0.9rem !important;
}
</style>
<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">
                    <div class="page-header-box d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div>
                            <h3 class="mb-1 fw-bold" style="color:#333;">
                                เขียนคู่มือ
                            </h3>
                            <p class="text-muted small mb-0">
                                <i class="ri-edit-2-line me-1"></i>
                                จัดการหัวข้อและเนื้อหาคู่มือการใช้งานระบบ
                            </p>
                        </div>
                    </div>
                    <div class="manual-wrapper">
                        <div class="manual-sidebar">
                            <div class="manual-sidebar-card">
                                <div class="manual-sidebar-title">
                                    <i class="ri-folder-3-line me-1"></i>
                                    Topic
                                </div>
                                <div id="sortable-topics">
                                    <?php foreach ($topics as $index => $topic): ?>
                                        <button type="button" class="topic-btn <?php echo $index === 0 ? 'active' : '' ?>" onclick="switchTopic('topic-panel-<?php echo (int) $topic['topic_id'] ?>',this)" data-id="<?php echo (int) $topic['topic_id'] ?>">
                                            <i class="ri-folder-3-line"></i>
                                            <span><?php echo htmlspecialchars($topic['topics_name']) ?></span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                                <button type="button" class="topic-add-btn mt-2" onclick="openAddTopicModal()">
                                    <i class="ri-add-line me-1"></i>
                                    เพิ่ม Topic
                                </button>
                            </div>
                        </div>
                        <div class="manual-content">
                            <?php if (empty($topics)): ?>
                                <div class="manual-card" style="text-align: center; padding: 60px 20px; border-radius: 14px; border: 1px solid #e2e8f0; background: #fff;">
                                    <i class="ri-folder-add-line" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px; display: inline-block;"></i>
                                    <h4 style="color: #64748b; font-weight: 600;">ยังไม่มี Topic คู่มือ</h4>
                                    <p class="text-muted mt-2">กรุณาคลิกปุ่ม "เพิ่ม Topic" ด้านซ้ายมือเพื่อเริ่มต้นสร้างคู่มือ</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($topics as $index => $topic): ?>
                                    <div class="topic-panel <?php echo $index === 0 ? 'active' : '' ?>" id="topic-panel-<?php echo (int) $topic['topic_id'] ?>">
                                        <div class="manual-card">
                                            <div class="manual-card-header">
                                                <h5 class="manual-card-title">
                                                    <i class="ri-folder-3-line"></i>
                                                    <?php echo htmlspecialchars($topic['topics_name']) ?>
                                                </h5>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="openAddContentModal(<?php echo (int) $topic['topic_id'] ?>,'<?php echo htmlspecialchars($topic['topics_name'], ENT_QUOTES) ?>')">
                                                    <i class="ri-add-line"></i>
                                                    เพิ่มเนื้อหา
                                                </button>
                                            </div>

                                        <?php if (empty($topic['contents'])): ?>
                                            <div class="inline-form inline-form-empty" id="inline-form-<?php echo (int) $topic['topic_id'] ?>">
                                                <div class="inline-form-header">
                                                    <span class="inline-form-title-label"><i class="ri-file-add-line me-1"></i><span class="ifl-text">เพิ่มเนื้อหา</span></span>
                                                </div>
                                                <input type="hidden" class="ifc-id" value="">
                                                <input type="hidden" class="ifc-topic-id" value="<?php echo (int) $topic['topic_id'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label-custom">Title <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-custom ifc-title" placeholder="กรอกชื่อหัวข้อ">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label-custom">รายละเอียด <span class="text-danger">*</span></label>
                                                    <div class="ifc-editor-slot"></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label-custom">รูปภาพประกอบ</label>
                                                    <input type="file" class="form-control form-control-custom ifc-image" accept="image/*">
                                                    <div class="form-text">รองรับ JPG, PNG, WEBP</div>
                                                    <img class="ifc-image-preview mt-2" src="" alt="Image Preview" style="max-width: 200px; display: none; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                </div>
                                                <div class="inline-form-footer">
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="saveInlineContent(<?php echo (int) $topic['topic_id'] ?>)">
                                                        <i class="ri-save-line me-1"></i>บันทึก
                                                    </button>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="sortable-contents">
                                                <?php foreach ($topic['contents'] as $ci => $content): ?>
                                                    <div class="content-item mb-4 position-relative" data-id="<?php echo (int) $content['content_id']; ?>" style="border-bottom: 1px dashed #e2e8f0; padding-bottom: 20px;">

                                                        <div class="acc-actions position-absolute" style="top: 0; right: 0; z-index: 10;">
                                                            <button type="button" class="btn btn-sm btn-outline-primary me-1 bg-white"
                                                                    onclick="openEditContentModal(<?php echo (int) $content['content_id']; ?>, <?php echo (int) $topic['topic_id']; ?>, '<?php echo htmlspecialchars($content['title_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($content['description'], ENT_QUOTES); ?>')">
                                                                <i class="ri-edit-line"></i> แก้ไข
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger bg-white"
                                                                    onclick="deleteInlineContent(<?php echo (int) $content['content_id']; ?>)">
                                                                <i class="ri-delete-bin-line"></i> ลบ
                                                            </button>
                                                            <i class="ri-draggable ms-2 text-muted" style="cursor: move; font-size: 1.2rem; vertical-align: middle;" title="ลากเพื่อเปลี่ยนลำดับ"></i>
                                                        </div>

                                                        <div class="content-body pe-5">
                                                            <h3 class="mb-3 fw-bold" style="color: #1e293b;">
                                                                <?php echo htmlspecialchars($content['title_name']); ?>
                                                            </h3>

                                                            <div class="manual-html-content" style="font-size: 1rem; color: #334155;">
                                                                <?php echo $content['description']; ?>
                                                            </div>

                                                            <?php if (! empty($content['content_image'])): ?>
                                                            <div class="mt-3 text-center">
                                                                <?php
                                                                    require_once dirname(__DIR__, 2) . '/services/AwsS3.php';
                                                                    $s3_url = \App\Services\AwsS3::getFileUrl($content['content_image']);
                                                                ?>
                                                                <img src="<?php echo htmlspecialchars($s3_url ?: $content['content_image']); ?>" alt="Image" style="max-width: 100%; border-radius: 8px;">
                                                            </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>

                                        <div class="inline-form" id="inline-form-<?php echo (int) $topic['topic_id'] ?>" style="display:none;">
                                                <div class="inline-form-header">
                                                    <span class="inline-form-title-label"><i class="ri-file-add-line me-1"></i><span class="ifl-text">เพิ่มเนื้อหา</span></span>
                                                    <button type="button" class="inline-form-close" onclick="closeInlineForm(<?php echo (int) $topic['topic_id'] ?>)"><i class="ri-close-line"></i></button>
                                                </div>
                                                <input type="hidden" class="ifc-id" value="">
                                                <input type="hidden" class="ifc-topic-id" value="<?php echo (int) $topic['topic_id'] ?>">
                                                <div class="mb-3">
                                                    <label class="form-label-custom">Title <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control form-control-custom ifc-title" placeholder="กรอกชื่อหัวข้อ">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label-custom">รายละเอียด <span class="text-danger">*</span></label>
                                                    <div class="ifc-editor-slot"></div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label-custom">รูปภาพประกอบ</label>
                                                    <input type="file" class="form-control form-control-custom ifc-image" accept="image/*">
                                                    <div class="form-text">รองรับ JPG, PNG, WEBP</div>
                                                    <img class="ifc-image-preview mt-2" src="" alt="Image Preview" style="max-width: 200px; display: none; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                </div>
                                                <div class="inline-form-footer">
                                                    <button type="button" class="btn btn-light btn-sm" onclick="closeInlineForm(<?php echo (int) $topic['topic_id'] ?>)">ยกเลิก</button>
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="saveInlineContent(<?php echo (int) $topic['topic_id'] ?>)">
                                                    <i class="ri-save-line me-1"></i>บันทึก
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Inline add/edit form (hidden by default) -->
                                        <div class="inline-form" id="inline-form-<?php echo (int) $topic['topic_id'] ?>" style="display:none;">
                                            <div class="inline-form-header">
                                                <span class="inline-form-title-label"><i class="ri-file-add-line me-1"></i><span class="ifl-text">เพิ่มเนื้อหา</span></span>
                                                <button type="button" class="inline-form-close" onclick="closeInlineForm(<?php echo (int) $topic['topic_id'] ?>)"><i class="ri-close-line"></i></button>
                                            </div>
                                            <input type="hidden" class="ifc-id" value="">
                                            <input type="hidden" class="ifc-topic-id" value="<?php echo (int) $topic['topic_id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label-custom">Title <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-custom ifc-title" placeholder="กรอกชื่อหัวข้อ">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label-custom">รายละเอียด <span class="text-danger">*</span></label>
                                                <div class="ifc-editor-slot"></div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label-custom">รูปภาพประกอบ</label>
                                                <input type="file" class="form-control form-control-custom ifc-image" accept="image/*">
                                                <div class="form-text">รองรับ JPG, PNG, WEBP</div>
                                                <img class="ifc-image-preview mt-2" src="" alt="Image Preview" style="max-width: 200px; display: none; border-radius: 8px; border: 1px solid #e2e8f0;">
                                            </div>
                                            <div class="inline-form-footer">
                                                <button type="button" class="btn btn-light btn-sm" onclick="closeInlineForm(<?php echo (int) $topic['topic_id'] ?>)">ยกเลิก</button>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="saveInlineContent(<?php echo (int) $topic['topic_id'] ?>)">
                                                    <i class="ri-save-line me-1"></i>บันทึก
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Quill editor (shared, moved by JS) -->
<div id="quill-shared-wrap" style="display:none;">
    <div id="quill_editor"></div>
</div>
<div class="modal fade manual-modal" id="topicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="topicForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="ri-folder-add-line me-1 text-primary"></i>
                    เพิ่ม Topic
                </h5>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label-custom">
                    ชื่อ Topic
                    <span class="text-danger">*</span>
                </label>
                <input
                    type="text"
                    name="topic_name"
                    id="topic_name"
                    class="form-control form-control-custom"
                    placeholder="เช่น การตั้งค่าระบบ"
                >
            </div>
            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                    ยกเลิก
                </button>
                <button type="button"
                        class="btn btn-primary"
                        onclick="saveTopic()">
                    <i class="ri-save-line me-1"></i>
                    บันทึก
                </button>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>

let contentModal;
let topicModal;
let quill;          // Quill shared instance
let activeFormId;   // topic_id ของ form ที่เปิดอยู่

document.addEventListener('DOMContentLoaded', function () {
    topicModal = new bootstrap.Modal(document.getElementById('topicModal'));

    quill = new Quill('#quill_editor', {
        theme: 'snow',
        placeholder: 'กรอกรายละเอียด...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    // ✅ mount quill ให้ topic panel ที่ active อยู่ ถ้ามันเป็น empty-state form
    mountEmptyFormsInPanel(document.querySelector('.topic-panel.active'));
});

function switchTopic(panelId, btn) {
    document.querySelectorAll('.topic-panel').forEach(function (panel) {
        panel.classList.remove('active');
    });
    document.querySelectorAll('.topic-btn').forEach(function (button) {
        button.classList.remove('active');
    });
    const panel = document.getElementById(panelId);
    if (panel) {
        panel.classList.add('active');
        mountEmptyFormsInPanel(panel); // ✅ ย้าย quill ไปให้ panel ใหม่ ถ้าเป็น empty-state
    }
    if (btn) {
        btn.classList.add('active');
    }
}

// ✅ ฟังก์ชันใหม่: ถ้า panel นี้มี inline-form ที่เป็น empty-state ให้ mount quill ให้ทันที
function mountEmptyFormsInPanel(panel) {
    if (!panel) return;
    const emptyForm = panel.querySelector('.inline-form-empty');
    if (!emptyForm) return;

    const topicId = parseInt(emptyForm.querySelector('.ifc-topic-id').value, 10);
    activeFormId = topicId;
    emptyForm.querySelector('.ifc-id').value = '';
    emptyForm.querySelector('.ifc-title').value = '';
    emptyForm.querySelector('.ifc-image').value = '';
    _mountQuill(topicId);
    quill.setContents([]);
}
function switchTopic(panelId, btn) {
    document.querySelectorAll('.topic-panel').forEach(function (panel) {
        panel.classList.remove('active');
    });
    document.querySelectorAll('.topic-btn').forEach(function (button) {

        button.classList.remove('active');
    });
    const panel = document.getElementById(panelId);
    if (panel) {
        panel.classList.add('active');
    }
    if (btn) {
        btn.classList.add('active');
    }
}
/* ===== Accordion Toggle ===== */
function toggleAcc(titleEl) {
    const body = titleEl.nextElementSibling;
    const isOpen = titleEl.classList.contains('open');
    titleEl.classList.toggle('open', !isOpen);
    body.classList.toggle('open', !isOpen);
}

/* ===== Inline Form Helpers ===== */
function _getForm(topicId) {
    return document.getElementById('inline-form-' + topicId);
}

function _mountQuill(topicId) {
    const slot = _getForm(topicId).querySelector('.ifc-editor-slot');
    const wrapEl = document.getElementById('quill-shared-wrap');
    if (!slot.contains(wrapEl)) {
        slot.appendChild(wrapEl);
        wrapEl.style.display = 'block';
    }
    const editorEl = document.getElementById('quill_editor');
    if(editorEl) editorEl.style.height = '260px';
}

function openAddContentModal(topicId, topicName) {
    if (activeFormId && activeFormId !== topicId) {
        closeInlineForm(activeFormId);
    } else if (activeFormId === topicId) {
        // If already open in edit mode, ensure we show the hidden item
        document.querySelectorAll('#topic-panel-' + topicId + ' .content-item').forEach(el => el.style.display = '');
    }

    activeFormId = topicId;
    const form = _getForm(topicId);

    // Move form back to the bottom of the sortable contents or panel
    const topicPanel = document.getElementById('topic-panel-' + topicId);
    const sortableContainer = topicPanel.querySelector('.sortable-contents');
    if (sortableContainer) {
        sortableContainer.parentElement.insertBefore(form, sortableContainer.nextSibling);
    }

    form.querySelector('.ifc-id').value = '';
    form.querySelector('.ifc-title').value = '';
    form.querySelector('.ifc-image').value = '';
    form.querySelector('.ifl-text').textContent = 'เพิ่มเนื้อหา';
    _mountQuill(topicId);
    quill.setContents([]);
    form.style.display = 'block';
    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function openEditContentModal(contentId, topicId, title, detail) {
    if (activeFormId && activeFormId !== topicId) {
        closeInlineForm(activeFormId);
    } else if (activeFormId === topicId) {
        // Restore previously hidden item in the same topic
        document.querySelectorAll('#topic-panel-' + topicId + ' .content-item').forEach(el => el.style.display = '');
    }

    activeFormId = topicId;
    const form = _getForm(topicId);

    // Hide the content item being edited
    const contentItem = document.querySelector('.content-item[data-id="' + contentId + '"]');
    if (contentItem) {
        contentItem.style.display = 'none';
        // Move the form to be right after the content item
        contentItem.parentElement.insertBefore(form, contentItem.nextSibling);
    }

    form.querySelector('.ifc-id').value = contentId;
    form.querySelector('.ifc-title').value = title;
    form.querySelector('.ifc-image').value = '';
    form.querySelector('.ifl-text').textContent = 'แก้ไขเนื้อหา';
    _mountQuill(topicId);
    quill.root.innerHTML = (typeof detail === 'string') ? detail : '';
    form.style.display = 'block';

    // Smooth scroll is not really needed if it's in place, but good to have
    form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function closeInlineForm(topicId) {
    const form = _getForm(topicId);
    if (form) {
        if (!form.classList.contains('inline-form-empty')) {
            form.style.display = 'none';
        }
        // Move form back to the bottom of the panel
        const topicPanel = document.getElementById('topic-panel-' + topicId);
        if (topicPanel) {
            const sortableContainer = topicPanel.querySelector('.sortable-contents');
            if (sortableContainer) {
                sortableContainer.parentElement.insertBefore(form, sortableContainer.nextSibling);
            }
        }
    }

    // Show all hidden content items
    document.querySelectorAll('.content-item').forEach(el => el.style.display = '');

    // Move quill wrapper back
    const wrapEl = document.getElementById('quill-shared-wrap');
    document.body.appendChild(wrapEl);
    wrapEl.style.display = 'none';

    activeFormId = null;
}

function saveInlineContent(topicId) {
    const form = _getForm(topicId);
    const title  = form.querySelector('.ifc-title').value.trim();
    const detail = quill ? quill.root.innerHTML.trim() : '';
    const isEmpty = !detail || detail === '<p><br></p>';

    if (!title) {
        alert('กรุณากรอก Title');
        form.querySelector('.ifc-title').focus();
        return;
    }
    if (isEmpty) {
        alert('กรุณากรอกรายละเอียด');
        return;
    }

    const contentId = form.querySelector('.ifc-id').value;
    const fileInput = form.querySelector('.ifc-image');
    const formData = new FormData();
    formData.append('content_id', contentId);
    formData.append('topic_id', topicId);
    formData.append('title', title);
    formData.append('description', detail);
    if (fileInput.files.length > 0) {
        formData.append('image', fileInput.files[0]);
    }

    $.ajax({
        url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/save_manual_content',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (response) {
            if (response.result === 1) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.msg }).then(() => {
                        location.reload();
                    });
                } else {
                    alert(response.msg);
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: response.msg });
                } else {
                    alert(response.msg);
                }
            }
        },
        error: function (err) {
            console.error(err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
            } else {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            }
        }
    });
}
function deleteContent(contentId) {
    if (!confirm('ต้องการลบคู่มือนี้ใช่หรือไม่?')) {
        return;
    }

    $.ajax({
        url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/delete_manual_content',
        type: 'POST',
        data: { content_id: contentId },
        dataType: 'json',
        success: function (response) {
            if (response.result === 1) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'success', title: 'สำเร็จ', text: response.msg }).then(() => {
                        location.reload();
                    });
                } else {
                    alert(response.msg);
                    location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: response.msg });
                } else {
                    alert(response.msg);
                }
            }
        },
        error: function (err) {
            console.error(err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
            } else {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            }
        }
    });
}
function openAddTopicModal() {
    document.getElementById('topic_name').value = '';
    topicModal.show();
}

function saveTopic() {
    const form = document.getElementById('topicForm');
    const formData = new FormData(form);
    const topicName = formData.get('topic_name').trim();

    if (!topicName) {
        swal('แจ้งเตือน', 'กรุณากรอกชื่อ Topic', 'warning');
        return;
    }

    $.ajax({
        url: '<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/save_manual_topic',
        type: 'POST',
        data: { topic_name: topicName },
        dataType: 'json',
        success: function (response) {
            if (response.result === 1) {
                const panelId = 'topic-panel-' + response.id;

                // เพิ่มปุ่ม Topic ใหม่ใน Sidebar
                const addBtn = document.querySelector('.topic-add-btn');
                const newBtn = document.createElement('button');
                newBtn.type = 'button';
                newBtn.className = 'topic-btn';
                newBtn.setAttribute('onclick', `switchTopic('${panelId}', this)`);
                newBtn.innerHTML = `<i class="ri-folder-3-line"></i><span>${response.topics_name}</span>`;
                addBtn.parentNode.insertBefore(newBtn, addBtn);

                // สร้าง panel เนื้อหาฝั่งขวา
                const contentArea = document.querySelector('.manual-content');
                const newPanel = document.createElement('div');
                newPanel.className = 'topic-panel';
                newPanel.id = panelId;
                newPanel.innerHTML = `
                    <div class="manual-card">
                        <div class="manual-card-header">
                            <h5 class="manual-card-title">
                                <i class="ri-folder-3-line"></i>
                                ${response.topics_name}
                            </h5>
                            <button type="button" class="btn btn-primary btn-sm"
                                onclick="openAddContentModal(${response.id}, '${response.topics_name.replace(/'/g, "\\'")}')"
                            >
                                <i class="ri-add-line"></i> เพิ่มคู่มือ
                            </button>
                        </div>
                        <div class="manual-empty">
                            <i class="ri-file-edit-line"></i>
                            <div>ยังไม่มีคู่มือในหัวข้อนี้</div>
                            <button type="button" class="btn btn-outline-primary btn-sm mt-3"
                                onclick="openAddContentModal(${response.id}, '${response.topics_name.replace(/'/g, "\\'")}')"
                            >
                                <i class="ri-add-line"></i> เพิ่มคู่มือแรก
                            </button>
                        </div>
                    </div>`;
                contentArea.appendChild(newPanel);

                switchTopic(panelId, newBtn);

                const modalEl = document.getElementById('topicModal');
                const modalInst = bootstrap.Modal.getInstance(modalEl);
                if (modalInst) modalInst.hide();

                document.getElementById('topic_name').value = '';
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: response.msg || 'ไม่สามารถบันทึก Topic ได้' });
                } else {
                    alert(response.msg || 'ไม่สามารถบันทึก Topic ได้');
                }
            }
        },
        error: function (err) {
            console.error(err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์' });
            } else {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            }
        }
    });
}
// Image Preview Logic
$(document).on('change', '.ifc-image', function() {
    const file = this.files[0];
    const preview = $(this).siblings('.ifc-image-preview');
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.attr('src', e.target.result).show();
        };
        reader.readAsDataURL(file);
    } else {
        preview.hide().attr('src', '');
    }
});

// Sortable Topics
document.addEventListener('DOMContentLoaded', function () {
    const topicList = document.getElementById('sortable-topics');
    if (topicList && typeof Sortable !== 'undefined') {
        Sortable.create(topicList, {
            animation: 150,
            onEnd: function (evt) {
                const topicBtns = topicList.querySelectorAll('.topic-btn');
                const topicIds = Array.from(topicBtns).map(btn => btn.getAttribute('data-id'));

                $.post('<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/update_manual_topic_order', { topic_ids: topicIds }, function(res) {
                    if (res.result !== 1) {
                        console.error('Failed to update topic order', res.msg);
                    }
                }, 'json');
            }
        });
    }

    // Sortable Contents
    const contentLists = document.querySelectorAll('.sortable-contents');
    contentLists.forEach(function (list) {
        if (typeof Sortable !== 'undefined') {
            Sortable.create(list, {
                animation: 150,
                handle: '.ri-draggable', // Use the drag icon as handle
                onEnd: function (evt) {
                    const contentItems = list.querySelectorAll('.content-item');
                    const contentIds = Array.from(contentItems).map(item => item.getAttribute('data-id'));

                    $.post('<?php echo BASE_URL ?? "/cpd_ac/public"; ?>/update_manual_content_order', { content_ids: contentIds }, function(res) {
                        if (res.result !== 1) {
                            console.error('Failed to update content order', res.msg);
                        }
                    }, 'json');
                }
            });
        }
    });
});
</script>

<?php
    require_once dirname(__DIR__) . '/main/footer.php';
?>
