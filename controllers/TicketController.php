<?php
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/BaseController.php';

class TicketController extends BaseController {
    private $ticketModel;

    public function __construct() {
        parent::__construct();
        $this->ticketModel = new Ticket();
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
}
