<?php
require_once 'config/database.php';

class Booking {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy danh sách loại vé theo mã sự kiện
     */
    public function getTicketTypesByEventId($eventId) {
        $query = "SELECT * FROM loaive WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy danh sách chỗ ngồi theo mã sự kiện
     */
    public function getSeatsByEventId($eventId) {
        $query = "SELECT * FROM chongoi WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin loại vé theo mã loại vé
     */
    public function getTicketTypeById($typeId) {
        $query = "SELECT * FROM loaive WHERE ma_loai_ve = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$typeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy thông tin chỗ ngồi theo mã chỗ ngồi
     */
    public function getSeatById($seatId) {
        $query = "SELECT * FROM chongoi WHERE ma_cho_ngoi = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$seatId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Cập nhật trạng thái chỗ ngồi
     */
    public function updateSeatStatus($seatId, $status) {
        $query = "UPDATE chongoi SET trang_thai = ? WHERE ma_cho_ngoi = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$status, $seatId]);
    }

    /**
     * Tạo vé mới
     */
    public function createTicket($data) {
        $query = "INSERT INTO ve (ma_su_kien, ma_khach_hang, ma_cho_ngoi, ma_loai_ve, ngay_mua, trang_thai) 
                  VALUES (?, ?, ?, ?, NOW(), ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $data['ma_su_kien'],
            $data['ma_khach_hang'],
            $data['ma_cho_ngoi'],
            $data['ma_loai_ve'],
            $data['trang_thai']
        ]);
        
        return $this->db->lastInsertId();
    }
}
