<?php
// Define routes
$routes = [
    // Home routes
    '/' => ['controller' => 'HomeController', 'action' => 'index'],
    '/home' => ['controller' => 'HomeController', 'action' => 'index'],
    
    // Auth routes
    '/login' => ['controller' => 'AuthController', 'action' => 'login'],
    '/register' => ['controller' => 'AuthController', 'action' => 'register'],
    '/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    
    // Event routes
    '/events' => ['controller' => 'EventController', 'action' => 'index'],
    '/events/create' => ['controller' => 'EventController', 'action' => 'create'],
    '/events/store' => ['controller' => 'EventController', 'action' => 'store'],
    '/events/edit/{id}' => ['controller' => 'EventController', 'action' => 'edit'],
    '/events/update/{id}' => ['controller' => 'EventController', 'action' => 'update'],
    '/events/delete/{id}' => ['controller' => 'EventController', 'action' => 'delete'],
    '/events/manage' => ['controller' => 'EventController', 'action' => 'manage'],
    '/event/{id}' => ['controller' => 'EventController', 'action' => 'show'],
    
    // Organizer event routes
    '/organizer/events' => ['controller' => 'OrganizerEventController', 'action' => 'index'],
    '/organizer/events/update' => ['controller' => 'OrganizerEventController', 'action' => 'update'],
    '/organizer/events/edit/{id}' => ['controller' => 'OrganizerEventController', 'action' => 'edit'],
    '/organizer/events/delete-ticket' => ['controller' => 'OrganizerEventController', 'action' => 'deleteTicket'],
    
    // Admin routes
    '/admin/revenue' => ['controller' => 'AdminController', 'action' => 'revenue'],
    '/admin/export-revenue-csv' => ['controller' => 'AdminController', 'action' => 'exportRevenueCSV'],
    '/admin/compare-revenue' => ['controller' => 'AdminController', 'action' => 'compareRevenue'],
    '/venow/admin/revenue' => ['controller' => 'AdminController', 'action' => 'revenue'],
    '/admin/pending-events' => ['controller' => 'AdminController', 'action' => 'pendingEvents'],
    '/admin/approve-event' => ['controller' => 'AdminController', 'action' => 'approveEvent'],
    '/admin/reject-event' => ['controller' => 'AdminController', 'action' => 'rejectEvent'],
    '/admin/revenue/export-csv' => ['controller' => 'AdminController', 'action' => 'exportRevenueCSV'],
    '/admin/revenue/compare' => ['controller' => 'AdminController', 'action' => 'compareRevenue'],
    
    // Staff routes
    '/staff/pending-events' => ['controller' => 'StaffController', 'action' => 'pendingEvents'],
    '/staff/approve-event' => ['controller' => 'StaffController', 'action' => 'approveEvent'],
    '/staff/reject-event' => ['controller' => 'StaffController', 'action' => 'rejectEvent'],
    '/reviews' => ['controller' => 'StaffController', 'action' => 'reviews'],
    '/staff/reviews/approve' => ['controller' => 'StaffController', 'action' => 'approveReview'],
    '/staff/reviews/reject' => ['controller' => 'StaffController', 'action' => 'rejectReview'],
    
    // Review routes
    '/review' => ['controller' => 'ReviewController', 'action' => 'index'],
    '/reviews/approve' => ['controller' => 'ReviewController', 'action' => 'approve'],
    '/reviews/reject' => ['controller' => 'ReviewController', 'action' => 'reject'],
    
    // User routes
    '/users' => ['controller' => 'UserController', 'action' => 'index'],
    '/users/create' => ['controller' => 'UserController', 'action' => 'create'],
    '/users/store' => ['controller' => 'UserController', 'action' => 'store'],
    '/users/edit/{id}' => ['controller' => 'UserController', 'action' => 'edit'],
    '/users/update/{id}' => ['controller' => 'UserController', 'action' => 'update'],
    '/users/delete/{id}' => ['controller' => 'UserController', 'action' => 'delete'],
    
    // Account routes
    '/account' => ['controller' => 'AccountController', 'action' => 'index'],
    '/account/update' => ['controller' => 'AccountController', 'action' => 'update'],
    '/account/change-password' => ['controller' => 'AccountController', 'action' => 'changePassword'],
    
    // Ticket routes
    '/tickets/history' => ['controller' => 'TicketController', 'action' => 'history'],
    '/tickets/download/{id}' => ['controller' => 'TicketController', 'action' => 'download'],
    '/tickets/refund/{id}' => ['controller' => 'TicketController', 'action' => 'refund'],
    '/tickets/my-tickets' => ['controller' => 'TicketController', 'action' => 'myTickets'],
    '/tickets/qr/{id}' => ['controller' => 'TicketController', 'action' => 'generateQR'],
    '/tickets/qr/download/{id}' => ['controller' => 'TicketController', 'action' => 'downloadQR'],
    
    // Search routes
    '/search' => ['controller' => 'SearchController', 'action' => 'index'],
    
    // Comment routes
    '/event/comment/add' => ['controller' => 'EventController', 'action' => 'addComment'],
    
    // Booking routes
    '/booking/{id}' => ['controller' => 'BookingController', 'action' => 'index'],
    '/booking/process-selection' => ['controller' => 'BookingController', 'action' => 'processSelection'],
    '/booking/payment' => ['controller' => 'BookingController', 'action' => 'payment'],
    '/booking/process-payment' => ['controller' => 'BookingController', 'action' => 'processPayment'],
    
    // Points routes
    '/points' => ['controller' => 'PointsController', 'action' => 'index'],
    '/points/history' => ['controller' => 'PointsController', 'action' => 'history'],
];

// Get current URL path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/venow', '', $path);
if ($path === '') {
    $path = '/';

}

// Debug
error_log("Current path: " . $path);

// Match route
$routeFound = false;
$params = [];

foreach ($routes as $route => $handler) {
    // Convert route to regex pattern
    $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route);
    $pattern = '#^' . $pattern . '$#';
    
    // Debug
    error_log("Checking route: " . $route . " against pattern: " . $pattern);
    
    if (preg_match($pattern, $path, $matches)) {
        $routeFound = true;
        
        // Extract parameters
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        
        // Debug
        error_log("Route matched: " . $route);
        error_log("Parameters: " . print_r($params, true));
        
        // Load controller and call action
        $controllerName = $handler['controller'];
        $actionName = $handler['action'];
        
        $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
        if (!file_exists($controllerFile)) {
            error_log("Controller file not found: " . $controllerFile);
            break;
        }
        
        require_once $controllerFile;
        $controller = new $controllerName();
        
        if (!method_exists($controller, $actionName)) {
            error_log("Action method not found: " . $actionName);
            break;
        }
        
        call_user_func_array([$controller, $actionName], $params);
        break;
    }
}

// If no route found, show 404 page
if (!$routeFound) {
    error_log("No route found for path: " . $path);
    http_response_code(404);
    require_once __DIR__ . '/error/404.php';
}

switch ($path) {
    case '/admin/revenue/export-csv':
        $adminController = new AdminController();
        $adminController->exportRevenueCSV();
        break;

    case '/admin/revenue/compare':
        $adminController = new AdminController();
        $adminController->compareRevenue();
        break;
}
