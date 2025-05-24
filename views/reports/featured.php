
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo sự kiện nổi bật</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .report-section {
            padding: 2rem 0;
        }
        .filter-form .form-group {
            margin-right: 1rem;
        }
        .stats-overview {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 2rem;
        }
        .stat-card {
            flex: 1 1 220px;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .stat-icon {
            font-size: 2rem;
            color: #17a2b8;
            margin-right: 1rem;
        }
        .chart-container {
            margin-top: 3rem;
        }
        .top-events {
            margin-top: 3rem;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th, .data-table td {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
        }
        .data-table th {
            background-color: #f1f1f1;
        }
        .rating .stars i {
            color: #ffc107;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="container report-section">
    <h1 class="text-center mb-4">Báo cáo sự kiện nổi bật</h1>

    <!-- Filter Form -->
    <form action="<?= BASE_URL ?>/reports/featured" method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label for="start_date" class="form-label">Từ ngày</label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?= $startDate ?>">
        </div>
        <div class="col-md-3">
            <label for="end_date" class="form-label">Đến ngày</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?= $endDate ?>">
        </div>
        <div class="col-md-6">
            <button type="submit" class="btn btn-primary me-2">Lọc</button>
            <a href="<?= BASE_URL ?>/reports/featured/export-csv?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-outline-secondary">
                <i class="fas fa-download"></i> Xuất CSV
            </a>
        </div>
    </form>

    <!-- Stats Overview -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
            <div>
                <h5>Tổng số sự kiện</h5>
                <p><?= number_format($featuredStats['total_events'] ?? 0) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div>
                <h5>Tổng doanh thu</h5>
                <p><?= number_format($featuredStats['total_revenue'] ?? 0) ?> VNĐ</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
            <div>
                <h5>Tổng số vé bán</h5>
                <p><?= number_format($featuredStats['total_tickets'] ?? 0) ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-star"></i></div>
            <div>
                <h5>Đánh giá trung bình</h5>
                <p><?= $featuredStats['avg_rating'] ?? '0.0' ?>/5</p>
            </div>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="chart-container">
        <h3 class="mb-3">Biểu đồ doanh thu theo tháng</h3>
        <canvas id="revenueChart"></canvas>
    </div>

    <!-- Tables -->
    <?php
        $tables = [
            ['title' => 'Top sự kiện có doanh thu cao nhất', 'data' => $topRevenue, 'columns' => ['ID', 'Tên sự kiện', 'Loại', 'Ngày diễn ra', 'Doanh thu', 'Số vé bán', 'Số người tham dự']],
            ['title' => 'Top sự kiện được đánh giá cao nhất', 'data' => $topRated, 'columns' => ['ID', 'Tên sự kiện', 'Loại', 'Ngày diễn ra', 'Đánh giá', 'Số lượt đánh giá']],
    
        ];
    ?>
    <?php foreach ($tables as $table): ?>
    <div class="top-events">
        <h3 class="mb-3"><?= $table['title'] ?></h3>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <?php foreach ($table['columns'] as $col): ?>
                            <th><?= $col ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($table['data'] as $event): ?>
                        <tr>
                            <?php foreach ($event as $value): ?>
                                <td><?= htmlspecialchars(is_numeric($value) ? number_format($value) : $value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= $chartLabels ?>,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: <?= $chartData ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
</body>
</html>