<?php
require_once __DIR__ . '/../../controllers/EventController.php';

// Khởi tạo EventController
$eventController = new EventController();

// Lấy danh sách sự kiện nổi bật và sắp tới
$featuredEvents = $eventController->getFeaturedEvents();
$upcomingEvents = $eventController->getUpcomingEvents();
$categories = $eventController->getAllEventTypes();

// Check if constants are defined, if not define them
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__DIR__)));
}

if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $domainName = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . $domainName);
}

// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="hero-section">
    <div class="container">
        <div class="hero-slider">
            <?php foreach ($featuredEvents as $index => $event): ?>
            <div class="slider-item <?php echo $index === 0 ? 'active' : ''; ?>">
                <img src="https://via.placeholder.com/1200x500/1eb75c/FFFFFF?text=<?php echo urlencode($event['ten_su_kien']); ?>" alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>" class="slider-image">
                <div class="slider-content">
                    <h2 class="slider-title"><?php echo htmlspecialchars($event['ten_su_kien']); ?></h2>
                    <p class="slider-description"><?php echo htmlspecialchars(substr($event['mo_ta'], 0, 200) . (strlen($event['mo_ta']) > 200 ? '...' : '')); ?></p>
                    <a href="<?php echo BASE_URL; ?>/event/<?php echo $event['ma_su_kien']; ?>" class="btn btn-primary">Mua vé ngay <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="slider-controls">
                <button class="slider-prev" aria-label="Previous slide"><i class="fas fa-chevron-left"></i></button>
                <div class="slider-dots">
                    <?php for ($i = 0; $i < count($featuredEvents); $i++): ?>
                    <span class="dot <?php echo $i === 0 ? 'active' : ''; ?>" aria-label="Go to slide <?php echo $i+1; ?>"></span>
                    <?php endfor; ?>
                </div>
                <button class="slider-next" aria-label="Next slide"><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>

<section class="featured-events">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Sự kiện nổi bật</h2>
            <a href="<?php echo BASE_URL; ?>/search?featured=1" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="event-grid">
            <?php foreach ($featuredEvents as $event): ?>
            <div class="event-card">
                <div class="event-image">
                    <img src="https://via.placeholder.com/400x250/1eb75c/FFFFFF?text=<?php echo urlencode($event['ten_su_kien']); ?>" alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>">
                    <div class="event-date">
                        <span class="day"><?php echo date('d', strtotime($event['ngay_dien_ra'])); ?></span>
                        <span class="month"><?php echo date('M', strtotime($event['ngay_dien_ra'])); ?></span>
                    </div>
                </div>
                <div class="event-info">
                    <h3 class="event-title"><?php echo htmlspecialchars($event['ten_su_kien']); ?></h3>
                    <div class="event-meta">
                        <span class="event-time"><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?></span>
                        <span class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['dia_diem']); ?></span>
                    </div>
                    <div class="event-price">
                        <?php if ($event['gia_ve_min'] == $event['gia_ve_max']): ?>
                            <span class="price"><?php echo number_format($event['gia_ve_min']); ?>đ</span>
                        <?php else: ?>
                            <span class="price"><?php echo number_format($event['gia_ve_min']); ?>đ - <?php echo number_format($event['gia_ve_max']); ?>đ</span>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/event/<?php echo $event['ma_su_kien']; ?>" class="btn btn-primary">Xem chi tiết</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="categories">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Khám phá theo danh mục</h2>
        </div>
        
        <div class="category-list">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo BASE_URL; ?>/search?category=<?php echo $category['maloaisukien']; ?>" class="category-item">
                    <i class="<?php echo htmlspecialchars($category['icon'] ?? 'fas fa-calendar-alt'); ?>"></i>
                    <span><?php echo htmlspecialchars($category['tenloaisukien']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="upcoming-events">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Sự kiện sắp diễn ra</h2>
            <a href="<?php echo BASE_URL; ?>/search?upcoming=1" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="event-grid">
            <?php foreach ($upcomingEvents as $event): ?>
            <div class="event-card">
                <div class="event-image">
                    <img src="https://via.placeholder.com/400x250/333333/FFFFFF?text=<?php echo urlencode($event['ten_su_kien']); ?>" alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>">
                    <div class="event-date">
                        <span class="day"><?php echo date('d', strtotime($event['ngay_dien_ra'])); ?></span>
                        <span class="month"><?php echo date('M', strtotime($event['ngay_dien_ra'])); ?></span>
                    </div>
                </div>
                <div class="event-info">
                    <h3 class="event-title"><?php echo htmlspecialchars($event['ten_su_kien']); ?></h3>
                    <div class="event-meta">
                        <span class="event-time"><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?></span>
                        <span class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['dia_diem']); ?></span>
                    </div>
                    <div class="event-price">
                        <?php if ($event['gia_ve_min'] == $event['gia_ve_max']): ?>
                            <span class="price"><?php echo number_format($event['gia_ve_min']); ?>đ</span>
                        <?php else: ?>
                            <span class="price"><?php echo number_format($event['gia_ve_min']); ?>đ - <?php echo number_format($event['gia_ve_max']); ?>đ</span>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/event/<?php echo $event['ma_su_kien']; ?>" class="btn btn-primary">Xem chi tiết</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="newsletter">
    <div class="container">
        <div class="newsletter-content">
            <h2 class="newsletter-title">Đừng bỏ lỡ sự kiện nào!</h2>
            <p class="newsletter-description">Đăng ký nhận thông báo về các sự kiện mới nhất phù hợp sở thích của bạn.</p>
            <form class="newsletter-form" action="<?php echo BASE_URL; ?>/subscribe" method="POST">
                <input type="email" name="email" placeholder="Nhập email của bạn" class="newsletter-input" required>
                <button type="submit" class="btn btn-primary">Đăng ký</button>
            </form>
        </div>
    </div>
</section>

<?php
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
?>
