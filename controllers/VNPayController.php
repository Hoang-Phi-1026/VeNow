<?php
require_once 'controllers/BaseController.php';
require_once 'models/Booking.php';
require_once 'models/Event.php';
require_once 'models/Ticket.php';
require_once 'controllers/PaymentService.php';

class VNPayController extends BaseController {
    private $bookingModel;
    private $eventModel;
    private $ticketModel;
    private $paymentService;

    // VNPAY API configuration
    private $vnpUrl = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    private $vnpTmnCode = "YSQ735C6";
    private $vnpHashSecret = "GKAIVLFI6DUISHUPDS1A7US8WROWSWYD";
    private $vnpReturnUrl;
    private $ipnUrl;

    public function __construct() {
        parent::__construct();
        $this->bookingModel = new Booking();
        $this->eventModel = new Event();
        $this->ticketModel = new Ticket();
        $this->paymentService = new PaymentService();
        
        // Set URLs based on BASE_URL
        $this->vnpReturnUrl = BASE_URL . "/vnpay/return";
        $this->ipnUrl = BASE_URL . "/vnpay/ipn";
    }

    /**
     * Xử lý yêu cầu thanh toán qua VNPAY
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

            // Nếu không phải thanh toán qua VNPAY, chuyển hướng về trang thanh toán
            if ($paymentMethod !== 'VNPAY') {
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
            $_SESSION['vnpay_payment'] = [
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
            
            // Chuẩn bị dữ liệu cho VNPAY
            $vnp_TxnRef = $orderId; // Mã đơn hàng
            $vnp_OrderInfo = $orderInfo;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $finalAmount * 100; // VNPAY yêu cầu số tiền * 100
            $vnp_Locale = 'vn';
            $vnp_BankCode = ''; // Để trống để hiển thị tất cả ngân hàng
            $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
            
            $inputData = array(
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $this->vnpTmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $this->vnpReturnUrl,
                "vnp_TxnRef" => $vnp_TxnRef
            );
            
            if (!empty($vnp_BankCode)) {
                $inputData['vnp_BankCode'] = $vnp_BankCode;
            }
            
            // Sắp xếp dữ liệu theo thứ tự key
            ksort($inputData);
            $query = "";
            $i = 0;
            $hashdata = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashdata .= urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
                $query .= urlencode($key) . "=" . urlencode($value) . '&';
            }
            
            // Tạo URL thanh toán VNPAY
            $vnpUrl = $this->vnpUrl . "?" . $query;
            
            // Tạo chữ ký
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $this->vnpHashSecret);
            $vnpUrl .= 'vnp_SecureHash=' . $vnpSecureHash;
            
            // Chuyển hướng đến trang thanh toán của VNPAY
            header('Location: ' . $vnpUrl);
            exit;
            
        } catch (Exception $e) {
            error_log("VNPAY payment exception: " . $e->getMessage());
            $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình thanh toán: ' . $e->getMessage();
            header('Location: ' . BASE_URL . '/booking/payment');
            exit;
        }
    }
    
    /**
     * Xử lý callback từ VNPAY (IPN - Instant Payment Notification)
     */
    public function ipn() {
        // Nhận dữ liệu từ VNPAY
        $inputData = array();
        $returnData = array();
        
        foreach ($_GET as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        // Ghi log dữ liệu nhận được
        error_log("VNPAY IPN data: " . print_r($inputData, true));
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'];
        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }
        
        $secureHash = hash_hmac('sha512', $hashData, $this->vnpHashSecret);
        
        // Kiểm tra chữ ký
        if ($secureHash == $vnp_SecureHash) {
            // Kiểm tra trạng thái giao dịch
            if ($inputData['vnp_ResponseCode'] == '00') {
                // Thanh toán thành công, cập nhật trạng thái đơn hàng trong database
                // Thông thường sẽ xử lý ở đây, nhưng trong trường hợp này chúng ta sẽ xử lý ở hàm return
                $returnData['RspCode'] = '00';
                $returnData['Message'] = 'Confirm Success';
            } else {
                $returnData['RspCode'] = '99';
                $returnData['Message'] = 'Confirm Fail';
            }
        } else {
            $returnData['RspCode'] = '97';
            $returnData['Message'] = 'Invalid Signature';
        }
        
        // Trả về kết quả cho VNPAY
        echo json_encode($returnData);
    }
    
