<?php
/**
 * Monthly Dashboard Customer List Tables (แยก 6 ส่วนตาม Stat Card)
 */
?>

<!-- 1. รายชื่อลูกค้าทั้งหมดในเดือนนี้ (total_customers) -->
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

<!-- 2. รายชื่อลูกค้าที่ได้รับเอกสารแล้ว (doc_received) -->
<div id="table_sec_doc_received" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_doc_received">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>

<!-- 3. รายชื่อลูกค้าที่ทำเสร็จแล้ว (completed) -->
<div id="table_sec_completed" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_completed">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>

<!-- 4. รายชื่อลูกค้าที่ยื่นภาษีแล้ว (tax_filed) -->
<div id="table_sec_tax_filed" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_tax_filed">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>

<!-- 5. รายชื่อลูกค้าที่เก็บเงินลูกค้าแล้ว (payment_collected) -->
<div id="table_sec_payment_collected" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_payment_collected">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>

<!-- 6. รายชื่อลูกค้าที่ยังไม่เก็บเงินลูกค้า (payment_pending) -->
<div id="table_sec_payment_pending" class="dash-table-sec" style="display: none;">
    <div class="table-responsive" style="min-height: 180px;">
        <table class="table-custom align-middle">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th class="text-start">ชื่อลูกค้า</th>
                    <th class="text-start" style="width: 250px;">ผู้ดูแล</th>
                </tr>
            </thead>
            <tbody id="customerListTbody_payment_pending">
                <!-- Dynamic JS Content -->
            </tbody>
        </table>
    </div>
</div>
