<?php
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/BaseController.php';

class AdminController extends BaseController {
    private $eventModel;

    public function __construct() {
        parent::__construct();
        $this->eventModel = new Event();
        
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 1) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function pendingEvents() {
        $events = $this->eventModel->getPendingEvents();
        require_once __DIR__ . '/../views/admin/pending_events.php';
    }

    public function approveEvent() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = $_POST['event_id'] ?? null;
            if ($eventId) {
                $this->eventModel->updateEventStatus($eventId, 'DA_DUYET');
                $_SESSION['success'] = 'Đã duyệt sự kiện thành công!';
            }
        }
        header('Location: ' . BASE_URL . '/admin/pending-events');
        exit;
    }

    public function rejectEvent() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = $_POST['event_id'] ?? null;
            if ($eventId) {
                $this->eventModel->updateEventStatus($eventId, 'TU_CHOI');
                $_SESSION['success'] = 'Đã từ chối sự kiện!';
            }
        }
        header('Location: ' . BASE_URL . '/admin/pending-events');
        exit;
    }
}
