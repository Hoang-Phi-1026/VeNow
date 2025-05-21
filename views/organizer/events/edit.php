<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa sự kiện - Venow</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css?v=1">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/event-create.css?v=1">
</head>
<body>

<?php require_once __DIR__ . '/../../../utils/IdHasher.php'; ?>

<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="event-create-container">
    <div class="container">
        <div class="event-create-card">
            <div class="event-create-header">
                <h1><i class="fas fa-edit"></i> Chỉnh sửa sự kiện</h1>
                <p>Cập nhật thông tin sự kiện của bạn</p>
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
                
                <form action="<?php echo BASE_URL; ?>/organizer/events/update" method="POST" enctype="multipart/form-data" class="event-create-form">
                    <input type="hidden" name="ma_su_kien" value="<?php echo $event['ma_su_kien']; ?>">
                    
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
                                <input type="text" id="ten_su_kien" name="ten_su_kien" placeholder="Nhập tên sự kiện" required value="<?php echo htmlspecialchars($event['ten_su_kien']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="ngay_dien_ra" class="required-field">Ngày diễn ra</label>
                                <div class="input-icon">
                                    <i class="fas fa-calendar"></i>
                                    <input type="date" id="ngay_dien_ra" name="ngay_dien_ra" required value="<?php echo date('Y-m-d', strtotime($event['ngay_dien_ra'])); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="ngay_ket_thuc" class="required-field">Ngày Kết thúc</label>
                                <div class="input-icon">
                                    <i class="fas fa-calendar"></i>
                                    <input type="date" id="ngay_ket_thuc" name="ngay_ket_thuc" required value="<?php echo !empty($event['ngay_ket_thuc']) ? date('Y-m-d', strtotime($event['ngay_ket_thuc'])) : ''; ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="gio_dien_ra" class="required-field">Giờ diễn ra</label>
                                <div class="input-icon">
                                    <i class="fas fa-clock"></i>
                                    <input type="time" id="gio_dien_ra" name="gio_dien_ra" required value="<?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="dia_diem" class="required-field">Địa điểm</label>
                            <div class="input-icon">
                                <i class="fas fa-map-marker-alt"></i>
                                <input type="text" id="dia_diem" name="dia_diem" placeholder="Nhập địa điểm tổ chức" required value="<?php echo htmlspecialchars($event['dia_diem']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="maloaisukien" class="required-field">Loại sự kiện</label>
                            <div class="input-icon">
                                <i class="fas fa-tag"></i>
                                <select id="maloaisukien" name="maloaisukien" required>
                                    <option value="">-- Chọn loại sự kiện --</option>
                                    <?php foreach ($eventTypes as $type): ?>
                                        <option value="<?php echo $type['maloaisukien']; ?>" <?php echo ($event['maloaisukien'] == $type['maloaisukien']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($type['tenloaisukien']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="mo_ta">Mô tả sự kiện</label>
                            <textarea id="mo_ta" name="mo_ta" placeholder="Mô tả chi tiết về sự kiện" rows="4"><?php echo htmlspecialchars($event['mo_ta'] ?? ''); ?></textarea>
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
                                    <input type="number" id="so_luong_cho" name="so_luong_cho" min="1" required value="<?php echo $event['so_luong_cho']; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="trang_thai_cho_ngoi">Trạng thái chỗ ngồi</label>
                                <div class="input-icon">
                                    <i class="fas fa-check-circle"></i>
                                    <select id="trang_thai_cho_ngoi" name="trang_thai_cho_ngoi">
                                        <option value="CON_CHO" <?php echo ($event['trang_thai_cho_ngoi'] == 'CON_CHO') ? 'selected' : ''; ?>>Còn chỗ</option>
                                        <option value="HET_CHO" <?php echo ($event['trang_thai_cho_ngoi'] == 'HET_CHO') ? 'selected' : ''; ?>>Hết chỗ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Hiển thị các loại vé hiện tại -->
                        <div class="section-title">
                            <i class="fas fa-ticket-alt"></i>
                            <h2>Loại vé hiện tại</h2>
                        </div>
                        
                        <?php
                        // Lấy danh sách loại vé của sự kiện
                        $db = Database::getInstance();
                        $stmt = $db->prepare("SELECT * FROM loaive WHERE ma_su_kien = ?");
                        $stmt->execute([$event['ma_su_kien']]);
                        $ticketTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                        
                        <?php if (empty($ticketTypes)): ?>
                            <div class="form-note-box">
                                <i class="fas fa-info-circle"></i>
                                <p>Sự kiện này chưa có loại vé nào. Vui lòng thêm loại vé mới.</p>
                            </div>
                        <?php else: ?>
                            <div class="ticket-types-container">
                                <?php foreach ($ticketTypes as $ticket): ?>
                                    <div class="ticket-type existing-ticket">
                                        <div class="ticket-header">
                                            <h3><?php echo htmlspecialchars($ticket['ten_loai_ve']); ?></h3>
                                            <div class="ticket-actions">
                                                <button type="button" class="btn-delete-ticket" data-id="<?php echo $ticket['ma_loai_ve']; ?>">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </div>
                                        </div>
                                        <div class="ticket-details">
                                            <div class="ticket-info">
                                                <span><i class="fas fa-money-bill"></i> Giá: <?php echo number_format($ticket['gia_ve'], 0, ',', '.'); ?> VNĐ</span>
                                                <span><i class="fas fa-th"></i> Số hàng: <?php echo $ticket['so_hang']; ?></span>
                                                <span><i class="fas fa-th-list"></i> Số cột: <?php echo $ticket['so_cot']; ?></span>
                                            </div>
                                            <div class="ticket-description">
                                                <p><?php echo htmlspecialchars($ticket['mo_ta'] ?? 'Không có mô tả'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Thêm loại vé mới -->
                        <div class="section-title mt-4">
                            <i class="fas fa-plus-circle"></i>
                            <h2>Thêm loại vé mới</h2>
                        </div>
                        
                        <div class="form-note-box">
                            <i class="fas fa-info-circle"></i>
                            <p>Thêm các loại vé mới cho sự kiện của bạn (VD: Vé VIP, Vé thường, v.v.)</p>
                        </div>
                        
                        <div id="new-ticket-types" class="ticket-types-container">
                            <div class="ticket-type">
                                <div class="form-group">
                                    <label for="ticket_name_0">Tên loại vé</label>
                                    <input type="text" id="ticket_name_0" name="new_ticket_types[0][ten_loai_ve]" placeholder="VD: Vé VIP">
                                </div>
                                <div class="form-group">
                                    <label for="ticket_price_0">Giá vé</label>
                                    <input type="number" id="ticket_price_0" name="new_ticket_types[0][gia_ve]" placeholder="VD: 500000" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="ticket_rows_0">Số hàng</label>
                                    <input type="number" id="ticket_rows_0" name="new_ticket_types[0][so_hang]" placeholder="VD: 5" min="1">
                                </div>
                                <div class="form-group">
                                    <label for="ticket_cols_0">Số cột</label>
                                    <input type="number" id="ticket_cols_0" name="new_ticket_types[0][so_cot]" placeholder="VD: 10" min="1">
                                </div>
                                <div class="form-group">
                                    <label for="ticket_desc_0">Mô tả</label>
                                    <textarea id="ticket_desc_0" name="new_ticket_types[0][mo_ta]" placeholder="Mô tả chi tiết về loại vé" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="add-ticket-btn">
                            <i class="fas fa-plus-circle"></i> Thêm loại vé mới
                        </button>
                        
                        <div class="form-group">
                            <label for="thoi_han_dat_ve">Thời hạn đặt vé</label>
                            <div class="input-icon">
                                <i class="fas fa-hourglass-end"></i>
                                <input type="datetime-local" id="thoi_han_dat_ve" name="thoi_han_dat_ve" 
                                       value="<?php echo !empty($event['thoi_han_dat_ve']) ? date('Y-m-d\TH:i', strtotime($event['thoi_han_dat_ve'])) : ''; ?>">
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
                        
                        <?php if (!empty($event['hinh_anh']) && file_exists(BASE_PATH . '/' . $event['hinh_anh'])): ?>
                            <div class="current-image">
                                <p>Hình ảnh hiện tại:</p>
                                <img src="<?php echo BASE_URL . '/' . $event['hinh_anh']; ?>" alt="Hình ảnh sự kiện" style="max-width: 300px; max-height: 200px;">
                                <input type="hidden" name="current_image" value="<?php echo $event['hinh_anh']; ?>">
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="hinh_anh">Thay đổi hình ảnh sự kiện</label>
                            <div class="file-upload">
                                <input type="file" id="hinh_anh" name="hinh_anh" accept="image/jpeg,image/png,image/gif" class="file-input">
                                <label for="hinh_anh" class="file-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Chọn hình ảnh mới</span>
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
                                <input type="text" id="ma_nguoi_dung_display" value="<?php echo htmlspecialchars($organizerName ?? 'Không xác định'); ?>" disabled>
                                <input type="hidden" id="ma_nguoi_dung" name="ma_nguoi_dung" value="<?php echo $_SESSION['user']['id']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-note-box">
                            <i class="fas fa-info-circle"></i>
                            <p>Lưu ý: Sự kiện sau khi cập nhật sẽ được gửi đến quản trị viên để duyệt lại trước khi hiển thị công khai.</p>
                        </div>
                        
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> CẬP NHẬT SỰ KIỆN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>

<script>
    let ticketIndex = 1;
    
    // Thêm loại vé mới
    document.querySelector('.add-ticket-btn').addEventListener('click', function () {
        const ticketTypes = document.getElementById('new-ticket-types');
        const newTicket = document.createElement('div');
        newTicket.classList.add('ticket-type');
        newTicket.innerHTML = `
            <div class="form-group">
                <label for="ticket_name_${ticketIndex}">Tên loại vé</label>
                <input type="text" id="ticket_name_${ticketIndex}" name="new_ticket_types[${ticketIndex}][ten_loai_ve]" placeholder="VD: Vé VIP">
            </div>
            <div class="form-group">
                <label for="ticket_price_${ticketIndex}">Giá vé</label>
                <input type="number" id="ticket_price_${ticketIndex}" name="new_ticket_types[${ticketIndex}][gia_ve]" placeholder="VD: 500000" min="0">
            </div>
            <div class="form-group">
                <label for="ticket_rows_${ticketIndex}">Số hàng</label>
                <input type="number" id="ticket_rows_${ticketIndex}" name="new_ticket_types[${ticketIndex}][so_hang]" placeholder="VD: 5" min="1">
            </div>
            <div class="form-group">
                <label for="ticket_cols_${ticketIndex}">Số cột</label>
                <input type="number" id="ticket_cols_${ticketIndex}" name="new_ticket_types[${ticketIndex}][so_cot]" placeholder="VD: 10" min="1">
            </div>
            <div class="form-group">
                <label for="ticket_desc_${ticketIndex}">Mô tả</label>
                <textarea id="ticket_desc_${ticketIndex}" name="new_ticket_types[${ticketIndex}][mo_ta]" placeholder="Mô tả chi tiết về loại vé" rows="2"></textarea>
            </div>
            <button type="button" class="remove-ticket-btn"><i class="fas fa-trash-alt"></i></button>
        `;
        ticketTypes.appendChild(newTicket);
        ticketIndex++;

        newTicket.querySelector('.remove-ticket-btn').addEventListener('click', function () {
            newTicket.remove();
        });
    });

    // Xóa loại vé hiện tại
    document.querySelectorAll('.btn-delete-ticket').forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = this.getAttribute('data-id');
            if (confirm('Bạn có chắc chắn muốn xóa loại vé này? Hành động này không thể hoàn tác.')) {
                window.location.href = `<?php echo BASE_URL; ?>/organizer/events/delete-ticket?id=${ticketId}&event_id=<?php echo IdHasher::encode($event['ma_su_kien']); ?>`;
            }
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

        if (errors.length > 0) {
            e.preventDefault();
            alert(errors.join('\n'));
        }
    });
</script>

<style>
    .existing-ticket {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .ticket-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    
    .ticket-header h3 {
        margin: 0;
        color: #333;
        font-size: 18px;
    }
    
    .ticket-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn-delete-ticket {
        background-color: #dc3545;
        color: white;
        border: none;
        border-radius: 4px;
        padding: 5px 10px;
        cursor: pointer;
        font-size: 14px;
    }
    
    .btn-delete-ticket:hover {
        background-color: #c82333;
    }
    
    .ticket-details {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .ticket-info {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .ticket-info span {
        font-size: 14px;
        color: #555;
    }
    
    .ticket-description {
        font-size: 14px;
        color: #666;
        font-style: italic;
    }
    
    .current-image {
        margin-bottom: 20px;
        padding: 10px;
        border: 1px dashed #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }
    
    .current-image p {
        margin-bottom: 10px;
        font-weight: bold;
    }
    
    .mt-4 {
        margin-top: 20px;
    }
</style>

</body>
</html>
