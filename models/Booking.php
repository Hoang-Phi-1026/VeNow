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
        try {
            // Kiểm tra kết nối database
            if (!$this->db) {
                $this->db = Database::getInstance();
                if (!$this->db) {
                    error_log("Database connection failed in updateSeatStatus");
                    throw new Exception("Không thể kết nối đến cơ sở dữ liệu");
                }
            }
            error_log("Updating seat status: Seat ID = $seatId, Status = $status");
            
            $query = "UPDATE chongoi SET trang_thai = ? WHERE ma_cho_ngoi = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$status, $seatId]);
            
            if (!$result) {
                error_log("SQL Error in updateSeatStatus: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Không thể cập nhật trạng thái ghế: " . implode(", ", $stmt->errorInfo()));
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Exception in updateSeatStatus: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tạo vé mới
     */
    public function createTicket($data) {
        try {
            // Kiểm tra kết nối database
            if (!$this->db) {
                $this->db = Database::getInstance();
                if (!$this->db) {
                    error_log("Database connection failed in createTicket");
                    throw new Exception("Không thể kết nối đến cơ sở dữ liệu");
                }
            }
            error_log("Creating ticket with data: " . print_r($data, true));
            
            $query = "INSERT INTO ve (ma_su_kien, ma_khach_hang, ma_cho_ngoi, ma_loai_ve, ngay_mua, trang_thai) 
                      VALUES (?, ?, ?, ?, NOW(), ?)";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                $data['ma_su_kien'],
                $data['ma_khach_hang'],
                $data['ma_cho_ngoi'],
                $data['ma_loai_ve'],
                $data['trang_thai']
            ]);
            
            if (!$result) {
                error_log("SQL Error in createTicket: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Không thể tạo vé: " . implode(", ", $stmt->errorInfo()));
            }
            
            $lastId = $this->db->lastInsertId();
            error_log("Ticket created with ID: " . $lastId);
            
            return $lastId;
        } catch (Exception $e) {
            error_log("Exception in createTicket: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tạo giao dịch mới
     */
    public function createTransaction($data) {
        try {
            // Kiểm tra kết nối database
            if (!$this->db) {
                $this->db = Database::getInstance();
                if (!$this->db) {
                    error_log("Database connection failed in createTransaction");
                    throw new Exception("Không thể kết nối đến cơ sở dữ liệu");
                }
            }
            error_log("Creating transaction with data: " . print_r($data, true));
            
            $query = "INSERT INTO giaodich (ma_khach_hang, ma_ve, so_tien, phuong_thuc_thanh_toan, ngay_giao_dich, trang_thai) 
                      VALUES (?, ?, ?, ?, NOW(), ?)";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([
                $data['ma_khach_hang'],
                $data['ma_ve'],
                $data['so_tien'],
                $data['phuong_thuc_thanh_toan'],
                $data['trang_thai']
            ]);
            
            if (!$result) {
                error_log("SQL Error in createTransaction: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Không thể tạo giao dịch: " . implode(", ", $stmt->errorInfo()));
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Exception in createTransaction: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Thêm điểm tích lũy
     */
    public function addLoyaltyPoints($userId, $points) {
        try {
            // Kiểm tra kết nối database
            if (!$this->db) {
                $this->db = Database::getInstance();
                if (!$this->db) {
                    error_log("Database connection failed in addLoyaltyPoints");
                    throw new Exception("Không thể kết nối đến cơ sở dữ liệu");
                }
            }
            error_log("Adding loyalty points: User ID = $userId, Points = $points");
            
            $query = "INSERT INTO diemtichluy (ma_khach_hang, so_diem, ngay_nhan, nguon) 
                      VALUES (?, ?, NOW(), 'MUA_VE')";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$userId, $points]);
            
            if (!$result) {
                error_log("SQL Error in addLoyaltyPoints: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Không thể thêm điểm tích lũy: " . implode(", ", $stmt->errorInfo()));
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Exception in addLoyaltyPoints: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy tổng điểm tích lũy của khách hàng
     */
    public function getTotalLoyaltyPoints($userId) {
        // Debug: Log the user ID and session data
        error_log("Getting loyalty points for user ID: " . $userId);
        error_log("Session user data: " . print_r($_SESSION['user'], true));
        
        // Ensure userId is an integer
        $userId = (int)$userId;
        
        $query = "SELECT SUM(so_diem) as total_points FROM diemtichluy WHERE ma_khach_hang = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Debug: Log the SQL query and result
        error_log("SQL Query: " . $query . " with parameter: " . $userId);
        error_log("Query result: " . print_r($result, true));
        
        // Kiểm tra nếu kết quả là NULL (chưa có điểm) thì trả về 0
        $totalPoints = ($result['total_points'] === null) ? 0 : floatval($result['total_points']);
        
        // Debug: Log the final total points
        error_log("Total points after processing: " . $totalPoints);
        
        return $totalPoints;
    }

    /**
     * Sử dụng điểm tích lũy
     */
    public function useLoyaltyPoints($userId, $points) {
        try {
            // Kiểm tra kết nối database
            if (!$this->db) {
                $this->db = Database::getInstance();
                if (!$this->db) {
                    error_log("Database connection failed in useLoyaltyPoints");
                    throw new Exception("Không thể kết nối đến cơ sở dữ liệu");
                }
            }
            error_log("Using loyalty points: User ID = $userId, Points = $points");
            
            $query = "INSERT INTO diemtichluy (ma_khach_hang, so_diem, ngay_nhan, nguon) 
                  VALUES (?, ?, NOW(), 'UU_DAI')";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$userId, -$points]);
            
            if (!$result) {
                error_log("SQL Error in useLoyaltyPoints: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Không thể sử dụng điểm tích lũy: " . implode(", ", $stmt->errorInfo()));
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Exception in useLoyaltyPoints: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Thêm lịch sử đặt vé
     */
    public function addTicketHistory($ticketId, $userId, $action, $note = null) {
        try {
            // Kiểm tra kết nối database
            if (!$this->db) {
                $this->db = Database::getInstance();
                if (!$this->db) {
                    error_log("Database connection failed in addTicketHistory");
                    throw new Exception("Không thể kết nối đến cơ sở dữ liệu");
                }
            }
            error_log("Adding ticket history: Ticket ID = $ticketId, User ID = $userId, Action = $action");
            
            $query = "INSERT INTO lichsudatve (ma_ve, ma_khach_hang, thao_tac, thoi_gian, ghi_chu) 
                      VALUES (?, ?, ?, NOW(), ?)";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$ticketId, $userId, $action, $note]);
            
            if (!$result) {
                error_log("SQL Error in addTicketHistory: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Không thể thêm lịch sử đặt vé: " . implode(", ", $stmt->errorInfo()));
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Exception in addTicketHistory: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Lấy thông tin chi tiết về điểm tích lũy của khách hàng
     */
    public function getLoyaltyPointsDetails($userId) {
        $query = "SELECT * FROM diemtichluy WHERE ma_khach_hang = ? ORDER BY ngay_nhan DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
