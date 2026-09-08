/**
 * หน้าคลังไฟล์ของแขก — อัปโหลด ดูรายการ ลบของตัวเอง
 *
 * ⛔ ห้ามแตะ localStorage (ดูเหตุผลใน js/portal.js)
 *
 * อัปทีละไฟล์ หนึ่งคำขอต่อหนึ่งไฟล์ เพื่อให้แต่ละใบมีแถบความคืบหน้าของตัวเอง
 * และไฟล์ที่ล้มไม่ลากใบอื่นล้มตาม — ลูกค้าที่เน็ตไม่ดีจะได้ไม่ต้องเริ่มใหม่ทั้งชุด
 *
 * โครงหน้าเลียนแบบ Google Drive สามอย่าง
 *   1. ลากไฟล์มาวางที่ไหนก็ได้ทั้งหน้าจอ ไม่ต้องเล็งกล่องเล็ก ๆ
 *   2. ความคืบหน้าอยู่แผงมุมล่างขวา ไม่ดันตารางให้ขยับระหว่างอัป
 *   3. ตารางไฟล์เป็นพระเอกของหน้า ไม่ใช่ปุ่มอัปโหลด — เพราะลูกค้าใช้ลิงก์เดียว
 *      หลายรอบ สิ่งที่ต้องเห็นชัดที่สุดคือ "ส่งอะไรไปแล้วบ้าง"
 */
