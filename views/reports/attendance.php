
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo cáo tham dự sự kiện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', sans-serif;
        }
        .card-stat {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .card-stat i {
            font-size: 2rem;
            color: #0d6efd;
        }
        .data-table {
            width: 100%;
        }
        .data-table th, .data-table td {
            padding: 0.75rem;
            vertical-align: middle;
        }
        h2.section-title {
            margin-top: 2rem;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 0.5rem;
        }
    </style>
</head>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<body>

<div class="container py-5">
    <h1 class="mb-4 fw-bold text-primary">📊 Báo cáo tham dự sự kiện</h1>

    <!-- Bộ lọc -->
    <form class="row g-3 bg-white p-4 rounded shadow-sm mb-5" action="<?= BASE_URL ?>/reports/attendance" method="GET">
        <div class="col-md-3">
            <label class="form-label">Từ ngày</label>
            <input type="date" class="form-control" name="start_date" value="<?= $startDate ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Đến ngày</label>
            <input type="date" class="form-control" name="end_date" value="<?= $endDate ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Loại sự kiện</label>
            <select class="form-select" name="event_type">
                <option value="">Tất cả</option>
                <?php foreach ($eventTypes as $type): ?>
                    <option value="<?= $type['maloaisukien'] ?>" <?= $eventType == $type['maloaisukien'] ? 'selected' : '' ?>>
                        <?= $type['tenloaisukien'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button class="btn btn-primary w-50" type="submit">Lọc</button>
            <a class="btn btn-outline-secondary w-50" href="<?= BASE_URL ?>/reports/attendance/export-csv?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&event_type=<?= $eventType ?>">
                <i class="fas fa-download me-1"></i> CSV
            </a>
        </div>
    </form>

    <!-- Thống kê -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card card-stat shadow-sm p-3">
                <i class="fas fa-users text-primary"></i>
                <div>
                    <div class="text-muted">Tổng tham dự</div>
                    <div class="fs-5 fw-bold"><?= number_format($totalAttendance['total_attendees'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm p-3">
                <i class="fas fa-ticket-alt text-success"></i>
                <div>
                    <div class="text-muted">Tổng giao dịch</div>
                    <div class="fs-5 fw-bold"><?= number_format($totalAttendance['total_transactions'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm p-3">
                <i class="fas fa-money-bill-wave text-warning"></i>
                <div>
                    <div class="text-muted">Doanh thu</div>
                    <div class="fs-5 fw-bold"><?= number_format($totalAttendance['total_revenue'] ?? 0) ?> VNĐ</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stat shadow-sm p-3">
                <i class="fas fa-chart-line text-danger"></i>
                <div>
                    <div class="text-muted">TB/giao dịch</div>
                    <div class="fs-5 fw-bold"><?= number_format($totalAttendance['avg_transaction'] ?? 0) ?> VNĐ</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ -->
    <div class="bg-white rounded shadow-sm p-4 mb-5">
        <h2 class="section-title">Biểu đồ tham dự theo tháng</h2>
        <canvas id="attendanceChart" height="100"></canvas>
    </div>

    <!-- Top sự kiện -->
    <div class="bg-white rounded shadow-sm p-4 mb-5">
        <h2 class="section-title">Top sự kiện có nhiều người tham dự</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sự kiện</th>
                        <th>Loại</th>
                        <th>Ngày diễn ra</th>
                        <th>Người tham dự</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topEvents as $event): ?>
                        <tr>
                            <td><?= $event['ma_su_kien'] ?? 'N/A' ?></td>
                            <td><?= htmlspecialchars($event['ten_su_kien'] ?? 'Không xác định') ?></td>
                            <td><?= htmlspecialchars($event['loai_su_kien'] ?? 'Không xác định') ?></td>
                            <td><?= isset($event['ngay_dien_ra']) ? date('d/m/Y', strtotime($event['ngay_dien_ra'])) : 'N/A' ?></td>
                            <td><?= number_format($event['total_attendees'] ?? 0) ?></td>
                            <td><?= number_format($event['total_revenue'] ?? 0) ?> VNĐ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Thống kê theo loại -->
    <div class="bg-white rounded shadow-sm p-4 mb-5">
        <h2 class="section-title">Thống kê theo loại sự kiện</h2>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Loại sự kiện</th>
                        <th>Số sự kiện</th>
                        <th>Người tham dự</th>
                        <th>Tỷ lệ</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendanceByEventType as $type): ?>
                        <tr>
                            <td><?= $type['loai_su_kien'] ?? 'Không xác định' ?></td>
                            <td><?= number_format($type['event_count'] ?? 0) ?></td>
                            <td><?= number_format($type['total_attendees'] ?? 0) ?></td>
                            <td><?= number_format($type['percentage'] ?? 0, 1) ?>%</td>
                            <td><?= number_format($type['total_revenue'] ?? 0) ?> VNĐ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chi tiết -->
    <div class="bg-white rounded shadow-sm p-4 mb-5">
        <h2 class="section-title">Chi tiết từng sự kiện</h2>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sự kiện</th>
                        <th>Loại</th>
                        <th>Ngày diễn ra</th>
                        <th>Tham dự</th>
                        <th>Giao dịch</th>
                        <th>Doanh thu</th>
                        <th>TB/giao dịch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendanceDetails as $detail): ?>
                        <tr>
                            <td><?= $detail['ma_su_kien'] ?? 'N/A' ?></td>
                            <td><?= $detail['ten_su_kien'] ?? 'Không xác định' ?></td>
                            <td><?= $detail['loai_su_kien'] ?? 'Không xác định' ?></td>
                            <td><?= isset($detail['ngay_dien_ra']) ? date('d/m/Y', strtotime($detail['ngay_dien_ra'])) : 'N/A' ?></td>
                            <td><?= number_format($detail['attendees'] ?? 0) ?></td>
                            <td><?= number_format($detail['transactions'] ?? 0) ?></td>
                            <td><?= number_format($detail['revenue'] ?? 0) ?> VNĐ</td>
                            <td><?= number_format($detail['avg_transaction'] ?? 0) ?> VNĐ</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= $chartLabels ?>,
            datasets: [{
                label: 'Số người tham dự',
                data: <?= $chartData ?>,
                backgroundColor: 'rgba(13, 110, 253, 0.5)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>
    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>
<script src="https://kit.fontawesome.com/a2d9a66b56.js" crossorigin="anonymous"></script>
</body>
</html>
