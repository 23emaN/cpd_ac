<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($data['title']) ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .welcome { font-size: 1.2rem; color: #555; }
        .btn-logout { display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #e74c3c; color: white; text-decoration: none; border-radius: 5px; }
        .btn-logout:hover { background-color: #c0392b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>หน้าแรก (Main Page)</h1>
        <p class="welcome">ยินดีต้อนรับคุณ <strong><?= htmlspecialchars($data['firstname'] . ' ' . $data['lastname']) ?></strong> เข้าสู่ระบบ!</p>
        
        <p>นี่คือตัวอย่างการแสดงผลหลังจาก Login ผ่านแล้ว โดยดึงข้อมูล Session มาแสดง</p>

        <a href="/new_am/public/logout" class="btn-logout">ออกจากระบบ</a>
    </div>
</body>
</html>
