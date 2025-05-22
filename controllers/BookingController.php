<?php
require_once 'controllers/BaseController.php';
require_once 'models/Event.php';
require_once 'models/Booking.php';
require_once 'utils/IdHasher.php';
require_once 'controllers/PaymentService.php';

class BookingController extends BaseController {
    private $eventModel;
    private $bookingModel;
    private $paymentService;

    public function __construct() {
        parent::__construct();
        $this->eventModel = new Event();
        $this->bookingModel = new Booking();
        $this->paymentService = new PaymentService();
    }

    // Hiển thị trang chọn chỗ ngồi
    public function index($eventId) {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            $hashedId = IdHasher::encode($eventId);
            $_SESSION['redirect_after_login'] = BASE_URL . "/booking/" . $hashedId;
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
        
        // Thêm hashed_id vào dữ liệu sự kiện
        $event['hashed_id'] = IdHasher::encode($event['ma_su_kien']);
        
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
        $totalLoyaltyPoints = $this->bookingModel->getTotalLoyaltyPoints($userId);
        
        // Lấy chi tiết điểm tích lũy
        $loyaltyPointsDetails = $this->bookingModel->getLoyaltyPointsDetails($userId);
        
        // Tính toán giá trị quy đổi của điểm (1 điểm = 1,000 VNĐ)
        $pointValue = 1000;
        $maxDiscount = min($totalLoyaltyPoints * $pointValue, $totalAmount);

        // Hiển thị trang thanh toán
        require_once 'views/booking/payment.php';
    }
    
    // Xử lý thanh toán tiêu chuẩn (không qua cổng thanh toán)
    public function processStandardPayment() {
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
        $paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : '';
        $usedPoints = isset($_POST['usedPoints']) ? floatval($_POST['usedPoints']) : 0;
        $discountAmount = isset($_POST['discountAmount']) ? floatval($_POST['discountAmount']) : 0;
        $finalAmount = isset($_POST['finalAmount']) ? floatval($_POST['finalAmount']) : 0;
        
        // Chuyển hướng đến cổng thanh toán tương ứng nếu cần
        if ($paymentMethod === 'MOMO') {
            header('Location: ' . BASE_URL . '/momo-payment/process');
            exit;
        } else if ($paymentMethod === 'VNPAY') {
            header('Location: ' . BASE_URL . '/vnpay/process');
            exit;
        }
        
        // Xử lý thanh toán tiêu chuẩn
        $booking = $_SESSION['booking'];
        $eventId = $booking['event_id'];
        $selectedSeats = $booking['selected_seats'];
        $userId = $_SESSION['user']['ma_nguoi_dung'];
        
        // Xử lý thanh toán
        $result = $this->paymentService->processTicketCreation(
            $userId, 
            $eventId, 
            $selectedSeats, 
            $usedPoints, 
            $finalAmount, 
            $paymentMethod
        );
        
        if ($result['success']) {
            // Lưu thông tin vé đã tạo để hiển thị trên trang cảm ơn
            $_SESSION['payment_success'] = true;
            $_SESSION['created_tickets'] = $result['created_tickets'];
            $_SESSION['payment_amount'] = $result['payment_amount'];
            $_SESSION['payment_method'] = $paymentMethod;
            $_SESSION['event_id'] = $eventId;
            
            // Xóa thông tin đặt chỗ khỏi session
            unset($_SESSION['booking']);
            
            // Chuyển hướng đến trang cảm ơn
            header('Location: ' . BASE_URL . '/momo-payment/thanks');
            exit;
        } else {
            // Xử lý lỗi
            $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình đặt vé: ' . $result['error'];
            header('Location: ' . BASE_URL . '/booking/payment');
            exit;
        }
    }
}
