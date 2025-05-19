<?php
require_once __DIR__ . '/../config/database.php';

class Ticket {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getTicketHistory($customerId) {
        $query = "SELECT l.ma_lich_su, l.thao_tac as trang_thai, l.ghi_chu, l.thoi_gian,
                        v.ma_ve, v.ma_su_kien, v.trang_thai as trang_thai_ve, v.ma_khach_hang,
                        s.ten_su_kien, s.ngay_dien_ra, s.gio_dien_ra, s.dia_diem,
                        lv.ten_loai_ve, lv.gia_ve, c.so_cho, c.ma_cho_ngoi
                 FROM lichsudatve l
                 JOIN ve v ON l.ma_ve = v.ma_ve
                 JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
                 JOIN loaive lv ON v.ma_loai_ve = lv.ma_loai_ve
                 JOIN chongoi c ON v.ma_cho_ngoi = c.ma_cho_ngoi
                 WHERE l.ma_khach_hang = ?
                 ORDER BY l.thoi_gian DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$customerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTicketById($ticketId) {
        try {
            error_log("Getting ticket by ID: $ticketId");
            
            $query = "SELECT v.*, s.ten_su_kien, s.ngay_dien_ra, s.gio_dien_ra, 
                         lv.ten_loai_ve, lv.gia_ve, c.so_cho, c.ma_cho_ngoi
                  FROM ve v
                  JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
                  JOIN loaive lv ON v.ma_loai_ve = lv.ma_loai_ve
                  JOIN chongoi c ON v.ma_cho_ngoi = c.ma_cho_ngoi
                  WHERE v.ma_ve = ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$ticketId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Ticket query result: " . ($result ? "Found" : "Not found"));
            
            return $result;
        } catch (Exception $e) {
            error_log("Error in getTicketById: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function updateTicketStatus($ticketId, $status) {
        try {
            error_log("Updating ticket status: Ticket ID = $ticketId, Status = $status");
            
            $query = "UPDATE ve SET trang_thai = ? WHERE ma_ve = ?";
            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$status, $ticketId]);
            
            if (!$result) {
                error_log("SQL Error in updateTicketStatus: " . print_r($stmt->errorInfo(), true));
                throw new Exception("Không thể cập nhật trạng thái vé: " . implode(", ", $stmt->errorInfo()));
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Exception in updateTicketStatus: " . $e->getMessage());
            throw $e;
        }
    }
    
    // Phương thức hoàn vé đơn giản hóa - chỉ kiểm tra thời gian sự kiện
    public function simpleRefundTicket($ticketId, $customerId) {
        try {
            error_log("Starting refund process for ticket ID: $ticketId, customer ID: $customerId");
            
            $this->db->beginTransaction();
            
            // 1. Cập nhật trạng thái vé
            $query1 = "UPDATE ve SET trang_thai = 'HOAN_VE' WHERE ma_ve = ?";
            $stmt1 = $this->db->prepare($query1);
            $stmt1->execute([$ticketId]);
            error_log("Updated ticket status to HOAN_VE");
            
            // 2. Lấy thông tin vé và sự kiện
            $query2 = "SELECT v.*, s.ten_su_kien, lv.gia_ve, c.ma_cho_ngoi 
                      FROM ve v 
                      JOIN sukien s ON v.ma_su_kien = s.ma_su_kien 
                      JOIN loaive lv ON v.ma_loai_ve = lv.ma_loai_ve 
                      JOIN chongoi c ON v.ma_cho_ngoi = c.ma_cho_ngoi 
                      WHERE v.ma_ve = ?";
            $stmt2 = $this->db->prepare($query2);
            $stmt2->execute([$ticketId]);
            $ticket = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if (!$ticket) {
                throw new Exception("Không tìm thấy thông tin vé");
            }
            error_log("Retrieved ticket information: " . print_r($ticket, true));
            
            // 3. Cập nhật trạng thái ghế
            $query3 = "UPDATE chongoi SET trang_thai = 'TRONG' WHERE ma_cho_ngoi = ?";
            $stmt3 = $this->db->prepare($query3);
            $stmt3->execute([$ticket['ma_cho_ngoi']]);
            error_log("Updated seat status to TRONG");
            
            // 4. Thêm điểm tích lũy - Sửa lại bảng nguoidung thay vì khachhang
            $loyaltyPoints = $ticket['gia_ve'] * 0.0002;
            
            // Kiểm tra xem cột diem_tich_luy có tồn tại trong bảng nguoidung không
            $checkColumnQuery = "SHOW COLUMNS FROM nguoidung LIKE 'diem_tich_luy'";
            $checkColumnStmt = $this->db->prepare($checkColumnQuery);
            $checkColumnStmt->execute();
            $columnExists = $checkColumnStmt->rowCount() > 0;
            
            if ($columnExists) {
                $query4 = "UPDATE nguoidung SET diem_tich_luy = diem_tich_luy + ? WHERE ma_nguoi_dung = ?";
                $stmt4 = $this->db->prepare($query4);
                $stmt4->execute([$loyaltyPoints, $customerId]);
                error_log("Updated loyalty points in nguoidung table");
            } else {
                // Nếu không có cột diem_tich_luy, thêm vào bảng diemtichluy
                $query4 = "INSERT INTO diemtichluy (ma_khach_hang, so_diem, nguon) VALUES (?, ?, 'HOAN_VE')";
                $stmt4 = $this->db->prepare($query4);
                $stmt4->execute([$customerId, $loyaltyPoints]);
                error_log("Added loyalty points to diemtichluy table");
            }
            
            // 5. Thêm lịch sử hoàn vé
            $note = "Hoàn vé sự kiện: " . $ticket['ten_su_kien'] . ". Nhận " . number_format($loyaltyPoints, 2) . " điểm tích lũy.";
            $query5 = "INSERT INTO lichsudatve (ma_ve, ma_khach_hang, thao_tac, ghi_chu, thoi_gian) VALUES (?, ?, 'HOAN_VE', ?, NOW())";
            $stmt5 = $this->db->prepare($query5);
            $stmt5->execute([$ticketId, $customerId, $note]);
            error_log("Added refund history record");
            
            $this->db->commit();
            error_log("Transaction committed successfully");
            
            return [
                'success' => true,
                'points' => $loyaltyPoints,
                'message' => 'Hoàn vé thành công! Bạn đã nhận được ' . number_format($loyaltyPoints, 2) . ' điểm tích lũy.'
            ];
            
        } catch (Exception $e) {
            error_log("Error in simpleRefundTicket: " . $e->getMessage());
            try {
                if ($this->db->inTransaction()) {
                    $this->db->rollBack();
                    error_log("Transaction rolled back");
                }
            } catch (Exception $ex) {
                error_log("Error during rollback: " . $ex->getMessage());
            }
            
            return [
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi hoàn vé: ' . $e->getMessage()
            ];
        }
    }
    public function getLatestActionByTicketId($ticketId) {
        try {
            $query = "SELECT thao_tac 
                      FROM lichsudatve 
                      WHERE ma_ve = ? 
                      ORDER BY thoi_gian DESC 
                      LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$ticketId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? $row['thao_tac'] : null;
        } catch (Exception $e) {
            error_log("Error in getLatestActionByTicketId: " . $e->getMessage());
            return null;
        }
    }
    public function getLastAction($ticketId) {
        $query = "SELECT thao_tac FROM lichsudatve 
                  WHERE ma_ve = ? 
                  ORDER BY thoi_gian DESC 
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId]);
        return $stmt->fetchColumn();
    }
    
    
}
