<?php require_once 'views/layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/booking.css">

<div class="container">
    <div class="booking-header">
        <h1>Thanh toán đặt vé</h1>
        
        <div class="event-info-compact">
            <div class="event-name">
                <i class="fas fa-ticket-alt"></i>
                <span><?php echo htmlspecialchars($event['ten_su_kien']); ?></span>
            </div>
            <div class="event-details-row">
                <div class="info-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?php echo date('d/m/Y', strtotime($event['ngay_dien_ra'])); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <span><?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?></span>
                </div>
                <div class="info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span><?php echo htmlspecialchars($event['dia_diem']); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form thanh toán đơn giản -->
    <form id="payment-form" method="POST" action="<?php echo BASE_URL; ?>/momo-payment/process">
        <div class="payment-container">
            <div class="order-summary">
                <h3>Thông tin đơn hàng</h3>
                
                <div class="selected-seats-summary">
                    <h4>Ghế đã chọn:</h4>
                    <ul class="selected-seats-list">
                        <?php foreach ($seatDetails as $seat): ?>
                        <li>
                            <div class="seat-info">
                                <span class="seat-number">Ghế <?php echo htmlspecialchars($seat['number']); ?></span>
                                <span class="ticket-type">(<?php echo htmlspecialchars($seat['ticketType']); ?>)</span>
                            </div>
                            <span class="seat-price"><?php echo number_format($seat['price']); ?> VNĐ</span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="loyalty-points-section">
                    <h4>Điểm tích lũy của bạn:</h4>
                    <div class="current-points">
                        <span class="points-value"><?php echo number_format($totalLoyaltyPoints); ?></span>
                        <span class="points-label">điểm</span>
                        <span class="points-info">(Tương đương <?php echo number_format($totalLoyaltyPoints * $pointValue); ?> VNĐ)</span>
                    </div>
                    
                    <?php if ($totalLoyaltyPoints > 0): ?>
                    <div class="use-points-form">
                        <div class="points-input-group">
                            <label for="points-to-use">Sử dụng điểm:</label>
                            <div class="input-with-button">
                                <input type="number" id="points-to-use" name="usedPoints" min="0" max="<?php echo $totalLoyaltyPoints; ?>" step="0.01" value="0">
                                <button type="button" id="use-max-points" class="btn-secondary">Dùng tối đa</button>
                            </div>
                        </div>
                        <button type="button" id="apply-points" class="btn-primary">Áp dụng</button>
                    </div>
                    <?php else: ?>
                    <div class="no-points-message">
                        <p>Bạn chưa có điểm tích lũy. Hãy hoàn tất đơn hàng này để nhận điểm!</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="discount-info" style="display: none;">
                        <div class="discount-row">
                            <span>Số điểm sử dụng:</span>
                            <span id="used-points-value">0</span>
                        </div>
                        <div class="discount-row">
                            <span>Số tiền giảm:</span>
                            <span id="discount-amount">0 VNĐ</span>
                        </div>
                    </div>
                </div>
                
                <div class="total-amount">
                    <h4>Tổng tiền:</h4>
                    <div class="amount-container">
                        <div id="original-amount" class="original-amount"><?php echo number_format($totalAmount); ?> VNĐ</div>
                        <div id="final-amount" class="amount"><?php echo number_format($totalAmount); ?> VNĐ</div>
                    </div>
                    <input type="hidden" name="finalAmount" id="final-amount-input" value="<?php echo $totalAmount; ?>">
                    <input type="hidden" name="discountAmount" id="discount-amount-input" value="0">
                </div>
                
                <div class="loyalty-points">
                    <h4>Điểm tích lũy nhận được:</h4>
                    <div id="new-points" class="points"><?php echo number_format($totalAmount * 0.00003); ?> điểm</div>
                    <div class="points-info">
                        <i class="fas fa-info-circle"></i>
                        <span>Bạn sẽ nhận được điểm tích lũy tương ứng với giá trị đơn hàng</span>
                    </div>
                </div>
                
                <?php if (!empty($loyaltyPointsDetails)): ?>
                <div class="loyalty-history">
                    <h4>Lịch sử điểm tích lũy:</h4>
                    <div class="loyalty-history-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Điểm</th>
                                    <th>Nguồn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($loyaltyPointsDetails, 0, 5) as $point): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($point['ngay_nhan'])); ?></td>
                                    <td class="<?php echo $point['so_diem'] >= 0 ? 'positive' : 'negative'; ?>">
                                        <?php echo $point['so_diem'] >= 0 ? '+' : ''; ?><?php echo number_format($point['so_diem']); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        switch($point['nguon']) {
                                            case 'MUA_VE':
                                                echo 'Mua vé';
                                                break;
                                            case 'HOAN_VE':
                                                echo 'Hoàn vé';
                                                break;
                                            case 'UU_DAI':
                                                echo 'Sử dụng ưu đãi';
                                                break;
                                            default:
                                                echo $point['nguon'];
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="payment-methods">
                <h3>Chọn phương thức thanh toán</h3>
                
                <div class="payment-options">
                    <div class="payment-option">
                        <input type="radio" id="momo" name="paymentMethod" value="MOMO" checked>
                        <label for="momo">
                            <div class="payment-logo">
                                <i class="fas fa-wallet"></i>
                                <span>MOMO</span>
                            </div>
                            <div class="payment-description">
                                Thanh toán qua ví điện tử MOMO
                            </div>
                        </label>
                    </div>
                    
                    <div class="payment-option">
                        <input type="radio" id="vnpay" name="paymentMethod" value="VNPAY">
                        <label for="vnpay">
                            <div class="payment-logo">
                                <i class="fas fa-credit-card"></i>
                                <span>VNPAY</span>
                            </div>
                            <div class="payment-description">
                                Thanh toán qua cổng VNPAY
                            </div>
                        </label>
                    </div>
                    
                    <div class="payment-option">
                        <input type="radio" id="bank" name="paymentMethod" value="THE_NGAN_HANG">
                        <label for="bank">
                            <div class="payment-logo">
                                <i class="fas fa-university"></i>
                                <span>Thẻ ngân hàng</span>
                            </div>
                            <div class="payment-description">
                                Thanh toán qua thẻ ATM/Visa/Master
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="payment-terms">
                    <p>Bằng cách nhấn "Xác nhận thanh toán", bạn đồng ý với <a href="#">Điều khoản dịch vụ</a> và <a href="#">Chính sách hoàn vé</a> của chúng tôi.</p>
                </div>
                
                <button type="submit" id="confirm-payment-btn" class="btn btn-primary">Xác nhận thanh toán</button>
            </div>
        </div>
    </form>

    <div id="payment-success-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h2>Thanh toán thành công</h2>
            </div>
            <div class="modal-body">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <p>Cảm ơn bạn đã đặt vé sự kiện. Vé của bạn đã được xác nhận!</p>
                <p>Bạn có thể xem thông tin vé trong mục "Lịch sử vé" của tài khoản.</p>
            </div>
            <div class="modal-footer">
                <a href="<?php echo BASE_URL; ?>/tickets/history" class="btn btn-primary">Xem vé của tôi</a>
            </div>
        </div>
    </div>

    <div id="loading-overlay" class="loading-overlay">
        <div class="loading-spinner"></div>
        <div class="loading-text">Đang xử lý đặt vé...</div>
    </div>
</div>

<style>
/* CSS cho trang thanh toán - đồng bộ với giao diện hệ thống */
.payment-container {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-6);
    margin-top: var(--spacing-6);
    margin-bottom: var(--spacing-8);
    animation: slideUp 0.5s ease-out forwards;
}

