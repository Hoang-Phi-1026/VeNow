<?php
require_once 'controllers/BaseController.php';
require_once 'models/Event.php';
require_once 'models/Booking.php';

class BookingController extends BaseController {
    private $eventModel;
    private $bookingModel;

    public function __construct() {
        parent::__construct();
        $this->eventModel = new Event();
        $this->bookingModel = new Booking();
    }

    // Hiển thị trang chọn chỗ ngồi
    public function index($eventId) {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            $_SESSION['redirect_after_login'] = BASE_URL . "/booking/" . $eventId;
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Lấy thông tin sự kiện
        $event = $this->eventModel->getEventById($eventId);
        if (!$event) {
            $_SESSION['error'] = 'Không tìm thấy sự kiện';
            header('Location: ' . BASE_URL);
            exit;
        }

        // Lấy danh sách loại vé
        $ticketTypes = $this->bookingModel->getTicketTypesByEventId($eventId);
        
        // Lấy danh sách chỗ ngồi
        $seats = $this->bookingModel->getSeatsByEventId($eventId);
        
        // Tổ chức dữ liệu chỗ ngồi theo loại vé
        $seatsByType = [];
        foreach ($ticketTypes as $type) {
            $seatsByType[$type['ma_loai_ve']] = [
                'info' => $type,
                'seats' => []
            ];
        }
        
        foreach ($seats as $seat) {
            if (isset($seatsByType[$seat['ma_loai_ve']])) {
                $seatsByType[$seat['ma_loai_ve']]['seats'][] = $seat;
            }
        }
        
        // Hiển thị trang chọn chỗ ngồi
        require_once 'views/booking/select-seats.php';
    }

