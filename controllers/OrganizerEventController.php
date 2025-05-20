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
}
