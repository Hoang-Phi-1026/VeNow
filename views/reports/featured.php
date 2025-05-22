<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/admin.css">

    <main class="container">
        <section class="report-section">
            <h1>Báo cáo sự kiện nổi bật</h1>
            
            <!-- Filter Form -->
            <div class="filter-form">
                <form action="<?= BASE_URL ?>/reports/featured" method="GET">
                    <div class="form-group">
                        <label for="start_date">Từ ngày:</label>
                        <input type="date" id="start_date" name="start_date" value="<?= $startDate ?>">
                    </div>
                    <div class="form-group">
                        <label for="end_date">Đến ngày:</label>
                        <input type="date" id="end_date" name="end_date" value="<?= $endDate ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Lọc</button>
                        <a href="<?= BASE_URL ?>/reports/featured/export-csv?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-secondary">
                            <i class="fas fa-download"></i> Xuất CSV
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Overview Stats -->
            <div class="stats-overview">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng số sự kiện</h3>
                        <p class="stat-value"><?= number_format($featuredStats['total_events']) ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng doanh thu</h3>
                        <p class="stat-value"><?= number_format($featuredStats['total_revenue']) ?> VNĐ</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Tổng số vé bán</h3>
                        <p class="stat-value"><?= number_format($featuredStats['total_tickets']) ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Đánh giá trung bình</h3>
                        <p class="stat-value"><?= number_format($featuredStats['avg_rating'], 1) ?>/5</p>
                    </div>
                </div>
            </div>
            
            <!-- Revenue Chart -->
            <div class="chart-container">
                <h2>Biểu đồ doanh thu theo tháng</h2>
                <canvas id="revenueChart"></canvas>
            </div>
            
            <!-- Top Revenue Events -->
            <div class="top-events">
                <h2>Top sự kiện có doanh thu cao nhất</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên sự kiện</th>
                                <th>Loại</th>
                                <th>Ngày diễn ra</th>
                                <th>Doanh thu</th>
                                <th>Số vé bán</th>
                                <th>Số người tham dự</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topRevenue as $event): ?>
                                <tr>
                                    <td><?= $event['ma_su_kien'] ?></td>
                                    <td><?= $event['ten_su_kien'] ?></td>
                                    <td><?= $event['loai_su_kien'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($event['ngay_dien_ra'])) ?></td>
                                    <td><?= number_format($event['total_revenue']) ?> VNĐ</td>
                                    <td><?= number_format($event['total_transactions']) ?></td>
                                    <td><?= number_format($event['total_attendees']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Top Rated Events -->
            <div class="top-events">
                <h2>Top sự kiện được đánh giá cao nhất</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên sự kiện</th>
                                <th>Loại</th>
                                <th>Ngày diễn ra</th>
                                <th>Đánh giá</th>
                                <th>Số lượt đánh giá</th>
                                <th>Doanh thu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($topRated as $event): ?>
                                <tr>
                                    <td><?= $event['ma_su_kien'] ?></td>
                                    <td><?= $event['ten_su_kien'] ?></td>
                                    <td><?= $event['loai_su_kien'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($event['ngay_dien_ra'])) ?></td>
                                    <td>
                                        <div class="rating">
                                            <?= number_format($event['avg_rating'], 1) ?>/5
                                            <div class="stars">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $event['avg_rating']): ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php elseif ($i - 0.5 <= $event['avg_rating']): ?>
                                                        <i class="fas fa-star-half-alt"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= number_format($event['rating_count']) ?></td>
                                    <td><?= number_format($event['total_revenue']) ?> VNĐ</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Trending Events -->
            <div class="top-events">
                <h2>Sự kiện đang thịnh hành</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên sự kiện</th>
                                <th>Loại</th>
                                <th>Ngày diễn ra</th>
                                <th>Lượt xem</th>
                                <th>Lượt đặt vé</th>
                                <th>Tỷ lệ chuyển đổi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trending as $event): ?>
                                <tr>
                                    <td><?= $event['ma_su_kien'] ?></td>
                                    <td><?= $event['ten_su_kien'] ?></td>
                                    <td><?= $event['loai_su_kien'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($event['ngay_dien_ra'])) ?></td>
                                    <td><?= number_format($event['view_count']) ?></td>
                                    <td><?= number_format($event['booking_count']) ?></td>
                                    <td><?= number_format($event['conversion_rate'], 2) ?>%</td>
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
        // Revenue Chart
        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= $chartLabels ?>,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: <?= $chartData ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
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
