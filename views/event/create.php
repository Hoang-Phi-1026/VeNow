<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo sự kiện mới - Venow</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css?v=1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/event-create.css?v=1">
</head>
<body>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="event-create-container">
    <div class="container">
        <div class="event-create-card">
            <div class="event-create-header">
                <h1><i class="fas fa-calendar-plus"></i> Tạo sự kiện mới</h1>
                <p>Điền đầy đủ thông tin để tạo sự kiện của bạn</p>
            </div>
            
            <div class="event-create-content">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></span>
                    </div>
                <?php endif; ?>
                
<form action="<?php echo BASE_URL; ?>/events/store" method="POST" enctype="multipart/form-data" class="event-create-form">
<!-- Thông tin cơ bản -->
<div class="form-section">
    <div class="section-title">
        <i class="fas fa-info-circle"></i>
        <h2>Thông tin cơ bản</h2>
    </div>
    
    <div class="form-group">
        <label for="ten_su_kien" class="required-field">Tên sự kiện</label>
        <div class="input-icon">
            <i class="fas fa-calendar-alt"></i>
            <input type="text" id="ten_su_kien" name="ten_su_kien" placeholder="Nhập tên sự kiện" required>
        </div>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="ngay_dien_ra" class="required-field">Ngày diễn ra</label>
            <div class="input-icon">
                <i class="fas fa-calendar"></i>
                <input type="date" id="ngay_dien_ra" name="ngay_dien_ra" required min="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="ngay_ket_thuc" class="required-field">Ngày Kết thúc</label>
            <div class="input-icon">
                <i class="fas fa-calendar"></i>
                <input type="date" id="ngay_ket_thuc" name="ngay_ket_thuc" required min="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="gio_dien_ra" class="required-field">Giờ diễn ra</label>
            <div class="input-icon">
                <i class="fas fa-clock"></i>
                <input type="time" id="gio_dien_ra" name="gio_dien_ra" required>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="dia_diem" class="required-field">Địa điểm</label>
        <div class="input-icon">
            <i class="fas fa-map-marker-alt"></i>
            <input type="text" id="dia_diem" name="dia_diem" placeholder="Nhập địa điểm tổ chức" required>
        </div>
    </div>
    
    <div class="form-group">
        <label for="maloaisukien" class="required-field">Loại sự kiện</label>
        <div class="input-icon">
            <i class="fas fa-tag"></i>
            <select id="maloaisukien" name="maloaisukien" required>
                <option value="">-- Chọn loại sự kiện --</option>
                <?php 
                $eventModel = new Event();
                $categories = $eventModel->getAllEventTypes();
                foreach ($categories as $category): 
                ?>
                    <option value="<?php echo $category['maloaisukien']; ?>"><?php echo $category['tenloaisukien']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <div class="form-group">
        <label for="mo_ta">Mô tả sự kiện</label>
        <textarea id="mo_ta" name="mo_ta" placeholder="Mô tả chi tiết về sự kiện" rows="4"></textarea>
        <p class="form-note">Mô tả chi tiết giúp người tham gia hiểu rõ hơn về sự kiện của bạn</p>
    </div>
</div>
<!-- Thông tin vé và chỗ ngồi -->
<div class="form-section">
    <div class="section-title">
        <i class="fas fa-ticket-alt"></i>
        <h2>Thông tin vé và chỗ ngồi</h2>
    </div>
    
    <div class="form-row">
        <div class="form-group">
            <label for="so_luong_cho" class="required-field">Số lượng chỗ</label>
            <div class="input-icon">
                <i class="fas fa-chair"></i>
                <input type="number" id="so_luong_cho" name="so_luong_cho" min="1" value="100" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="trang_thai_cho_ngoi">Trạng thái chỗ ngồi</label>
            <div class="input-icon">
                <i class="fas fa-check-circle"></i>
                <select id="trang_thai_cho_ngoi" name="trang_thai_cho_ngoi">
                    <option value="CON_CHO">Còn chỗ</option>
                    <option value="HET_CHO">Hết chỗ</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Loại vé -->
    <div class="ticket-types-section">
    <div class="section-title">
        <i class="fas fa-ticket-alt"></i>
        <h2>Loại vé</h2>
    </div>
    
    <div class="form-note-box">
        <i class="fas fa-info-circle"></i>
        <p>Thêm các loại vé khác nhau cho sự kiện của bạn (VD: Vé VIP, Vé thường, v.v.)</p>
    </div>
    
    <div id="ticket-types" class="ticket-types-container">
        <div class="ticket-type">
            <div class="form-group">
                <label for="ticket_name_0">Tên loại vé</label>
                <input type="text" id="ticket_name_0" name="ticket_types[0][ten_loai_ve]" placeholder="VD: Vé VIP" required>
            </div>
            <div class="form-group">
                <label for="ticket_price_0">Giá vé</label>
                <input type="number" id="ticket_price_0" name="ticket_types[0][gia_ve]" placeholder="VD: 500000" min="0" required>
            </div>
            <div class="form-group">
                <label for="ticket_rows_0">Số hàng</label>
                <input type="number" id="ticket_rows_0" name="ticket_types[0][so_hang]" placeholder="VD: 5" min="1" required>
            </div>
            <div class="form-group">
                <label for="ticket_cols_0">Số cột</label>
                <input type="number" id="ticket_cols_0" name="ticket_types[0][so_cot]" placeholder="VD: 10" min="1" required>
            </div>
            <div class="form-group">
                <label for="ticket_desc_0">Mô tả</label>
                <textarea id="ticket_desc_0" name="ticket_types[0][mo_ta]" placeholder="Mô tả chi tiết về loại vé" rows="2"></textarea>
            </div>
        </div>
    </div>
    
    <button type="button" class="add-ticket-btn">
        <i class="fas fa-plus-circle"></i> Thêm loại vé
    </button>
