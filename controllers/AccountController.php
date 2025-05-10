<?php
require_once __DIR__ . '/BaseController.php';

class AccountController extends BaseController {
    public function index() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        // Lấy thông tin người dùng
        $maNguoiDung = $_SESSION['user']['id'];
        $stmt = $this->db->prepare("SELECT * FROM nguoidung WHERE ma_nguoi_dung = ?");
        $stmt->execute([$maNguoiDung]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Kiểm tra file ảnh đại diện
        if (!empty($user['avt'])) {
            $avatarPath = BASE_PATH . '/public/uploads/avatars/' . $user['avt'];
            if (!file_exists($avatarPath)) {
                $user['avt'] = null;
            }
        }

        require_once __DIR__ . '/../views/account/index.php';
    }

    public function update() {
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $maNguoiDung = $_SESSION['user']['id'];
            $hoTen = $_POST['ho_ten'] ?? '';
            $soDienThoai = $_POST['so_dien_thoai'] ?? '';
            $gioiTinh = $_POST['gioi_tinh'] ?? null;
            $moTa = $_POST['mo_ta'] ?? null;
            $matKhau = $_POST['mat_khau'] ?? '';
            $matKhauMoi = $_POST['mat_khau_moi'] ?? '';
            $xacNhanMatKhau = $_POST['xac_nhan_mat_khau'] ?? '';

            // Validate dữ liệu
            $errors = [];
            if (empty($hoTen)) {
                $errors[] = 'Họ tên không được để trống';
            }
            if (!empty($soDienThoai) && !preg_match('/^[0-9]{10}$/', $soDienThoai)) {
                $errors[] = 'Số điện thoại không hợp lệ';
            }

            // Xử lý cập nhật mật khẩu nếu có
            if (!empty($matKhau)) {
                // Kiểm tra mật khẩu cũ
                $stmt = $this->db->prepare("SELECT mat_khau FROM nguoidung WHERE ma_nguoi_dung = ?");
                $stmt->execute([$maNguoiDung]);
                $currentPassword = $stmt->fetchColumn();

                if (!password_verify($matKhau, $currentPassword)) {
                    $errors[] = 'Mật khẩu cũ không đúng';
                } elseif (empty($matKhauMoi)) {
                    $errors[] = 'Vui lòng nhập mật khẩu mới';
                } elseif ($matKhauMoi !== $xacNhanMatKhau) {
                    $errors[] = 'Xác nhận mật khẩu không khớp';
                }
            }

            // Xử lý upload ảnh đại diện
            $avt = null;
            if (isset($_FILES['avt']) && $_FILES['avt']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxSize = 5 * 1024 * 1024; // 5MB

                if (!in_array($_FILES['avt']['type'], $allowedTypes)) {
                    $errors[] = 'Chỉ chấp nhận file ảnh (JPEG, PNG, GIF)';
                } elseif ($_FILES['avt']['size'] > $maxSize) {
                    $errors[] = 'Kích thước file không được vượt quá 5MB';
                } else {
                    $extension = pathinfo($_FILES['avt']['name'], PATHINFO_EXTENSION);
                    $avt = uniqid() . '.' . $extension;
                    $uploadPath = BASE_PATH . '/public/uploads/avatars/' . $avt;

                    // Tạo thư mục nếu chưa tồn tại
                    if (!is_dir(dirname($uploadPath))) {
                        mkdir(dirname($uploadPath), 0777, true);
                    }

                    if (!move_uploaded_file($_FILES['avt']['tmp_name'], $uploadPath)) {
                        $errors[] = 'Không thể upload ảnh đại diện';
                    }
                }
            }

            if (empty($errors)) {
                try {
                    $conn = $this->db->getConnection();
                    $conn->beginTransaction();

                    // Cập nhật thông tin cơ bản
                    $sql = "UPDATE nguoidung SET ho_ten = ?, so_dien_thoai = ?, gioi_tinh = ?, mo_ta = ?";
                    $params = [$hoTen, $soDienThoai, $gioiTinh, $moTa];

                    // Thêm mật khẩu mới nếu có
                    if (!empty($matKhauMoi)) {
                        $sql .= ", mat_khau = ?";
                        $params[] = password_hash($matKhauMoi, PASSWORD_DEFAULT);
                    }

                    // Thêm ảnh đại diện nếu có
                    if ($avt) {
                        $sql .= ", avt = ?";
                        $params[] = $avt;
                    }

                    $sql .= " WHERE ma_nguoi_dung = ?";
                    $params[] = $maNguoiDung;

                    $stmt = $this->db->prepare($sql);
                    $stmt->execute($params);

                    $conn->commit();
                    $_SESSION['success'] = 'Cập nhật thông tin thành công!';
                    header('Location: ' . BASE_URL . '/account');
                    exit;
                } catch (Exception $e) {
                    $conn->rollBack();
                    $errors[] = 'Có lỗi xảy ra: ' . $e->getMessage();
                }
            }

            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
                header('Location: ' . BASE_URL . '/account');
                exit;
            }
        }
    }
}