(function () {
    'use strict';

    var cfgEl = document.getElementById('pt-config');

    if (!cfgEl) return;

    var cfg = JSON.parse(cfgEl.textContent);
    var listEl = document.getElementById('pt-list');
    var theadEl = document.getElementById('pt-thead');
    var countEl = document.getElementById('pt-count');
    var queueEl = document.getElementById('pt-queue');
    var fileEl = document.getElementById('pt-file');
    var zoneEl = document.getElementById('pt-dropzone');
    var upEl = document.getElementById('pt-uploader');
    var upTitle = document.getElementById('pt-uploader-title');

    var pending = 0;
    var doneCount = 0;
    var dragDepth = 0;   // นับ dragenter/dragleave เพราะมันยิงซ้ำตอนลากผ่านลูก ๆ

    // ------------------------------------------------------------ รายการไฟล์

    function refresh() {
        Portal.post('list', {})
            .then(function (res) {
                if (!res || res.result !== 1) {
                    // session หมดอายุกลางคัน — กลับไปกรอกรหัสใหม่
                    // ไฟล์ที่ส่งไปแล้วอยู่ครบ ไม่หายไปไหน
                    if (res && res.expired) {
                        window.location.href = Portal.API;
                        return;
                    }

                    fail('โหลดรายการไม่สำเร็จ');
                    return;
                }

                render(res);
            })
            .catch(function () {
                fail('โหลดรายการไม่สำเร็จ');
            });
    }

    function fail(text) {
        theadEl.hidden = true;
        countEl.textContent = '';
        listEl.innerHTML = '<div class="pt-empty"><div class="pt-empty-title">' + Portal.esc(text) + '</div></div>';
    }

    function render(res) {
        var items = res.items || [];

        if (!items.length) {
            theadEl.hidden = true;
            countEl.textContent = '';
            listEl.innerHTML = '<div class="pt-empty">' +
                '<div class="pt-empty-icon" aria-hidden="true">↥</div>' +
                '<div class="pt-empty-title">' +
                (cfg.canUpload ? 'ยังไม่ได้ส่งไฟล์เข้ามา' : 'ยังไม่มีไฟล์') +
                '</div>' +
                (cfg.canUpload ? '<div class="pt-empty-sub">ลากไฟล์มาวางตรงนี้ หรือกดปุ่มอัปโหลดไฟล์ด้านบน</div>' : '') +
                '</div>';
            return;
        }

        theadEl.hidden = false;

        var note = cfg.mode === 'collect'
            ? 'ส่งไปแล้ว ' + items.length + ' ไฟล์'
            : items.length + ' รายการ';

        if (res.max > 0 && cfg.canUpload) {
            note += ' · ส่งได้อีก ' + Math.max(0, res.max - res.used) + ' ไฟล์';
        }

        countEl.textContent = note;

        var html = '';

        items.forEach(function (it) {
            html += '<div class="pt-item" data-id="' + it.id + '">' +
                '<span class="pt-item-main">' +
                Portal.fileIcon(it.kind) +
                '<span class="pt-item-text">' +
                '<span class="pt-item-name" title="' + Portal.esc(it.name) + '">' + Portal.esc(it.name) + '</span>' +
                (it.where ? '<span class="pt-item-where">ใน ' + Portal.esc(it.where) + '</span>' : '') +
                '</span>' +
                '</span>' +
                '<span class="pt-col-size">' + Portal.esc(it.size) + '</span>' +
                '<span class="pt-col-when">' + Portal.esc(it.when) + '</span>' +
                '<span class="pt-col-act">' +
                (it.canDownload ? '<a class="pt-item-act" href="' + Portal.API + 'download?node_id=' + it.id + '">ดาวน์โหลด</a>' : '') +
                (it.canRemove ? '<button class="pt-item-act pt-item-del" type="button" data-remove="' + it.id + '">ลบ</button>' : '') +
                '</span>' +
                '</div>';
        });

        listEl.innerHTML = html;
    }

    // ------------------------------------------------------------ อัปโหลด

    function enqueue(files) {
        if (!files || !files.length || !cfg.canUpload) return;

        for (var i = 0; i < files.length; i++) {
            var f = files[i];
            var e = Portal.ext(f.name);

            if (cfg.allowed.indexOf(e) === -1) {
                row(f.name, 'ไฟล์ชนิด .' + e + ' ส่งไม่ได้', 'fail');
                continue;
            }

            if (f.size > cfg.maxUpload) {
                row(f.name, 'ใหญ่เกิน ' + cfg.maxText, 'fail');
                continue;
            }

            send(f);
        }

        syncUploader();
    }

    function row(name, note, cls) {
        var el = document.createElement('div');

        el.className = 'pt-up' + (cls ? ' pt-up-' + cls : '');
        el.innerHTML = '<span class="pt-up-name">' + Portal.esc(name) + '</span>' +
            '<span class="pt-up-note">' + Portal.esc(note) + '</span>' +
            '<span class="pt-up-bar"><i></i></span>';

        queueEl.appendChild(el);
        upEl.hidden = false;
        upEl.classList.remove('pt-uploader-min');

        return el;
    }

    /** หัวแผงบอกสถานะรวม — "กำลังส่ง 3 ไฟล์" หรือ "ส่งเสร็จแล้ว 5 ไฟล์" */
    function syncUploader() {
        if (!upTitle) return;

        upTitle.textContent = pending > 0
            ? 'กำลังส่ง ' + pending + ' ไฟล์'
            : (doneCount > 0 ? 'ส่งเสร็จแล้ว ' + doneCount + ' ไฟล์' : 'ส่งไฟล์');
    }

    /*
     * ไฟล์ใหญ่กว่านี้ส่งแบบแบ่งก้อน เล็กกว่านี้ส่งทีเดียวจบ
     *
     * ไม่แบ่งทุกไฟล์เพราะการแบ่งมีต้นทุน: อย่างน้อยสามคำขอต่อไฟล์
     * (status + chunk + finish) เทียบกับหนึ่งคำขอ สำหรับไฟล์ 200 KB
     * ต้นทุนนั้นมากกว่าประโยชน์ที่ได้ทั้งหมด
     *
     * 4 MB เป็นจุดที่การส่งเริ่มใช้เวลานานพอที่เน็ตจะหลุดระหว่างทางได้จริง
     */
    var CHUNK_THRESHOLD = 4 * 1024 * 1024;

    /* ขนาดก้อนละ 1 MB — เล็กพอที่ post_max_size ของ hosting ทั่วไปรับได้แน่ */
    var CHUNK_SIZE = 1024 * 1024;

    /* ก้อนหนึ่งลองซ้ำได้กี่ครั้งก่อนยอมแพ้ */
    var CHUNK_RETRY = 3;

    /**
     * รหัสประจำการอัปโหลดหนึ่งครั้ง — ต้องเหมือนเดิมเมื่อส่งไฟล์เดิมซ้ำ
     *
     * ประกอบจาก ชื่อ + ขนาด + เวลาแก้ไขล่าสุด ซึ่งอ่านได้ทันทีโดยไม่ต้อง
     * อ่านเนื้อไฟล์ทั้งใบ (ไฟล์ 40 MB บนมือถือใช้เวลานานและกินหน่วยความจำ)
     *
     * ความเสี่ยงที่สามค่านี้ชนกันโดยที่เนื้อไฟล์ต่างกันมีน้อยมาก และถ้าชนจริง
     * ด่านตรวจขนาดตอน finish จะจับได้ก่อนไฟล์เข้าระบบ
     */
    function uploadId(file) {
        var seed = file.name + '|' + file.size + '|' + (file.lastModified || 0);
        var bytes = new TextEncoder().encode(seed);

        return crypto.subtle.digest('SHA-256', bytes).then(function (buf) {
            return Array.prototype.map.call(new Uint8Array(buf), function (b) {
                return ('0' + b.toString(16)).slice(-2);
            }).join('');
        });
    }

    /** ยิงหนึ่งคำขอไปที่ upload_chunk แล้วคืน JSON */
    function chunkApi(form) {
        return fetch(Portal.API + 'upload_chunk', {
            method: 'POST',
            body: form,
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); });
    }

    /**
     * ส่งไฟล์ทีละก้อน พร้อมต่อจากที่ค้างและลองซ้ำเมื่อก้อนใดล้ม
     *
     * เหตุผลจริงของทางนี้คือ "ต่อจากที่ค้างได้" ไม่ใช่แค่รองรับไฟล์ใหญ่ —
     * ลูกค้าที่สแกนเอกสารจากมือถือบนเน็ตที่หลุด ๆ ติด ๆ จะเสียแค่ก้อนที่ค้าง
     * ไม่ใช่เริ่มใหม่ทั้งไฟล์แล้วหลุดซ้ำไม่จบไม่สิ้น
     */
    function sendChunked(file, el, bar, note) {
        return uploadId(file).then(function (id) {
            var status = new FormData();
            status.append('action', 'status');
            status.append('upload_id', id);

            // ถามก่อนว่าเซิร์ฟเวอร์มีถึงไหนแล้ว — นี่คือหัวใจของการต่อจากที่ค้าง
            return chunkApi(status).then(function (res) {
                var offset = (res && res.result === 1) ? (res.received || 0) : 0;

                if (offset > file.size) {
                    offset = 0;   // ชิ้นส่วนเก่าที่ไม่เข้ากับไฟล์นี้ ให้เริ่มใหม่
                }

                if (offset > 0) {
                    note.textContent = 'ส่งต่อจาก ' + Math.round(offset / file.size * 100) + '%';
                }

                function step(tries) {
                    if (offset >= file.size) {
                        var fin = new FormData();
                        fin.append('action', 'finish');
                        fin.append('upload_id', id);
                        fin.append('name', file.name);
                        fin.append('size', file.size);

                        return chunkApi(fin);
                    }

                    var slice = file.slice(offset, Math.min(offset + CHUNK_SIZE, file.size));
                    var form = new FormData();

                    form.append('action', 'chunk');
                    form.append('upload_id', id);
                    form.append('offset', offset);
                    form.append('chunk', slice);

                    return chunkApi(form).then(function (res) {
                        if (res && res.result === 1) {
                            offset = res.received;
                            bar.style.width = Math.round(offset / file.size * 100) + '%';
                            note.textContent = Math.round(offset / file.size * 100) + '%';

                            return step(CHUNK_RETRY);
                        }

                        /*
                         * result 4 = ลำดับไม่ตรง เซิร์ฟเวอร์บอกมาแล้วว่าควรเริ่มที่ไหน
                         * เกิดตอนคำขอก่อนหน้าสำเร็จแต่คำตอบหายกลางทาง — กระโดดไปจุดนั้น
                         * แล้วไปต่อ ไม่นับเป็นความล้มเหลว
                         */
                        if (res && res.result === 4 && typeof res.received === 'number') {
                            offset = res.received;

                            return step(CHUNK_RETRY);
                        }

                        return Promise.reject(new Error((res && res.msg) || 'ส่งไม่สำเร็จ'));
                    }).catch(function (e) {
                        if (tries > 1) {
                            // หน่วงก่อนลองใหม่ ให้เน็ตที่เพิ่งสะดุดมีเวลาตั้งตัว
                            return new Promise(function (r) { setTimeout(r, 1200); })
                                .then(function () { return step(tries - 1); });
                        }

                        throw e;
                    });
                }

                return step(CHUNK_RETRY);
            });
        });
    }

    function send(file) {
        var el = row(file.name, Portal.bytes(file.size), '');
        var bar = el.querySelector('.pt-up-bar i');
        var note = el.querySelector('.pt-up-note');

        pending++;
        syncUploader();

        // ไฟล์ใหญ่ไปทางแบ่งก้อน ที่เหลือส่งทีเดียวแบบเดิม
        if (file.size > CHUNK_THRESHOLD && window.crypto && crypto.subtle) {
            sendChunked(file, el, bar, note)
                .then(function (res) { finishRow(el, bar, note, res); })
                .catch(function (e) {
                    el.className = 'pt-up pt-up-fail';
                    note.textContent = (e && e.message) || 'ส่งไม่สำเร็จ กรุณาลองใหม่';
                })
                .then(done, done);

            return;
        }

        var form = new FormData();

        form.append('file', file);

        var xhr = new XMLHttpRequest();

        xhr.open('POST', Portal.API + 'upload');
        xhr.withCredentials = true;

        xhr.upload.addEventListener('progress', function (e) {
            if (!e.lengthComputable) return;

            var pct = Math.round((e.loaded / e.total) * 100);
            bar.style.width = pct + '%';
            note.textContent = pct + '%';
        });

        xhr.addEventListener('load', function () {
            var res = null;

            try { res = JSON.parse(xhr.responseText); } catch (e) { res = null; }

            finishRow(el, bar, note, res);
            done();
        });

        xhr.addEventListener('error', function () {
            el.className = 'pt-up pt-up-fail';
            note.textContent = 'ส่งไม่สำเร็จ กรุณาลองใหม่';
            done();
        });

        xhr.send(form);
    }

    /** วาดผลลัพธ์ของหนึ่งไฟล์ — ใช้ร่วมกันทั้งทางส่งทีเดียวและทางแบ่งก้อน */
    function finishRow(el, bar, note, res) {
        if (res && res.result === 1) {
            el.className = 'pt-up pt-up-done';
            bar.style.width = '100%';
            note.textContent = res.skipped ? 'ได้รับแล้ว' : (res.renamed ? 'ส่งแล้ว (เปลี่ยนชื่อกันซ้ำ)' : 'ส่งแล้ว');
            doneCount++;
            return;
        }

        el.className = 'pt-up pt-up-fail';
        note.textContent = (res && res.msg) || 'ส่งไม่สำเร็จ';

        if (res && res.expired) {
            window.location.href = Portal.API;
        }
    }

    function done() {
        pending--;

        if (pending <= 0) {
            pending = 0;
            refresh();
        }

        syncUploader();
    }

    // ------------------------------------------------------------ ผูกเหตุการณ์

    if (cfg.canUpload) {
        document.getElementById('pt-pick').addEventListener('click', function () {
            fileEl.click();
        });

        fileEl.addEventListener('change', function () {
            enqueue(this.files);
            this.value = '';
        });

        /*
         * ลากไฟล์มาวางที่ไหนก็ได้ทั้งหน้าจอ
         *
         * dragenter/dragleave ยิงซ้ำทุกครั้งที่เมาส์ผ่านลูก ๆ ของ element
         * ถ้าเปิด/ปิดฉากคลุมตรง ๆ มันจะกะพริบทั้งหน้าจอตลอดเวลาที่ลาก
         * จึงนับความลึกเอาไว้ แล้วปิดเมื่อกลับมาเป็นศูนย์เท่านั้น
         */
        window.addEventListener('dragenter', function (e) {
            if (!e.dataTransfer) return;
            if (Array.prototype.indexOf.call(e.dataTransfer.types || [], 'Files') === -1) return;

            e.preventDefault();
            dragDepth++;
            zoneEl.hidden = false;
        });

        window.addEventListener('dragover', function (e) {
            if (zoneEl.hidden) return;

            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        });

        window.addEventListener('dragleave', function () {
            dragDepth = Math.max(0, dragDepth - 1);

            if (dragDepth === 0) {
                zoneEl.hidden = true;
            }
        });

        window.addEventListener('drop', function (e) {
            dragDepth = 0;
            zoneEl.hidden = true;

            // กันเบราว์เซอร์เปิดไฟล์แทนที่จะอัปให้เรา
            e.preventDefault();
            enqueue(e.dataTransfer && e.dataTransfer.files);
        });
    }

    listEl.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-remove]');

        if (!btn) return;

        var id = btn.getAttribute('data-remove');
        var name = btn.closest('.pt-item').querySelector('.pt-item-name').textContent;

        if (!window.confirm('ลบ "' + name + '" ออกจากรายการที่ส่งไปแล้ว?')) return;

        btn.disabled = true;

        Portal.post('remove_own', { node_id: id })
            .then(function (res) {
                if (res && res.expired) {
                    window.location.href = Portal.API;
                    return;
                }

                refresh();
            })
            .catch(function () {
                btn.disabled = false;
            });
    });

    var toggleBtn = document.getElementById('pt-uploader-toggle');
    var closeBtn = document.getElementById('pt-uploader-close');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            upEl.classList.toggle('pt-uploader-min');
            toggleBtn.textContent = upEl.classList.contains('pt-uploader-min') ? '▴' : '▾';
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            upEl.hidden = true;
            queueEl.innerHTML = '';
            doneCount = 0;
        });
    }

    document.getElementById('pt-leave').addEventListener('click', function () {
        // ปล่อยให้เซิร์ฟเวอร์เป็นคนล้าง cookie — JS อ่าน cookie นี้ไม่ได้อยู่แล้ว (HttpOnly)
        window.location.href = Portal.API + 'logout';
    });

    refresh();
})();
