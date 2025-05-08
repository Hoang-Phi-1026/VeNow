<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TicketBox - Mua vé sự kiện trực tuyến</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/public/images/favicon.ico">
    
    <?php
    // Add specific CSS files based on the current page
    $current_url = $_SERVER['REQUEST_URI'];
    if (strpos($current_url, '/login') !== false) {
        echo '<link rel="stylesheet" href="' . BASE_URL . '/public/css/login.css">';
    } elseif (strpos($current_url, '/register') !== false) {
        echo '<link rel="stylesheet" href="' . BASE_URL . '/public/css/register.css">';
    }
    ?>
    
    <script src="<?php echo BASE_URL; ?>/public/js/theme.js"></script>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo BASE_URL; ?>" class="logo">
                    <img src="<?php echo BASE_URL; ?>/public/images/logo.png" alt="TicketBox Logo" class="logo-image">
                </a>
                
                <div class="search-container">
                    <form action="<?php echo BASE_URL; ?>/search" method="GET" class="search-form">
                        <div class="search-input-wrapper">
                            <input type="text" name="q" class="search-input" placeholder="Tìm kiếm sự kiện...">
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="user-actions">
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="user-info">
                            <span>Xin chào, <?php echo htmlspecialchars($_SESSION['user']['ten_nguoi_dung']); ?></span>
                            <a href="<?php echo BASE_URL; ?>/logout" class="btn-logout">Đăng xuất</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/login" class="btn-login">Đăng nhập</a>
                    <?php endif; ?>
                    <button class="theme-toggle" title="Chuyển đổi chế độ tối/sáng">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <nav class="main-nav">
        <div class="container">
            <button class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-list">
                <?php if (!isset($_SESSION['user'])): ?>
                    <!-- Không hiển thị menu khi chưa đăng nhập -->
                <?php else: ?>
                    <?php
                    $vai_tro = $_SESSION['user']['vai_tro'];
                    switch ($vai_tro) {
                        case 1: // Admin
                            ?>
                            <li class="nav-item dropdown">
                                <a href="#">Quản lý sự kiện</a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo BASE_URL; ?>/events/create">Tạo sự kiện</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/events/manage">Xóa sự kiện</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/events/manage">Sự kiện chờ duyệt</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#">Quản lý tài khoản</a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo BASE_URL; ?>/users">Quản lý người dùng</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/roles">Phân quyền</a></li>
                                </ul>
                            </li>
                            <li class="nav-item dropdown">
                                <a href="#">Thống kê & báo cáo</a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo BASE_URL; ?>/reports/revenue">Doanh thu</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/reports/attendance">Lượt tham gia</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/reports/featured">Sự kiện nổi bật</a></li>
                                </ul>
                            </li>
                            <?php
                            break;
                        case 2: // Organizer
                            ?>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/events/create">Tạo sự kiện</a></li>
                            <li class="nav-item dropdown">
                                <a href="#">Thống kê doanh thu</a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo BASE_URL; ?>/reports/organizer/revenue">Báo cáo doanh thu</a></li>
                                    <li><a href="<?php echo BASE_URL; ?>/reports/organizer/events">Sự kiện của tôi</a></li>
                                </ul>
                            </li>
                            <?php
                            break;
                        case 3: // Staff
                            ?>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/events/requests">Yêu cầu sự kiện</a></li>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/reviews">Đánh giá chờ duyệt</a></li>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/complaints">Xử lý khiếu nại</a></li>
                            <?php
                            break;
                        case 4: // Customer
                            ?>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/events">Sự kiện</a></li>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/tickets/history">Lịch sử đặt vé</a></li>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/points">Điểm tích lũy</a></li>
                            <li class="nav-item"><a href="<?php echo BASE_URL; ?>/account">Tài khoản</a></li>
                            <?php
                            break;
                    }
                    ?>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main>
        <script src="<?php echo BASE_URL; ?>/public/js/main.js"></script>

    <?php
    // Hiển thị thông báo từ session
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
        unset($_SESSION['success']);
    }
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-error">' . $_SESSION['error'] . '</div>';
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['warning'])) {
        echo '<div class="alert alert-warning">' . $_SESSION['warning'] . '</div>';
        unset($_SESSION['warning']);
    }
    if (isset($_SESSION['info'])) {
        echo '<div class="alert alert-info">' . $_SESSION['info'] . '</div>';
        unset($_SESSION['info']);
    }
    ?>
