<?php session_start(); ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/tickets.css?v=4">

<div class="container ticket-history">
    <div class="ticket-history-header">
        <div class="header-title">
            <h1>Vé của tôi</h1>
            <p>Các vé sự kiện sắp diễn ra của bạn</p>
        </div>
        
        <div class="header-actions">
            <a href="<?php echo BASE_URL; ?>/tickets/history" class="btn btn-secondary">
                <i class="fas fa-history"></i> Lịch sử đặt vé
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="refund-info-box">
        <h3><i class="fas fa-info-circle"></i> Chính sách hoàn vé</h3>
        <p>Bạn chỉ có thể hoàn vé trước ngày diễn ra sự kiện ít nhất 5 ngày. Khi hoàn vé, bạn sẽ nhận được điểm tích lũy tương ứng với giá trị vé.</p>
    </div>

    <?php if (empty($upcomingTickets)): ?>
        <div class="empty-state">
            <i class="fas fa-ticket-alt"></i>
            <h3>Bạn chưa có vé nào cho sự kiện sắp tới</h3>
            <p>Hãy khám phá các sự kiện thú vị và đặt vé ngay!</p>
            <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">Khám phá sự kiện</a>
        </div>
    <?php else: ?>
        <div class="tickets-grid">
            <?php foreach ($upcomingTickets as $ticket): ?>
                <?php 
                    // Tạo ID mã hóa nếu cần
                    $ticketId = defined('ENCODE_URL_IDS') && ENCODE_URL_IDS ? encodeId($ticket['ma_ve']) : $ticket['ma_ve'];
                    $eventId = defined('ENCODE_URL_IDS') && ENCODE_URL_IDS ? encodeId($ticket['ma_su_kien']) : $ticket['ma_su_kien'];
                    
                    // Kiểm tra xem có giảm giá không
                    $hasDiscount = isset($ticket['gia_goc']) && isset($ticket['gia_ve']) && $ticket['gia_goc'] > $ticket['gia_ve'];
                    
                    // Tính số ngày còn lại đến sự kiện
                    $eventDate = new DateTime($ticket['ngay_dien_ra']);
                    $today = new DateTime();
                    $daysDiff = $today->diff($eventDate)->days;
                    $canRefund = $daysDiff >= 5 && $eventDate > $today;
                    
                    // Tính điểm tích lũy dự kiến (0.02% giá vé)
                    $ticketPrice = isset($ticket['gia_ve']) ? $ticket['gia_ve'] : (isset($ticket['gia_goc']) ? $ticket['gia_goc'] : 0);
                    $estimatedPoints = $ticketPrice * 0.0002;
                ?>
                <div class="ticket-card">
                    <div class="ticket-header">
                        <div class="qr-code">
                            <img 
                                src="<?php echo BASE_URL; ?>/tickets/qr/<?php echo $ticketId; ?>" 
                                alt="QR Code"
                                title="Mã QR vé sự kiện"
                            >
                        </div>
                        <div class="event-info">
                            <h3><a href="<?php echo BASE_URL; ?>/event/<?php echo $eventId; ?>"><?php echo htmlspecialchars($ticket['ten_su_kien']); ?></a></h3>
                            <div class="event-meta">
                                <div class="meta-item"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($ticket['dia_diem']); ?></div>
                                <div class="meta-item"><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($ticket['ngay_dien_ra'])); ?></div>
                                <div class="meta-item"><i class="fas fa-clock"></i> <?php echo $ticket['gio_dien_ra']; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="ticket-body">
                        <div class="ticket-details">
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-ticket-alt"></i> Loại vé</div>
                                <div class="detail-value"><?php echo htmlspecialchars($ticket['ten_loai_ve']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-chair"></i> Số ghế</div>
                                <div class="detail-value"><?php echo htmlspecialchars($ticket['so_cho']); ?></div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-tag"></i> Giá vé</div>
                                <div class="detail-value price">
                                    <?php if ($hasDiscount): ?>
                                        <span class="price-original"><?php echo number_format($ticket['gia_goc'], 0, ',', '.'); ?>đ</span>
                                        <?php echo number_format($ticket['gia_ve'], 0, ',', '.'); ?>đ
                                    <?php else: ?>
                                        <?php echo number_format($ticket['gia_ve'], 0, ',', '.'); ?>đ
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label"><i class="fas fa-check-circle"></i> Trạng thái</div>
                                <div class="detail-value">
                                    <span class="status-badge status-success">Đã thanh toán</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="days-left">
                            <?php if ($daysDiff > 0): ?>
                                <span class="badge <?php echo $daysDiff < 10 ? 'status-pending' : 'status-success'; ?>">
                                    <i class="fas fa-calendar-day"></i> Còn <?php echo $daysDiff; ?> ngày
                                </span>
                            <?php else: ?>
                                <span class="badge status-pending">
                                    <i class="fas fa-calendar-day"></i> Hôm nay
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="ticket-actions">
                            <a href="<?php echo BASE_URL; ?>/tickets/qr/download/<?php echo $ticketId; ?>" class="btn btn-primary">
                                <i class="fas fa-qrcode"></i> Tải mã QR
                            </a>
                            <?php if ($canRefund): ?>
                                <button class="btn btn-refund" onclick="openRefundModal('<?php echo $ticketId; ?>', '<?php echo htmlspecialchars($ticket['ten_su_kien']); ?>', '<?php echo $ticketPrice; ?>', '<?php echo $estimatedPoints; ?>')">
                                    <i class="fas fa-undo"></i> Hoàn vé
                                </button>
                            <?php else: ?>
                                <div class="refund-not-available">
                                    <i class="fas fa-info-circle"></i> Quá hạn hoàn vé
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal xác nhận hoàn vé -->
<div id="refundModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeRefundModal()">&times;</span>
        <h2>Xác nhận hoàn vé</h2>
        <p>Bạn có chắc chắn muốn hoàn vé cho sự kiện <strong id="eventName"></strong>?</p>
        
        <div class="points-info">
            <div class="points-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="points-details">
                <h4>Điểm tích lũy</h4>
                <p>Bạn sẽ nhận được khoảng <strong id="estimatedPoints">0</strong> điểm tích lũy khi hoàn vé này.</p>
            </div>
        </div>
        
        <div class="refund-warning">
            <p><i class="fas fa-exclamation-triangle"></i> Lưu ý: Hành động này không thể hoàn tác sau khi xác nhận.</p>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeRefundModal()">Hủy</button>
            <button class="btn btn-danger" id="confirmRefund" onclick="processRefund()">Xác nhận hoàn vé</button>
        </div>
    </div>
</div>

<!-- Add success modal -->
<div id="successModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeSuccessModal()">&times;</span>
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <div class="success-message">
            <h3>Hoàn vé thành công!</h3>
            <p>Vé của bạn đã được hoàn thành công.</p>
            <div class="points-earned">
                <i class="fas fa-coins"></i>
                <span>Bạn đã nhận được <strong id="actualPoints">0</strong> điểm tích lũy.</span>
            </div>
        </div>
        <div class="success-actions">
            <button class="btn btn-primary" onclick="closeSuccessModal()">Đóng</button>
        </div>
    </div>
</div>

<style>
/* Styling for the tickets grid */
.tickets-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-top: 20px;
}

