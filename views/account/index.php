<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tài khoản của tôi - Venow</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/account.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="container account-page">
        <div class="account-container">
            <div class="account-header">
                <div class="account-title">
                    <h1>Tài khoản của tôi</h1>
                    <p>Quản lý thông tin cá nhân và cài đặt tài khoản</p>
                </div>
                <div class="account-tabs">
                    <a href="<?php echo BASE_URL; ?>/account" class="account-tab active">
                        <i class="fas fa-user"></i> Thông tin cá nhân
                    </a>
                    <a href="<?php echo BASE_URL; ?>/account/events" class="account-tab">
                        <i class="fas fa-calendar"></i> Sự kiện của tôi
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="account-content">
                <form action="<?php echo BASE_URL; ?>/account/update" method="POST" enctype="multipart/form-data" class="account-form">
                    <div class="form-section">
                        <h2>Thông tin cơ bản</h2>
                        
                        <div class="avatar-upload-container">
                            <div class="avatar-preview">
                                <?php if (!empty($user['avt'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/public/uploads/avatars/<?php echo htmlspecialchars($user['avt']); ?>" 
                                         alt="Avatar" id="avatar-preview-img">
                                <?php else: ?>
                                    <div class="avatar-placeholder" id="avatar-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="avatar-upload-controls">
                                <h3><?php echo htmlspecialchars($user['ho_ten']); ?></h3>
                                <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                                <div class="upload-btn-wrapper">
                                    <input type="file" id="avt" name="avt" accept="image/*">
                                    <label for="avt" class="upload-btn">
                                        <i class="fas fa-camera"></i> Thay đổi ảnh đại diện
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="ho_ten">Họ tên</label>
                                <input type="text" id="ho_ten" name="ho_ten" 
                                       value="<?php echo htmlspecialchars($user['ho_ten']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                                <small>Email không thể thay đổi</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="so_dien_thoai">Số điện thoại</label>
                            <input type="tel" id="so_dien_thoai" name="so_dien_thoai" 
                                   value="<?php echo htmlspecialchars($user['so_dien_thoai'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-section">
                        <h2>Thay đổi mật khẩu</h2>
                        <p class="section-desc">Để trống nếu bạn không muốn thay đổi mật khẩu</p>
                        
                        <div class="form-group">
                            <label for="mat_khau">Mật khẩu hiện tại</label>
                            <input type="password" id="mat_khau" name="mat_khau">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="mat_khau_moi">Mật khẩu mới</label>
                                <input type="password" id="mat_khau_moi" name="mat_khau_moi">
                            </div>

                            <div class="form-group">
                                <label for="xac_nhan_mat_khau">Xác nhận mật khẩu mới</label>
                                <input type="password" id="xac_nhan_mat_khau" name="xac_nhan_mat_khau">
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save"></i> Cập nhật thông tin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

    <script>
    // Preview avatar image before upload
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avt');
        const avatarPreview = document.getElementById('avatar-preview-img');
        const avatarPlaceholder = document.getElementById('avatar-placeholder');
        
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    if (avatarPreview) {
                        // If there's already an image element
                        avatarPreview.src = e.target.result;
                    } else if (avatarPlaceholder) {
                        // If there's a placeholder, replace it with an image
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.id = 'avatar-preview-img';
                        avatarPlaceholder.parentNode.replaceChild(img, avatarPlaceholder);
                    }
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    });
    </script>
</body>
</html>