    /**
     * Xử lý khi người dùng được chuyển hướng về từ VNPAY
     */
    public function return() {
        try {
            // Nhận dữ liệu từ VNPAY
            $inputData = array();
            foreach ($_GET as $key => $value) {
                if (substr($key, 0, 4) == "vnp_") {
                    $inputData[$key] = $value;
                }
            }
            
            // Ghi log dữ liệu nhận được
            error_log("VNPAY return data: " . print_r($inputData, true));
            
            // Kiểm tra chữ ký
            $vnp_SecureHash = $inputData['vnp_SecureHash'];
            unset($inputData['vnp_SecureHash']);
            ksort($inputData);
            $i = 0;
            $hashData = "";
            foreach ($inputData as $key => $value) {
                if ($i == 1) {
                    $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
                } else {
                    $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                    $i = 1;
                }
            }
            
            $secureHash = hash_hmac('sha512', $hashData, $this->vnpHashSecret);
            
            if ($secureHash != $vnp_SecureHash) {
                $_SESSION['error'] = 'Chữ ký không hợp lệ';
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
            // Lấy thông tin giao dịch
            $vnp_ResponseCode = $inputData['vnp_ResponseCode'];
            $vnp_TxnRef = $inputData['vnp_TxnRef'];
            
            // Kiểm tra thông tin đơn hàng trong session
            if (!isset($_SESSION['vnpay_payment']) || $_SESSION['vnpay_payment']['order_id'] !== $vnp_TxnRef) {
                $_SESSION['error'] = 'Không tìm thấy thông tin đơn hàng';
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
            // Lấy thông tin đơn hàng từ session
            $paymentData = $_SESSION['vnpay_payment'];
            
            // Kiểm tra kết quả thanh toán
            if ($vnp_ResponseCode == '00') {
                // Thanh toán thành công
                $userId = $paymentData['user_id'];
                $eventId = $paymentData['event_id'];
                $selectedSeats = $paymentData['selected_seats'];
                $usedPoints = $paymentData['used_points'];
                $finalAmount = $paymentData['final_amount'];
                
                // Xử lý thanh toán
                $result = $this->paymentService->processTicketCreation(
                    $userId, 
                    $eventId, 
                    $selectedSeats, 
                    $usedPoints, 
                    $finalAmount, 
                    'VNPAY'
                );
                
                if ($result['success']) {
                    // Lưu thông tin vé đã tạo để hiển thị trên trang cảm ơn
                    $_SESSION['payment_success'] = true;
                    $_SESSION['created_tickets'] = $result['created_tickets'];
                    $_SESSION['payment_amount'] = $result['payment_amount'];
                    $_SESSION['payment_method'] = 'VNPAY';
                    $_SESSION['event_id'] = $eventId;
                    
                    // Xóa thông tin đặt chỗ và thanh toán khỏi session
                    unset($_SESSION['booking']);
                    unset($_SESSION['vnpay_payment']);
                    
                    // Chuyển hướng đến trang cảm ơn MoMo (sử dụng lại)
                    header('Location: ' . BASE_URL . '/momo-payment/thanks');
                    exit;
                } else {
                    // Xử lý lỗi
                    $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình xử lý thanh toán: ' . $result['error'];
                    header('Location: ' . BASE_URL . '/booking/payment');
                    exit;
                }
            } else {
                // Thanh toán thất bại
                $_SESSION['error'] = 'Thanh toán không thành công. Mã lỗi: ' . $vnp_ResponseCode;
                header('Location: ' . BASE_URL . '/booking/payment');
                exit;
            }
            
        } catch (Exception $e) {
            error_log("VNPAY return processing error: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            
            // Đặt thông báo lỗi
            $_SESSION['error'] = 'Đã xảy ra lỗi trong quá trình xử lý thanh toán: ' . $e->getMessage();
            
            // Chuyển hướng về trang thanh toán
            header('Location: ' . BASE_URL . '/booking/payment');
            exit;
        }
    }
}
