<?php
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://localhost/venow');

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Xử lý preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Chỉ chấp nhận phương thức POST'
    ]);
    exit;
}

// Lấy input
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['message']) || trim($data['message']) === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Vui lòng nhập câu hỏi'
    ]);
    exit;
}

$userMessage = trim($data['message']);

// Prompt hệ thống
$systemPrompt = <<<EOD
Bạn là trợ lý AI của Venow - nền tảng đặt vé sự kiện. 
Trả lời bằng tiếng Việt, giọng văn thân thiện, ngắn gọn khi cần, nhưng đầy đủ và chuyên nghiệp nếu được hỏi chi tiết. 
Cố gắng đưa ra thông tin hữu ích, ví dụ cụ thể nếu có thể. 
Thông tin: Venow là nền tảng đặt vé sự kiện trực tuyến, nơi người dùng có thể tìm kiếm, đặt vé, quản lý tài khoản và tổ chức sự kiện.
EOD;

// Kết hợp prompt
$fullPrompt = $systemPrompt . "\n\nCâu hỏi: " . $userMessage . "\nTrả lời:";

// Gửi yêu cầu đến Ollama API
$requestData = [
    'model' => 'tinyllama',    
    'prompt' => $fullPrompt,
    'stream' => true,
    'temperature' => 0.8,
    'top_p' => 0.9,
    'max_tokens' => 100
];

try {
    $ch = curl_init('http://localhost:11434/api/generate');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0); 

    // Bắt stream từng dòng
    $response = '';
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($curl, $data) use (&$response) {
        $response .= $data;
        return strlen($data);
    });

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception('Lỗi HTTP từ Ollama: ' . $httpCode);
    }

    // Tách từng dòng JSON
    $lines = explode("\n", trim($response));
    $final = '';

    foreach ($lines as $line) {
        if (trim($line) === '') continue;
        $chunk = json_decode($line, true);
        if (isset($chunk['response'])) {
            $final .= $chunk['response'];
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => trim($final)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Lỗi: ' . $e->getMessage()
    ]);
}
