<?php
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/BaseController.php';

class SearchController extends BaseController {
    private $eventModel;

    public function __construct() {
        parent::__construct();
        $this->eventModel = new Event();
    }

    public function index() {
        $keyword = $_GET['q'] ?? '';
        $date = $_GET['date'] ?? null;
        $location = $_GET['location'] ?? null;

        $query = "SELECT s.*, n.tennhatochuc, l.tenloaisukien,
                    MIN(t.gia_ve) as gia_ve_min,
                    MAX(t.gia_ve) as gia_ve_max
             FROM sukien s 
             LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
             LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
             LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
             WHERE s.trang_thai = 'DA_DUYET'";
    
        $params = [];
    
        if (!empty($keyword)) {
            $query .= " AND (s.ten_su_kien LIKE ? OR s.mo_ta LIKE ? OR s.dia_diem LIKE ?)";
            $keyword = "%$keyword%";
            $params = array_merge($params, [$keyword, $keyword, $keyword]);
        }
    
        if (!empty($date)) {
            $query .= " AND DATE(s.ngay_dien_ra) = ?";
            $params[] = $date;
        }
    
        if (!empty($location)) {
            $query .= " AND s.dia_diem LIKE ?";
            $params[] = "%$location%";
        }
    
        $query .= " GROUP BY s.ma_su_kien ORDER BY s.ngay_dien_ra DESC";
    
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require_once __DIR__ . '/../views/search/index.php';
    }

    public function search() {
        $keyword = $_GET['keyword'] ?? '';
        $category = $_GET['category'] ?? '';
        $date = $_GET['date'] ?? '';
        $location = $_GET['location'] ?? '';
        $price = $_GET['price'] ?? '';

        // Lấy danh sách danh mục
        $stmt = $this->db->prepare("SELECT * FROM loaisukien ORDER BY tenloaisukien ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT DISTINCT s.*, n.tennhatochuc, l.tenloaisukien,
                MIN(t.gia_ve) as gia_ve_min,
                MAX(t.gia_ve) as gia_ve_max
         FROM sukien s 
         LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
         LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
         LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
         WHERE s.trang_thai = 'DA_DUYET'";

        $params = [];

        if (!empty($keyword)) {
            $query .= " AND (s.ten_su_kien LIKE ? OR s.mo_ta LIKE ? OR s.dia_diem LIKE ?)";
            $keyword = "%$keyword%";
            $params = array_merge($params, [$keyword, $keyword, $keyword]);
        }

        if (!empty($category)) {
            $query .= " AND s.maloaisukien = ?";
            $params[] = $category;
        }

        if (!empty($date)) {
            $query .= " AND DATE(s.ngay_dien_ra) = ?";
            $params[] = $date;
        }

        if (!empty($location)) {
            $query .= " AND s.dia_diem LIKE ?";
            $params[] = "%$location%";
        }

        if (!empty($price)) {
            switch ($price) {
                case 'free':
                    $query .= " AND t.gia_ve = 0";
                    break;
                case 'paid':
                    $query .= " AND t.gia_ve > 0";
                    break;
            }
        }

        $query .= " GROUP BY s.ma_su_kien ORDER BY s.ngay_dien_ra DESC";

        // Debug: In ra câu truy vấn và tham số
        error_log("Search Query: " . $query);
        error_log("Search Params: " . print_r($params, true));

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debug: In ra số lượng kết quả
        error_log("Number of results: " . count($events));

        // Lấy thông tin danh mục nếu có
        $categoryInfo = null;
        if (!empty($category)) {
            $stmt = $this->db->prepare("SELECT * FROM loaisukien WHERE maloaisukien = ?");
            $stmt->execute([$category]);
            $categoryInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        require_once __DIR__ . '/../views/search/index.php';
    }

    private function applyFilters($events, $category, $date, $price, $location, $featured = false, $upcoming = false) {
        if (empty($events)) {
            return [];
        }

        return array_filter($events, function($event) use ($category, $date, $price, $location, $featured, $upcoming) {
            // Lọc theo danh mục
            if (!empty($category) && $event['danh_muc'] !== $category) {
                return false;
            }

            // Lọc theo ngày
            if (!empty($date)) {
                $eventDate = strtotime($event['ngay_dien_ra']);
                $today = strtotime('today');
                $tomorrow = strtotime('tomorrow');
                $weekEnd = strtotime('+7 days');
                $monthEnd = strtotime('+30 days');

                switch ($date) {
                    case 'today':
                        if ($eventDate < $today || $eventDate >= $tomorrow) return false;
                        break;
                    case 'tomorrow':
                        if ($eventDate < $tomorrow || $eventDate >= strtotime('+2 days')) return false;
                        break;
                    case 'week':
                        if ($eventDate < $today || $eventDate >= $weekEnd) return false;
                        break;
                    case 'month':
                        if ($eventDate < $today || $eventDate >= $monthEnd) return false;
                        break;
                }
            }

            // Lọc theo giá
            if (!empty($price)) {
                $minPrice = $event['gia_ve_thap_nhat'] ?? 0;
                switch ($price) {
                    case 'free':
                        if ($minPrice > 0) return false;
                        break;
                    case 'paid':
                        if ($minPrice <= 0) return false;
                        break;
                }
            }

            // Lọc theo địa điểm
            if (!empty($location)) {
                $eventLocation = strtolower($event['dia_diem']);
                if (strpos($eventLocation, strtolower($location)) === false) {
                    return false;
                }
            }

            // Lọc theo sự kiện nổi bật
            if ($featured) {
                $eventDate = strtotime($event['ngay_dien_ra']);
                $today = strtotime('today');
                if ($eventDate < $today) return false;
            }

            // Lọc theo sự kiện sắp diễn ra
            if ($upcoming) {
                $eventDate = strtotime($event['ngay_dien_ra']);
                $today = strtotime('today');
                $monthEnd = strtotime('+30 days');
                if ($eventDate < $today || $eventDate >= $monthEnd) return false;
            }

            return true;
        });
    }
}
