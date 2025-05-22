
<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/admin.css">

    <main class="container">
        <section class="report-section">
            <h1>Báo cáo tham dự sự kiện</h1>
            
            <!-- Filter Form -->
            <div class="filter-form">
                <form action="<?= BASE_URL ?>/reports/attendance" method="GET">
                    <div class="form-group">
                        <label for="start_date">Từ ngày:</label>
                        <input type="date" id="start_date" name="start_date" value="<?= $startDate ?>">
                    </div>
                    <div class="form-group">
                        <label for="end_date">Đến ngày:</label>
                        <input type="date" id="end_date" name="end_date" value="<?= $endDate ?>">
                    </div>
                    <div class="form-group">
                        <label for="event_type">Loại sự kiện:</label>
                        <select id="event_type" name="event_type">
                            <option value="">Tất cả</option>
                            <?php foreach ($eventTypes as $type): ?>
                                <option value="<?= $type['ma_loai_su_kien'] ?>" <?= $eventType == $type['ma_loai_su_kien'] ? 'selected' : '' ?>>
                                    <?= $type['ten_loai_su_kien'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="<?= BASE_URL ?>/reports/attendance/export-csv?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>&event_type=<?= $eventType ?>" class="btn btn-secondary">
                            <i class="fas fa-download"></i> Xuất CSV
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Overview Stats -->
            <div class="stats-overview">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng số người tham dự</h3>
                        <p class="stat-value"><?= number_format($totalAttendance['total_attendees']) ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng số giao dịch</h3>
                        <p class="stat-value"><?= number_format($totalAttendance['total_transactions']) ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng doanh thu</h3>
                        <p class="stat-value"><?= number_format($totalAttendance['total_revenue']) ?> VNĐ</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Trung bình/giao dịch</h3>
                        <p class="stat-value"><?= number_format($totalAttendance['avg_transaction']) ?> VNĐ</p>
                    </div>
                </div>
            </div>
            
            <!-- Attendance Chart -->
            <div class="chart-container">
                <h2>Biểu đồ tham dự theo tháng</h2>
                <canvas id="attendanceChart"></canvas>
            </div>
            
            <!-- Top Events -->
            <div class="top-events">
                <h2>Top sự kiện có nhiều người tham dự nhất</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên sự kiện</th>
                                <th>Loại</th>
                                <th>Ngày diễn ra</th>
                                <th>Số người tham dự</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topEvents as $event): ?>
                                <tr>
                                    <td><?= $event['ma_su_kien'] ?></td>
                                    <td><?= $event['ten_su_kien'] ?></td>
                                    <td><?= $event['loai_su_kien'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($event['ngay_dien_ra'])) ?></td>
                                    <td><?= number_format($event['total_attendees']) ?></td>
                                    <td><?= number_format($event['total_revenue']) ?> VNĐ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Attendance by Event Type -->
            <div class="event-type-stats">
                <h2>Tham dự theo loại sự kiện</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Loại sự kiện</th>
                                <th>Số sự kiện</th>
                                <th>Số người tham dự</th>
                                <th>Tỷ lệ</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceByEventType as $type): ?>
                                <tr>
                                    <td><?= $type['loai_su_kien'] ?></td>
                                    <td><?= number_format($type['event_count']) ?></td>
                                    <td><?= number_format($type['total_attendees']) ?></td>
                                    <td><?= number_format($type['percentage'], 1) ?>%</td>
                                    <td><?= number_format($type['total_revenue']) ?> VNĐ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Detailed Attendance -->
            <div class="detailed-attendance">
                <h2>Chi tiết tham dự sự kiện</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên sự kiện</th>
                                <th>Loại</th>
                                <th>Ngày diễn ra</th>
                                <th>Số người tham dự</th>
                                <th>Số giao dịch</th>
                                <th>Doanh thu</th>
                                <th>TB/giao dịch</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceDetails as $detail): ?>
                                <tr>
                                    <td><?= $detail['ma_su_kien'] ?></td>
                                    <td><?= $detail['ten_su_kien'] ?></td>
                                    <td><?= $detail['loai_su_kien'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($detail['ngay_dien_ra'])) ?></td>
                                    <td><?= number_format($detail['attendees']) ?></td>
                                    <td><?= number_format($detail['transactions']) ?></td>
                                    <td><?= number_format($detail['revenue']) ?> VNĐ</td>
                                    <td><?= number_format($detail['avg_transaction']) ?> VNĐ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script>
        // Attendance Chart
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $chartLabels ?>,
                datasets: [{
                    label: 'Số người tham dự',
                    data: <?= $chartData ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
