<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/tickets.css">

<div class="container ticket-history">
    <div class="ticket-history-header">
        <h1>Lịch sử đặt vé</h1>
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

    <div class="refund-info-box">
        <h3><i class="fas fa-info-circle"></i> Điều kiện hoàn vé</h3>
        <p>Vé có thể hoàn nếu sự kiện chưa diễn ra trong vòng 5 ngày tới. Khi hoàn vé, bạn sẽ nhận được điểm tích lũy bằng 0.02% giá vé.</p>
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
                        <th>Thao tác</th>
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
                            <td>
                                <?php
                                    // Chỉ kiểm tra thời gian sự kiện
                                    $eventDate = new DateTime($ticket['ngay_dien_ra']);
                                    $today = new DateTime();
                                    $daysDiff = $today->diff($eventDate)->days;
                                    
                                    // Hiển thị nút hoàn vé nếu sự kiện còn ít nhất 5 ngày
                                    if ($daysDiff >= 5 && $eventDate > $today):
                                ?>
                                    <button class="btn btn-refund" 
                                            onclick="confirmRefund(<?php echo $ticket['ma_ve']; ?>, 
                                                                  '<?php echo htmlspecialchars($ticket['ten_su_kien']); ?>', 
                                                                  <?php echo $ticket['gia_ve']; ?>, 
                                                                  '<?php echo $ticket['so_cho']; ?>')">
                                        Hoàn vé
                                    </button>
                                <?php else: ?>
                                    <span class="refund-not-available">Quá hạn hoàn vé</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal xác nhận hoàn vé -->
<div id="refundModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Xác nhận hoàn vé</h2>
        <p id="refundEventName"></p>
        <p id="refundSeatInfo"></p>
        <p id="refundPriceInfo"></p>
        <p id="refundPointsInfo"></p>
        <p class="refund-warning">Lưu ý: Bạn chỉ có thể hoàn vé trước 5 ngày sự kiện diễn ra.</p>
        <div class="modal-actions">
            <button id="confirmRefundBtn" class="btn btn-primary">Xác nhận hoàn vé</button>
            <button id="cancelRefundBtn" class="btn btn-secondary">Hủy</button>
        </div>
    </div>
</div>

<script>
    // Hiển thị modal xác nhận hoàn vé
    function confirmRefund(ticketId, eventName, ticketPrice, seatNumber) {
        // Tính số điểm tích lũy sẽ nhận được (giá vé × 0.0002)
        const loyaltyPoints = ticketPrice * 0.0002;
        
        // Cập nhật nội dung modal
        document.getElementById('refundEventName').textContent = `Sự kiện: ${eventName}`;
        document.getElementById('refundSeatInfo').textContent = `Số ghế: ${seatNumber}`;
        document.getElementById('refundPriceInfo').textContent = `Giá vé: ${new Intl.NumberFormat('vi-VN').format(ticketPrice)}đ`;
        document.getElementById('refundPointsInfo').textContent = `Điểm tích lũy nhận được: ${loyaltyPoints.toFixed(2)} điểm`;
        
        // Hiển thị modal
        const modal = document.getElementById('refundModal');
        modal.style.display = 'block';
        
        // Xử lý nút đóng modal
        const closeBtn = document.getElementsByClassName('close')[0];
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        }
        
        // Xử lý nút hủy
        document.getElementById('cancelRefundBtn').onclick = function() {
            modal.style.display = 'none';
        }
        
        // Xử lý nút xác nhận hoàn vé
        document.getElementById('confirmRefundBtn').onclick = function() {
            // Hiển thị thông báo đang xử lý
            document.getElementById('confirmRefundBtn').textContent = 'Đang xử lý...';
            document.getElementById('confirmRefundBtn').disabled = true;
            
            // Gửi yêu cầu hoàn vé
            window.location.href = `<?php echo BASE_URL; ?>/tickets/refund/${ticketId}`;
        }
        
        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    }
</script>

<style>
    /* CSS cho modal */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .modal-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 20px;
        border-radius: 8px;
        width: 50%;
        max-width: 500px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .close:hover {
        color: #000;
    }
    
    .modal h2 {
        margin-top: 0;
        color: #333;
    }
    
    .modal p {
        margin: 10px 0;
        font-size: 16px;
    }
    
    .refund-warning {
        color: #e74c3c;
        font-weight: bold;
        margin: 15px 0;
    }
    
    .modal-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
        gap: 10px;
    }
    
    /* CSS cho nút hoàn vé */
    .btn-refund {
        background-color: #e74c3c;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background-color 0.3s;
    }
    
    .btn-refund:hover {
        background-color: #c0392b;
    }
    
    /* CSS cho trạng thái hoàn vé */
    .status-refunded {
        background-color: #3498db;
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
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
