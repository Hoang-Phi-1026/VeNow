<?php
/**
 * Lớp IdHasher cung cấp các phương thức để mã hóa và giải mã ID
 */
class IdHasher {
    // Khóa bí mật để mã hóa, nên thay đổi trong môi trường sản xuất
    private static $secretKey = 'venow_secret_key_2023';
    
    // Tiền tố cho ID đã mã hóa để dễ nhận biết
    private static $prefix = 'v';
    
    /**
     * Mã hóa ID thành chuỗi an toàn để sử dụng trong URL
     * 
     * @param int $id ID cần mã hóa
     * @return string Chuỗi đã mã hóa
     */
    public static function encode($id) {
        if (!$id) return '';
        
        // Chuyển ID thành chuỗi và kết hợp với khóa bí mật
        $data = $id . '|' . time() . '|' . self::$secretKey;
        
        // Mã hóa bằng base64 và thay thế các ký tự đặc biệt
        $encoded = base64_encode($data);
        $encoded = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);
        
        // Thêm tiền tố
        return self::$prefix . $encoded;
    }
    
    /**
     * Giải mã chuỗi đã mã hóa để lấy ID gốc
     * 
     * @param string $encoded Chuỗi đã mã hóa
     * @return int|null ID gốc hoặc null nếu giải mã thất bại
     */
    public static function decode($encoded) {
        if (empty($encoded)) return null;
        
        // Kiểm tra tiền tố
        if (substr($encoded, 0, strlen(self::$prefix)) !== self::$prefix) {
            return null;
        }
        
        // Loại bỏ tiền tố
        $encoded = substr($encoded, strlen(self::$prefix));
        
        // Khôi phục các ký tự đặc biệt
        $encoded = str_replace(['-', '_'], ['+', '/'], $encoded);
        $encoded = $encoded . str_repeat('=', (4 - strlen($encoded) % 4) % 4);
        
        // Giải mã base64
        $decoded = base64_decode($encoded);
        if ($decoded === false) {
            return null;
        }
        
        // Tách ID từ chuỗi đã giải mã
        $parts = explode('|', $decoded);
        if (count($parts) < 3 || $parts[2] !== self::$secretKey) {
            return null;
        }
        
        return (int)$parts[0];
    }
}
