<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../utils/IdHasher.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/admin.css">

<div class="container admin-container">
    <div class="page-header">
        <h1>Sự kiện chờ duyệt</h1>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($events)): ?>
        <div class="no-results">
            <i class="fas fa-check-circle"></i>
            <h3>Không có sự kiện nào chờ duyệt</h3>
            <p>Tất cả sự kiện đã được xử lý.</p>
        </div>
    <?php else: ?>
        <div class="admin-event-grid">
            <?php foreach ($events as $event): ?>
                <div class="admin-event-card">
                    <div class="event-image">
                        <?php if (!empty($event['hinh_anh']) && file_exists(BASE_PATH . '/' . $event['hinh_anh'])): ?>
                            <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($event['hinh_anh']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>">
                        <?php else: ?>
                            <img src="<?php echo BASE_URL; ?>/public/images/placeholder.jpg" 
                                 alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>">
                        <?php endif; ?>
                        <div class="event-date">
                            <span class="day"><?php echo date('d', strtotime($event['ngay_dien_ra'])); ?></span>
                            <span class="month"><?php echo date('M', strtotime($event['ngay_dien_ra'])); ?></span>
                        </div>
                    </div>
                    <div class="event-info">
                        <h3 class="event-title"><?php echo htmlspecialchars($event['ten_su_kien']); ?></h3>
                        <div class="event-meta">
                            <span><i class="fas fa-building"></i> <?php echo htmlspecialchars($event['ho_ten']); ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['dia_diem']); ?></span>
                            <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($event['ngay_dien_ra'])); ?></span>
                            <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($event['tenloaisukien']); ?></span>
                        </div>
                        <div class="event-price">
                            <span class="price">
                                <?php if (isset($event['gia_ve_min']) && isset($event['gia_ve_max'])): ?>
                                    <?php if ($event['gia_ve_min'] == 0 && $event['gia_ve_max'] == 0): ?>
                                        Miễn phí
                                    <?php else: ?>
                                        <?php echo number_format($event['gia_ve_min']); ?>đ - <?php echo number_format($event['gia_ve_max']); ?>đ
                                    <?php endif; ?>
                                <?php else: ?>
                                    Chưa có thông tin giá vé
                                <?php endif; ?>
                            </span>
                            <span class="status-badge status-pending">Chờ duyệt</span>
                        </div>
                        <div class="event-actions">
                            <form action="<?php echo BASE_URL; ?>/admin/approve-event" method="POST">
                                <input type="hidden" name="event_id" value="<?php echo $event['ma_su_kien']; ?>">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Duyệt
                                </button>
                            </form>
                            <form action="<?php echo BASE_URL; ?>/admin/reject-event" method="POST">
                                <input type="hidden" name="event_id" value="<?php echo $event['ma_su_kien']; ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Từ chối
                                </button>
                            </form>
                            <a href="<?php echo BASE_URL; ?>/event/<?php echo IdHasher::encode($event['ma_su_kien']); ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
