<?php

class CustomerDriveController
{
    private $userPayload;

    private function checkAuth()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();

        if (!$user) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        $this->userPayload = $user;
    }

    /////////////////////////////////////// index ///////////////////////////////////////////////
    public function index()
    {
        $this->checkAuth();

        $fiscal_id = $_SESSION['fiscal_year_id'] ?? null;

        if (!$fiscal_id) {
            header("Location: " . BASE_URL . "/main");
            exit();
        }

        require_once '../app/models/CompanyModel.php';
        $companyModel = new CompanyModel();
        $userId       = $this->userPayload['user_id'] ?? null;
        $companies    = $companyModel->getAllCompanies($userId);

        // หา active_company_id และ active_fiscal_year จาก fiscal_id ที่ใช้งานอยู่
        $active_company_id  = '';
        $active_fiscal_year = '';
        foreach ($companies as $company) {
            if (isset($company['fiscal_years'])) {
                foreach ($company['fiscal_years'] as $fy) {
                    $fy_id = $fy['fiscal_id'] ?? $fy['id'] ?? '';
                    if ($fy_id == $fiscal_id) {
                        $active_company_id  = $company['company_id'] ?? $company['id'] ?? '';
                        $active_fiscal_year = $fy['fiscal_years'] ?? $fy['working_year'] ?? $fy['year'] ?? '';
                        break 2;
                    }
                }
            }
        }

        $data = [
            'title'              => 'คลังไฟล์ลูกค้า',
            'user'               => $this->userPayload,
            'user_id'            => $this->userPayload['user_id'] ?? '',
            'firstname'          => $this->userPayload['user_firstname'] ?? '',
            'lastname'           => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin'     => $this->userPayload['is_super_admin'] ?? '0',
            'fiscal_id'          => $fiscal_id,
            'companies'          => $companies,
            'active_company_id'  => $active_company_id,
            'active_fiscal_year' => $active_fiscal_year,
        ];

        require_once '../app/views/backoffice/customer_drive/index.php';
    }

    /////////////////////////////////////// load_page ///////////////////////////////////////////////
    public function loadPage()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId       = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id  = (int) ($_POST['customer_id'] ?? 0);
        $folder_id    = (int) ($_POST['folder_id'] ?? 0);
        $isSuperAdmin = ($this->userPayload['is_super_admin'] ?? '0') === '1';

        require_once '../app/views/backoffice/customer_drive/load_page.php';
    }

    /////////////////////////////////////// render_table ///////////////////////////////////////////////
    public function renderTable()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $folder_id   = (int) ($_POST['folder_id'] ?? 0);
        $keyword     = trim((string) ($_POST['keyword'] ?? ''));

        require_once '../app/views/backoffice/customer_drive/render_table.php';
    }

    /////////////////////////////////////// mkdir ///////////////////////////////////////////////
    public function mkdir()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $parent_id   = (int) ($_POST['parent_id'] ?? 0);
        $name        = cd_safe_name((string) ($_POST['name'] ?? ''));

        cd_guard($customer_id);

        if ($name === '') {
            cd_fail('กรุณาตั้งชื่อโฟลเดอร์');
        }

        $parent = null;

        if ($parent_id > 0) {
            $parent = cd_folder_at($customer_id, $parent_id);

            if (!$parent) {
                cd_fail('ไม่พบโฟลเดอร์ปลายทาง');
            }
        }

        $parentId = $parent ? (int) $parent['node_id'] : null;

        if (cd_find_by_name($customer_id, $parentId, $name)) {
            cd_json(['result' => 0, 'msg' => 'มีชื่อนี้อยู่แล้วในโฟลเดอร์นี้']);
        }

        $pdo = cd_pdo();
        $pdo->beginTransaction();

        try {
            cd_query(
                "INSERT INTO tbl_cd_node
                    (customer_id, parent_id, kind, name, list_order, source, create_by)
                 VALUES (?, ?, 'folder', ?, ?, 'staff', ?)",
                [$customer_id, $parentId, $name, cd_next_sort($customer_id, $parentId), $userId]
            );

            $node_id = (int) $pdo->lastInsertId();

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            cd_fail('สร้างโฟลเดอร์ไม่สำเร็จ: ' . $e->getMessage());
        }

        cd_activity($customer_id, 'create_folder', $node_id, $name, 'staff', $userId);

        cd_ok(['msg' => 'สร้างโฟลเดอร์แล้ว', 'node_id' => $node_id]);
    }

    /////////////////////////////////////// upload ///////////////////////////////////////////////
    public function upload()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $parent_id   = (int) ($_POST['parent_id'] ?? 0);

        cd_guard($customer_id);

        $storageError = cd_storage_error();

        if ($storageError !== '') {
            cd_fail($storageError);
        }

        if (!$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            cd_fail('ไฟล์ใหญ่เกินกว่าที่เซิร์ฟเวอร์รับได้ (สูงสุด ' . cd_format_bytes(cd_max_upload_bytes()) . ')');
        }

        $file = $_FILES['file'] ?? null;

        if (!$file || !is_array($file)) {
            cd_fail('ไม่พบไฟล์ที่ส่งมา');
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $reason = match ((int) $file['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE
                    => 'ไฟล์ใหญ่เกินกว่าที่เซิร์ฟเวอร์รับได้ (สูงสุด ' . cd_format_bytes(cd_max_upload_bytes()) . ')',
                UPLOAD_ERR_PARTIAL   => 'ไฟล์ถูกส่งมาไม่ครบ กรุณาลองใหม่',
                UPLOAD_ERR_NO_FILE   => 'ไม่พบไฟล์ที่ส่งมา',
                UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE
                    => 'เซิร์ฟเวอร์เขียนไฟล์ชั่วคราวไม่ได้ กรุณาแจ้งผู้ดูแลระบบ',
                default => 'อัปโหลดไม่สำเร็จ (รหัส ' . (int) $file['error'] . ')',
            };

            cd_fail($reason);
        }

        $tmp = (string) $file['tmp_name'];

        if (!is_uploaded_file($tmp)) {
            cd_fail('ไฟล์ที่ส่งมาไม่ถูกต้อง');
        }

        $size = (int) ($file['size'] ?? 0);
        $max  = cd_max_upload_bytes();

        if ($size <= 0) {
            cd_fail('ไฟล์ว่างเปล่า');
        }

        if ($size > $max) {
            cd_fail('ไฟล์ใหญ่เกิน ' . cd_format_bytes($max));
        }

        $original = cd_safe_name((string) ($file['name'] ?? ''));
        $ext      = cd_ext_of($original);

        if ($original === '') {
            cd_fail('ชื่อไฟล์ไม่ถูกต้อง');
        }

        if ($ext === '' || !in_array($ext, cd_allowed_ext(), true)) {
            cd_fail('ไฟล์ชนิดนี้อัปโหลดไม่ได้ — รองรับเฉพาะ ' . implode(', ', cd_allowed_ext()));
        }

        if (!cd_mime_matches($tmp, $ext)) {
            cd_fail('เนื้อไฟล์ไม่ตรงกับนามสกุล .' . $ext . ' — ไฟล์อาจเสียหายหรือถูกเปลี่ยนนามสกุลมา');
        }

        $parent = null;

        if ($parent_id > 0) {
            $parent = cd_folder_at($customer_id, $parent_id);

            if (!$parent) {
                cd_fail('ไม่พบโฟลเดอร์ปลายทาง');
            }
        }

        $parentId = $parent ? (int) $parent['node_id'] : null;

        // ------------------------------------------------------------ ชื่อซ้ำ = เวอร์ชันใหม่

        $existing = cd_find_by_name($customer_id, $parentId, $original);

        if ($existing) {
            if ($existing['kind'] !== 'file') {
                cd_json(['result' => 0, 'msg' => 'มีโฟลเดอร์ชื่อนี้อยู่แล้วในโฟลเดอร์นี้']);
            }

            if ((string) $existing['ext'] !== $ext) {
                cd_json(['result' => 0, 'msg' => 'มีไฟล์ชื่อนี้อยู่แล้วแต่คนละนามสกุล กรุณาเปลี่ยนชื่อก่อน']);
            }

            $sha = hash_file('sha256', $tmp) ?: '';

            if ($sha !== '' && $sha === (string) $existing['sha256']) {
                cd_ok([
                    'msg'     => 'ไฟล์นี้อยู่ในคลังอยู่แล้ว (เนื้อหาเหมือนเดิม)',
                    'node_id' => (int) $existing['node_id'],
                    'skipped' => true,
                ]);
            }

            $result = cd_commit_version($existing, $tmp, 'staff_upload', $userId);

            if (!$result['ok']) {
                cd_fail('บันทึกเวอร์ชันใหม่ไม่สำเร็จ: ' . $result['msn']);
            }

            cd_activity(
                $customer_id,
                'upload_version',
                (int) $existing['node_id'],
                $original . ' (v' . $result['version'] . ')',
                'staff',
                $userId
            );

            cd_ok([
                'msg'     => 'บันทึกเป็นเวอร์ชัน v' . $result['version'] . ' แล้ว',
                'node_id' => (int) $existing['node_id'],
                'version' => $result['version'],
            ]);
        }

        // ------------------------------------------------------------ ไฟล์ใบใหม่

        $pdo = cd_pdo();
        $pdo->beginTransaction();

        try {
            cd_query(
                "INSERT INTO tbl_cd_node
                    (customer_id, parent_id, kind, name, ext, list_order, source, create_by)
                 VALUES (?, ?, 'file', ?, ?, ?, 'staff', ?)",
                [$customer_id, $parentId, $original, $ext, cd_next_sort($customer_id, $parentId), $userId]
            );

            $node_id = (int) $pdo->lastInsertId();

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            cd_fail('สร้างรายการไฟล์ไม่สำเร็จ: ' . $e->getMessage());
        }

        $node = cd_find_node($customer_id, $node_id);
        $result = cd_commit_version($node, $tmp, 'staff_upload', $userId);

        if (!$result['ok']) {
            cd_forget_files($customer_id, $node_id);
            cd_query("DELETE FROM tbl_cd_node WHERE customer_id = ? AND node_id = ?", [$customer_id, $node_id]);

            cd_fail('บันทึกไฟล์ไม่สำเร็จ: ' . $result['msn']);
        }

        cd_activity($customer_id, 'upload', $node_id, $original, 'staff', $userId);

        cd_ok(['msg' => 'อัปโหลดสำเร็จ', 'node_id' => $node_id, 'version' => $result['version']]);
    }

    /////////////////////////////////////// download ///////////////////////////////////////////////
    public function download()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_GET['customer_id'] ?? 0);
        $node_id     = (int) ($_GET['node_id'] ?? 0);
        $version     = (int) ($_GET['version'] ?? 0);
        $inline      = ($_GET['mode'] ?? '') === 'inline';

        $stop = static function (int $code, string $msg): void {
            http_response_code($code);
            header('Content-Type: text/plain; charset=utf-8');
            echo $msg;
            exit;
        };

        if (!cd_customer($customer_id)) {
            $stop(403, 'ไม่มีสิทธิ์เปิดไฟล์นี้');
        }

        $node = cd_find_node($customer_id, $node_id);

        if (!$node || $node['kind'] !== 'file') {
            $stop(404, 'ไม่พบไฟล์');
        }

        $ext = (string) $node['ext'];

        if ($inline && !cd_is_previewable($ext)) {
            $stop(400, 'ไฟล์ชนิดนี้เปิดดูในเว็บไม่ได้ กรุณาดาวน์โหลด');
        }

        $path = $version > 0 ? cd_version_path($node, $version) : cd_current_path($node);

        if (!is_file($path)) {
            $stop(404, 'ไม่พบไฟล์บนเซิร์ฟเวอร์ — อาจถูกลบไปแล้วหรือยังอัปโหลดไม่สำเร็จ');
        }

        $name = (string) $node['name'];

        if ($version > 0) {
            $base = $ext !== '' ? mb_substr($name, 0, mb_strlen($name, 'UTF-8') - mb_strlen($ext, 'UTF-8') - 1, 'UTF-8') : $name;
            $name = $base . ' (v' . $version . ')' . ($ext !== '' ? '.' . $ext : '');
        }

        cd_activity($customer_id, $inline ? 'preview' : 'download', $node_id, $name, 'staff', $userId);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . cd_mime($ext));
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment')
            . '; filename="' . rawurlencode($name) . '"'
            . "; filename*=UTF-8''" . rawurlencode($name));

        if ($inline) {
            header("Content-Security-Policy: sandbox; default-src 'none'; object-src 'self'; plugin-types application/pdf;");
        }

        readfile($path);
        exit;
    }

    /////////////////////////////////////// download_zip ///////////////////////////////////////////////
    public function downloadZip()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_GET['customer_id'] ?? 0);
        $raw         = (string) ($_GET['node_ids'] ?? '');

        $stop = static function (int $code, string $msg): void {
            http_response_code($code);
            header('Content-Type: text/plain; charset=utf-8');
            echo $msg;
            exit;
        };

        $customer = cd_customer($customer_id);

        if (!$customer) {
            $stop(403, 'ไม่มีสิทธิ์ดาวน์โหลดจากคลังนี้');
        }

        if (!class_exists('ZipArchive')) {
            $stop(500, 'เซิร์ฟเวอร์ไม่มีส่วนขยาย zip — ดาวน์โหลดทีละไฟล์แทนได้');
        }

        $node_ids = array_values(array_unique(array_filter(
            array_map('intval', explode(',', $raw)),
            static fn ($id) => $id > 0
        )));

        if (!$node_ids) {
            $stop(400, 'ยังไม่ได้เลือกรายการ');
        }

        $byId = [];
        $byParent = [];

        foreach (cd_all_nodes($customer_id) as $n) {
            $byId[(int) $n['node_id']] = $n;
            $byParent[(int) ($n['parent_id'] ?? 0)][] = $n;
        }

        $collect = static function (array $node, string $prefix) use (&$collect, $byParent): array {
            $out = [];
            $name = (string) $node['name'];

            if ($node['kind'] === 'file') {
                $path = cd_current_path($node);

                if (is_file($path)) {
                    $out[] = ['path' => $prefix . $name, 'file' => $path];
                }

                return $out;
            }

            foreach ($byParent[(int) $node['node_id']] ?? [] as $child) {
                $out = array_merge($out, $collect($child, $prefix . $name . '/'));
            }

            if (!$out) {
                $out[] = ['path' => $prefix . $name . '/', 'file' => ''];
            }

            return $out;
        };

        $entries = [];

        foreach ($node_ids as $id) {
            if (!isset($byId[$id])) {
                continue;
            }

            $entries = array_merge($entries, $collect($byId[$id], ''));
        }

        if (!$entries) {
            $stop(404, 'ไม่พบไฟล์ที่จะดาวน์โหลด');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'cdzip');

        if ($tmp === false) {
            $stop(500, 'สร้างไฟล์ชั่วคราวไม่ได้');
        }

        register_shutdown_function(static function () use ($tmp): void {
            if (is_file($tmp)) {
                @unlink($tmp);
            }
        });

        $zip = new ZipArchive();

        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            $stop(500, 'สร้างไฟล์ zip ไม่ได้');
        }

        $used = [];

        foreach ($entries as $entry) {
            if ($entry['file'] === '') {
                $zip->addEmptyDir(rtrim($entry['path'], '/'));
                continue;
            }

            $path = $entry['path'];
            $i = 2;

            while (isset($used[$path])) {
                $ext = cd_ext_of($entry['path']);
                $base = $ext !== '' ? substr($entry['path'], 0, -(strlen($ext) + 1)) : $entry['path'];
                $path = $base . ' (' . $i++ . ')' . ($ext !== '' ? '.' . $ext : '');
            }

            $used[$path] = true;
            $zip->addFile($entry['file'], $path);
        }

        $zip->close();

        $label = count($node_ids) === 1 && isset($byId[$node_ids[0]])
            ? (string) $byId[$node_ids[0]]['name']
            : $customer['customer_name'];

        $filename = cd_safe_name($label) . '.zip';

        cd_activity($customer_id, 'download', null, 'ดาวน์โหลด zip: ' . $filename . ' (' . count($entries) . ' รายการ)', 'staff', $userId);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/zip');
        header('Content-Length: ' . filesize($tmp));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"'
            . "; filename*=UTF-8''" . rawurlencode($filename));

        readfile($tmp);
        exit;
    }

    /////////////////////////////////////// delete ///////////////////////////////////////////////
    public function delete()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $raw         = (string) ($_POST['node_ids'] ?? '');

        cd_guard($customer_id);

        $node_ids = array_values(array_unique(array_filter(
            array_map('intval', explode(',', $raw)),
            static fn ($id) => $id > 0
        )));

        if (!$node_ids) {
            cd_fail('ยังไม่ได้เลือกรายการที่จะลบ');
        }

        $now = cd_now();
        $deleted = 0;
        $names = [];

        $pdo = cd_pdo();
        $pdo->beginTransaction();

        try {
            foreach ($node_ids as $id) {
                $node = cd_find_node($customer_id, $id);

                if (!$node) {
                    continue;
                }

                $ids = cd_descendant_ids($customer_id, $id);
                $ids[] = $id;

                foreach (array_unique($ids) as $target) {
                    cd_query(
                        "UPDATE tbl_cd_node
                         SET delete_datetime = ?, delete_by = ?
                         WHERE customer_id = ? AND node_id = ? AND delete_datetime IS NULL",
                        [$now, $userId, $customer_id, $target]
                    );
                }

                $deleted++;
                $names[] = (string) $node['name'];
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            cd_fail('ทิ้งลงถังขยะไม่สำเร็จ: ' . $e->getMessage());
        }

        if ($deleted === 0) {
            cd_fail('ไม่พบรายการที่จะลบ');
        }

        cd_activity($customer_id, 'delete', null, implode(', ', array_slice($names, 0, 5)), 'staff', $userId);

        cd_ok([
            'msg'         => 'ทิ้ง ' . $deleted . ' รายการลงถังขยะแล้ว',
            'trash_count' => cd_trash_count($customer_id),
        ]);
    }

    /////////////////////////////////////// move ///////////////////////////////////////////////
    public function move()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $target_id   = (int) ($_POST['target_id'] ?? 0);
        $raw         = (string) ($_POST['node_ids'] ?? '');

        cd_guard($customer_id);

        $node_ids = array_values(array_unique(array_filter(
            array_map('intval', explode(',', $raw)),
            static fn ($id) => $id > 0
        )));

        if (!$node_ids) {
            cd_fail('ยังไม่ได้เลือกรายการที่จะย้าย');
        }

        $target = null;

        if ($target_id > 0) {
            $target = cd_folder_at($customer_id, $target_id);

            if (!$target) {
                cd_fail('ปลายทางต้องเป็นโฟลเดอร์');
            }
        }

        $targetId = $target ? (int) $target['node_id'] : null;

        $moved = 0;
        $renamed = [];

        $pdo = cd_pdo();
        $pdo->beginTransaction();

        try {
            foreach ($node_ids as $id) {
                $node = cd_find_node($customer_id, $id);

                if (!$node) {
                    throw new Exception('ไม่พบรายการบางรายการในคลังไฟล์นี้');
                }

                $currentParent = $node['parent_id'] === null ? null : (int) $node['parent_id'];

                if ($currentParent === $targetId) {
                    continue;
                }

                if ($node['kind'] === 'folder' && cd_is_descendant($customer_id, $id, $targetId)) {
                    throw new Exception('ย้ายโฟลเดอร์ "' . $node['name'] . '" เข้าไปในตัวเองหรือโฟลเดอร์ย่อยของตัวเองไม่ได้');
                }

                $name = cd_unique_name($customer_id, $targetId, (string) $node['name']);

                if ($name !== (string) $node['name']) {
                    $renamed[] = $node['name'] . ' → ' . $name;
                }

                cd_query(
                    "UPDATE tbl_cd_node
                     SET parent_id = ?, name = ?, list_order = ?, update_by = ?
                     WHERE customer_id = ? AND node_id = ?",
                    [$targetId, $name, cd_next_sort($customer_id, $targetId), $userId, $customer_id, $id]
                );

                $moved++;
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            cd_json(['result' => 0, 'msg' => $e->getMessage()]);
        }

        if ($moved === 0) {
            cd_ok(['msg' => 'รายการที่เลือกอยู่ในโฟลเดอร์นี้อยู่แล้ว']);
        }

        $where = $target ? $target['name'] : 'คลังไฟล์ (ชั้นบนสุด)';

        cd_activity($customer_id, 'move', null, 'ย้าย ' . $moved . ' รายการไปที่ ' . $where, 'staff', $userId);

        $msg = 'ย้าย ' . $moved . ' รายการแล้ว';

        if ($renamed) {
            $msg .= ' · เปลี่ยนชื่อเพราะชนกับของเดิม: ' . implode(', ', array_slice($renamed, 0, 3));

            if (count($renamed) > 3) {
                $msg .= ' และอีก ' . (count($renamed) - 3) . ' รายการ';
            }
        }

        cd_ok(['msg' => $msg, 'moved' => $moved]);
    }

    /////////////////////////////////////// rename ///////////////////////////////////////////////
    public function rename()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $node_id     = (int) ($_POST['node_id'] ?? 0);
        $name        = cd_safe_name((string) ($_POST['name'] ?? ''));

        cd_guard($customer_id);

        $node = cd_guard_node($customer_id, $node_id);

        if ($name === '') {
            cd_fail('กรุณาตั้งชื่อ');
        }

        if ($name === (string) $node['name']) {
            cd_ok(['msg' => 'ชื่อเดิม ไม่มีอะไรเปลี่ยน']);
        }

        if ($node['kind'] === 'file') {
            $ext = (string) $node['ext'];

            if ($ext !== '' && cd_ext_of($name) !== $ext) {
                $name = cd_safe_name($name . '.' . $ext);
            }
        }

        $parentId = $node['parent_id'] === null ? null : (int) $node['parent_id'];

        if (cd_find_by_name($customer_id, $parentId, $name, $node_id)) {
            cd_json(['result' => 0, 'msg' => 'มีชื่อนี้อยู่แล้วในโฟลเดอร์นี้']);
        }

        $old = (string) $node['name'];

        try {
            cd_query(
                "UPDATE tbl_cd_node SET name = ?, update_by = ? WHERE customer_id = ? AND node_id = ?",
                [$name, $userId, $customer_id, $node_id]
            );
        } catch (Throwable $e) {
            cd_fail('เปลี่ยนชื่อไม่สำเร็จ: ' . $e->getMessage());
        }

        cd_activity($customer_id, 'rename', $node_id, $old . ' → ' . $name, 'staff', $userId);

        cd_ok(['msg' => 'เปลี่ยนชื่อแล้ว', 'name' => $name]);
    }

    /////////////////////////////////////// folder_tree ///////////////////////////////////////////////
    public function folderTree()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $raw         = (string) ($_POST['node_ids'] ?? '');

        require_once '../app/views/backoffice/customer_drive/folder_tree.php';
    }

    /////////////////////////////////////// render_trash ///////////////////////////////////////////////
    public function renderTrash()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);

        require_once '../app/views/backoffice/customer_drive/render_trash.php';
    }

    /////////////////////////////////////// restore_trash ///////////////////////////////////////////////
    public function restoreTrash()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $node_id     = (int) ($_POST['node_id'] ?? 0);

        cd_guard($customer_id);

        $node = cd_find_trashed($customer_id, $node_id);

        if (!$node) {
            cd_fail('ไม่พบรายการนี้ในถังขยะ');
        }

        $stamp = (string) $node['delete_datetime'];
        $restored = 0;
        $orphaned = false;

        $pdo = cd_pdo();
        $pdo->beginTransaction();

        try {
            $ids = cd_descendant_ids($customer_id, $node_id, true);
            $ids[] = $node_id;

            foreach (array_unique($ids) as $id) {
                $row = cd_query(
                    "SELECT delete_datetime FROM tbl_cd_node WHERE customer_id = ? AND node_id = ?",
                    [$customer_id, $id]
                )->fetch();

                if (!$row || $row['delete_datetime'] === null) {
                    continue;
                }

                if ((string) $row['delete_datetime'] !== $stamp) {
                    continue;
                }

                cd_query(
                    "UPDATE tbl_cd_node SET delete_datetime = NULL, delete_by = NULL, update_by = ?
                     WHERE customer_id = ? AND node_id = ?",
                    [$userId, $customer_id, $id]
                );

                $restored++;
            }

            $parentId = $node['parent_id'] === null ? null : (int) $node['parent_id'];

            if ($parentId !== null && !cd_find_node($customer_id, $parentId)) {
                cd_query(
                    "UPDATE tbl_cd_node SET parent_id = NULL, list_order = ? WHERE customer_id = ? AND node_id = ?",
                    [cd_next_sort($customer_id, null), $customer_id, $node_id]
                );

                $orphaned = true;
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            cd_fail('กู้คืนไม่สำเร็จ: ' . $e->getMessage());
        }

        cd_activity($customer_id, 'restore', $node_id, (string) $node['name'], 'staff', $userId);

        $msg = 'กู้คืน "' . $node['name'] . '" แล้ว';

        if ($restored > 1) {
            $msg .= ' (รวมของข้างใน ' . ($restored - 1) . ' รายการ)';
        }

        if ($orphaned) {
            $msg .= ' · โฟลเดอร์เดิมยังอยู่ในถังขยะ จึงย้ายไปไว้ที่ชั้นบนสุดของคลัง';
        }

        cd_ok(['msg' => $msg, 'trash_count' => cd_trash_count($customer_id)]);
    }

    /////////////////////////////////////// trash_purge ///////////////////////////////////////////////
    public function trashPurge()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $raw         = (string) ($_POST['node_id'] ?? '');

        cd_guard($customer_id);

        $targets = [];

        if ($raw === 'all') {
            foreach (cd_trash_items($customer_id) as $item) {
                $targets[] = $item;
            }

            if (!$targets) {
                cd_fail('ถังขยะว่างอยู่แล้ว');
            }
        } else {
            $node = cd_find_trashed($customer_id, (int) $raw);

            if (!$node) {
                cd_fail('ไม่พบรายการนี้ในถังขยะ');
            }

            $targets[] = $node;
        }

        // ตรวจลิงก์แชร์ที่ชี้มาก่อนลงมือ — fk_cd_link_root เป็น RESTRICT โดยตั้งใจ
        $blocking = [];

        foreach ($targets as $node) {
            $ids = cd_descendant_ids($customer_id, (int) $node['node_id'], true);
            $ids[] = (int) $node['node_id'];

            $blocking = array_merge($blocking, cd_links_pointing_at($customer_id, array_values(array_unique($ids))));
        }

        if ($blocking) {
            $blocking = array_values(array_unique($blocking));

            cd_json([
                'result' => 0,
                'msg'    => 'ลบถาวรไม่ได้ เพราะยังมีลิงก์แชร์ชี้มาที่โฟลเดอร์นี้: '
                    . implode(', ', array_slice($blocking, 0, 3))
                    . (count($blocking) > 3 ? ' และอีก ' . (count($blocking) - 3) . ' ลิงก์' : '')
                    . ' — เพิกถอนลิงก์เหล่านั้นก่อนแล้วลองใหม่',
            ]);
        }

        $names = [];
        $doomed = [];

        $pdo = cd_pdo();
        $pdo->beginTransaction();

        try {
            foreach ($targets as $node) {
                $doomed = array_merge($doomed, cd_purge_node($customer_id, (int) $node['node_id']));
                $names[] = (string) $node['name'];
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            error_log('cd trash_purge: ' . $e->getMessage());
            cd_fail('ลบถาวรไม่สำเร็จ กรุณาลองใหม่');
        }

        $purged = count(array_unique($doomed));

        foreach (array_unique($doomed) as $id) {
            cd_forget_files($customer_id, (int) $id);
        }

        cd_activity(
            $customer_id,
            'purge',
            null,
            'ลบถาวร: ' . implode(', ', array_slice($names, 0, 5)) . (count($names) > 5 ? ' และอีก ' . (count($names) - 5) . ' รายการ' : ''),
            'staff',
            $userId
        );

        cd_ok([
            'msg'         => 'ลบถาวรแล้ว ' . $purged . ' รายการ',
            'trash_count' => cd_trash_count($customer_id),
        ]);
    }

    /////////////////////////////////////// versions ///////////////////////////////////////////////
    public function versions()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $node_id     = (int) ($_POST['node_id'] ?? 0);

        require_once '../app/views/backoffice/customer_drive/versions.php';
    }

    /////////////////////////////////////// restore_version ///////////////////////////////////////////////
    public function restoreVersion()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $node_id     = (int) ($_POST['node_id'] ?? 0);
        $version     = (int) ($_POST['version'] ?? 0);

        cd_guard($customer_id);

        $node = cd_guard_node($customer_id, $node_id);

        if ($node['kind'] !== 'file') {
            cd_fail('โฟลเดอร์ไม่มีเวอร์ชัน');
        }

        if ($version <= 0) {
            cd_fail('ไม่ได้ระบุเวอร์ชัน');
        }

        if ($version === (int) $node['version']) {
            cd_ok(['msg' => 'เวอร์ชันนี้เป็นเวอร์ชันล่าสุดอยู่แล้ว']);
        }

        $source = cd_version_path($node, $version);

        if (!is_file($source)) {
            cd_fail('ไม่พบไฟล์ของเวอร์ชัน v' . $version . ' บนเซิร์ฟเวอร์');
        }

        $result = cd_commit_version($node, $source, 'restore', $userId);

        if (!$result['ok']) {
            cd_fail('ย้อนคืนไม่สำเร็จ: ' . $result['msn']);
        }

        cd_activity(
            $customer_id,
            'upload_version',
            $node_id,
            $node['name'] . ' — ย้อนคืน v' . $version . ' เป็น v' . $result['version'],
            'staff',
            $userId
        );

        cd_ok(['msg' => 'ย้อนคืน v' . $version . ' ขึ้นมาเป็น v' . $result['version'] . ' แล้ว', 'version' => $result['version']]);
    }

    /////////////////////////////////////// activity ///////////////////////////////////////////////
    public function activity()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId      = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id = (int) ($_POST['customer_id'] ?? 0);

        require_once '../app/views/backoffice/customer_drive/activity.php';
    }

    /////////////////////////////////////// link_list ///////////////////////////////////////////////
    public function linkList()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId       = (int) ($this->userPayload['user_id'] ?? 0);
        $customer_id  = (int) ($_POST['customer_id'] ?? 0);
        $isSuperAdmin = ($this->userPayload['is_super_admin'] ?? '0') === '1';

        require_once '../app/views/backoffice/customer_drive/link_list.php';
    }

    /////////////////////////////////////// link_create ///////////////////////////////////////////////
    public function linkCreate()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId       = (int) ($this->userPayload['user_id'] ?? 0);
        $isSuperAdmin = ($this->userPayload['is_super_admin'] ?? '0') === '1';

        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $title       = cd_safe_name((string) ($_POST['title'] ?? ''));
        $message     = trim((string) ($_POST['message'] ?? ''));
        $mode        = ($_POST['mode'] ?? 'collect') === 'share' ? 'share' : 'collect';
        $root_id     = (int) ($_POST['root_node_id'] ?? 0);
        $password    = (string) ($_POST['password'] ?? '');
        $days        = (int) ($_POST['days'] ?? 0);
        $maxUploads  = (int) ($_POST['max_uploads'] ?? 0);
        $allowDl     = ($_POST['allow_download'] ?? '0') === '1';
        $allowUp     = ($_POST['allow_upload'] ?? '0') === '1';

        cd_guard($customer_id, true, $isSuperAdmin);

        if ($title === '') {
            cd_fail('กรุณาตั้งชื่อเรื่องของลิงก์ เช่น "ขอเอกสารปิดงบ 2568"');
        }

        if (mb_strlen($password, 'UTF-8') !== CD_LINK_PASSWORD_LEN) {
            cd_fail('รหัสผ่านต้องยาว ' . CD_LINK_PASSWORD_LEN . ' ตัวอักษรพอดี');
        }

        $root = null;

        if ($root_id > 0) {
            $root = cd_folder_at($customer_id, $root_id);

            if (!$root) {
                cd_fail('โฟลเดอร์ปลายทางต้องเป็นโฟลเดอร์ในคลังของลูกค้ารายนี้');
            }
        }

        $days = $days > 0 ? min($days, 365) : CD_LINK_DEFAULT_DAYS;
        $maxUploads = $maxUploads > 0 ? min($maxUploads, 10000) : CD_LINK_DEFAULT_MAX_UPLOADS;

        // โหมด collect ห้ามดาวน์โหลดเด็ดขาด ไม่ว่าฝั่งหน้าเว็บจะส่งอะไรมา
        $allowDownload = ($mode === 'share' && $allowDl) ? '1' : '0';
        // โหมด collect บังคับอัปโหลด '1' เสมอ ส่วนโหมด share ค่าตั้งต้นคืออ่านอย่างเดียว
        $allowUpload = $mode === 'collect' ? '1' : ($allowUp ? '1' : '0');

        $token = cd_token();

        $pdo = cd_pdo();
        $pdo->beginTransaction();

        try {
            cd_query(
                "INSERT INTO tbl_cd_link
                    (customer_id, token, title, message, mode, root_node_id,
                     allow_upload, allow_download, password_hash, expires_datetime,
                     max_uploads, create_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY), ?, ?)",
                [
                    $customer_id,
                    $token,
                    mb_substr($title, 0, 200, 'UTF-8'),
                    $message !== '' ? mb_substr($message, 0, 2000, 'UTF-8') : null,
                    $mode,
                    $root ? (int) $root['node_id'] : null,
                    $allowUpload,
                    $allowDownload,
                    password_hash($password, PASSWORD_DEFAULT),
                    $days,
                    $maxUploads,
                    $userId,
                ]
            );

            $link_id = (int) $pdo->lastInsertId();

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            cd_fail('สร้างลิงก์ไม่สำเร็จ: ' . $e->getMessage());
        }

        cd_activity($customer_id, 'link_create', $root ? (int) $root['node_id'] : null, $title, 'staff', $userId, null, $link_id);

        cd_ok([
            'msg'      => 'สร้างลิงก์แล้ว',
            'link_id'  => $link_id,
            'url'      => cd_portal_url($token),
            // ครั้งเดียวที่รหัสผ่านออกจากเซิร์ฟเวอร์เป็นข้อความธรรมดา
            'password' => $password,
        ]);
    }

    /////////////////////////////////////// link_revoke ///////////////////////////////////////////////
    public function linkRevoke()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $userId       = (int) ($this->userPayload['user_id'] ?? 0);
        $isSuperAdmin = ($this->userPayload['is_super_admin'] ?? '0') === '1';
        $customer_id  = (int) ($_POST['customer_id'] ?? 0);
        $link_id      = (int) ($_POST['link_id'] ?? 0);

        cd_guard($customer_id, true, $isSuperAdmin);

        $link = cd_link_by_id($customer_id, $link_id);

        if (!$link) {
            cd_fail('ไม่พบลิงก์นี้');
        }

        if ((string) $link['revoked'] === '1') {
            cd_ok(['msg' => 'ลิงก์นี้ถูกเพิกถอนไปแล้ว']);
        }

        $pdo = cd_pdo();
        $pdo->beginTransaction();

        try {
            cd_query(
                "UPDATE tbl_cd_link SET revoked = '1' WHERE customer_id = ? AND link_id = ?",
                [$customer_id, $link_id]
            );

            // ตัดรอบเข้าใช้ที่ยังค้าง — เพิกถอนแล้วต้องเข้าไม่ได้เดี๋ยวนี้ ไม่ใช่รออีกสองชั่วโมง
            cd_query("DELETE FROM tbl_cd_link_session WHERE link_id = ?", [$link_id]);

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            cd_fail('เพิกถอนไม่สำเร็จ: ' . $e->getMessage());
        }

        cd_activity($customer_id, 'link_revoke', null, (string) $link['title'], 'staff', $userId, null, $link_id);

        cd_ok(['msg' => 'เพิกถอนลิงก์แล้ว ลูกค้าเข้าไม่ได้อีก']);
    }

    /////////////////////////////////////// link_reset_password ///////////////////////////////////////////////
    public function linkResetPassword()
    {
        $this->checkAuth();
        require_once '../app/models/customer_drive/cd_functions.php';

        $isSuperAdmin = ($this->userPayload['is_super_admin'] ?? '0') === '1';
        $customer_id  = (int) ($_POST['customer_id'] ?? 0);
        $link_id      = (int) ($_POST['link_id'] ?? 0);
        $password     = (string) ($_POST['password'] ?? '');

        cd_guard($customer_id, true, $isSuperAdmin);

        $link = cd_link_by_id($customer_id, $link_id);

        if (!$link) {
            cd_fail('ไม่พบลิงก์นี้');
        }

        if ((string) $link['revoked'] === '1') {
            cd_json(['result' => 0, 'msg' => 'ลิงก์นี้ถูกเพิกถอนแล้ว ตั้งรหัสใหม่ไม่ได้ — สร้างลิงก์ใหม่แทน']);
        }

        if (mb_strlen($password, 'UTF-8') !== CD_LINK_PASSWORD_LEN) {
            cd_fail('รหัสผ่านต้องยาว ' . CD_LINK_PASSWORD_LEN . ' ตัวอักษรพอดี');
        }

        try {
            cd_query(
                "UPDATE tbl_cd_link
                 SET password_hash = ?, fail_count = 0, locked_until = NULL
                 WHERE customer_id = ? AND link_id = ?",
                [password_hash($password, PASSWORD_DEFAULT), $customer_id, $link_id]
            );
        } catch (Throwable $e) {
            cd_fail('ตั้งรหัสใหม่ไม่สำเร็จ: ' . $e->getMessage());
        }

        cd_ok([
            'msg'      => 'ตั้งรหัสใหม่แล้ว ลิงก์เดิมยังใช้ได้',
            'url'      => cd_portal_url((string) $link['token']),
            'password' => $password,
        ]);
    }
}
