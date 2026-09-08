<?php

/**
 * หน้าแขก (portal) — จุดเดียวในระบบทั้งหมดที่คนนอกเข้าถึงได้โดยไม่ต้องล็อกอิน
 * ไม่มี checkAuth() ในไฟล์นี้เลย จงใจ — ผู้เรียกคือลูกค้า ไม่ใช่พนักงาน
 *
 * ported from _export_customer_drive/files/portal/**
 */
class PortalController
{
    private const CSP = "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; "
        . "font-src 'self'; img-src 'self' data:; connect-src 'self'; form-action 'self'; "
        . "frame-ancestors 'none'; base-uri 'none'";

    private function guestHeaders(bool $full = false): void
    {
        header('Referrer-Policy: no-referrer');

        if ($full) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: DENY');
            header('Content-Security-Policy: ' . self::CSP);
        }
    }

    /////////////////////////////////////// index ///////////////////////////////////////////////
    public function index()
    {
        require_once '../app/models/customer_drive/cd_functions.php';
        $this->guestHeaders(true);

        $token = (string) ($_GET['t'] ?? '');
        $link  = cd_link_by_token($token);
        $state = $link ? cd_link_state($link) : 'missing';

        $lockMinutes = 0;

        if ($state === 'locked') {
            $lockMinutes = max(1, (int) ceil((strtotime((string) $link['locked_until']) - time()) / 60));
        }

        $usable = $state === '';
        $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

        require_once '../app/views/portal/index.php';
    }

    /////////////////////////////////////// auth ///////////////////////////////////////////////
    public function authenticate()
    {
        require_once '../app/models/customer_drive/cd_functions.php';
        $this->guestHeaders();

        $token = (string) ($_POST['t'] ?? '');
        $pass  = (string) ($_POST['password'] ?? '');
        $label = ''; // ไม่มีชื่อผู้ส่งแล้ว — ลูกค้ากรอกแค่รหัส

        /*
         * ล้มเหลวแบบเดียวกันเสมอ พร้อมหน่วงเวลาให้เท่ากันทุกเส้นทาง
         *
         * $verified บอกว่าเส้นทางนี้ได้รัน password_verify() ไปแล้วหรือยัง —
         * ถ้ายัง ต้องรันกับ hash หลอกให้เสียเวลาเท่ากัน กัน timing attack ที่ใช้
         * เวลาตอบกลับแยกว่า "ไม่มีลิงก์" (เร็ว) กับ "รหัสผิด" (ช้าเพราะต้อง verify)
         */
        $authDeny = static function (bool $verified = false): void {
            if (!$verified) {
                password_verify('x', '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG');
            }

            usleep(400000);
            cd_json(['result' => 0, 'msg' => CD_GUEST_DENY]);
        };

        if ($pass === '') {
            // อันนี้บอกตรง ๆ ได้ เพราะไม่ได้เผยอะไรเกี่ยวกับตัวลิงก์เลย
            cd_json(['result' => 0, 'msg' => 'กรุณากรอกรหัสผ่าน']);
        }

        $link = cd_link_by_token($token);

        if (!$link) {
            $authDeny();
        }

        $state = cd_link_state($link);

        if ($state === 'locked') {
            $minutes = max(1, (int) ceil((strtotime((string) $link['locked_until']) - time()) / 60));

            usleep(400000);
            cd_json(['result' => 0, 'msg' => 'กรุณาลองใหม่ในอีก ' . $minutes . ' นาที']);
        }

        if ($state !== '') {
            $authDeny();
        }

        // ------------------------------------------------------------ ตรวจรหัสผ่าน

        if (!password_verify($pass, (string) $link['password_hash'])) {
            $fails = (int) $link['fail_count'] + 1;

            try {
                if ($fails % CD_LINK_FAIL_LOCK === 0) {
                    cd_query(
                        "UPDATE tbl_cd_link
                         SET fail_count = ?, locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE)
                         WHERE link_id = ?",
                        [$fails, CD_LINK_LOCK_MINUTES, (int) $link['link_id']]
                    );
                } else {
                    cd_query("UPDATE tbl_cd_link SET fail_count = ? WHERE link_id = ?", [$fails, (int) $link['link_id']]);
                }
            } catch (Throwable $e) {
                error_log('portal auth fail_count: ' . $e->getMessage());
            }

            cd_activity(
                (int) $link['customer_id'],
                'link_auth_fail',
                null,
                'กรอกรหัสผิดครั้งที่ ' . $fails,
                'guest',
                null,
                $label,
                (int) $link['link_id']
            );

            if ($fails % CD_LINK_FAIL_LOCK === 0) {
                usleep(400000);
                cd_json(['result' => 0, 'msg' => 'กรุณาลองใหม่ในอีก ' . CD_LINK_LOCK_MINUTES . ' นาที']);
            }

            $authDeny(true);
        }

        // ------------------------------------------------------------ ผ่านแล้ว

        try {
            cd_query(
                "UPDATE tbl_cd_link
                 SET fail_count = 0, locked_until = NULL, last_open_datetime = NOW()
                 WHERE link_id = ?",
                [(int) $link['link_id']]
            );
        } catch (Throwable $e) {
            error_log('portal auth reset: ' . $e->getMessage());
        }

        // ใช้ชื่อเรื่องของลิงก์เป็นป้ายกำกับ เพราะไม่มีชื่อคนให้ใช้แล้ว
        $label = mb_substr((string) $link['title'], 0, 120, 'UTF-8');
        $first = (string) $link['notified_first_open'] !== '1';

        cd_guest_start($link, $label);

        cd_activity(
            (int) $link['customer_id'],
            'link_open',
            null,
            $first ? 'เปิดลิงก์ครั้งแรก' : 'เปิดลิงก์',
            'guest',
            null,
            $label,
            (int) $link['link_id']
        );

        if ($first) {
            try {
                cd_query(
                    "UPDATE tbl_cd_link SET notified_first_open = '1'
                     WHERE link_id = ? AND notified_first_open <> '1'",
                    [(int) $link['link_id']]
                );
            } catch (Throwable $e) {
                error_log('portal auth flag: ' . $e->getMessage());
            }
        }

        cd_ok(['msg' => 'เข้าสู่คลังไฟล์แล้ว']);
    }

    /////////////////////////////////////// drive ///////////////////////////////////////////////
    public function drive()
    {
        require_once '../app/models/customer_drive/cd_functions.php';
        $this->guestHeaders(true);

        // ตรวจ session แบบไม่ใช้ cd_guest_guard() เพราะตัวนั้นตอบ JSON แล้ว exit
        // หน้านี้ต้องเด้งกลับไปหน้ากรอกรหัสแทน
        $token = (string) ($_COOKIE[CD_GUEST_COOKIE] ?? '');
        $link = null;

        if ($token !== '') {
            $row = cd_query(
                "SELECT s.*, l.token AS link_token FROM tbl_cd_link_session s
                 JOIN tbl_cd_link l ON l.link_id = s.link_id
                 WHERE s.session_token = ?",
                [$token]
            )->fetch();

            if ($row) {
                $expires = strtotime((string) $row['expires_datetime']);
                $created = strtotime((string) $row['create_datetime']);

                if ($expires >= time() && ($created + CD_GUEST_MAX_AGE) >= time()) {
                    $candidate = cd_link_by_id_any((int) $row['link_id']);

                    if ($candidate && cd_link_state($candidate) === '') {
                        $link = $candidate;
                    }
                }
            }
        }

        if (!$link) {
            header('Location: ' . BASE_URL . '/portal');
            exit;
        }

        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $maxUpload = cd_max_upload_bytes();

        require_once '../app/views/portal/drive.php';
    }

    /////////////////////////////////////// list ///////////////////////////////////////////////
    public function list()
    {
        require_once '../app/models/customer_drive/cd_functions.php';
        $this->guestHeaders();

        [$link, $session] = cd_guest_guard();

        $items = cd_guest_items($link, $session);
        $canDownload = (string) $link['allow_download'] === '1';

        $folderNames = [];

        foreach ($items as $item) {
            if ($item['kind'] === 'folder') {
                $folderNames[(int) $item['node_id']] = (string) $item['name'];
            }
        }

        $scopeId = cd_guest_target($link);

        $out = [];

        foreach ($items as $item) {
            if ($item['kind'] === 'folder') {
                continue;
            }

            $ext = (string) ($item['ext'] ?? '');
            $mine = $item['source'] === 'guest'
                && (int) ($item['source_link_id'] ?? 0) === (int) $link['link_id'];

            $parentId = $item['parent_id'] === null ? null : (int) $item['parent_id'];
            $where = ($parentId !== null && $parentId !== $scopeId && isset($folderNames[$parentId]))
                ? $folderNames[$parentId]
                : '';

            $out[] = [
                'id'          => (int) $item['node_id'],
                'name'        => (string) $item['name'],
                'folder'      => false,
                'kind'        => cd_file_kind($ext),
                'size'        => cd_format_bytes((int) $item['size']),
                'when'        => cd_time($item['create_datetime']),
                'where'       => $where,
                'canRemove'   => $mine,
                'canDownload' => $canDownload,
            ];
        }

        cd_ok([
            'items' => $out,
            'used'  => (int) $link['used_uploads'],
            'max'   => $link['max_uploads'] === null ? 0 : (int) $link['max_uploads'],
        ]);
    }

    /////////////////////////////////////// download ///////////////////////////////////////////////
    public function download()
    {
        require_once '../app/models/customer_drive/cd_functions.php';
        $this->guestHeaders();

        [$link, $session] = cd_guest_guard();

        $stop = static function (int $code, string $msg): void {
            http_response_code($code);
            header('Content-Type: text/plain; charset=utf-8');
            header('Referrer-Policy: no-referrer');
            echo $msg;
            exit;
        };

        if ($link['mode'] !== 'share' || (string) $link['allow_download'] !== '1') {
            $stop(403, 'ลิงก์นี้ไม่ได้เปิดให้ดาวน์โหลด');
        }

        $node_id = (int) ($_GET['node_id'] ?? 0);
        $node = cd_guest_can_see($link, $session, $node_id);

        if (!$node || $node['kind'] !== 'file') {
            $stop(404, 'ไม่พบไฟล์');
        }

        $path = cd_current_path($node);

        if (!is_file($path)) {
            $stop(404, 'ไม่พบไฟล์');
        }

        $name = (string) $node['name'];

        cd_activity(
            (int) $link['customer_id'],
            'download',
            $node_id,
            $name,
            'guest',
            null,
            (string) ($session['guest_label'] ?? ''),
            (int) $link['link_id']
        );

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . cd_mime((string) $node['ext']));
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: no-referrer');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Content-Disposition: attachment; filename="' . rawurlencode($name) . '"'
            . "; filename*=UTF-8''" . rawurlencode($name));

        readfile($path);
        exit;
    }

    /////////////////////////////////////// remove_own ///////////////////////////////////////////////
    public function removeOwn()
    {
        require_once '../app/models/customer_drive/cd_functions.php';
        $this->guestHeaders();

        [$link, $session] = cd_guest_guard();

        $node_id = (int) ($_POST['node_id'] ?? 0);
        $customer_id = (int) $link['customer_id'];
        $label = (string) ($session['guest_label'] ?? '');

        $node = cd_guest_can_see($link, $session, $node_id);

        if (!$node || $node['kind'] !== 'file') {
            cd_fail('ไม่พบไฟล์นี้');
        }

        // ต้องเป็นไฟล์ที่เข้ามาทางลิงก์ใบนี้จริง ๆ ไม่ใช่แค่มองเห็นได้
        $mine = $node['source'] === 'guest'
            && (int) ($node['source_link_id'] ?? 0) === (int) $link['link_id'];

        if (!$mine) {
            cd_fail('ลบได้เฉพาะไฟล์ที่ส่งเข้ามาทางลิงก์นี้');
        }

        try {
            cd_query(
                "UPDATE tbl_cd_node SET delete_datetime = NOW(), delete_by = NULL
                 WHERE customer_id = ? AND node_id = ? AND delete_datetime IS NULL",
                [$customer_id, $node_id]
            );
        } catch (Throwable $e) {
            error_log('portal remove: ' . $e->getMessage());
            cd_fail('ลบไม่สำเร็จ กรุณาลองใหม่');
        }

        cd_activity($customer_id, 'delete', $node_id, (string) $node['name'], 'guest', null, $label, (int) $link['link_id']);

        cd_ok(['msg' => 'ลบแล้ว']);
    }

    /////////////////////////////////////// upload ///////////////////////////////////////////////
    public function upload()
    {
        require_once '../app/models/customer_drive/cd_functions.php';
        $this->guestHeaders();

        [$link, $session] = cd_guest_guard();

        if ((string) $link['allow_upload'] !== '1') {
            cd_fail('ลิงก์นี้ไม่ได้เปิดให้ส่งไฟล์');
        }

        $storageError = cd_storage_error();

        if ($storageError !== '') {
            error_log('portal upload: ' . $storageError);
            cd_fail('ระบบรับไฟล์ขัดข้องชั่วคราว กรุณาติดต่อผู้ตรวจสอบบัญชีของท่าน');
        }

        if (!$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            cd_fail('ไฟล์ใหญ่เกินกว่าที่ระบบรับได้ (สูงสุด ' . cd_format_bytes(cd_max_upload_bytes()) . ')');
        }

        $file = $_FILES['file'] ?? null;

        if (!$file || !is_array($file)) {
            cd_fail('ไม่พบไฟล์ที่ส่งมา');
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $reason = match ((int) $file['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE
                    => 'ไฟล์ใหญ่เกินกว่าที่ระบบรับได้ (สูงสุด ' . cd_format_bytes(cd_max_upload_bytes()) . ')',
                UPLOAD_ERR_PARTIAL => 'ไฟล์ถูกส่งมาไม่ครบ กรุณาลองใหม่',
                UPLOAD_ERR_NO_FILE => 'ไม่พบไฟล์ที่ส่งมา',
                default            => 'ส่งไฟล์ไม่สำเร็จ กรุณาลองใหม่',
            };

            cd_fail($reason);
        }

        $tmp = (string) $file['tmp_name'];

        if (!is_uploaded_file($tmp)) {
            cd_fail('ไฟล์ที่ส่งมาไม่ถูกต้อง');
        }

        // ตั้งแต่ตรงนี้ใช้ตรรกะร่วมกับ uploadChunk() — ทั้งสองทางต่างกันแค่ "ไฟล์มาถึงยังไง"
        cd_guest_accept($link, $session, $tmp, (string) ($file['name'] ?? ''));
    }

    /////////////////////////////////////// upload_chunk ///////////////////////////////////////////////
    public function uploadChunk()
    {
        require_once '../app/models/customer_drive/cd_functions.php';
        $this->guestHeaders();

        [$link, $session] = cd_guest_guard();

        if ((string) $link['allow_upload'] !== '1') {
            cd_fail('ลิงก์นี้ไม่ได้เปิดให้ส่งไฟล์');
        }

        $storageError = cd_storage_error();

        if ($storageError !== '') {
            error_log('portal upload_chunk: ' . $storageError);
            cd_fail('ระบบรับไฟล์ขัดข้องชั่วคราว กรุณาติดต่อผู้ตรวจสอบบัญชีของท่าน');
        }

        $action = (string) ($_POST['action'] ?? '');
        $rawId  = (string) ($_POST['upload_id'] ?? '');

        // upload_id มาจากฝั่งเบราว์เซอร์ จึงเชื่อไม่ได้ว่าเป็นอะไร — บังคับ hex 64 ตัว
        // แล้วผูกกับ link_id ของรอบนี้อีกชั้น กันไม่ให้ใครเดา id ของคนอื่น
        if (!preg_match('/^[0-9a-f]{64}$/', $rawId)) {
            cd_fail('รหัสการอัปโหลดไม่ถูกต้อง');
        }

        $uploadId = hash('sha256', $rawId . '|' . (int) $link['link_id']);
        $part = cd_chunk_path($uploadId);

        // ------------------------------------------------------------ ถามว่ามีถึงไหนแล้ว

        if ($action === 'status') {
            cd_chunk_sweep();
            cd_ok(['received' => is_file($part) ? (int) filesize($part) : 0]);
        }

        // ------------------------------------------------------------ รับก้อน

        if ($action === 'chunk') {
            $offset = (int) ($_POST['offset'] ?? -1);
            $have = is_file($part) ? (int) filesize($part) : 0;

            if (!$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
                cd_fail('ก้อนข้อมูลใหญ่เกินกว่าเซิร์ฟเวอร์รับได้');
            }

            $file = $_FILES['chunk'] ?? null;

            if (!$file || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                cd_fail('ไม่พบก้อนข้อมูลที่ส่งมา');
            }

            $tmp = (string) $file['tmp_name'];

            if (!is_uploaded_file($tmp)) {
                cd_fail('ก้อนข้อมูลไม่ถูกต้อง');
            }

            // ปฏิเสธถ้าลำดับไม่ตรง แล้วบอกไปด้วยว่าควรเริ่มที่ไหน (result=4 = สัญญาณ
            // "ลำดับไม่ตรง ให้กระโดดไปจุดนี้" ไม่ใช่ความล้มเหลวจริง — โปรโตคอลภายในของ
            // ฟีเจอร์นี้เท่านั้น ฝั่ง JS อ่าน res.result === 4 คู่กับ res.received)
            if ($offset !== $have) {
                cd_json([
                    'result'   => 4,
                    'msg'      => 'ลำดับข้อมูลไม่ตรง กำลังส่งต่อจากจุดที่ถูกต้อง',
                    'received' => $have,
                ]);
            }

            $max = cd_max_upload_bytes();

            if ($have + (int) $file['size'] > $max) {
                @unlink($part);
                cd_fail('ไฟล์ใหญ่เกิน ' . cd_format_bytes($max));
            }

            $in = @fopen($tmp, 'rb');
            $out = @fopen($part, $have === 0 ? 'wb' : 'ab');

            if (!$in || !$out) {
                if ($in) { fclose($in); }
                if ($out) { fclose($out); }

                error_log('portal upload_chunk: เปิดไฟล์เพื่อต่อก้อนไม่ได้ ' . $part);
                cd_fail('บันทึกข้อมูลไม่สำเร็จ กรุณาลองใหม่');
            }

            // ล็อกไฟล์ระหว่างเขียน — เบราว์เซอร์อาจยิงซ้ำตอนเน็ตกลับมา
            if (!flock($out, LOCK_EX)) {
                fclose($in);
                fclose($out);
                cd_fail('มีการส่งซ้อนกัน กรุณาลองใหม่');
            }

            stream_copy_to_stream($in, $out);

            fflush($out);
            flock($out, LOCK_UN);
            fclose($out);
            fclose($in);

            clearstatcache(true, $part);

            cd_ok(['received' => (int) filesize($part)]);
        }

        // ------------------------------------------------------------ ประกอบเสร็จ

        if ($action === 'finish') {
            $name = (string) ($_POST['name'] ?? '');
            $size = (int) ($_POST['size'] ?? 0);

            if (!is_file($part)) {
                cd_fail('ไม่พบข้อมูลที่ส่งมา กรุณาส่งใหม่');
            }

            clearstatcache(true, $part);
            $have = (int) filesize($part);

            if ($size <= 0 || $have !== $size) {
                @unlink($part);

                cd_json([
                    'result' => 4,
                    'msg'    => 'ข้อมูลไม่ครบ (ได้ ' . cd_format_bytes($have) . ' จาก ' . cd_format_bytes($size) . ') กรุณาส่งใหม่',
                ]);
            }

            // cd_guest_accept() คัดลอกไฟล์ไปที่เก็บจริงแล้วจบคำขอเอง ชิ้นส่วนจึงต้องถูกลบ
            // "หลัง" เสมอไม่ว่าทางไหน — ลงทะเบียนลบตอนจบสคริปต์ ทำงานทุกเส้นทางรวมถึง cd_fail()
            register_shutdown_function(static function () use ($part): void {
                @unlink($part);
            });

            cd_guest_accept($link, $session, $part, $name);
        }

        cd_fail('คำสั่งไม่ถูกต้อง');
    }

    /////////////////////////////////////// logout ///////////////////////////////////////////////
    public function logout()
    {
        require_once '../app/models/customer_drive/cd_functions.php';
        $this->guestHeaders();

        $token = (string) ($_COOKIE[CD_GUEST_COOKIE] ?? '');

        if ($token !== '') {
            try {
                cd_query("DELETE FROM tbl_cd_link_session WHERE session_token = ?", [$token]);
            } catch (Throwable $e) {
                error_log('portal logout: ' . $e->getMessage());
            }
        }

        cd_guest_cookie('', 0);

        header('Location: ' . BASE_URL . '/portal');
        exit;
    }
}
