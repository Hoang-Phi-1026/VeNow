<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function login($email, $password) {
        $query = "SELECT n.*, v.ten_vai_tro 
                 FROM nguoidung n 
                 JOIN vaitro v ON n.ma_vai_tro = v.ma_vai_tro 
                 WHERE n.email = ? AND n.kich_hoat = 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['mat_khau']) {
            return $user;
        }
        return false;
    }

    public function register($data) {
        $query = "INSERT INTO nguoidung (ma_vai_tro, email, mat_khau, ho_ten, so_dien_thoai) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ma_vai_tro'],
            $data['email'],
            $data['mat_khau'],
            $data['ho_ten'],
            $data['so_dien_thoai']
        ]);
    }

    public function getUserById($id) {
        $query = "SELECT n.*, v.ten_vai_tro 
                 FROM nguoidung n 
                 JOIN vaitro v ON n.ma_vai_tro = v.ma_vai_tro 
                 WHERE n.ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser($id, $data) {
        $query = "UPDATE nguoidung 
                 SET ho_ten = ?, so_dien_thoai = ?, ngay_cap_nhat = CURRENT_TIMESTAMP 
                 WHERE ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ho_ten'],
            $data['so_dien_thoai'],
            $id
        ]);
    }

    public function changePassword($id, $newPassword) {
        $query = "UPDATE nguoidung 
                 SET mat_khau = ?, ngay_cap_nhat = CURRENT_TIMESTAMP 
                 WHERE ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            password_hash($newPassword, PASSWORD_DEFAULT),
            $id
        ]);
    }

    public function getRoleById($roleId) {
        $query = "SELECT * FROM vaitro WHERE ma_vai_tro = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$roleId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllRoles() {
        $query = "SELECT * FROM vaitro";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserByEmail($email) {
        $query = "SELECT * FROM nguoidung WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($data) {
        $query = "INSERT INTO nguoidung (ho_ten, email, mat_khau) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ho_ten'],
            $data['email'],
            password_hash($data['mat_khau'], PASSWORD_DEFAULT)
        ]);
    }
}
