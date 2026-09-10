<?php
/**
 * Pagination กลาง
 * รูปแบบ: << < 1 > >>
 *
 * ต้องกำหนดตัวแปรก่อน include:
 * $page, $per_page, $total
 */

$__per   = max(1, (int) ($per_page ?? 10));
$__total = (int) ($total ?? 0);
$__pages = max(1, (int) ceil($__total / $__per));
$__page  = max(1, min((int) ($page ?? 1), $__pages));

$__from = $__total > 0 ? ($__page - 1) * $__per + 1 : 0;
$__to   = min($__page * $__per, $__total);
?>

<div class="d-flex justify-content-between align-items-center px-1 py-3 flex-wrap gap-2">

    <span class="text-secondary">
        แสดง <?php echo number_format($__from) ?>-<?php echo number_format($__to) ?>
        จาก <?php echo number_format($__total) ?> รายการ
    </span>

    <nav aria-label="pagination">
        <ul class="pagination mb-0">

            <!-- หน้าแรก << -->
            <li class="page-item <?php echo $__page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="javascript:void(0);"
                   onclick="GetData(1)"
                   aria-label="หน้าแรก">
                    &laquo;
                </a>
            </li>

            <!-- ก่อนหน้า < -->
            <li class="page-item <?php echo $__page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="javascript:void(0);"
                   onclick="GetData(<?php echo max(1, $__page - 1) ?>)"
                   aria-label="ก่อนหน้า">
                    &lsaquo;
                </a>
            </li>

            <!-- หน้าปัจจุบัน -->
            <li class="page-item active">
                <a class="page-link"
                   href="javascript:void(0);">
                    <?php echo $__page ?>
                </a>
            </li>

            <!-- ถัดไป > -->
            <li class="page-item <?php echo $__page >= $__pages ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="javascript:void(0);"
                   onclick="GetData(<?php echo min($__pages, $__page + 1) ?>)"
                   aria-label="ถัดไป">
                    &rsaquo;
                </a>
            </li>

            <!-- หน้าสุดท้าย >> -->
            <li class="page-item <?php echo $__page >= $__pages ? 'disabled' : '' ?>">
                <a class="page-link"
                   href="javascript:void(0);"
                   onclick="GetData(<?php echo $__pages ?>)"
                   aria-label="หน้าสุดท้าย">
                    &raquo;
                </a>
            </li>

        </ul>
    </nav>
</div>
