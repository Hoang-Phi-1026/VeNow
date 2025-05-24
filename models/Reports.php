<?php
require_once __DIR__ . '/../config/database.php';

class Reports {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Báo cáo lượt tham gia
    public function getTotalAttendance($startDate, $endDate, $eventType = '') {
    $sql = "SELECT COUNT(DISTINCT gd.ma_khach_hang) as total_attendees,
                   COUNT(gd.ma_giao_dich) as total_transactions,
                   SUM(gd.so_tien) as total_revenue,
                   IF(COUNT(gd.ma_giao_dich) > 0, SUM(gd.so_tien) / COUNT(gd.ma_giao_dich), 0) as avg_transaction
            FROM giaodich gd
            JOIN ve v ON gd.ma_ve = v.ma_ve
            JOIN sukien sk ON v.ma_su_kien = sk.ma_su_kien
            WHERE gd.ngay_giao_dich BETWEEN ? AND ?
            AND gd.trang_thai = 'THANH_CONG'";

    $params = [$startDate, $endDate];

    if ($eventType) {
        $sql .= " AND sk.maloaisukien = ?";
        $params[] = $eventType;
    }

    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return [
        'total_attendees' => $result['total_attendees'] ?? 0,
        'total_transactions' => $result['total_transactions'] ?? 0,
        'total_revenue' => $result['total_revenue'] ?? 0,
        'avg_transaction' => $result['avg_transaction'] ?? 0
    ];
}
    
