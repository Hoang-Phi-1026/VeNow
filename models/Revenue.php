<?php
require_once __DIR__ . '/../config/database.php';

class Revenue {
   private $db;

   public function __construct() {
       $this->db = Database::getInstance();
   }

   /**
    * Lấy tổng doanh thu trong khoảng thời gian
    */
   public function getTotalRevenue($startDate = null, $endDate = null) {
       $sql = "SELECT SUM(g.so_tien) as total 
               FROM giaodich g 
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->execute();
       $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
       return $result['total'] ?? 0;
   }
   
   /**
    * Lấy doanh thu theo ngày
    */
   public function getRevenueByDay($startDate = null, $endDate = null) {
       $sql = "SELECT DATE(g.ngay_giao_dich) as date, SUM(g.so_tien) as total 
               FROM giaodich g 
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $sql .= " GROUP BY DATE(g.ngay_giao_dich) ORDER BY date";
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   
   /**
    * Lấy doanh thu theo tháng
    */
   public function getRevenueByMonth($year = null) {
       if (!$year) {
           $year = date('Y');
       }
       
       $sql = "SELECT MONTH(g.ngay_giao_dich) as month, SUM(g.so_tien) as total 
               FROM giaodich g 
               WHERE g.trang_thai = 'THANH_CONG' 
               AND YEAR(g.ngay_giao_dich) = :year
               GROUP BY MONTH(g.ngay_giao_dich) 
               ORDER BY month";
       
       $stmt = $this->db->prepare($sql);
       $stmt->bindParam(':year', $year);
       $stmt->execute();
       
       $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
       // Đảm bảo có đủ 12 tháng
       $monthlyData = [];
       for ($i = 1; $i <= 12; $i++) {
           $monthlyData[$i] = 0;
       }
       
       foreach ($result as $row) {
           $monthlyData[$row['month']] = (float)$row['total'];
       }
       
       return $monthlyData;
   }
   
   /**
    * Lấy doanh thu theo phương thức thanh toán
    */
   public function getRevenueByPaymentMethod($startDate = null, $endDate = null) {
       $sql = "SELECT g.phuong_thuc_thanh_toan, SUM(g.so_tien) as total 
               FROM giaodich g 
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $sql .= " GROUP BY g.phuong_thuc_thanh_toan";
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   
   /**
    * Lấy doanh thu theo loại sự kiện
    */
   public function getRevenueByEventType($startDate = null, $endDate = null) {
       $sql = "SELECT ls.tenloaisukien as ten_loai, SUM(g.so_tien) as total 
               FROM giaodich g
               JOIN ve v ON g.ma_ve = v.ma_ve
               JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
               JOIN loaisukien ls ON s.maloaisukien = ls.maloaisukien
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $sql .= " GROUP BY ls.maloaisukien, ls.tenloaisukien";
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   
   /**
    * Lấy doanh thu theo sự kiện
    */
   public function getRevenueByEvent($startDate = null, $endDate = null, $limit = 10) {
       $sql = "SELECT s.ma_su_kien, s.ten_su_kien, SUM(g.so_tien) as total, COUNT(g.ma_giao_dich) as ticket_count
               FROM giaodich g
               JOIN ve v ON g.ma_ve = v.ma_ve
               JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $sql .= " GROUP BY s.ma_su_kien, s.ten_su_kien
               ORDER BY total DESC
               LIMIT :limit";
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   
   /**
    * Lấy doanh thu theo nhà tổ chức
    */
   public function getRevenueByOrganizer($startDate = null, $endDate = null) {
       $sql = "SELECT u.ma_nguoi_dung, u.ho_ten as organizer_name, SUM(g.so_tien) as total, COUNT(DISTINCT s.ma_su_kien) as event_count
               FROM giaodich g
               JOIN ve v ON g.ma_ve = v.ma_ve
               JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
               JOIN nguoidung u ON s.ma_nguoi_dung = u.ma_nguoi_dung
               WHERE g.trang_thai = 'THANH_CONG'
               AND u.ma_vai_tro = 2"; // Vai trò nhà tổ chức
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $sql .= " GROUP BY u.ma_nguoi_dung, u.ho_ten
               ORDER BY total DESC";
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   
   /**
    * Lấy chi tiết giao dịch
    */
   public function getTransactionDetails($startDate = null, $endDate = null, $page = 1, $limit = 10) {
       $offset = ($page - 1) * $limit;
       
       $sql = "SELECT g.ma_giao_dich, g.ngay_giao_dich, g.so_tien, g.phuong_thuc_thanh_toan,
               u.ho_ten as ten_nguoi_dung, u.email,
               s.ten_su_kien, s.ngay_dien_ra,
               lv.ten_loai_ve, lv.gia_ve
               FROM giaodich g
               JOIN nguoidung u ON g.ma_khach_hang = u.ma_nguoi_dung
               JOIN ve v ON g.ma_ve = v.ma_ve
               JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
               JOIN loaive lv ON v.ma_loai_ve = lv.ma_loai_ve
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $sql .= " ORDER BY g.ngay_giao_dich DESC
               LIMIT :limit OFFSET :offset";
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
       $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
       $stmt->execute();
       
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   
   /**
    * Đếm tổng số giao dịch
    */
   public function countTransactions($startDate = null, $endDate = null) {
       $sql = "SELECT COUNT(*) as total FROM giaodich WHERE trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->execute();
       $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
       return $result['total'] ?? 0;
   }
   
   /**
    * Đếm số sự kiện có doanh thu
    */
   public function countEventsWithRevenue($startDate = null, $endDate = null) {
       $sql = "SELECT COUNT(DISTINCT s.ma_su_kien) as total
               FROM giaodich g
               JOIN ve v ON g.ma_ve = v.ma_ve
               JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->execute();
       $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
       return $result['total'] ?? 0;
   }
   
   /**
    * Lấy dữ liệu để xuất CSV
    */
   public function getDataForCSV($startDate = null, $endDate = null) {
       $sql = "SELECT g.ma_giao_dich, g.ngay_giao_dich, g.so_tien, g.phuong_thuc_thanh_toan,
               u.ho_ten as ten_nguoi_dung, u.email,
               s.ten_su_kien, s.ngay_dien_ra,
               lv.ten_loai_ve, lv.gia_ve,
               ls.tenloaisukien as loai_su_kien,
               tc.ho_ten as ten_nguoi_to_chuc
               FROM giaodich g
               JOIN nguoidung u ON g.ma_khach_hang = u.ma_nguoi_dung
               JOIN ve v ON g.ma_ve = v.ma_ve
               JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
               JOIN loaive lv ON v.ma_loai_ve = lv.ma_loai_ve
               JOIN loaisukien ls ON s.maloaisukien = ls.maloaisukien
               JOIN nguoidung tc ON s.ma_nguoi_dung = tc.ma_nguoi_dung
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $sql .= " ORDER BY g.ngay_giao_dich DESC";
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   
   /**
    * Lấy thống kê doanh thu theo năm
    */
   public function getRevenueByYear($startYear = null, $endYear = null) {
       if (!$startYear) {
           $startYear = date('Y') - 5;
       }
       if (!$endYear) {
           $endYear = date('Y');
       }
       
       $sql = "SELECT YEAR(g.ngay_giao_dich) as year, SUM(g.so_tien) as total 
               FROM giaodich g 
               WHERE g.trang_thai = 'THANH_CONG' 
               AND YEAR(g.ngay_giao_dich) BETWEEN :start_year AND :end_year
               GROUP BY YEAR(g.ngay_giao_dich) 
               ORDER BY year";
       
       $stmt = $this->db->prepare($sql);
       $stmt->bindParam(':start_year', $startYear);
       $stmt->bindParam(':end_year', $endYear);
       $stmt->execute();
       
       $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
       // Đảm bảo có đủ các năm
       $yearlyData = [];
       for ($i = $startYear; $i <= $endYear; $i++) {
           $yearlyData[$i] = 0;
       }
       
       foreach ($result as $row) {
           $yearlyData[$row['year']] = (float)$row['total'];
       }
       
       return $yearlyData;
   }
   
   /**
    * Lấy số lượng giao dịch theo loại vé
    */
   public function getTicketsSoldByType($startDate = null, $endDate = null) {
       $sql = "SELECT lv.ten_loai_ve, COUNT(g.ma_giao_dich) as ticket_count, SUM(g.so_tien) as total
               FROM giaodich g
               JOIN ve v ON g.ma_ve = v.ma_ve
               JOIN loaive lv ON v.ma_loai_ve = lv.ma_loai_ve
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $sql .= " GROUP BY lv.ma_loai_ve, lv.ten_loai_ve
               ORDER BY ticket_count DESC";
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   
   /**
    * Lấy thống kê khách hàng có nhiều giao dịch nhất
    */
   public function getTopCustomers($startDate = null, $endDate = null, $limit = 10) {
       $sql = "SELECT u.ma_nguoi_dung, u.ho_ten, u.email, COUNT(g.ma_giao_dich) as ticket_count, SUM(g.so_tien) as total_spent
               FROM giaodich g
               JOIN ve v ON g.ma_ve = v.ma_ve
               JOIN nguoidung u ON g.ma_khach_hang = u.ma_nguoi_dung
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $sql .= " GROUP BY u.ma_nguoi_dung, u.ho_ten, u.email
               ORDER BY ticket_count DESC
               LIMIT :limit";
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
       $stmt->execute();
       return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }
   
   /**
    * Lấy thống kê doanh thu theo quý
    */
   public function getRevenueByQuarter($year = null) {
       if (!$year) {
           $year = date('Y');
       }
       
       $sql = "SELECT 
                   QUARTER(g.ngay_giao_dich) as quarter, 
                   SUM(g.so_tien) as total 
               FROM giaodich g 
               WHERE g.trang_thai = 'THANH_CONG' 
               AND YEAR(g.ngay_giao_dich) = :year
               GROUP BY QUARTER(g.ngay_giao_dich) 
               ORDER BY quarter";
       
       $stmt = $this->db->prepare($sql);
       $stmt->bindParam(':year', $year);
       $stmt->execute();
       
       $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
       // Đảm bảo có đủ 4 quý
       $quarterlyData = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
       
       foreach ($result as $row) {
           $quarterlyData[$row['quarter']] = (float)$row['total'];
       }
       
       return $quarterlyData;
   }

   /**
    * Lấy tổng số vé đã bán
    */
   public function getTotalTicketsSold($startDate = null, $endDate = null) {
       $sql = "SELECT COUNT(v.ma_ve) as total 
               FROM giaodich g
               JOIN ve v ON g.ma_ve = v.ma_ve
               WHERE g.trang_thai = 'THANH_CONG'";
       
       if ($startDate && $endDate) {
           $sql .= " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
       }
       
       $stmt = $this->db->prepare($sql);
       
       if ($startDate && $endDate) {
           $stmt->bindParam(':start_date', $startDate);
           $stmt->bindParam(':end_date', $endDate);
       }
       
       $stmt->execute();
       $result = $stmt->fetch(PDO::FETCH_ASSOC);
       
       return $result['total'] ?? 0;
   }
}