.order-summary, .payment-methods {
    flex: 1;
    min-width: 300px;
    background-color: var(--bg-secondary);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: var(--spacing-5);
    border: 1px solid var(--border-color);
    transition: all var(--transition-normal);
}

.order-summary:hover, .payment-methods:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-3px);
}

.order-summary h3, .payment-methods h3 {
    margin-bottom: var(--spacing-4);
    padding-bottom: var(--spacing-3);
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
    font-weight: 600;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.order-summary h3::before {
    content: "\f07a";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    color: var(--accent-color);
}

.payment-methods h3::before {
    content: "\f09d";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    color: var(--accent-color);
}

.selected-seats-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.selected-seats-list li {
    display: flex;
    justify-content: space-between;
    padding: var(--spacing-3) 0;
    border-bottom: 1px solid var(--border-color);
    transition: all var(--transition-normal);
}

.selected-seats-list li:hover {
    background-color: var(--hover-bg);
    padding-left: var(--spacing-2);
    padding-right: var(--spacing-2);
    margin-left: calc(-1 * var(--spacing-2));
    margin-right: calc(-1 * var(--spacing-2));
    border-radius: var(--radius-md);
}

.seat-info {
    display: flex;
    flex-direction: column;
}

.seat-number {
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.seat-number::before {
    content: "\f5ca";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    color: var(--accent-color);
    font-size: 0.9rem;
}

.ticket-type {
    font-size: 0.85rem;
    color: var(--text-secondary);
    margin-top: var(--spacing-1);
}

.seat-price {
    color: var(--accent-color);
    font-weight: 600;
}

/* Loyalty Points Section */
.loyalty-points-section {
    margin-top: var(--spacing-5);
    padding: var(--spacing-4);
    background-color: rgba(22, 163, 74, 0.05);
    border-radius: var(--radius-lg);
    border: 1px dashed var(--success-300);
}

.loyalty-points-section h4 {
    color: var(--success-700);
    font-size: 1rem;
    margin-bottom: var(--spacing-3);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
}

.loyalty-points-section h4::before {
    content: "\f005";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    color: var(--success-500);
}

.current-points {
    display: flex;
    align-items: baseline;
    gap: var(--spacing-2);
    margin-bottom: var(--spacing-3);
}

.points-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--success-600);
}

