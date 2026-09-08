# สรุปการแก้ไขปัญหา cpd_ac (2026-09-04)

ปัญหาเริ่มจาก `cpd_ac.test/login` ขึ้น `ERR_TOO_MANY_REDIRECTS` แล้วไล่แก้ไปทีละชั้นจนถึงหน้า login ใช้งานได้จริง พบทั้งหมด 5 ปัญหาแยกกัน ไล่ตามลำดับที่เจอ

---

## 1. หน้าเว็บวนลูป redirect ไม่รู้จบ (ERR_TOO_MANY_REDIRECTS)

**ไฟล์**: `public/index.php`

**สาเหตุ**: ระบบ routing เดิมพึ่งพา `public/.htaccess` (Apache mod_rewrite) ในการส่ง path เข้ามาทาง `$_GET['url']` แต่ Laravel Herd ใช้ **nginx** ซึ่งไม่อ่านไฟล์ `.htaccess` เลย ทำให้ `$_GET['url']` ไม่เคยถูกตั้งค่า → ทุก request ตกไปที่ค่า default `'home'` → ไม่ตรงกับ route ไหนเลย → router ยิง redirect ไป `/login` เสมอ → พอเปิด `/login` ก็เจอปัญหาเดิมซ้ำ วนลูปไม่รู้จบ

**เดิม** (`public/index.php` บรรทัด 13):
```php
$url = isset($_GET['url']) ? $_GET['url'] : 'home';
```

**แก้เป็น**:
```php
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$url = trim(substr($requestPath, strlen(BASE_URL)), '/');
if ($url === '') $url = 'home';
```

อ่าน path จาก `REQUEST_URI` ของ request โดยตรง แทนที่จะพึ่ง `.htaccess` ทำให้ใช้ได้ทั้งบน nginx (Herd) และ Apache

---

## 2. Asset/JS/CSS โหลดไม่ขึ้น (Uncaught SyntaxError: Unexpected token '<')

**ไฟล์ที่แก้ (10 ไฟล์)**: `app/views/main/sidebar.php`, `index.php`, `header.php`, `footer.php`, `app/views/backoffice/tasks.php`, `post_it.php`, `index.php`, `employee.php`, `customer.php`, `app/views/auth/login.php`

**สาเหตุ**: หน้า view ฝัง path แบบ hardcode ไว้ เช่น `/cpd_ac/public/assets/...`, `/cpd_ac/public/template/assets/js/...` ซึ่งเป็นรูปแบบเก่าสมัยรันบน XAMPP (`localhost/cpd_ac/public/...`) แต่บน Herd โดเมน `cpd_ac.test/` **คือ** `public/` อยู่แล้ว ไม่มี prefix `/cpd_ac/public` ทำให้ request ไฟล์ .js/.css พวกนี้ 404 แล้วตกไปที่ router ซึ่ง redirect กลับหน้า login (คืน HTML แทนที่จะเป็น JS) เบราว์เซอร์เลย error `Unexpected token '<'`

**เดิม**:
```php
<script src="/cpd_ac/public/template/assets/js/jquery-3.1.1.min.js"></script>
```

**แก้เป็น** (ใช้ constant `BASE_URL` ที่คำนวณแบบ dynamic อยู่แล้วใน `public/index.php`):
```php
<script src="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/template/assets/js/jquery-3.1.1.min.js"></script>
```

ทำงานถูกต้องทั้งบน Herd (root domain) และกรณี deploy ใน subdirectory (fallback `/cpd_ac/public` ยังอยู่เผื่อกรณีนั้น)

> **หมายเหตุ**: ระหว่างแก้ไขจุดนี้ เกิดบั๊กจากสคริปต์ PowerShell ที่รันซ้ำโดยไม่ตั้งใจ ทำให้ PHP tag ซ้อนกัน (nested `<?php`) ในบางไฟล์ — ได้ตรวจสอบและแก้ไขจนทุกไฟล์ผ่าน `php -l` เรียบร้อยแล้ว

---

## 3. ตัวอักษรไทยเพี้ยนทั้งหน้า (mojibake เช่น "à¸ˆà¸¡à¸²...")

**ไฟล์ที่แก้ (10 ไฟล์เดียวกับข้อ 2)**

**สาเหตุ**: เป็นผลข้างเคียงจากการแก้ข้อ 2 — สคริปต์ PowerShell ที่ใช้ `Get-Content`/`Set-Content` ไม่ได้ระบุ encoding ให้ถูกต้อง ทำให้ตอนอ่านไฟล์ PowerShell ตีความตัวอักษรไทย (UTF-8) ผิดเป็น Windows-1252 แล้วบันทึกทับกลับเป็น UTF-8 ซ้ำอีกชั้น (double-encoding) ข้อความไทยทั้งหมดในไฟล์เลยกลายเป็นตัวอักษรมั่ว

