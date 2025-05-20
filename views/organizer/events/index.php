<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2>Sự kiện của tôi</h2>
      <p class="text-muted">Nhà tổ chức: <?php echo htmlspecialchars($organizerName); ?></p>
    </div>
    <div>
      <a href="<?php echo BASE_URL; ?>/events/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tạo sự kiện mới
      </a>
      <a href="<?php echo BASE_URL; ?>/organizer/revenue" class="btn btn-success ms-2">
        <i class="fas fa-chart-line"></i> Báo cáo doanh thu
      </a>
    </div>
  </div>

  <?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
      <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
  <?php endif; ?>

  <?php if (empty($events)): ?>
    <div class="alert alert-info">
      <i class="fas fa-info-circle"></i> Bạn chưa có sự kiện nào. Hãy tạo sự kiện mới!
    </div>
  <?php else: ?>
    <div class="event-grid">
      <?php foreach ($events as $event): ?>
        <div class="event-card">
          <div class="event-image">
            <?php if (!empty($event['hinh_anh']) && file_exists(BASE_PATH . '/' . $event['hinh_anh'])): ?>
              <img src="<?php echo BASE_URL; ?>/<?php echo $event['hinh_anh']; ?>" 
                   alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>">
            <?php else: ?>
              <img src="https://via.placeholder.com/400x250/1eb75c/FFFFFF?text=<?php echo urlencode($event['ten_su_kien']); ?>" 
                   alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>">
            <?php endif; ?>
            <span class="event-status <?php echo $event['trang_thai']; ?>">
              <?php 
                echo match($event['trang_thai']) {
                  'CHO_DUYET' => 'Chờ duyệt',
                  'DA_DUYET' => 'Đã duyệt',
                  'TU_CHOI' => 'Từ chối',
                  'DA_HUY' => 'Đã hủy',
                  default => $event['trang_thai']
                };
              ?>
            </span>
          </div>
          <div class="event-info">
            <h3 class="event-title"><?php echo htmlspecialchars($event['ten_su_kien']); ?></h3>
            <div class="event-meta">
              <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($event['ngay_dien_ra'])); ?></span>
              <span><i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?></span>
              <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['dia_diem']); ?></span>
              <span><i class="fas fa-ticket-alt"></i> Đã bán: <?php echo $event['so_ve_da_ban'] ?? 0; ?> vé</span>
            </div>
            <div class="event-actions">
              <a href="<?php echo BASE_URL; ?>/event/<?php echo $event['ma_su_kien']; ?>" 
                 class="btn btn-sm btn-outline-info">
                <i class="fas fa-eye"></i> Xem
              </a>
              <a href="<?php echo BASE_URL; ?>/organizer/events/edit?id=<?php echo $event['ma_su_kien']; ?>" 
                 class="btn btn-sm btn-outline-warning">
                <i class="fas fa-edit"></i> Sửa
              </a>
              <?php if ($event['trang_thai'] != 'DA_DUYET'): ?>
                <button onclick="deleteEvent(<?php echo $event['ma_su_kien']; ?>)" 
                        class="btn btn-sm btn-outline-danger">
                  <i class="fas fa-trash"></i> Xóa
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
function deleteEvent(maSuKien) {
  if (confirm('Bạn có chắc chắn muốn xóa sự kiện này?')) {
    window.location.href = `<?php echo BASE_URL; ?>/organizer/events/delete?id=${maSuKien}`;
  }
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
