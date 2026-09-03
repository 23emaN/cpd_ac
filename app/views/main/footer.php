<?php 
// app/views/main/footer.php
?>
<footer class="main-footer py-4 text-center mt-auto">
    <div class="container-fluid">
        <p class="mb-0 text-muted" style="font-size: 0.82rem; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            &copy; <?php echo date('Y') ?> <strong class="text-dark">CPDTH</strong> Develop By : <span class="fw-semibold text-primary">Bigsara Company</span>
        </p>
    </div>
</footer>

<!-- Core Scripts -->
<!-- Core Scripts -->
<script src="/cpd_ac/public/template/assets/js/jquery-3.1.1.min.js"></script>
<script src="/cpd_ac/public/template/assets/js/bootstrap.bundle.min.js"></script>
<script src="/cpd_ac/public/template/assets/js/feather.min.js"></script>
<script src="/cpd_ac/public/template/assets/js/simplebar.min.js"></script>
<script src="/cpd_ac/public/template/assets/js/clipboard.min.js"></script>
<script src="/cpd_ac/public/template/assets/js/Sortable.min.js"></script>
<script src="/cpd_ac/public/template/assets/js/sweetalert2.min.js"></script>
<script src="/cpd_ac/public/template/assets/js/swiper-bundle.min.js"></script>
<script src="/cpd_ac/public/template/assets/js/sidebar-menu.js"></script>
<script src="/cpd_ac/public/template/assets/js/custom/custom.js"></script>

<script>
// ฟังก์ชันกลางสำหรับจัดการเมื่อเปลี่ยนปีทำงานใน Header (ใช้งานร่วมกันทุกหน้า)
function onYearChanged(companyId, year, fiscalId) {
    // ถ้าระบบไม่ได้อยู่ในหน้า Backoffice ก็ไม่ต้องทำอะไร (เช่น หน้าแรกที่โชว์การ์ด)
    const path = window.location.pathname.toLowerCase();
    const isBackoffice = path.includes('/backoffice') || path.includes('/tasks') || path.includes('/customer') || path.includes('/employee');
    
    if (!isBackoffice) {
        return; 
    }

    // ยิง AJAX ไปเซต Session ที่ฝั่งเซิร์ฟเวอร์
    $.ajax({
        url: '/cpd_ac/public/fiscal_years/set_context',
        type: 'POST',
        data: { fiscal_id: fiscalId },
        success: function() {
            // เมื่อเซิร์ฟเวอร์จำค่าปีใหม่แล้ว ให้โหลดหน้าเว็บซ้ำ 1 รอบ (Refresh)
            window.location.reload();
        },
        error: function(err) {
            console.error('Failed to change context:', err);
        }
    });
}

// ตรวจสอบ Toast จาก Session Storage (เช่น หลังจากการเพิ่ม/แก้ไขข้อมูล)
document.addEventListener('DOMContentLoaded', function() {
    let tMsg = sessionStorage.getItem('toast_msg');
    let tIcon = sessionStorage.getItem('toast_icon');
    if (tMsg && typeof Swal !== 'undefined') {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
        Toast.fire({ icon: tIcon || 'success', title: tMsg });
        sessionStorage.removeItem('toast_msg');
        sessionStorage.removeItem('toast_icon');
    }
});
</script>

</body>
</html>