    public function getAttendanceByMonth($startDate, $endDate, $eventType = '') {
        $sql = "SELECT DATE_FORMAT(gd.ngay_giao_dich, '%Y-%m') as month,
                       COUNT(DISTINCT gd.ma_khach_hang) as total_attendance,
                       COUNT(gd.ma_giao_dich) as total_transactions
                FROM giaodich gd
                JOIN ve v ON gd.ma_ve = v.ma_ve
                JOIN sukien sk ON v.ma_su_kien = sk.ma_su_kien
                WHERE gd.ngay_giao_dich BETWEEN ? AND ?
                AND gd.trang_thai = 'THANH_CONG'";
        
        $params = [$startDate, $endDate];
        
        if ($eventType) {
            $sql .= " AND sk.maloaisukien = ?";
            $params[] = $eventType;
        }
        
        $sql .= " GROUP BY DATE_FORMAT(gd.ngay_giao_dich, '%Y-%m')
                  ORDER BY month";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTopEventsByAttendance($startDate, $endDate, $eventType = '', $limit = 10) {
    $sql = "SELECT sk.ma_su_kien, sk.ten_su_kien, ls.tenloaisukien as loai_su_kien, sk.ngay_dien_ra,
                   COUNT(DISTINCT gd.ma_khach_hang) as total_attendees,
                   COUNT(gd.ma_giao_dich) as total_transactions,
                   SUM(gd.so_tien) as total_revenue
            FROM sukien sk
            JOIN ve v ON sk.ma_su_kien = v.ma_su_kien
            JOIN giaodich gd ON v.ma_ve = gd.ma_ve
            JOIN loaisukien ls ON sk.maloaisukien = ls.maloaisukien
            WHERE gd.ngay_giao_dich BETWEEN ? AND ?
            AND gd.trang_thai = 'THANH_CONG'";
    
    $params = [$startDate, $endDate];
    
    if ($eventType) {
        $sql .= " AND sk.maloaisukien = ?";
        $params[] = $eventType;
    }
    
    $sql .= " GROUP BY sk.ma_su_kien, sk.ten_su_kien, ls.tenloaisukien, sk.ngay_dien_ra
              ORDER BY total_attendees DESC
              LIMIT ?";
    
    $params[] = $limit;
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    public function getAttendanceByEventType($startDate, $endDate) {
    // First, get the total number of attendees to calculate percentages
    $totalAttendeesSql = "SELECT COUNT(DISTINCT gd.ma_khach_hang) as total
                         FROM giaodich gd
                         JOIN ve v ON gd.ma_ve = v.ma_ve
                         JOIN sukien sk ON v.ma_su_kien = sk.ma_su_kien
                         WHERE gd.ngay_giao_dich BETWEEN ? AND ?
                         AND gd.trang_thai = 'THANH_CONG'";
    $stmt = $this->db->prepare($totalAttendeesSql);
    $stmt->execute([$startDate, $endDate]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 1; // Avoid division by zero

    // Now get attendance by event type
    $sql = "SELECT ls.tenloaisukien as loai_su_kien,
                   COUNT(DISTINCT sk.ma_su_kien) as event_count,
                   COUNT(DISTINCT gd.ma_khach_hang) as total_attendees,
                   COUNT(gd.ma_giao_dich) as total_transactions,
                   SUM(gd.so_tien) as total_revenue,
                   (COUNT(DISTINCT gd.ma_khach_hang) / ?) * 100 as percentage
            FROM sukien sk
            JOIN ve v ON sk.ma_su_kien = v.ma_su_kien
            JOIN giaodich gd ON v.ma_ve = gd.ma_ve
            JOIN loaisukien ls ON sk.maloaisukien = ls.maloaisukien
            WHERE gd.ngay_giao_dich BETWEEN ? AND ?
            AND gd.trang_thai = 'THANH_CONG'
            GROUP BY ls.tenloaisukien
            ORDER BY total_attendees DESC";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$total, $startDate, $endDate]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    public function getAttendanceDetails($startDate, $endDate, $eventType = '') {
        $sql = "SELECT sk.ma_su_kien, sk.ten_su_kien, ls.tenloaisukien as loai_su_kien, sk.ngay_dien_ra,
                       COUNT(DISTINCT gd.ma_khach_hang) as attendees,
                       COUNT(gd.ma_giao_dich) as transactions,
                       SUM(gd.so_tien) as revenue,
                       AVG(gd.so_tien) as avg_transaction
                FROM sukien sk
                JOIN ve v ON sk.ma_su_kien = v.ma_su_kien
                JOIN giaodich gd ON v.ma_ve = gd.ma_ve
                JOIN loaisukien ls ON sk.maloaisukien = ls.maloaisukien
                WHERE gd.ngay_giao_dich BETWEEN ? AND ?
                AND gd.trang_thai = 'THANH_CONG'";
        
        $params = [$startDate, $endDate];
        
        if ($eventType) {
            $sql .= " AND sk.maloaisukien = ?";
            $params[] = $eventType;
        }
        
        $sql .= " GROUP BY sk.ma_su_kien, sk.ten_su_kien, ls.tenloaisukien, sk.ngay_dien_ra
                  ORDER BY attendees DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Báo cáo sự kiện nổi bật
    public function getTopEventsByRevenue($startDate, $endDate, $limit = 10) {
        $sql = "SELECT sk.ma_su_kien, sk.ten_su_kien, ls.tenloaisukien as loai_su_kien, sk.ngay_dien_ra,
                       SUM(gd.so_tien) as total_revenue,
                       COUNT(gd.ma_giao_dich) as total_transactions,
                       COUNT(DISTINCT gd.ma_khach_hang) as total_attendees
                FROM sukien sk
                JOIN ve v ON sk.ma_su_kien = v.ma_su_kien
                JOIN giaodich gd ON v.ma_ve = gd.ma_ve
                JOIN loaisukien ls ON sk.maloaisukien = ls.maloaisukien
                WHERE gd.ngay_giao_dich BETWEEN ? AND ?
                AND gd.trang_thai = 'THANH_CONG'
                GROUP BY sk.ma_su_kien, sk.ten_su_kien, ls.tenloaisukien, sk.ngay_dien_ra
                ORDER BY total_revenue DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTopEventsByRating($startDate, $endDate, $limit = 10) {
        $sql = "SELECT sk.ma_su_kien, sk.ten_su_kien, ls.tenloaisukien as loai_su_kien, sk.ngay_dien_ra,
                       AVG(bl.diem_danh_gia) as avg_rating,
                       COUNT(bl.ma_binh_luan) as total_reviews,
                       SUM(gd.so_tien) as total_revenue
                FROM sukien sk
                LEFT JOIN binhluan bl ON sk.ma_su_kien = bl.ma_su_kien AND bl.trang_thai = 'DA_DUYET'
                LEFT JOIN ve v ON sk.ma_su_kien = v.ma_su_kien
                LEFT JOIN giaodich gd ON v.ma_ve = gd.ma_ve AND gd.trang_thai = 'THANH_CONG'
                JOIN loaisukien ls ON sk.maloaisukien = ls.maloaisukien
                WHERE sk.ngay_dien_ra BETWEEN ? AND ?
                GROUP BY sk.ma_su_kien, sk.ten_su_kien, ls.tenloaisukien, sk.ngay_dien_ra
                HAVING avg_rating IS NOT NULL
                ORDER BY avg_rating DESC, total_reviews DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
        public function getTrendingEvents($startDate, $endDate, $limit = 10) {
        $sql = "SELECT 
                    sk.ma_su_kien, 
                    sk.ten_su_kien, 
                    ls.tenloaisukien as loai_su_kien, 
                    sk.ngay_dien_ra,
                    COUNT(gd.ma_giao_dich) as booking_count,
                    SUM(gd.so_tien) as recent_revenue,
                    COUNT(DISTINCT gd.ma_khach_hang) as recent_attendees
                FROM sukien sk
                JOIN ve v ON sk.ma_su_kien = v.ma_su_kien
                JOIN giaodich gd ON v.ma_ve = gd.ma_ve
                JOIN loaisukien ls ON sk.maloaisukien = ls.maloaisukien
                WHERE gd.ngay_giao_dich BETWEEN DATE_SUB(?, INTERVAL 7 DAY) AND ?
                AND gd.trang_thai = 'THANH_CONG'
                GROUP BY sk.ma_su_kien, sk.ten_su_kien, ls.tenloaisukien, sk.ngay_dien_ra
                ORDER BY booking_count DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$endDate, $endDate, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
        
    public function getRevenueByMonth($startDate, $endDate) {
        $sql = "SELECT DATE_FORMAT(gd.ngay_giao_dich, '%Y-%m') as month,
                       SUM(gd.so_tien) as total_revenue,
                       COUNT(gd.ma_giao_dich) as total_transactions
                FROM giaodich gd
                WHERE gd.ngay_giao_dich BETWEEN ? AND ?
                AND gd.trang_thai = 'THANH_CONG'
                GROUP BY DATE_FORMAT(gd.ngay_giao_dich, '%Y-%m')
                ORDER BY month";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getFeaturedStats($startDate, $endDate) {
    $sql = "SELECT 
                COUNT(DISTINCT sk.ma_su_kien) as total_events,
                COUNT(DISTINCT gd.ma_khach_hang) as total_customers,
                SUM(gd.so_tien) as total_revenue,
                AVG(gd.so_tien) as avg_transaction,
                COUNT(gd.ma_giao_dich) as total_transactions,
                COUNT(v.ma_ve) as total_tickets,
                AVG(bl.diem_danh_gia) as avg_rating
            FROM sukien sk
            LEFT JOIN ve v ON sk.ma_su_kien = v.ma_su_kien
            LEFT JOIN giaodich gd ON v.ma_ve = gd.ma_ve AND gd.trang_thai = 'THANH_CONG'
            LEFT JOIN binhluan bl ON sk.ma_su_kien = bl.ma_su_kien AND bl.trang_thai = 'DA_DUYET'
            WHERE sk.ngay_dien_ra BETWEEN ? AND ?";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$startDate, $endDate]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Đảm bảo giá trị mặc định để tránh lỗi undefined keys
    return [
        'total_events' => $result['total_events'] ?? 0,
        'total_customers' => $result['total_customers'] ?? 0,
        'total_revenue' => $result['total_revenue'] ?? 0,
        'avg_transaction' => $result['avg_transaction'] ?? 0,
        'total_transactions' => $result['total_transactions'] ?? 0,
        'total_tickets' => $result['total_tickets'] ?? 0,
        'avg_rating' => $result['avg_rating'] ? number_format($result['avg_rating'], 1) : '0.0'
    ];
}
    
    public function getEventTypes() {
        $sql = "SELECT maloaisukien, tenloaisukien FROM loaisukien ORDER BY tenloaisukien";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
