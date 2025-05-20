<?php
require_once __DIR__ . '/../models/Event.php';

class OrganizerEventController extends BaseController {
    private $eventModel;

    public function __construct() {
        parent::__construct();
        $this->eventModel = new Event();
    }

    public function index() {
        try {
            // Lấy mã người dùng từ session
            $maNguoiDung = $_SESSION['user']['id'];
            
            // Lấy thông tin nhà tổ chức từ bảng nguoidung
            $sql = "SELECT nd.*, n.ma_nha_to_chuc
                   FROM nguoidung nd
                   LEFT JOIN nhatochuc n ON nd.ma_nguoi_dung = n.ma_nguoi_dung
                   WHERE nd.ma_nguoi_dung = :ma_nguoi_dung AND nd.ma_vai_tro = 2";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_nguoi_dung', $maNguoiDung, PDO::PARAM_INT);
            $stmt->execute();
            $organizer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$organizer) {
                $_SESSION['error'] = 'Không tìm thấy thông tin nhà tổ chức. Vui lòng liên hệ quản trị viên.';
                header('Location: ' . BASE_URL);
                exit;
            }
            
            // Lấy danh sách sự kiện của nhà tổ chức
            $sql = "SELECT s.*, l.tenloaisukien, 
                   (SELECT COUNT(*) FROM ve v WHERE v.ma_su_kien = s.ma_su_kien) as so_ve_da_ban
                   FROM sukien s
                   LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien 
                   WHERE s.ma_nguoi_dung = :ma_nguoi_dung
                   ORDER BY s.ngay_dien_ra DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_nguoi_dung', $maNguoiDung, PDO::PARAM_INT);
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Load view với thông tin nhà tổ chức
            $organizerName = $organizer['ho_ten'];
            require_once __DIR__ . '/../views/organizer/events/index.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            header('Location: ' . BASE_URL);
            exit;
        }
    }

    public function edit($id) {
        try {
            // Lấy mã người dùng từ session
            $maNguoiDung = $_SESSION['user']['id'];
            
            // Lấy thông tin nhà tổ chức từ bảng nguoidung
            $sql = "SELECT nd.*, n.ma_nha_to_chuc
                   FROM nguoidung nd
                   LEFT JOIN nhatochuc n ON nd.ma_nguoi_dung = n.ma_nguoi_dung
                   WHERE nd.ma_nguoi_dung = :ma_nguoi_dung AND nd.ma_vai_tro = 2";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_nguoi_dung', $maNguoiDung, PDO::PARAM_INT);
            $stmt->execute();
            $organizer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$organizer) {
                $_SESSION['error'] = 'Không tìm thấy thông tin nhà tổ chức. Vui lòng liên hệ quản trị viên.';
                header('Location: ' . BASE_URL);
                exit;
            }

            // Lấy thông tin sự kiện
            $event = $this->eventModel->getEventById($id);
            
            // Đảm bảo biến organizerName được định nghĩa
            $organizerName = $organizer['ho_ten'] ?? 'Không xác định';

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'ten_su_kien' => $_POST['ten_su_kien'],
                    'mo_ta' => $_POST['mo_ta'],
                    'ngay_dien_ra' => $_POST['ngay_dien_ra'],
                    'gio_dien_ra' => $_POST['gio_dien_ra'],
                    'dia_diem' => $_POST['dia_diem'],
                    'maloaisukien' => $_POST['maloaisukien'],
                    'so_luong_ve' => $_POST['so_luong_ve']
                ];

                if ($this->eventModel->updateEvent($id, $data)) {
                    $_SESSION['success'] = 'Cập nhật sự kiện thành công!';
                    header('Location: ' . BASE_URL . '/organizer/events');
                    exit;
                } else {
                    $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật sự kiện!';
                }
            }

            // Lấy danh sách loại sự kiện
            $eventTypes = $this->eventModel->getAllEventTypes();
            
            // Đảm bảo organizerName được truyền đến view
            $organizerName = $organizer['ho_ten'] ?? 'Không xác định';
            
            require_once __DIR__ . '/../views/organizer/events/edit.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/organizer/events');
            exit;
        }
    }

    public function delete($id) {
        try {
            if ($this->eventModel->deleteEvent($id)) {
                $_SESSION['success'] = 'Xóa sự kiện thành công!';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi xóa sự kiện!';
            }

            header('Location: ' . BASE_URL . '/organizer/events');
            exit;
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/organizer/events');
            exit;
        }
    }

    public function revenue() {
        try {
            // Lấy mã người dùng từ session
            $maNguoiDung = $_SESSION['user']['id'];
            
            // Lấy thông tin nhà tổ chức từ bảng nguoidung
            $sql = "SELECT nd.*, n.ma_nha_to_chuc
                   FROM nguoidung nd
                   LEFT JOIN nhatochuc n ON nd.ma_nguoi_dung = n.ma_nguoi_dung
                   WHERE nd.ma_nguoi_dung = :ma_nguoi_dung AND nd.ma_vai_tro = 2";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_nguoi_dung', $maNguoiDung, PDO::PARAM_INT);
            $stmt->execute();
            $organizer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$organizer) {
                $_SESSION['error'] = 'Không tìm thấy thông tin nhà tổ chức. Vui lòng liên hệ quản trị viên.';
                header('Location: ' . BASE_URL);
                exit;
            }
            
            // Xử lý lọc theo thời gian
            $timeFilter = isset($_GET['time_filter']) ? $_GET['time_filter'] : 'all';
            $dateCondition = '';
            $params = [':ma_nguoi_dung' => $maNguoiDung];
            
            switch ($timeFilter) {
                case 'this_month':
                    $dateCondition = " AND MONTH(g.ngay_giao_dich) = MONTH(CURRENT_DATE()) AND YEAR(g.ngay_giao_dich) = YEAR(CURRENT_DATE())";
                    break;
                case 'last_month':
                    $dateCondition = " AND MONTH(g.ngay_giao_dich) = MONTH(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)) AND YEAR(g.ngay_giao_dich) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH))";
                    break;
                case 'this_year':
                    $dateCondition = " AND YEAR(g.ngay_giao_dich) = YEAR(CURRENT_DATE())";
                    break;
                case 'last_year':
                    $dateCondition = " AND YEAR(g.ngay_giao_dich) = YEAR(DATE_SUB(CURRENT_DATE(), INTERVAL 1 YEAR))";
                    break;
                case 'custom':
                    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                        $startDate = $_GET['start_date'];
                        $endDate = $_GET['end_date'];
                        $dateCondition = " AND DATE(g.ngay_giao_dich) BETWEEN :start_date AND :end_date";
                        $params[':start_date'] = $startDate;
                        $params[':end_date'] = $endDate;
                    }
                    break;
                default: // 'all'
                    $dateCondition = "";
                    break;
            }
            
            // Lấy doanh thu theo sự kiện
            $sql = "SELECT s.ma_su_kien, s.ten_su_kien, s.ngay_dien_ra, s.hinh_anh,
                   COUNT(DISTINCT v.ma_ve) as so_ve_da_ban,
                   SUM(g.so_tien) as tong_doanh_thu
                   FROM sukien s
                   LEFT JOIN ve v ON s.ma_su_kien = v.ma_su_kien
                   LEFT JOIN giaodich g ON v.ma_ve = g.ma_ve
                   WHERE s.ma_nguoi_dung = :ma_nguoi_dung
                   AND g.trang_thai = 'THANH_CONG'
                   $dateCondition
                   GROUP BY s.ma_su_kien
                   ORDER BY tong_doanh_thu DESC";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $eventRevenues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Tính tổng doanh thu
            $totalRevenue = 0;
            $totalTicketsSold = 0;
            foreach ($eventRevenues as $event) {
                $totalRevenue += $event['tong_doanh_thu'];
                $totalTicketsSold += $event['so_ve_da_ban'];
            }
            
            // Lấy doanh thu theo loại vé
            $sql = "SELECT lv.ten_loai_ve, lv.gia_ve,
                   COUNT(v.ma_ve) as so_ve_da_ban,
                   SUM(g.so_tien) as tong_doanh_thu
                   FROM loaive lv
                   JOIN sukien s ON lv.ma_su_kien = s.ma_su_kien
                   LEFT JOIN ve v ON lv.ma_loai_ve = v.ma_loai_ve
                   LEFT JOIN giaodich g ON v.ma_ve = g.ma_ve
                   WHERE s.ma_nguoi_dung = :ma_nguoi_dung
                   AND g.trang_thai = 'THANH_CONG'
                   $dateCondition
                   GROUP BY lv.ma_loai_ve
                   ORDER BY tong_doanh_thu DESC";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $ticketTypeRevenues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Lấy doanh thu theo tháng (cho biểu đồ)
            $sql = "SELECT 
                   YEAR(g.ngay_giao_dich) as nam,
                   MONTH(g.ngay_giao_dich) as thang,
                   SUM(g.so_tien) as doanh_thu
                   FROM giaodich g
                   JOIN ve v ON g.ma_ve = v.ma_ve
                   JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
                   WHERE s.ma_nguoi_dung = :ma_nguoi_dung
                   AND g.trang_thai = 'THANH_CONG'
                   GROUP BY YEAR(g.ngay_giao_dich), MONTH(g.ngay_giao_dich)
                   ORDER BY nam, thang";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':ma_nguoi_dung', $maNguoiDung);
            $stmt->execute();
            $monthlyRevenues = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Chuẩn bị dữ liệu cho biểu đồ
            $chartLabels = [];
            $chartData = [];
            
            foreach ($monthlyRevenues as $monthly) {
                $chartLabels[] = $monthly['thang'] . '/' . $monthly['nam'];
                $chartData[] = $monthly['doanh_thu'];
            }
            
            // Load view với dữ liệu
            $organizerName = $organizer['ho_ten'];
            require_once __DIR__ . '/../views/organizer/revenue/index.php';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/organizer/events');
            exit;
        }
    }

    public function update() {
        try {
            // Kiểm tra xem có phải là POST request không
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $_SESSION['error'] = 'Phương thức không hợp lệ';
                header('Location: ' . BASE_URL . '/organizer/events');
                exit;
            }

            // Lấy mã người dùng từ session
            $maNguoiDung = $_SESSION['user']['id'];
            
            // Lấy dữ liệu từ form
            $maSuKien = $_POST['ma_su_kien'] ?? null;
            $tenSuKien = $_POST['ten_su_kien'] ?? '';
            $moTa = $_POST['mo_ta'] ?? '';
            $ngayDienRa = $_POST['ngay_dien_ra'] ?? '';
            $ngayKetThuc = $_POST['ngay_ket_thuc'] ?? null;
            $gioDienRa = $_POST['gio_dien_ra'] ?? '';
            $diaDiem = $_POST['dia_diem'] ?? '';
            $maLoaiSuKien = $_POST['maloaisukien'] ?? '';
            $soLuongCho = $_POST['so_luong_cho'] ?? 0;
            $trangThaiChoNgoi = $_POST['trang_thai_cho_ngoi'] ?? 'CON_CHO';
            $thoiHanDatVe = $_POST['thoi_han_dat_ve'] ?? null;
            $currentImage = $_POST['current_image'] ?? '';
            
            // Kiểm tra dữ liệu đầu vào
            if (empty($maSuKien) || empty($tenSuKien) || empty($ngayDienRa) || empty($gioDienRa) || 
                empty($diaDiem) || empty($maLoaiSuKien) || empty($soLuongCho)) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc';
                header('Location: ' . BASE_URL . '/organizer/events/edit/' . $maSuKien);
                exit;
            }
            
            // Kiểm tra quyền sở hữu sự kiện
            $sql = "SELECT ma_nguoi_dung FROM sukien WHERE ma_su_kien = :ma_su_kien";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_su_kien', $maSuKien, PDO::PARAM_INT);
            $stmt->execute();
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$event || $event['ma_nguoi_dung'] != $maNguoiDung) {
                $_SESSION['error'] = 'Bạn không có quyền cập nhật sự kiện này';
                header('Location: ' . BASE_URL . '/organizer/events');
                exit;
            }
            
            // Xử lý upload hình ảnh mới (nếu có)
            $hinhAnh = $currentImage;
            if (isset($_FILES['hinh_anh']) && $_FILES['hinh_anh']['error'] == 0) {
                $uploadDir = 'public/uploads/events/';
                $fileName = uniqid() . '.' . pathinfo($_FILES['hinh_anh']['name'], PATHINFO_EXTENSION);
                $uploadFile = $uploadDir . $fileName;
                
                // Kiểm tra loại file
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($_FILES['hinh_anh']['type'], $allowedTypes)) {
                    $_SESSION['error'] = 'Chỉ chấp nhận file hình ảnh (JPG, PNG, GIF)';
                    header('Location: ' . BASE_URL . '/organizer/events/edit/' . $maSuKien);
                    exit;
                }
                
                // Kiểm tra kích thước file (tối đa 5MB)
                if ($_FILES['hinh_anh']['size'] > 5 * 1024 * 1024) {
                    $_SESSION['error'] = 'Kích thước file không được vượt quá 5MB';
                    header('Location: ' . BASE_URL . '/organizer/events/edit/' . $maSuKien);
                    exit;
                }
                
                // Di chuyển file tạm thời đến thư mục đích
                if (move_uploaded_file($_FILES['hinh_anh']['tmp_name'], $uploadFile)) {
                    $hinhAnh = $uploadFile;
                    
                    // Xóa hình ảnh cũ nếu có
                    if (!empty($currentImage) && file_exists($currentImage) && $currentImage != $uploadFile) {
                        @unlink($currentImage);
                    }
                } else {
                    $_SESSION['error'] = 'Có lỗi xảy ra khi tải lên hình ảnh';
                    header('Location: ' . BASE_URL . '/organizer/events/edit/' . $maSuKien);
                    exit;
                }
            }
            
            // Cập nhật thông tin sự kiện
            $sql = "UPDATE sukien SET 
                    ten_su_kien = :ten_su_kien,
                    mo_ta = :mo_ta,
                    ngay_dien_ra = :ngay_dien_ra,
                    ngay_ket_thuc = :ngay_ket_thuc,
                    gio_dien_ra = :gio_dien_ra,
                    dia_diem = :dia_diem,
                    maloaisukien = :maloaisukien,
                    so_luong_cho = :so_luong_cho,
                    trang_thai_cho_ngoi = :trang_thai_cho_ngoi,
                    thoi_han_dat_ve = :thoi_han_dat_ve,
                    hinh_anh = :hinh_anh,
                    trang_thai = 'CHO_DUYET',
                    ngay_cap_nhat = NOW()
                    WHERE ma_su_kien = :ma_su_kien AND ma_nguoi_dung = :ma_nguoi_dung";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ten_su_kien', $tenSuKien);
            $stmt->bindParam(':mo_ta', $moTa);
            $stmt->bindParam(':ngay_dien_ra', $ngayDienRa);
            $stmt->bindParam(':ngay_ket_thuc', $ngayKetThuc);
            $stmt->bindParam(':gio_dien_ra', $gioDienRa);
            $stmt->bindParam(':dia_diem', $diaDiem);
            $stmt->bindParam(':maloaisukien', $maLoaiSuKien);
            $stmt->bindParam(':so_luong_cho', $soLuongCho);
            $stmt->bindParam(':trang_thai_cho_ngoi', $trangThaiChoNgoi);
            $stmt->bindParam(':thoi_han_dat_ve', $thoiHanDatVe);
            $stmt->bindParam(':hinh_anh', $hinhAnh);
            $stmt->bindParam(':ma_su_kien', $maSuKien);
            $stmt->bindParam(':ma_nguoi_dung', $maNguoiDung);
            
            $result = $stmt->execute();
            
            // Xử lý thêm loại vé mới (nếu có)
            if (isset($_POST['new_ticket_types']) && is_array($_POST['new_ticket_types'])) {
                foreach ($_POST['new_ticket_types'] as $ticket) {
                    // Kiểm tra xem có đủ thông tin không
                    if (!empty($ticket['ten_loai_ve']) && isset($ticket['gia_ve']) && 
                        !empty($ticket['so_hang']) && !empty($ticket['so_cot'])) {
                        
                        $sql = "INSERT INTO loaive (ma_su_kien, ten_loai_ve, gia_ve, so_hang, so_cot, mo_ta) 
                                VALUES (:ma_su_kien, :ten_loai_ve, :gia_ve, :so_hang, :so_cot, :mo_ta)";
                        
                        $stmt = $this->db->prepare($sql);
                        $stmt->bindParam(':ma_su_kien', $maSuKien);
                        $stmt->bindParam(':ten_loai_ve', $ticket['ten_loai_ve']);
                        $stmt->bindParam(':gia_ve', $ticket['gia_ve']);
                        $stmt->bindParam(':so_hang', $ticket['so_hang']);
                        $stmt->bindParam(':so_cot', $ticket['so_cot']);
                        $stmt->bindParam(':mo_ta', $ticket['mo_ta']);
                        $stmt->execute();
                    }
                }
            }
            
            if ($result) {
                $_SESSION['success'] = 'Cập nhật sự kiện thành công! Sự kiện đã được chuyển sang trạng thái chờ duyệt.';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật sự kiện';
            }
            
            header('Location: ' . BASE_URL . '/organizer/events');
            exit;
            
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/organizer/events');
            exit;
        }
    }
    
    public function deleteTicket() {
        try {
            // Kiểm tra tham số
            $ticketId = $_GET['id'] ?? null;
            $eventId = $_GET['event_id'] ?? null;
            
            if (!$ticketId || !$eventId) {
                $_SESSION['error'] = 'Thiếu thông tin cần thiết';
                header('Location: ' . BASE_URL . '/organizer/events');
                exit;
            }
            
            // Lấy mã người dùng từ session
            $maNguoiDung = $_SESSION['user']['id'];
            
            // Kiểm tra quyền sở hữu sự kiện
            $sql = "SELECT s.ma_nguoi_dung 
                   FROM sukien s 
                   JOIN loaive lv ON s.ma_su_kien = lv.ma_su_kien 
                   WHERE lv.ma_loai_ve = :ma_loai_ve AND s.ma_su_kien = :ma_su_kien";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_loai_ve', $ticketId);
            $stmt->bindParam(':ma_su_kien', $eventId);
            $stmt->execute();
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$event || $event['ma_nguoi_dung'] != $maNguoiDung) {
                $_SESSION['error'] = 'Bạn không có quyền xóa loại vé này';
                header('Location: ' . BASE_URL . '/organizer/events/edit/' . $eventId);
                exit;
            }
            
            // Kiểm tra xem loại vé đã có vé được bán chưa
            $sql = "SELECT COUNT(*) as count FROM ve WHERE ma_loai_ve = :ma_loai_ve";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_loai_ve', $ticketId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                $_SESSION['error'] = 'Không thể xóa loại vé này vì đã có vé được bán';
                header('Location: ' . BASE_URL . '/organizer/events/edit/' . $eventId);
                exit;
            }
            
            // Xóa loại vé
            $sql = "DELETE FROM loaive WHERE ma_loai_ve = :ma_loai_ve";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_loai_ve', $ticketId);
            $result = $stmt->execute();
            
            if ($result) {
                $_SESSION['success'] = 'Xóa loại vé thành công';
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi xóa loại vé';
            }
            
            header('Location: ' . BASE_URL . '/organizer/events/edit/' . $eventId);
            exit;
            
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Có lỗi xảy ra: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/organizer/events');
            exit;
        }
    }
}