/* Ticket card styling */
.ticket-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
    border-top: 4px solid #007bff;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.ticket-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.15);
}

/* Ticket header */
.ticket-header {
    padding: 12px;
    display: flex;
    align-items: flex-start;
    border-bottom: 1px solid #eee;
}

/* QR Code styling */
.qr-code {
    width: 100px;
    height: 100px;
    margin-top: 10px;
    border-radius: 6px;
    overflow: hidden;
    margin-right: 12px;
    flex-shrink: 0;
    border: 1px solid #eee;
    background-color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
}

.qr-code img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.event-info {
    flex-grow: 1;
    min-width: 0; /* Prevent text overflow */
}

.event-info h3 {
    margin: 0 0 5px;
    font-size: 14px;
    line-height: 1.3;
    font-weight: 600;
}

.event-info h3 a {
    color: #333;
    text-decoration: none;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.event-info h3 a:hover {
    color: #007bff;
}

.event-meta {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.meta-item {
    font-size: 12px;
    color: #666;
    display: flex;
    align-items: center;
}

.meta-item i {
    margin-right: 5px;
    width: 14px;
    text-align: center;
    color: #888;
}

/* Ticket body */
.ticket-body {
    padding: 12px;
    display: flex;
    flex-direction: column;
    flex-grow: 1;
}

.ticket-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 12px;
}

.detail-item {
    font-size: 12px;
}

.detail-label {
    color: #666;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
}

.detail-label i {
    margin-right: 5px;
    width: 14px;
    text-align: center;
    color: #888;
}

.detail-value {
    font-weight: 500;
    color: #333;
}

.price-original {
    text-decoration: line-through;
    color: #999;
    font-size: 11px;
    display: block;
}

.status-badge {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}

.status-success {
    background-color: #d4edda;
    color: #155724;
}

.status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.days-left {
    margin-bottom: 12px;
}

.badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}

