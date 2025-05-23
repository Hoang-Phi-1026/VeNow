<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php require_once __DIR__ . '/../../utils/IdHasher.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/event-manage.css">

<div class="container admin-container">
    <div class="page-header">
        <h1><i class="fas fa-calendar-alt"></i> Quản lý sự kiện</h1>
    </div>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <!-- Form tìm kiếm -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-search"></i> Tìm kiếm sự kiện</h2>
        </div>
        <div class="card-body">
            <form action="<?php echo BASE_URL; ?>/events/manage" method="GET" class="filter-form">
                <div class="form-group">
                    <label for="keyword" class="form-label">Từ khóa</label>
                    <input type="text" class="form-control" id="keyword" name="keyword" placeholder="Tên sự kiện, địa điểm..." value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="category" class="form-label">Loại sự kiện</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">Tất cả loại</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['maloaisukien']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $category['maloaisukien']) ? 'selected' : ''; ?>>
                                <?php echo $category['tenloaisukien']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="CHO_DUYET" <?php echo (isset($_GET['status']) && $_GET['status'] == 'CHO_DUYET') ? 'selected' : ''; ?>>Chờ duyệt</option>
                        <option value="DA_DUYET" <?php echo (isset($_GET['status']) && $_GET['status'] == 'DA_DUYET') ? 'selected' : ''; ?>>Đã duyệt</option>
                        <option value="TU_CHOI" <?php echo (isset($_GET['status']) && $_GET['status'] == 'TU_CHOI') ? 'selected' : ''; ?>>Từ chối</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Danh sách sự kiện -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-list"></i> Danh sách sự kiện</h2>
        </div>
        <div class="card-body">
            <?php if (empty($events)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>Không tìm thấy sự kiện nào</h3>
                    <p>Vui lòng thử lại với các tiêu chí tìm kiếm khác.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tên sự kiện</th>
                                <th>Nhà tổ chức</th>
                                <th>Loại</th>
                                <th>Ngày diễn ra</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?php echo $event['ma_su_kien']; ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/event/<?php echo IdHasher::encode($event['ma_su_kien']); ?>" class="text-decoration-none fw-bold">
                                            <?php echo htmlspecialchars($event['ten_su_kien']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($event['ho_ten'] ?? 'Không có'); ?></td>
                                    <td><?php echo htmlspecialchars($event['tenloaisukien'] ?? 'Không có'); ?></td>
                                    <td>
                                        <?php 
                                            $date = new DateTime($event['ngay_dien_ra']);
                                            echo $date->format('d/m/Y'); 
                                        ?> 
                                        <span class="text-muted"><?php echo $event['gio_dien_ra']; ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            switch ($event['trang_thai']) {
                                                case 'CHO_DUYET':
                                                    echo '<span class="status-badge status-pending">Chờ duyệt</span>';
                                                    break;
                                                case 'DA_DUYET':
                                                    echo '<span class="status-badge status-active">Đã duyệt</span>';
                                                    break;
                                                case 'TU_CHOI':
                                                    echo '<span class="status-badge status-inactive">Từ chối</span>';
                                                    break;
                                                default:
                                                    echo '<span class="status-badge">Đã hủy</span>';
                                                    break;
                                                
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="<?php echo BASE_URL; ?>/event/<?php echo IdHasher::encode($event['ma_su_kien']); ?>" class="btn btn-info btn-sm" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/events/delete/<?php echo $event['ma_su_kien']; ?>" 
                                               class="btn btn-danger btn-sm" 
                                               title="Xóa sự kiện"
                                               onclick="return confirmDelete('<?php echo htmlspecialchars($event['ten_su_kien']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </a>
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
function confirmDelete(eventName) {
    return confirm('Bạn có chắc chắn muốn xóa sự kiện "' + eventName + '"?\n\n' +
                  'Cảnh báo: Hành động này không thể hoàn tác. Tất cả dữ liệu liên quan đến sự kiện này (vé, chỗ ngồi, bình luận) sẽ bị xóa vĩnh viễn.');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
