<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm sự kiện - Venow</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="<?php echo BASE_URL; ?>/public/js/theme.js"></script>
</head>
<body>
    <?php include 'views/layouts/header.php'; ?>

    <div class="container">
        <div class="search-page">
            <div class="filter-container">
                <form action="<?php echo BASE_URL; ?>/search" method="GET" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="category">Danh mục</label>
                            <select name="category" id="category" class="filter-select">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo isset($_GET['category']) && $_GET['category'] == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['ten_danh_muc']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="date">Thời gian</label>
                            <select name="date" id="date" class="filter-select">
                                <option value="">Tất cả thời gian</option>
                                <option value="today" <?php echo isset($_GET['date']) && $_GET['date'] == 'today' ? 'selected' : ''; ?>>Hôm nay</option>
                                <option value="tomorrow" <?php echo isset($_GET['date']) && $_GET['date'] == 'tomorrow' ? 'selected' : ''; ?>>Ngày mai</option>
                                <option value="week" <?php echo isset($_GET['date']) && $_GET['date'] == 'week' ? 'selected' : ''; ?>>Tuần này</option>
                                <option value="month" <?php echo isset($_GET['date']) && $_GET['date'] == 'month' ? 'selected' : ''; ?>>Tháng này</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="price">Giá vé</label>
                            <select name="price" id="price" class="filter-select">
                                <option value="">Tất cả giá</option>
                                <option value="free" <?php echo isset($_GET['price']) && $_GET['price'] == 'free' ? 'selected' : ''; ?>>Miễn phí</option>
                                <option value="paid" <?php echo isset($_GET['price']) && $_GET['price'] == 'paid' ? 'selected' : ''; ?>>Có phí</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="location">Địa điểm</label>
                            <select name="location" id="location" class="filter-select">
                                <option value="">Tất cả địa điểm</option>
                                <?php foreach ($locations as $location): ?>
                                <option value="<?php echo $location['id']; ?>" <?php echo isset($_GET['location']) && $_GET['location'] == $location['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($location['ten_dia_diem']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="reset" class="btn btn-secondary">
                            <i class="fas fa-undo"></i> Đặt lại
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>

            <div class="search-results">
                <?php if (empty($events)): ?>
                <div class="no-results">
                    <i class="fas fa-search fa-3x"></i>
                    <h3>Không tìm thấy sự kiện</h3>
                    <p>Vui lòng thử lại với bộ lọc khác</p>
                </div>
                <?php else: ?>
                <h2 class="results-title">
                    <?php if (empty($_GET['q'])): ?>
                        Tất cả sự kiện
                    <?php else: ?>
                        Kết quả tìm kiếm cho "<?php echo htmlspecialchars($_GET['q']); ?>"
                    <?php endif; ?>
                </h2>
                <div class="event-grid">
                    <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="event-image">
                            <?php if (!empty($event['hinh_anh']) && file_exists('public/uploads/events/' . $event['hinh_anh'])): ?>
                                <img src="<?php echo BASE_URL; ?>/public/uploads/events/<?php echo htmlspecialchars($event['hinh_anh']); ?>" 
                                     alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>/public/images/placeholder.jpg" 
                                     alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>"
                                     loading="lazy">
                            <?php endif; ?>
                            <div class="event-date">
                                <span class="day"><?php echo date('d', strtotime($event['ngay_dien_ra'])); ?></span>
                                <span class="month"><?php echo date('M', strtotime($event['ngay_dien_ra'])); ?></span>
                            </div>
                        </div>
                        <div class="event-info">
                            <h3 class="event-title"><?php echo htmlspecialchars($event['ten_su_kien']); ?></h3>
                            <div class="event-meta">
                                <span><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['dia_diem']); ?></span>
                            </div>
                            <div class="event-price">
                                <?php if (isset($event['gia_ve_thap_nhat']) && isset($event['gia_ve_cao_nhat'])): ?>
                                    <?php if ($event['gia_ve_thap_nhat'] == 0 && $event['gia_ve_cao_nhat'] == 0): ?>
                                        <span class="price">Miễn phí</span>
                                    <?php else: ?>
                                        <span class="price"><?php echo number_format($event['gia_ve_thap_nhat']); ?>đ - <?php echo number_format($event['gia_ve_cao_nhat']); ?>đ</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="price">Liên hệ</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/event/<?php echo $event['ma_su_kien']; ?>" class="btn btn-primary">Xem chi tiết</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'views/layouts/footer.php'; ?>
</body>
</html>
