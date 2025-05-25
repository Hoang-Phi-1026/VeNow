<?php
// Include header
require_once __DIR__ . '/../layouts/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/register.css?v=3">

<div class="container">
    <div class="register-container">
        <h3 class="register-title">Trở thành đối tác với VeNow!</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL; ?>/auth/register/organizer" class="register-form" id="registerOrganizerForm" autocomplete="off">
            <div class="form-group">
                <label for="fullname">Tên đơn vị tổ chức <span class="required">*</span></label>
                <input type="text" id="fullname" name="fullname" required>
            </div>

            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại <span class="required">*</span></label>
                <input type="tel" id="phone" name="phone" pattern="0[0-9]{9,10}" maxlength="11" required>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu <span class="required">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" required minlength="8">
                    <button type="button" class="password-toggle" aria-label="Hiển thị mật khẩu">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-bar">
                        <div class="strength-indicator"></div>
                    </div>
                    <span class="strength-text">Độ mạnh mật khẩu</span>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu <span class="required">*</span></label>
                <div class="password-input-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                    <button type="button" class="password-toggle" aria-label="Hiển thị mật khẩu">
                        <i class="far fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="bank_name">Tên ngân hàng <span class="required">*</span></label>
                <input type="text" id="bank_name" name="bank_name" placeholder="VD: Vietcombank, BIDV..." required>
                <small class="input-hint">Tên ngân hàng dùng để nhận thanh toán từ sự kiện.</small>
            </div>

            <div class="form-group">
                <label for="account_number">Số tài khoản thụ hưởng<span class="required">*</span></label>
                <input type="text" id="account_number" name="account_number" required pattern="[0-9]{8,20}" maxlength="20">
                <small class="input-hint">Thông tin số tài khoản phải chính xác để nhận doanh thu nhanh chóng.</small>
            </div>

            <div class="form-group terms-checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    Tôi đồng ý với <a href="<?= BASE_URL; ?>/terms" target="_blank">Điều khoản dịch vụ</a> và <a href="<?= BASE_URL; ?>/privacy" target="_blank">Chính sách bảo mật</a>
                </label>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-register" onclick="showConfirmation()">Đăng ký</button>
            </div>

            <div class="form-footer">
                <p class="login-link">Đã có tài khoản? <a href="<?= BASE_URL; ?>/login">Đăng nhập</a></p>
            </div>
        </form>
    </div>
</div>

<!-- Modal xác nhận ngân hàng -->
<div id="confirmationModal" class="modal">
  <div class="modal-content">
    <span class="close-button" onclick="closeConfirmation()">&times;</span>
    <div class="modal-info">
      <h2>Chính sách thanh toán dành cho Nhà tổ chức</h2>
      <p>
        Để đảm bảo quyền lợi cho Nhà tổ chức, Venow sẽ chuyển doanh thu bán vé trực tiếp vào tài khoản ngân hàng bạn đã đăng ký.
      </p>
      <ul>
        <li>
          <b>Thời gian nhận tiền:</b> Bạn sẽ nhận được khoản thanh toán trong vòng <strong>7–10 ngày</strong> sau khi xác nhận báo cáo doanh thu (sale report).
        </li>
        <li>
          <b>Số tiền thanh toán:</b> Doanh thu bạn nhận được là số tiền bán vé sau khi đã trừ chi phí dịch vụ theo quy định của Venow.
        </li>
        <li>
          <b>Hỗ trợ thanh toán nhanh:</b> Nếu bạn muốn nhận doanh thu sự kiện sớm hơn, vui lòng liên hệ với Venow qua số <b>1900 6408</b> hoặc email <b>support@venow.vn</b> để được hỗ trợ.
        </li>
      </ul>
      <p class="note">
        <strong>Lưu ý quan trọng:</strong> Vui lòng kiểm tra và nhập đúng số tài khoản ngân hàng của bạn. Venow không chịu trách nhiệm đối với các trường hợp chuyển khoản không thành công do thông tin tài khoản không chính xác.
      </p>
    </div>
    <div class="modal-buttons">
      <button class="btn-accept" onclick="registerOrganizer()">Xác nhận</button>
      <button class="btn-cancel" onclick="closeConfirmation()">Hủy</button>
    </div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function () {
            const input = this.previousElementSibling;
            const type = input.type === 'password' ? 'text' : 'password';
            input.type = type;
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });

    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');
    const strengthBar = document.querySelector('.strength-indicator');
    const strengthText = document.querySelector('.strength-text');

    password.addEventListener('input', function () {
        let value = password.value;
        let strength = 0;
        if (value.length >= 8) strength += 25;
        if (/[A-Z]/.test(value)) strength += 25;
        if (/[0-9]/.test(value)) strength += 25;
        if (/[^A-Za-z0-9]/.test(value)) strength += 25;

        strengthBar.style.width = strength + '%';
        if (strength <= 25) {
            strengthBar.style.backgroundColor = '#ff4d4d';
            strengthText.textContent = 'Yếu';
        } else if (strength <= 50) {
            strengthBar.style.backgroundColor = '#ffa64d';
            strengthText.textContent = 'Trung bình';
        } else if (strength <= 75) {
            strengthBar.style.backgroundColor = '#ffff4d';
            strengthText.textContent = 'Khá';
        } else {
            strengthBar.style.backgroundColor = '#4dff4d';
            strengthText.textContent = 'Mạnh';
        }
    });

    document.getElementById('registerOrganizerForm').addEventListener('submit', function (e) {
        if (password.value !== confirm.value) {
            e.preventDefault();
            alert('Mật khẩu xác nhận không khớp!');
            confirm.focus();
        }
    });
});

function showConfirmation() {
    document.getElementById('confirmationModal').style.display = 'block';
}

function closeConfirmation() {
    document.getElementById('confirmationModal').style.display = 'none';
}

function registerOrganizer() {
    document.getElementById('registerOrganizerForm').submit();
}
</script>