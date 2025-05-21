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
        // Lấy các tham số tìm kiếm
        $keyword = isset($_GET['q']) && trim($_GET['q']) !== '' ? trim($_GET['q']) : null;
        $category = isset($_GET['category']) && trim($_GET['category']) !== '' ? trim($_GET['category']) : null;
        $date = isset($_GET['date']) && trim($_GET['date']) !== '' ? trim($_GET['date']) : null;
        $location = isset($_GET['location']) && trim($_GET['location']) !== '' ? trim($_GET['location']) : null;
        $price = isset($_GET['price']) && trim($_GET['price']) !== '' ? trim($_GET['price']) : null;

        // Lấy danh sách loại sự kiện
        $stmt = $this->db->prepare("SELECT * FROM loaisukien ORDER BY tenloaisukien ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Câu truy vấn cơ sở
        $query = "SELECT DISTINCT s.*, nd.ho_ten, l.tenloaisukien,
                COALESCE(MIN(t.gia_ve), 0) as gia_ve_min,
                COALESCE(MAX(t.gia_ve), 0) as gia_ve_max
                FROM sukien s 
                LEFT JOIN nguoidung nd ON s.ma_nguoi_dung = nd.ma_nguoi_dung AND nd.ma_vai_tro = 2
                LEFT JOIN loaisukien l ON s.maloaisukien = l.maloaisukien
                LEFT JOIN loaive t ON s.ma_su_kien = t.ma_su_kien
                WHERE s.trang_thai = 'DA_DUYET'";

        $params = [];

        // Tìm kiếm từ khóa không dấu
        if ($keyword !== null) {
            $keywordNoAccent = $this-> xoaDauTiengViet(mb_strtolower($keyword, 'UTF-8'));
            $query .= " AND (
                LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(s.ten_su_kien, '̀',''), '́',''), '̃',''), '̣',''), '̉',''), 'đ','d'), 'Đ','D')) LIKE ? OR 
                LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(s.mo_ta, '̀',''), '́',''), '̃',''), '̣',''), '̉',''), 'đ','d'), 'Đ','D')) LIKE ? OR 
                LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(s.dia_diem, '̀',''), '́',''), '̃',''), '̣',''), '̉',''), 'đ','d'), 'Đ','D')) LIKE ?
            )";
            $searchTerm = "%" . $keywordNoAccent . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Lọc theo loại sự kiện
        if ($category !== null) {
            $query .= " AND s.maloaisukien = ?";
            $params[] = $category;
        }

        // Lọc theo ngày
        if ($date !== null) {
            $query .= " AND DATE(s.ngay_dien_ra) = ?";
            $params[] = $date;
        }

        // Lọc theo địa điểm (không dấu)
        if ($location !== null) {
            $locationNoAccent = $this-> xoaDauTiengViet(mb_strtolower($location, 'UTF-8'));
            $query .= " AND LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(s.dia_diem, '̀',''), '́',''), '̃',''), '̣',''), '̉',''), 'đ','d'), 'Đ','D')) LIKE ?";
            $params[] = "%" . $locationNoAccent . "%";
        }

        // Lọc theo giá vé
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

        // Nhóm và sắp xếp
        $query .= " GROUP BY s.ma_su_kien ORDER BY s.ngay_dien_ra DESC";

        // Thực thi truy vấn
        $stmt = $this->db->prepare($query);
        if (!empty($params)) {
            foreach ($params as $index => $param) {
                $stmt->bindValue($index + 1, $param);
            }
        }
        $stmt->execute();
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Lấy thông tin loại sự kiện nếu có lọc
        $categoryInfo = null;
        if ($category !== null) {
            $stmt = $this->db->prepare("SELECT * FROM loaisukien WHERE maloaisukien = ?");
            $stmt->execute([$category]);
            $categoryInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        }

    // Gọi view
    require_once __DIR__ . '/../views/search/index.php';
    }

    private function xoaDauTiengViet($str) {
        $str = preg_replace([
            "/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u",
            "/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u",
            "/(ì|í|ị|ỉ|ĩ)/u",
            "/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u",
            "/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u",
            "/(ỳ|ý|ỵ|ỷ|ỹ)/u",
            "/(đ)/u",
            "/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/u",
            "/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/u",
            "/(Ì|Í|Ị|Ỉ|Ĩ)/u",
            "/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/u",
            "/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/u",
            "/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/u",
            "/(Đ)/u"
        ], [
            "a", "e", "i", "o", "u", "y", "d",
            "A", "E", "I", "O", "U", "Y", "D"
        ], $str);
        return $str;
    }


    public function search() {
        // Redirect to index method to avoid duplicate code
        $this->index();
    }
}
