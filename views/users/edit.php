<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/users.css">

<div class="user-management user-form">
    <div class="container">
        <div class="page-header">
            <h1>Chỉnh sửa tài khoản</h1>
            <a href="<?php echo BASE_URL; ?>/users" class="btn-create">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form action="<?php echo BASE_URL; ?>/users/edit/<?php echo $user['ma_nguoi_dung']; ?>" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="mat_khau" class="form-label">Mật khẩu mới (để trống nếu không muốn thay đổi)</label>
                        <input type="password" class="form-control" id="mat_khau" name="mat_khau">
                    </div>

                    <div class="mb-3">
                        <label for="ho_ten" class="form-label">Họ tên</label>
                        <input type="text" class="form-control" id="ho_ten" name="ho_ten" 
                               value="<?php echo htmlspecialchars($user['ho_ten']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="so_dien_thoai" class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" id="so_dien_thoai" name="so_dien_thoai" 
                               value="<?php echo htmlspecialchars($user['so_dien_thoai']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="ma_vai_tro" class="form-label">Vai trò</label>
                        <select class="form-select" id="ma_vai_tro" name="ma_vai_tro" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['ma_vai_tro']; ?>" 
                                        <?php echo $role['ma_vai_tro'] == $user['ma_vai_tro'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($role['ten_vai_tro']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="kich_hoat" name="kich_hoat" 
                                   <?php echo $user['kich_hoat'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="kich_hoat">Tài khoản đang hoạt động</label>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                        <a href="<?php echo BASE_URL; ?>/users" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
