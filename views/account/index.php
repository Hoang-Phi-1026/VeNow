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
                        
                        <!-- Phần giới thiệu bản thân -->
                        <div class="bio-section">
                            <div class="bio-header">
                                <div class="bio-display">
                                    <h4><i class="fas fa-user-edit"></i> Giới thiệu về bạn</h4>
                                    <div class="bio-content" id="bioContent">
                                        <?php if (!empty($user['mo_ta'])): ?>
                                            <?php echo nl2br(htmlspecialchars($user['mo_ta'])); ?>
                                        <?php else: ?>
                                            <em>Chưa có thông tin giới thiệu. Hãy một tả một chút về bạn.</em>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button type="button" class="bio-edit-btn" id="bioEditBtn">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                            </div>
                            
                            <div class="bio-editor-container" id="bioEditorContainer">
                                <div class="bio-editor">
                                    <textarea id="mo_ta" name="mo_ta" rows="6" 
                                        placeholder="Hãy giới thiệu một chút về bản thân..."><?php echo htmlspecialchars($user['mo_ta'] ?? ''); ?></textarea>
                                    <div class="bio-counter"><span id="charCount">0</span>/500 ký tự</div>
                                </div>
                                <div class="bio-tips">
                                    <i class="fas fa-lightbulb"></i> Gợi ý: Chia sẻ sở thích, kinh nghiệm hoặc lĩnh vực bạn quan tâm để mọi người hiểu hơn về bạn.
                                </div>
                                <div class="bio-actions">
                                    <button type="button" class="btn-cancel" id="bioCancelBtn">
                                        <i class="fas fa-times"></i> Hủy
                                    </button>
                                    <button type="button" class="btn-save" id="bioSaveBtn">
                                        <i class="fas fa-check"></i> Lưu thay đổi
                                    </button>
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

                        <div class="form-row">
                            <div class="form-group">
                                <label for="so_dien_thoai">Số điện thoại</label>
                                <input type="tel" id="so_dien_thoai" name="so_dien_thoai" 
                                       value="<?php echo htmlspecialchars($user['so_dien_thoai'] ?? ''); ?>">
                            </div>

                            <div class="form-group">
                                <label for="gioi_tinh">Giới tính</label>
                                <select id="gioi_tinh" name="gioi_tinh" required>
                                    <option value="">Chọn giới tính</option>
                                    <option value="NAM" <?php echo $user['gioi_tinh'] === 'NAM' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="NU" <?php echo $user['gioi_tinh'] === 'NU' ? 'selected' : ''; ?>>Nữ</option>
                                    <option value="KHAC" <?php echo $user['gioi_tinh'] === 'KHAC' ? 'selected' : ''; ?>>Khác</option>
                                </select>
                            </div>
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
document.addEventListener('DOMContentLoaded', function() {
    // Avatar preview functionality
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

    // Bio editor functionality
    const bioTextarea = document.getElementById('mo_ta');
    const charCount = document.getElementById('charCount');
    const bioEditBtn = document.getElementById('bioEditBtn');
    const bioCancelBtn = document.getElementById('bioCancelBtn');
    const bioSaveBtn = document.getElementById('bioSaveBtn');
    const bioEditorContainer = document.getElementById('bioEditorContainer');
    const bioContent = document.getElementById('bioContent');
    
    // Set initial character count
    charCount.textContent = bioTextarea.value.length;
    
    // Hide bio editor by default
    bioEditorContainer.style.display = 'none';
    
    // Show bio editor when edit button is clicked
    bioEditBtn.addEventListener('click', function() {
        bioEditorContainer.style.display = 'block';
        bioEditBtn.style.display = 'none';
        bioTextarea.focus();
    });
    
    // Hide bio editor when cancel button is clicked
    bioCancelBtn.addEventListener('click', function() {
        bioEditorContainer.style.display = 'none';
        bioEditBtn.style.display = 'block';
    });
    
    // Save bio content when save button is clicked
    bioSaveBtn.addEventListener('click', function() {
        const bioText = bioTextarea.value;
        bioContent.innerHTML = bioText ? nl2br(bioText) : '<em>Chưa có thông tin giới thiệu. Nhấp vào biểu tượng bút để thêm.</em>';
        bioEditorContainer.style.display = 'none';
        bioEditBtn.style.display = 'block';
    });
    
    bioTextarea.addEventListener('input', function() {
        // Update character count
        charCount.textContent = this.value.length;
        
        // Change color based on length
        if (this.value.length > 400) {
            charCount.style.color = '#ff9800';
        } else {
            charCount.style.color = '';
        }
        
        // Limit to 500 characters
        if (this.value.length > 500) {
            this.value = this.value.substring(0, 500);
            charCount.textContent = 500;
            charCount.style.color = '#f44336';
        }
    });
    
    // Helper function to convert newlines to <br> tags
    function nl2br(str) {
        return str.replace(/\n/g, '<br>');
    }
});
</script>
</body>
</html>
