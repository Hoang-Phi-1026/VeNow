<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/users.css">

<div class="user-management user-form">
    <div class="container">
        <div class="page-header">
            <h1>Tạo tài khoản mới</h1>
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
                <form action="<?php echo BASE_URL; ?>/users/create" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="mat_khau" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control" id="mat_khau" name="mat_khau" required>
                    </div>

                    <div class="mb-3">
                        <label for="ho_ten" class="form-label">Họ tên</label>
                        <input type="text" class="form-control" id="ho_ten" name="ho_ten" required>
                    </div>

                    <div class="mb-3">
                        <label for="so_dien_thoai" class="form-label">Số điện thoại</label>
                        <input type="tel" class="form-control" id="so_dien_thoai" name="so_dien_thoai" required>
                    </div>

                    <div class="mb-3">
                        <label for="ma_vai_tro" class="form-label">Vai trò</label>
                        <select class="form-select" id="ma_vai_tro" name="ma_vai_tro" required>
                            <option value="">Chọn vai trò</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?php echo $role['ma_vai_tro']; ?>">
                                    <?php echo htmlspecialchars($role['ten_vai_tro']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Tạo tài khoản
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
