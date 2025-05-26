<?php
// Improved database configuration with better error handling
error_reporting(E_ALL);
ini_set('log_errors', 1);

class Database {
    private static $instance = null;
    private $connection;
    
    // Database configuration
    private $host = 'sql309.infinityfree.com';
    private $username = 'if0_39074279';
    private $password = 'Phi010103';
    private $database = 'if0_39074279_venow_db'; // Updated database name
    private $charset = 'utf8mb4';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            // Test connection
            $this->connection->query("SELECT 1");
            
        } catch (PDOException $e) {
            $error_message = "Database connection failed: " . $e->getMessage();
            error_log($error_message);
            
            // Don't expose sensitive info in production
            if (defined('DEBUG') && DEBUG) {
                throw new Exception($error_message);
            } else {
                throw new Exception("Không thể kết nối cơ sở dữ liệu");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }
    
    public static function getConnection() {
        return self::getInstance();
    }
    
    // Test connection method
    public static function testConnection() {
        try {
            $db = self::getConnection();
            $stmt = $db->query("SELECT 1 as test");
            return $stmt->fetch() !== false;
        } catch (Exception $e) {
            error_log("Database test failed: " . $e->getMessage());
            return false;
        }
    }
}

// Define debug mode
if (!defined('DEBUG')) {
    define('DEBUG', true); // Set to false in production
}
?>
