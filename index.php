<?php
// Thiết lập múi giờ
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Định nghĩa đường dẫn gốc
define('BASE_PATH', __DIR__);
define('BASE_URL', 'http://localhost/venow');

// Include database config
require_once BASE_PATH . '/config/database.php';

// Include models
require_once BASE_PATH . '/models/User.php';
require_once BASE_PATH . '/models/Event.php';

// Include controllers
require_once BASE_PATH . '/controllers/BaseController.php';
require_once BASE_PATH . '/controllers/AuthController.php';
require_once BASE_PATH . '/controllers/EventController.php';
require_once BASE_PATH . '/controllers/SearchController.php';
require_once BASE_PATH . '/controllers/AccountController.php';
require_once BASE_PATH . '/controllers/UserController.php';
require_once BASE_PATH . '/controllers/AdminController.php';
require_once BASE_PATH . '/controllers/OrganizerEventController.php';

// Lấy đường dẫn hiện tại
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = parse_url(BASE_URL, PHP_URL_PATH);
$path = substr($request_uri, strlen($base_path));

// Loại bỏ query string nếu có
if (($pos = strpos($path, '?')) !== false) {
    $path = substr($path, 0, $pos);
}

// Xử lý các route
switch ($path) {
    case '/login':
        $authController = new AuthController();
        $authController->showLoginForm();
        break;

    case '/register':
        $authController = new AuthController();
        $authController->showRegisterForm();
        break;

    case '/auth/register':
        $authController = new AuthController();
        $authController->register();
        break;

    case '/auth/login':
        $authController = new AuthController();
        $authController->login();
        break;

    case '/logout':
        $authController = new AuthController();
        $authController->logout();
        break;

    case '/search':
        $searchController = new SearchController();
        $searchController->search();
        break;

    case '/users':
        $userController = new UserController();
        $userController->index();
        break;

    case '/users/create':
        $userController = new UserController();
        $userController->create();
        break;

    case '/admin/pending-events':
        $adminController = new AdminController();
        $adminController->pendingEvents();
        break;

    case '/admin/approve-event':
        $adminController = new AdminController();
        $adminController->approveEvent();
        break;

    case '/admin/reject-event':
        $adminController = new AdminController();
        $adminController->rejectEvent();
        break;

    case '/organizer/events':
        $organizerEventController = new OrganizerEventController();
        $organizerEventController->index();
        break;

    case '/organizer/events/edit':
        $organizerEventController = new OrganizerEventController();
        $organizerEventController->edit($_GET['id']);
        break;

    case '/organizer/events/delete':
        $organizerEventController = new OrganizerEventController();
        $organizerEventController->delete($_GET['id']);
        break;

    case '/account':
        $accountController = new AccountController();
        $accountController->index();
        break;

    case '/account/update':
        $accountController = new AccountController();
        $accountController->update();
        break;

    case '/':
    case '':
        $eventController = new EventController();
        $eventController->index();
        break;

    case '/events/create':
        $eventController = new EventController();
        $eventController->create();
        break;

    case '/events/store':
        $eventController = new EventController();
        $eventController->store();
        break;

    default:
        // Kiểm tra xem có phải là route chi tiết sự kiện không
        if (preg_match('/^\/event\/(\d+)$/', $path, $matches)) {
            $eventController = new EventController();
            $eventController->show($matches[1]);
        } 
        // Kiểm tra xem có phải là route chỉnh sửa người dùng không
        else if (preg_match('/^\/users\/edit\/(\d+)$/', $path, $matches)) {
            $userController = new UserController();
            $userController->edit($matches[1]);
        }
        // Kiểm tra xem có phải là route xóa người dùng không
        else if (preg_match('/^\/users\/delete\/(\d+)$/', $path, $matches)) {
            $userController = new UserController();
            $userController->delete($matches[1]);
        }
        else {
            // Nếu không khớp với route nào, hiển thị trang 404
            http_response_code(404);
            require_once BASE_PATH . '/error/404.php';
        }
        break;
}
