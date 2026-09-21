<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=admin_account', 'admin_account', 'jtWxJkX6vjPQ3TVwTpyF');
    $stmt = $pdo->query('SELECT customer_tasks_id, is_reply, comment_detail FROM tbl_comment_tasks ORDER BY comment_id DESC LIMIT 10');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
