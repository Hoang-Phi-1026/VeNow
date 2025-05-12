<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/tickets.css">

<div class="container ticket-history">
    <div class="ticket-history-header">
        <h1>Lịch sử đặt vé</h1>
    </div>

    <?php if (empty($tickets)): ?>
        <div class="empty-state">
            <i class="fas fa-ticket-alt"></i>
            <h3>Bạn chưa có lịch sử đặt vé nào</h3>
            <p>Hãy khám phá các sự kiện thú vị và đặt vé ngay!</p>
            <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">Khám phá sự kiện</a>
        </div>
    <?php else: ?>
        <div class="ticket-table-container">
            <table class="ticket-table">
                <thead>
                    <tr>
                        <th>Sự kiện</th>
                        <th>Ngày diễn ra</th>
                        <th>Địa điểm</th>
                        <th>Loại vé</th>
                        <th>Số chỗ</th>
                        <th>Giá vé</th>
                        <th>Trạng thái</th>
                        <th>Thời gian đặt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/event/<?php echo $ticket['ma_su_kien']; ?>" class="event-link">
                                    <?php echo htmlspecialchars($ticket['ten_su_kien']); ?>
                                </a>
                            </td>
                            <td>
                                <?php 
                                    $date = new DateTime($ticket['ngay_dien_ra']);
                                    echo $date->format('d/m/Y') . ' ' . $ticket['gio_dien_ra'];
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($ticket['dia_diem']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['ten_loai_ve']); ?></td>
                            <td><?php echo htmlspecialchars($ticket['so_cho']); ?></td>
                            <td>
                                <span class="price">
                                    <?php echo number_format($ticket['gia_ve'], 0, ',', '.'); ?>đ
                                </span>
                            </td>
                            <td>
                                <?php
                                    switch ($ticket['trang_thai']) {
                                        case 'DAT_VE':
                                            echo '<span class="status-badge status-success">Đặt vé</span>';
                                            break;
                                        case 'HUY_VE':
                                            echo '<span class="status-badge status-cancelled">Hủy vé</span>';
                                            break;
                                        case 'HOAN_VE':
                                            echo '<span class="status-badge status-pending">Hoàn vé</span>';
                                            break;
                                        default:
                                            echo '<span class="status-badge status-unknown">Không xác định</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php 
                                    $date = new DateTime($ticket['thoi_gian']);
                                    echo $date->format('d/m/Y H:i');
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
