/**
 * หน้ากรอกรหัสผ่านของแขก — ช่องแยกตัวละกล่องแบบรหัส OTP
 *
 * แยกเป็นไฟล์ต่างหากแทนที่จะเป็น inline script เพราะ CSP ของหน้านี้ตั้ง
 * script-src 'self' ซึ่งบล็อก inline script ทั้งหมด — เป็นเจตนา ไม่ใช่ความยุ่งยาก
 * ที่ต้องหาทางเลี่ยง เพราะ inline script คือช่องทางหลักของ XSS
 *
 * ⛔ ห้ามแตะ localStorage (ดูเหตุผลใน js/portal.js)
 *
 * สามอย่างที่ช่องแบบนี้ต้องทำให้ถูก ไม่งั้นมันน่ารำคาญกว่าช่องเดียวธรรมดา
 *   1. **วางทั้งรหัสได้** — ลูกค้าคัดลอกมาจากไลน์ ถ้าวางแล้วลงแค่กล่องแรก
 *      จะต้องมานั่งพิมพ์เองทีละตัวซึ่งแย่กว่าเดิม
 *   2. **Backspace ในกล่องว่างต้องถอยไปกล่องก่อนหน้า** ไม่ใช่ค้างอยู่ที่เดิม
 *   3. **ไม่ส่งฟอร์มเองทันทีที่ครบ** — ผู้ใช้ต้องได้ตรวจสิ่งที่พิมพ์ก่อน
 *      เพราะผิด 5 ครั้งคือโดนล็อก 15 นาที การยิงอัตโนมัติจึงเสี่ยงเกินไป
 */
(function () {
    'use strict';

    var form = document.getElementById('pt-form');

    if (!form) return;

    var wrap = document.getElementById('pt-otp');
    var boxes = Array.prototype.slice.call(wrap.querySelectorAll('.pt-otp-box'));
    var errEl = document.getElementById('pt-error');
    var btn = document.getElementById('pt-submit');

    // token อ่านจาก URL ตอนส่งเท่านั้น ไม่เก็บไว้ที่ไหนทั้งสิ้น
    var token = new URLSearchParams(window.location.search).get('t') || '';

    function code() {
        return boxes.map(function (b) { return b.value; }).join('');
    }

    function clear() {
        boxes.forEach(function (b) { b.value = ''; });
        boxes[0].focus();
    }

    /** เติมตัวอักษรลงกล่องตั้งแต่ตำแหน่ง start — ใช้ทั้งตอนพิมพ์และตอนวาง */
    function fill(start, text) {
        var chars = text.replace(/\s/g, '').split('');
        var i = start;

        while (i < boxes.length && chars.length) {
            boxes[i].value = chars.shift();
            i++;
        }

        // โฟกัสไปกล่องว่างถัดไป ถ้าเต็มหมดแล้วไปหยุดที่กล่องสุดท้าย
        var next = boxes.findIndex(function (b) { return !b.value; });
        boxes[next === -1 ? boxes.length - 1 : next].focus();
    }

    boxes.forEach(function (box, idx) {
        box.addEventListener('input', function () {
            Portal.hideError(errEl);

            // พิมพ์เร็วหรือคีย์บอร์ดมือถือส่งมาหลายตัวพร้อมกันได้
            if (box.value.length > 1) {
                var text = box.value;
                box.value = '';
                fill(idx, text);
                return;
            }

            if (box.value && idx < boxes.length - 1) {
                boxes[idx + 1].focus();
            }
        });

        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !box.value && idx > 0) {
                e.preventDefault();
                boxes[idx - 1].value = '';
                boxes[idx - 1].focus();
                return;
            }

            if (e.key === 'ArrowLeft' && idx > 0) {
                e.preventDefault();
                boxes[idx - 1].focus();
                return;
            }

            if (e.key === 'ArrowRight' && idx < boxes.length - 1) {
                e.preventDefault();
                boxes[idx + 1].focus();
            }
        });

        box.addEventListener('paste', function (e) {
            e.preventDefault();
            fill(idx, (e.clipboardData || window.clipboardData).getData('text') || '');
            Portal.hideError(errEl);
        });

        // กดกล่องไหนก็เลือกตัวที่อยู่ในนั้นทั้งตัว พิมพ์ทับได้เลย
        box.addEventListener('focus', function () { box.select(); });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        Portal.hideError(errEl);

        var pass = code();

        if (pass.length < boxes.length) {
            Portal.showError(errEl, 'กรุณากรอกรหัสให้ครบ ' + boxes.length + ' ตัว');
            (boxes.find(function (b) { return !b.value; }) || boxes[0]).focus();
            return;
        }

        btn.disabled = true;
        btn.textContent = 'กำลังตรวจสอบ…';

        Portal.post('auth', { t: token, password: pass })
            .then(function (res) {
                if (res && res.result === 1) {
                    // ไปหน้าคลังไฟล์ — ไม่ต่อ token ท้าย URL เพราะตัวตนอยู่ใน cookie แล้ว
                    window.location.href = Portal.API + 'drive';
                    return;
                }

                Portal.showError(errEl, res && res.msg);
                clear();
            })
            .catch(function () {
                Portal.showError(errEl, 'เชื่อมต่อไม่สำเร็จ กรุณาลองใหม่');
            })
            .finally(function () {
                btn.disabled = false;
                btn.textContent = 'เข้าสู่คลังไฟล์';
            });
    });

    boxes[0].focus();
})();
