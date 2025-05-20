<?php require_once 'views/layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/booking.css">

<div class="container">
    <div class="thanks-container">
        <div class="thanks-header">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Cảm ơn bạn đã đặt vé!</h1>
            <p class="subtitle">Thanh toán của bạn đã được xác nhận và vé đã được đặt thành công.</p>
        </div>
        
        <?php if ($event): ?>
        <div class="event-info-card">
            <div class="event-image">
                <?php if (!empty($event['hinh_anh'])): ?>
                <img src="<?php echo BASE_URL; ?>/public/uploads/events/<?php echo $event['hinh_anh']; ?>" alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>">
                <?php else: ?>
                <div class="no-image">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <?php endif; ?>
            </div>
            <div class="event-details">
                <h2><?php echo htmlspecialchars($event['ten_su_kien']); ?></h2>
                <div class="event-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span><?php echo date('d/m/Y', strtotime($event['ngay_dien_ra'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($event['dia_diem']); ?></span>
                    </div>
                </div>
                <div class="event-description">
                    <?php echo nl2br(htmlspecialchars(substr($event['mo_ta'], 0, 150)) . (strlen($event['mo_ta']) > 150 ? '...' : '')); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="tickets-summary">
            <h3>Thông tin vé của bạn</h3>
            
            <div class="tickets-list">
                <?php if (!empty($tickets)): ?>
                    <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket-item">
                        <div class="ticket-header">
                            <div class="ticket-number">
                                <span>Mã vé: #<?php echo $ticket['id']; ?></span>
                            </div>
                            <div class="ticket-type">
                                <span><?php echo isset($ticket['ticket_type']['ten_loai_ve']) ? htmlspecialchars($ticket['ticket_type']['ten_loai_ve']) : 'Loại vé không xác định'; ?></span>
                            </div>
                        </div>
                        <div class="ticket-details">
                            <div class="seat-info">
                                <i class="fas fa-chair"></i>
                                <span>Ghế: <?php 
                                    if (isset($ticket['seat']['so_cho'])) {
                                        echo htmlspecialchars($ticket['seat']['so_cho']);
                                    } else {
                                        echo 'Không xác định';
                                    }
                                ?> (<?php echo isset($ticket['seat']['khu_vuc']) ? htmlspecialchars($ticket['seat']['khu_vuc']) : 'Không xác định'; ?>)</span>
                            </div>
                            <div class="price-info">
                                <i class="fas fa-tag"></i>
                                <span>Giá: <?php echo isset($ticket['ticket_type']['gia_ve']) ? number_format($ticket['ticket_type']['gia_ve']) : 0; ?> VNĐ</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-tickets">
                        <p>Không có thông tin vé.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="payment-summary">
                <div class="payment-row">
                    <span>Phương thức thanh toán:</span>
                    <span class="payment-method">
                        <?php 
                        switch($paymentMethod) {
                            case 'MOMO':
                                echo '<i class="fas fa-wallet"></i> MoMo';
                                break;
                            case 'VNPAY':
                                echo '<i class="fas fa-credit-card"></i> VNPAY';
                                break;
                            case 'THE_NGAN_HANG':
                                echo '<i class="fas fa-university"></i> Thẻ ngân hàng';
                                break;
                            default:
                                echo $paymentMethod;
                        }
                        ?>
                    </span>
                </div>
                <div class="payment-row total">
                    <span>Tổng thanh toán:</span>
                    <span class="total-amount"><?php echo number_format($paymentAmount); ?> VNĐ</span>
                </div>
            </div>
        </div>
        
        <div class="thanks-actions">
            <a href="<?php echo BASE_URL; ?>/tickets/my-tickets" class="btn-primary">
                <i class="fas fa-ticket-alt"></i>
                Xem vé của tôi
            </a>
            <a href="<?php echo BASE_URL; ?>" class="btn-secondary">
                <i class="fas fa-home"></i>
                Về trang chủ
            </a>
        </div>
        
        <div class="additional-info">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="info-content">
                    <h4>Thông tin quan trọng</h4>
                    <p>Vé của bạn đã được gửi đến email đăng ký. Vui lòng kiểm tra hộp thư của bạn.</p>
                    <p>Bạn cũng có thể xem và tải vé của mình trong mục "Vé của tôi" trên tài khoản.</p>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="info-content">
                    <h4>Cần hỗ trợ?</h4>
                    <p>Nếu bạn có bất kỳ câu hỏi nào về đơn hàng hoặc vé của mình, vui lòng liên hệ với chúng tôi qua:</p>
                    <p><i class="fas fa-envelope"></i> support@venow.com</p>
                    <p><i class="fas fa-phone"></i> 1900 1234</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.thanks-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 3rem 0;
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.thanks-header {
    text-align: center;
    margin-bottom: 3rem;
}

