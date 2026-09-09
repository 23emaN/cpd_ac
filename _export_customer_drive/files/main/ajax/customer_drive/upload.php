<?php

/**
 * อัปโหลดไฟล์เข้าคลัง — ฝั่งพนักงานเท่านั้น
 *
 * รับทีละไฟล์ (หน้าเว็บยิงหนึ่งคำขอต่อหนึ่งไฟล์เพื่อให้มีแถบความคืบหน้าแยกใบ)
 *
 * กติกาสำคัญสามข้อ
 *   1. **ชื่อซ้ำในโฟลเดอร์เดิม = เวอร์ชันใหม่ของใบเดิม** ไม่ใช่แถวใหม่ที่ชื่อซ้ำกัน
 *      ใช้ได้เฉพาะฝั่งพนักงาน เพราะพนักงานเห็นของเดิมอยู่ตรงหน้าจึงตั้งใจทับได้
 *      (ฝั่งแขกในเฟส 2 ต้องเปลี่ยนชื่อเป็น (2) แทน — ห้ามลอกกติกานี้ไปใช้)
 *   2. **ตรวจนามสกุลแบบ allow-list และตรวจ MIME จากเนื้อไฟล์จริง** ไม่เชื่อ $_FILES['type']
 *   3. **ชื่อจากผู้ใช้ไม่เคยถูกส่งเข้าฟังก์ชันระบบไฟล์** ไฟล์บนดิสก์ชื่อ current.{ext} เสมอ
 */

include('../../../config/customer_drive.php');

$customer_id = (int) ($_POST['customer_id'] ?? 0);
$user_id     = (int) ($_POST['user_id'] ?? 0);
$parent_id   = (int) ($_POST['parent_id'] ?? 0);

cd_guard($customer_id, $user_id, 'cdrive_upload');

$storageError = cd_storage_error();

if ($storageError !== '') {
    cd_fail($storageError);
}

/*
 * กับดักที่อันตรายที่สุดของฟีเจอร์นี้
 *
 * ไฟล์ที่ใหญ่เกิน post_max_size ทำให้ PHP ทิ้งทั้ง body ก่อนโค้ดเราทำงาน
 * $_FILES และ $_POST จึงว่างเปล่าพร้อมกันโดยไม่มี error ใด ๆ
 * ถ้าไม่ดักตรงนี้ ผู้ใช้จะเห็นแค่ "ข้อมูลไม่ครบ" แล้วงงว่าทำไมไฟล์ใหญ่ส่งไม่ได้
 */
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

// ต้องเป็นไฟล์ที่มาจากการอัปโหลดจริง ไม่ใช่ path ที่ผู้เรียกยัดมาเอง
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

if ($ext === '' || !in_array($ext, cd_allowed_ext('staff'), true)) {
    cd_fail('ไฟล์ชนิดนี้อัปโหลดไม่ได้ — รองรับเฉพาะ ' . implode(', ', cd_allowed_ext('staff')));
}

// เนื้อไฟล์จริงต้องสอดคล้องกับนามสกุล มิฉะนั้นเป็นการปลอมนามสกุล
if (!cd_mime_matches($tmp, $ext)) {
    cd_fail('เนื้อไฟล์ไม่ตรงกับนามสกุล .' . $ext . ' — ไฟล์อาจเสียหายหรือถูกเปลี่ยนนามสกุลมา');
}

// โฟลเดอร์ปลายทางต้องอยู่ในคลังนี้ และต้องเป็นโฟลเดอร์จริง
$parent = null;

if ($parent_id > 0) {
    $parent = cd_folder_at($customer_id, $parent_id);

    if (!$parent) {
        cd_fail('ไม่พบโฟลเดอร์ปลายทาง');
    }
}

$parentId = $parent ? (int) $parent['node_id'] : null;

global $conn;

// ------------------------------------------------------------ ชื่อซ้ำ = เวอร์ชันใหม่

