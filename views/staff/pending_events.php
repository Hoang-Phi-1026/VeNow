<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h2 class="section-title">Sự kiện chờ duyệt</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (empty($events)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Không có sự kiện nào đang chờ duyệt.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sự kiện</th>
                        <th>Nhà tổ chức</th>
                        <th>Loại sự kiện</th>
                        <th>Ngày diễn ra</th>
                        <th>Địa điểm</th>
                        <th>Giá vé</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= $event['ma_su_kien'] ?></td>
                            <td>
                                <a href="<?= BASE_URL ?>/event/<?= $event['ma_su_kien'] ?>" target="_blank">
                                    <?= htmlspecialchars($event['ten_su_kien']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($event['tennhatochuc']) ?></td>
                            <td><?= htmlspecialchars($event['tenloaisukien']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($event['ngay_dien_ra'] . ' ' . $event['gio_dien_ra'])) ?></td>
                            <td><?= htmlspecialchars($event['dia_diem']) ?></td>
                            <td>
                                <?php if (isset($event['gia_ve_min']) && isset($event['gia_ve_max'])): ?>
                                    <?php if ($event['gia_ve_min'] == $event['gia_ve_max']): ?>
                                        <?= number_format($event['gia_ve_min'], 0, ',', '.') ?> VNĐ
                                    <?php else: ?>
                                        <?= number_format($event['gia_ve_min'], 0, ',', '.') ?> - <?= number_format($event['gia_ve_max'], 0, ',', '.') ?> VNĐ
                                    <?php endif; ?>
                                <?php else: ?>
                                    Chưa có thông tin
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <form action="<?= BASE_URL ?>/staff/approve-event" method="POST" class="d-inline">
                                        <input type="hidden" name="event_id" value="<?= $event['ma_su_kien'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Bạn có chắc muốn duyệt sự kiện này?')">
                                            <i class="fas fa-check"></i> Duyệt
                                        </button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/staff/reject-event" method="POST" class="d-inline ms-2">
                                        <input type="hidden" name="event_id" value="<?= $event['ma_su_kien'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc muốn từ chối sự kiện này?')">
                                            <i class="fas fa-times"></i> Từ chối
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
