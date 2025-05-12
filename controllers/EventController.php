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
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 2) {
            $_SESSION['error'] = 'Bạn không có quyền tạo sự kiện';
            header('Location: ' . BASE_URL);
            exit;
        }
        require_once __DIR__ . '/../views/event/create.php';
    }

    public function store() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 2) {
            $_SESSION['error'] = 'Bạn không có quyền tạo sự kiện';
            header('Location: ' . BASE_URL);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Phương thức không hợp lệ';
            header('Location: ' . BASE_URL . '/events/create');
            exit;
        }

        // Lấy dữ liệu từ form
        $data = [
            'ten_su_kien' => trim($_POST['ten_su_kien'] ?? ''),
            'ngay_dien_ra' => $_POST['ngay_dien_ra'] ?? '',
            'gio_dien_ra' => $_POST['gio_dien_ra'] ?? '',
            'dia_diem' => trim($_POST['dia_diem'] ?? ''),
            'mo_ta' => trim($_POST['mo_ta'] ?? ''),
            'so_luong_cho' => (int)($_POST['so_luong_cho'] ?? 0),
            'thoi_han_dat_ve' => $_POST['thoi_han_dat_ve'] ?? null,
            'trang_thai_cho_ngoi' => $_POST['trang_thai_cho_ngoi'] ?? 'CON_CHO',
            'maloaisukien' => $_POST['maloaisukien'] ?? '',
            'ma_nguoi_dung' => $_SESSION['user']['id'],
            'ticket_types' => $_POST['ticket_types'] ?? []
        ];

        // Kiểm tra nhà tổ chức
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM nhatochuc WHERE ma_nguoi_dung = ?");
        $stmt->execute([$data['ma_nguoi_dung']]);
        if ($stmt->fetchColumn() == 0) {
            $_SESSION['error'] = 'Bạn chưa đăng ký làm nhà tổ chức';
            header('Location: ' . BASE_URL . '/events/create');
            exit;
        }

        // Xử lý file hình ảnh
        $hinh_anh = null;
        if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB

            // Tạo thư mục uploads nếu chưa tồn tại
            $upload_dir = BASE_PATH . '/public/uploads/events';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (in_array($_FILES['hinh_anh']['type'], $allowed_types) && $_FILES['hinh_anh']['size'] <= $max_size) {
                $ext = pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $target_path = $upload_dir . '/' . $filename;
                
                if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $target_path)) {
                    $hinh_anh = 'public/uploads/events/' . $filename;
                } else {
                    $_SESSION['error'] = 'Lỗi khi tải lên hình ảnh. Vui lòng thử lại.';
                    header('Location: ' . BASE_URL . '/events/create');
                    exit;
                }
            } else {
                $_SESSION['error'] = 'Hình ảnh không hợp lệ (chỉ hỗ trợ JPG, PNG, GIF, tối đa 5MB)';
                header('Location: ' . BASE_URL . '/events/create');
                exit;
            }
        }

        if ($hinh_anh) {
            $data['hinh_anh'] = $hinh_anh;
        }

        // Validate dữ liệu
        $errors = [];
        if (empty($data['ten_su_kien'])) {
            $errors[] = 'Tên sự kiện không được để trống';
        }
        if (empty($data['ngay_dien_ra'])) {
            $errors[] = 'Ngày diễn ra không được để trống';
        } else {
            $ngay_dien_ra = DateTime::createFromFormat('Y-m-d', $data['ngay_dien_ra']);
            if (!$ngay_dien_ra || $ngay_dien_ra < new DateTime()) {
                $errors[] = 'Ngày diễn ra không hợp lệ hoặc đã qua';
            }
        }
        if (empty($data['gio_dien_ra'])) {
            $errors[] = 'Giờ diễn ra không được để trống';
        }
        if (empty($data['dia_diem'])) {
            $errors[] = 'Địa điểm không được để trống';
        }
        if ($data['so_luong_cho'] < 1) {
            $errors[] = 'Số lượng chỗ phải lớn hơn 0';
        }
        if (empty($data['maloaisukien'])) {
            $errors[] = 'Vui lòng chọn loại sự kiện';
        }
        if (empty($data['ma_nguoi_dung'])) {
            $errors[] = 'Thông tin nhà tổ chức không hợp lệ';
        }
        if (!in_array($data['trang_thai_cho_ngoi'], ['CON_CHO', 'HET_CHO'])) {
            $errors[] = 'Trạng thái chỗ ngồi không hợp lệ';
        }
        if (!empty($data['thoi_han_dat_ve'])) {
            $thoi_han_dat_ve = DateTime::createFromFormat('Y-m-d\TH:i', $data['thoi_han_dat_ve']);
            $event_date_time = DateTime::createFromFormat('Y-m-d H:i', $data['ngay_dien_ra'] . ' ' . $data['gio_dien_ra']);
            if (!$thoi_han_dat_ve || $thoi_han_dat_ve < new DateTime() || $thoi_han_dat_ve > $event_date_time) {
                $errors[] = 'Thời hạn đặt vé không hợp lệ hoặc sau thời gian diễn ra sự kiện';
            }
        }
        if (empty($data['ticket_types'])) {
            $errors[] = 'Vui lòng thêm ít nhất một loại vé';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . BASE_URL . '/events/create');
            exit;
        }

        // Chuẩn bị dữ liệu để lưu vào CSDL
        $eventData = [
            'ten_su_kien' => $data['ten_su_kien'],
            'ngay_dien_ra' => $data['ngay_dien_ra'],
            'gio_dien_ra' => $data['gio_dien_ra'],
            'dia_diem' => $data['dia_diem'],
            'mo_ta' => $data['mo_ta'],
            'hinh_anh' => $data['hinh_anh'] ?? null,
            'so_luong_cho' => $data['so_luong_cho'],
            'thoi_han_dat_ve' => $data['thoi_han_dat_ve'] ?: null,
            'trang_thai_cho_ngoi' => $data['trang_thai_cho_ngoi'],
            'maloaisukien' => $data['maloaisukien'],
            'ma_nguoi_dung' => $data['ma_nguoi_dung']
        ];

        try {
            $this->db->beginTransaction();

            // Tạo sự kiện
            $eventId = $this->eventModel->createEvent($eventData);
            if (!$eventId) {
                throw new Exception('Không thể tạo sự kiện');
            }

            // Lưu các loại vé
            $ticketTypeIds = [];
            foreach ($data['ticket_types'] as $ticket) {
                $ticketData = [
                    'ma_su_kien' => $eventId,
                    'ten_loai_ve' => trim($ticket['ten_loai_ve']),
                    'gia_ve' => (float)($ticket['gia_ve'] ?? 0),
                    'mo_ta' => trim($ticket['mo_ta'] ?? '')
                ];
                $ticketId = $this->eventModel->addTicketType($ticketData);
                if (!$ticketId) {
                    throw new Exception('Không thể tạo loại vé: ' . $ticketData['ten_loai_ve']);
                }
                $ticketTypeIds[] = $ticketId;
            }

            // Tạo chỗ ngồi (phân bổ đều cho loại vé đầu tiên để đơn giản hóa)
            $seats = [];
            for ($i = 1; $i <= $data['so_luong_cho']; $i++) {
                $seats[] = [
                    'so_cho' => "A-$i",
                    'ma_loai_ve' => $ticketTypeIds[0] // Gán cho loại vé đầu tiên
                ];
            }
            if (!$this->eventModel->addSeats($eventId, $seats)) {
                throw new Exception('Không thể tạo chỗ ngồi');
            }

            // Lưu yêu cầu sự kiện
            $this->saveEventRequest($eventId, $data['ma_nguoi_dung'], $eventData);

            $this->db->commit();
            $_SESSION['success'] = 'Tạo sự kiện thành công! Sự kiện đang chờ duyệt';
        header('Location: ' . BASE_URL . '/organizer/events');
        exit;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi khi tạo sự kiện: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi tạo sự kiện: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/events/create');
            exit;
        }
    }

    private function saveEventRequest($eventId, $ma_nguoi_dung, $eventData) {
        // Lấy ma_nha_to_chuc từ ma_nguoi_dung
        $query = "SELECT ma_nha_to_chuc FROM nhatochuc WHERE ma_nguoi_dung = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ma_nguoi_dung]);
        $nhatochuc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$nhatochuc) {
            throw new Exception("Không tìm thấy thông tin nhà tổ chức");
        }
        
        $ma_nha_to_chuc = $nhatochuc['ma_nha_to_chuc'];

        $query = "INSERT INTO yeucausukien (ma_su_kien, ma_nha_to_chuc, loai_yeu_cau, chi_tiet_yeu_cau, trang_thai)
                 VALUES (?, ?, 'TAO', ?, 'CHO_DUYET')";
        $stmt = $this->db->prepare($query);
        $chi_tiet_yeu_cau = json_encode([
            'ten_su_kien' => $eventData['ten_su_kien'],
            'ngay_dien_ra' => $eventData['ngay_dien_ra'],
            'gio_dien_ra' => $eventData['gio_dien_ra'],
            'dia_diem' => $eventData['dia_diem'],
            'mo_ta' => $eventData['mo_ta'],
            'hinh_anh' => $eventData['hinh_anh'],
            'so_luong_cho' => $eventData['so_luong_cho'],
            'thoi_han_dat_ve' => $eventData['thoi_han_dat_ve'],
            'trang_thai_cho_ngoi' => $eventData['trang_thai_cho_ngoi'],
            'maloaisukien' => $eventData['maloaisukien'],
            'ma_nguoi_dung' => $eventData['ma_nguoi_dung']
        ], JSON_UNESCAPED_UNICODE);
        $stmt->execute([$eventId, $ma_nha_to_chuc, $chi_tiet_yeu_cau]);
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
        $query = "SELECT s.*, nd.ho_ten, l.tenloaisukien,
                        MIN(t.gia_ve) as gia_ve_min,
                        MAX(t.gia_ve) as gia_ve_max
                 FROM sukien s 
                 LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
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
        $categoryQuery = "SELECT * FROM loaisukien WHERE maloaisukien = ?";
        $stmt = $this->db->prepare($categoryQuery);
        $stmt->execute([$categoryId]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$category) {
            http_response_code(404);
            require_once BASE_PATH . '/error/404.php';
            return;
        }

        $events = $this->getEventsByCategory($categoryId);
        require_once __DIR__ . '/../views/event/category.php';
    }

    public function addComment() {
        if (!isset($_SESSION['user'])) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để bình luận';
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL);
            exit;
        }

        $maSuKien = $_POST['event_id'] ?? '';
        $noiDung = $_POST['comment'] ?? '';
        $diemDanhGia = $_POST['rating'] ?? '';
        $maKhachHang = $_SESSION['user']['id'];

        $errors = [];
        if (empty($maSuKien)) {
            $errors[] = 'Mã sự kiện không hợp lệ';
        } else {
            $event = $this->eventModel->getEventById($maSuKien);
            if (!$event) {
                $errors[] = 'Sự kiện không tồn tại';
            } elseif ($event['trang_thai'] != 'DA_DUYET') {
                $errors[] = 'Sự kiện chưa được duyệt, không thể bình luận';
            }
        }
        if (empty($noiDung)) {
            $errors[] = 'Nội dung không được để trống';
        }
        if (empty($diemDanhGia) || $diemDanhGia < 1 || $diemDanhGia > 5) {
            $errors[] = 'Điểm đánh giá không hợp lệ';
        }

        if (empty($errors)) {
            try {
                $commentModel = new Comment();
                if ($commentModel->addComment($maSuKien, $maKhachHang, $noiDung, $diemDanhGia)) {
                    $_SESSION['success'] = 'Bình luận của bạn đã được gửi và đang chờ duyệt';
                } else {
                    $_SESSION['error'] = 'Có lỗi xảy ra khi gửi bình luận';
                }
            } catch (Exception $e) {
                error_log("Lỗi khi thêm bình luận: " . $e->getMessage());
                $_SESSION['error'] = 'Có lỗi xảy ra khi gửi bình luận: ' . $e->getMessage();
            }
        } else {
            $_SESSION['error'] = implode('<br>', $errors);
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    
    public function manage() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 1) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $keyword = $_GET['keyword'] ?? '';
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $events = $this->eventModel->searchEventsForAdmin($keyword, $category, $status);
        $categories = $this->eventModel->getAllEventTypes();
        
        require_once __DIR__ . '/../views/event/manage.php';
    }
    
    public function delete($id) {
        // Kiểm tra quyền xóa
        if (!isset($_SESSION['user']) || ($_SESSION['user']['vai_tro'] != 1 && $_SESSION['user']['vai_tro'] != 2)) {
            $_SESSION['error'] = 'Bạn không có quyền xóa sự kiện';
            header('Location: ' . BASE_URL . '/events/manage');
            exit;
        }

        // Kiểm tra sự kiện có tồn tại không
        $event = $this->eventModel->getEventById($id);
        if (!$event) {
            $_SESSION['error'] = 'Không tìm thấy sự kiện';
            header('Location: ' . BASE_URL . '/events/manage');
            exit;
        }

        // Kiểm tra quyền xóa (chỉ admin hoặc người tạo sự kiện mới được xóa)
        if ($_SESSION['user']['vai_tro'] != 1 && $event['ma_nguoi_dung'] != $_SESSION['user']['id']) {
            $_SESSION['error'] = 'Bạn không có quyền xóa sự kiện này';
            header('Location: ' . BASE_URL . '/events/manage');
            exit;
        }

        // Xóa ảnh sự kiện nếu có
        if (!empty($event['hinh_anh'])) {
            $imagePath = BASE_PATH . '/' . $event['hinh_anh'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Xóa sự kiện
        if ($this->eventModel->deleteEvent($id)) {
            $_SESSION['success'] = 'Xóa sự kiện thành công';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa sự kiện';
        }

        header('Location: ' . BASE_URL . '/events/manage');
        exit;
    }
}
