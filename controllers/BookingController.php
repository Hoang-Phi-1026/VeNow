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
            echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đặt vé']);
            exit;
        }

        // Kiểm tra phương thức request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ']);
            exit;
        }

        // Lấy dữ liệu từ request
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['eventId']) || !isset($data['selectedSeats']) || empty($data['selectedSeats'])) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            exit;
        }

        $eventId = $data['eventId'];
        $selectedSeats = $data['selectedSeats'];
        
        // Lưu thông tin chọn chỗ vào session
        $_SESSION['booking'] = [
            'event_id' => $eventId,
            'selected_seats' => $selectedSeats,
            'timestamp' => time()
        ];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Đã lưu thông tin chọn chỗ',
            'redirect' => BASE_URL . '/booking/payment'
        ]);
        exit;
    }
}
