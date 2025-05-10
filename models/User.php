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
        $query = "INSERT INTO nguoidung (ma_vai_tro, email, mat_khau, ho_ten, so_dien_thoai, gioi_tinh) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ma_vai_tro'],
            $data['email'],
            $data['mat_khau'],
            $data['ho_ten'],
            $data['so_dien_thoai'],
            $data['gioi_tinh'] ?? null
        ]);
    }

    public function getAllUsers() {
        $query = "SELECT n.*, v.ten_vai_tro 
                 FROM nguoidung n 
                 JOIN vaitro v ON n.ma_vai_tro = v.ma_vai_tro 
                 ORDER BY n.ngay_tao DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                 SET email = ?, ho_ten = ?, so_dien_thoai = ?, 
                     gioi_tinh = ?, ma_vai_tro = ?, kich_hoat = ?";
        $params = [
            $data['email'],
            $data['ho_ten'],
            $data['so_dien_thoai'],
            $data['gioi_tinh'],
            $data['ma_vai_tro'],
            $data['kich_hoat']
        ];

        // Nếu có mật khẩu mới
        if (isset($data['mat_khau'])) {
            $query .= ", mat_khau = ?";
            $params[] = $data['mat_khau'];
        }

        $query .= " WHERE ma_nguoi_dung = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    public function changePassword($id, $newPassword) {
        $query = "UPDATE nguoidung 
                 SET mat_khau = ?, ngay_cap_nhat = CURRENT_TIMESTAMP 
                 WHERE ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $newPassword,
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
        $query = "INSERT INTO nguoidung (email, mat_khau, ho_ten, so_dien_thoai, gioi_tinh, ma_vai_tro) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['email'],
            $data['mat_khau'],
            $data['ho_ten'],
            $data['so_dien_thoai'],
            $data['gioi_tinh'],
            $data['ma_vai_tro']
        ]);
    }

    public function deleteUser($id) {
        $query = "DELETE FROM nguoidung WHERE ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    public function checkEmailExists($email, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM nguoidung WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $query .= " AND ma_nguoi_dung != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    public function getAllUsersExceptAdmin($search = '', $role = 0) {
        $sql = "SELECT n.*, v.ten_vai_tro 
                FROM nguoidung n 
                JOIN vaitro v ON n.ma_vai_tro = v.ma_vai_tro 
                WHERE n.ma_vai_tro != 1";

        $params = [];
        
        // Thêm điều kiện tìm kiếm
        if (!empty($search)) {
            $sql .= " AND (n.ho_ten LIKE ? OR n.email LIKE ? OR n.so_dien_thoai LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        // Thêm điều kiện lọc theo vai trò
        if ($role > 0) {
            $sql .= " AND n.ma_vai_tro = ?";
            $params[] = $role;
        }

        $sql .= " ORDER BY n.ngay_tao DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