.points-label {
    font-size: 1rem;
    color: var(--text-secondary);
}

.points-info {
    font-size: 0.85rem;
    color: var(--text-tertiary);
    margin-left: var(--spacing-2);
}

.use-points-form {
    margin-top: var(--spacing-3);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
}

.points-input-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.points-input-group label {
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.input-with-button {
    display: flex;
    gap: var(--spacing-2);
}

#points-to-use {
    flex: 1;
    padding: var(--spacing-2) var(--spacing-3);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 1rem;
    transition: all var(--transition-fast);
}

#points-to-use:focus {
    border-color: var(--success-500);
    outline: none;
    box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.2);
}

.btn-secondary {
    padding: var(--spacing-2) var(--spacing-3);
    background-color: var(--neutral-200);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.btn-secondary:hover {
    background-color: var(--neutral-300);
}

.btn-primary {
    padding: var(--spacing-3);
    background-color: var(--success-600);
    border: none;
    border-radius: var(--radius-md);
    font-size: 1rem;
    font-weight: 600;
    color: white;
    cursor: pointer;
    transition: all var(--transition-fast);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-2);
}

.btn-primary::before {
    content: "\f00c";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
}

.btn-primary:hover {
    background-color: var(--success-700);
    transform: translateY(-2px);
}

.no-points-message {
    margin-top: var(--spacing-3);
    padding: var(--spacing-3);
    background-color: rgba(255, 255, 255, 0.5);
    border-radius: var(--radius-md);
    border: 1px solid var(--neutral-200);
    color: var(--text-secondary);
    font-size: 0.9rem;
}

.discount-info {
    margin-top: var(--spacing-3);
    padding: var(--spacing-3);
    background-color: rgba(255, 255, 255, 0.5);
    border-radius: var(--radius-md);
    border: 1px solid var(--success-200);
}

.discount-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: var(--spacing-2);
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.discount-row:last-child {
    margin-bottom: 0;
    font-weight: 600;
    color: var(--success-700);
}

.total-amount, .loyalty-points {
    margin-top: var(--spacing-5);
    padding-top: var(--spacing-4);
    border-top: 1px solid var(--border-color);
}

