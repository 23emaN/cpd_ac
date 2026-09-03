<?php
require_once '../app/models/Model.php';

class TeamModel extends Model {

    public function getAllTeams() {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_team ORDER BY team_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeamByName($teamName) {
        $stmt = $this->pdo->prepare("SELECT * FROM tbl_team WHERE team_name = :team_name");
        $stmt->execute(['team_name' => $teamName]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addTeam($teamName) {
        $stmt = $this->pdo->prepare("INSERT INTO tbl_team (team_name, created_at) VALUES (:team_name, NOW())");
        $stmt->execute(['team_name' => $teamName]);
        return $this->pdo->lastInsertId();
    }
}
