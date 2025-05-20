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
require_once BASE_PATH . '/models/Ticket.php';
require_once BASE_PATH . '/models/Booking.php';

// Include controllers
require_once BASE_PATH . '/controllers/BaseController.php';
require_once BASE_PATH . '/controllers/AuthController.php';
require_once BASE_PATH . '/controllers/EventController.php';
require_once BASE_PATH . '/controllers/SearchController.php';
require_once BASE_PATH . '/controllers/AccountController.php';
require_once BASE_PATH . '/controllers/UserController.php';
require_once BASE_PATH . '/controllers/AdminController.php';
require_once BASE_PATH . '/controllers/OrganizerEventController.php';
require_once BASE_PATH . '/controllers/TicketController.php';
require_once BASE_PATH . '/controllers/StaffController.php';
require_once BASE_PATH . '/controllers/BookingController.php';
require_once BASE_PATH . '/controllers/PointsController.php';
require_once BASE_PATH . '/controllers/MomoPaymentController.php';


// Lấy đường dẫn hiện tại
$request_uri = $_SERVER['REQUEST_URI'];
$base_path = parse_url(BASE_URL, PHP_URL_PATH);
$path = substr($request_uri, strlen($base_path));

// Loại bỏ query string nếu có
if (($pos = strpos($path, '?')) !== false) {
    $path = substr($path, 0, $pos);
}

// Debug log
error_log("Processing path: " . $path);

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

    case '/tickets/history':
        $ticketController = new TicketController();
        $ticketController->history();
        break;
    case '/tickets/my-tickets':
        $ticketController = new TicketController();
        $ticketController->myTickets();
        break;

    case '/tickets/refund':
        $ticketController = new TicketController();
        $ticketController->refund($_GET['id']);
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

    case '/events/manage':
        $eventController = new EventController();
        $eventController->manage();
        break;

    case '/events/store':
        $eventController = new EventController();
        $eventController->store();
        break;

    case '/event/:id':
        $eventController = new EventController();
        $eventController->show($path);
        break;

    case '/event/category/:id':
        $eventController = new EventController();
        $eventController->category($path);
        break;

    case '/event/comment/add':
        $eventController = new EventController();
        $eventController->addComment();
        break;

    case '/points':
        $pointsController = new PointsController();
        $pointsController->index();
        break;

    case '/points/history':
        $pointsController = new PointsController();
        $pointsController->history();
        break;

    // Staff routes
    case '/reviews':
    case '/staff/reviews':
        $staffController = new StaffController();
        $staffController->reviews();
        break;

    case '/staff/reviews/approve':
        $staffController = new StaffController();
        $commentId = isset($_POST['id']) ? $_POST['id'] : null;
        if ($commentId) {
            $staffController->approveReview($commentId);
        } else {
            header('Location: ' . BASE_URL . '/reviews');
        }
        break;

    case '/staff/reviews/reject':
        $staffController = new StaffController();
        $commentId = isset($_POST['id']) ? $_POST['id'] : null;
        if ($commentId) {
            $staffController->rejectReview($commentId);
        } else {
            header('Location: ' . BASE_URL . '/reviews');
        }
        break;

    case '/staff/pending-events':
        $staffController = new StaffController();
        $staffController->pendingEvents();
        break;

    case '/staff/approve-event':
        $staffController = new StaffController();
        $staffController->approveEvent();
        break;

    case '/staff/reject-event':
        $staffController = new StaffController();
        $staffController->rejectEvent();
        break;

    case '/about':
        require_once BASE_PATH . '/views/about/index.php';
        break;

    case '/support':
        require_once BASE_PATH . '/views/support/index.php';
        break;

    case '/booking/process-selection':
        $bookingController = new BookingController();
        $bookingController->processSelection();
        break;

    case '/booking/payment':
        $bookingController = new BookingController();
        $bookingController->payment();
        break;

    case '/booking/process-payment':
        $bookingController = new BookingController();
        $bookingController->processPayment();
        break;

<<<<<<< HEAD
    case '/momo-payment/process':
        $momoPaymentController = new MomoPaymentController();
        $momoPaymentController->processPayment();
        break;

    case '/momo-payment/ipn':
        $momoPaymentController = new MomoPaymentController();
        $momoPaymentController->ipn();
        break;

    case '/momo-payment/return':
        $momoPaymentController = new MomoPaymentController();
        $momoPaymentController->return();
        break;

    case '/momo-payment/thanks':
        $momoPaymentController = new MomoPaymentController();
        $momoPaymentController->thanks();
=======
    case '/organizer/revenue':
        $organizerEventController = new OrganizerEventController();
        $organizerEventController->revenue();
>>>>>>> 29a30cc95cb325296d0375b539830bd2c3adede8
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
        // Kiểm tra xem có phải là route xóa sự kiện không
        else if (preg_match('/^\/events\/delete\/(\d+)$/', $path, $matches)) {
            $eventController = new EventController();
            $eventController->delete($matches[1]);
        }
        // Kiểm tra xem có phải là route đặt vé không
        else if (preg_match('/^\/booking\/(\d+)$/', $path, $matches)) {
            $bookingController = new BookingController();
            $bookingController->index($matches[1]);
        }
        // Kiểm tra xem có phải là route hoàn vé không
        else if (preg_match('/^\/tickets\/refund\/(\d+)$/', $path, $matches)) {
            $ticketController = new TicketController();
            $ticketController->refund($matches[1]);
            error_log("Matched refund route with ID: " . $matches[1]);
        }
        else {
            // Nếu không khớp với route nào, hiển thị trang 404
            error_log("No route matched for path: " . $path);
            http_response_code(404);
            require_once BASE_PATH . '/error/404.php';
        }


        break;
}