.total-amount h4, .loyalty-points h4 {
    color: var(--text-primary);
    font-size: 1rem;
    margin-bottom: var(--spacing-2);
    font-weight: 600;
}

.amount-container {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-2);
}

.original-amount {
    font-size: 1rem;
    color: var(--text-tertiary);
    text-decoration: line-through;
    display: none;
}

.amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--accent-color);
    padding: var(--spacing-3);
    background-color: rgba(255, 87, 34, 0.1);
    border-radius: var(--radius-lg);
    display: inline-block;
    transition: all var(--transition-normal);
}

.points {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--success-600);
    padding: var(--spacing-2) var(--spacing-3);
    background-color: rgba(22, 163, 74, 0.1);
    border-radius: var(--radius-lg);
    display: inline-block;
}

.points-info {
    display: flex;
    align-items: center;
    gap: var(--spacing-2);
    margin-top: var(--spacing-2);
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.points-info i {
    color: var(--success-600);
}

/* Loyalty History */
.loyalty-history {
    margin-top: var(--spacing-5);
    padding-top: var(--spacing-4);
    border-top: 1px solid var(--border-color);
}

.loyalty-history h4 {
    color: var(--text-primary);
    font-size: 1rem;
    margin-bottom: var(--spacing-3);
    font-weight: 600;
}

.loyalty-history-table {
    overflow-x: auto;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
}

.loyalty-history-table table {
    width: 100%;
    border-collapse: collapse;
}

.loyalty-history-table th, 
.loyalty-history-table td {
    padding: var(--spacing-2) var(--spacing-3);
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.loyalty-history-table th {
    background-color: var(--bg-tertiary);
    font-weight: 600;
    color: var(--text-primary);
}

.loyalty-history-table tr:last-child td {
    border-bottom: none;
}

.loyalty-history-table .positive {
    color: var(--success-600);
}

.loyalty-history-table .negative {
    color: var(--error-600);
}

.payment-options {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-3);
    margin-top: var(--spacing-4);
}

.payment-option {
    position: relative;
}

.payment-option input[type="radio"] {
    position: absolute;
    opacity: 0;
}

.payment-option label {
    display: flex;
    flex-direction: column;
    padding: var(--spacing-4);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    cursor: pointer;
    transition: all var(--transition-normal);
    background-color: var(--bg-tertiary);
}

.payment-option input[type="radio"]:checked + label {
    border-color: var(--success-600);
    background-color: rgba(22, 163, 74, 0.1);
    box-shadow: var(--shadow-sm);
    transform: translateY(-2px);
}

.payment-logo {
    display: flex;
    align-items: center;
    gap: var(--spacing-3);
    font-weight: 600;
    color: var(--text-primary);
}

.payment-logo i {
    font-size: 1.5rem;
    color: var(--accent-color);
}

.payment-description {
    margin-top: var(--spacing-2);
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.payment-terms {
    margin: var(--spacing-5) 0;
    font-size: 0.85rem;
    color: var(--text-secondary);
    padding: var(--spacing-  0);
    font-size: 0.85rem;
    color: var(--text-secondary);
    padding: var(--spacing-3);
    border: 1px dashed var(--border-color);
    border-radius: var(--radius-md);
    background-color: var(--bg-tertiary);
}

.payment-terms a {
    color: var(--accent-color);
    text-decoration: none;
    font-weight: 500;
    transition: all var(--transition-fast);
}

.payment-terms a:hover {
    color: var(--accent-hover);
    text-decoration: underline;
}

#confirm-payment-btn {
    width: 100%;
    padding: var(--spacing-4);
    font-size: 1.1rem;
    margin-top: var(--spacing-4);
    background-color: var(--accent-color);
    border: none;
    border-radius: var(--radius-lg);
    color: white;
    cursor: pointer;
    transition: all var(--transition-normal);
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-2);
    box-shadow: var(--shadow-sm);
}

#confirm-payment-btn::before {
    content: "\f07a";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
}

