<?php
class BaseController {
    protected $db;

    public function __construct() {
        // Khởi tạo session nếu chưa có
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getInstance();
    }

    protected function render($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Define the view file path
        $viewFile = BASE_PATH . '/views/' . $view . '.php';
        
        // Check if view file exists
        if (!file_exists($viewFile)) {
            die("View file not found: " . $viewFile);
        }
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        require_once $viewFile;
        
        // Get the contents of the buffer and clean it
        $content = ob_get_clean();
        
        // Output the content directly
        echo $content;
    }

    protected function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit();
    }

    protected function renderError($message) {
        $_SESSION['error'] = $message;
    }

    protected function renderSuccess($message) {
        $_SESSION['success'] = $message;
    }

    protected function renderWarning($message) {
        $_SESSION['warning'] = $message;
    }

    protected function renderInfo($message) {
        $_SESSION['info'] = $message;
    }

    protected function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Vui lòng đăng nhập để tiếp tục';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    protected function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    protected function requireAdmin() {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = 'Bạn không có quyền truy cập trang này';
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }
}
