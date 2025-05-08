<?php
require_once __DIR__ . '/../models/Event.php';

class SearchController {
    private $eventModel;

    public function __construct() {
        $this->eventModel = new Event();
    }

    public function search() {
        $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
        $category = isset($_GET['category']) ? trim($_GET['category']) : '';
        $date = isset($_GET['date']) ? trim($_GET['date']) : '';
        $price = isset($_GET['price']) ? trim($_GET['price']) : '';
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';
        $featured = isset($_GET['featured']) ? (bool)$_GET['featured'] : false;
        $upcoming = isset($_GET['upcoming']) ? (bool)$_GET['upcoming'] : false;

        try {
            // Nếu có từ khóa tìm kiếm, sử dụng searchEvents
            if (!empty($keyword)) {
                $events = $this->eventModel->searchEvents($keyword);
            } else {
                // Nếu không có từ khóa, lấy tất cả sự kiện
                $events = $this->eventModel->getAllEvents();
            }

            // Áp dụng các bộ lọc
            if (!empty($events)) {
                $events = $this->applyFilters($events, $category, $date, $price, $location, $featured, $upcoming);
            } else {
                $events = [];
            }
        } catch (Exception $e) {
            // Xử lý lỗi nếu có
            error_log("Error in SearchController: " . $e->getMessage());
            $events = [];
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
