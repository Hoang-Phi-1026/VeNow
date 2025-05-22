<?php
/**
 * Shared Payment Service
 * This class contains common payment processing functionality used by different payment controllers
 */
class PaymentService {
    private $db;
    private $bookingModel;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->bookingModel = new Booking();
    }
    
    /**
     * Process ticket creation and payment
     */
    public function processTicketCreation($userId, $eventId, $selectedSeats, $usedPoints, $finalAmount, $paymentMethod) {
        try {
            // Bắt đầu transaction
            $this->db->beginTransaction();
            error_log("Transaction started for $paymentMethod payment");
            
            // Tính tổng tiền ban đầu
            $totalAmount = 0;
            foreach ($selectedSeats as $seatInfo) {
                $totalAmount += $seatInfo['price'];
            }
            
            // Nếu finalAmount không được cung cấp hoặc không hợp lệ, sử dụng totalAmount
            if ($finalAmount <= 0) {
                $finalAmount = $totalAmount;
            }
            
            // Tính tỷ lệ giảm giá để áp dụng cho từng vé
            $discountRatio = ($totalAmount > 0) ? $finalAmount / $totalAmount : 1;
            error_log("Total amount: $totalAmount, Final amount: $finalAmount, Discount ratio: $discountRatio");
            
            $createdTickets = [];
            $seatIdToTicketId = []; // Map để lưu trữ mã vé theo mã ghế
            
            // Lưu thông tin vé và cập nhật trạng thái ghế
            foreach ($selectedSeats as $seatId => $seatInfo) {
                error_log("Processing seat ID: $seatId");
                
                // Tạo vé mới
                $ticketData = [
                    'ma_su_kien' => $eventId,
                    'ma_khach_hang' => $userId,
                    'ma_cho_ngoi' => $seatId,
                    'ma_loai_ve' => $seatInfo['ticketType'],
                    'trang_thai' => 'DA_DAT'
                ];
                
                error_log("Creating ticket with data: " . print_r($ticketData, true));
                $ticketId = $this->bookingModel->createTicket($ticketData);
                error_log("Ticket created with ID: $ticketId");
                
                $createdTickets[] = $ticketId;
                $seatIdToTicketId[$seatId] = $ticketId; // Lưu mapping
                
                // Cập nhật trạng thái ghế
                error_log("Updating seat status for seat ID: $seatId");
                $this->bookingModel->updateSeatStatus($seatId, 'DA_DAT');
            }
            
            // Nếu có sử dụng điểm tích lũy
            if ($usedPoints > 0) {
                error_log("Using $usedPoints loyalty points");
                // Trừ điểm tích lũy đã sử dụng
                $this->bookingModel->useLoyaltyPoints($userId, $usedPoints);
            }
            
            // Lưu thông tin giao dịch với số tiền đã giảm giá cho từng vé
            foreach ($selectedSeats as $seatId => $seatInfo) {
                if (isset($seatIdToTicketId[$seatId])) {
                    $ticketId = $seatIdToTicketId[$seatId];
                    
                    // Tính giá vé sau khi giảm giá
                    $originalPrice = $seatInfo['price'];
                    $discountedPrice = $originalPrice * $discountRatio;
                    
                    error_log("Seat ID: $seatId, Original price: $originalPrice, Discounted price: $discountedPrice");
                    
                    $transactionData = [
                        'ma_khach_hang' => $userId,
                        'ma_ve' => $ticketId,
                        'so_tien' => $discountedPrice, // Giá đã giảm
                        'phuong_thuc_thanh_toan' => $paymentMethod,
                        'trang_thai' => 'THANH_CONG'
                    ];
                    
                    error_log("Creating transaction with data: " . print_r($transactionData, true));
                    $this->bookingModel->createTransaction($transactionData);
                }
            }
            
            // Tính và lưu điểm tích lũy (dựa trên tổng tiền ban đầu, không phụ thuộc vào giảm giá)
            $loyaltyPoints = $totalAmount * 0.00003;
            error_log("Adding $loyaltyPoints loyalty points for user ID: $userId");
            $this->bookingModel->addLoyaltyPoints($userId, $loyaltyPoints);
            
            // Lưu lịch sử đặt vé
            foreach ($createdTickets as $ticketId) {
                $message = 'Đặt vé thành công qua ' . $paymentMethod;
                error_log("Adding ticket history for ticket ID: $ticketId");
                $this->bookingModel->addTicketHistory($ticketId, $userId, 'DAT_VE', $message);
            }
            
            // Commit transaction
            error_log("Committing transaction");
            $this->db->commit();
            
            // Trả về thông tin vé đã tạo
            return [
                'success' => true,
                'created_tickets' => $createdTickets,
                'payment_amount' => $finalAmount
            ];
            
        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            if ($this->db->inTransaction()) {
                error_log("Rolling back transaction due to error: " . $e->getMessage());
                $this->db->rollBack();
            }
            
            error_log("Payment processing error: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
