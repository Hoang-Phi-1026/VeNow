<?php
require_once __DIR__ . '/../config/database.php';

class Role {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllRoles() {
        $query = "SELECT * FROM vaitro ORDER BY ma_vai_tro";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRoleById($id) {
        $query = "SELECT * FROM vaitro WHERE ma_vai_tro = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllRolesExceptAdmin() {
        $query = "SELECT * FROM vaitro WHERE ma_vai_tro != 1 ORDER BY ma_vai_tro";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
