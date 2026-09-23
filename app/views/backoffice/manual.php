<?php
    // 1. นำ Header เข้ามา
    require_once dirname(__DIR__) . '/main/header.php';

    // 2. นำ Sidebar เข้ามา
    require_once dirname(__DIR__) . '/main/sidebar.php';

    // ==================== FETCH DATA FROM DATABASE ====================
    require_once dirname(__DIR__, 2) . '/models/ManualModel.php';
    $manualModel = new ManualModel();
    $topics = $manualModel->getAllTopicsWithContents();
    // ==================== END FETCH DATA ====================
?>

<style>
.settings-wrapper { display:flex; gap:24px; align-items:flex-start; }
.settings-sidebar { width:320px; flex-shrink:0; }
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
.setting-card-title { font-size:1rem; font-weight:700; color:#1e293b; margin-bottom:20px; padding-bottom:12px; border-bottom:2px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; }
.setting-card-title i { color:#3b82f6; margin-right:6px; }

.manual-item { border:1px solid #f1f5f9; border-radius:10px; padding:16px 18px; margin-bottom:14px; }
.manual-item:last-child { margin-bottom:0; }
.manual-item .m-title { font-weight:700; color:#1e293b; font-size:0.92rem; margin-bottom:8px; display:flex; align-items:center; gap:8px; }
.manual-item .m-title i { color:#3b82f6; }
.manual-item .m-desc { color:#475569; font-size:0.85rem; line-height:1.7; white-space:pre-line; }
.manual-item .m-image { margin-top:10px; }
.manual-item .m-image img { max-width:100%; border-radius:8px; border:1px solid #f1f5f9; }
.manual-empty { text-align:center; color:#94a3b8; padding:50px 20px; }
.mockup-badge {
    display:inline-block; background:#fef3c7; color:#92400e; font-size:0.7rem;
    font-weight:700; padding:3px 10px; border-radius:20px; margin-left:10px;
}
.content-title {
    font-size: 1.4rem;
    font-weight: bold;
    color: #1e293b;
    border-left: 6px solid #1e3a8a;
    padding-left: 12px;
    margin-bottom: 20px;
    margin-top: 40px;
}
.content-title:first-child {
    margin-top: 10px;
}
.content-html {
    font-size: 1rem;
    color: #334155;
    line-height: 1.6;
}
.search-input-custom {
    padding-left: 45px !important;
    border-radius: 30px !important;
    border: 1px solid #cbd5e1 !important;
    background-color: #f8fafc !important;
    height: 42px !important;
    font-size: 0.95rem !important;
}
</style>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">

                    <div class="page-header-box d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom">
                        <div>
                            <h3 class="mb-1 fw-bold" style="color: #333;">
                                คู่มือการใช้งาน
                             
                            </h3>
                            <p class="text-muted small mb-0">
                                <i class="ri-book-2-line me-1"></i> หัวข้อและรายละเอียดคู่มือการใช้งานระบบ (ข้อมูลตัวอย่าง)
                            </p>
                        </div>
                    </div>

                    <div class="settings-wrapper">

                        <!-- Sidebar Nav: Topic (ซ้าย) -->
                        <div class="settings-sidebar" style="background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.07); border: 1px solid #e2e8f0;">
                            <div class="mb-4 position-relative">
                                <i class="ri-search-line position-absolute" style="top: 50%; transform: translateY(-50%); left: 18px; color: #64748b; font-size: 1.1rem; pointer-events: none;"></i>
                                <input type="text" id="topicSearch" class="form-control shadow-none search-input-custom" placeholder="ค้นหาหัวข้อ..." onkeyup="filterTopics()">
                            </div>
                            <div id="topicListContainer">
                                <?php foreach ($topics as $index => $topic): ?>
                                    <button class="s-tab-btn <?= $index === 0 ? 's-active' : '' ?>"
                                            data-title="<?= strtolower(htmlspecialchars($topic['topics_name'])) ?>"
                                            onclick="switchTab('panel-topic-<?= (int) $topic['topic_id'] ?>', this)">
                                        <i class="ri-folder-3-line"></i>
                                        <span><?= htmlspecialchars($topic['topics_name']) ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Content (ขวา) -->
                        <div class="settings-content">
                            <?php if (empty($topics)): ?>
                                <div class="setting-card" style="border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.07); text-align: center; padding: 60px 20px;">
                                    <i class="ri-book-3-line" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px; display: block;"></i>
                                    <h5 style="color: #64748b; font-weight: 600;">ยังไม่มีคู่มือการใช้งานในระบบ</h5>
                                </div>
                            <?php else: ?>
                                <?php foreach ($topics as $index => $topic): ?>
                                    <div class="s-panel <?= $index === 0 ? 's-active' : '' ?>" id="panel-topic-<?= (int) $topic['topic_id'] ?>">

                                        <div class="setting-card" style="border: 1px solid #e2e8f0; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,0.07);">
                                            <h5 class="setting-card-title">
                                                <span>
                                                    <i class="ri-folder-3-line"></i>
                                                    <?= htmlspecialchars($topic['topics_name']) ?>
                                                </span>
                                                
                                            </h5>

                                            <?php if (empty($topic['contents'])): ?>
                                                <div class="manual-empty">
                                                    <i class="ri-information-line" style="font-size:1.5rem; display:block; margin-bottom:8px;"></i>
                                                    ยังไม่มีเนื้อหาในหมวดนี้
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($topic['contents'] as $content): ?>
                                                    <div class="mb-5 pb-5" style="border-bottom: 2px dashed #cbd5e1;">
                                                        <h3 class="content-title"><?= htmlspecialchars($content['title_name']) ?></h3>
                                                        <div class="content-html">
                                                            <?= $content['description'] ?>
                                                        </div>
                                                        
                                                        <?php if (!empty($content['content_image'])): ?>
                                                            <div class="mt-4 text-center">
                                                                <?php
                                                                    require_once dirname(__DIR__, 2) . '/services/AwsS3.php';
                                                                    $s3_url = \App\Services\AwsS3::getFileUrl($content['content_image']);
                                                                ?>
                                                                <img src="<?= htmlspecialchars($s3_url ?: $content['content_image']) ?>" alt="Image" style="max-width: 100%; border-radius: 8px;">
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
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

<script>
function filterTopics() {
    let input = document.getElementById('topicSearch').value.toLowerCase();
    let buttons = document.querySelectorAll('#topicListContainer .s-tab-btn');
    
    buttons.forEach(function(btn) {
        let title = btn.getAttribute('data-title');
        if (title.includes(input)) {
            btn.style.display = 'flex';
        } else {
            btn.style.display = 'none';
        }
    });
}

function switchTab(panelId, btn) {
    document.querySelectorAll('.s-panel').forEach(function(p) { p.classList.remove('s-active'); });
    document.querySelectorAll('.s-tab-btn').forEach(function(b) { b.classList.remove('s-active'); });
    document.getElementById(panelId).classList.add('s-active');
    btn.classList.add('s-active');
}
</script>

<?php require_once dirname(__DIR__) . '/main/footer.php'; ?>