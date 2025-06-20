<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
?>

<div class="container">
    <div class="register-container">
        <h2 class="register-title">Đăng ký tài khoản</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
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

        <form method="POST" action="<?php echo BASE_URL; ?>/auth/register" class="register-form" id="registerForm">
            <div class="form-group">
                <label for="fullname">Họ và tên</label>
                <input type="text" id="fullname" name="fullname" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="gender">Giới tính</label>
                <select id="gender" name="gender" required>
                    <option value="">Chọn giới tính</option>
                    <option value="NAM">Nam</option>
                    <option value="NU">Nữ</option>
                    <option value="KHAC">Khác</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="password-toggle" aria-label="Hiển thị mật khẩu">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-indicator" style="width: 0%"></div>
                    </div>
                    <span class="strength-text">Độ mạnh mật khẩu</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu</label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="password-toggle" aria-label="Hiển thị mật khẩu">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group terms-checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">Tôi đồng ý với <a href="<?php echo BASE_URL; ?>/terms" target="_blank">Điều khoản dịch vụ</a> và <a href="<?php echo BASE_URL; ?>/privacy" target="_blank">Chính sách bảo mật</a></label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-register">Đăng ký</button>
            </div>
            
            <div class="form-footer">
                <p class="login-link">
                    Đã có tài khoản? <a href="<?php echo BASE_URL; ?>/login">Đăng nhập ngay</a>
                </p>
                <div class="organizer-register-cta">
                    <hr>
                    <div class="organizer-highlight">
                        <i class="fas fa-bullhorn"></i>
                        <span>Bạn muốn có sự kiện của riêng mình?</span>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/organizer" class="btn-organizer-register">
                        Đăng ký trở thành nhà tổ chức sự kiện tại đây
                    </a>
                    <p class="organizer-desc">
                        Tạo tài khoản nhà tổ chức để đăng tải sự kiện, bán vé và nhận doanh thu từ sự kiện của chính mình!
                    </p>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.organizer-register-cta {
    margin-top: 28px;
    padding: 18px 12px;
    background: #faf6f2;
    border-radius: 12px;
    border: 1px solid #ffe0cc;
    box-shadow: 0 1px 6px rgba(255,87,34,0.06);
    text-align: center;
    position: relative;
}
.organizer-highlight {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    color: #ff5722;
    font-size: 1.08rem;
    gap: 8px;
}
.organizer-register-cta hr {
    border: none;
    border-top: 1px solid #ffe0cc;
    margin: 0 0 12px 0;
}
.btn-organizer-register {
    display: inline-block;
    margin: 14px auto 8px auto;
    background: #ff5722;
    color: #fff;
    padding: 10px 28px;
    font-weight: 600;
    border-radius: 8px;
    transition: background 0.18s, box-shadow 0.18s;
    box-shadow: 0 2px 8px rgba(255,87,34,0.13);
    font-size: 1.08rem;
    text-decoration: none;
}
.btn-organizer-register:hover {
    background: #e64a19;
    box-shadow: 0 4px 18px rgba(255,87,34,0.16);
}
.organizer-desc {
    color: #b56b30;
    font-size: 0.99rem;
    margin: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const toggleButtons = document.querySelectorAll('.password-toggle');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Toggle icon
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });

    // Simple password strength indicator (for UI only)
    const passwordInput = document.getElementById('password');
    const strengthIndicator = document.querySelector('.strength-indicator');
    const strengthText = document.querySelector('.strength-text');

    passwordInput.addEventListener('input', function() {
        const password = this.value;
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^A-Za-z0-9]/)) strength += 25;
        
        strengthIndicator.style.width = strength + '%';
        
        if (strength <= 25) {
            strengthIndicator.style.backgroundColor = '#ff4d4d';
            strengthText.textContent = 'Yếu';
        } else if (strength <= 50) {
            strengthIndicator.style.backgroundColor = '#ffa64d';
            strengthText.textContent = 'Trung bình';
        } else if (strength <= 75) {
            strengthIndicator.style.backgroundColor = '#ffff4d';
            strengthText.textContent = 'Khá';
        } else {
            strengthIndicator.style.backgroundColor = '#4dff4d';
            strengthText.textContent = 'Mạnh';
        }
    });
    
    // Password confirmation validation
    const confirmPasswordInput = document.getElementById('confirm_password');
    const registerForm = document.getElementById('registerForm');
    
    registerForm.addEventListener('submit', function(event) {
        if (passwordInput.value !== confirmPasswordInput.value) {
            event.preventDefault();
            alert('Mật khẩu xác nhận không khớp!');
            confirmPasswordInput.focus();
        }
    });
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../layouts/footer.php';
?>