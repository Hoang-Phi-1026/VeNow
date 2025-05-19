<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/points.css">

<div class="container mt-4">
    <div class="page-header">
        <h1>Điểm tích lũy của bạn</h1>
    </div>
    
    <div class="row">
        <!-- Thẻ điểm tích lũy -->
        <div class="col-md-6 mb-4">
            <div class="card loyalty-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Thẻ thành viên</h5>
                        <span class="badge bg-light text-dark"><?= $user['ho_ten'] ?></span>
                    </div>
                    <div class="points-display">
                        <h2><?= number_format($totalPoints, 2) ?></h2>
                        <p>Điểm tích lũy</p>
                    </div>
                    <div class="points-value">
                        <p>Tương đương: <strong><?= number_format($totalPoints * 1000) ?> VNĐ</strong></p>
                    </div>
                    <div class="card-actions">
                        <a href="<?= BASE_URL ?>/points/history" class="btn">
                            <i class="fas fa-history"></i> Xem lịch sử
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Thống kê điểm -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Thống kê điểm</h5>
                    <div class="stats-container">
                        <div class="stat-item">
                            <span class="stat-label">Tổng điểm đã tích lũy:</span>
                            <span class="stat-value"><?= number_format($pointsStats['total_earned'], 2) ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Tổng điểm đã sử dụng:</span>
                            <span class="stat-value"><?= number_format($pointsStats['total_used'], 2) ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Điểm tích lũy tháng này:</span>
                            <span class="stat-value"><?= number_format($pointsStats['this_month_earned'], 2) ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Điểm sử dụng tháng này:</span>
                            <span class="stat-value"><?= number_format($pointsStats['this_month_used'], 2) ?></span>
                        </div>
                        <?php if ($pointsStats['last_transaction_date']): ?>
                        <div class="stat-item">
                            <span class="stat-label">Giao dịch gần nhất:</span>
                            <span class="stat-value"><?= date('d/m/Y H:i', strtotime($pointsStats['last_transaction_date'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Giao dịch gần đây -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title">Giao dịch gần đây</h5>
                        <a href="<?= BASE_URL ?>/points/history" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt"></i> Xem tất cả
                        </a>
                    </div>
                    
                    <?php if (empty($recentTransactions)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Bạn chưa có giao dịch điểm tích lũy nào.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Loại</th>
                                        <th>Điểm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $transaction): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($transaction['ngay_nhan'])) ?></td>
                                            <td>
                                                <?php if ($transaction['nguon'] == 'MUA_VE'): ?>
                                                    <i class="fas fa-ticket-alt me-1"></i>
                                                <?php elseif ($transaction['nguon'] == 'HOAN_VE'): ?>
                                                    <i class="fas fa-undo me-1"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-gift me-1"></i>
                                                <?php endif; ?>
                                                <?= $transaction['nguon_text'] ?>
                                            </td>
                                            <td class="<?= $transaction['so_diem'] > 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= $transaction['so_diem'] > 0 ? '+' : '' ?><?= number_format($transaction['so_diem'], 2) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Thông tin về điểm -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Thông tin điểm tích lũy</h5>
                    <div class="info-container">
                        <div class="info-item">
                            <i class="fas fa-info-circle"></i>
                            <p>Mỗi 1 điểm tích lũy tương đương với 1,000 VNĐ khi thanh toán.</p>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-ticket-alt"></i>
                            <p>Bạn nhận được điểm tích lũy khi mua vé sự kiện (2% giá trị đơn hàng).</p>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-gift"></i>
                            <p>Sử dụng điểm tích lũy để giảm giá khi mua vé hoặc đổi các ưu đãi đặc biệt.</p>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-calendar-alt"></i>
                            <p>Điểm tích lũy có hiệu lực trong vòng 12 tháng kể từ ngày nhận.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($availableRewards)): ?>
    <div class="row">
        <!-- Ưu đãi có thể đổi -->
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Ưu đãi có thể đổi</h5>
                    <div class="row">
                        <?php foreach ($availableRewards as $reward): ?>
                            <div class="col-md-4 mb-3">
                                <div class="reward-card">
                                    <h6><?= $reward['ten_uu_dai'] ?></h6>
                                    <p><?= $reward['mo_ta'] ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-success"><?= $reward['phan_tram_giam'] ?>% giảm</span>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="fas fa-exchange-alt me-1"></i> Đổi ngay
                                        </button>
                                    </div>
                                    <small class="text-muted">Hết hạn: <?= date('d/m/Y', strtotime($reward['ngay_ket_thuc'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
