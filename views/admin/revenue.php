<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/revenue.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container revenue-dashboard">
    <h1 class="page-title">Thống kê doanh thu</h1>
    
    <!-- Filter Form -->
    <div class="filter-section">
        <form action="" method="GET" class="filter-form">
            <div class="form-group">
                <label for="start_date">Từ ngày:</label>
                <input type="date" id="start_date" name="start_date" value="<?= $startDate ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="end_date">Đến ngày:</label>
                <input type="date" id="end_date" name="end_date" value="<?= $endDate ?? '' ?>">
            </div>
            <div class="form-group">
                <label for="year">Năm (cho biểu đồ tháng):</label>
                <select id="year" name="year">
                    <?php for ($y = date('Y'); $y >= date('Y') - 5; $y--): ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="<?= BASE_URL ?>/admin/revenue" class="btn btn-secondary">Đặt lại</a>
                <a href="<?= BASE_URL ?>/admin/revenue/export-csv?start_date=<?= $startDate ?? '' ?>&end_date=<?= $endDate ?? '' ?>" class="btn btn-success">Xuất CSV</a>
                <a href="<?= BASE_URL ?>/admin/revenue/compare?period1_start=<?= date('Y-m-d', strtotime('-60 days')) ?>&period1_end=<?= date('Y-m-d', strtotime('-31 days')) ?>&period2_start=<?= date('Y-m-d', strtotime('-30 days')) ?>&period2_end=<?= date('Y-m-d') ?>" class="btn btn-info">So sánh doanh thu</a>
            </div>
        </form>
    </div>
    
    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tổng doanh thu</h5>
                <p class="card-value"><?= number_format($totalRevenue, 0, ',', '.') ?> VNĐ</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Tổng số giao dịch</h5>
                <p class="card-value"><?= $totalTickets ?></p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Doanh thu trung bình/giao dịch</h5>
                <p class="card-value"><?= number_format($averageTicketPrice, 0, ',', '.') ?> VNĐ</p>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Số sự kiện có doanh thu</h5>
                <p class="card-value"><?= $totalEvents ?></p>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="charts-section">
        <!-- Daily Revenue Chart -->
        <div class="chart-container">
            <h3>Doanh thu theo ngày</h3>
            <canvas id="dailyRevenueChart"></canvas>
        </div>
        
        <!-- Monthly Revenue Chart -->
        <div class="chart-container">
            <h3>Doanh thu theo tháng (<?= $year ?>)</h3>
            <canvas id="monthlyRevenueChart"></canvas>
        </div>
        
        <!-- Quarterly Revenue Chart -->
        <div class="chart-container">
            <h3>Doanh thu theo quý (<?= $year ?>)</h3>
            <canvas id="quarterlyRevenueChart"></canvas>
        </div>
        
        <!-- Yearly Revenue Chart -->
        <div class="chart-container">
            <h3>Doanh thu theo năm</h3>
            <canvas id="yearlyRevenueChart"></canvas>
        </div>
        
        <!-- Payment Method Chart -->
        <div class="chart-container">
            <h3>Doanh thu theo phương thức thanh toán</h3>
            <canvas id="paymentMethodChart"></canvas>
        </div>
        
        <!-- Event Type Chart -->
        <div class="chart-container">
            <h3>Doanh thu theo loại sự kiện</h3>
            <canvas id="eventTypeChart"></canvas>
        </div>
        
        <!-- Ticket Type Chart -->
        <div class="chart-container">
            <h3>Số lượng giao dịch theo loại vé</h3>
            <canvas id="ticketTypeChart"></canvas>
        </div>
    </div>
    
    <!-- Top Events -->
    <div class="top-events-section">
        <h3>Top sự kiện có doanh thu cao nhất</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tên sự kiện</th>
                        <th>Số giao dịch</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revenueByEvent as $event): ?>
                    <tr>
                        <td><?= $event['ten_su_kien'] ?></td>
                        <td><?= $event['ticket_count'] ?></td>
                        <td><?= number_format($event['total'], 0, ',', '.') ?> VNĐ</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Top Organizers -->
    <div class="top-organizers-section">
        <h3>Doanh thu theo nhà tổ chức</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nhà tổ chức</th>
                        <th>Số sự kiện</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($revenueByOrganizer as $organizer): ?>
                    <tr>
                        <td><?= $organizer['organizer_name'] ?></td>
                        <td><?= $organizer['event_count'] ?></td>
                        <td><?= number_format($organizer['total'], 0, ',', '.') ?> VNĐ</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Top Customers -->
    <div class="top-customers-section">
        <h3>Khách hàng có nhiều giao dịch nhất</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Khách hàng</th>
                        <th>Email</th>
                        <th>Số giao dịch</th>
                        <th>Tổng chi tiêu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topCustomers as $customer): ?>
                    <tr>
                        <td><?= $customer['ho_ten'] ?></td>
                        <td><?= $customer['email'] ?></td>
                        <td><?= $customer['ticket_count'] ?></td>
                        <td><?= number_format($customer['total_spent'], 0, ',', '.') ?> VNĐ</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Transaction Details -->
    <div class="transaction-details-section">
        <h3>Chi tiết giao dịch</h3>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Mã giao dịch</th>
                        <th>Ngày giao dịch</th>
                        <th>Người dùng</th>
                        <th>Sự kiện</th>
                        <th>Loại vé</th>
                        <th>Phương thức thanh toán</th>
                        <th>Số tiền</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= $transaction['ma_giao_dich'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($transaction['ngay_giao_dich'])) ?></td>
                        <td><?= $transaction['ten_nguoi_dung'] ?></td>
                        <td><?= $transaction['ten_su_kien'] ?></td>
                        <td><?= $transaction['ten_loai_ve'] ?></td>
                        <td><?= $transaction['phuong_thuc_thanh_toan'] ?></td>
                        <td><?= number_format($transaction['so_tien'], 0, ',', '.') ?> VNĐ</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>&start_date=<?= $startDate ?? '' ?>&end_date=<?= $endDate ?? '' ?>&year=<?= $year ?>" class="page-link">&laquo; Trước</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&start_date=<?= $startDate ?? '' ?>&end_date=<?= $endDate ?? '' ?>&year=<?= $year ?>" class="page-link <?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>&start_date=<?= $startDate ?? '' ?>&end_date=<?= $endDate ?? '' ?>&year=<?= $year ?>" class="page-link">Sau &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Biểu đồ doanh thu theo ngày
