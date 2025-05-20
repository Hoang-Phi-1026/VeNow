<?php
require_once 'controllers/BaseController.php';
require_once 'models/Booking.php';
require_once 'models/Event.php';
require_once 'models/Ticket.php';

class MomoPaymentController extends BaseController {
    private $bookingModel;
    private $eventModel;
    private $ticketModel;

    // MoMo API configuration
    private $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
    private $partnerCode = 'MOMOBKUN20180529';
    private $accessKey = 'klm05TvNBzhg7h7j';
    private $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
    private $ipnUrl;
    private $redirectUrl;

    public function __construct() {
        parent::__construct();
        $this->bookingModel = new Booking();
        $this->eventModel = new Event();
        $this->ticketModel = new Ticket();
        
        // Set URLs based on BASE_URL
        $this->ipnUrl = BASE_URL . "/momo-payment/ipn";
        $this->redirectUrl = BASE_URL . "/momo-payment/return";
    }

    /**
     * Xử lý yêu cầu thanh toán qua MoMo
     */
    public function processPayment() {
        try {
            // Kiểm tra đăng nhập
            if (!isset($_SESSION['user'])) {
                $_SESSION['error'] = 'Vui lòng đăng nhập để thanh toán';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            // Kiểm tra phương thức request
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $_SESSION['error'] = 'Phương thức không hợp lệ';
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }

            // Kiểm tra thông tin đặt chỗ trong session
            if (!isset($_SESSION['booking']) || empty($_SESSION['booking'])) {
                $_SESSION['error'] = 'Dữ liệu đặt vé không hợp lệ hoặc đã hết hạn';
                header('Location: ' . BASE_URL);
                exit;
            }

            // Lấy thông tin từ form
            $paymentMethod = isset($_POST['paymentMethod']) ? $_POST['paymentMethod'] : '';
            $usedPoints = isset($_POST['usedPoints']) ? floatval($_POST['usedPoints']) : 0;
            $discountAmount = isset($_POST['discountAmount']) ? floatval($_POST['discountAmount']) : 0;
            $finalAmount = isset($_POST['finalAmount']) ? floatval($_POST['finalAmount']) : 0;

            // Nếu không phải thanh toán qua MoMo, xử lý như phương thức thanh toán thông thường
            if ($paymentMethod !== 'MOMO') {
                try {
                    // Kết nối database
                    $this->db = Database::getInstance();
                    
                    // Bắt đầu transaction
                    $this->db->beginTransaction();
                    error_log("Transaction started for standard payment");
                    
                    $booking = $_SESSION['booking'];
                    $eventId = $booking['event_id'];
                    $selectedSeats = $booking['selected_seats'];
                    $userId = $_SESSION['user']['ma_nguoi_dung'];
                    
                    error_log("Processing payment for user ID: $userId, event ID: $eventId");
                    error_log("Payment method: $paymentMethod, Used points: $usedPoints, Discount: $discountAmount, Final amount: $finalAmount");
                    
                    // Tính tổng tiền ban đầu
                    $totalAmount = 0;
                    foreach ($selectedSeats as $seatInfo) {
                        $totalAmount += $seatInfo['price'];
                    }
                    
                    // Nếu finalAmount không được cung cấp hoặc không hợp lệ, sử dụng totalAmount
                    if ($finalAmount <= 0) {
                        $finalAmount = $totalAmount;
                    }
                    
                    // Tính tỷ lệ giảm giá để áp dụng cho từng vé
                    $discountRatio = ($totalAmount > 0) ? $finalAmount / $totalAmount : 1;
                    error_log("Total amount: $totalAmount, Final amount: $finalAmount, Discount ratio: $discountRatio");
                    
                    $createdTickets = [];
                    $seatIdToTicketId = []; // Map để lưu trữ mã vé theo mã ghế
                    
                    // Lưu thông tin vé và cập nhật trạng thái ghế
                    foreach ($selectedSeats as $seatId => $seatInfo) {
                        error_log("Processing seat ID: $seatId");
                        
                        // Tạo vé mới
                        $ticketData = [
                            'ma_su_kien' => $eventId,
                            'ma_khach_hang' => $userId,
                            'ma_cho_ngoi' => $seatId,
                            'ma_loai_ve' => $seatInfo['ticketType'],
                            'trang_thai' => 'DA_DAT'
                        ];
                        
                        error_log("Creating ticket with data: " . print_r($ticketData, true));
                        $ticketId = $this->bookingModel->createTicket($ticketData);
                        error_log("Ticket created with ID: $ticketId");
                        
                        $createdTickets[] = $ticketId;
                        $seatIdToTicketId[$seatId] = $ticketId; // Lưu mapping
                        
                        // Cập nhật trạng thái ghế
                        error_log("Updating seat status for seat ID: $seatId");
                        $this->bookingModel->updateSeatStatus($seatId, 'DA_DAT');
                    }
                    
                    // Nếu có sử dụng điểm tích lũy
                    if ($usedPoints > 0) {
                        error_log("Using $usedPoints loyalty points");
                        // Trừ điểm tích lũy đã sử dụng
                        $this->bookingModel->useLoyaltyPoints($userId, $usedPoints);
                    }
                    
                    // Lưu thông tin giao dịch với số tiền đã giảm giá cho từng vé
                    foreach ($selectedSeats as $seatId => $seatInfo) {
                        if (isset($seatIdToTicketId[$seatId])) {
                            $ticketId = $seatIdToTicketId[$seatId];
                            
                            // Tính giá vé sau khi giảm giá
                            $originalPrice = $seatInfo['price'];
                            $discountedPrice = $originalPrice * $discountRatio;
                            
                            error_log("Seat ID: $seatId, Original price: $originalPrice, Discounted price: $discountedPrice");
                            
                            $transactionData = [
                                'ma_khach_hang' => $userId,
                                'ma_ve' => $ticketId,
                                'so_tien' => $discountedPrice, // Giá đã giảm
                                'phuong_thuc_thanh_toan' => $paymentMethod,
                                'trang_thai' => 'THANH_CONG'
                            ];
                            
                            error_log("Creating transaction with data: " . print_r($transactionData, true));
                            $this->bookingModel->createTransaction($transactionData);
                        }
                    }
                    
                    // Tính và lưu điểm tích lũy (dựa trên tổng tiền ban đầu, không phụ thuộc vào giảm giá)
                    $loyaltyPoints = $totalAmount * 0.00003;
                    error_log("Adding $loyaltyPoints loyalty points for user ID: $userId");
                    $this->bookingModel->addLoyaltyPoints($userId, $loyaltyPoints);
                    
                    // Lưu lịch sử đặt vé
                    foreach ($createdTickets as $ticketId) {
                        error_log("Adding ticket history for ticket ID: $ticketId");
                        $this->bookingModel->addTicketHistory($ticketId, $userId, 'DAT_VE', 'Đặt vé thành công');
                    }
                    
                    // Commit transaction
                    error_log("Committing transaction");
                    $this->db->commit();
                    
                    // Lưu thông tin vé đã tạo để hiển thị trên trang cảm ơn
                    $_SESSION['payment_success'] = true;
                    $_SESSION['created_tickets'] = $createdTickets;
                    $_SESSION['payment_amount'] = $finalAmount;
                    $_SESSION['payment_method'] = $paymentMethod;
                    $_SESSION['event_id'] = $eventId;
                    
                    // Xóa thông tin đặt chỗ khỏi session
                    unset($_SESSION['booking']);
                    
                    // Chuyển hướng đến trang vé của tôi
                    header('Location: ' . BASE_URL . '/momo-payment/thanks');
                    exit;
                    
                } catch (Exception $e) {
                    // Rollback transaction nếu có lỗi
                    if (isset($this->db) && $this->db->inTransaction()) {
                        error_log("Rolling back transaction due to error: " . $e->getMessage());
                        $this->db->rollBack();
                    }
                    
                    error_log("Payment processing error: " . $e->getMessage());
                    error_log("Error trace: " . $e->getTraceAsString());
                    
                    // Đặt thông báo lỗi
                    $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình đặt vé: ' . $e->getMessage();
                    
                    // Chuyển hướng về trang thanh toán
                    header('Location: ' . BASE_URL . '/booking/payment');
                    exit;
                }
            }
            
            $booking = $_SESSION['booking'];
            $eventId = $booking['event_id'];
            $selectedSeats = $booking['selected_seats'];
            $userId = $_SESSION['user']['ma_nguoi_dung'];
            
            // Lấy thông tin sự kiện
            $event = $this->eventModel->getEventById($eventId);
            
            // Tạo mã đơn hàng duy nhất
            $orderId = time() . "_" . $userId . "_" . $eventId;
            
            // Lưu thông tin đơn hàng vào session để xử lý sau khi thanh toán
            $_SESSION['momo_payment'] = [
                'order_id' => $orderId,
                'event_id' => $eventId,
                'user_id' => $userId,
                'selected_seats' => $selectedSeats,
                'used_points' => $usedPoints,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'payment_method' => $paymentMethod,
                'timestamp' => time()
            ];
            
            // Tạo thông tin đơn hàng
            $orderInfo = "Thanh toán vé sự kiện: " . $event['ten_su_kien'];
            
            // Tạo dữ liệu bổ sung (có thể sử dụng để lưu thông tin chi tiết)
            $extraData = base64_encode(json_encode([
                'event_id' => $eventId,
                'user_id' => $userId,
                'seats_count' => count($selectedSeats)
            ]));
            
            // Tạo requestId
            $requestId = time() . "";
            $requestType = "captureWallet";
            
            // Tạo chữ ký
            $rawHash = "accessKey=" . $this->accessKey . 
                      "&amount=" . $finalAmount . 
                      "&extraData=" . $extraData . 
                      "&ipnUrl=" . $this->ipnUrl . 
                      "&orderId=" . $orderId . 
                      "&orderInfo=" . $orderInfo . 
                      "&partnerCode=" . $this->partnerCode . 
                      "&redirectUrl=" . $this->redirectUrl . 
                      "&requestId=" . $requestId . 
                      "&requestType=" . $requestType;
                      
            $signature = hash_hmac("sha256", $rawHash, $this->secretKey);
            
            // Tạo dữ liệu gửi đến MoMo
            $data = [
                'partnerCode' => $this->partnerCode,
                'partnerName' => "Venow",
                'storeId' => "VenowStore",
                'requestId' => $requestId,
                'amount' => $finalAmount,
                'orderId' => $orderId,
                'orderInfo' => $orderInfo,
                'redirectUrl' => $this->redirectUrl,
                'ipnUrl' => $this->ipnUrl,
                'lang' => 'vi',
                'extraData' => $extraData,
                'requestType' => $requestType,
                'signature' => $signature
            ];
            
            // Gửi yêu cầu đến MoMo
            $result = $this->execPostRequest($this->endpoint, json_encode($data));
            $jsonResult = json_decode($result, true);
            
            // Kiểm tra kết quả
            if (isset($jsonResult['payUrl'])) {
                // Chuyển hướng đến trang thanh toán của MoMo
                header('Location: ' . $jsonResult['payUrl']);
                exit;
            } else {
                // Xử lý lỗi
                error_log("MoMo payment error: " . print_r($jsonResult, true));
                $_SESSION['error'] = 'Không thể kết nối đến cổng thanh toán MoMo. Vui lòng thử lại sau.';
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
        } catch (Exception $e) {
            error_log("MoMo payment exception: " . $e->getMessage());
            $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình thanh toán: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/booking/payment');
            exit;
        }
    }
    
    /**
     * Xử lý callback từ MoMo (IPN - Instant Payment Notification)
     */
    public function ipn() {
        // Nhận dữ liệu từ MoMo
        $inputData = file_get_contents("php://input");
        $resultData = json_decode($inputData, true);
        
        // Ghi log dữ liệu nhận được
        error_log("MoMo IPN data: " . print_r($resultData, true));
        
        // Kiểm tra dữ liệu
        if (!isset($resultData['resultCode'])) {
            echo json_encode(['message' => 'Invalid data']);
            exit;
        }
        
        // Xử lý kết quả
        if ($resultData['resultCode'] == 0) {
            // Thanh toán thành công, cập nhật trạng thái đơn hàng trong database
            // Thông thường sẽ xử lý ở đây, nhưng trong trường hợp này chúng ta sẽ xử lý ở hàm return
            // vì người dùng sẽ được chuyển hướng về trang return sau khi thanh toán
            
            echo json_encode(['message' => 'Success']);
        } else {
            // Thanh toán thất bại
            echo json_encode(['message' => 'Failed']);
        }
    }
    
    /**
     * Xử lý khi người dùng được chuyển hướng về từ MoMo
     */
    public function return() {
        try {
            // Nhận dữ liệu từ MoMo
            $resultCode = isset($_GET['resultCode']) ? $_GET['resultCode'] : null;
            $orderId = isset($_GET['orderId']) ? $_GET['orderId'] : null;
            $amount = isset($_GET['amount']) ? $_GET['amount'] : 0;
            $orderInfo = isset($_GET['orderInfo']) ? $_GET['orderInfo'] : '';
            
            // Ghi log dữ liệu nhận được
            error_log("MoMo return data: " . print_r($_GET, true));
            
            // Kiểm tra dữ liệu
            if ($resultCode === null || $orderId === null) {
                $_SESSION['error'] = 'Dữ liệu không hợp lệ';
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
            // Kiểm tra thông tin đơn hàng trong session
            if (!isset($_SESSION['momo_payment']) || $_SESSION['momo_payment']['order_id'] !== $orderId) {
                $_SESSION['error'] = 'Không tìm thấy thông tin đơn hàng';
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
            // Lấy thông tin đơn hàng từ session
            $paymentData = $_SESSION['momo_payment'];
            
            // Kiểm tra kết quả thanh toán
            if ($resultCode == '0') {
                // Thanh toán thành công
                
                // Kết nối database
                $this->db = Database::getInstance();
                
                // Bắt đầu transaction
                $this->db->beginTransaction();
                error_log("Transaction started for MoMo payment");
                
                $userId = $paymentData['user_id'];
                $eventId = $paymentData['event_id'];
                $selectedSeats = $paymentData['selected_seats'];
                $usedPoints = $paymentData['used_points'];
                $finalAmount = $paymentData['final_amount'];
                
                $createdTickets = [];
                $seatIdToTicketId = []; // Map để lưu trữ mã vé theo mã ghế
                
                // Lưu thông tin vé và cập nhật trạng thái ghế
                foreach ($selectedSeats as $seatId => $seatInfo) {
                    error_log("Processing seat ID: $seatId");
                    
                    // Tạo vé mới
                    $ticketData = [
                        'ma_su_kien' => $eventId,
                        'ma_khach_hang' => $userId,
                        'ma_cho_ngoi' => $seatId,
                        'ma_loai_ve' => $seatInfo['ticketType'],
                        'trang_thai' => 'DA_DAT'
                    ];
                    
                    error_log("Creating ticket with data: " . print_r($ticketData, true));
                    $ticketId = $this->bookingModel->createTicket($ticketData);
                    error_log("Ticket created with ID: $ticketId");
                    
                    $createdTickets[] = $ticketId;
                    $seatIdToTicketId[$seatId] = $ticketId; // Lưu mapping
                    
                    // Cập nhật trạng thái ghế
                    error_log("Updating seat status for seat ID: $seatId");
                    $this->bookingModel->updateSeatStatus($seatId, 'DA_DAT');
                }
                
                // Nếu có sử dụng điểm tích lũy
                if ($usedPoints > 0) {
                    error_log("Using $usedPoints loyalty points");
                    // Trừ điểm tích lũy đã sử dụng
                    $this->bookingModel->useLoyaltyPoints($userId, $usedPoints);
                }
                
                // Lưu thông tin giao dịch với số tiền đã giảm giá cho từng vé
                foreach ($selectedSeats as $seatId => $seatInfo) {
                    if (isset($seatIdToTicketId[$seatId])) {
                        $ticketId = $seatIdToTicketId[$seatId];
                        
                        // Tính giá vé sau khi giảm giá
                        $originalPrice = $seatInfo['price'];
                        $totalAmount = array_sum(array_column($selectedSeats, 'price'));
                        $discountRatio = ($totalAmount > 0) ? $finalAmount / $totalAmount : 1;
                        $discountedPrice = $originalPrice * $discountRatio;
                        
                        error_log("Seat ID: $seatId, Original price: $originalPrice, Discounted price: $discountedPrice");
                        
                        $transactionData = [
                            'ma_khach_hang' => $userId,
                            'ma_ve' => $ticketId,
                            'so_tien' => $discountedPrice, // Giá đã giảm
                            'phuong_thuc_thanh_toan' => 'MOMO',
                            'trang_thai' => 'THANH_CONG'
                        ];
                        
                        error_log("Creating transaction with data: " . print_r($transactionData, true));
                        $this->bookingModel->createTransaction($transactionData);
                    }
                }
                
                // Tính và lưu điểm tích lũy (dựa trên tổng tiền ban đầu, không phụ thuộc vào giảm giá)
                $totalAmount = array_sum(array_column($selectedSeats, 'price'));
                $loyaltyPoints = $totalAmount * 0.00003;
                error_log("Adding $loyaltyPoints loyalty points for user ID: $userId");
                $this->bookingModel->addLoyaltyPoints($userId, $loyaltyPoints);
                
                // Lưu lịch sử đặt vé
                foreach ($createdTickets as $ticketId) {
                    error_log("Adding ticket history for ticket ID: $ticketId");
                    $this->bookingModel->addTicketHistory($ticketId, $userId, 'DAT_VE', 'Đặt vé thành công qua MoMo');
                }
                
                // Commit transaction
                error_log("Committing transaction");
                $this->db->commit();
                
                // Lưu thông tin vé đã tạo để hiển thị trên trang cảm ơn
                $_SESSION['payment_success'] = true;
                $_SESSION['created_tickets'] = $createdTickets;
                $_SESSION['payment_amount'] = $finalAmount;
                $_SESSION['payment_method'] = 'MOMO';
                $_SESSION['event_id'] = $eventId;
                
                // Xóa thông tin đặt chỗ và thanh toán khỏi session
                unset($_SESSION['booking']);
                unset($_SESSION['momo_payment']);
                
                // Chuyển hướng đến trang cảm ơn
                header('Location: ' . BASE_URL . '/momo-payment/thanks');
                exit;
                
            } else {
                // Thanh toán thất bại
                $_SESSION['error'] = 'Thanh toán không thành công. Mã lỗi: ' . $resultCode;
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
        } catch (Exception $e) {
            // Rollback transaction nếu có lỗi
            if (isset($this->db) && $this->db->inTransaction()) {
                error_log("Rolling back transaction due to error: " . $e->getMessage());
                $this->db->rollBack();
            }
            
            error_log("MoMo return processing error: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            
            // Đặt thông báo lỗi
            $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình xử lý thanh toán: ' . $e->getMessage();
            
            // Chuyển hướng về trang thanh toán
            header('Location: ' . BASE_URL . '/booking/payment');
            exit;
        }
    }
    
    /**
     * Hiển thị trang cảm ơn sau khi thanh toán thành công
     */
    public function thanks() {
        // Kiểm tra xem có thông tin thanh toán thành công không
        if (!isset($_SESSION['payment_success']) || !$_SESSION['payment_success']) {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        // Lấy thông tin vé đã tạo
        $ticketIds = $_SESSION['created_tickets'] ?? [];
        $paymentAmount = $_SESSION['payment_amount'] ?? 0;
        $paymentMethod = $_SESSION['payment_method'] ?? '';
        $eventId = $_SESSION['event_id'] ?? null;
        
        // Lấy thông tin sự kiện
        $event = null;
        if ($eventId) {
            $event = $this->eventModel->getEventById($eventId);
        }
        
        // Lấy thông tin chi tiết vé - sử dụng cách tiếp cận tương tự như trong TicketController::myTickets()
        $tickets = [];
        
        // Sử dụng truy vấn JOIN tương tự như trong trang my-tickets
        foreach ($ticketIds as $ticketId) {
            $query = "SELECT v.ma_ve, v.ma_su_kien, v.trang_thai as trang_thai_ve, v.ma_khach_hang,
                        s.ten_su_kien, s.ngay_dien_ra, s.gio_dien_ra, s.dia_diem, s.hinh_anh,
                        lv.ten_loai_ve, lv.gia_ve as gia_goc, c.so_cho, c.khu_vuc,
                        g.so_tien as gia_ve
                     FROM ve v
                     JOIN sukien s ON v.ma_su_kien = s.ma_su_kien
                     JOIN loaive lv ON v.ma_loai_ve = lv.ma_loai_ve
                     JOIN chongoi c ON v.ma_cho_ngoi = c.ma_cho_ngoi
                     LEFT JOIN (
                         SELECT ma_ve, so_tien
                         FROM giaodich
                         WHERE trang_thai = 'THANH_CONG'
                         ORDER BY ngay_giao_dich DESC
                         LIMIT 1
                     ) g ON v.ma_ve = g.ma_ve
                     WHERE v.ma_ve = ?";
            
            try {
                $stmt = $this->db->prepare($query);
                $stmt->execute([$ticketId]);
                $ticketData = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($ticketData) {
                    error_log("Ticket data for ID $ticketId: " . print_r($ticketData, true));
                    
                    // Định dạng dữ liệu để phù hợp với cách hiển thị trong template
                    $tickets[] = [
                        'id' => $ticketId,
                        'ticket_type' => [
                            'ten_loai_ve' => $ticketData['ten_loai_ve'],
                            'gia_ve' => $ticketData['gia_ve'] ?? $ticketData['gia_goc']
                        ],
                        'seat' => [
                            'so_cho' => $ticketData['so_cho'],
                            'khu_vuc' => $ticketData['khu_vuc']
                        ]
                    ];
                }
            } catch (Exception $e) {
                error_log("Error fetching ticket data: " . $e->getMessage());
                
                // Nếu truy vấn JOIN thất bại, thử cách khác
                $ticket = $this->bookingModel->getTicketById($ticketId);
                
                if ($ticket) {
                    // Lấy thông tin ghế
                    $seatId = $ticket['ma_cho_ngoi'];
                    $seat = $this->bookingModel->getSeatById($seatId);
                    
                    // Lấy thông tin loại vé
                    $ticketTypeId = $ticket['ma_loai_ve'];
                    $ticketType = $this->bookingModel->getTicketTypeById($ticketTypeId);
                    
                    // Lấy giá vé từ giao dịch
                    $query = "SELECT so_tien FROM giaodich WHERE ma_ve = ? AND trang_thai = 'THANH_CONG' ORDER BY ngay_giao_dich DESC LIMIT 1";
                    $stmt = $this->db->prepare($query);
                    $stmt->execute([$ticketId]);
                    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $tickets[] = [
                        'id' => $ticketId,
                        'ticket_type' => $ticketType,
                        'seat' => $seat,
                        'price' => $transaction ? $transaction['so_tien'] : ($ticketType ? $ticketType['gia_ve'] : 0)
                    ];
                }
            }
        }
        
        // Hiển thị trang cảm ơn
        require_once 'views/booking/thanks.php';
        
        // Xóa thông tin thanh toán khỏi session sau khi hiển thị
        unset($_SESSION['payment_success']);
        unset($_SESSION['created_tickets']);
        unset($_SESSION['payment_amount']);
        unset($_SESSION['payment_method']);
        unset($_SESSION['event_id']);
    }
    
    /**
     * Hàm gửi request POST đến MoMo
     */
    private function execPostRequest($url, $data) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        
        // Execute post
        $result = curl_exec($ch);
        
        // Check for errors
        if (curl_errno($ch)) {
            error_log("cURL Error: " . curl_error($ch));
        }
        
        // Close connection
        curl_close($ch);
        return $result;
    }
}
