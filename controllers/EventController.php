<?php
require_once __DIR__ . '/../models/Event.php';

class EventController extends BaseController {
    private $eventModel;

    public function __construct() {
        parent::__construct();
        $this->eventModel = new Event();
    }

    public function index() {
        $featuredEvents = $this->eventModel->getFeaturedEvents();
        $upcomingEvents = $this->eventModel->getUpcomingEvents();
        $categories = $this->eventModel->getAllEventTypes();
        require_once __DIR__ . '/../views/home/index.php';
    }

    public function show($id) {
        $event = $this->eventModel->getEventById($id);
        if (!$event) {
            http_response_code(404);
            require_once BASE_PATH . '/error/404.php';
            return;
        }
        require_once __DIR__ . '/../views/event/show.php';
    }

    public function getFeaturedEvents() {
        return $this->eventModel->getFeaturedEvents();
    }

    public function getUpcomingEvents() {
        return $this->eventModel->getUpcomingEvents();
    }

    public function getAllEventTypes() {
        return $this->eventModel->getAllEventTypes();
    }

    public function getEventsByCategory($categoryId) {
        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
                 LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                 LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                 WHERE s.maloaisukien = ? AND s.trang_thai = 'DA_DUYET'
                 GROUP BY s.ma_su_kien
                 ORDER BY s.ngay_dien_ra DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$categoryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function category($categoryId) {
        // Lấy thông tin danh mục
        $categoryQuery = "SELECT * FROM loaisukien WHERE maloaisukien = ?";
        $stmt = $this->db->prepare($categoryQuery);
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            http_response_code(404);
            require_once BASE_PATH . '/error/404.php';
            return;
        }

        // Lấy danh sách sự kiện thuộc danh mục
        $events = $this->getEventsByCategory($categoryId);
        
        require_once __DIR__ . '/../views/event/category.php';
    }
}
