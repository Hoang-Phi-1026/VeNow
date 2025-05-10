<?php
require_once __DIR__ . '/../config/database.php';

class Ticket {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getTicketHistory($customerId) {
        $query = "SELECT l.ma_lich_su, l.trang_thai, l.ghi_chu, l.thoi_gian,
                        v.ma_ve, v.ma_su_kien,
                        s.ten_su_kien, s.ngay_dien_ra, s.gio_dien_ra, s.dia_diem,
                        lv.ten_loai_ve, lv.gia_ve, c.so_cho
                 FROM lichsudatve l
                 JOIN ve v ON l.ma_ve = v.ma_ve
                 JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
                 JOIN loaive lv ON l.ma_loai_ve = lv.ma_loai_ve
                 JOIN chongoi c ON v.ma_cho_ngoi = c.ma_cho_ngoi
                 WHERE l.ma_nguoi_dung = ?
                 ORDER BY l.thoi_gian DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 