#confirm-payment-btn:hover {
    background-color: var(--accent-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

#confirm-payment-btn:disabled {
    background-color: var(--neutral-400);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
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
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background-color: var(--bg-secondary);
    margin: 10% auto;
    padding: var(--spacing-5);
    border-radius: var(--radius-lg);
    width: 80%;
    max-width: 500px;
    box-shadow: var(--shadow-lg);
    animation: modalFadeIn 0.3s;
    border: 1px solid var(--border-color);
}

@keyframes modalFadeIn {
    from {opacity: 0; transform: translateY(-50px);}
    to {opacity: 1; transform: translateY(0);}
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: var(--spacing-3);
    margin-bottom: var(--spacing-4);
}

.modal-header h2 {
    color: var(--text-primary);
    font-size: 1.5rem;
    margin: 0;
    font-weight: 600;
}

.close {
    color: var(--text-tertiary);
    font-size: 1.75rem;
    font-weight: bold;
    cursor: pointer;
    transition: all var(--transition-fast);
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-full);
}

.close:hover {
    color: var(--text-primary);
    background-color: var(--hover-bg);
}

.modal-body {
    padding: var(--spacing-4) 0;
    text-align: center;
    color: var(--text-primary);
}

.success-icon {
    font-size: 4rem;
    color: var(--success-500);
    margin-bottom: var(--spacing-4);
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {transform: scale(0); opacity: 0;}
    to {transform: scale(1); opacity: 1;}
}

.modal-body p {
    color: var(--text-secondary);
    margin-bottom: var(--spacing-3);
    font-size: 1rem;
    line-height: 1.6;
}

.modal-footer {
    text-align: center;
    padding-top: var(--spacing-4);
    border-top: 1px solid var(--border-color);
    margin-top: var(--spacing-4);
}

.modal-footer .btn-primary {
    padding: var(--spacing-3) var(--spacing-5);
    background-color: var(--accent-color);
    color: white;
    border: none;
    border-radius: var(--radius-lg);
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    transition: all var(--transition-normal);
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-2);
    text-decoration: none;
}

.modal-footer .btn-primary::before {
    content: "\f145";
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
}