**แก้โดย**: กลับรหัส (reverse) ข้อความในไฟล์ที่ได้รับผลกระทบทั้งหมด กลับเป็น UTF-8 ที่ถูกต้อง (ไม่มี BOM เหมือนไฟล์เดิม) ตรวจสอบแล้วว่าไม่มีตัวอักษรมั่วเหลืออยู่ และทุกไฟล์ผ่าน `php -l`

---

## 4. เข้าสู่ระบบแล้ว Fatal error: Access denied for user 'root'@'localhost'

**ไฟล์**: `app/config/Connection.php`

**สาเหตุ**: โค้ดคำนวณ path ไปยังไฟล์ `.env` ผิดระดับ — `Connection.php` อยู่ที่ `app/config/` การถอย 3 ระดับ (`__DIR__ . '/../../../'`) จะเลยจุด root ของโปรเจกต์ (`cpd_ac/`) ไปถึงโฟลเดอร์ `Herd/` ซึ่งไม่มีไฟล์ `.env` เพราะใช้ `safeLoad()` (ไม่ error เมื่อหาไฟล์ไม่เจอ) `$_ENV` เลยว่างเปล่า ค่า connection ทั้งหมดตกไปใช้ default ในโค้ด (`root` / รหัสผ่านว่าง) ตรงกับ error ที่เจอเป๊ะ

**เดิม**:
```php
// แก้ไข Path ให้ถอยไป 3 ระดับเพื่อไปให้ถึงโฟลเดอร์นอกสุด (cpd_ac)
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
```

**แก้เป็น**:
```php
// ถอยจาก app/config ขึ้น 2 ระดับ เพื่อไปให้ถึงโฟลเดอร์โปรเจกต์ (cpd_ac) ที่มีไฟล์ .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
```

ทดสอบแล้วว่า `.env` โหลดถูกต้องและเชื่อมต่อฐานข้อมูลสำเร็จด้วย credential จริง (`root` / `1234`)

---

## 5. Fatal error: Class "App\Config\Connection" not found

**ไฟล์**: `vendor/composer/autoload_psr4.php` (ไฟล์ที่ Composer สร้างอัตโนมัติ ไม่ใช่ไฟล์ที่แก้เอง)

**สาเหตุ**: `composer.json` กำหนด mapping `"App\\": "app/"` แต่ไฟล์ autoload ที่ Composer สร้างไว้ (`vendor/composer/autoload_psr4.php`) ยังเป็นค่าเก่าคือ `"App\\" => "app/core"` (โฟลเดอร์ `app/core` ไม่มีอยู่จริงแล้ว) — เป็นเพราะแก้ `composer.json` แล้วไม่เคยรัน `composer dump-autoload` ใหม่ ทำให้ class ที่ถูกเรียกผ่าน autoload อย่างเดียว (เช่น `AuthModel.php` ที่ใช้ `use App\Config\Connection;`) หาไม่เจอ ในขณะที่โค้ดบางจุด (เช่น `Model.php`) ใช้ `require_once` ตรง ๆ เลยไม่โดนผลกระทบ — เป็นสาเหตุที่ทำให้ error เกิดเฉพาะบางหน้า ไม่ใช่ทุกหน้า

**แก้โดย**: รันคำสั่ง
```
composer dump-autoload
```
เพื่อสร้างไฟล์ autoload ใหม่ให้ตรงกับ `composer.json` ปัจจุบัน ตรวจสอบแล้วว่า `App\Config\Connection` resolve ถูกไฟล์ และหน้า `/main` ที่เคย error ตอนนี้ทำงานปกติ

---

## รายชื่อไฟล์ที่ถูกแก้ไขทั้งหมด

| ไฟล์ | ปัญหาที่แก้ |
|---|---|
| `public/index.php` | ข้อ 1 — redirect loop |
| `app/views/main/sidebar.php` | ข้อ 2, 3 — asset path + encoding |
| `app/views/main/index.php` | ข้อ 2, 3 |
| `app/views/main/header.php` | ข้อ 2, 3 |
| `app/views/main/footer.php` | ข้อ 2, 3 |
| `app/views/backoffice/tasks.php` | ข้อ 2, 3 |
| `app/views/backoffice/post_it.php` | ข้อ 2, 3 |
| `app/views/backoffice/index.php` | ข้อ 2, 3 |
| `app/views/backoffice/employee.php` | ข้อ 2, 3 |
| `app/views/backoffice/customer.php` | ข้อ 2, 3 |
| `app/views/auth/login.php` | ข้อ 2, 3 |
| `app/config/Connection.php` | ข้อ 4 — path ไป `.env` ผิด |
| `vendor/composer/autoload_psr4.php` | ข้อ 5 — regenerate ผ่าน `composer dump-autoload` (ไม่ได้แก้ไฟล์ตรง ๆ) |

## จุดที่ยังไม่ได้แก้ (พบระหว่างทาง แต่ยังไม่ได้ลงมือ)

- ไม่มี — ทุกจุดที่เจอระหว่างการทดสอบได้รับการแก้ไขแล้ว หากเจอ error ใหม่ระหว่างใช้งานต่อ ให้แจ้งเพิ่มได้
