<?php
require_once __DIR__ . '/../models/Points.php';
require_once __DIR__ . '/../models/User.php';

class PointsController extends BaseController {
    private $pointsModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->pointsModel = new Points();
        $this->userModel = new User();
        
        // Kiểm tra đăng nhập
        if (!isset($_SESSION['user'])) {
            header('Location: ' . BASE_URL . '/login');
            exit();
        }
    }

    public function index() {
        $userId = $_SESSION['user']['ma_nguoi_dung'];
        
        // Lấy thông tin điểm tích lũy
        $totalPoints = $this->pointsModel->getTotalPoints($userId);
        $pointsHistory = $this->pointsModel->getPointsHistory($userId);
        $pointsStats = $this->pointsModel->getPointsStatistics($userId);
        $recentTransactions = $this->pointsModel->getRecentTransactions($userId, 5);
        
        // Lấy thông tin người dùng
        $user = $this->userModel->getUserById($userId);
        
        // Lấy thông tin về các ưu đãi hiện có
        $availableRewards = $this->pointsModel->getAvailableRewards();
        
        require_once __DIR__ . '/../views/points/index.php';
    }
    
    public function history() {
        $userId = $_SESSION['user']['ma_nguoi_dung'];
        
        // Phân trang
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // Lọc theo loại
        $type = isset($_GET['type']) ? $_GET['type'] : '';
        
        // Lấy lịch sử điểm tích lũy với phân trang
        $pointsHistory = $this->pointsModel->getPointsHistoryPaginated($userId, $limit, $offset, $type);
        $totalRecords = $this->pointsModel->countPointsHistory($userId, $type);
        
        $totalPages = ceil($totalRecords / $limit);
        
        require_once __DIR__ . '/../views/points/history.php';
    }
}
