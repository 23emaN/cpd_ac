/**
 * ตัวช่วยร่วมของหน้าแขก (portal) — ไม่มีที่พึ่งภายนอกเลยโดยตั้งใจ
 *
 * หน้านี้เป็นจุดเดียวในระบบทั้งหมดที่คนนอกเข้าถึงได้โดยไม่ต้องล็อกอิน
 * จึงห้ามแตะ localStorage (กันไม่ให้สคริปต์ที่หลุดเข้ามาอ่านข้อมูลของพนักงาน
 * ถ้าเผื่อวันหนึ่งหน้านี้โดน XSS) และไม่โหลดฟอนต์ไอคอนจากภายนอกเลย —
 * ไอคอนไฟล์ใช้ตัวอักษรนามสกุลในกล่องสีแทน (ดู Portal.fileIcon)
 */
window.Portal = (function () {
    'use strict';

    var escDiv = document.createElement('div');

    function esc(text) {
        escDiv.textContent = text == null ? '' : String(text);
        return escDiv.innerHTML;
    }

    function bytes(n) {
        if (!n || n <= 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB'];
        var i = Math.min(Math.floor(Math.log(n) / Math.log(1024)), units.length - 1);
        return (Math.round((n / Math.pow(1024, i)) * 10) / 10) + ' ' + units[i];
    }

    function ext(filename) {
        var m = /\.([a-z0-9]+)$/i.exec(String(filename || ''));
        return m ? m[1].toLowerCase() : '';
    }

    /** กล่องสีพร้อมตัวย่อนามสกุล — ไม่พึ่งฟอนต์ไอคอนใด ๆ เลย */
    function fileIcon(kind) {
        var label = {
            image: 'รูป', pdf: 'PDF', sheet: 'XLS', doc: 'DOC',
            archive: 'ZIP', text: 'TXT'
        }[kind] || 'FILE';

        return '<span class="pt-file-icon pt-file-icon-' + esc(kind || 'file') + '">' + esc(label) + '</span>';
    }

    function showError(el, msg) {
        if (!el) return;
        el.textContent = msg || 'เกิดข้อผิดพลาด กรุณาลองใหม่';
        el.hidden = false;
    }

    function hideError(el) {
        if (!el) return;
        el.hidden = true;
    }

    /** POST แบบฟอร์มธรรมดา คืน Promise<object|null> ของ JSON ที่ตอบกลับ */
    function post(path, data) {
        var body = new URLSearchParams();

        Object.keys(data || {}).forEach(function (k) {
            body.append(k, data[k]);
        });

        return fetch(Portal.API + path, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            body: body
        }).then(function (r) {
            return r.json().catch(function () { return null; });
        });
    }

    // อ่านจาก data-api ของ <script> แท็กตัวเอง แทนการรับค่าจาก inline script
    // ที่หน้าเว็บฝัง — CSP ของหน้าแขก (script-src 'self') บล็อก inline script
    // ทุกก้อนโดยตั้งใจ (กัน XSS) จึงต้องส่งค่าผ่าน attribute ของ markup แทน
    var selfScript = document.currentScript;
    var api = selfScript ? (selfScript.getAttribute('data-api') || '') : '';

    return {
        API: api,
        post: post,
        esc: esc,
        bytes: bytes,
        ext: ext,
        fileIcon: fileIcon,
        showError: showError,
        hideError: hideError
    };
})();
