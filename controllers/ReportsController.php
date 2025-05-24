<?php
require_once __DIR__ . '/../controllers/BaseController.php';
require_once __DIR__ . '/../models/Reports.php';

class ReportsController extends BaseController {
    private $reportsModel;
    
    public function __construct() {
        parent::__construct();
        $this->reportsModel = new Reports();

        if (!isset($_SESSION['user']) || $_SESSION['user']['vai_tro'] !== 1) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    
    
    public function attendance() {
        //Kiểm tra quyền admin

        
        //Xử lý tham số
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
        $eventType = isset($_GET['event_type']) ? $_GET['event_type'] : '';
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        
        // Lấy dữ liệu
        $totalAttendance = $this->reportsModel->getTotalAttendance($startDate, $endDate, $eventType);
        $attendanceByMonth = $this->reportsModel->getAttendanceByMonth($startDate, $endDate, $eventType);
        $topEvents = $this->reportsModel->getTopEventsByAttendance($startDate, $endDate, $eventType, 10);
        $attendanceByEventType = $this->reportsModel->getAttendanceByEventType($startDate, $endDate);
        $attendanceDetails = $this->reportsModel->getAttendanceDetails($startDate, $endDate, $eventType);
        $eventTypes = $this->reportsModel->getEventTypes();
        
        // Chuẩn bị dữ liệu cho biểu đồ
        $chartLabels = [];
        $chartData = [];
        
        foreach ($attendanceByMonth as $item) {
            $monthYear = explode('-', $item['month']);
            $monthName = date('M', mktime(0, 0, 0, $monthYear[1], 1));
            $chartLabels[] = $monthName . ' ' . $monthYear[0];
            $chartData[] = $item['total_attendance'];
        }
        
        // Render view
        $this->render('reports/attendance', [
            'totalAttendance' => $totalAttendance,
            'topEvents' => $topEvents,
            'attendanceByEventType' => $attendanceByEventType,
            'attendanceDetails' => $attendanceDetails,
            'eventTypes' => $eventTypes,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'eventType' => $eventType,
            'year' => $year,
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData)
        ]);
    }
    
        public function featured() {
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
        $year = isset($_GET['year']) ? $_GET['year'] : date('Y');
        
        $featuredStats = $this->reportsModel->getFeaturedStats($startDate, $endDate);
        $topRevenue = $this->reportsModel->getTopEventsByRevenue($startDate, $endDate, 5);
        $topRated = $this->reportsModel->getTopEventsByRating($startDate, $endDate, 5);
        $trending = $this->reportsModel->getTrendingEvents($startDate, $endDate, 5);
        $revenueByMonth = $this->reportsModel->getRevenueByMonth($startDate, $endDate);
        
        $chartLabels = [];
        $chartData = [];
        
        foreach ($revenueByMonth as $item) {
            $monthYear = explode('-', $item['month']);
            $monthName = date('M', mktime(0, 0, 0, $monthYear[1], 1));
            $chartLabels[] = $monthName . ' ' . $monthYear[0];
            $chartData[] = $item['total_revenue'];
        }
        
        $this->render('reports/featured', [
            'featuredStats' => $featuredStats,
            'topRevenue' => $topRevenue,
            'topRated' => $topRated,
            'trending' => $trending,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'year' => $year,
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData)
        ]);
    }
        
    // Xuất báo cáo dạng CSV
    public function exportAttendanceCSV() {
 
        
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
        $eventType = isset($_GET['event_type']) ? $_GET['event_type'] : '';
        
        $attendanceDetails = $this->reportsModel->getAttendanceDetails($startDate, $endDate, $eventType);
        
        // Thiết lập header cho file CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=attendance_report_' . date('Y-m-d') . '.csv');
        
        // Tạo file pointer cho output
        $output = fopen('php://output', 'w');
        
        // Thêm BOM để hỗ trợ UTF-8 trong Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Thêm header cho CSV
        fputcsv($output, ['ID Sự kiện', 'Tên sự kiện', 'Loại sự kiện', 'Ngày diễn ra', 'Số người tham gia', 'Số giao dịch', 'Doanh thu', 'Trung bình/giao dịch']);
        
        // Thêm dữ liệu
        foreach ($attendanceDetails as $row) {
            fputcsv($output, [
                $row['ma_su_kien'],
                $row['ten_su_kien'],
                $row['loai_su_kien'],
                $row['ngay_dien_ra'],
                $row['attendees'],
                $row['transactions'],
                $row['revenue'],
                $row['avg_transaction']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    public function exportFeaturedCSV() {
        if (!$this->isAdmin()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
        
        $topRevenue = $this->reportsModel->getTopEventsByRevenue($startDate, $endDate, 20);
        
        // Thiết lập header cho file CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=featured_events_report_' . date('Y-m-d') . '.csv');
        
        // Tạo file pointer cho output
        $output = fopen('php://output', 'w');
        
        // Thêm BOM để hỗ trợ UTF-8 trong Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Thêm header cho CSV
        fputcsv($output, ['ID Sự kiện', 'Tên sự kiện', 'Loại sự kiện', 'Ngày diễn ra', 'Doanh thu', 'Số giao dịch', 'Số người tham gia']);
        
        // Thêm dữ liệu
        foreach ($topRevenue as $row) {
            fputcsv($output, [
                $row['ma_su_kien'],
                $row['ten_su_kien'],
                $row['loai_su_kien'],
                $row['ngay_dien_ra'],
                $row['total_revenue'],
                $row['total_transactions'],
                $row['total_attendees']
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    protected function isAdmin() {
        return isset($_SESSION['user']) && ($_SESSION['user']['ma_vai_tro'] == 1 || $_SESSION['user']['ma_vai_tro'] == 3);
    }
}
?>