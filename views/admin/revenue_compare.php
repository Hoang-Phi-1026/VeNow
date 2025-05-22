<?php
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="admin-container">
    <div class="admin-header">
        <h1>So sánh doanh thu</h1>
        <p>So sánh doanh thu giữa hai khoảng thời gian</p>
    </div>

    <!-- Form lọc -->
    <div class="filter-section">
        <form method="GET" action="<?= BASE_URL ?>/admin/compare-revenue" class="filter-form">
            <div class="filter-row">
                <div class="filter-group">
                    <label>Khoảng thời gian 1:</label>
                    <input type="date" name="period1_start" value="<?= $period1Start ?>" required>
                    <span>đến</span>
                    <input type="date" name="period1_end" value="<?= $period1End ?>" required>
                </div>
                
                <div class="filter-group">
                    <label>Khoảng thời gian 2:</label>
                    <input type="date" name="period2_start" value="<?= $period2Start ?>" required>
                    <span>đến</span>
                    <input type="date" name="period2_end" value="<?= $period2End ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary">So sánh</button>
            </div>
        </form>
    </div>

    <!-- Tổng quan so sánh -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3>Khoảng thời gian 1</h3>
                <p class="stat-value"><?= number_format($period1Revenue, 0, ',', '.') ?> VNĐ</p>
                <p class="stat-label"><?= date('d/m/Y', strtotime($period1Start)) ?> - <?= date('d/m/Y', strtotime($period1End)) ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-content">
                <h3>Khoảng thời gian 2</h3>
                <p class="stat-value"><?= number_format($period2Revenue, 0, ',', '.') ?> VNĐ</p>
                <p class="stat-label"><?= date('d/m/Y', strtotime($period2Start)) ?> - <?= date('d/m/Y', strtotime($period2End)) ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><?= $growthRate >= 0 ? '📈' : '📉' ?></div>
            <div class="stat-content">
                <h3>Tỷ lệ tăng trưởng</h3>
                <p class="stat-value <?= $growthRate >= 0 ? 'positive' : 'negative' ?>">
                    <?= $growthRate >= 0 ? '+' : '' ?><?= number_format($growthRate, 2) ?>%
                </p>
                <p class="stat-label">
                    <?= $growthRate >= 0 ? 'Tăng' : 'Giảm' ?> 
                    <?= number_format(abs($period2Revenue - $period1Revenue), 0, ',', '.') ?> VNĐ
                </p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-content">
                <h3>Chênh lệch</h3>
                <p class="stat-value"><?= number_format(abs($period2Revenue - $period1Revenue), 0, ',', '.') ?> VNĐ</p>
                <p class="stat-label">Giá trị tuyệt đối</p>
            </div>
        </div>
    </div>

    <!-- Biểu đồ so sánh -->
    <div class="chart-section">
        <div class="chart-container">
            <h3>So sánh doanh thu tổng</h3>
            <canvas id="revenueComparisonChart"></canvas>
        </div>
    </div>

    <!-- So sánh theo loại sự kiện -->
    <div class="comparison-section">
        <h3>So sánh doanh thu theo loại sự kiện</h3>
        <div class="comparison-table">
            <table>
                <thead>
                    <tr>
                        <th>Loại sự kiện</th>
                        <th>Khoảng 1 (VNĐ)</th>
                        <th>Khoảng 2 (VNĐ)</th>
                        <th>Chênh lệch</th>
                        <th>Tỷ lệ (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Tạo danh sách tất cả loại sự kiện
                    $allEventTypes = array_unique(array_merge(
                        array_keys($period1EventTypeRevenue),
                        array_keys($period2EventTypeRevenue)
                    ));
                    
                    foreach ($allEventTypes as $eventType):
                        $period1Value = $period1EventTypeRevenue[$eventType] ?? 0;
                        $period2Value = $period2EventTypeRevenue[$eventType] ?? 0;
                        $difference = $period2Value - $period1Value;
                        $percentage = $period1Value > 0 ? (($difference / $period1Value) * 100) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($eventType) ?></td>
                        <td><?= number_format($period1Value, 0, ',', '.') ?></td>
                        <td><?= number_format($period2Value, 0, ',', '.') ?></td>
                        <td class="<?= $difference >= 0 ? 'positive' : 'negative' ?>">
                            <?= $difference >= 0 ? '+' : '' ?><?= number_format($difference, 0, ',', '.') ?>
                        </td>
                        <td class="<?= $percentage >= 0 ? 'positive' : 'negative' ?>">
                            <?= $percentage >= 0 ? '+' : '' ?><?= number_format($percentage, 2) ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- So sánh theo phương thức thanh toán -->
    <div class="comparison-section">
        <h3>So sánh doanh thu theo phương thức thanh toán</h3>
        <div class="comparison-table">
            <table>
                <thead>
                    <tr>
                        <th>Phương thức</th>
                        <th>Khoảng 1 (VNĐ)</th>
                        <th>Khoảng 2 (VNĐ)</th>
                        <th>Chênh lệch</th>
                        <th>Tỷ lệ (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Tạo danh sách tất cả phương thức thanh toán
                    $allPaymentMethods = array_unique(array_merge(
                        array_keys($period1PaymentMethodRevenue),
                        array_keys($period2PaymentMethodRevenue)
                    ));
                    
                    foreach ($allPaymentMethods as $paymentMethod):
                        $period1Value = $period1PaymentMethodRevenue[$paymentMethod] ?? 0;
                        $period2Value = $period2PaymentMethodRevenue[$paymentMethod] ?? 0;
                        $difference = $period2Value - $period1Value;
                        $percentage = $period1Value > 0 ? (($difference / $period1Value) * 100) : 0;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($paymentMethod) ?></td>
                        <td><?= number_format($period1Value, 0, ',', '.') ?></td>
                        <td><?= number_format($period2Value, 0, ',', '.') ?></td>
                        <td class="<?= $difference >= 0 ? 'positive' : 'negative' ?>">
                            <?= $difference >= 0 ? '+' : '' ?><?= number_format($difference, 0, ',', '.') ?>
                        </td>
                        <td class="<?= $percentage >= 0 ? 'positive' : 'negative' ?>">
                            <?= $percentage >= 0 ? '+' : '' ?><?= number_format($percentage, 2) ?>%
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Nút quay lại -->
    <div class="action-buttons">
        <a href="<?= BASE_URL ?>/admin/revenue" class="btn btn-secondary">← Quay lại báo cáo doanh thu</a>
    </div>
</div>

<script>
// Biểu đồ so sánh doanh thu
const ctx = document.getElementById('revenueComparisonChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Khoảng thời gian 1', 'Khoảng thời gian 2'],
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: [<?= $period1Revenue ?>, <?= $period2Revenue ?>],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'So sánh doanh thu giữa hai khoảng thời gian'
            },
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + ' VNĐ';
                    }
                }
            }
        }
    }
});
</script>

<style>
.comparison-section {
    margin: 30px 0;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.comparison-table table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.comparison-table th,
.comparison-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.comparison-table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.comparison-table .positive {
    color: #28a745;
    font-weight: 600;
}

.comparison-table .negative {
    color: #dc3545;
    font-weight: 600;
}

.chart-section {
    margin: 30px 0;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chart-container {
    position: relative;
    height: 400px;
}

.action-buttons {
    margin-top: 30px;
    text-align: center;
}

.filter-row {
    display: flex;
    gap: 20px;
    align-items: end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-weight: 600;
    color: #333;
}

.filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.filter-group span {
    align-self: center;
    margin: 0 10px;
    font-weight: 500;
}

.stats-grid .stat-value.positive {
    color: #28a745;
}

.stats-grid .stat-value.negative {
    color: #dc3545;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
