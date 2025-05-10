<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';

class UserController extends BaseController {
    private $userModel;
    private $roleModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->roleModel = new Role();
        
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 1) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }

    public function index() {
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] != 1) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }

        $userModel = new User();
        $roleModel = new Role();

        // Lấy tham số tìm kiếm và lọc
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $role = isset($_GET['role']) ? (int)$_GET['role'] : 0;

        // Lấy danh sách người dùng với bộ lọc
        $users = $userModel->getAllUsersExceptAdmin($search, $role);
        
        // Lấy danh sách vai trò cho form lọc
        $roles = $roleModel->getAllRolesExceptAdmin();

        require_once __DIR__ . '/../views/users/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $hoTen = $_POST['ho_ten'] ?? '';
            $soDienThoai = $_POST['so_dien_thoai'] ?? '';
            $gioiTinh = $_POST['gioi_tinh'] ?? null;
            $maVaiTro = $_POST['ma_vai_tro'] ?? '';
            $matKhau = $_POST['mat_khau'] ?? '';

            // Validate dữ liệu
            $errors = [];
            if (empty($email)) {
                $errors[] = 'Email không được để trống';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email không hợp lệ';
            } elseif ($this->userModel->checkEmailExists($email)) {
                $errors[] = 'Email đã tồn tại';
            }

            if (empty($hoTen)) {
                $errors[] = 'Họ tên không được để trống';
            }

            if (empty($soDienThoai)) {
                $errors[] = 'Số điện thoại không được để trống';
            } elseif (!preg_match('/^[0-9]{10}$/', $soDienThoai)) {
                $errors[] = 'Số điện thoại không hợp lệ';
            }

            if (empty($maVaiTro)) {
                $errors[] = 'Vai trò không được để trống';
            }

            if (empty($matKhau)) {
                $errors[] = 'Mật khẩu không được để trống';
            } elseif (strlen($matKhau) < 6) {
                $errors[] = 'Mật khẩu phải có ít nhất 6 ký tự';
            }

            if (empty($errors)) {
                $data = [
                    'email' => $email,
                    'ho_ten' => $hoTen,
                    'so_dien_thoai' => $soDienThoai,
                    'gioi_tinh' => $gioiTinh,
                    'ma_vai_tro' => $maVaiTro,
                    'mat_khau' => $matKhau // Không mã hóa mật khẩu
                ];

                if ($this->userModel->createUser($data)) {
                    $_SESSION['success'] = 'Thêm người dùng thành công!';
                    header('Location: ' . BASE_URL . '/users');
                    exit;
                } else {
                    $errors[] = 'Có lỗi xảy ra khi thêm người dùng';
                }
            }

            if (!empty($errors)) {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }

        // Lấy danh sách vai trò
        $roles = $this->userModel->getAllRoles();
        require_once __DIR__ . '/../views/users/create.php';
    }

    public function edit($id) {
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy người dùng!';
            header('Location: ' . BASE_URL . '/users');
            exit();
        }

        // Kiểm tra nếu đang chỉnh sửa tài khoản admin
        if ($user['ma_vai_tro'] == 1) {
            $_SESSION['error'] = 'Không thể chỉnh sửa tài khoản admin!';
            header('Location: ' . BASE_URL . '/users');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Kiểm tra nếu đang chuyển thành tài khoản admin
            if ($_POST['ma_vai_tro'] == 1) {
                $_SESSION['error'] = 'Không thể chuyển thành tài khoản admin!';
                header('Location: ' . BASE_URL . '/users');
                exit();
            }

            $data = [
                'email' => $_POST['email'],
                'ho_ten' => $_POST['ho_ten'],
                'so_dien_thoai' => $_POST['so_dien_thoai'],
                'gioi_tinh' => $_POST['gioi_tinh'],
                'ma_vai_tro' => $_POST['ma_vai_tro'],
                'kich_hoat' => isset($_POST['kich_hoat']) ? 1 : 0
            ];

            // Nếu có mật khẩu mới
            if (!empty($_POST['mat_khau'])) {
                $data['mat_khau'] = password_hash($_POST['mat_khau'], PASSWORD_DEFAULT);
            }

            if ($this->userModel->updateUser($id, $data)) {
                $_SESSION['success'] = 'Cập nhật tài khoản thành công!';
                header('Location: ' . BASE_URL . '/users');
                exit();
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật tài khoản!';
            }
        }

        // Lấy danh sách vai trò trừ admin
        $roles = $this->roleModel->getAllRolesExceptAdmin();
        require_once __DIR__ . '/../views/users/edit.php';
    }

    public function delete($id) {
        $user = $this->userModel->getUserById($id);
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy người dùng!';
            header('Location: ' . BASE_URL . '/users');
            exit();
        }

        // Kiểm tra nếu đang xóa tài khoản admin
        if ($user['ma_vai_tro'] == 1) {
            $_SESSION['error'] = 'Không thể xóa tài khoản admin!';
            header('Location: ' . BASE_URL . '/users');
            exit();
        }

        if ($this->userModel->deleteUser($id)) {
            $_SESSION['success'] = 'Xóa tài khoản thành công!';
        } else {
            $_SESSION['error'] = 'Có lỗi xảy ra khi xóa tài khoản!';
        }
        header('Location: ' . BASE_URL . '/users');
        exit();
    }
} 