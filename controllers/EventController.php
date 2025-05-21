<?php
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../utils/IdHasher.php';

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
        
        // Mã hóa ID cho các sự kiện
        foreach ($featuredEvents as &$event) {
            $event['hashed_id'] = IdHasher::encode($event['ma_su_kien']);
        }
        
        foreach ($upcomingEvents as &$event) {
            $event['hashed_id'] = IdHasher::encode($event['ma_su_kien']);
        }
        
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

        // Kiểm tra ticket_types
        if (!isset($_POST['ticket_types']) || !is_array($_POST['ticket_types'])) {
            error_log("Không nhận được dữ liệu ticket_types từ form");
            $_SESSION['error'] = 'Vui lòng thêm ít nhất một loại vé';
            header('Location: ' . BASE_URL . '/events/create');
            exit;
        }

        // Lọc ticket_types để đảm bảo dữ liệu hợp lệ
        $ticket_types = array_filter($_POST['ticket_types'], function($ticket) {
            return !empty($ticket['ten_loai_ve']) && 
                   isset($ticket['gia_ve']) && $ticket['gia_ve'] >= 0 && 
                   !empty($ticket['so_hang']) && $ticket['so_hang'] > 0 && 
                   !empty($ticket['so_cot']) && $ticket['so_cot'] > 0;
        });

        if (empty($ticket_types)) {
            error_log("Dữ liệu ticket_types không hợp lệ sau khi lọc: " . json_encode($_POST['ticket_types']));
            $_SESSION['error'] = 'Dữ liệu loại vé không hợp lệ';
            header('Location: ' . BASE_URL . '/events/create');
            exit;
        }

        // Lấy dữ liệu từ form
        $data = [
            'ten_su_kien' => trim($_POST['ten_su_kien'] ?? ''),
            'ngay_dien_ra' => $_POST['ngay_dien_ra'] ?? '',
            'gio_dien_ra' => $_POST['gio_dien_ra'] ?? '',
            'ngay_ket_thuc' => $_POST['ngay_ket_thuc'] ?? null,
            'dia_diem' => trim($_POST['dia_diem'] ?? ''),
            'mo_ta' => trim($_POST['mo_ta'] ?? ''),
            'so_luong_cho' => (int)($_POST['so_luong_cho'] ?? 0),
            'thoi_han_dat_ve' => $_POST['thoi_han_dat_ve'] ?? null,
            'trang_thai_cho_ngoi' => $_POST['trang_thai_cho_ngoi'] ?? 'CON_CHO',
            'maloaisukien' => $_POST['maloaisukien'] ?? '',
            'ma_nguoi_dung' => $_SESSION['user']['id'],
            'ticket_types' => $ticket_types
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
        if (!empty($data['ngay_ket_thuc'])) {
            $ngay_ket_thuc = DateTime::createFromFormat('Y-m-d', $data['ngay_ket_thuc']);
            if (!$ngay_ket_thuc || $ngay_ket_thuc < $ngay_dien_ra) {
                $errors[] = 'Ngày kết thúc không hợp lệ hoặc trước ngày diễn ra';
            }
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
            if (!$thoi_han_dat_ve || $thoi_han_dat_ve < new DateTime()) {
                $errors[] = 'Thời hạn đặt vé không hợp lệ hoặc đã qua';
            }
            if (!empty($data['ngay_ket_thuc'])) {
                $event_end_date = DateTime::createFromFormat('Y-m-d', $data['ngay_ket_thuc']);
                $event_end_time = clone $event_end_date;
                $event_end_time->setTime(23, 59);
                if ($thoi_han_dat_ve > $event_end_time) {
                    $errors[] = 'Thời hạn đặt vé không được sau ngày kết thúc sự kiện';
                }
            } elseif ($thoi_han_dat_ve > $event_date_time) {
                $errors[] = 'Thời hạn đặt vé không được sau thời gian diễn ra sự kiện';
            }
        }
        if (empty($data['ticket_types'])) {
            $errors[] = 'Vui lòng thêm ít nhất một loại vé';
        }
        $totalSeats = 0;
        foreach ($data['ticket_types'] as $index => $ticket) {
            if (empty($ticket['ten_loai_ve'])) {
                $errors[] = "Tên loại vé thứ " . ($index + 1) . " không được để trống";
            }
            if (!isset($ticket['gia_ve']) || $ticket['gia_ve'] < 0) {
                $errors[] = "Giá vé thứ " . ($index + 1) . " không hợp lệ";
            }
            if (empty($ticket['so_hang']) || $ticket['so_hang'] < 1) {
                $errors[] = "Số hàng của loại vé thứ " . ($index + 1) . " không hợp lệ";
            }
            if (empty($ticket['so_cot']) || $ticket['so_cot'] < 1) {
                $errors[] = "Số cột của loại vé thứ " . ($index + 1) . " không hợp lệ";
            }
            $totalSeats += $ticket['so_hang'] * $ticket['so_cot'];
        }
        if ($totalSeats > $data['so_luong_cho']) {
            $errors[] = 'Tổng số chỗ ngồi của các loại vé vượt quá số lượng chỗ cho phép';
        }
        if ($totalSeats < $data['so_luong_cho']) {
            $errors[] = 'Tổng số chỗ ngồi của các loại vé phải bằng số lượng chỗ cho phép';
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
            'ngay_ket_thuc' => $data['ngay_ket_thuc'] ?: null,
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

            // Lưu các loại vé và chỗ ngồi
            foreach ($data['ticket_types'] as $ticket) {
                $ticketData = [
                    'ma_su_kien' => $eventId,
                    'ten_loai_ve' => trim($ticket['ten_loai_ve']),
                    'gia_ve' => (float)($ticket['gia_ve'] ?? 0),
                    'so_hang' => (int)($ticket['so_hang'] ?? 0),
                    'so_cot' => (int)($ticket['so_cot'] ?? 0),
                    'mo_ta' => trim($ticket['mo_ta'] ?? '')
                ];
                $ticketId = $this->eventModel->addTicketType($ticketData);
                if (!$ticketId) {
                    throw new Exception('Không thể tạo loại vé: ' . $ticketData['ten_loai_ve']);
                }

                // Tạo chỗ ngồi dựa trên số hàng và số cột
                $seats = [];
                for ($row = 1; $row <= $ticketData['so_hang']; $row++) {
                    for ($col = 1; $col <= $ticketData['so_cot']; $col++) {
                        $seatNumber = chr(64 + $row) . '-' . $col; // Ví dụ: A-1, A-2, B-1, B-2
                        $seats[] = [
                            'so_cho' => $seatNumber,
                            'ma_loai_ve' => $ticketId
                        ];
                    }
                }
                if (!$this->eventModel->addSeats($eventId, $seats)) {
                    throw new Exception('Không thể tạo chỗ ngồi cho loại vé: ' . $ticketData['ten_loai_ve']);
                }
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
            'ngay_ket_thuc' => $eventData['ngay_ket_thuc'],
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
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mã hóa ID cho các sự kiện
        foreach ($events as &$event) {
            $event['hashed_id'] = IdHasher::encode($event['ma_su_kien']);
        }
        
        return $events;
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
        
        // Mã hóa ID cho các sự kiện
        foreach ($events as &$event) {
            $event['hashed_id'] = IdHasher::encode($event['ma_su_kien']);
        }
        
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
                    header('Location: ' . BASE_URL);
                    exit;
                } else {
                    $_SESSION['error'] = 'Có lỗi xảy ra khi gửi bình luận';
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
            } catch (Exception $e) {
                error_log("Lỗi khi thêm bình luận: " . $e->getMessage());
                $_SESSION['error'] = 'Có lỗi xảy ra khi gửi bình luận: ' . $e->getMessage();
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
        } else {
            $_SESSION['error'] = implode('<br>', $errors);
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
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
        if (!isset($_SESSION['user']) || ($_SESSION['user']['vai_tro'] != 1 && $_SESSION['user']['vai_tro'] != 2)) {
            $_SESSION['error'] = 'Bạn không có quyền xóa sự kiện';
            header('Location: ' . BASE_URL . '/events/manage');
            exit;
        }
    
        $event = $this->eventModel->getEventById($id);
        if (!$event) {
            $_SESSION['error'] = 'Không tìm thấy sự kiện';
            header('Location: ' . BASE_URL . '/events/manage');
            exit;
        }
    
        if ($_SESSION['user']['vai_tro'] != 1 && $event['ma_nguoi_dung'] != $_SESSION['user']['id']) {
            $_SESSION['error'] = 'Bạn không có quyền xóa sự kiện này';
            header('Location: ' . BASE_URL . '/events/manage');
            exit;
        }
    
        // Xóa hình ảnh nếu có
        if (!empty($event['hinh_anh'])) {
            $imagePath = BASE_PATH . '/' . $event['hinh_anh'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
    
        // Thực hiện xóa hoặc cập nhật trạng thái
        try {
            $result = $this->eventModel->deleteEvent($id);
    
            if ($result === "DA_XOA") {
                $_SESSION['success'] = 'Xóa sự kiện thành công';
            } elseif ($result === "DA_HUY") {
                $_SESSION['success'] = 'Sự kiện đã được chuyển sang trạng thái "ĐÃ HỦY" vì đã có vé bán';
            } else {
                $_SESSION['error'] = 'Không xác định được trạng thái sau khi xử lý sự kiện';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Lỗi khi xóa sự kiện: ' . $e->getMessage();
        }
    
        header('Location: ' . BASE_URL . '/events/manage');
        exit;
    }
    

    public function deleteTicketType($ticketId) {
        if (!isset($_SESSION['user']) || ($_SESSION['user']['vai_tro'] != 1 && $_SESSION['user']['vai_tro'] != 2)) {
            $_SESSION['error'] = 'Bạn không có quyền xóa loại vé';
            header('Location: ' . BASE_URL . '/events/manage');
            exit;
        }

        try {
            if ($this->eventModel->deleteTicketType($ticketId)) {
                $_SESSION['success'] = 'Xóa loại vé thành công';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi xóa loại vé';
            }
        } catch (Exception $e) {
            error_log("Lỗi khi xóa loại vé: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa loại vé: ' . $e->getMessage();
        }

        header('Location: ' . BASE_URL . '/events/manage');
        exit;
    }
}
