<?php

$selected_year = $_GET['year'] ?? '2569';
$company_name  = $_GET['company'] ?? 'TEST ACCOUNTING';
$show_company_workspace = true;

// 1. นำ Header เข้ามา
require_once dirname(__DIR__) . '/main/header.php';

// 2. นำ Sidebar เข้ามา
require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Kanit', "Segoe UI", Tahoma, sans-serif;
    }

    .main-content {
        padding-top: 0px !important;
    }

    .main-page-wrapper {
        padding-top: 0px !important;
        padding: 24px 32px;
        min-height: calc(100vh - 72px);
    }

    /* --- Master Card Wrapper --- */
    .main-card-wrapper {
        background-color: #ffffff;
        border-radius: 16px;
        border: 1px solid #edf2f7;
        box-shadow: 0 2px 12px rgba(16, 24, 40, 0.03);
        padding: 32px;
    }

    /* --- Header Section --- */
    .page-header-box {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 14px;
    }

    .page-title {
        color: #1e293b;
        margin-bottom: 3px;
        letter-spacing: -0.2px;
        font-weight: 600;
    }

    .page-subtitle {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
        margin: 0;
    }

    /* --- Action Button --- */
    .btn-primary-action {
        background-color: #007aff;
        color: #ffffff;
        border: none;
        border-radius: 10px;
        padding: 8px 16px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        font-weight: 500;
    }

    .btn-primary-action i {
        color: #ffffff;
    }

    .btn-primary-action:hover {
        background-color: #0062cc;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0, 122, 255, 0.25);
    }

    /* --- Filter Toolbar --- */
    .filter-container {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 24px;
        width: 100%;
        flex-wrap: wrap;
    }

    .search-box-wrap {
        position: relative;
        flex-grow: 1;
        max-width: 400px;
    }

    .search-box-wrap i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 16px;
    }

    .search-input {
        width: 100%;
        background-color: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 10px 12px 10px 36px;
        font-size: 0.9rem;
        color: #334155;
        font-family: inherit;
        outline: none;
        transition: all 0.2s ease;
    }

    .search-input:focus {
        background-color: #ffffff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .search-input::placeholder {
        color: #94a3b8;
    }

    .filter-select {
        background-color: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        padding: 10px 36px 10px 16px;
        font-size: 0.9rem;
        color: #334155;
        outline: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='16' height='16' fill='%2364748b'%3E%3Cpath d='M11.9997 13.1716L16.9495 8.22168L18.3637 9.63589L11.9997 16L5.63574 9.63589L7.04996 8.22168L11.9997 13.1716Z'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .filter-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* --- Forum Thread Style --- */
    .forum-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .forum-item {
        display: flex;
        align-items: flex-start;
        padding: 20px;
        border-radius: 12px;
        background-color: #ffffff;
        border: 1px solid #edf2f7;
        transition: all 0.2s ease;
        text-decoration: none;
        color: inherit;
    }

    .forum-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        transform: translateY(-1px);
    }

    .forum-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background-color: #f1f5f9;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
        margin-right: 20px;
    }
    
    .forum-icon.urgent {
        background-color: #fef2f2;
        color: #ef4444;
    }

    .forum-icon.pending {
        background-color: #fffbeb;
        color: #f59e0b;
    }

    .forum-icon.completed {
        background-color: #f0fdf4;
        color: #10b981;
    }

    .forum-content {
        flex-grow: 1;
        min-width: 0;
    }

    .forum-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 6px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .forum-desc {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 12px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .forum-meta {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 0.85rem;
        color: #94a3b8;
        flex-wrap: wrap;
    }

    .forum-author {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #475569;
        font-weight: 500;
    }

    .author-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background-color: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        color: #475569;
        font-weight: bold;
    }

    .forum-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .forum-badge.bg-warning-soft { background-color: #fffbeb; color: #b45309; }
    .forum-badge.bg-danger-soft { background-color: #fef2f2; color: #b91c1c; }
    .forum-badge.bg-success-soft { background-color: #f0fdf4; color: #15803d; }
    .forum-badge.bg-primary-soft { background-color: #eff6ff; color: #1d4ed8; }

    .forum-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        justify-content: space-between;
        margin-left: 20px;
        flex-shrink: 0;
        gap: 16px;
    }

    .forum-stats {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #94a3b8;
        font-size: 0.85rem;
    }

    .forum-stats-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .forum-stats-item i {
        font-size: 1.1rem;
    }

    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-icon:hover {
        background-color: #f1f5f9;
        color: #0f172a;
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
                            <h4 class="page-title">ประเด็นคงค้าง</h4>
                            <!-- <p class="page-subtitle">จัดการและติดตามประเด็นปัญหาต่างๆ ของลูกค้า</p> -->
                        </div>
                        <!-- <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-primary-action" onclick="alert('เตรียมเปิดฟอร์มเพิ่มประเด็นคงค้าง');">
                                <i class="ri-add-line"></i>
                                <span>เพิ่มประเด็นใหม่</span>
                            </button>
                        </div> -->
                    </div>

                    <!-- Filter Toolbar -->
                    <div class="filter-container">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" placeholder="ค้นหาประเด็นคงค้าง...">
                        </div>
                        
                        <select class="filter-select">
                            <option value="">สถานะทั้งหมด</option>
                            <option value="pending">รอดำเนินการ</option>
                            <option value="in_progress">กำลังดำเนินการ</option>
                            <option value="completed">แก้ไขแล้ว</option>
                        </select>

                        <select class="filter-select">
                            <option value="">ความสำคัญทั้งหมด</option>
                            <option value="high">ด่วนมาก</option>
                            <option value="medium">ปานกลาง</option>
                            <option value="low">ทั่วไป</option>
                        </select>
                    </div>

                    <!-- Forum List -->
                    <div class="forum-list">
                        <?php if (empty($data['issues'])): ?>
                            <div class="text-center text-muted py-5">
                                <i class="ri-inbox-2-line" style="font-size: 3rem;"></i>
                                <p class="mt-2">ไม่พบประเด็นคงค้างสำหรับปีบัญชีนี้</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($data['issues'] as $issue): 
                                $status = $issue['task_status'] ?? '0'; // '0' = รอดำเนินการ, '1' = เสร็จแล้ว
                                
                                // ตั้งค่ารูปแบบตามสถานะ
                                $isCompleted = ($status === '1');
                                $iconClass = $isCompleted ? 'completed' : 'pending'; // สามารถปรับเป็น urgent ตามเงื่อนไขได้
                                $iconRi = $isCompleted ? 'ri-check-double-line' : 'ri-timer-line';
                                $badgeClass = $isCompleted ? 'bg-success-soft' : 'bg-warning-soft';
                                $badgeText = $isCompleted ? 'แก้ไขแล้ว' : 'รอดำเนินการ';
                                $opacityStyle = $isCompleted ? 'style="opacity: 0.7;"' : '';
                                $titleStyle = $isCompleted ? 'text-decoration-line-through text-muted' : '';
                                
                                // รูปแบบวันที่
                                $createAt = !empty($issue['create_at']) ? date('d/m/Y', strtotime($issue['create_at'])) : '-';
                                
                                // Avatar Name (First 2 chars)
                                $userName = $issue['user_name'] ?: 'ไม่ระบุ';
                                $avatarStr = mb_substr($userName, 0, 2, 'UTF-8');
                                $avatarColor = $isCompleted ? 'bg-info' : 'bg-primary'; // สุ่มสีได้ถ้าต้องการ
                            ?>
                            <div class="forum-item" <?php echo $opacityStyle; ?>>
                                <div class="forum-icon <?php echo $iconClass; ?>">
                                    <i class="<?php echo $iconRi; ?>"></i>
                                </div>
                                <div class="forum-content">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <!-- <span class="forum-badge bg-primary-soft">ปานกลาง</span> -->
                                        <span class="forum-badge <?php echo $badgeClass; ?>"><?php echo $badgeText; ?></span>
                                    </div>
                                    <h5 class="forum-title <?php echo $titleStyle; ?>"><?php echo htmlspecialchars($issue['tasks_name'] ?? 'ไม่มีชื่อประเด็น'); ?></h5>
                                    <p class="forum-desc">
                                        <?php echo nl2br(htmlspecialchars($issue['comment_text'] ?? '-')); ?>
                                    </p>
                                    <div class="forum-meta">
                                        <div class="forum-author">
                                            <div class="author-avatar <?php echo $avatarColor; ?> text-white"><?php echo htmlspecialchars($avatarStr); ?></div>
                                            <span><?php echo htmlspecialchars($userName); ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="ri-time-line"></i> <?php echo $createAt; ?>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <i class="ri-building-4-line"></i> <?php echo htmlspecialchars($issue['customer_name'] ?? '-'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="forum-actions">
                                    <div class="forum-meta text-end d-flex flex-column align-items-end justify-content-center">
                                        <span class="forum-date mb-2"><?php echo $createAt; ?></span>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-issue-comment" data-customer-tasks-id="<?php echo $issue['customer_tasks_id']; ?>" style="background-color: #EBF4FF; color: #007aff; border-radius: 8px; border: none; padding: 4px 12px; font-weight: 500; font-size: 0.8rem;">
                                                <i class="ri-chat-3-line me-1"></i>ตอบกลับ
                                            </button>
                                            <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/backoffice/monthly_task" class="btn btn-sm btn-go-task" style="background-color: #f1f5f9; color: #475569; border-radius: 8px; padding: 4px 12px; font-weight: 500; font-size: 0.8rem; text-decoration: none;">
                                                <i class="ri-external-link-line me-1"></i>ไปที่งาน
                                            </a>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <!-- <button class="btn-icon" title="แก้ไข"><i class="ri-edit-2-line"></i></button> -->
                                        <button class="btn-icon" title="ดูรายละเอียด"><i class="ri-arrow-right-line"></i></button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<style>
/* CSS for comment thread in issues */
.comment-thread-panel {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1rem;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    width: 100%;
}
.comment-day-divider {
    text-align: center; margin: 15px 0; position: relative;
}
.comment-day-divider::before {
    content: ""; position: absolute; left: 0; top: 50%; width: 100%; height: 1px; background: #e2e8f0; z-index: 1;
}
.comment-day-divider span {
    background: #f8fafc; padding: 0 10px; color: #64748b; font-size: 0.75rem; position: relative; z-index: 2;
}
.comment-message { margin-bottom: 12px; display: flex; flex-direction: column; align-items: flex-start; }
.comment-message.is-mine { align-items: flex-end; }
.comment-author { font-size: 0.75rem; color: #64748b; margin-bottom: 4px; padding: 0 4px; }
.comment-bubble {
    background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 8px 12px; max-width: 85%;
    border-top-left-radius: 2px;
}
.comment-message.is-mine .comment-bubble {
    background: #eff6ff; border-color: #bfdbfe; border-top-right-radius: 2px; border-top-left-radius: 12px;
}
.comment-time { font-size: 0.65rem; color: #94a3b8; margin-top: 4px; text-align: right; }
.comment-list-container {
    max-height: 400px; overflow-y: auto; padding-right: 8px; margin-bottom: 15px;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const CURRENT_USER_ID = <?php echo json_encode((string) ($data['user_id'] ?? '')); ?>;
    const BASE_URL = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>';

    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return String(unsafe)
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }

    document.querySelectorAll('.btn-issue-comment').forEach(btn => {
        btn.addEventListener('click', function() {
            const taskId = this.dataset.customerTasksId;
            toggleCommentThread(taskId, this);
        });
    });

    function toggleCommentThread(customerTasksId, triggerElement) {
        const itemContainer = triggerElement.closest('.forum-item');
        let existingPanel = itemContainer.nextElementSibling;

        if (existingPanel && existingPanel.classList.contains('comment-thread-panel')) {
            existingPanel.style.opacity = '0';
            existingPanel.style.transform = 'translateY(-10px)';
            setTimeout(() => existingPanel.remove(), 200);
            return;
        }

        // Remove any other open panels
        document.querySelectorAll('.comment-thread-panel').forEach(el => el.remove());

        const panelHtml = `
            <div class="comment-thread-panel mt-2 mb-3" style="opacity: 0; transform: translateY(-10px); transition: all 0.2s ease;">
                <div class="comment-list-container">
                    <div class="text-center text-muted py-3" style="font-size:0.8rem;"><i class="ri-loader-4-line"></i> กำลังโหลด...</div>
                </div>
                <div class="d-flex gap-2 align-items-center mt-2 border-top pt-3">
                    <input type="text" class="form-control comment-thread-input" placeholder="พิมพ์ความคิดเห็น..." style="border-radius:24px; font-size:0.85rem; border: 1px solid #cbd5e1; padding: 10px 15px;">
                    <button type="button" class="btn btn-primary comment-thread-send" style="width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; padding: 0;">
                        <i class="ri-send-plane-fill"></i>
                    </button>
                </div>
            </div>
        `;
        
        itemContainer.insertAdjacentHTML('afterend', panelHtml);
        const panel = itemContainer.nextElementSibling;
        
        // Trigger animation
        setTimeout(() => {
            panel.style.opacity = '1';
            panel.style.transform = 'translateY(0)';
        }, 10);

        const listEl = panel.querySelector('.comment-list-container');
        const inputEl = panel.querySelector('.comment-thread-input');
        const sendBtn = panel.querySelector('.comment-thread-send');

        loadComments(customerTasksId, listEl);

        function submitComment() {
            const text = inputEl.value.trim();
            if (!text) return;
            sendBtn.disabled = true;

            fetch(BASE_URL + '/monthly_task/comments/store', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    customer_tasks_id: customerTasksId, 
                    comment_text: text,
                    is_reply: 1 
                })
            })
            .then(res => res.json())
            .then(data => {
                inputEl.value = '';
                sendBtn.disabled = false;
                loadComments(customerTasksId, listEl);
            })
            .catch(() => {
                alert('ส่งความคิดเห็นไม่สำเร็จ');
                sendBtn.disabled = false;
            });
        }

        sendBtn.addEventListener('click', submitComment);
        inputEl.addEventListener('keydown', e => {
            if (e.key === 'Enter') { e.preventDefault(); submitComment(); }
        });
        inputEl.focus();
    }

    function loadComments(customerTasksId, listEl) {
        fetch(BASE_URL + '/monthly_task/comments?customer_tasks_id=' + customerTasksId)
            .then(res => res.json())
            .then(data => {
                const comments = data.comments || [];
                if (!comments.length) {
                    listEl.innerHTML = '<div class="text-center py-4 text-muted"><i class="ri-chat-3-line" style="font-size:2rem;opacity:0.5;"></i><br><small>ยังไม่มีข้อความตอบกลับ</small></div>';
                    return;
                }

                let html = '';
                let previousDay = '';

                comments.forEach(c => {
                    const dayKey = String(c.create_at || '').slice(0, 10);
                    const isMine = String(c.comment_user_id) === String(CURRENT_USER_ID);
                    
                    if (dayKey !== previousDay) {
                        html += `<div class="comment-day-divider"><span>${dayKey}</span></div>`;
                        previousDay = dayKey;
                    }

                    const timeStr = String(c.create_at || '').substring(11, 16);

                    html += `
                    <div class="comment-message ${isMine ? 'is-mine' : ''}">
                        <span class="comment-author">${escapeHtml(c.user_name || 'ไม่ระบุ')}</span>
                        <div class="comment-bubble">
                            <div class="comment-body">${escapeHtml(c.comment_text || '').replace(/\\n/g, '<br>')}</div>
                            <div class="comment-time">${timeStr}</div>
                        </div>
                    </div>`;
                });

                listEl.innerHTML = html;
                listEl.scrollTop = listEl.scrollHeight;
            })
            .catch(() => {
                listEl.innerHTML = '<div class="text-center text-danger py-3"><small>โหลดข้อความไม่สำเร็จ</small></div>';
            });
    }
});
</script>

<?php require_once dirname(__DIR__) . '/main/footer.php'; ?>
