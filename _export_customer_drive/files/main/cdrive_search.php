<?php require 'header.php' ?>
<!-- Start Main Content Area -->
<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h3 class="mb-0">ค้นหาไฟล์ลูกค้า</h3>

        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="material-symbols-outlined menu-icon">folder_shared</span>
                    <span class="fw-medium">ค้นหาไฟล์ลูกค้า</span>
                </li>
            </ol>
        </nav>
    </div>

    <div class="card bg-white border-0 rounded-3 cd-card">
        <div class="card-body p-4">
            <div class="cd-search position-relative mb-3">
                <span class="material-symbols-outlined cd-search-icon">search</span>
                <input type="search" class="form-control form-control-lg ps-5" id="cds-input"
                    placeholder="พิมพ์ชื่อไฟล์หรือโฟลเดอร์ — ค้นข้ามลูกค้าทุกราย" autocomplete="off" autofocus>
            </div>

            <div id="cds-result"></div>
        </div>
    </div>
</div>
<!-- End Main Content Area -->

<?php require 'footer.php' ?>
<script>
    /*
     * ค้นหาไฟล์ข้ามลูกค้าทุกราย
     *
     * หน้านี้เล็กพอที่ไม่ต้องมีไฟล์ js แยก — ตรรกะทั้งหมดคือ
     * "พิมพ์ → หน่วง → ยิง → วาดผล" เท่านั้น
     */
    let user_id;
    let cdsTimer = null;

    $(document).ready(function() {
        user_id = localStorage.getItem('user_id');

        $('#cds-input').on('input', function() {
            const keyword = $.trim($(this).val());

            clearTimeout(cdsTimer);

            // คำเดียวตัวอักษรเดียวคืนผลเป็นพันแถวโดยไม่มีประโยชน์ — รออย่างน้อย 2 ตัว
            if (keyword.length < 2) {
                $('#cds-result').html(
                    '<div class="text-center text-muted py-5">' +
                    '<span class="material-symbols-outlined cd-empty-icon">search</span>' +
                    '<div class="mt-2">พิมพ์อย่างน้อย 2 ตัวอักษรเพื่อเริ่มค้นหา</div></div>'
                );
                return;
            }

            cdsTimer = setTimeout(function() {
                search(keyword);
            }, 300);
        }).trigger('input');
    });

    function search(keyword) {
        $.ajax({
            url: 'ajax/cdrive_search/get_table.php',
            type: 'POST',
            dataType: 'html',
            data: {
                user_id: user_id,
                keyword: keyword
            },
            beforeSend: function() {
                $('#cds-result').html(
                    '<div class="text-center text-muted py-5">' +
                    '<div class="spinner-border text-primary" role="status"></div>' +
                    '<div class="mt-2">กำลังค้นหา…</div></div>'
                );
            },
            success: function(response) {
                $('#cds-result').html(response);
            },
            error: function(xhr) {
                $('#cds-result').html(
                    '<div class="alert alert-danger">ค้นหาไม่สำเร็จ<br><small>' +
                    $('<div>').text(xhr.responseText || '').html() + '</small></div>'
                );
            }
        });
    }
</script>
