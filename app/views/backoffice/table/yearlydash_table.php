<?php
/**
 * Yearly Dashboard Customer List Tables (แยก 6 ส่วนตาม Stat Card ของงานรายปี)
 */
?>

<!-- 1. รายชื่อลูกค้าปิดงบทั้งหมด (total_customers) -->
<div id="table_sec_total_customers" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_total_customers">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>

<!-- 2. รายชื่อลูกค้าที่ปิดงบเสร็จแล้ว (closing_completed) -->
<div id="table_sec_closing_completed" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_closing_completed">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>

<!-- 3. รายชื่อลูกค้าที่ได้รับงบคืนแล้ว (audit_completed) -->
<div id="table_sec_audit_completed" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_audit_completed">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>

<!-- 4. รายชื่อลูกค้าที่ทำ บอจ. 5 แล้ว (boj5) -->
<div id="table_sec_boj5" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_boj5">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>

<!-- 5. รายชื่อลูกค้าที่ทำ DBD E-Filing แล้ว (dbd) -->
<div id="table_sec_dbd" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_dbd">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>

<!-- 6. รายชื่อลูกค้าที่ยื่น ภ.ง.ด.50 แล้ว (pnd50) -->
<div id="table_sec_pnd50" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_pnd50">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>
