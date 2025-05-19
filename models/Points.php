<?php
require_once 'config/database.php';

class Points {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy tổng điểm tích lũy của người dùng
     */
    public function getTotalPoints($userId) {
        $query = "SELECT SUM(so_diem) as total_points FROM diemtichluy WHERE ma_khach_hang = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($result['total_points'] === null) ? 0 : floatval($result['total_points']);
    }

    /**
     * Lấy lịch sử điểm tích lũy của người dùng
     */
    public function getPointsHistory($userId) {
        $query = "SELECT * FROM diemtichluy WHERE ma_khach_hang = ? ORDER BY ngay_nhan DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy lịch sử điểm tích lũy có phân trang
     */
    public function getPointsHistoryPaginated($userId, $limit, $offset, $type = '') {
        $params = [$userId];
        $typeCondition = '';
        
        if ($type) {
            $typeCondition = " AND nguon = ?";
            $params[] = $type;
        }
        
        $query = "SELECT d.*, 
                  CASE 
                    WHEN d.nguon = 'MUA_VE' THEN 'Mua vé'
                    WHEN d.nguon = 'HOAN_VE' THEN 'Hoàn vé'
                    WHEN d.nguon = 'UU_DAI' THEN 'Sử dụng ưu đãi'
                    ELSE d.nguon
                  END as nguon_text
                  FROM diemtichluy d 
                  WHERE d.ma_khach_hang = ?$typeCondition 
                  ORDER BY d.ngay_nhan DESC 
                  LIMIT ? OFFSET ?";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Đếm tổng số bản ghi lịch sử điểm
     */
    public function countPointsHistory($userId, $type = '') {
        $params = [$userId];
        $typeCondition = '';
        
        if ($type) {
            $typeCondition = " AND nguon = ?";
            $params[] = $type;
        }
        
        $query = "SELECT COUNT(*) as total FROM diemtichluy WHERE ma_khach_hang = ?$typeCondition";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return (int)$result['total'];
    }

    /**
     * Lấy thống kê điểm tích lũy
     */
    public function getPointsStatistics($userId) {
        $stats = [
            'total_earned' => 0,
            'total_used' => 0,
            'this_month_earned' => 0,
            'this_month_used' => 0,
            'last_transaction_date' => null
        ];
        
        // Tổng điểm đã kiếm được
        $query = "SELECT SUM(so_diem) as total FROM diemtichluy WHERE ma_khach_hang = ? AND so_diem > 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_earned'] = ($result['total'] === null) ? 0 : floatval($result['total']);
        
        // Tổng điểm đã sử dụng
        $query = "SELECT SUM(ABS(so_diem)) as total FROM diemtichluy WHERE ma_khach_hang = ? AND so_diem < 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_used'] = ($result['total'] === null) ? 0 : floatval($result['total']);
        
        // Điểm kiếm được trong tháng này
        $query = "SELECT SUM(so_diem) as total FROM diemtichluy 
                  WHERE ma_khach_hang = ? AND so_diem > 0 
                  AND MONTH(ngay_nhan) = MONTH(CURRENT_DATE()) 
                  AND YEAR(ngay_nhan) = YEAR(CURRENT_DATE())";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['this_month_earned'] = ($result['total'] === null) ? 0 : floatval($result['total']);
        
        // Điểm sử dụng trong tháng này
        $query = "SELECT SUM(ABS(so_diem)) as total FROM diemtichluy 
                  WHERE ma_khach_hang = ? AND so_diem < 0 
                  AND MONTH(ngay_nhan) = MONTH(CURRENT_DATE()) 
                  AND YEAR(ngay_nhan) = YEAR(CURRENT_DATE())";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['this_month_used'] = ($result['total'] === null) ? 0 : floatval($result['total']);
        
        // Ngày giao dịch gần nhất
        $query = "SELECT ngay_nhan FROM diemtichluy 
                  WHERE ma_khach_hang = ? 
                  ORDER BY ngay_nhan DESC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['last_transaction_date'] = $result ? $result['ngay_nhan'] : null;
        
        return $stats;
    }
    
    /**
     * Lấy các giao dịch gần đây
     */
    public function getRecentTransactions($userId, $limit = 5) {
        $query = "SELECT d.*, 
                  CASE 
                    WHEN d.nguon = 'MUA_VE' THEN 'Mua vé'
                    WHEN d.nguon = 'HOAN_VE' THEN 'Hoàn vé'
                    WHEN d.nguon = 'UU_DAI' THEN 'Sử dụng ưu đãi'
                    ELSE d.nguon
                  END as nguon_text
                  FROM diemtichluy d 
                  WHERE d.ma_khach_hang = ? 
                  ORDER BY d.ngay_nhan DESC 
                  LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lấy danh sách các ưu đãi có thể đổi điểm
     */
    public function getAvailableRewards() {
        $query = "SELECT * FROM uudai WHERE trang_thai = 'HOAT_DONG' AND ngay_ket_thuc >= CURRENT_DATE()";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
