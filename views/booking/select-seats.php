<?php require_once 'views/layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/booking.css">

<div class="container">
   <div class="booking-header">
       <h1>Đặt vé sự kiện</h1>
       
       <div class="event-info-compact">
           <div class="event-name">
               <i class="fas fa-ticket-alt"></i>
               <span><?php echo htmlspecialchars($event['ten_su_kien']); ?></span>
           </div>
           <div class="event-details-row">
               <div class="info-item">
                   <i class="fas fa-calendar-alt"></i>
                   <span><?php echo date('d/m/Y', strtotime($event['ngay_dien_ra'])); ?></span>
               </div>
               <div class="info-item">
                   <i class="fas fa-clock"></i>
                   <span><?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?></span>
               </div>
               <div class="info-item">
                   <i class="fas fa-map-marker-alt"></i>
                   <span><?php echo htmlspecialchars($event['dia_diem']); ?></span>
               </div>
               <div class="info-item">
                   <i class="fas fa-chair"></i>
                   <span><?php echo number_format($event['so_luong_cho']); ?> ghế</span>
               </div>
           </div>
       </div>
   </div>

   <div class="seat-legend">
       <div class="legend-item">
           <div class="seat available"></div>
           <span>Ghế trống</span>
       </div>
       <div class="legend-item">
           <div class="seat selected"></div>
           <span>Ghế đang chọn</span>
       </div>
       <div class="legend-item">
           <div class="seat reserved"></div>
           <span>Ghế đã đặt</span>
       </div>
       <div class="legend-item">
           <div class="seat sold"></div>
           <span>Ghế đã bán</span>
       </div>
   </div>

   <div class="screen-container">
       <div class="screen">SÂN KHẤU TRÌNH DIỄN</div>
   </div>

   <div class="seating-container">
       <?php 
       foreach ($seatsByType as $typeId => $typeData): 
           $type = $typeData['info'];
           $typeSeats = $typeData['seats'];
           
           // Tạo mảng 2D để lưu trữ chỗ ngồi cho loại vé này
           $seatGrid = [];
           foreach ($typeSeats as $seat) {
               // Phân tích vị trí hàng và cột từ số chỗ (ví dụ: A-1)
               $parts = explode('-', $seat['so_cho']);
               if (count($parts) === 2) {
                   $row = ord($parts[0]) - 65; // Chuyển A thành 0, B thành 1, ...
                   $col = (int)$parts[1] - 1; // Chuyển 1 thành 0, 2 thành 1, ...
                   $seatGrid[$row][$col] = $seat;
               }
           }
       ?>
           
       <div class="ticket-type-section">
           <h3><?php echo htmlspecialchars($type['ten_loai_ve']); ?> - <?php echo number_format($type['gia_ve']); ?> VNĐ</h3>
           
           <div class="seating-grid" data-ticket-type="<?php echo $typeId; ?>" data-price="<?php echo $type['gia_ve']; ?>">
               <?php 
               // Mỗi loại vé bắt đầu từ A (không phụ thuộc vào loại vé trước)
               for ($row = 0; $row < $type['so_hang']; $row++): 
                   $currentRowChar = chr(65 + $row); // A=65, B=66, ...
               ?>
                   <div class="seat-row">
                       <div class="row-label"><?php echo $currentRowChar; ?></div>
                       
                       <?php for ($col = 0; $col < $type['so_cot']; $col++): 
                           $seatClass = 'seat';
                           $seatId = '';
                           $seatStatus = '';
                           $seatNumber = $currentRowChar . '-' . ($col + 1);
                           
                           if (isset($seatGrid[$row][$col])) {
                               $seat = $seatGrid[$row][$col];
                               $seatId = $seat['ma_cho_ngoi'];
                               $seatStatus = $seat['trang_thai'];
                               
                               if ($seatStatus === 'TRONG') {
                                   $seatClass .= ' available';
                               } elseif ($seatStatus === 'DA_DAT') {
                                   $seatClass .= ' sold';
                               } elseif ($seatStatus === 'DA_DANH_DAU') {
                                   $seatClass .= ' reserved';
                               }
                           } else {
                               // Nếu không tìm thấy ghế trong cơ sở dữ liệu, tạo ghế mới với trạng thái TRONG
                               $seatClass .= ' available';
                               $seatStatus = 'TRONG';
                           }
                       ?>
                           
                           <div class="<?php echo $seatClass; ?>" 
                                data-seat-id="<?php echo $seatId; ?>"
                                data-ticket-type="<?php echo $typeId; ?>"
                                data-price="<?php echo $type['gia_ve']; ?>"
                                data-status="<?php echo $seatStatus; ?>"
                                data-seat-number="<?php echo $seatNumber; ?>">
                               <?php echo $col + 1; ?>
                           </div>
                       <?php endfor; ?>
                   </div>
               <?php endfor; ?>
           </div>
       </div>
       <?php endforeach; ?>
   </div>

   <div class="booking-summary">
       <h3>Thông tin đặt vé</h3>
       
       <div class="selected-seats-container">
           <h4>Ghế đã chọn:</h4>
           <div id="selected-seats-list">
               <p>Chưa có ghế nào được chọn</p>
           </div>
       </div>
       
       <div class="total-price-container">
           <h4>Tổng tiền:</h4>
           <div id="total-price">0 VNĐ</div>
       </div>
       
       <form id="booking-form" action="<?php echo BASE_URL; ?>/booking/process-selection" method="POST">
           <input type="hidden" name="eventId" value="<?php echo $event['ma_su_kien']; ?>">
           <input type="hidden" id="selected-seats-input" name="selectedSeats" value="">
           <button id="checkout-btn" class="btn btn-primary" disabled>Thanh toán ngay</button>
       </form>
   </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
   // Lấy các phần tử DOM
   const availableSeats = document.querySelectorAll('.seat.available');
   const selectedSeatsList = document.getElementById('selected-seats-list');
   const totalPriceElement = document.getElementById('total-price');
   const checkoutBtn = document.getElementById('checkout-btn');
   const bookingForm = document.getElementById('booking-form');
   
   // Biến lưu trữ ghế đã chọn và tổng tiền
   let selectedSeats = {};
   let totalPrice = 0;
   
   // Thêm sự kiện click cho các ghế có thể chọn
   availableSeats.forEach(seat => {
       seat.addEventListener('click', function() {
           const seatId = this.getAttribute('data-seat-id');
           const ticketType = this.getAttribute('data-ticket-type');
           const price = parseInt(this.getAttribute('data-price'));
           const seatNumber = this.getAttribute('data-seat-number');
           
           if (this.classList.contains('selected')) {
               // Bỏ chọn ghế
               this.classList.remove('selected');
               delete selectedSeats[seatId];
               totalPrice -= price;
           } else {
               // Chọn ghế
               this.classList.add('selected');
               selectedSeats[seatId] = {
                   ticketType: ticketType,
                   price: price,
                   seatNumber: seatNumber
               };
               totalPrice += price;
           }
           
           // Cập nhật danh sách ghế đã chọn
           updateSelectedSeatsList();
           
           // Cập nhật tổng tiền
           totalPriceElement.textContent = formatCurrency(totalPrice) + ' VNĐ';
           
           // Kích hoạt/vô hiệu hóa nút thanh toán
           checkoutBtn.disabled = Object.keys(selectedSeats).length === 0;
           
           // Cập nhật input ẩn
           document.getElementById('selected-seats-input').value = JSON.stringify(selectedSeats);
       });
   });
   
   // Cập nhật danh sách ghế đã chọn
   function updateSelectedSeatsList() {
       if (Object.keys(selectedSeats).length === 0) {
           selectedSeatsList.innerHTML = '<p>Chưa có ghế nào được chọn</p>';
           return;
       }
       
       let html = '<ul class="selected-seats-list">';
       
       for (const seatId in selectedSeats) {
           const seat = selectedSeats[seatId];
           html += `
               <li>
                   <span class="seat-number">Ghế ${seat.seatNumber}</span>
                   <span class="seat-price">${formatCurrency(seat.price)} VNĐ</span>
               </li>
           `;
       }
       
       html += '</ul>';
       selectedSeatsList.innerHTML = html;
   }
   
   // Định dạng số tiền
   function formatCurrency(amount) {
       return new Intl.NumberFormat('vi-VN').format(amount);
   }

   // Truyền trạng thái sự kiện từ PHP sang JS
   const eventStatus = "<?php echo isset($event['trang_thai']) ? $event['trang_thai'] : ''; ?>";

   // Chỉ cho phép thanh toán nếu trạng thái là DA_DUYET
   bookingForm.addEventListener('submit', function(e) {
       if (eventStatus !== 'DA_DUYET') {
           e.preventDefault();
           alert('Sự kiện chưa được duyệt, không thể thanh toán!');
       }
   });
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>
