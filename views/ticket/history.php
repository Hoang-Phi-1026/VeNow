<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/tickets.css">

<div class="container ticket-history">
    <div class="ticket-history-header">
        <div class="header-title">
            <h1>Lịch sử đặt vé</h1>
            <p class="text-muted">Xem lại tất cả các vé bạn đã đặt</p>
        </div>
        
        <div class="header-actions">
            <a href="<?php echo BASE_URL; ?>/tickets/my-tickets" class="btn btn-primary">
                <i class="fas fa-ticket-alt"></i> Vé của tôi
            </a>
        </div>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
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
                        <th>Tên sự kiện</th>
                        <th>Ngày diễn ra</th>
                        <th>Địa điểm</th>
                        <th>Loại vé</th>
                        <th>Số chỗ</th>
                        <th>Giá vé</th>
                        <th>Trạng thái</th>
                        <th>Thời gian thực hiện</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <?php 
                            // Tạo ID mã hóa nếu cần
                            $ticketId = defined('ENCODE_URL_IDS') && ENCODE_URL_IDS ? encodeId($ticket['ma_ve']) : $ticket['ma_ve'];
                            $eventId = defined('ENCODE_URL_IDS') && ENCODE_URL_IDS ? encodeId($ticket['ma_su_kien']) : $ticket['ma_su_kien'];
                            
                            // Kiểm tra xem có giảm giá không
                            $hasDiscount = isset($ticket['gia_goc']) && isset($ticket['gia_ve']) && $ticket['gia_goc'] > $ticket['gia_ve'];
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo BASE_URL; ?>/event/<?php echo $eventId; ?>" class="event-link">
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
                                <?php if ($hasDiscount): ?>
                                    <span class="price-original"><?php echo number_format($ticket['gia_goc'], 0, ',', '.'); ?>đ</span>
                                    <span class="price-discounted"><?php echo number_format($ticket['gia_ve'], 0, ',', '.'); ?>đ</span>
                                <?php else: ?>
                                    <span class="price"><?php echo number_format($ticket['gia_ve'], 0, ',', '.'); ?>đ</span>
                                <?php endif; ?>
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
                                            echo '<span class="status-badge status-refunded">Hoàn vé</span>';
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



<style>
  
    
    /* CSS cho trạng thái hoàn vé */
    .status-refunded {
        background-color:rgba(220, 12, 12, 0.73);
    }
    
    /* CSS cho thông báo */
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }
    
    .alert-danger {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .refund-not-available {
        color: #777;
        font-size: 14px;
        font-style: italic;
    }
    
    .refund-info-box {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .refund-info-box h3 {
        margin-top: 0;
        color: #333;
        font-size: 18px;
    }
    
    .refund-info-box p {
        margin: 10px 0 0 0;
    }
    
    /* CSS cho hiển thị giá vé có giảm giá */
    .price-original {
        text-decoration: line-through;
        color: #999;
        font-size: 14px;
        display: block;
    }
    
    .price-discounted {
        color: #e74c3c;
        font-weight: bold;
        display: block;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