const dailyCtx = document.getElementById('dailyRevenueChart').getContext('2d');
const dailyRevenueChart = new Chart(dailyCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($dailyLabels) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode($dailyData) ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 2,
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('vi-VN') + ' VNĐ';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw.toLocaleString('vi-VN') + ' VNĐ';
                    }
                }
            }
        }
    }
});

// Biểu đồ doanh thu theo tháng
const monthlyCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
const monthlyRevenueChart = new Chart(monthlyCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($monthlyLabels) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode($monthlyData) ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('vi-VN') + ' VNĐ';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw.toLocaleString('vi-VN') + ' VNĐ';
                    }
                }
            }
        }
    }
});

// Biểu đồ doanh thu theo quý
const quarterlyCtx = document.getElementById('quarterlyRevenueChart').getContext('2d');
const quarterlyRevenueChart = new Chart(quarterlyCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($quarterlyLabels) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode($quarterlyData) ?>,
            backgroundColor: 'rgba(153, 102, 255, 0.2)',
            borderColor: 'rgba(153, 102, 255, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('vi-VN') + ' VNĐ';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw.toLocaleString('vi-VN') + ' VNĐ';
                    }
                }
            }
        }
    }
});

// Biểu đồ doanh thu theo năm
const yearlyCtx = document.getElementById('yearlyRevenueChart').getContext('2d');
const yearlyRevenueChart = new Chart(yearlyCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($yearlyLabels) ?>,
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: <?= json_encode($yearlyData) ?>,
            backgroundColor: 'rgba(255, 159, 64, 0.2)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('vi-VN') + ' VNĐ';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + context.raw.toLocaleString('vi-VN') + ' VNĐ';
                    }
                }
            }
        }
    }
});

