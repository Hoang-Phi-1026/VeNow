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
            
            // Lấy thông tin nhà tổ chức
            $sql = "SELECT n.* 
                   FROM nhatochuc n 
                   WHERE n.ma_nguoi_dung = :ma_nguoi_dung";
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
                   WHERE s.ma_nha_to_chuc = :ma_nha_to_chuc
                   ORDER BY s.ngay_dien_ra DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':ma_nha_to_chuc', $organizer['manhatochuc'], PDO::PARAM_INT);
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Load view với thông tin nhà tổ chức
            $organizerName = $organizer['tennhatochuc'];
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
            
            // Lấy thông tin nhà tổ chức
            $sql = "SELECT n.* 
                   FROM nhatochuc n 
                   WHERE n.ma_nguoi_dung = :ma_nguoi_dung";
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
}
