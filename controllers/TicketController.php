<?php
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/BaseController.php';

class TicketController extends BaseController {
    private $ticketModel;
    private $bookingModel;

    public function __construct() {
        parent::__construct();
        $this->ticketModel = new Ticket();
        $this->bookingModel = new Booking();
    }

    public function history() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Kiểm tra vai trò khách hàng
        if ($_SESSION['user']['vai_tro'] != 4) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location: ' . BASE_URL);
            exit;
        }

        // Lấy lịch sử đặt vé
        $tickets = $this->ticketModel->getTicketHistory($_SESSION['user']['id']);
        
        require_once __DIR__ . '/../views/ticket/history.php';
    }
    
    public function refund($id)
{
    // Kiểm tra đăng nhập
    if (!isset($_SESSION['user'])) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    // Kiểm tra vai trò khách hàng
    if ($_SESSION['user']['vai_tro'] != 4) {
        $_SESSION['error'] = 'Bạn không có quyền thực hiện thao tác này';
        header('Location: ' . BASE_URL);
        exit;
    }

    try {
        $userId = $_SESSION['user']['id'];
        error_log("Refund request for ticket ID: $id by user ID: $userId");

        // Lấy thông tin vé
        $ticket = $this->ticketModel->getTicketById($id);

        if (!$ticket) {
            $_SESSION['error'] = 'Vé không tồn tại';
            header('Location: ' . BASE_URL . '/tickets/history');
            exit;
        }

        if ($ticket['ma_khach_hang'] != $userId) {
            $_SESSION['error'] = 'Bạn không có quyền hoàn vé này';
            header('Location: ' . BASE_URL . '/tickets/history');
            exit;
        }

        // Kiểm tra trạng thái cuối cùng trong lichsudatve
        $lastAction = $this->ticketModel->getLastAction($id);
        error_log("Last action for ticket $id: '" . $lastAction . "'");
        
        if (trim(strtoupper($lastAction)) !== 'DAT_VE') {
            $_SESSION['error'] = 'Vé sự kiện đã được hoàn không thể hoàn lại!';
            header('Location: ' . BASE_URL . '/tickets/history');
            exit;
        }
        

        

        // Kiểm tra thời gian sự kiện đang diễn ra
        $today = new DateTime();
        $ngayDienRa = new DateTime($ticket['ngay_dien_ra']);
        $interval = $today->diff($ngayDienRa);
        $daysUntilEvent = (int)$interval->format('%r%a'); // %r để giữ dấu âm nếu có
        
        if ($daysUntilEvent < 5) {
            $_SESSION['error'] = 'Chỉ có thể hoàn vé trước ngày diễn ra sự kiện ít nhất 5 ngày';
            header('Location: ' . BASE_URL . '/tickets/history');
            exit;
        }



        $today = new DateTime();
        $ngayDienRa = new DateTime($ticket['ngay_dien_ra']);
        $ngayKetThuc = new DateTime($ticket['ngay_ket_thuc']);
        
        // Tính số ngày còn lại đến ngày diễn ra sự kiện
        $interval = $today->diff($ngayDienRa);
        $daysUntilEvent = (int)$interval->format('%r%a');
        
        // Nếu sự kiện đã kết thúc
        if ($today > $ngayKetThuc) {
            $_SESSION['error'] = 'Sự kiện đã kết thúc, không thể hoàn vé.';
            header('Location: ' . BASE_URL . '/tickets/history');
            exit;
        }
        
        // Nếu còn dưới 5 ngày thì không cho hoàn
        if ($daysUntilEvent < 5) {
            $_SESSION['error'] = 'Chỉ có thể hoàn vé trước ngày diễn ra sự kiện ít nhất 5 ngày.';
            header('Location: ' . BASE_URL . '/tickets/history');
            exit;
        }
        
        
        

        // Thực hiện hoàn vé
        $result = $this->ticketModel->simpleRefundTicket($id, $userId);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['message'];
        }

        header('Location: ' . BASE_URL . '/tickets/history');
        exit;

    } catch (Exception $e) {
        error_log("Error refunding ticket: " . $e->getMessage());
        $_SESSION['error'] = 'Đã xảy ra lỗi khi hoàn vé: ' . $e->getMessage();
        header('Location: ' . BASE_URL . '/tickets/history');
        exit;
    }
}

}
