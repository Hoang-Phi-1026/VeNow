<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

<div class="container mt-4" id="revenue-report">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Báo cáo doanh thu</h2>
            <p class="text-muted">Nhà tổ chức: <?php echo htmlspecialchars($organizerName); ?></p>
        </div>
        <div>
            <a href="<?php echo BASE_URL; ?>/organizer/events" class="btn btn-outline-primary me-2">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách sự kiện
            </a>
            <button id="exportPDF" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Xuất PDF
            </button>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Bộ lọc thời gian -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Bộ lọc thời gian</h5>
        </div>
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>/organizer/revenue" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="time_filter" class="form-label">Khoảng thời gian</label>
                    <select name="time_filter" id="time_filter" class="form-select" onchange="toggleCustomDateInputs()">
                        <option value="all" <?php echo isset($_GET['time_filter']) && $_GET['time_filter'] == 'all' ? 'selected' : ''; ?>>Tất cả thời gian</option>
                        <option value="this_month" <?php echo isset($_GET['time_filter']) && $_GET['time_filter'] == 'this_month' ? 'selected' : ''; ?>>Tháng này</option>
                        <option value="last_month" <?php echo isset($_GET['time_filter']) && $_GET['time_filter'] == 'last_month' ? 'selected' : ''; ?>>Tháng trước</option>
                        <option value="this_year" <?php echo isset($_GET['time_filter']) && $_GET['time_filter'] == 'this_year' ? 'selected' : ''; ?>>Năm nay</option>
                        <option value="last_year" <?php echo isset($_GET['time_filter']) && $_GET['time_filter'] == 'last_year' ? 'selected' : ''; ?>>Năm trước</option>
                        <option value="custom" <?php echo isset($_GET['time_filter']) && $_GET['time_filter'] == 'custom' ? 'selected' : ''; ?>>Tùy chỉnh</option>
                    </select>
                </div>
                <div class="col-md-3 custom-date" style="display: <?php echo isset($_GET['time_filter']) && $_GET['time_filter'] == 'custom' ? 'block' : 'none'; ?>;">
                    <label for="start_date" class="form-label">Từ ngày</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
                </div>
                <div class="col-md-3 custom-date" style="display: <?php echo isset($_GET['time_filter']) && $_GET['time_filter'] == 'custom' ? 'block' : 'none'; ?>;">
                    <label for="end_date" class="form-label">Đến ngày</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tổng quan doanh thu -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Tổng quan doanh thu</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="revenue-stat">
                                <h3 class="text-success"><?php echo number_format($totalRevenue, 0, ',', '.'); ?> VNĐ</h3>
                                <p class="text-muted">Tổng doanh thu</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="revenue-stat">
                                <h3 class="text-primary"><?php echo number_format($totalTicketsSold, 0, ',', '.'); ?></h3>
                                <p class="text-muted">Tổng vé đã bán</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="revenue-stat">
                                <h3 class="text-info"><?php echo count($eventRevenues); ?></h3>
                                <p class="text-muted">Số sự kiện có doanh thu</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="revenue-stat">
                                <h3 class="text-warning"><?php echo $totalTicketsSold > 0 ? number_format($totalRevenue / $totalTicketsSold, 0, ',', '.') : 0; ?> VNĐ</h3>
                                <p class="text-muted">Giá vé trung bình</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Biểu đồ doanh thu theo tháng</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ tròn doanh thu theo loại vé -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Phân bổ doanh thu theo loại vé</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <canvas id="ticketTypeChart" width="400" height="300"></canvas>
                </div>
                <div class="col-md-4">
                    <div class="ticket-type-legend">
                        <h5 class="mb-3">Chú thích</h5>
                        <?php if (empty($ticketTypeRevenues)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Không có dữ liệu doanh thu theo loại vé.
                            </div>
                        <?php else: ?>
                            <?php 
                            $colors = [
                                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
                                '#6f42c1', '#5a5c69', '#2ecc71', '#3498db', '#e67e22'
                            ];
                            $i = 0;
                            foreach ($ticketTypeRevenues as $ticketType): 
                                $percentage = $totalRevenue > 0 ? ($ticketType['tong_doanh_thu'] / $totalRevenue) * 100 : 0;
                            ?>
                                <div class="legend-item mb-2">
                                    <div class="d-flex align-items-center">
                                        <div class="color-box" style="background-color: <?php echo $colors[$i % count($colors)]; ?>"></div>
                                        <div class="ms-2">
                                            <div class="fw-bold"><?php echo htmlspecialchars($ticketType['ten_loai_ve']); ?></div>
                                            <div class="small text-muted">
                                                <?php echo number_format($ticketType['tong_doanh_thu'], 0, ',', '.'); ?> VNĐ
                                                (<?php echo round($percentage, 1); ?>%)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                $i++;
                                endforeach; 
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Doanh thu theo sự kiện -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Doanh thu theo sự kiện</h5>
        </div>
        <div class="card-body">
            <?php if (empty($eventRevenues)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Không có dữ liệu doanh thu trong khoảng thời gian đã chọn.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Sự kiện</th>
                                <th>Ngày diễn ra</th>
                                <th>Số vé đã bán</th>
                                <th>Doanh thu</th>
                                <th>Tỷ lệ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventRevenues as $event): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($event['hinh_anh']) && file_exists(BASE_PATH . '/' . $event['hinh_anh'])): ?>
                                                <img src="<?php echo BASE_URL . '/' . $event['hinh_anh']; ?>" alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>" class="me-2" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <div class="me-2" style="width: 50px; height: 50px; background-color: #e9ecef; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                                    <i class="fas fa-calendar-day text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <a href="<?php echo BASE_URL; ?>/event/<?php echo $event['ma_su_kien']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($event['ten_su_kien']); ?>
                                            </a>
                                        </div>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($event['ngay_dien_ra'])); ?></td>
                                    <td><?php echo number_format($event['so_ve_da_ban'], 0, ',', '.'); ?></td>
                                    <td><?php echo number_format($event['tong_doanh_thu'], 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <?php 
                                            $percentage = $totalRevenue > 0 ? ($event['tong_doanh_thu'] / $totalRevenue) * 100 : 0;
                                        ?>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo round($percentage, 1); ?>%</div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Doanh thu theo loại vé -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-ticket-alt"></i> Doanh thu theo loại vé</h5>
        </div>
        <div class="card-body">
            <?php if (empty($ticketTypeRevenues)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Không có dữ liệu doanh thu theo loại vé trong khoảng thời gian đã chọn.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Loại vé</th>
                                <th>Giá vé</th>
                                <th>Số vé đã bán</th>
                                <th>Doanh thu</th>
                                <th>Tỷ lệ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ticketTypeRevenues as $ticketType): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($ticketType['ten_loai_ve']); ?></td>
                                    <td><?php echo number_format($ticketType['gia_ve'], 0, ',', '.'); ?> VNĐ</td>
                                    <td><?php echo number_format($ticketType['so_ve_da_ban'], 0, ',', '.'); ?></td>
                                    <td><?php echo number_format($ticketType['tong_doanh_thu'], 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <?php 
                                            $percentage = $totalRevenue > 0 ? ($ticketType['tong_doanh_thu'] / $totalRevenue) * 100 : 0;
                                        ?>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $percentage; ?>%;" aria-valuenow="<?php echo $percentage; ?>" aria-valuemin="0" aria-valuemax="100"><?php echo round($percentage, 1); ?>%</div>
                                        </div>
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

<script>
// Hàm hiển thị/ẩn các trường ngày tùy chỉnh
function toggleCustomDateInputs() {
    const timeFilter = document.getElementById('time_filter').value;
    const customDateInputs = document.querySelectorAll('.custom-date');
    
    if (timeFilter === 'custom') {
        customDateInputs.forEach(input => {
            input.style.display = 'block';
        });
    } else {
        customDateInputs.forEach(input => {
            input.style.display = 'none';
        });
    }
}

// Khởi tạo biểu đồ doanh thu
document.addEventListener('DOMContentLoaded', function() {
    // Biểu đồ cột doanh thu theo tháng
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: <?php echo json_encode($chartData); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
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
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' VNĐ';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Biểu đồ tròn doanh thu theo loại vé
    <?php if (!empty($ticketTypeRevenues)): ?>
    const pieCtx = document.getElementById('ticketTypeChart').getContext('2d');
    const ticketTypeData = {
        labels: [
            <?php 
            foreach ($ticketTypeRevenues as $ticketType) {
                echo "'" . htmlspecialchars($ticketType['ten_loai_ve']) . "', ";
            }
            ?>
        ],
        datasets: [{
            data: [
                <?php 
                foreach ($ticketTypeRevenues as $ticketType) {
                    echo $ticketType['tong_doanh_thu'] . ", ";
                }
                ?>
            ],
            backgroundColor: [
                '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', 
                '#6f42c1', '#5a5c69', '#2ecc71', '#3498db', '#e67e22'
            ],
            hoverOffset: 4
        }]
    };
    
    const ticketTypeChart = new Chart(pieCtx, {
        type: 'pie',
        data: ticketTypeData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw;
                            const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${new Intl.NumberFormat('vi-VN').format(value)} VNĐ (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Xuất PDF
    document.getElementById('exportPDF').addEventListener('click', function() {
        // Hiển thị thông báo đang xử lý
        const processingAlert = document.createElement('div');
        processingAlert.className = 'alert alert-info position-fixed top-0 start-50 translate-middle-x mt-3';
        processingAlert.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo PDF, vui lòng đợi...';
        processingAlert.style.zIndex = '9999';
        document.body.appendChild(processingAlert);

        // Khởi tạo jsPDF
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        
        // Lấy phần tử cần xuất PDF
        const reportElement = document.getElementById('revenue-report');
        
        // Ẩn nút xuất PDF và bộ lọc thời gian khi xuất
        const exportButton = document.getElementById('exportPDF');
        const filterCard = reportElement.querySelector('.card:first-of-type');
        
        exportButton.style.display = 'none';
        filterCard.style.display = 'none';
        
        // Sử dụng html2canvas để chuyển đổi HTML thành canvas
        html2canvas(reportElement, {
            scale: 1,
            useCORS: true,
            logging: false,
            allowTaint: true
        }).then(canvas => {
            // Khôi phục hiển thị các phần tử đã ẩn
            exportButton.style.display = 'inline-block';
            filterCard.style.display = 'block';
            
            // Lấy dữ liệu từ canvas
            const imgData = canvas.toDataURL('image/png');
            
            // Tính toán kích thước để vừa với trang A4
            const imgWidth = 210; // A4 width in mm
            const pageHeight = 295; // A4 height in mm
            const imgHeight = canvas.height * imgWidth / canvas.width;
            let heightLeft = imgHeight;
            let position = 0;
            
            // Thêm trang đầu tiên
            doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
            
            // Thêm các trang tiếp theo nếu cần
            while (heightLeft > 0) {
                position = heightLeft - imgHeight;
                doc.addPage();
                doc.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }
            
            // Tạo tên file với thời gian hiện tại
            const now = new Date();
            const fileName = `Bao_cao_doanh_thu_${now.getDate()}_${now.getMonth() + 1}_${now.getFullYear()}.pdf`;
            
            // Lưu file
            doc.save(fileName);
            
            // Xóa thông báo đang xử lý
            document.body.removeChild(processingAlert);
            
            // Hiển thị thông báo thành công
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
            successAlert.innerHTML = '<i class="fas fa-check-circle"></i> Xuất PDF thành công!';
            successAlert.style.zIndex = '9999';
            document.body.appendChild(successAlert);
            
            // Tự động ẩn thông báo sau 3 giây
            setTimeout(() => {
                document.body.removeChild(successAlert);
            }, 3000);
        });
    });
});
</script>

<style>
.revenue-stat {
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    background-color: #f8f9fa;
    height: 100%;
    transition: all 0.3s ease;
}

.revenue-stat:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.revenue-stat h3 {
    margin-bottom: 5px;
    font-weight: bold;
}

.progress {
    height: 20px;
}

.progress-bar {
    line-height: 20px;
    font-size: 12px;
    font-weight: bold;
}

.color-box {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}

.legend-item {
    padding: 8px;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.legend-item:hover {
    background-color: #f8f9fa;
}

.ticket-type-legend {
    padding: 15px;
    border-radius: 8px;
    background-color: #f8f9fa;
    height: 100%;
}

@media print {
    .no-print {
        display: none !important;
    }
}
</style>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
