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
            $data = [
                'email' => $_POST['email'],
                'mat_khau' => $_POST['mat_khau'],
                'ho_ten' => $_POST['ho_ten'],
                'so_dien_thoai' => $_POST['so_dien_thoai'],
                'ma_vai_tro' => $_POST['ma_vai_tro']
            ];

            if ($this->userModel->createUser($data)) {
                $_SESSION['success'] = 'Tạo tài khoản thành công!';
                header('Location: ' . BASE_URL . '/users');
                exit();
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi tạo tài khoản!';
            }
        }

        // Lấy danh sách vai trò trừ admin
        $roles = $this->roleModel->getAllRolesExceptAdmin();
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
                'ma_vai_tro' => $_POST['ma_vai_tro'],
                'kich_hoat' => isset($_POST['kich_hoat']) ? 1 : 0
            ];

            // Nếu có mật khẩu mới
            if (!empty($_POST['mat_khau'])) {
                $data['mat_khau'] = $_POST['mat_khau'];
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