</div>

    
    <div class="form-group">
        <label for="thoi_han_dat_ve">Thời hạn đặt vé</label>
        <div class="input-icon">
            <i class="fas fa-hourglass-end"></i>
            <input type="datetime-local" id="thoi_han_dat_ve" name="thoi_han_dat_ve" min="<?php echo date('Y-m-d\TH:i'); ?>">
        </div>
        <p class="form-note">Nếu không chọn, thời hạn đặt vé sẽ là đến khi sự kiện diễn ra</p>
    </div>
</div>
<!-- Hình ảnh sự kiện -->
<div class="form-section">
    <div class="section-title">
        <i class="fas fa-image"></i>
        <h2>Hình ảnh sự kiện</h2>
    </div>
    
    <div class="form-group">
        <label for="hinh_anh">Hình ảnh sự kiện</label>
        <div class="file-upload">
            <input type="file" id="hinh_anh" name="hinh_anh" accept="image/jpeg,image/png,image/gif" class="file-input">
            <label for="hinh_anh" class="file-label">
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Chọn hình ảnh</span>
            </label>
            <div id="file-name" class="file-name">Chưa có file nào được chọn</div>
        </div>
        <p class="form-note">Hỗ trợ định dạng: JPG, PNG, GIF. Kích thước tối đa: 5MB</p>
    </div>
</div>
<!-- Thông tin nhà tổ chức -->
<div class="form-section">
    <div class="section-title">
        <i class="fas fa-building"></i>
        <h2>Thông tin nhà tổ chức</h2>
    </div>
    
    <div class="form-group">
        <label for="ma_nguoi_dung" class="required-field">Nhà tổ chức</label>
        <div class="input-icon">
            <i class="fas fa-building"></i>
            <?php
            try {
                if (isset($_SESSION['user']) && $_SESSION['user']['vai_tro'] == 2) {
                    $userId = $_SESSION['user']['id'];
                    $db = Database::getInstance();
                    $stmt = $db->prepare("SELECT ho_ten FROM nguoidung WHERE ma_nguoi_dung = ? AND ma_vai_tro = 2");
                    $stmt->execute([$userId]);
                    $organizer = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($organizer) {
                        echo '<input type="text" id="ma_nguoi_dung_display" value="' . htmlspecialchars($organizer['ho_ten']) . '" disabled>';
                        echo '<input type="hidden" id="ma_nguoi_dung" name="ma_nguoi_dung" value="' . $userId . '">';
                    } else {
                        echo '<input type="text" id="ma_nguoi_dung_display" value="Chưa xác định - Vui lòng liên hệ quản trị viên" disabled>';
                        echo '<input type="hidden" id="ma_nguoi_dung" name="ma_nguoi_dung" value="">';
                    }
                } else {
                    echo '<input type="text" id="ma_nguoi_dung_display" value="Chưa xác định" disabled>';
                    echo '<input type="hidden" id="ma_nguoi_dung" name="ma_nguoi_dung" value="">';
                }
            } catch (PDOException $e) {
                error_log("Lỗi truy vấn nhà tổ chức: " . $e->getMessage());
                $_SESSION['error'] = 'Lỗi khi lấy thông tin nhà tổ chức';
                header('Location: ' . BASE_URL);
                exit;
            }
            ?>
        </div>
        <?php if (!isset($organizer) || !$organizer): ?>
        <p class="form-note error-note"><i class="fas fa-exclamation-triangle"></i> Bạn chưa được cấp quyền nhà tổ chức. Vui lòng liên hệ quản trị viên.</p>
        <?php endif; ?>
    </div>
    
    <div class="form-note-box">
        <i class="fas fa-info-circle"></i>
        <p>Lưu ý: Sự kiện sau khi tạo sẽ được gửi đến quản trị viên để duyệt trước khi hiển thị công khai.</p>
    </div>
    
    <button type="submit" class="btn-submit">
        <i class="fas fa-calendar-plus"></i> TẠO SỰ KIỆN
    </button>
