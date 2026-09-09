<?php
// app/views/backoffice/customer_drive/index.php
// Ported from _export_customer_drive/files/main/customer_drive.php
$baseUrl = defined('BASE_URL') ? BASE_URL : '/cpd_ac/public';

require_once dirname(__DIR__, 2) . '/main/header.php';
require_once dirname(__DIR__, 2) . '/main/sidebar.php';
?>

<link rel="stylesheet" href="<?php echo $baseUrl; ?>/template/assets/css/customer_drive.css?v=<?php echo filemtime(__DIR__ . '/../../../../public/template/assets/css/customer_drive.css'); ?>">

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">

                <div id="show_page"></div>

            </div>
        </div>
    </div>
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

<?php require_once dirname(__DIR__, 2) . '/main/footer.php'; ?>

<script src="<?php echo $baseUrl; ?>/template/assets/js/toastr.min.js"></script>
<script src="<?php echo $baseUrl; ?>/template/assets/js/customer_drive.js?v=<?php echo filemtime(__DIR__ . '/../../../../public/template/assets/js/customer_drive.js'); ?>"></script>
<script>
    // เปลือกหน้าคลังไฟล์ลูกค้า — ตรรกะทั้งหมดอยู่ใน customer_drive.js (window.CustomerDrive)
    // ไฟล์นี้ทำแค่สองอย่าง: อ่าน id จาก URL แล้วสั่งวาดหน้าครั้งแรก
    let customer_id;

    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);

        customer_id = urlParams.get('id');

        if (!customer_id) {
            Swal.fire({
                title: 'ไม่พบรหัสลูกค้า',
                icon: 'error',
                confirmButtonText: 'ตกลง'
            }).then(() => {
                window.location.href = '<?php echo $baseUrl; ?>/customer';
            });
            return;
        }

        loadPage(urlParams.get('folder') || '');
    });

    function loadPage(folderId) {
        $.ajax({
            url: "<?php echo $baseUrl; ?>/customer_drive/load_page",
            dataType: "html",
            type: "POST",
            data: {
                customer_id: customer_id,
                folder_id: folderId || ''
            },
            success: function(response) {
                $("#show_page").html(response);

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
