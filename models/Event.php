<?php
require_once __DIR__ . '/../config/database.php';

class Event {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAllEvents($limit = null) {
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.trang_thai = 'DA_DUYET' 
                 GROUP BY s.ma_su_kien
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
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien 
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
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
        $query = "INSERT INTO sukien (ten_su_kien, ngay_dien_ra, gio_dien_ra, 
                                    dia_diem, mo_ta, trang_thai, maloaisukien, ma_nha_to_chuc) 
                 VALUES (?, ?, ?, ?, ?, 'CHO_DUYET', ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ten_su_kien'],
            $data['ngay_dien_ra'],
            $data['gio_dien_ra'],
            $data['dia_diem'],
            $data['mo_ta'],
            $data['maloaisukien'],
            $data['ma_nha_to_chuc']
        ]);
    }

    public function updateEvent($id, $data) {
        $query = "UPDATE sukien 
                 SET ten_su_kien = ?, ngay_dien_ra = ?, gio_dien_ra = ?, 
                     dia_diem = ?, mo_ta = ?, maloaisukien = ?, ma_nha_to_chuc = ?,
                     trang_thai = 'CHO_DUYET' 
                 WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ten_su_kien'],
            $data['ngay_dien_ra'],
            $data['gio_dien_ra'],
            $data['dia_diem'],
            $data['mo_ta'],
            $data['maloaisukien'],
            $data['ma_nha_to_chuc'],
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

    public function searchEvents($keyword, $category = null, $date = null, $location = null, $price = null) {
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.trang_thai = 'DA_DUYET'";
        
        $params = [];
        
        if (!empty($keyword)) {
            $query .= " AND (s.ten_su_kien LIKE ? OR s.mo_ta LIKE ? OR s.dia_diem LIKE ?)";
            $searchTerm = "%$keyword%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($category)) {
            $query .= " AND s.maloaisukien = ?";
            $params[] = $category;
        }

        if (!empty($date)) {
            $query .= " AND DATE(s.ngay_dien_ra) = ?";
            $params[] = $date;
        }

        if (!empty($location)) {
            $query .= " AND s.dia_diem LIKE ?";
            $params[] = "%$location%";
        }

        if (!empty($price)) {
            switch ($price) {
                case 'free':
                    $query .= " AND t.gia_ve = 0";
                    break;
                case 'paid':
                    $query .= " AND t.gia_ve > 0";
                    break;
            }
        }

        $query .= " GROUP BY s.ma_su_kien ORDER BY s.ngay_dien_ra DESC";
        
        $stmt = $this->db->prepare($query);
        
        // Bind parameters manually
        if (!empty($params)) {
            for ($i = 0; $i < count($params); $i++) {
                $stmt->bindValue($i + 1, $params[$i]);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFeaturedEvents() {
        $sql = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                       MIN(t.gia_ve) as gia_ve_min,
                       MAX(t.gia_ve) as gia_ve_max
                FROM sukien s 
                LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
                LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien 
                LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                WHERE s.trang_thai = 'DA_DUYET' 
                GROUP BY s.ma_su_kien
                ORDER BY s.ngay_tao DESC 
                LIMIT 4";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUpcomingEvents() {
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.trang_thai = 'DA_DUYET'
                 GROUP BY s.ma_su_kien
                 ORDER BY s.ngay_dien_ra DESC
                 LIMIT 4";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByOrganizer($organizerId) {
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.ma_nha_to_chuc = ?
                 GROUP BY s.ma_su_kien
                 ORDER BY s.ngay_dien_ra DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$organizerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventsByType($typeId) {
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.maloaisukien = ? AND s.trang_thai = 'DA_DUYET'
                 GROUP BY s.ma_su_kien
                 ORDER BY s.ngay_dien_ra DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$typeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventCategories() {
        $query = "SELECT * FROM loaisukien ORDER BY tenloaisukien ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllEventTypes() {
        $query = "SELECT * FROM loaisukien ORDER BY tenloaisukien ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingEvents() {
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.trang_thai = 'CHO_DUYET'
                 GROUP BY s.ma_su_kien
                 ORDER BY s.ngay_tao DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateEventStatus($eventId, $status) {
        $query = "UPDATE sukien SET trang_thai = ? WHERE ma_su_kien = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$status, $eventId]);
    }
}