/* Ticket actions */
.ticket-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-top: auto;
}

.btn {
    display: inline-block;
    font-weight: 500;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    user-select: none;
    border: 1px solid transparent;
    padding: 6px 12px;
    font-size: 12px;
    line-height: 1.5;
    border-radius: 4px;
    transition: all 0.15s ease-in-out;
    text-decoration: none;
    cursor: pointer;
}

.btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0069d9;
    border-color: #0062cc;
}

.btn-refund {
    color: #dc3545;
    background-color: transparent;
    border-color: #dc3545;
}

.btn-refund:hover {
    color: #fff;
    background-color: #dc3545;
}

.btn-secondary {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

.btn-danger {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.refund-not-available {
    color: #6c757d;
    font-size: 11px;
    font-style: italic;
    text-align: center;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.modal.show {
    opacity: 1;
}

.modal-content {
    background-color: #fefefe;
    margin: 10% auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    width: 90%;
    max-width: 500px;
    position: relative;
    transform: translateY(-20px);
    transition: transform 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.modal.show .modal-content {
    transform: translateY(0);
}

.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
}

.modal h2 {
    margin-top: 0;
    color: #333;
    font-size: 20px;
}

.refund-warning {
    background-color: #fff3cd;
    border: 1px solid #ffeeba;
    border-radius: 4px;
    padding: 10px;
    margin: 15px 0;
}

.refund-warning p {
    color: #856404;
    margin: 0;
    font-size: 13px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

/* Success modal */
.success-icon {
    text-align: center;
    margin-bottom: 15px;
    color: #28a745;
}

.success-icon i {
    font-size: 48px;
}

.success-message {
    text-align: center;
}

.success-message h3 {
    color: #28a745;
    margin-bottom: 10px;
}

.success-actions {
    text-align: center;
    margin-top: 20px;
}

/* Points info styling */
.points-info {
    display: flex;
    align-items: center;
    background-color: #e8f4fd;
    border: 1px solid #b8daff;
    border-radius: 6px;
    padding: 12px;
    margin: 15px 0;
}

.points-icon {
    font-size: 24px;
    color: #007bff;
    margin-right: 15px;
}

.points-details {
    flex-grow: 1;
}

.points-details h4 {
    margin: 0 0 5px;
    color: #007bff;
    font-size: 16px;
}

.points-details p {
    margin: 0;
    font-size: 14px;
    color: #333;
}

.points-earned {
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #e8f4fd;
    border-radius: 6px;
    padding: 10px;
    margin-top: 15px;
    color: #007bff;
    font-size: 16px;
}

.points-earned i {
    margin-right: 10px;
    font-size: 20px;
}

/* Responsive styles */
@media (max-width: 1200px) {
    .tickets-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 992px) {
    .tickets-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .tickets-grid {
        grid-template-columns: 1fr;
    }
    
    .ticket-history-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-actions {
        margin-top: 10px;
    }
    
    .modal-content {
        margin: 20% auto;
        padding: 15px;
    }
}
</style>

<script>
    // Biến lưu trữ thông tin vé đang được xử lý
    let currentTicketId = null;
    let currentTicketPrice = 0;
    let currentEstimatedPoints = 0;
    
    
    // Xử lý modal xác nhận hoàn vé
    function openRefundModal(ticketId, eventName, ticketPrice, estimatedPoints) {
        currentTicketId = ticketId;
        currentTicketPrice = ticketPrice;
        currentEstimatedPoints = estimatedPoints;
        
        document.getElementById('eventName').textContent = eventName;
        document.getElementById('estimatedPoints').textContent = formatNumber(estimatedPoints);
        
        const modal = document.getElementById('refundModal');
        modal.style.display = 'block';
        
        // Thêm class show sau khi hiển thị để kích hoạt animation
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }
    
    function closeRefundModal() {
        const modal = document.getElementById('refundModal');
        modal.classList.remove('show');
        
        // Đợi animation kết thúc rồi mới ẩn modal
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
    
    // Xử lý modal thành công
    function openSuccessModal(points) {
        const modal = document.getElementById('successModal');
        document.getElementById('actualPoints').textContent = formatNumber(points || currentEstimatedPoints);
        
        modal.style.display = 'block';
        
        // Thêm class show sau khi hiển thị để kích hoạt animation
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }
    
    function closeSuccessModal() {
        const modal = document.getElementById('successModal');
        modal.classList.remove('show');
        
        // Đợi animation kết thúc rồi mới ẩn modal
        setTimeout(() => {
            modal.style.display = 'none';
            // Reload trang sau khi đóng modal thành công
            window.location.reload();
        }, 300);
    }
    
    // Xử lý hoàn vé
    function processRefund() {
        if (!currentTicketId) return;
        
        // Thêm hiệu ứng loading cho nút xác nhận
        const confirmButton = document.getElementById('confirmRefund');
        const originalText = confirmButton.innerHTML;
        confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        confirmButton.disabled = true;
        
        // Giả lập quá trình xử lý hoàn vé (thay thế bằng AJAX thực tế)
        setTimeout(() => {
            // Đóng modal xác nhận
            closeRefundModal();
            
            // Mở modal thành công sau khi đóng modal xác nhận
            setTimeout(() => {
                // Chuyển hướng đến trang xử lý hoàn vé
                window.location.href = '<?php echo BASE_URL; ?>/tickets/refund/' + currentTicketId;
                
                // Nếu muốn hiển thị modal thành công thay vì chuyển hướng, hãy bỏ comment dòng dưới
                // openSuccessModal();
            }, 300);
        }, 1500);
    }
    
    // Hàm định dạng số
    function formatNumber(number) {
        return parseFloat(number).toFixed().replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    
    // Đóng modal khi click bên ngoài
    window.onclick = function(event) {
        const refundModal = document.getElementById('refundModal');
        const successModal = document.getElementById('successModal');
        
        if (event.target == refundModal) {
            closeRefundModal();
        }
        
        if (event.target == successModal) {
            closeSuccessModal();
        }
    }
    
    // Kiểm tra nếu có thông báo thành công từ server với thông tin điểm
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['success']) && strpos($_SESSION['success'], 'điểm tích lũy') !== false): ?>
            // Trích xuất số điểm từ thông báo thành công
            const successMessage = "<?php echo $_SESSION['success']; ?>";
            const pointsMatch = successMessage.match(/(\d+(\.\d+)?)\s*điểm tích lũy/);
            if (pointsMatch && pointsMatch[1]) {
                const points = parseFloat(pointsMatch[1]);
                // Hiển thị modal thành công với số điểm
                setTimeout(() => {
                    openSuccessModal(points);
                }, 500);
            }
        <?php endif; ?>
    });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
