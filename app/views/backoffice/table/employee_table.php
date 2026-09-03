<!-- Employee Table -->
<div class="customer-table-wrap">
    <table class="customer-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">#</th>
                <th class="text-start" style="width: 25%;">ชื่อพนักงาน</th>
                <th class="text-center" style="width: 20%;">ตำแหน่ง</th>
                <th class="text-center" style="width: 20%;">ทีม</th>
                <th class="text-center" style="width: 15%;">สถานะ</th>
                <th class="text-center" style="width: 15%;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($employees)): ?>
                <?php foreach ($employees as $index => $emp): ?>
                    <tr>
                        <td class="text-center" style="color: #64748b; font-weight: 600;">
                            <?php echo $index + 1; ?>
                        </td>
                        <td class="text-start">
                            <div style="font-weight: 700; color: #1e293b;"><?php echo htmlspecialchars($emp['user_firstname'] . ' ' . $emp['user_lastname']); ?></div>
                        </td>
                        <td class="text-center">
                            <div style="color: #475569; font-weight: 500;">
                                <?php echo htmlspecialchars($emp['position'] ?: '-'); ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <div style="color: #475569; font-weight: 500;">
                                <?php echo htmlspecialchars($emp['team_name'] ?: '-'); ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($emp['user_status'] == '1'): ?>
                                <span style="background-color: #dcfce7; color: #16a34a; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                    ยังทำงานอยู่
                                </span>
                            <?php else: ?>
                                <span style="background-color: #f1f5f9; color: #64748b; padding: 4px 12px; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                    เลิกจ้าง
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="action-btn-group" style="display: flex; gap: 8px; justify-content: center;">
                                <button type="button" title="แก้ไข" onclick="edit_employee(<?php echo $emp['user_id']; ?>)" style="width: 32px; height: 32px; border-radius: 6px; border: none; background-color: #f1f5f9; color: #475569; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <button type="button" title="ลบ" onclick="delete_employee(<?php echo $emp['user_id']; ?>, '<?php echo htmlspecialchars($emp['user_firstname']); ?>')" style="width: 32px; height: 32px; border-radius: 6px; border: none; background-color: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center" style="padding: 32px; color: #94a3b8; font-weight: 500;">
                        ยังไม่มีข้อมูลพนักงานในปีนี้
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination Toolbar ด้านล่าง -->
<div class="pagination-toolbar">
    <div class="per-page-wrap">
        <span>แสดง</span>
        <select class="per-page-select">
            <option value="25" selected>25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
        <span>รายการต่อหน้า</span>
    </div>

    <div class="pagination-info">
        รายการที่ 1-2 จาก 2
    </div>

    <div class="pagination-nav">
        <button type="button" class="page-btn" title="หน้าแรก"><i class="ri-arrow-left-double-line"></i></button>
        <button type="button" class="page-btn" title="ก่อนหน้า"><i class="ri-arrow-left-s-line"></i></button>
        <button type="button" class="page-btn active">1</button>
        <button type="button" class="page-btn" title="ถัดไป"><i class="ri-arrow-right-s-line"></i></button>
        <button type="button" class="page-btn" title="หน้าสุดท้าย"><i class="ri-arrow-right-double-line"></i></button>
    </div>
</div>
