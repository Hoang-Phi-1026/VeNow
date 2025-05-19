<?php
require_once __DIR__ . '/../models/User.php';

class AuthController extends BaseController {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function showLoginForm() {
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function showRegisterForm() {
        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $_SESSION['error'] = 'Vui lòng nhập đầy đủ email và mật khẩu';
                $this->redirect('/login');
                return;
            }

            $user = $this->userModel->login($email, $password);

            if ($user) {
                $_SESSION['user'] = [
                    'id' => $user['ma_nguoi_dung'],
                    'ma_nguoi_dung' => $user['ma_nguoi_dung'],  // Add this line to ensure compatibility
                    'email' => $user['email'],
                    'ten_nguoi_dung' => $user['ho_ten'],
                    'vai_tro' => $user['ma_vai_tro']
                ];
                $this->redirect('/');
            } else {
                $_SESSION['error'] = 'Email hoặc mật khẩu không đúng';
                $this->redirect('/login');
            }
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/');
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = $_POST['fullname'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Kiểm tra dữ liệu đầu vào
            if (empty($fullname) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
                $this->redirect('/register');
                return;
            }

            // Kiểm tra mật khẩu khớp nhau
            if ($password !== $confirm_password) {
                $_SESSION['error'] = 'Mật khẩu xác nhận không khớp';
                $this->redirect('/register');
                return;
            }

            // Kiểm tra email đã tồn tại chưa
            if ($this->userModel->checkEmailExists($email)) {
                $_SESSION['error'] = 'Email đã tồn tại';
                $this->redirect('/register');
                return;
            }

            // Tạo tài khoản mới với vai trò là khách hàng (ma_vai_tro = 4)
            $data = [
                'ma_vai_tro' => 4,
                'email' => $email,
                'mat_khau' => $password, // Mật khẩu sẽ được mã hóa trong User::register
                'ho_ten' => $fullname,
                'so_dien_thoai' => $phone
            ];

            if ($this->userModel->register($data)) {
                $_SESSION['success'] = 'Đăng ký thành công! Vui lòng đăng nhập để tiếp tục.';
                $this->redirect('/login');
            } else {
                $_SESSION['error'] = 'Đăng ký thất bại. Vui lòng thử lại sau.';
                $this->redirect('/register');
            }
        }
    }
}