$existing = cd_find_by_name($customer_id, $parentId, $original);

if ($existing) {
    if ($existing['kind'] !== 'file') {
        cd_json(['result' => '3', 'msn' => 'มีโฟลเดอร์ชื่อนี้อยู่แล้วในโฟลเดอร์นี้']);
    }

    if ((string) $existing['ext'] !== $ext) {
        cd_json(['result' => '3', 'msn' => 'มีไฟล์ชื่อนี้อยู่แล้วแต่คนละนามสกุล กรุณาเปลี่ยนชื่อก่อน']);
    }

    // เนื้อไฟล์เหมือนเดิมเป๊ะ = กดส่งซ้ำ ไม่ใช่เวอร์ชันใหม่ อย่าสร้างขยะ
    $sha = hash_file('sha256', $tmp) ?: '';

    if ($sha !== '' && $sha === (string) $existing['sha256']) {
        cd_ok([
            'msn'     => 'ไฟล์นี้อยู่ในคลังอยู่แล้ว (เนื้อหาเหมือนเดิม)',
            'node_id' => (int) $existing['node_id'],
            'skipped' => true,
        ]);
    }

    $result = cd_commit_version($existing, $tmp, 'staff_upload', $user_id);

    if (!$result['ok']) {
        cd_fail('บันทึกเวอร์ชันใหม่ไม่สำเร็จ: ' . $result['msn']);
    }

    cd_activity(
        $customer_id,
        'upload_version',
        (int) $existing['node_id'],
        $original . ' (v' . $result['version'] . ')',
        'staff',
        $user_id
    );
    cd_log($user_id, 'อัปโหลดเวอร์ชันใหม่ในคลังไฟล์ลูกค้า: ' . $original);

    cd_ok([
        'msn'     => 'บันทึกเป็นเวอร์ชัน v' . $result['version'] . ' แล้ว',
        'node_id' => (int) $existing['node_id'],
        'version' => $result['version'],
    ]);
}

// ------------------------------------------------------------ ไฟล์ใบใหม่

$conn->beginTransaction();

try {
    $conn->prepareAndExecute(
        "INSERT INTO tbl_cd_node
            (customer_id, parent_id, kind, name, ext, list_order, source, create_by)
         VALUES (?, ?, 'file', ?, ?, ?, 'staff', ?)",
        [$customer_id, $parentId, $original, $ext, cd_next_sort($customer_id, $parentId), $user_id]
    );

    $node_id = (int) $conn->get_insert_id();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    cd_fail('สร้างรายการไฟล์ไม่สำเร็จ: ' . $e->getMessage());
}

/*
 * เขียนไฟล์ลงดิสก์เป็นเวอร์ชันที่ 1 หลัง commit แถวแล้ว
 *
 * ถ้าเขียนไฟล์ไม่ผ่าน ต้องลบแถวที่เพิ่งสร้างทิ้ง มิฉะนั้นจะเหลือรายการที่ไม่มีไฟล์จริง
 * ซึ่งกดดาวน์โหลดแล้วพังโดยไม่มีใครรู้ว่าเกิดตอนไหน
 */
$node = cd_find_node($customer_id, $node_id);
$result = cd_commit_version($node, $tmp, 'staff_upload', $user_id);

if (!$result['ok']) {
    cd_forget_files($customer_id, $node_id);
    $conn->prepareAndExecute("DELETE FROM tbl_cd_node WHERE customer_id = ? AND node_id = ?", [$customer_id, $node_id]);

    cd_fail('บันทึกไฟล์ไม่สำเร็จ: ' . $result['msn']);
}

cd_activity($customer_id, 'upload', $node_id, $original, 'staff', $user_id);
cd_log($user_id, 'อัปโหลดไฟล์เข้าคลังไฟล์ลูกค้า: ' . $original);

cd_ok(['msn' => 'อัปโหลดสำเร็จ', 'node_id' => $node_id, 'version' => $result['version']]);
