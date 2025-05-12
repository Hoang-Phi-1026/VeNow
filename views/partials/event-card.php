<?php
/**
 * Partial view for displaying an event card
 * @param array $event Event data
 */
?>
<div class="event-card">
    <div class="event-image">
    <?php if (!empty($event['hinh_anh']) && file_exists(BASE_PATH . '/' . $event['hinh_anh'])): ?>
        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($event['hinh_anh']); ?>" 
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
            <?php if (isset($event['gia_ve_min']) && $event['gia_ve_min'] > 0): ?>
                <?php if (isset($event['gia_ve_max']) && $event['gia_ve_max'] > $event['gia_ve_min']): ?>
                    <span class="price"><?php echo number_format($event['gia_ve_min']); ?>đ - <?php echo number_format($event['gia_ve_max']); ?>đ</span>
                <?php else: ?>
                    <span class="price"><?php echo number_format($event['gia_ve_min']); ?>đ</span>
                <?php endif; ?>
            <?php else: ?>
                <span class="price">Miễn phí</span>
            <?php endif; ?>
        </div>
        <a href="<?php echo BASE_URL; ?>/event/<?php echo $event['ma_su_kien']; ?>" class="btn btn-primary">Xem chi tiết</a>
    </div>
</div>
