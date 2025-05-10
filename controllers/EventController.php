<?php
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/BaseController.php';

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

    public function create() {
        // Kiểm tra đăng nhập và quyền tổ chức
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ($_SESSION['user']['vai_tro'] != 2) { // 2 là mã vai trò của tổ chức
            $_SESSION['error'] = 'Bạn không có quyền tạo sự kiện';
            header('Location: ' . BASE_URL);
            exit;
        }

        require_once __DIR__ . '/../views/event/create.php';
    }

    public function store() {
        // Chỉ là mẫu, không xử lý gì cả
        $_SESSION['success'] = 'Tạo sự kiện thành công!';
        header('Location: ' . BASE_URL . '/organizer/events');
        exit;
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

    /**
     * Xử lý thêm bình luận
     */
    public function addComment() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để bình luận';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        // Kiểm tra method POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }

        // Lấy dữ liệu từ form
        $maSuKien = $_POST['event_id'] ?? '';
        $noiDung = $_POST['comment'] ?? '';
        $diemDanhGia = $_POST['rating'] ?? '';
        $maKhachHang = $_SESSION['user']['id'];

        // Debug
        error_log("Form data - Event ID: $maSuKien, User ID: $maKhachHang, Rating: $diemDanhGia, Content: $noiDung");

        // Validate dữ liệu
        $errors = [];
        if (empty($maSuKien)) {
            $errors[] = 'Mã sự kiện không hợp lệ';
        }
        if (empty($noiDung)) {
            $errors[] = 'Nội dung không được để trống';
        }
        if (empty($diemDanhGia) || $diemDanhGia < 1 || $diemDanhGia > 5) {
            $errors[] = 'Điểm đánh giá không hợp lệ';
        }

        if (empty($errors)) {
            try {
                // Thêm bình luận
                $commentModel = new Comment();
                if ($commentModel->addComment($maSuKien, $maKhachHang, $noiDung, $diemDanhGia)) {
                    $_SESSION['success'] = 'Bình luận của bạn đã được gửi và đang chờ duyệt';
                } else {
                    $_SESSION['error'] = 'Có lỗi xảy ra khi gửi bình luận';
                }
            } catch (Exception $e) {
                error_log("Lỗi khi thêm bình luận: " . $e->getMessage());
                $_SESSION['error'] = 'Có lỗi xảy ra khi gửi bình luận';
            }
        } else {
            $_SESSION['error'] = implode('<br>', $errors);
        }

        // Chuyển hướng về trang sự kiện
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    /**
     * Hiển thị trang quản lý sự kiện cho admin
     */
    public function manage() {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 1) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Xử lý tìm kiếm
        $keyword = $_GET['keyword'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        
        // Lấy danh sách sự kiện theo điều kiện tìm kiếm
        $events = $this->eventModel->searchEventsForAdmin($keyword, $category, $status);
        
        // Lấy danh sách loại sự kiện cho dropdown tìm kiếm
        $categories = $this->eventModel->getAllEventTypes();
        
        // Hiển thị view
        require_once __DIR__ . '/../views/event/manage.php';
    }
    
    /**
     * Xóa sự kiện
     */
    public function delete($id) {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 1) {
            $_SESSION['error'] = 'Bạn không có quyền xóa sự kiện';
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Kiểm tra sự kiện tồn tại
        $event = $this->eventModel->getEventById($id);
        if (!$event) {
            $_SESSION['error'] = 'Sự kiện không tồn tại';
            header('Location: ' . BASE_URL . '/events/manage');
            exit;
        }
        
        // Thực hiện xóa sự kiện
        if ($this->eventModel->deleteEvent($id)) {
            $_SESSION['success'] = 'Xóa sự kiện thành công';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa sự kiện';
        }
        
        // Chuyển hướng về trang quản lý
        header('Location: ' . BASE_URL . '/events/manage');
        exit;
    }
}