.success-icon {
    font-size: 5rem;
    color: #4CAF50;
    margin-bottom: 1.5rem;
    animation: scaleIn 0.6s ease-out;
}

@keyframes scaleIn {
    from { transform: scale(0); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.thanks-header h1 {
    font-size: 2.5rem;
    color: #333;
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.subtitle {
    font-size: 1.1rem;
    color: #666;
    max-width: 600px;
    margin: 0 auto;
}

.event-info-card {
    display: flex;
    background-color: #fff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    margin-bottom: 2.5rem;
    border: 1px solid #eaeaea;
    transition: all 0.3s ease;
}

.event-info-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    transform: translateY(-3px);
}

.event-image {
    width: 200px;
    min-width: 200px;
    height: 200px;
    overflow: hidden;
    position: relative;
}

.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.event-info-card:hover .event-image img {
    transform: scale(1.05);
}

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f5f5f5;
    color: #aaa;
    font-size: 3rem;
}

.event-details {
    padding: 1.5rem;
    flex: 1;
}

.event-details h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1rem;
}

.event-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
    font-size: 0.9rem;
}

.meta-item i {
    color: #FF5722;
}

.event-description {
    color: #777;
    font-size: 0.9rem;
    line-height: 1.6;
}

.tickets-summary {
    background-color: #fff;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid #eaeaea;
}

.tickets-summary h3 {
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #eaeaea;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.tickets-summary h3::before {
    content: "\f145";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    color: #FF5722;
}

.tickets-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.ticket-item {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #eaeaea;
    transition: all 0.3s ease;
}

.ticket-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.ticket-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px dashed #ddd;
}

.ticket-number {
    font-weight: 600;
    color: #333;
}

.ticket-type {
    background-color: #FF5722;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.ticket-details {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.seat-info, .price-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #666;
}

.seat-info i, .price-info i {
    color: #FF5722;
    width: 16px;
}

.no-tickets {
    grid-column: 1 / -1;
    text-align: center;
    padding: 2rem;
    background-color: #f9f9f9;
    border-radius: 8px;
    color: #666;
}

.payment-summary {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 1.5rem;
    margin-top: 1.5rem;
    border: 1px solid #eaeaea;
}

.payment-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    color: #666;
}

.payment-row.total {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #eaeaea;
    font-weight: 600;
    color: #333;
}

.payment-method {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.payment-method i {
    color: #FF5722;
}

.total-amount {
    color: #FF5722;
    font-size: 1.2rem;
}

.thanks-actions {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.btn-primary, .btn-secondary {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #FF5722;
    color: white;
}

.btn-primary:hover {
    background-color: #E64A19;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 87, 34, 0.3);
}

.btn-secondary {
    background-color: #f5f5f5;
    color: #666;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background-color: #eaeaea;
    color: #333;
    transform: translateY(-2px);
}

.additional-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.info-card {
    background-color: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    border: 1px solid #eaeaea;
    transition: all 0.3s ease;
}

.info-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.info-icon {
    font-size: 2rem;
    color: #FF5722;
}

.info-content {
    flex: 1;
}

.info-content h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.75rem;
}

.info-content p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
    line-height: 1.5;
}

.info-content p:last-child {
    margin-bottom: 0;
}

@media (max-width: 768px) {
    .event-info-card {
        flex-direction: column;
    }
    
    .event-image {
        width: 100%;
        height: 180px;
    }
    
    .thanks-actions {
        flex-direction: column;
    }
    
    .tickets-list {
        grid-template-columns: 1fr;
    }
}
</style>

<?php require_once 'views/layouts/footer.php'; ?>
