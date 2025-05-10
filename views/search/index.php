<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm kiếm sự kiện - Venow</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="<?php echo BASE_URL; ?>/public/js/theme.js"></script>
    <style>
        /* Additional styles for search page */
        .search-debug {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-family: monospace;
            display: none; /* Hidden by default */
        }
        
        .search-debug.show {
            display: block;
        }
        
        .debug-toggle {
            background: #f1f1f1;
            border: 1px solid #ddd;
            padding: 5px 10px;
            margin-bottom: 10px;
            cursor: pointer;
            font-size: 12px;
            border-radius: 3px;
        }
        
        /* Styles for search form */
        .search-form {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            align-items: flex-end;
        }
        
        .form-group {
            flex: 1;
            min-width: 150px;
        }
        
        .search-input {
            flex: 2;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        
        .reset-filters {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-group, .search-input, .form-actions {
                flex: 100%;
            }
            
            .form-actions {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="search-page">
        <div class="container">
            <div class="search-header">
                <?php if (!empty($categoryInfo)): ?>
                    <h1 class="search-title"><?php echo htmlspecialchars($categoryInfo['tenloaisukien']); ?></h1>
                    <?php if (!empty($categoryInfo['mota'])): ?>
                        <p class="category-description"><?php echo htmlspecialchars($categoryInfo['mota']); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <h3 class="search-title">Lọc sự kiện</h3>
                <?php endif; ?>
            </div>

            <!-- Debug information for administrators -->
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['vai_tro'] == 1): ?>
                <div id="searchDebug" class="search-debug">
                    <h4>Search Parameters:</h4>
                    <pre>
Keyword: <?php echo htmlspecialchars($keyword ?? 'null'); ?>
Category: <?php echo htmlspecialchars($category ?? 'null'); ?>
Date: <?php echo htmlspecialchars($date ?? 'null'); ?>
Location: <?php echo htmlspecialchars($location ?? 'null'); ?>
Price: <?php echo htmlspecialchars($price ?? 'null'); ?>
                    </pre>
                    <h4>Results Count: <?php echo count($events); ?></h4>
                </div>
            <?php endif; ?>

            <div class="search-filters">
                <form action="<?php echo BASE_URL; ?>/search" method="GET" class="search-form">
                    
                    <div class="form-group">
                        <select name="category" class="form-control">
                            <option value="">Tất cả danh mục</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['maloaisukien']; ?>" <?php echo (isset($category) && $category == $cat['maloaisukien']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['tenloaisukien']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="date" name="date" value="<?php echo htmlspecialchars($date ?? ''); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="text" name="location" placeholder="Địa điểm" 
                               value="<?php echo htmlspecialchars($location ?? ''); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <select name="price" class="form-control">
                            <option value="">Tất cả giá</option>
                            <option value="free" <?php echo (isset($price) && $price === 'free') ? 'selected' : ''; ?>>Miễn phí</option>
                            <option value="paid" <?php echo (isset($price) && $price === 'paid') ? 'selected' : ''; ?>>Có phí</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                        <a href="<?php echo BASE_URL; ?>/search" class="btn btn-secondary reset-filters">
                            <i class="fas fa-undo"></i> Thiết lập lại
                        </a>
                    </div>
                </form>
            </div>

            <?php if (empty($events)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>Không tìm thấy sự kiện nào</h3>
                    <p>Hãy thử tìm kiếm với từ khóa khác hoặc bỏ bớt bộ lọc</p>
                </div>
            <?php else: ?>
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
                                    <span class="event-time"><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?></span>
                                    <span class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['dia_diem']); ?></span>
                                </div>
                                <div class="event-price">
                                    <?php if (isset($event['gia_ve_min']) && isset($event['gia_ve_max'])): ?>
                                        <?php if ($event['gia_ve_min'] == $event['gia_ve_max']): ?>
                                            <span class="price"><?php echo number_format($event['gia_ve_min']); ?>đ</span>
                                        <?php else: ?>
                                            <span class="price"><?php echo number_format($event['gia_ve_min']); ?>đ - <?php echo number_format($event['gia_ve_max']); ?>đ</span>
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

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>