.modal-footer .btn-primary:hover {
    background-color: var(--accent-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

/* Loading overlay */
.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    z-index: 2000;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    backdrop-filter: blur(5px);
}

.loading-spinner {
    width: 60px;
    height: 60px;
    border: 5px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: var(--accent-color);
    animation: spin 1s ease-in-out infinite;
}

.loading-text {
    color: white;
    font-size: 1.2rem;
    margin-top: 20px;
    font-weight: 500;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .payment-container {
        flex-direction: column;
    }
    
    .modal-content {
        width: 95%;
        margin: 20% auto;
    }
    
    .amount, .points {
        font-size: 1.2rem;
        padding: var(--spacing-2);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lấy các phần tử DOM
    const paymentForm = document.getElementById('payment-form');
    const confirmPaymentBtn = document.getElementById('confirm-payment-btn');
    const modal = document.getElementById('payment-success-modal');
    const closeBtn = modal.querySelector('.close');
    const loadingOverlay = document.getElementById('loading-overlay');
    
    // Phần tử liên quan đến điểm tích lũy
    const pointsToUseInput = document.getElementById('points-to-use');
    const useMaxPointsBtn = document.getElementById('use-max-points');
    const applyPointsBtn = document.getElementById('apply-points');
    const discountInfo = document.querySelector('.discount-info');
    const usedPointsValue = document.getElementById('used-points-value');
    const discountAmount = document.getElementById('discount-amount');
    const originalAmount = document.getElementById('original-amount');
    const finalAmount = document.getElementById('final-amount');
    const finalAmountInput = document.getElementById('final-amount-input');
    const discountAmountInput = document.getElementById('discount-amount-input');
    const newPoints = document.getElementById('new-points');
    
    // Giá trị ban đầu
    const totalAmount = <?php echo $totalAmount; ?>;
    const totalLoyaltyPoints = <?php echo $totalLoyaltyPoints; ?>;
    const pointValue = <?php echo $pointValue; ?>;
    const maxDiscount = <?php echo $maxDiscount; ?>;
    
    // Xử lý sự kiện khi nhấn nút "Dùng tối đa"
    if (useMaxPointsBtn) {
        useMaxPointsBtn.addEventListener('click', function() {
            // Tính số điểm tối đa có thể sử dụng
            const maxPointsToUse = Math.min(totalLoyaltyPoints, maxDiscount / pointValue);
            pointsToUseInput.value = maxPointsToUse.toFixed();
        });
    }
    
    // Xử lý sự kiện khi nhấn nút "Áp dụng"
    if (applyPointsBtn) {
        applyPointsBtn.addEventListener('click', function() {
            const pointsToUse = parseFloat(pointsToUseInput.value) || 0;
            
            // Kiểm tra số điểm hợp lệ
            if (pointsToUse < 0) {
                alert('Số điểm không thể là số âm');
                return;
            }
            
            if (pointsToUse > totalLoyaltyPoints) {
                alert('Số điểm vượt quá số điểm hiện có');
                return;
            }
            
            // Tính toán số tiền giảm giá
            const discount = Math.min(pointsToUse * pointValue, totalAmount);
            const newFinalAmount = totalAmount - discount;
            
            // Cập nhật giao diện
            usedPointsValue.textContent = pointsToUse.toFixed();
            discountAmount.textContent = discount.toLocaleString() + ' VNĐ';
            
            if (discount > 0) {
                originalAmount.style.display = 'block';
                originalAmount.textContent = totalAmount.toLocaleString() + ' VNĐ';
            } else {
                originalAmount.style.display = 'none';
            }
            
            finalAmount.textContent = newFinalAmount.toLocaleString() + ' VNĐ';
            
            // Cập nhật giá trị input ẩn
            finalAmountInput.value = newFinalAmount;
            discountAmountInput.value = discount;
            
            // Điểm tích lũy luôn dựa trên tổng tiền ban đầu, không thay đổi khi áp dụng điểm
            const newLoyaltyPoints = totalAmount * 0.00003;
            newPoints.textContent = newLoyaltyPoints.toFixed() + ' điểm';
            
            // Hiển thị thông tin giảm giá
            discountInfo.style.display = 'block';
        });
    }
    
    // Xử lý sự kiện khi submit form
    paymentForm.addEventListener('submit', function(e) {
        // Hiển thị overlay loading
        loadingOverlay.style.display = 'flex';
        
        // Vô hiệu hóa nút để tránh nhấn nhiều lần
        confirmPaymentBtn.disabled = true;
        
        // Form sẽ tự submit, không cần ngăn chặn
    });
    
    // Kiểm tra nếu có thông báo thành công từ server
    <?php if (isset($_SESSION['payment_success']) && $_SESSION['payment_success']): ?>
    // Hiển thị modal thành công
    modal.style.display = 'block';
    // Xóa thông báo thành công
    <?php unset($_SESSION['payment_success']); ?>
    <?php endif; ?>
    
    // Đóng modal khi nhấn nút đóng
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        window.location.href = '<?php echo BASE_URL; ?>/tickets/history';
    });
    
    // Đóng modal khi nhấn bên ngoài modal
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
            window.location.href = '<?php echo BASE_URL; ?>/tickets/history';
        }
    });

    // Add payment method handling
    const paymentMethodRadios = document.querySelectorAll('input[name="paymentMethod"]');

    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const paymentMethod = this.value;
            
            // Update form action based on selected payment method
            if (paymentMethod === 'VNPAY') {
                paymentForm.action = '<?php echo BASE_URL; ?>/vnpay/process';
            } else if (paymentMethod === 'MOMO') {
                paymentForm.action = '<?php echo BASE_URL; ?>/momo-payment/process';
            } else {
                // thẻ ngân hàng
                paymentForm.action = '<?php echo BASE_URL; ?>/booking/process-standard-payment';


            }
        });
    });
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>
