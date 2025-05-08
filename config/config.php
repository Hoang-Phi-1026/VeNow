<?php
// Định nghĩa đường dẫn gốc của ứng dụng
define('BASE_PATH', dirname(__DIR__));

// Định nghĩa URL gốc của ứng dụng
define('BASE_URL', 'http://localhost/venow');

// Định nghĩa các hằng số khác
define('DEFAULT_CONTROLLER', 'Home');
define('DEFAULT_ACTION', 'index');

// Định nghĩa các thông báo lỗi
define('ERROR_MESSAGES', [
    'required' => 'Vui lòng điền đầy đủ thông tin',
    'email' => 'Email không hợp lệ',
    'phone' => 'Số điện thoại không hợp lệ',
    'password' => 'Mật khẩu phải có ít nhất 6 ký tự',
    'password_match' => 'Mật khẩu không khớp',
    'email_exists' => 'Email đã tồn tại',
    'login_failed' => 'Email hoặc mật khẩu không đúng',
    'not_logged_in' => 'Vui lòng đăng nhập để tiếp tục',
    'not_admin' => 'Bạn không có quyền truy cập trang này'
]); 