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
        // Get search parameters
        $keyword = isset($_GET['q']) && trim($_GET['q']) !== '' ? trim($_GET['q']) : null;
        $category = isset($_GET['category']) && trim($_GET['category']) !== '' ? trim($_GET['category']) : null;
        $date = isset($_GET['date']) && trim($_GET['date']) !== '' ? trim($_GET['date']) : null;
        $location = isset($_GET['location']) && trim($_GET['location']) !== '' ? trim($_GET['location']) : null;
        $price = isset($_GET['price']) && trim($_GET['price']) !== '' ? trim($_GET['price']) : null;

        // Debug log
        error_log("Search parameters: keyword=" . ($keyword ?? 'null') . 
                  ", category=" . ($category ?? 'null') . 
                  ", date=" . ($date ?? 'null') . 
                  ", location=" . ($location ?? 'null') . 
                  ", price=" . ($price ?? 'null'));

        // Get categories for the filter dropdown
        $stmt = $this->db->prepare("SELECT * FROM loaisukien ORDER BY tenloaisukien ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Base query
        $query = "SELECT DISTINCT s.*, n.tennhatochuc, l.tenloaisukien,
                  MIN(t.gia_ve) as gia_ve_min,
                  MAX(t.gia_ve) as gia_ve_max
                  FROM sukien s 
                  LEFT JOIN nhatochuc n ON s.ma_nha_to_chuc = n.manhatochuc 
                  LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                  LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                  WHERE s.trang_thai = 'DA_DUYET'";

        $params = [];

        // Add search conditions
        if ($keyword !== null) {
            $query .= " AND (s.ten_su_kien LIKE ? OR s.mo_ta LIKE ? OR s.dia_diem LIKE ?)";
            $searchTerm = "%" . $keyword . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if ($category !== null) {
            $query .= " AND s.maloaisukien = ?";
            $params[] = $category;
        }

        if ($date !== null) {
            $query .= " AND DATE(s.ngay_dien_ra) = ?";
            $params[] = $date;
        }

        if ($location !== null) {
            $query .= " AND s.dia_diem LIKE ?";
            $params[] = "%" . $location . "%";
        }

        if ($price !== null) {
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

        // Debug: Log the query and parameters
        error_log("Search Query: " . $query);
        error_log("Search Params: " . print_r($params, true));

        // Execute the query
        $stmt = $this->db->prepare($query);
        
        // Bind parameters and execute
        if (!empty($params)) {
            for ($i = 0; $i < count($params); $i++) {
                // PDO parameters are 1-indexed
                $stmt->bindValue($i + 1, $params[$i]);
            }
        }
        
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debug: Log the number of results
        error_log("Number of results: " . count($events));

        // Get category info if category filter is applied
        $categoryInfo = null;
        if ($category !== null) {
            $stmt = $this->db->prepare("SELECT * FROM loaisukien WHERE maloaisukien = ?");
            $stmt->execute([$category]);
            $categoryInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        require_once __DIR__ . '/../views/search/index.php';
    }

    public function search() {
        // Redirect to index method to avoid duplicate code
        $this->index();
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
