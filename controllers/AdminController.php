<?php
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/Revenue.php';
require_once __DIR__ . '/BaseController.php';

class AdminController extends BaseController {
    private $eventModel;
    private $revenueModel;

    public function __construct() {
        parent::__construct();
        $this->eventModel = new Event();
        $this->revenueModel = new Revenue();
        
        // Kiểm tra quyền admin
        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 1) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public function pendingEvents() {
        $events = $this->eventModel->getPendingEvents();
        require_once __DIR__ . '/../views/admin/pending_events.php';
    }

    public function approveEvent() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = $_POST['event_id'] ?? null;
            if ($eventId) {
                $this->eventModel->updateEventStatus($eventId, 'DA_DUYET');
                $_SESSION['success'] = 'Đã duyệt sự kiện thành công!';
            }
        }
        header('Location: ' . BASE_URL . '/admin/pending-events');
        exit;
    }

    public function rejectEvent() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = $_POST['event_id'] ?? null;
            if ($eventId) {
                $this->eventModel->updateEventStatus($eventId, 'TU_CHOI');
                $_SESSION['success'] = 'Đã từ chối sự kiện!';
            }
        }
        header('Location: ' . BASE_URL . '/admin/pending-events');
        exit;
    }
    
    public function revenue() {
        // Xử lý filter
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $year = $_GET['year'] ?? date('Y');
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        
        // Nếu không có ngày bắt đầu và kết thúc, mặc định lấy 30 ngày gần nhất
        if (!$startDate && !$endDate) {
            $endDate = date('Y-m-d');
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        
        // Lấy dữ liệu doanh thu
        $totalRevenue = $this->revenueModel->getTotalRevenue($startDate, $endDate);
        $dailyRevenue = $this->revenueModel->getRevenueByDay($startDate, $endDate);
        $monthlyRevenue = $this->revenueModel->getRevenueByMonth($year);
        $quarterlyRevenue = $this->revenueModel->getRevenueByQuarter($year);
        $yearlyRevenue = $this->revenueModel->getRevenueByYear(date('Y')-4, date('Y'));
        $revenueByPaymentMethod = $this->revenueModel->getRevenueByPaymentMethod($startDate, $endDate);
        $revenueByEventType = $this->revenueModel->getRevenueByEventType($startDate, $endDate);
        $revenueByEvent = $this->revenueModel->getRevenueByEvent($startDate, $endDate, 5);
        $revenueByOrganizer = $this->revenueModel->getRevenueByOrganizer($startDate, $endDate);
        $ticketsByType = $this->revenueModel->getTicketsSoldByType($startDate, $endDate);
        $topCustomers = $this->revenueModel->getTopCustomers($startDate, $endDate, 5);
        
        // Lấy chi tiết giao dịch
        $transactions = $this->revenueModel->getTransactionDetails($startDate, $endDate, $page, $limit);
        $totalTransactions = $this->revenueModel->countTransactions($startDate, $endDate);
        $totalPages = ceil($totalTransactions / $limit);
        
        // Tính toán các chỉ số thống kê
        $totalTickets = $totalTransactions; // Tổng số vé chính là tổng số giao dịch
        $totalTicketsSold = $this->revenueModel->getTotalTicketsSold($startDate, $endDate); // Lấy tổng số vé đã bán thực tế
        $totalEvents = count(array_unique(array_column($transactions, 'ten_su_kien')));
        
        // Tính doanh thu trung bình mỗi giao dịch
        $averageTicketPrice = $totalTickets > 0 ? $totalRevenue / $totalTickets : 0;
        
        // Chuẩn bị dữ liệu cho biểu đồ
        $dailyLabels = [];
        $dailyData = [];
        foreach ($dailyRevenue as $day) {
            $dailyLabels[] = date('d/m', strtotime($day['date']));
            $dailyData[] = $day['total'];
        }
        
        $monthlyLabels = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 
                          'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];
        $monthlyData = array_values($monthlyRevenue);
        
        $quarterlyLabels = ['Quý 1', 'Quý 2', 'Quý 3', 'Quý 4'];
        $quarterlyData = array_values($quarterlyRevenue);
        
        $yearlyLabels = array_keys($yearlyRevenue);
        $yearlyData = array_values($yearlyRevenue);
        
        require_once __DIR__ . '/../views/admin/revenue.php';
    }
    
    public function exportRevenueCSV() {
        // Xử lý filter
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        // Lấy dữ liệu để xuất CSV
        $data = $this->revenueModel->getDataForCSV($startDate, $endDate);
        
        // Tên file
        $filename = 'doanh-thu-' . date('Y-m-d') . '.csv';
        
        // Header cho CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Mở output stream
        $output = fopen('php://output', 'w');
        
        // Thêm BOM để hỗ trợ UTF-8 trong Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Thêm header
        fputcsv($output, [
            'ID', 'Ngày giao dịch', 'Số tiền', 'Phương thức thanh toán',
            'Người dùng', 'Email', 'Sự kiện', 'Ngày diễn ra', 'Loại vé', 'Giá vé',
            'Loại sự kiện', 'Người tổ chức'
        ]);
        
        // Thêm dữ liệu
        foreach ($data as $row) {
            fputcsv($output, [
                $row['ma_giao_dich'],
                $row['ngay_giao_dich'],
                number_format($row['so_tien'], 0, ',', '.') . ' VNĐ',
                $row['phuong_thuc_thanh_toan'],
                $row['ten_nguoi_dung'],
                $row['email'],
                $row['ten_su_kien'],
                $row['ngay_dien_ra'],
                $row['ten_loai_ve'],
                number_format($row['gia_ve'], 0, ',', '.') . ' VNĐ',
                $row['loai_su_kien'],
                $row['ten_nguoi_to_chuc']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function compareRevenue() {
        // Xử lý filter
        $period1Start = $_GET['period1_start'] ?? date('Y-m-d', strtotime('-60 days'));
        $period1End = $_GET['period1_end'] ?? date('Y-m-d', strtotime('-31 days'));
        $period2Start = $_GET['period2_start'] ?? date('Y-m-d', strtotime('-30 days'));
        $period2End = $_GET['period2_end'] ?? date('Y-m-d');
        
        // Lấy dữ liệu doanh thu cho 2 khoảng thời gian
        $period1Revenue = $this->revenueModel->getTotalRevenue($period1Start, $period1End);
        $period2Revenue = $this->revenueModel->getTotalRevenue($period2Start, $period2End);
        
        // Tính tỷ lệ tăng trưởng
        $growthRate = 0;
        if ($period1Revenue > 0) {
            $growthRate = (($period2Revenue - $period1Revenue) / $period1Revenue) * 100;
        }
        
        // Lấy doanh thu theo loại sự kiện cho 2 khoảng thời gian
        $period1EventTypeRevenue = $this->revenueModel->getRevenueByEventType($period1Start, $period1End);
        $period2EventTypeRevenue = $this->revenueModel->getRevenueByEventType($period2Start, $period2End);
        
        // Lấy doanh thu theo phương thức thanh toán cho 2 khoảng thời gian
        $period1PaymentMethodRevenue = $this->revenueModel->getRevenueByPaymentMethod($period1Start, $period1End);
        $period2PaymentMethodRevenue = $this->revenueModel->getRevenueByPaymentMethod($period2Start, $period2End);
        
        require_once __DIR__ . '/../views/admin/revenue_compare.php';
    }
}
