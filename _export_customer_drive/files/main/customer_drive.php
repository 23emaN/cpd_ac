<?php require 'header.php' ?>
<?php // css/customer_drive.css ถูกโหลดใน header.php แล้ว (แถบพื้นที่ดิสก์ใน sidebar ต้องใช้ทุกหน้า) ?>
<!-- Start Main Content Area -->
<div class="main-content-container overflow-hidden">
    <div id="show_page"></div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content animated fadeIn">
            <div id="showModal"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModalLg" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content animated fadeIn">
            <div id="showModalLg"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModalXl" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content animated fadeIn">
            <div id="showModalXl"></div>
        </div>
    </div>
</div>
<!-- End Main Content Area -->

<?php require 'footer.php' ?>
<script src="../js/customer_drive.js?v=<?php echo filemtime(__DIR__ . '/../js/customer_drive.js'); ?>"></script>
<script>
    /*
     * เปลือกหน้าคลังไฟล์ลูกค้า
     *
     * ตรรกะทั้งหมดอยู่ใน js/customer_drive.js (window.CustomerDrive)
     * ไฟล์นี้ทำแค่สองอย่าง: อ่าน id จาก URL แล้วสั่งวาดหน้าครั้งแรก
     */
    let customer_id;
    let user_id;
    let user_status;

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);

        customer_id = urlParams.get('id');
        user_id = localStorage.getItem('user_id');
        user_status = localStorage.getItem('user_status');

        if (!customer_id) {
            Swal.fire({
                title: 'ไม่พบรหัสลูกค้า',
                icon: 'error',
                confirmButtonText: 'ตกลง'
            }).then(() => {
                window.location.href = 'list_customer.php';
            });
            return;
        }

        // ?folder=<id> ใช้ตอนมาจากหน้าค้นหาข้ามลูกค้า เพื่อเปิดตรงโฟลเดอร์ที่ของชิ้นนั้นอยู่
        loadPage(urlParams.get('folder') || '');
    });

    function loadPage(folderId) {
        $.ajax({
            url: "ajax/customer_drive/load_page.php",
            dataType: "html",
            type: "POST",
            data: {
                customer_id: customer_id,
                user_id: user_id,
                folder_id: folderId || ''
            },
            success: function(response) {
                $("#show_page").html(response);

                // ผูกเหตุการณ์หลัง DOM เข้าที่แล้ว — customer_drive.js อ่านค่าจากบล็อก #cd-config
                if (window.CustomerDrive) {
                    CustomerDrive.init();
                }
            },
            error: function(xhr) {
                $("#show_page").html(
                    '<div class="alert alert-danger">เปิดหน้าคลังไฟล์ไม่สำเร็จ<br><small>' +
                    $('<div>').text(xhr.responseText || '').html() + '</small></div>'
                );
            }
        });
    }
</script>
