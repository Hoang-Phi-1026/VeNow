<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/users.css">

<div class="user-management">
    <div class="container">
        <div class="page-header">
            <h1>Quản lý tài khoản</h1>
            <a href="<?php echo BASE_URL; ?>/users/create" class="btn-create">
                <i class="fas fa-plus"></i> Tạo tài khoản mới
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Form tìm kiếm và lọc -->
        <div class="filter-section">
            <form action="<?php echo BASE_URL; ?>/users" method="GET" class="filter-form">
                <div class="search-group">
                    <input type="text" class="search-input" name="search" 
                           placeholder="Tìm theo tên, email hoặc số điện thoại..."
                           value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <i class="fas fa-search"></i>
                </div>
                <div class="role-filter">
                    <select class="form-select" name="role">
                        <option value="">Tất cả vai trò</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['ma_vai_tro']; ?>" 
                                    <?php echo (isset($_GET['role']) && $_GET['role'] == $role['ma_vai_tro']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['ten_vai_tro']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="filter-btn">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </form>
        </div>

        <div class="table-container" style="height: 350px; overflow-y: auto;">
            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3>Không tìm thấy tài khoản nào</h3>
                    <p>Hãy thử tìm kiếm với từ khóa khác hoặc xóa bộ lọc</p>
                </div>
            <?php else: ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Số điện thoại</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['ma_nguoi_dung']; ?></td>
                            <td><?php echo htmlspecialchars($user['ho_ten']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['so_dien_thoai']); ?></td>
                            <td><?php echo htmlspecialchars($user['ten_vai_tro']); ?></td>
                            <td>
                                <span class="status-badge <?php echo $user['kich_hoat'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $user['kich_hoat'] ? 'Đang hoạt động' : 'Đã khóa'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($user['ngay_tao'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="<?php echo BASE_URL; ?>/users/edit/<?php echo $user['ma_nguoi_dung']; ?>" 
                                       class="btn-edit" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo BASE_URL; ?>/users/delete/<?php echo $user['ma_nguoi_dung']; ?>" 
                                       class="btn-delete" title="Xóa"
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
