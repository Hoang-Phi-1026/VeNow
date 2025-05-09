<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Sự kiện của tôi</h2>
            <p class="text-muted">Nhà tổ chức: <?php echo htmlspecialchars($organizerName); ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>/events/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo sự kiện mới
        </a>
    </div>

    <?php if (empty($events)): ?>
        <div class="alert alert-info">
            <p>Bạn chưa có sự kiện nào. Hãy tạo sự kiện mới!</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($events as $event): ?>
                <div class="col-md-6 col-lg-3 mb-4">
                    <div class="card h-100">
                        <div class="card-img-top position-relative">
                            <?php if ($event['hinh_anh']): ?>
                                <img src="<?php echo BASE_URL; ?>/public/uploads/events/<?php echo $event['hinh_anh']; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>">
                            <?php else: ?>
                                <img src="<?php echo BASE_URL; ?>/public/images/default-event.jpg" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>">
                            <?php endif; ?>
                            <span class="badge <?php 
                                echo match($event['trang_thai']) {
                                    'CHO_DUYET' => 'bg-warning',
                                    'DA_DUYET' => 'bg-success',
                                    'TU_CHOI' => 'bg-danger',
                                    'DA_HUY' => 'bg-secondary',
                                    default => 'bg-primary'
                                };
                            ?> position-absolute top-0 end-0 m-2">
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
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($event['ten_su_kien']); ?></h5>
                            <p class="card-text text-muted">
                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($event['ngay_dien_ra'])); ?><br>
                                <i class="fas fa-clock"></i> <?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?><br>
                                <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['dia_diem']); ?><br>
                                <i class="fas fa-ticket-alt"></i> Đã bán: <?php echo $event['so_ve_da_ban']; ?> vé
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100">
                                <a href="<?php echo BASE_URL; ?>/event/<?php echo $event['ma_su_kien']; ?>" 
                                   class="btn btn-outline-info">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                                <a href="<?php echo BASE_URL; ?>/organizer/events/edit/<?php echo $event['ma_su_kien']; ?>" 
                                   class="btn btn-outline-warning">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <?php if ($event['trang_thai'] != 'DA_DUYET'): ?>
                                    <button onclick="deleteEvent(<?php echo $event['ma_su_kien']; ?>)" 
                                            class="btn btn-outline-danger">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                <?php endif; ?>
                            </div>
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
        const button = event.target.closest('button');
        button.classList.add('loading');
        
        fetch(`<?php echo BASE_URL; ?>/organizer/events/delete/${maSuKien}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Có lỗi xảy ra khi xóa sự kiện!');
                    button.classList.remove('loading');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa sự kiện!');
                button.classList.remove('loading');
            });
    }
}
</script>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
