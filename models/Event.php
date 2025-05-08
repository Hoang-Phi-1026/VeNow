<?php
require_once __DIR__ . '/../config/database.php';

class Event {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllEvents($limit = null) {
        $query = "SELECT s.*, n.ho_ten as ten_nha_to_chuc 
                 FROM sukien s 
                 JOIN nguoidung n ON s.ma_nha_to_chuc = n.ma_nguoi_dung 
                 WHERE s.trang_thai = 'DA_DUYET' 
                 ORDER BY s.ngay_dien_ra DESC";
        
        if ($limit) {
            $query .= " LIMIT ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$limit]);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventById($id) {
        $query = "SELECT s.*, n.ho_ten as ten_nha_to_chuc 
                 FROM sukien s 
                 JOIN nguoidung n ON s.ma_nha_to_chuc = n.ma_nguoi_dung 
                 WHERE s.ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getEventTickets($eventId) {
        $query = "SELECT * FROM loaive WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventSeats($eventId) {
        $query = "SELECT c.*, l.ten_loai_ve, l.gia_ve 
                 FROM chongoi c 
                 LEFT JOIN loaive l ON c.ma_loai_ve = l.ma_loai_ve 
                 WHERE c.ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$eventId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createEvent($data) {
        $query = "INSERT INTO sukien (ma_nha_to_chuc, ten_su_kien, ngay_dien_ra, gio_dien_ra, 
                                    dia_diem, mo_ta, trang_thai) 
                 VALUES (?, ?, ?, ?, ?, ?, 'CHO_DUYET')";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ma_nha_to_chuc'],
            $data['ten_su_kien'],
            $data['ngay_dien_ra'],
            $data['gio_dien_ra'],
            $data['dia_diem'],
            $data['mo_ta']
        ]);
    }

    public function updateEvent($id, $data) {
        $query = "UPDATE sukien 
                 SET ten_su_kien = ?, ngay_dien_ra = ?, gio_dien_ra = ?, 
                     dia_diem = ?, mo_ta = ?, trang_thai = 'CHO_DUYET' 
                 WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ten_su_kien'],
            $data['ngay_dien_ra'],
            $data['gio_dien_ra'],
            $data['dia_diem'],
            $data['mo_ta'],
            $id
        ]);
    }

    public function addTicketType($data) {
        $query = "INSERT INTO loaive (ma_su_kien, ten_loai_ve, gia_ve, mo_ta) 
                 VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ma_su_kien'],
            $data['ten_loai_ve'],
            $data['gia_ve'],
            $data['mo_ta']
        ]);
    }

    public function addSeats($eventId, $seats) {
        $query = "INSERT INTO chongoi (ma_su_kien, so_cho, trang_thai, ma_loai_ve) 
                 VALUES (?, ?, 'TRONG', ?)";
        $stmt = $this->db->prepare($query);
        
        foreach ($seats as $seat) {
            $stmt->execute([
                $eventId,
                $seat['so_cho'],
                $seat['ma_loai_ve']
            ]);
        }
        return true;
    }

    public function searchEvents($keyword, $date = null, $location = null) {
        $query = "SELECT s.*, n.ho_ten as ten_nha_to_chuc 
                 FROM sukien s 
                 JOIN nguoidung n ON s.ma_nha_to_chuc = n.ma_nguoi_dung 
                 WHERE s.trang_thai = 'DA_DUYET' 
                 AND (s.ten_su_kien LIKE ? OR s.mo_ta LIKE ?)";
        $params = ["%$keyword%", "%$keyword%"];

        if ($date) {
            $query .= " AND s.ngay_dien_ra = ?";
            $params[] = $date;
        }

        if ($location) {
            $query .= " AND s.dia_diem LIKE ?";
            $params[] = "%$location%";
        }

        $query .= " ORDER BY s.ngay_dien_ra DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeaturedEvents() {
        $query = "SELECT s.*, n.ho_ten as ten_nha_to_chuc,
                        MIN(l.gia_ve) as gia_ve_min,
                        MAX(l.gia_ve) as gia_ve_max
                 FROM sukien s 
                 JOIN nguoidung n ON s.ma_nha_to_chuc = n.ma_nguoi_dung 
                 LEFT JOIN loaive l ON s.ma_su_kien = l.ma_su_kien
                 WHERE s.trang_thai = 'DA_DUYET'
                 GROUP BY s.ma_su_kien
                 ORDER BY s.ngay_dien_ra DESC
                 LIMIT 5";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingEvents() {
        $query = "SELECT s.*, n.ho_ten as ten_nha_to_chuc,
                        MIN(l.gia_ve) as gia_ve_min,
                        MAX(l.gia_ve) as gia_ve_max
                 FROM sukien s 
                 JOIN nguoidung n ON s.ma_nha_to_chuc = n.ma_nguoi_dung 
                 LEFT JOIN loaive l ON s.ma_su_kien = l.ma_su_kien
                 WHERE s.trang_thai = 'DA_DUYET'
                 GROUP BY s.ma_su_kien
                 ORDER BY s.ngay_dien_ra DESC
                 LIMIT 10";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} 