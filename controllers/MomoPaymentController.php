<?php
require_once 'controllers/BaseController.php';
require_once 'models/Booking.php';
require_once 'models/Event.php';
require_once 'models/Ticket.php';
require_once 'controllers/PaymentService.php';

class MomoPaymentController extends BaseController {
    private $bookingModel;
    private $eventModel;
    private $ticketModel;
    private $paymentService;

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
        $this->paymentService = new PaymentService();
        
        // Set URLs - QUAN TRỌNG: Phải là HTTPS và accessible từ internet
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

            // Nếu không phải thanh toán qua MoMo, chuyển hướng về trang thanh toán
            if ($paymentMethod !== 'MOMO') {
                $_SESSION['error'] = 'Phương thức thanh toán không hợp lệ';
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
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
            
            // Tạo thông tin đơn hàng - CHỈ SỬ DỤNG ASCII VÀ SỐ
            $eventName = $event['ten_su_kien'];
            
            // Chuyển đổi tiếng Việt sang không dấu và loại bỏ ký tự đặc biệt
            $cleanEventName = $this->removeVietnameseAccents($eventName);
            $cleanEventName = preg_replace('/[^a-zA-Z0-9\s]/', '', $cleanEventName);
            $cleanEventName = trim(preg_replace('/\s+/', ' ', $cleanEventName));
            
            $orderInfo = "Thanh toan su kien " . $cleanEventName;
            
            // Giới hạn độ dài orderInfo (MoMo yêu cầu tối đa 100 ký tự)
            if (strlen($orderInfo) > 100) {
                $maxEventNameLength = 100 - strlen("Thanh toan su kien ");
                $cleanEventName = substr($cleanEventName, 0, $maxEventNameLength - 3) . "...";
                $orderInfo = "Thanh toan su kien " . $cleanEventName;
            }

            // DEBUG: Log chi tiết
            error_log("=== MOMO DEBUG: Order Info Processing ===");
            error_log("Original event name: " . $eventName);
            error_log("Clean event name: " . $cleanEventName);
            error_log("Final order info: " . $orderInfo);
            error_log("Order info length: " . strlen($orderInfo));
            error_log("Order info bytes: " . strlen(utf8_encode($orderInfo)));
            
            // ExtraData để trống (theo yêu cầu MoMo)
            $extraData = "";
            
            // Tạo requestId - phải là string
            $requestId = (string)time();
            $requestType = "captureWallet";
            
            // Chuyển đổi amount thành số nguyên, đảm bảo >= 1000 VND
            $finalAmount = max(1000, (int)$finalAmount);
            
            // DEBUG: Log các tham số trước khi tạo signature
            error_log("=== MOMO DEBUG: Parameters ===");
            error_log("accessKey: " . $this->accessKey);
            error_log("amount: " . $finalAmount . " (type: " . gettype($finalAmount) . ")");
            error_log("extraData: '" . $extraData . "'");
            error_log("ipnUrl: " . $this->ipnUrl);
            error_log("orderId: " . $orderId);
            error_log("orderInfo: " . $orderInfo);
            error_log("partnerCode: " . $this->partnerCode);
            error_log("redirectUrl: " . $this->redirectUrl);
            error_log("requestId: " . $requestId);
            error_log("requestType: " . $requestType);
            
            // Tạo chữ ký theo đúng thứ tự alphabet (QUAN TRỌNG!)
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
            
            // DEBUG: Log signature
            error_log("=== MOMO DEBUG: Signature ===");
            error_log("Raw hash string: " . $rawHash);
            error_log("Secret key: " . $this->secretKey);
            error_log("Generated signature: " . $signature);
            
            // Tạo dữ liệu gửi đến MoMo - ĐÚNG THEO SPEC
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
            
            // Validate dữ liệu trước khi gửi
            $this->validateMomoRequest($data);
            
            error_log("=== MOMO DEBUG: Final Request ===");
            error_log("Request data: " . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            // Gửi yêu cầu đến MoMo
            $result = $this->execPostRequest($this->endpoint, json_encode($data));
            
            if (is_array($result) && isset($result['error'])) {
                error_log("cURL Error: " . $result['error']);
                $_SESSION['error'] = 'Lỗi kết nối: ' . $result['error'];
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
            $jsonResult = json_decode($result, true);
            error_log("=== MOMO DEBUG: Response ===");
            error_log("Raw response: " . $result);
            error_log("Parsed response: " . print_r($jsonResult, true));

            if (isset($jsonResult['payUrl']) && !empty($jsonResult['payUrl'])) {
                error_log("MoMo payment URL generated successfully");
                // Chuyển hướng đến trang thanh toán của MoMo
                header('Location: ' . $jsonResult['payUrl']);
                exit;
            } else {
                $errorMessage = 'Không thể tạo liên kết thanh toán MoMo.';
                if (isset($jsonResult['message'])) {
                    $errorMessage .= ' Lỗi: ' . $jsonResult['message'];
                }
                if (isset($jsonResult['resultCode'])) {
                    $errorMessage .= ' Mã lỗi: ' . $jsonResult['resultCode'];
                }
                if (isset($jsonResult['localMessage'])) {
                    $errorMessage .= ' Chi tiết: ' . $jsonResult['localMessage'];
                }
                
                error_log("MoMo API Error: " . print_r($jsonResult, true));
                $_SESSION['error'] = $errorMessage;
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
        } catch (Exception $e) {
            error_log("MoMo payment exception: " . $e->getMessage());
            error_log("Exception trace: " . $e->getTraceAsString());
            $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình thanh toán: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/booking/payment');
            exit;
        }
    }
    
    /**
     * Validate dữ liệu request trước khi gửi đến MoMo
     */
    private function validateMomoRequest($data) {
        $required = ['partnerCode', 'requestId', 'amount', 'orderId', 'orderInfo', 'redirectUrl', 'ipnUrl', 'requestType', 'signature'];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        // Validate amount
        if (!is_numeric($data['amount']) || $data['amount'] < 1000) {
            throw new Exception("Invalid amount: " . $data['amount']);
        }
        
        // Validate URLs
        if (!filter_var($data['redirectUrl'], FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid redirectUrl: " . $data['redirectUrl']);
        }
        
        if (!filter_var($data['ipnUrl'], FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid ipnUrl: " . $data['ipnUrl']);
        }
        
        // Validate orderInfo length
        if (strlen($data['orderInfo']) > 100) {
            throw new Exception("OrderInfo too long: " . strlen($data['orderInfo']) . " characters");
        }
        
        error_log("MoMo request validation passed");
    }
    
    /**
     * Loại bỏ dấu tiếng Việt
     */
    private function removeVietnameseAccents($str) {
        $vietnamese = array(
            'à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ',
            'è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ',
            'ì','í','ị','ỉ','ĩ',
            'ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ',
            'ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ',
            'ỳ','ý','ỵ','ỷ','ỹ',
            'đ',
            'À','Á','Ạ','Ả','Ã','Â','Ầ','Ấ','Ậ','Ẩ','Ẫ','Ă','Ằ','Ắ','Ặ','Ẳ','Ẵ',
            'È','É','Ẹ','Ẻ','Ẽ','Ê','Ề','Ế','Ệ','Ể','Ễ',
            'Ì','Í','Ị','Ỉ','Ĩ',
            'Ò','Ó','Ọ','Ỏ','Õ','Ô','Ồ','Ố','Ộ','Ổ','Ỗ','Ơ','Ờ','Ớ','Ợ','Ở','Ỡ',
            'Ù','Ú','Ụ','Ủ','Ũ','Ư','Ừ','Ứ','Ự','Ử','Ữ',
            'Ỳ','Ý','Ỵ','Ỷ','Ỹ',
            'Đ'
        );
        
        $english = array(
            'a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a',
            'e','e','e','e','e','e','e','e','e','e','e',
            'i','i','i','i','i',
            'o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o',
            'u','u','u','u','u','u','u','u','u','u','u',
            'y','y','y','y','y',
            'd',
            'A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A','A',
            'E','E','E','E','E','E','E','E','E','E','E',
            'I','I','I','I','I',
            'O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O','O',
            'U','U','U','U','U','U','U','U','U','U','U',
            'Y','Y','Y','Y','Y',
            'D'
        );
        
        return str_replace($vietnamese, $english, $str);
    }
    
    /**
     * Xử lý callback từ MoMo (IPN - Instant Payment Notification)
     */
    public function ipn() {
        error_log("=== MOMO IPN RECEIVED ===");
        error_log("Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Headers: " . print_r(getallheaders(), true));
        
        $inputData = file_get_contents("php://input");
        error_log("Raw input: " . $inputData);
        
        $resultData = json_decode($inputData, true);
        error_log("Parsed data: " . print_r($resultData, true));
        
        if (!isset($resultData['resultCode'])) {
            error_log("IPN: Missing resultCode");
            http_response_code(400);
            echo json_encode(['message' => 'Invalid data']);
            exit;
        }
        
        $orderId = $resultData['orderId'] ?? '';
        $resultCode = $resultData['resultCode'] ?? '';
        $amount = $resultData['amount'] ?? 0;
        
        error_log("IPN Processing - OrderID: $orderId, ResultCode: $resultCode, Amount: $amount");
        
        if ($resultCode == 0) {
            error_log("IPN: Payment successful for order $orderId");
            http_response_code(200);
            echo json_encode(['message' => 'Success']);
        } else {
            error_log("IPN: Payment failed for order $orderId with code $resultCode");
            http_response_code(200);
            echo json_encode(['message' => 'Failed']);
        }
        exit;
    }
    
    /**
     * Xử lý khi người dùng được chuyển hướng về từ MoMo
     */
    public function return() {
        try {
            error_log("=== MOMO RETURN RECEIVED ===");
            error_log("GET params: " . print_r($_GET, true));
            
            $resultCode = isset($_GET['resultCode']) ? $_GET['resultCode'] : null;
            $orderId = isset($_GET['orderId']) ? $_GET['orderId'] : null;
            $amount = isset($_GET['amount']) ? $_GET['amount'] : 0;
            $orderInfo = isset($_GET['orderInfo']) ? $_GET['orderInfo'] : '';
            $message = isset($_GET['message']) ? $_GET['message'] : '';
            
            if ($resultCode === null || $orderId === null) {
                error_log("Return: Missing required parameters");
                $_SESSION['error'] = 'Dữ liệu không hợp lệ từ MoMo';
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
            if (!isset($_SESSION['momo_payment'])) {
                error_log("Return: No payment session found");
                $_SESSION['error'] = 'Không tìm thấy thông tin đơn hàng trong session';
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
            $paymentData = $_SESSION['momo_payment'];
            
            if ($paymentData['order_id'] !== $orderId) {
                error_log("Return: OrderID mismatch. Session: " . $paymentData['order_id'] . ", MoMo: " . $orderId);
                $_SESSION['error'] = 'Mã đơn hàng không khớp';
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
            if ($resultCode == '0') {
                error_log("Return: Payment successful for order $orderId");
                
                $userId = $paymentData['user_id'];
                $eventId = $paymentData['event_id'];
                $selectedSeats = $paymentData['selected_seats'];
                $usedPoints = $paymentData['used_points'];
                $finalAmount = $paymentData['final_amount'];
                
                $result = $this->paymentService->processTicketCreation(
                    $userId, 
                    $eventId, 
                    $selectedSeats, 
                    $usedPoints, 
                    $finalAmount, 
                    'MOMO'
                );
                
                if ($result['success']) {
                    $_SESSION['payment_success'] = true;
                    $_SESSION['created_tickets'] = $result['created_tickets'];
                    $_SESSION['payment_amount'] = $result['payment_amount'];
                    $_SESSION['payment_method'] = 'MOMO';
                    $_SESSION['event_id'] = $eventId;
                    
                    unset($_SESSION['booking']);
                    unset($_SESSION['momo_payment']);
                    
                    error_log("Return: Tickets created successfully, redirecting to thanks page");
                    header('Location: ' . BASE_URL . '/momo-payment/thanks');
                    exit;
                } else {
                    error_log("Return: Ticket creation failed - " . $result['error']);
                    $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình xử lý thanh toán: ' . $result['error'];
                    header('Location: ' . BASE_URL . '/booking/payment');
                    exit;
                }
            } else {
                error_log("Return: Payment failed for order $orderId with code $resultCode. Message: $message");
                $_SESSION['error'] = 'Thanh toán không thành công. ' . ($message ? $message : 'Mã lỗi: ' . $resultCode);
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
        } catch (Exception $e) {
            error_log("MoMo return processing error: " . $e->getMessage());
            $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình xử lý thanh toán: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/booking/payment');
            exit;
        }
    }
    
    /**
     * Hiển thị trang cảm ơn sau khi thanh toán thành công
     */
    public function thanks() {
        if (!isset($_SESSION['payment_success']) || !$_SESSION['payment_success']) {
            header('Location: ' . BASE_URL);
            exit;
        }
        
        $ticketIds = $_SESSION['created_tickets'] ?? [];
        $paymentAmount = $_SESSION['payment_amount'] ?? 0;
        $paymentMethod = $_SESSION['payment_method'] ?? '';
        $eventId = $_SESSION['event_id'] ?? null;
        
        $event = null;
        if ($eventId) {
            $event = $this->eventModel->getEventById($eventId);
        }
        
        $tickets = [];
        
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
            }
        }
        
        require_once 'views/booking/thanks.php';
        
        unset($_SESSION['payment_success']);
        unset($_SESSION['created_tickets']);
        unset($_SESSION['payment_amount']);
        unset($_SESSION['payment_method']);
        unset($_SESSION['event_id']);
    }
    
    /**
     * Hàm gửi request POST đến MoMo với improved error handling
     */
    private function execPostRequest($url, $data) {
        error_log("=== MOMO DEBUG: cURL Request ===");
        error_log("URL: " . $url);
        error_log("Data length: " . strlen($data) . " bytes");
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        
        $result = curl_exec($ch);
        
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlInfo = curl_getinfo($ch);
        
        error_log("=== MOMO DEBUG: cURL Response ===");
        error_log("HTTP Code: " . $httpCode);
        error_log("Total time: " . $curlInfo['total_time']);
        error_log("Response size: " . strlen($result) . " bytes");
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            $errorCode = curl_errno($ch);
            error_log("cURL Error Code: " . $errorCode);
            error_log("cURL Error: " . $error);
            curl_close($ch);
            return ['error' => 'Connection failed: ' . $error . ' (Code: ' . $errorCode . ')'];
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("MoMo API returned HTTP code: " . $httpCode);
            error_log("Response body: " . $result);
            return ['error' => 'HTTP Error: ' . $httpCode . ' - Response: ' . $result];
        }
        
        return $result;
    }
}
