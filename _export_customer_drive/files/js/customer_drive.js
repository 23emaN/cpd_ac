/**
 * คลังไฟล์ลูกค้า — ฝั่งหน้าเว็บ
 *
 * โครงเดียวกับ js/job_view.js: IIFE ตัวเดียว ผูก event delegation ไว้ที่ #cd-app
 * แล้วอ่านค่าที่ต้องใช้จากบล็อก JSON #cd-config ที่ load_page.php พ่นมาให้
 *
 * กติกาที่ยึดทั้งไฟล์
 *   1. เทียบ result แบบเข้ม (=== '1') จึงต้องแน่ใจว่าเซิร์ฟเวอร์ส่งสตริงเสมอ
 *      (cd_ok() / cd_fail() จัดการให้แล้ว)
 *   2. สิทธิ์ที่อ่านจาก cfg.perm ใช้ "ซ่อนปุ่ม" เท่านั้น ไม่ใช่ด่านความปลอดภัย
 *      endpoint ตรวจซ้ำฝั่งเซิร์ฟเวอร์ทุกตัวอยู่แล้ว
 *   3. วาดตารางใหม่ทั้งก้อนหลังทุกการเปลี่ยนแปลง ไม่แก้ DOM ทีละแถว —
 *      ตารางเล็กพอที่จะทำแบบนี้ได้ และทำให้ไม่มีทางที่หน้าจอกับฐานข้อมูลเถียงกัน
 */
