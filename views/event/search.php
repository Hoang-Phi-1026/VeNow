<div class="event-grid">
    <?php foreach ($events as $event): ?>
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
                <h3><?php echo htmlspecialchars($event['ten_su_kien']); ?></h3>
                <p><?php echo htmlspecialchars($event['mo_ta']); ?></p>
                <a href="event.php?id=<?php echo htmlspecialchars($event['id']); ?>" class="btn">Xem chi tiết</a>
            </div>
        </div>
    <?php endforeach; ?>
</div> 