</div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<script>
    let ticketIndex = 1;
    document.querySelector('.add-ticket-btn').addEventListener('click', function () {
        const ticketTypes = document.getElementById('ticket-types');
        const newTicket = document.createElement('div');
        newTicket.classList.add('ticket-type');
        newTicket.innerHTML = `
            <div class="form-group">
                <label for="ticket_name_${ticketIndex}">Tên loại vé</label>
                <input type="text" id="ticket_name_${ticketIndex}" name="ticket_types[${ticketIndex}][ten_loai_ve]" placeholder="VD: Vé VIP" required>
            </div>
            <div class="form-group">
                <label for="ticket_price_${ticketIndex}">Giá vé</label>
                <input type="number" id="ticket_price_${ticketIndex}" name="ticket_types[${ticketIndex}][gia_ve]" placeholder="VD: 500000" min="0" required>
            </div>
            <div class="form-group">
                <label for="ticket_rows_${ticketIndex}">Số hàng</label>
                <input type="number" id="ticket_rows_${ticketIndex}" name="ticket_types[${ticketIndex}][so_hang]" placeholder="VD: 5" min="1" required>
            </div>
            <div class="form-group">
                <label for="ticket_cols_${ticketIndex}">Số cột</label>
                <input type="number" id="ticket_cols_${ticketIndex}" name="ticket_types[${ticketIndex}][so_cot]" placeholder="VD: 10" min="1" required>
            </div>
            <div class="form-group">
                <label for="ticket_desc_${ticketIndex}">Mô tả</label>
                <textarea id="ticket_desc_${ticketIndex}" name="ticket_types[${ticketIndex}][mo_ta]" placeholder="Mô tả chi tiết về loại vé" rows="2"></textarea>
            </div>
            <button type="button" class="remove-ticket-btn"><i class="fas fa-trash-alt"></i></button>
        `;
        ticketTypes.appendChild(newTicket);
        ticketIndex++;

        newTicket.querySelector('.remove-ticket-btn').addEventListener('click', function () {
            newTicket.remove();
        });
    });

    

    // File upload preview
    document.getElementById('hinh_anh').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const fileNameDisplay = document.getElementById('file-name');
        
        if (file) {
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!allowedTypes.includes(file.type)) {
                alert('Chỉ hỗ trợ file JPG, PNG, GIF');
                e.target.value = '';
                fileNameDisplay.textContent = 'Chưa có file nào được chọn';
                return;
            }
            
            if (file.size > 5 * 1024 * 1024) {
                alert('Kích thước file không được vượt quá 5MB');
                e.target.value = '';
                fileNameDisplay.textContent = 'Chưa có file nào được chọn';
                return;
            }
            
            fileNameDisplay.textContent = file.name;
            fileNameDisplay.classList.add('has-file');
        } else {
            fileNameDisplay.textContent = 'Chưa có file nào được chọn';
            fileNameDisplay.classList.remove('has-file');
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const tenSuKien = document.getElementById('ten_su_kien').value.trim();
        const ngayDienRa = document.getElementById('ngay_dien_ra').value;
        const gioDienRa = document.getElementById('gio_dien_ra').value;
        const diaDiem = document.getElementById('dia_diem').value.trim();
        const loaiSuKien = document.getElementById('maloaisukien').value;
        const soLuongCho = document.getElementById('so_luong_cho').value;
        const maNguoiDung = document.getElementById('ma_nguoi_dung').value;
        const thoiHanDatVe = document.getElementById('thoi_han_dat_ve').value;
        
        let errors = [];
        
        if (!tenSuKien) errors.push('Vui lòng nhập tên sự kiện');
        if (!ngayDienRa) errors.push('Vui lòng chọn ngày diễn ra');
        if (!gioDienRa) errors.push('Vui lòng chọn giờ diễn ra');
        if (!diaDiem) errors.push('Vui lòng nhập địa điểm');
        if (!loaiSuKien) errors.push('Vui lòng chọn loại sự kiện');
        if (!soLuongCho || soLuongCho < 1) errors.push('Số lượng chỗ phải lớn hơn 0');
        if (!maNguoiDung) errors.push('Thông tin nhà tổ chức không hợp lệ');

        if (thoiHanDatVe) {
            const eventDateTime = new Date(`${ngayDienRa}T${gioDienRa}`);
            const bookingDeadline = new Date(thoiHanDatVe);
            if (bookingDeadline > eventDateTime) {
                errors.push('Thời hạn đặt vé không được sau thời gian diễn ra sự kiện');
            }
        }

        const ticketTypes = document.querySelectorAll('.ticket-type');
        if (ticketTypes.length === 0) {
            errors.push('Vui lòng thêm ít nhất một loại vé');
        }

        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join('\n'));
        }
    });
</script>

</body>
</html>