window.CustomerDrive = (function () {
    'use strict';

    var cfg = null;          // ค่าจาก #cd-config
    var folderId = null;     // โฟลเดอร์ที่เปิดอยู่ (null = ชั้นบนสุด)
    var keyword = '';        // คำค้นปัจจุบัน
    var selected = [];       // node_id ที่ถูกเลือก เรียงตามลำดับที่คลิก
    var lastClicked = null;  // ใช้กับ Shift+คลิก
    var searchTimer = null;
    var uploadQueue = 0;     // จำนวนไฟล์ที่ยังอัปไม่เสร็จ

    /*
     * โฟลเดอร์ที่ถูกย่อไว้ (node_id)
     *
     * เก็บฝั่งหน้าเว็บ ไม่ส่งไปเซิร์ฟเวอร์ เพราะเป็นเรื่องของ "มุมมองตอนนี้"
     * ไม่ใช่ข้อมูล และต้องอยู่รอดตอนวาดตารางใหม่หลังอัปโหลด/ลบ/เปลี่ยนชื่อ
     * มิฉะนั้นทุกครั้งที่ทำอะไรสักอย่าง ทรีจะกางกลับหมดแล้วผู้ใช้หลงที่
     *
     * ค่าปริยายคือ "กางหมด" — คลังของสำนักงานสอบบัญชีไม่ได้ลึกมาก
     * และการเห็นทั้งโครงสร้างในสายตาเดียวคือเหตุผลที่เลือกทรีแทนตารางรายโฟลเดอร์
     */
    var collapsed = {};

    /*
     * แถวที่กดค้างอยู่บนกลุ่มที่เลือกไว้แล้ว — รอดูว่าจะลากหรือแค่คลิก
     * (ถ้าลาก ต้องคงกลุ่มเดิมไว้ทั้งชุด ถ้าแค่คลิก ให้ยุบเหลือใบนั้นใบเดียว)
     */
    var pendingSingle = null;


    // ------------------------------------------------------------ ตัวช่วย

    function $app() {
        return $('#cd-app');
    }

    function ok(message) {
        if (window.toastr) {
            toastr.success(message);
        }
    }

    /**
     * หา element ที่ควรใช้เป็น target ของ Swal.fire()
     *
     * ค่าเริ่มต้นของ SweetAlert2 คือแปะ popup ไว้ที่ document.body ซึ่งอยู่นอก
     * modal ของ Bootstrap ที่เปิดอยู่ — Bootstrap ดักเหตุการณ์ focusin แล้วดึง
     * โฟกัสกลับเข้า modal ทันที (เหตุผลเดียวกับที่ copyText() ต้องย้าย textarea
     * เข้าไปใน modal) ทำให้กดหรือพิมพ์อะไรใน Swal ที่ลอยอยู่นอก modal ไม่ติดเลย
     */
    function swalHost($trigger) {
        return $trigger.closest('.modal')[0] || document.body;
    }

    function warn(message) {
        Swal.fire({ title: 'ทำรายการไม่สำเร็จ', text: message, icon: 'warning', confirmButtonText: 'ตกลง' });
    }

    function boom(message) {
        Swal.fire({ title: 'เกิดข้อผิดพลาด', text: message, icon: 'error', confirmButtonText: 'ตกลง' });
    }

    /** ตอบกลับมาตรฐาน {result, msn} — คืน true เมื่อสำเร็จ */
    function handled(res) {
        if (res && res.result === '1') {
            if (res.msn) ok(res.msn);
            return true;
        }

        warn((res && res.msn) || 'ไม่ทราบสาเหตุ');
        return false;
    }

    function post(file, data) {
        return $.ajax({
            url: cfg.api + file,
            type: 'POST',
            dataType: 'json',
            data: $.extend({ customer_id: cfg.customerId, user_id: cfg.userId }, data || {})
        });
    }

    function html(file, data) {
        return $.ajax({
            url: cfg.api + file,
            type: 'POST',
            dataType: 'html',
            data: $.extend({ customer_id: cfg.customerId, user_id: cfg.userId }, data || {})
        });
    }

    /** URL ของ endpoint ที่ตอบเป็นไฟล์ (ต้องเปิดด้วย location ไม่ใช่ AJAX) */
    function fileUrl(file, params) {
        var q = $.param($.extend({ customer_id: cfg.customerId, user_id: cfg.userId }, params || {}));
        return cfg.api + file + '?' + q;
    }

    function esc(text) {
        return $('<div>').text(text == null ? '' : text).html();
    }

    function bytes(n) {
        if (!n || n <= 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var i = Math.min(Math.floor(Math.log(n) / Math.log(1024)), units.length - 1);
        return (Math.round((n / Math.pow(1024, i)) * 10) / 10) + ' ' + units[i];
    }

    // ------------------------------------------------------------ ตาราง

    function reload() {
        return html('render_table.php', {
            folder_id: folderId || '',
            keyword: keyword
        }).done(function (markup) {
            $('#cd-table').html(markup);
            applyTree();
            clearSelection();
        }).fail(function (xhr) {
            $('#cd-table').html('<div class="alert alert-danger mb-0">โหลดรายการไม่สำเร็จ<br><small>' +
                esc(xhr.responseText || '') + '</small></div>');
        });
    }

    /** ไปโฟลเดอร์อื่น — โหลดหน้าใหม่ทั้งหน้าเพื่อให้ breadcrumb ถูกต้อง */
    function go(id) {
        keyword = '';
        collapsed = {};   // เปลี่ยนขอบเขตแล้ว สถานะย่อของเดิมไม่มีความหมายอีก
        window.loadPage(id || '');
    }

    function rowOf(el) {
        return $(el).closest('.cd-row');
    }

    function nodeOf(el) {
        var $row = rowOf(el);
        if (!$row.length) return null;

        return {
            id: parseInt($row.data('id'), 10),
            parent: parseInt($row.data('parent'), 10) || null,
            kind: String($row.data('kind')),
            name: String($row.data('name')),
            ext: String($row.data('ext') || ''),
            preview: String($row.data('preview')) === '1',
            version: parseInt($row.data('version'), 10) || 0,
            kids: String($row.data('kids')) === '1',
            depth: parseInt($row.data('depth'), 10) || 0
        };
    }

    // ------------------------------------------------------------ ทรี

    /**
     * ซ่อน/แสดงแถวตามสถานะย่อของแม่
     *
     * ไล่จากบนลงล่างครั้งเดียว จำไว้ว่ากำลังอยู่ใต้แม่ที่ถูกย่อหรือเปล่า —
     * ถูกกว่าการไล่หาบรรพบุรุษของแต่ละแถว และได้ผลเหมือนกันเพราะแถวถูกวาง
     * เรียงตามลำดับทรีอยู่แล้ว (cd_flatten คืนมาแบบ pre-order)
     */
    function applyTree() {
        var hideUnder = null;   // depth ของแม่ที่ถูกย่อ ถ้ากำลังซ่อนอยู่
        var visible = [];

        $('.cd-row').each(function () {
            var $row = $(this);
            var depth = parseInt($row.data('depth'), 10) || 0;
            var id = parseInt($row.data('id'), 10);

            // ออกจากกิ่งที่ถูกซ่อนแล้วเมื่อกลับมาตื้นเท่าเดิมหรือตื้นกว่า
            if (hideUnder !== null && depth <= hideUnder) {
                hideUnder = null;
            }

            var hidden = hideUnder !== null;

            $row.toggleClass('d-none', hidden);

            if (!hidden) {
                visible.push(id);
            }

            var isCollapsed = !!collapsed[id];

            $row.toggleClass('collapsed', isCollapsed);

            if (!hidden && isCollapsed && String($row.data('kids')) === '1') {
                hideUnder = depth;
            }
        });

        return visible;
    }

    function toggleFolder(id) {
        if (collapsed[id]) {
            delete collapsed[id];
        } else {
            collapsed[id] = true;
        }

        applyTree();
    }

    /** id ของแถวที่มองเห็นอยู่ เรียงตามที่เห็นบนจอ — ใช้กับปุ่มลัดและ Shift+คลิก */
    function visibleIds() {
        return $('.cd-row').not('.d-none').map(function () {
            return parseInt($(this).data('id'), 10);
        }).get();
    }

    // ------------------------------------------------------------ การเลือก

    function clearSelection() {
        selected = [];
        lastClicked = null;
        pendingSingle = null;
        syncSelection();
    }

    function syncSelection() {
        $('.cd-row').each(function () {
            var id = parseInt($(this).data('id'), 10);
            $(this).toggleClass('selected', selected.indexOf(id) !== -1);
        });
    }

    /** เลือกทั้งหมดในโฟลเดอร์ที่เห็นอยู่ */
    function selectAll() {
        selected = visibleIds();
        syncSelection();
    }

    /**
     * เลื่อนแถวที่เลือกด้วยลูกศรขึ้น/ลง
     *
     * เลื่อนแล้วต้องพาแถวเข้ามาในจอด้วย มิฉะนั้นพอกดค้างลงไปเรื่อย ๆ
     * แถวที่เลือกจะหลุดออกนอกจอโดยที่ผู้ใช้ไม่เห็นว่าตัวเองอยู่ตรงไหน
     */
    function moveCursor(step, extend) {
        var ids = visibleIds();

        if (!ids.length) return;

        var at = lastClicked === null ? -1 : ids.indexOf(lastClicked);
        var next = at === -1 ? (step > 0 ? 0 : ids.length - 1) : at + step;

        next = Math.max(0, Math.min(ids.length - 1, next));

        if (extend) {
            toggle(ids[next], true);
        } else {
            selected = [ids[next]];
        }

        lastClicked = ids[next];
        syncSelection();

        var el = $('.cd-row[data-id="' + ids[next] + '"]')[0];
        if (el && el.scrollIntoView) {
            el.scrollIntoView({ block: 'nearest' });
        }
    }

    function toggle(id, on) {
        var i = selected.indexOf(id);

        if (on === undefined) on = i === -1;

        if (on && i === -1) selected.push(id);
        if (!on && i !== -1) selected.splice(i, 1);
    }

    function selectRange(toId) {
        var ids = visibleIds();
        var a = ids.indexOf(lastClicked);
        var b = ids.indexOf(toId);

        if (a === -1 || b === -1) {
            toggle(toId, true);
            return;
        }

        var from = Math.min(a, b);
        var to = Math.max(a, b);

        for (var i = from; i <= to; i++) {
            toggle(ids[i], true);
        }
    }

    // ------------------------------------------------------------ เมนู

    /**
     * รายการในเมนู — เปลี่ยนตามจำนวนที่เลือกอยู่
     *
     * เลือกหลายรายการแล้วคลิกขวา = เมนูทำกับทั้งชุด (ไม่มีแถบคำสั่งด้านบนแล้ว
     * ตัดสินใจแล้วว่าใช้เมนูคลิกขวาแทน) จึงต้องตัดคำสั่งที่ทำกับหลายรายการ
     * พร้อมกันไม่ได้ออก — เปลี่ยนชื่อ ดูเวอร์ชัน และเปิดดู ทำได้ทีละใบเท่านั้น
     */
    function menuFor(node) {
        var items = [];

        if (selected.length > 1) {
            items.push({ head: 'เลือกไว้ ' + selected.length + ' รายการ' });
            items.push({ act: 'download', icon: 'download', label: 'ดาวน์โหลดเป็น .zip' });

            if (cfg.perm.cdrive_edit) {
                items.push({ act: 'move', icon: 'drive_file_move', label: 'ย้ายไป…' });
            }

            if (cfg.perm.cdrive_delete) {
                items.push({ sep: true });
                items.push({ act: 'delete', icon: 'delete', label: 'ทิ้งลงถังขยะ', danger: true });
            }

            items.push({ sep: true });
            items.push({ act: 'clear', icon: 'deselect', label: 'ยกเลิกการเลือก' });

            return items;
        }

        if (node.kind === 'folder') {
            if (node.kids) {
                items.push({
                    act: 'toggle',
                    icon: collapsed[node.id] ? 'unfold_more' : 'unfold_less',
                    label: collapsed[node.id] ? 'ขยายโฟลเดอร์' : 'ย่อโฟลเดอร์'
                });
            }

            items.push({ act: 'open', icon: 'folder_open', label: 'เปิดเฉพาะโฟลเดอร์นี้' });
        } else if (node.preview) {
            items.push({ act: 'preview', icon: 'visibility', label: 'เปิดดู' });
        }

        items.push({ act: 'download', icon: 'download', label: node.kind === 'folder' ? 'ดาวน์โหลดเป็น .zip' : 'ดาวน์โหลด' });

        if (node.kind === 'file' && node.version > 0) {
            items.push({ act: 'versions', icon: 'history', label: 'ประวัติเวอร์ชัน' });
        }

        if (cfg.perm.cdrive_edit) {
            items.push({ sep: true });
            items.push({ act: 'rename', icon: 'edit', label: 'เปลี่ยนชื่อ' });
            items.push({ act: 'move', icon: 'drive_file_move', label: 'ย้ายไป…' });
        }

        if (cfg.perm.cdrive_delete) {
            items.push({ sep: true });
            items.push({ act: 'delete', icon: 'delete', label: 'ทิ้งลงถังขยะ', danger: true });
        }

        return items;
    }

    function showMenu(node, x, y) {
        var $menu = $('#cd-menu');
        var out = '';

        menuFor(node).forEach(function (item) {
            if (item.sep) {
                out += '<div class="cd-menu-sep"></div>';
                return;
            }

            if (item.head) {
                out += '<div class="cd-menu-head">' + esc(item.head) + '</div>';
                return;
            }

            out += '<button type="button" class="cd-menu-item' + (item.danger ? ' danger' : '') +
                '" data-act="' + item.act + '">' +
                '<span class="material-symbols-outlined fs-18">' + item.icon + '</span>' +
                '<span>' + esc(item.label) + '</span></button>';
        });

        $menu.html(out).data('node', node).removeClass('d-none');

        // กันเมนูล้นขอบจอ — วัดหลังใส่เนื้อแล้วเท่านั้น
        var w = $menu.outerWidth();
        var h = $menu.outerHeight();
        var left = Math.min(x, $(window).width() - w - 8);
        var top = Math.min(y, $(window).scrollTop() + $(window).height() - h - 8);

        $menu.css({ left: Math.max(8, left) + 'px', top: Math.max(8, top) + 'px' });
    }

    function hideMenu() {
        $('#cd-menu').addClass('d-none').removeData('node');
    }

    // ------------------------------------------------------------ คำสั่ง

    /**
     * ลงมือทำ — ถ้าเลือกไว้หลายรายการให้ทำกับทั้งชุด
     *
     * ดาวน์โหลด/ย้าย/ลบ ทำกับหลายรายการได้ ส่วนที่เหลือทำได้ทีละใบ
     * (menuFor() ตัดตัวที่ทำหลายใบไม่ได้ออกไปแล้ว แต่ปุ่ม ⋮ ยังเรียกมาได้อยู่)
     */
    function doAction(act, node) {
        if (act === 'clear') {
            clearSelection();
            return;
        }

        if (selected.length > 1) {
            if (act === 'download') {
                window.location = fileUrl('download_zip.php', { node_ids: selected.join(',') });
                return;
            }

            if (act === 'move') {
                openPicker(selected.slice());
                return;
            }

            if (act === 'delete') {
                confirmDelete(selected.slice(), '');
                return;
            }
        }

        switch (act) {
            case 'toggle':
                toggleFolder(node.id);
                break;

            case 'open':
                go(node.id);
                break;

            case 'preview':
                window.open(fileUrl('download.php', { node_id: node.id, mode: 'inline' }), '_blank', 'noopener');
                break;

            case 'download':
                if (node.kind === 'folder') {
                    window.location = fileUrl('download_zip.php', { node_ids: node.id });
                } else {
                    window.location = fileUrl('download.php', { node_id: node.id });
                }
                break;

            case 'versions':
                openVersions(node.id);
                break;

            case 'rename':
                startRename(node.id);
                break;

            case 'move':
                openPicker([node.id]);
                break;

            case 'delete':
                confirmDelete([node.id], node.name);
                break;
        }
    }

    function startRename(id) {
        var $row = $('.cd-row[data-id="' + id + '"]');
        var $name = $row.find('.cd-name');

        if (!$name.length || $name.find('input').length) return;

        var current = String($row.data('name'));

        $name.html('<input type="text" class="form-control form-control-sm cd-rename-input">');

        var $input = $name.find('input').val(current).trigger('focus');

        // เลือกเฉพาะส่วนชื่อ ไม่รวมนามสกุล — คนแก้ชื่อ ไม่ได้แก้นามสกุล
        var dot = current.lastIndexOf('.');
        if (dot > 0 && String($row.data('kind')) === 'file') {
            $input[0].setSelectionRange(0, dot);
        } else {
            $input[0].select();
        }

        var done = false;

        function finish(save) {
            if (done) return;
            done = true;

            var value = $.trim($input.val());

            if (!save || value === '' || value === current) {
                $name.text(current);
                return;
            }

            post('rename.php', { node_id: id, name: value }).done(function (res) {
                if (handled(res)) {
                    reload();
                } else {
                    $name.text(current);
                }
            }).fail(function (xhr) {
                $name.text(current);
                boom(xhr.responseText || '');
            });
        }

        $input.on('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); finish(true); }
            if (e.key === 'Escape') { e.preventDefault(); finish(false); }
        }).on('blur', function () { finish(true); });
    }

    function confirmDelete(ids, label) {
        Swal.fire({
            title: 'ทิ้งลงถังขยะ?',
            html: ids.length === 1
                ? esc(label) + '<br><small class="text-muted">กู้คืนได้จากถังขยะ</small>'
                : 'ทิ้ง ' + ids.length + ' รายการ<br><small class="text-muted">กู้คืนได้จากถังขยะ</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ทิ้งลงถังขยะ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#dc3545'
        }).then(function (r) {
            if (!r.isConfirmed) return;

            post('delete.php', { node_ids: ids.join(',') }).done(function (res) {
                if (handled(res)) {
                    setTrashCount(res.trash_count);
                    reload();
                }
            }).fail(function (xhr) { boom(xhr.responseText || ''); });
        });
    }

    function setTrashCount(n) {
        var $badge = $('.cd-trash-count');

        if (!$badge.length) return;

        $badge.text(n || 0).toggleClass('d-none', !n);
    }

    /**
     * ใส่เนื้อหาลง modal แล้วเปิด — ใช้ตัวเดียวกันทั้งเปิดครั้งแรกและอัปเดตตอนเปิดอยู่
     *
     * **ห้ามใช้ new bootstrap.Modal(el).show() ซ้ำกับ modal ที่เปิดค้างอยู่**
     * Bootstrap จะสร้าง instance ใหม่แล้วเพิ่ม .modal-backdrop อีกชั้นทุกครั้ง
     * พอปิด modal มันลบ backdrop แค่ชั้นเดียว ที่เหลือค้างคลุมหน้าจอเป็นสีเทา
     * กดอะไรไม่ได้เลยจนกว่าจะรีโหลดหน้า
     *
     * เคยเกิดจริงตอนกดกู้คืนจากถังขยะ ซึ่งวาดเนื้อ modal ใหม่ทั้งที่ modal ยังเปิดอยู่
     *
     * getOrCreateInstance ใช้ instance เดิมถ้ามีอยู่แล้ว และเรียก show()
     * เฉพาะตอนที่ยังไม่ได้เปิด
     */
    function fillModal(modalId, targetId, markup) {
        /*
         * ติดคลาส cd-modal ให้กล่องที่รับเนื้อหา — เป็นขอบเขตของระบบตัวอักษร
         * และระยะห่างทั้งชุดใน css/customer_drive.css
         *
         * ต้องติดจากตรงนี้ ไม่ใช่เขียนลงใน markup ที่ endpoint ส่งมา เพราะ
         * .modal-header / .modal-body ต้องเป็นลูกตรงของ .modal-content
         * ตามที่ Bootstrap กำหนด จะเอา div มาครอบเพิ่มไม่ได้
         *
         * เปลือก modal (#showModal / #showModalLg / #showModalXl) ใช้ร่วมกับ
         * โมดูลอื่นที่คัดลอกมาจาก import_script.php การผูกสไตล์ไว้กับคลาสนี้
         * จึงกันไม่ให้รั่วไปโดนหน้าอื่นที่ css ตัวนี้ถูกโหลดด้วย (header.php โหลดทุกหน้า)
         */
        $('#' + targetId).addClass('cd-modal').html(markup);

        var el = document.getElementById(modalId);

        if (!el) return;

        var instance = bootstrap.Modal.getOrCreateInstance(el);

        if (!el.classList.contains('show')) {
            instance.show();
        }
    }

    function openVersions(id) {
        html('versions.php', { node_id: id }).done(function (markup) {
            fillModal('myModalLg', 'showModalLg', markup);
            $('#showModalLg').data('node-id', id);
        });
    }

    function openPicker(ids) {
        html('folder_tree.php', { node_ids: ids.join(',') }).done(function (markup) {
            fillModal('myModal', 'showModal', markup);
            $('#showModal').data('moving', ids);
        });
    }

    function openLinks() {
        html('link_list.php').done(function (markup) {
            fillModal('myModalXl', 'showModalXl', markup);
        });
    }

    /**
     * แสดงลิงก์กับรหัสผ่านหลังสร้างเสร็จ
     *
     * **สองกล่องแยกกัน มีปุ่มคัดลอกแยกกัน และจงใจไม่มีปุ่ม "คัดลอกทั้งชุด"**
     * เพราะกติกาคือรหัสผ่านต้องไม่เดินทางไปพร้อมลิงก์ในข้อความเดียวกัน
     * ถ้ามีปุ่มคัดลอกทั้งชุด ทางที่ผิดจะกลายเป็นทางที่ง่ายที่สุด
     *
     * รหัสนี้ออกจากเซิร์ฟเวอร์เป็นข้อความธรรมดาแค่ครั้งเดียว เพราะเก็บเป็น hash
     */
    function showLinkResult(res, isReset) {
        var $box = $('.cd-linkresult');

        /*
         * สองการ์ดแยกกันชัดเจน ไม่ใช่กล่องเดียวที่มีสองช่อง
         *
         * เพราะสิ่งที่ต้องสื่อไม่ใช่ "นี่คือผลลัพธ์" แต่คือ "นี่คือของสองชิ้น
         * ที่ต้องเดินทางแยกกัน" — ถ้าวางรวมในกรอบเดียว มันอ่านเหมือนข้อมูลชุดเดียว
         * แล้วคนจะลากคลุมทั้งก้อนส่งไปทีเดียว ซึ่งทำลายเหตุผลของการมีรหัสผ่าน
         *
         * ตัวเลข "1" กับ "2" บอกลำดับ และคำเตือนอยู่คั่นกลางระหว่างสองการ์ดพอดี
         * ตรงจุดที่คนกำลังจะทำผิด
         */
        $box.removeClass('d-none').html(
            '<div class="cd-result">' +
            '<div class="cd-result-head">' +
            '<span class="material-symbols-outlined" aria-hidden="true">check_circle</span>' +
            '<div>' +
            '<div class="cd-result-title">' + (isReset ? 'ตั้งรหัสผ่านใหม่แล้ว' : 'สร้างลิงก์แล้ว') + '</div>' +
            '<div class="cd-result-sub">รหัสผ่านแสดงครั้งเดียว ปิดหน้าต่างแล้วดูซ้ำไม่ได้</div>' +
            '</div>' +
            '</div>' +

            '<div class="cd-give">' +
            '<div class="cd-give-no">1</div>' +
            '<div class="cd-give-body">' +
            '<div class="cd-give-label">ลิงก์สำหรับลูกค้า</div>' +
            '<div class="cd-give-row">' +
            '<input type="text" class="cd-give-value" id="lk-out-url" readonly value="' + esc(res.url) + '">' +
            '<button type="button" class="cd-btn cd-btn-ghost cd-btn-sm" data-cd="lk-copy" data-url="' + esc(res.url) + '">' +
            '<span class="material-symbols-outlined" aria-hidden="true">content_copy</span>' +
            '<span class="cd-btn-label">คัดลอก</span>' +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>' +

            '<div class="cd-give-warn">' +
            '<span class="material-symbols-outlined" aria-hidden="true">warning</span>' +
            '<div><strong>ส่งรหัสผ่านคนละข้อความกับลิงก์</strong> ' +
            'ถ้าส่งไปด้วยกัน ใครที่เห็นข้อความนั้นก็เปิดเอกสารของลูกค้าได้ทันที</div>' +
            '</div>' +

            '<div class="cd-give">' +
            '<div class="cd-give-no">2</div>' +
            '<div class="cd-give-body">' +
            '<div class="cd-give-label">รหัสผ่าน</div>' +
            '<div class="cd-give-row">' +
            '<input type="text" class="cd-give-value cd-give-code" id="lk-out-pw" readonly value="' + esc(res.password) + '">' +
            '<button type="button" class="cd-btn cd-btn-ghost cd-btn-sm" data-cd="lk-copy" data-url="' + esc(res.password) + '">' +
            '<span class="material-symbols-outlined" aria-hidden="true">content_copy</span>' +
            '<span class="cd-btn-label">คัดลอก</span>' +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>'
        );

        $box[0].scrollIntoView({ block: 'nearest' });
    }

    /**
     * คัดลอกข้อความลงคลิปบอร์ด
     *
     * ⚠️ ของเดิมพังเงียบ ๆ — ป้ายขึ้น "คัดลอกแล้ว" ทั้งที่คลิปบอร์ดว่าง สามสาเหตุ
     *
     *   1. **วาง textarea ชั่วคราวไว้ที่ document.body** ซึ่งอยู่นอก modal ที่เปิดอยู่
     *      Bootstrap ดักเหตุการณ์ focusin แล้วดึงโฟกัสกลับเข้า modal ทันที
     *      การเลือกข้อความจึงหลุดก่อน execCommand จะทำงาน
     *   2. **ไม่เคยดูค่าที่ execCommand คืนมา** มันคืน false เมื่อทำไม่สำเร็จ
     *      โดยไม่ throw โค้ดเดิมจับแต่ exception จึงคิดว่าสำเร็จเสมอ
     *   3. `opacity: 0` ทำให้บางเบราว์เซอร์ถือว่าเลือกข้อความไม่ได้
     *
     * เว็บพัฒนารันบน http ซึ่งไม่ใช่ secure context — `navigator.clipboard`
     * ไม่มีให้ใช้เลย ทางถอยนี้จึงไม่ใช่ทางสำรอง แต่เป็นทางหลักบนเครื่องนี้
     */
    function copyText(text, $btn) {
        function flash(okText) {
            var $label = $btn.find('.cd-btn-label');
            var $target = $label.length ? $label : $btn;
            var old = $target.text();

            $btn.addClass('cd-copied');
            $target.text(okText);

            setTimeout(function () {
                $target.text(old);
                $btn.removeClass('cd-copied');
            }, 1400);
        }

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text)
                .then(function () { flash('คัดลอกแล้ว'); })
                .catch(fallback);
            return;
        }

        fallback();

        function fallback() {
            /*
             * วางไว้ใน modal เดียวกับปุ่ม เพื่อไม่ให้ชนกับกับดักโฟกัสของ Bootstrap
             * ถ้าไม่ได้อยู่ใน modal ก็ใช้ body ตามเดิม
             */
            var host = $btn.closest('.modal')[0] || document.body;

            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'absolute';
            ta.style.left = '-9999px';
            ta.style.top = '0';

            host.appendChild(ta);

            ta.focus();
            ta.select();
            ta.setSelectionRange(0, text.length);   // iOS ต้องสั่งช่วงเอง

            var ok = false;

            try { ok = document.execCommand('copy'); } catch (e) { ok = false; }

            host.removeChild(ta);

            if (ok) {
                flash('คัดลอกแล้ว');
                return;
            }

            /*
             * คัดลอกไม่สำเร็จจริง ๆ — ต้องบอกตรง ๆ ไม่ใช่แกล้งว่าสำเร็จ
             * แล้วเลือกข้อความในช่องข้าง ๆ ไว้ให้ ผู้ใช้กด Ctrl+C ต่อได้เลย
             */
            var $field = $btn.closest('.cd-give-row').find('.cd-give-value');

            if ($field.length) {
                $field[0].focus();
                $field[0].select();
            }

            warn('เบราว์เซอร์ไม่ยอมให้คัดลอกอัตโนมัติ — เลือกข้อความไว้ให้แล้ว กด Ctrl+C ได้เลย');
        }
    }

    function openTrash() {
        html('render_trash.php').done(function (markup) {
            fillModal('myModalLg', 'showModalLg', markup);
        });
    }

    function openActivity() {
        html('activity.php').done(function (markup) {
            fillModal('myModalLg', 'showModalLg', markup);
        });
    }

    function makeFolder() {
        Swal.fire({
            title: 'โฟลเดอร์ใหม่',
            input: 'text',
            inputPlaceholder: 'เช่น ' + cfg.fiscalYear + ' หรือ เอกสารประกอบ',
            inputAttributes: { maxlength: 200, autocapitalize: 'off' },
            showCancelButton: true,
            confirmButtonText: 'สร้าง',
            cancelButtonText: 'ยกเลิก',
            inputValidator: function (v) {
                if (!$.trim(v)) return 'กรุณาตั้งชื่อโฟลเดอร์';
                return null;
            }
        }).then(function (r) {
            if (!r.isConfirmed) return;

            post('mkdir.php', { parent_id: uploadTarget() || 0, name: $.trim(r.value) }).done(function (res) {
                if (handled(res)) reload();
            }).fail(function (xhr) { boom(xhr.responseText || ''); });
        });
    }

    // ------------------------------------------------------------ ปลายทางของของใหม่

    /**
     * โฟลเดอร์ที่ของใหม่จะไปลง — ใช้ทั้งปุ่มอัปโหลดและปุ่มโฟลเดอร์ใหม่
     *
     * **ทำไมต้องมีฟังก์ชันนี้**
     *
     * ตารางเป็นทรีที่กางทุกชั้นพร้อมกัน จึงไม่มีสิ่งที่เรียกว่า "โฟลเดอร์ที่เปิดอยู่"
     * ในความหมายเดิมอีกต่อไป — folderId เป็น null เกือบตลอดเวลาเพราะผู้ใช้ไม่ได้
     * เดินเข้าโฟลเดอร์ ของใหม่จึงตกลงชั้นบนสุดเสมอโดยไม่มีทางเลือก
     * ซึ่งเป็นสิ่งที่ผู้ใช้แจ้งว่า "เลือกไม่ได้ว่าจะลงโฟลเดอร์ไหน"
     *
     * ทางแก้คือให้ "แถวที่เลือกไว้" เป็นตัวบอกปลายทาง ซึ่งตรงกับที่คนคาดอยู่แล้ว
     * จากการใช้ File Explorer และ Finder
     *
     *   เลือกโฟลเดอร์ไว้        → ลงในโฟลเดอร์นั้น
     *   เลือกไฟล์ไว้            → ลงข้าง ๆ ไฟล์นั้น (โฟลเดอร์แม่ของมัน)
     *   เลือกหลายอันคนละแม่     → ชั้นบนสุด เพราะเดาไม่ได้ว่าหมายถึงอันไหน
     *   ไม่ได้เลือกอะไร         → ชั้นบนสุด
     */
    function uploadTarget() {
        if (!selected.length) {
            return folderId;
        }

        var rows = selected.map(function (id) {
            return nodeOf($('.cd-row[data-id="' + id + '"]'));
        }).filter(Boolean);

        if (!rows.length) {
            return folderId;
        }

        // เลือกโฟลเดอร์ใบเดียว = เจตนาชัดว่าจะลงในนั้น
        if (rows.length === 1 && rows[0].kind === 'folder') {
            return rows[0].id;
        }

        // ที่เหลือยึดโฟลเดอร์แม่ และต้องเป็นแม่เดียวกันทั้งชุดถึงจะนับ
        var parent = rows[0].parent;

        for (var i = 1; i < rows.length; i++) {
            if (rows[i].parent !== parent) {
                return folderId;
            }
        }

        return parent || folderId;
    }

    /** ชื่อโฟลเดอร์สำหรับแสดงผล — null/0 คือชั้นบนสุด */
    function folderName(id) {
        if (!id) {
            return 'คลังไฟล์ (ชั้นบนสุด)';
        }

        var $row = $('.cd-row[data-id="' + id + '"]');

        return $row.length ? String($row.data('name')) : 'คลังไฟล์ (ชั้นบนสุด)';
    }

    // ------------------------------------------------------------ อัปโหลด

    /**
     * อัปทีละไฟล์ หนึ่งคำขอต่อหนึ่งไฟล์
     *
     * ทำแบบนี้เพื่อให้แต่ละใบมีแถบความคืบหน้าของตัวเอง และไฟล์ที่ล้มไม่ลากใบอื่นล้มตาม
     * (max_file_uploads ของ PHP ปริยายคือ 20 ไฟล์ต่อคำขอ — ส่งทีละใบจึงไม่ชนเพดานนั้นด้วย)
     */
    function upload(files, targetId) {
        if (!files || !files.length) return;

        var allowed = cfg.allowedExt;
        var list = [];

        for (var i = 0; i < files.length; i++) {
            var f = files[i];
            var ext = (f.name.split('.').pop() || '').toLowerCase();

            if (allowed.indexOf(ext) === -1) {
                addRow(f.name, 'ไฟล์ชนิด .' + ext + ' อัปโหลดไม่ได้', false);
                continue;
            }

            if (f.size > cfg.maxUpload) {
                addRow(f.name, 'ใหญ่เกิน ' + cfg.maxUploadText, false);
                continue;
            }

            list.push(f);
        }

        if (!list.length) return;

        showUploads();

        list.forEach(function (f) {
            uploadQueue++;
            send(f, targetId);
        });
    }

    function showUploads() {
        $('#cd-uploads').removeClass('d-none');
    }

    function addRow(name, note, spinning) {
        var id = 'up' + Math.random().toString(36).slice(2);

        $('.cd-uploads-body').append(
            '<div class="cd-up" id="' + id + '">' +
            '<div class="d-flex justify-content-between gap-2">' +
            '<span class="cd-up-name text-truncate">' + esc(name) + '</span>' +
            '<span class="cd-up-note">' + esc(note || '') + '</span>' +
            '</div>' +
            (spinning ? '<div class="cd-up-bar"><div class="cd-up-fill" style="width:0%"></div></div>' : '') +
            '</div>'
        );

        if (!spinning) {
            $('#' + id).addClass('failed');
        }

        showUploads();

        return $('#' + id);
    }

    function send(file, targetId) {
        var $row = addRow(file.name, bytes(file.size), true);

        var form = new FormData();
        form.append('customer_id', cfg.customerId);
        form.append('user_id', cfg.userId);
        form.append('parent_id', targetId || 0);
        form.append('file', file);

        $.ajax({
            url: cfg.api + 'upload.php',
            type: 'POST',
            data: form,
            dataType: 'json',
            processData: false,
            contentType: false,
            xhr: function () {
                var xhr = new window.XMLHttpRequest();

                xhr.upload.addEventListener('progress', function (e) {
                    if (!e.lengthComputable) return;
                    var pct = Math.round((e.loaded / e.total) * 100);
                    $row.find('.cd-up-fill').css('width', pct + '%');
                    $row.find('.cd-up-note').text(pct + '%');
                });

                return xhr;
            }
        }).done(function (res) {
            if (res && res.result === '1') {
                $row.addClass('done');
                $row.find('.cd-up-fill').css('width', '100%');
                $row.find('.cd-up-note').text(res.skipped ? 'มีอยู่แล้ว' : 'สำเร็จ');
            } else {
                $row.addClass('failed');
                $row.find('.cd-up-bar').remove();
                $row.find('.cd-up-note').text((res && res.msn) || 'ล้มเหลว');
            }
        }).fail(function (xhr) {
            $row.addClass('failed');
            $row.find('.cd-up-bar').remove();
            $row.find('.cd-up-note').text(xhr.status === 413
                ? 'ใหญ่เกินกว่าเซิร์ฟเวอร์รับได้'
                : 'ส่งไม่สำเร็จ');
        }).always(function () {
            uploadQueue--;

            // วาดตารางใหม่เมื่อคิวว่างแล้วเท่านั้น ไม่ใช่ทุกครั้งที่ไฟล์เสร็จ
            if (uploadQueue <= 0) {
                uploadQueue = 0;
                reload();
            }
        });
    }

    // ------------------------------------------------------------ ลาก-วาง

    var dragDepth = 0;

    function isFileDrag(e) {
        var dt = e.originalEvent && e.originalEvent.dataTransfer;
        if (!dt || !dt.types) return false;

        for (var i = 0; i < dt.types.length; i++) {
            if (dt.types[i] === 'Files') return true;
        }

        return false;
    }

    function bindFileDrop() {
        var $veil = $('.cd-dropveil');

        /**
         * โฟลเดอร์ที่จะรับไฟล์ จากตำแหน่งเมาส์ขณะลาก
         *
         * วางบนแถวโฟลเดอร์      → เข้าโฟลเดอร์นั้น
         * วางบนแถวไฟล์          → เข้าโฟลเดอร์แม่ของไฟล์นั้น (คนเล็งไฟล์เพราะอยากวางข้าง ๆ)
         * วางที่อื่น             → ชั้นบนสุด
         */
        function dropTargetOf(e) {
            var $row = $(e.target).closest('.cd-row');

            if (!$row.length) {
                return folderId;
            }

            if (String($row.data('kind')) === 'folder') {
                return parseInt($row.data('id'), 10);
            }

            return parseInt($row.data('parent'), 10) || folderId;
        }

        /*
         * ไฮไลต์โฟลเดอร์ที่กำลังเล็ง แล้วบอกชื่อมันบนฉากคลุม
         *
         * ของเดิมฉากคลุมเขียนว่า "ลงในคลังไฟล์ (ชั้นบนสุด)" ค้างไว้ตลอดการลาก
         * ทั้งที่การวางบนแถวโฟลเดอร์เข้าโฟลเดอร์นั้นได้อยู่แล้ว — ความสามารถมีอยู่
         * แต่ไม่มีอะไรบอกผู้ใช้ว่ามันมี จึงเท่ากับไม่มี
         */
        function paint(e) {
            var target = dropTargetOf(e);

            $('.cd-row').removeClass('cd-drop-on');

            if (target) {
                $('.cd-row[data-id="' + target + '"]').addClass('cd-drop-on');
            }

            $veil.find('.cd-dropveil-target').text('ลงใน ' + folderName(target));
        }

        $(document).on('dragenter.cddrop', function (e) {
            if (!isFileDrag(e) || !cfg.perm.cdrive_upload) return;
            e.preventDefault();
            dragDepth++;
            $veil.addClass('show');
            paint(e);
        });

        $(document).on('dragover.cddrop', function (e) {
            if (!isFileDrag(e) || !cfg.perm.cdrive_upload) return;
            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'copy';
            paint(e);
        });

        $(document).on('dragleave.cddrop', function (e) {
            if (!isFileDrag(e)) return;
            dragDepth = Math.max(0, dragDepth - 1);

            if (dragDepth === 0) {
                $veil.removeClass('show');
                $('.cd-row').removeClass('cd-drop-on');
            }
        });

        $(document).on('drop.cddrop', function (e) {
            if (!isFileDrag(e) || !cfg.perm.cdrive_upload) return;
            e.preventDefault();
            dragDepth = 0;
            $veil.removeClass('show');
            $('.cd-row').removeClass('cd-drop-on');

            upload(e.originalEvent.dataTransfer.files, dropTargetOf(e));
        });
    }

    /** ลากแถวไปวางบนโฟลเดอร์หรือ breadcrumb = ย้าย */
    function bindRowDrag() {
        var dragging = [];

        $app().on('dragstart', '.cd-row', function (e) {
            pendingSingle = null;   // ลากจริงแล้ว ไม่ใช่คลิก — คงกลุ่มที่เลือกไว้ทั้งชุด

            var id = parseInt($(this).data('id'), 10);

            // ลากแถวที่ถูกเลือกอยู่ = ลากทั้งชุด ลากแถวอื่น = ลากใบเดียว
            dragging = selected.indexOf(id) !== -1 && selected.length > 1 ? selected.slice() : [id];

            e.originalEvent.dataTransfer.effectAllowed = 'move';
            e.originalEvent.dataTransfer.setData('text/plain', dragging.join(','));
            $(this).addClass('dragging');
        });

        $app().on('dragend', '.cd-row', function () {
            $(this).removeClass('dragging');
            $('.cd-drop-on').removeClass('cd-drop-on');
        });

        $app().on('dragover', '.cd-row[data-kind="folder"], .cd-crumb, .cd-empty', function (e) {
            if (!dragging.length) return;

            var id = parseInt($(this).data('id'), 10);

            // ห้ามวางบนตัวเอง
            if (id && dragging.indexOf(id) !== -1) return;

            e.preventDefault();
            e.originalEvent.dataTransfer.dropEffect = 'move';
            $(this).addClass('cd-drop-on');
        });

        $app().on('dragleave', '.cd-row, .cd-crumb, .cd-empty', function () {
            $(this).removeClass('cd-drop-on');
        });

        $app().on('drop', '.cd-row[data-kind="folder"], .cd-crumb, .cd-empty', function (e) {
            if (!dragging.length) return;

            e.preventDefault();
            e.stopPropagation();
            $(this).removeClass('cd-drop-on');

            var $t = $(this);
            var target;

            if ($t.hasClass('cd-row')) {
                target = parseInt($t.data('id'), 10);
            } else if ($t.hasClass('cd-crumb')) {
                target = $t.data('folder') || '';
            } else {
                target = folderId || '';
            }

            if (target && dragging.indexOf(parseInt(target, 10)) !== -1) return;

            move(dragging.slice(), target);
            dragging = [];
        });
    }

    function move(ids, targetId) {
        post('move.php', { node_ids: ids.join(','), target_id: targetId || 0 }).done(function (res) {
            if (handled(res)) reload();
        }).fail(function (xhr) { boom(xhr.responseText || ''); });
    }

    // ------------------------------------------------------------ ผูกเหตุการณ์

    function bind() {
        var $app_ = $app();

        // --- แถบคำสั่ง
        $app_.on('click', '[data-cd="upload-pick"]', function () { $('#cd-file-input').trigger('click'); });
        $app_.on('click', '[data-cd="mkdir"]', makeFolder);
        $app_.on('click', '[data-cd="trash"]', openTrash);
        $app_.on('click', '[data-cd="activity"]', openActivity);
        $app_.on('click', '[data-cd="links"]', openLinks);


        $('#cd-file-input').on('change', function () {
            upload(this.files, uploadTarget());
            $(this).val('');
        });

        // --- ค้นหา (หน่วงไว้ ไม่ยิงทุกตัวอักษร)
        $app_.on('input', '#cd-search', function () {
            var value = $.trim($(this).val());

            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                keyword = value;
                reload();
            }, 250);
        });

        // --- เส้นทาง
        $app_.on('click', '[data-cd="go"]', function () {
            go($(this).data('folder') || '');
        });

        // --- เลือก
        /*
         * เลือกแถวตอน mousedown ไม่ใช่ตอน click — และนี่คือเหตุผลที่สำคัญ
         *
         * แถวมี draggable="true" (เพื่อลากย้ายข้ามโฟลเดอร์) ซึ่งทำให้เบราว์เซอร์
         * ยิง dragstart แทน click ทันทีที่เมาส์ขยับเกินไม่กี่พิกเซลระหว่างกดกับปล่อย
         * ผลคือการคลิกเลือกจะ "ไม่ติด" แบบสุ่ม ขึ้นกับว่ามือนิ่งแค่ไหน
         * mousedown ยิงเสมอไม่ว่าจะเกิด drag ตามมาหรือไม่ จึงเชื่อถือได้
         *
         * (File Explorer และ Finder ก็เลือกตอน mousedown ด้วยเหตุผลเดียวกัน)
         */
        $app_.on('mousedown', '.cd-row', function (e) {
            if (e.which !== 1) return;   // ซ้ายเท่านั้น — ขวาเป็นหน้าที่ของ contextmenu
            if ($(e.target).closest('input, button, .cd-rename-input').length) return;

            var id = parseInt($(this).data('id'), 10);

            if (e.shiftKey && lastClicked !== null) {
                selected = [];
                selectRange(id);
                syncSelection();
                return;
            }

            if (e.ctrlKey || e.metaKey) {
                toggle(id);
                lastClicked = id;
                syncSelection();
                return;
            }

            /*
             * กดบนแถวที่อยู่ในกลุ่มที่เลือกอยู่แล้ว = อาจกำลังจะลากทั้งกลุ่ม
             * จึงยังไม่ยุบเหลือใบเดียวตอนนี้ รอดูตอนปล่อยว่าลากจริงหรือแค่คลิก
             */
            if (selected.indexOf(id) !== -1 && selected.length > 1) {
                pendingSingle = id;
                return;
            }

            pendingSingle = null;
            selected = [id];
            lastClicked = id;
            syncSelection();
        });

        // ปล่อยเมาส์บนแถวเดิมโดยไม่ได้ลาก = ตั้งใจเลือกใบเดียวจริง ๆ
        $app_.on('mouseup', '.cd-row', function (e) {
            if (e.which !== 1 || pendingSingle === null) return;
            if ($(e.target).closest('input, button, .cd-rename-input').length) return;

            var id = parseInt($(this).data('id'), 10);

            if (id === pendingSingle) {
                selected = [id];
                lastClicked = id;
                syncSelection();
            }

            pendingSingle = null;
        });

        // ลูกศรย่อ/ขยายหน้าโฟลเดอร์
        $app_.on('click', '[data-cd="toggle"]', function (e) {
            e.stopPropagation();
            var node = nodeOf(this);
            if (node) toggleFolder(node.id);
        });

        $app_.on('dblclick', '.cd-row', function (e) {
            if ($(e.target).closest('input, button').length) return;

            var node = nodeOf(this);
            if (!node) return;

            /*
             * ดับเบิลคลิกโฟลเดอร์ในทรี = ย่อ/ขยาย ไม่ใช่เข้าไปข้างใน
             *
             * เพราะทรีเห็นข้างในอยู่แล้ว การกระโดดเข้าไปทำให้เสียบริบทของทั้งโครงสร้าง
             * ซึ่งเป็นเหตุผลที่เลือกทรีตั้งแต่แรก — ถ้าอยากเข้าไปจริง ๆ
             * (เช่นคลังใหญ่มากจนอยากตัดให้เหลือแค่กิ่งเดียว) ใช้เมนูคลิกขวา
             */
            if (node.kind === 'folder') {
                if (node.kids) toggleFolder(node.id);
            } else if (node.preview) {
                doAction('preview', node);
            } else {
                doAction('download', node);
            }
        });

        // ดับเบิลคลิกที่ชื่อ = เปลี่ยนชื่อในที่ (แต่ต้องไม่ชนกับดับเบิลคลิกเปิดโฟลเดอร์)
        $app_.on('dblclick', '.cd-name', function (e) {
            if (!cfg.perm.cdrive_edit) return;
            e.stopPropagation();
            var node = nodeOf(this);
            if (node) startRename(node.id);
        });

        // คลิกที่ว่างนอกแถว = ล้างการเลือก (พฤติกรรมเดียวกับ Explorer/Finder)
        $app_.on('click', '.cd-table-wrap', function (e) {
            if ($(e.target).closest('.cd-row, button, input').length) return;
            clearSelection();
        });

        // --- เมนู
        $app_.on('click', '[data-cd="menu"]', function (e) {
            e.stopPropagation();
            var node = nodeOf(this);
            if (!node) return;

            var rect = this.getBoundingClientRect();
            showMenu(node, rect.left - 160, rect.bottom + window.scrollY + 4);
        });

        $app_.on('contextmenu', '.cd-row', function (e) {
            var node = nodeOf(this);
            if (!node) return;

            e.preventDefault();

            // คลิกขวาบนแถวที่ยังไม่ถูกเลือก = เลือกใบนั้นก่อน (พฤติกรรมเดียวกับ Explorer)
            if (selected.indexOf(node.id) === -1) {
                selected = [node.id];
                lastClicked = node.id;
                syncSelection();
            }

            showMenu(node, e.pageX, e.pageY);
        });

        $(document).on('click.cdmenu', function () { hideMenu(); });

        // --- ปุ่มลัด
        /*
         * ผูกที่ document เพราะตารางไม่ใช่ช่องกรอก จึงรับ focus เองไม่ได้
         * ต้องข้ามทุกกรณีที่ผู้ใช้กำลังพิมพ์อยู่จริง ๆ (ช่องค้นหา · เปลี่ยนชื่อในที่ ·
         * ช่องใน modal ของ SweetAlert) มิฉะนั้นกด Del ตอนแก้ชื่อจะกลายเป็นลบไฟล์
         */
        $(document).on('keydown.cdkeys', function (e) {
            if ($(e.target).is('input, textarea, select') || $(e.target).attr('contenteditable') === 'true') {
                return;
            }

            // มี modal เปิดอยู่ = ปุ่มลัดของตารางต้องเงียบ
            if ($('.modal.show').length || $('.swal2-container').length) {
                if (e.key === 'Escape') hideMenu();
                return;
            }

            var mod = e.ctrlKey || e.metaKey;

            if (mod && (e.key === 'a' || e.key === 'A')) {
                e.preventDefault();
                selectAll();
                return;
            }

            if (e.key === 'Escape') {
                hideMenu();
                clearSelection();
                return;
            }

            /*
             * ← → ย่อ/ขยายโฟลเดอร์ที่เลือกอยู่ (แบบเดียวกับ tree ทุกตัวในโลก)
             * → บนโฟลเดอร์ที่กางแล้ว หรือบนไฟล์ ให้เลื่อนลงแถวถัดไปแทน
             */
            if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
                var cur = selected.length ? nodeOf($('.cd-row[data-id="' + selected[selected.length - 1] + '"]')) : null;

                if (cur && cur.kind === 'folder' && cur.kids) {
                    var wantCollapsed = e.key === 'ArrowLeft';

                    if (!!collapsed[cur.id] !== wantCollapsed) {
                        e.preventDefault();
                        toggleFolder(cur.id);
                        return;
                    }
                }

                e.preventDefault();
                moveCursor(e.key === 'ArrowRight' ? 1 : -1, false);
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                moveCursor(1, e.shiftKey);
                return;
            }

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                moveCursor(-1, e.shiftKey);
                return;
            }

            if (!selected.length) return;

            var one = nodeOf($('.cd-row[data-id="' + selected[selected.length - 1] + '"]'));

            if (e.key === 'Enter' && one) {
                e.preventDefault();
                if (one.kind === 'folder') {
                    if (one.kids) toggleFolder(one.id);
                } else {
                    doAction(one.preview ? 'preview' : 'download', one);
                }
                return;
            }

            // Backspace = ถอยขึ้นโฟลเดอร์แม่ (เหมือน Explorer) — ต้องไม่ให้เบราว์เซอร์ย้อนหน้า
            if (e.key === 'Backspace') {
                e.preventDefault();
                var $up = $('.cd-crumb').not('.active').last();
                if ($up.length) go($up.data('folder') || '');
                return;
            }

            if (e.key === 'F2' && one && cfg.perm.cdrive_edit) {
                e.preventDefault();
                startRename(one.id);
                return;
            }

            if ((e.key === 'Delete' || e.key === 'Del') && cfg.perm.cdrive_delete) {
                e.preventDefault();
                confirmDelete(selected.slice(), selected.length === 1 && one ? one.name : '');
            }
        });

        $(document).on('click.cdmenuitem', '.cd-menu-item', function (e) {
            e.stopPropagation();
            var node = $('#cd-menu').data('node');
            hideMenu();
            if (node) doAction($(this).data('act'), node);
        });

        $app_.on('click', '[data-cd="versions"]', function (e) {
            e.stopPropagation();
            var node = nodeOf(this);
            if (node) openVersions(node.id);
        });

        // --- แถบอัปโหลด
        $(document).on('click.cdup', '.cd-uploads-toggle', function () {
            $('#cd-uploads').toggleClass('collapsed');
            $(this).find('.material-symbols-outlined')
                .text($('#cd-uploads').hasClass('collapsed') ? 'expand_less' : 'expand_more');
        });

        $(document).on('click.cdup', '.cd-uploads-close', function () {
            $('#cd-uploads').addClass('d-none');
            $('.cd-uploads-body').empty();
        });

        /*
         * ตาข่ายกันเหนียว — เก็บ backdrop ที่ค้างหลังปิด modal
         *
         * ถึงจะแก้ต้นเหตุที่ fillModal() แล้ว ก็ยังเหลือทางที่โค้ดอื่นหรือเทมเพลต
         * จะทิ้ง backdrop ไว้ได้ และอาการของมันคือ "หน้าจอเทาแล้วกดอะไรไม่ได้เลย"
         * ซึ่งผู้ใช้แก้เองไม่ได้นอกจากรีโหลด — ราคาของการกวาดทิ้งตรงนี้ถูกกว่ามาก
         */
        $(document).on('hidden.bs.modal.cdmodal', '.modal', function () {
            if ($('.modal.show').length) return;

            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
        });

        // --- modal (ผูกที่ document เพราะเนื้อ modal ถูกวาดใหม่ทุกครั้ง)
        $(document).on('click.cdmodal', '[data-cd="trash-restore"]', function () {
            var id = $(this).closest('[data-trash-id]').data('trash-id');

            post('restore_trash.php', { node_id: id }).done(function (res) {
                if (handled(res)) {
                    setTrashCount(res.trash_count);
                    openTrash();
                    reload();
                }
            });
        });

        $(document).on('click.cdmodal', '[data-cd="trash-purge"]', function () {
            var $row = $(this).closest('[data-trash-id]');
            var id = $row.data('trash-id');
            var name = $.trim($row.find('.cd-name').text());

            Swal.fire({
                target: swalHost($(this)),
                title: 'ลบถาวร?',
                html: esc(name) + '<br><small class="text-danger">ไฟล์ทุกเวอร์ชันจะหายไปและกู้กลับไม่ได้</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ลบถาวร',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#dc3545'
            }).then(function (r) {
                if (!r.isConfirmed) return;

                post('trash_purge.php', { node_id: id }).done(function (res) {
                    if (handled(res)) {
                        setTrashCount(res.trash_count);
                        openTrash();
                    }
                });
            });
        });

        // --- ลิงก์แชร์
        $(document).on('click.cdmodal', '[data-cd="lk-new"]', function () {
            $('.cd-linkform').removeClass('d-none');
            $('.cd-linkhead, .cd-linklist').addClass('d-none');
            // เติมรหัสสุ่มไว้ให้ตั้งแต่เปิด แต่ช่องยังแก้ได้อิสระ
            $('[data-cd="lk-random"]').trigger('click');
            $('#lk-title').trigger('focus');
        });

        $(document).on('click.cdmodal', '[data-cd="lk-cancel"]', function () {
            $('.cd-linkform').addClass('d-none');
            $('.cd-linkhead, .cd-linklist').removeClass('d-none');
        });

        $(document).on('click.cdmodal', '[data-cd="lk-random"]', function () {
            /*
             * ตัดตัวที่สับสนออก (0 O o 1 l I) เพราะรหัสนี้ถูกอ่านให้ลูกค้าฟังทางโทรศัพท์
             * หรือพิมพ์ต่อใน LINE — ตัวที่แยกไม่ออกทำให้ลูกค้ากรอกผิดแล้วโดนล็อกฟรี ๆ
             * (ชุดเดียวกับ cd_random_password() ฝั่งเซิร์ฟเวอร์)
             */
            var pool = 'abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
            var out = '';
            var rnd = new Uint32Array(6);

            window.crypto.getRandomValues(rnd);

            for (var i = 0; i < 6; i++) {
                out += pool[rnd[i] % pool.length];
            }

            $('#lk-password').val(out);
        });

        /*
         * ช่อง "ให้ลูกค้าส่งไฟล์กลับได้" มีความหมายเฉพาะโหมดแชร์
         * โหมดรับไฟล์เปิดให้อยู่แล้วเสมอ จึงซ่อนไปเลยแทนที่จะโชว์ช่องที่กดแล้วไม่มีผล
         *
         * "เพดานไฟล์" ก็เช่นกัน — ไม่มีความหมายกับลิงก์ที่ส่งไฟล์ไม่ได้
         */
        $(document).on('change.cdmodal', '#lk-mode, #lk-upload', function () {
            var share = $('#lk-mode').val() === 'share';
            var canUp = !share || $('#lk-upload').is(':checked');

            $('#lk-uprow').toggleClass('d-none', !share);
            $('#lk-max').closest('.col-md-3').toggleClass('d-none', !canUp);
        });

        $(document).on('click.cdmodal', '[data-cd="lk-copy"]', function () {
            copyText(String($(this).data('url')), $(this));
        });

        $(document).on('click.cdmodal', '[data-cd="lk-save"]', function () {
            var $btn = $(this);

            var data = {
                title: $.trim($('#lk-title').val()),
                message: $.trim($('#lk-message').val()),
                mode: $('#lk-mode').val(),
                root_node_id: $('#lk-root').val() || 0,
                password: $('#lk-password').val(),
                days: $('#lk-days').val(),
                max_uploads: $('#lk-max').val(),
                allow_download: $('#lk-mode').val() === 'share' ? '1' : '0',
                // โหมดรับไฟล์บังคับเปิดอยู่แล้วฝั่งเซิร์ฟเวอร์ ส่งอะไรมาก็ไม่มีผล
                allow_upload: $('#lk-upload').is(':checked') ? '1' : '0'
            };

            // เปลี่ยนเฉพาะข้อความ ไม่ใช่ทั้งปุ่ม ไม่งั้นไอคอนหายแล้วไม่กลับมา
            $btn.prop('disabled', true).find('.cd-btn-label').text('กำลังสร้าง…');

            post('link_create.php', data).done(function (res) {
                if (res && res.result === '1') {
                    showLinkResult(res, false);
                    $('.cd-linkform').addClass('d-none');
                    // วาดรายการลิงก์ใหม่ แต่เก็บกล่องผลลัพธ์ไว้ให้อ่านก่อน
                    var $keep = $('.cd-linkresult').clone(true);

                    html('link_list.php').done(function (markup) {
                        $('#showModalXl').html(markup);
                        $('.cd-linkresult').replaceWith($keep);
                    });
                } else {
                    warn((res && res.msn) || 'สร้างลิงก์ไม่สำเร็จ');
                }
            }).fail(function (xhr) {
                boom(xhr.responseText || '');
            }).always(function () {
                $btn.prop('disabled', false).find('.cd-btn-label').text('สร้างลิงก์');
            });
        });

        $(document).on('click.cdmodal', '[data-cd="lk-revoke"]', function () {
            var id = $(this).closest('[data-link-id]').data('link-id');

            Swal.fire({
                target: swalHost($(this)),
                title: 'เพิกถอนลิงก์นี้?',
                html: 'ลูกค้าจะเข้าไม่ได้ทันที รวมถึงคนที่เปิดหน้าค้างไว้อยู่<br>' +
                    '<small class="text-muted">ไฟล์ที่ส่งมาแล้วยังอยู่ครบ</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'เพิกถอน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#dc3545'
            }).then(function (r) {
                if (!r.isConfirmed) return;

                post('link_revoke.php', { link_id: id }).done(function (res) {
                    if (handled(res)) openLinks();
                });
            });
        });

        $(document).on('click.cdmodal', '[data-cd="lk-reset"]', function () {
            var id = $(this).closest('[data-link-id]').data('link-id');

            Swal.fire({
                target: swalHost($(this)),
                title: 'ตั้งรหัสผ่านใหม่',
                input: 'text',
                inputValue: '',
                inputPlaceholder: '6 ตัวอักษรพอดี',
                inputAttributes: { maxlength: 6, autocapitalize: 'off', autocomplete: 'off' },
                html: '<div class="fs-13 text-muted">ลิงก์เดิมยังใช้ได้ ส่งเฉพาะรหัสใหม่ให้ลูกค้า</div>',
                showCancelButton: true,
                confirmButtonText: 'ตั้งรหัสใหม่',
                cancelButtonText: 'ยกเลิก',
                // ต้องยาว 6 พอดี เพราะหน้ากรอกของแขกมีช่องตายตัว 6 ช่อง
                inputValidator: function (v) {
                    return (!v || v.length !== 6) ? 'รหัสผ่านต้องยาว 6 ตัวอักษรพอดี' : null;
                }
            }).then(function (r) {
                if (!r.isConfirmed) return;

                post('link_reset_password.php', { link_id: id, password: r.value }).done(function (res) {
                    if (res && res.result === '1') {
                        openLinks();
                        setTimeout(function () { showLinkResult(res, true); }, 400);
                    } else {
                        warn((res && res.msn) || 'ตั้งรหัสใหม่ไม่สำเร็จ');
                    }
                });
            });
        });

        $(document).on('click.cdmodal', '.cd-pick:not(.disabled)', function () {
            $('#cd-picker .cd-pick').removeClass('active');
            $(this).addClass('active');
        });

        $(document).on('click.cdmodal', '[data-cd="pick-confirm"]', function () {
            var ids = $('#showModal').data('moving') || [];
            var target = $('#cd-picker .cd-pick.active').data('folder') || '';

            bootstrap.Modal.getOrCreateInstance(document.getElementById('myModal')).hide();
            move(ids, target);
        });

        $(document).on('click.cdmodal', '[data-cd="ver-download"]', function () {
            var nodeId = $('#showModalLg').data('node-id');
            var version = $(this).closest('[data-version]').data('version');

            window.location = fileUrl('download.php', { node_id: nodeId, version: version });
        });

        $(document).on('click.cdmodal', '[data-cd="ver-restore"]', function () {
            var nodeId = $('#showModalLg').data('node-id');
            var version = $(this).closest('[data-version]').data('version');

            Swal.fire({
                target: swalHost($(this)),
                title: 'ย้อนคืน v' + version + '?',
                text: 'เวอร์ชันเก่าจะถูกคัดลอกขึ้นมาเป็นเวอร์ชันใหม่ล่าสุด ไม่มีเวอร์ชันไหนถูกลบ',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'ย้อนคืน',
                cancelButtonText: 'ยกเลิก'
            }).then(function (r) {
                if (!r.isConfirmed) return;

                post('restore_version.php', { node_id: nodeId, version: version }).done(function (res) {
                    if (handled(res)) {
                        openVersions(nodeId);
                        reload();
                    }
                });
            });
        });
    }

    // ------------------------------------------------------------ เริ่มทำงาน

    function init() {
        var raw = $('#cd-config').text();

        if (!raw) return;

        cfg = JSON.parse(raw);
        folderId = cfg.folderId;
        keyword = '';
        selected = [];

        /*
         * ติดคลาสไว้ที่ body เพื่อให้ css จับกล่อง SweetAlert ได้
         *
         * SweetAlert วาดตัวเองเป็นลูกของ body ไม่ได้อยู่ใน #cd-app จึงเอื้อมไปหา
         * ด้วย selector ปกติไม่ได้ และถ้าเขียนกฎที่ .swal2-popup ตรง ๆ
         * มันจะไปโดนทุกหน้าในระบบ เพราะ customer_drive.css ถูกโหลดจาก header.php
         * ทุกหน้า (แถบพื้นที่ดิสก์ใน sidebar ต้องใช้)
         *
         * ผูกที่ body ของหน้านี้หน้าเดียวจึงเป็นทางที่แคบที่สุดที่ยังทำงานได้
         */
        document.body.classList.add('cd-page');

        // ผูกใหม่ทุกครั้งที่ init เพราะ #cd-app ถูกวาดใหม่ทั้งก้อน
        // (event ที่ผูกไว้ที่ document ต้องถอดก่อน ไม่งั้นจะซ้อนกันทุกครั้งที่เปลี่ยนโฟลเดอร์)
        $(document).off('.cdmenu .cdmenuitem .cdkeys .cdup .cdmodal .cddrop');

        $('#cd-file-input').attr('accept', cfg.accept);

        bind();
        bindFileDrop();
        bindRowDrag();

        reload();
    }

    return { init: init, reload: reload };
})();
