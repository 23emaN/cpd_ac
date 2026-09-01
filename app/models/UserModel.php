<?php
require_once '../app/models/Model.php';

class UserModel extends Model {

    public function getUserByUsername($username) {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_user WHERE user_name = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch();
    }
}