    // Xử lý dữ liệu khi người dùng chọn chỗ ngồi
    public function processSelection() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để đặt vé';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Phương thức không hợp lệ';
            header('Location: ' . BASE_URL);
            exit;
        }

        // Lấy dữ liệu từ form
        $eventId = isset($_POST['eventId']) ? $_POST['eventId'] : null;
        $selectedSeatsJson = isset($_POST['selectedSeats']) ? $_POST['selectedSeats'] : null;
        
        if (!$eventId || !$selectedSeatsJson || $selectedSeatsJson === '') {
            $_SESSION['error'] = 'Vui lòng chọn ít nhất một ghế';
            header('Location: ' . BASE_URL . '/booking/' . $eventId);
            exit;
        }

        $selectedSeats = json_decode($selectedSeatsJson, true);
        
        if (!$selectedSeats || empty($selectedSeats)) {
            $_SESSION['error'] = 'Dữ liệu ghế không hợp lệ';
            header('Location: ' . BASE_URL . '/booking/' . $eventId);
            exit;
        }
        
        // Lưu thông tin chọn chỗ vào session
        $_SESSION['booking'] = [
            'event_id' => $eventId,
            'selected_seats' => $selectedSeats,
            'timestamp' => time()
        ];
        
        // Chuyển hướng đến trang thanh toán
        header('Location: ' . BASE_URL . '/booking/payment');
        exit;
    }

    // Hiển thị trang thanh toán
    public function payment() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Kiểm tra thông tin đặt chỗ trong session
        if (!isset($_SESSION['booking']) || empty($_SESSION['booking']['selected_seats'])) {
            $_SESSION['error'] = 'Vui lòng chọn chỗ ngồi trước khi thanh toán';
            header('Location: ' . BASE_URL);
            exit;
        }

        // Lấy thông tin sự kiện
        $eventId = $_SESSION['booking']['event_id'];
        $event = $this->eventModel->getEventById($eventId);
        if (!$event) {
            $_SESSION['error'] = 'Không tìm thấy sự kiện';
            header('Location: ' . BASE_URL);
            exit;
        }

        // Lấy thông tin ghế đã chọn
        $selectedSeats = $_SESSION['booking']['selected_seats'];
        $totalAmount = 0;
        $seatDetails = [];

        foreach ($selectedSeats as $seatId => $seatInfo) {
            $seat = $this->bookingModel->getSeatById($seatId);
            $ticketType = $this->bookingModel->getTicketTypeById($seatInfo['ticketType']);
            
            if ($seat && $ticketType) {
                $seatDetails[] = [
                    'id' => $seatId,
                    'number' => $seatInfo['seatNumber'],
                    'price' => $seatInfo['price'],
                    'ticketType' => $ticketType['ten_loai_ve']
                ];
                
                $totalAmount += $seatInfo['price'];
            }
        }

        // Lấy tổng điểm tích lũy của người dùng
        $userId = $_SESSION['user']['ma_nguoi_dung'];
        
        // Debug: In ra thông tin người dùng
        error_log("Current user: " . print_r($_SESSION['user'], true));
        
        $totalLoyaltyPoints = $this->bookingModel->getTotalLoyaltyPoints($userId);
        
        // Debug: In ra tổng điểm tích lũy
        error_log("Total loyalty points: " . $totalLoyaltyPoints);
        
        // Lấy chi tiết điểm tích lũy
        $loyaltyPointsDetails = $this->bookingModel->getLoyaltyPointsDetails($userId);
        
        // Debug: In ra chi tiết điểm tích lũy
        error_log("Loyalty points details: " . print_r($loyaltyPointsDetails, true));
        
        // Tính toán giá trị quy đổi của điểm (1 điểm = 1,000 VNĐ)
        $pointValue = 1000;
        $maxDiscount = min($totalLoyaltyPoints * $pointValue, $totalAmount);

        // Hiển thị trang thanh toán
        require_once 'views/booking/payment.php';
    }

    // Xử lý thanh toán
    public function processPayment() {
    try {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để thanh toán';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Phương thức không hợp lệ';
            header('Location: ' . BASE_URL . '/booking/payment');
            exit;
        }

        // Kiểm tra thông tin đặt chỗ trong session
        if (!isset($_SESSION['booking']) || empty($_SESSION['booking'])) {
            $_SESSION['error'] = 'Dữ liệu đặt vé không hợp lệ hoặc đã hết hạn';
            header('Location: ' . BASE_URL);
            exit;
        }

        // Lấy thông tin từ form
        $paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : 'MOMO';
        $usedPoints = isset($_POST['usedPoints']) ? floatval($_POST['usedPoints']) : 0;
        $discountAmount = isset($_POST['discountAmount']) ? floatval($_POST['discountAmount']) : 0;
        $finalAmount = isset($_POST['finalAmount']) ? floatval($_POST['finalAmount']) : 0;
        
        $booking = $_SESSION['booking'];
        $eventId = $booking['event_id'];
        $selectedSeats = $booking['selected_seats'];
        $userId = $_SESSION['user']['ma_nguoi_dung'];
        
        error_log("Processing payment for user ID: $userId, event ID: $eventId");
        error_log("Payment method: $paymentMethod, Used points: $usedPoints, Discount: $discountAmount, Final amount: $finalAmount");
        
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
        
        // Kết nối database
        $this->db = Database::getInstance();
        
        // Bắt đầu transaction
        $this->db->beginTransaction();
        error_log("Transaction started");
        
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
            error_log("Adding ticket history for ticket ID: $ticketId");
            $this->bookingModel->addTicketHistory($ticketId, $userId, 'DAT_VE', 'Đặt vé thành công');
        }
        
        // Commit transaction
        error_log("Committing transaction");
        $this->db->commit();
        
        // Xóa thông tin đặt chỗ khỏi session
        unset($_SESSION['booking']);
        
        // Đặt thông báo thành công
        $_SESSION['payment_success'] = true;
        $_SESSION['success'] = 'Đặt vé thành công';
        
        // Chuyển hướng đến trang vé của tôi
        header('Location: ' . BASE_URL . '/tickets/my-tickets');
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction nếu có lỗi
        if (isset($this->db) && $this->db->inTransaction()) {
            error_log("Rolling back transaction due to error: " . $e->getMessage());
            $this->db->rollBack();
        }
        
        error_log("Payment processing error: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        
        // Đặt thông báo lỗi
        $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình đặt vé: ' . $e->getMessage();
        
        // Chuyển hướng về trang thanh toán
        header('Location: ' . BASE_URL . '/booking/payment');
        exit;
    }
}
}