// Biểu đồ theo phương thức thanh toán
const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
const paymentMethodLabels = [];
const paymentMethodData = [];

<?php foreach ($revenueByPaymentMethod as $payment): ?>
    paymentMethodLabels.push('<?= $payment['phuong_thuc_thanh_toan'] ?>');
    paymentMethodData.push(<?= $payment['total'] ?>);
<?php endforeach; ?>

const paymentMethodChart = new Chart(paymentMethodCtx, {
    type: 'pie',
    data: {
        labels: paymentMethodLabels,
        datasets: [{
            data: paymentMethodData,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw.toLocaleString('vi-VN') + ' VNĐ';
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((context.raw / total) * 100);
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Biểu đồ theo loại sự kiện
const eventTypeCtx = document.getElementById('eventTypeChart').getContext('2d');
const eventTypeLabels = [];
const eventTypeData = [];

<?php foreach ($revenueByEventType as $type): ?>
    eventTypeLabels.push('<?= $type['ten_loai'] ?>');
    eventTypeData.push(<?= $type['total'] ?>);
<?php endforeach; ?>

const eventTypeChart = new Chart(eventTypeCtx, {
    type: 'doughnut',
    data: {
        labels: eventTypeLabels,
        datasets: [{
            data: eventTypeData,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw.toLocaleString('vi-VN') + ' VNĐ';
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((context.raw / total) * 100);
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Biểu đồ theo loại vé
const ticketTypeCtx = document.getElementById('ticketTypeChart').getContext('2d');
const ticketTypeLabels = [];
const ticketTypeData = [];
const ticketTypeColors = [];

<?php 
$colors = [
    'rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)',
    'rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)',
    'rgba(201, 203, 207, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)'
];
$borderColors = [
    'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)',
    'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)',
    'rgba(201, 203, 207, 1)', 'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)'
];
$i = 0;
foreach ($ticketsByType as $type): 
?>
    ticketTypeLabels.push('<?= $type['ten_loai_ve'] ?>');
    ticketTypeData.push(<?= $type['ticket_count'] ?>);
    ticketTypeColors.push({
        backgroundColor: '<?= $colors[$i % count($colors)] ?>',
        borderColor: '<?= $borderColors[$i % count($borderColors)] ?>'
    });
<?php 
$i++;
endforeach; 
?>

const ticketTypeChart = new Chart(ticketTypeCtx, {
    type: 'bar',
    data: {
        labels: ticketTypeLabels,
        datasets: [{
            label: 'Số lượng giao dịch',
            data: ticketTypeData,
            backgroundColor: ticketTypeColors.map(c => c.backgroundColor),
            borderColor: ticketTypeColors.map(c => c.borderColor),
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

<style>
.revenue-dashboard {
    padding: 20px;
}

.page-title {
    margin-bottom: 20px;
    color: #333;
    text-align: center;
}

.filter-section {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: flex-end;
}

.form-group {
    flex: 1;
    min-width: 200px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input, .form-group select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 8px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    white-space: nowrap;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-body {
    padding: 20px;
    text-align: center;
}

.card-title {
    margin-bottom: 10px;
    color: #555;
    font-size: 16px;
}

.card-value {
    font-size: 24px;
    font-weight: bold;
    color: #007bff;
    margin: 0;
}

.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.chart-container {
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    padding: 20px;
}

.chart-container h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
    color: #333;
    text-align: center;
}

.top-events-section, .top-organizers-section, .top-customers-section, .transaction-details-section {
    background-color: white;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    padding: 20px;
    margin-bottom: 30px;
}

.top-events-section h3, .top-organizers-section h3, .top-customers-section h3, .transaction-details-section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
    color: #333;
}

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th, .table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.pagination {
    display: flex;
    justify-content: center;
    margin-top: 20px;
}

.page-link {
    padding: 8px 12px;
    margin: 0 5px;
    border: 1px solid #ddd;
    border-radius: 4px;
    color: #007bff;
    text-decoration: none;
}

.page-link.active {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

@media (max-width: 768px) {
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .filter-form {
        flex-direction: column;
    }
    
    .form-group {
        width: 100%;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
