<?php require_once 'views/layouts/header.php'; ?>
<?php require_once 'models/Comment.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/event-detail.css?v=1">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/comments.css">

<div class="event-detail">
    <div class="container">
        <?php if (!$event): ?>
            <div class="no-results">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Không tìm thấy sự kiện</h3>
                <p>Sự kiện bạn đang tìm kiếm không tồn tại hoặc đã bị xóa.</p>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">Quay lại trang chủ</a>
            </div>
        <?php else: ?>
            <!-- Event Header -->
            <div class="event-header">
                <div class="event-title-section">
                    <h1 class="event-title"><?php echo htmlspecialchars($event['ten_su_kien']); ?></h1>
                    <div class="event-meta-tags">
                        <span class="event-tag">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('d/m/Y', strtotime($event['ngay_dien_ra'])); ?>
                        </span>
                        <span class="event-tag">
                            <i class="fas fa-clock"></i>
                            <?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?>
                        </span>
                        <span class="event-tag">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($event['dia_diem']); ?>
                        </span>
                        <span class="event-tag">
                            <i class="fas fa-tag"></i>
                            <?php echo htmlspecialchars($event['tenloaisukien']); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Event Gallery -->
            <div class="event-gallery">
                <?php if (!empty($event['hinh_anh'])): ?>
                    <img src="<?php echo BASE_URL . '/' . $event['hinh_anh']; ?>" alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>" class="event-main-image" id="main-event-image">
                <?php else: ?>
                    <img src="https://via.placeholder.com/1200x450/1eb75c/FFFFFF?text=<?php echo urlencode($event['ten_su_kien']); ?>" alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>" class="event-main-image" id="main-event-image">
                <?php endif; ?>
            </div>

            <!-- Event Content -->
            <div class="event-content">
                <!-- Left Column - Event Details -->
                <div class="event-details-column">
                    <!-- Event Description -->
                    <div class="event-details">
                        <div class="event-details-header">
                            <i class="fas fa-info-circle"></i>
                            <h3>Thông tin sự kiện</h3>
                        </div>
                        <div class="event-details-content">
                            <div class="event-description">
                                <?php echo nl2br(htmlspecialchars($event['mo_ta'])); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Location Map -->
                    <div class="location-section">
                        <div class="location-header">
                            <i class="fas fa-map-marked-alt"></i>
                            <h3>Địa điểm</h3>
                        </div>
                        <div class="location-map">
                            <iframe 
                                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4946681007846!2d106.69908867469967!3d10.771913089387625!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f40a3b49e59%3A0xa1bd14e483a602db!2sSaigon%20Opera%20House!5e0!3m2!1sen!2s!4v1683870293223!5m2!1sen!2s" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade"
                                title="Bản đồ địa điểm sự kiện">
                            </iframe>
                        </div>
                        <div class="location-details">
                            <p class="location-address"><?php echo htmlspecialchars($event['dia_diem']); ?></p>
                            <a href="https://www.google.com/maps/search/<?php echo urlencode($event['dia_diem']); ?>" target="_blank" class="location-directions" aria-label="Xem chỉ đường đến địa điểm sự kiện">
                                <i class="fas fa-directions"></i> Xem chỉ đường
                            </a>
                        </div>
                    </div>

                    <!-- Organizer Info -->
                    <div class="organizer-section">
                        <div class="organizer-header">
                            <i class="fas fa-user-tie"></i>
                            <h3>Nhà tổ chức</h3>
                        </div>
                        <div class="organizer-content">
                            <div class="organizer-logo">
                                <?php if (!empty($event['avatar_nha_to_chuc'])): ?>
                                    <img src="<?php echo BASE_URL; ?>/public/uploads/avatars/<?php echo htmlspecialchars($event['avatar_nha_to_chuc']); ?>" alt="Avatar <?php echo htmlspecialchars($event['ten_nha_to_chuc']); ?>">
                                <?php else: ?>
                                    <img src="<?php echo BASE_URL; ?>/public/images/default-avatar.png" alt="Default Avatar">
                                <?php endif; ?>
                            </div>
                            <div class="organizer-info">
                                <h4 class="organizer-name"><?php echo htmlspecialchars($event['ten_nha_to_chuc']); ?></h4>
                                <p class="organizer-desc">Nhà tổ chức sự kiện chuyên nghiệp</p>
                                <a href="#" class="organizer-link" aria-label="Xem thêm thông tin về nhà tổ chức">
                                    <i class="fas fa-external-link-alt"></i> Xem thêm về nhà tổ chức
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Share Section -->
                    <div class="share-section">
                        <span class="share-label">Chia sẻ sự kiện:</span>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BASE_URL . '/event/' . IdHasher::encode($event['ma_su_kien'])); ?>" target="_blank" class="share-button share-facebook" aria-label="Chia sẻ lên Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(BASE_URL . '/event/' . IdHasher::encode($event['ma_su_kien'])); ?>&text=<?php echo urlencode($event['ten_su_kien']); ?>" target="_blank" class="share-button share-twitter" aria-label="Chia sẻ lên Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="mailto:?subject=<?php echo urlencode($event['ten_su_kien']); ?>&body=<?php echo urlencode('Xem sự kiện này: ' . BASE_URL . '/event/' . IdHasher::encode($event['ma_su_kien'])); ?>" class="share-button share-email" aria-label="Chia sẻ qua Email">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <button class="share-button share-link" onclick="copyEventLink()" aria-label="Sao chép liên kết">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Event Actions -->
                    <div class="event-actions">
                        <?php if (isset($_SESSION['user'])): ?>
                            <a href="<?php echo BASE_URL; ?>/booking/<?php echo IdHasher::encode($event['ma_su_kien']); ?>" class="btn-buy-ticket">Mua vé ngay</a>
                        <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>/login?redirect=<?php echo urlencode('/booking/' . IdHasher::encode($event['ma_su_kien'])); ?>" class="btn-buy-ticket">Đăng nhập để mua vé</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column - Event Info & Tickets -->
                <div class="event-sidebar">
                    <div class="sticky-sidebar-container">
                        <!-- Event Info Card -->
                        <div class="event-info">
                            <div class="event-info-header">
                                <i class="fas fa-calendar-alt"></i>
                                <h3>Thông tin sự kiện</h3>
                            </div>
                            <div class="event-info-content">
                                <div class="event-info-item">
                                    <div class="event-info-icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                    <div class="event-info-text">
                                        <div class="event-info-label">Ngày diễn ra</div>
                                        <div class="event-info-value"><?php echo date('d/m/Y', strtotime($event['ngay_dien_ra'])); ?></div>
                                    </div>
                                </div>
                                <div class="event-info-item">
                                    <div class="event-info-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="event-info-text">
                                        <div class="event-info-label">Thời gian</div>
                                        <div class="event-info-value"><?php echo date('H:i', strtotime($event['gio_dien_ra'])); ?></div>
                                    </div>
                                </div>
                                <div class="event-info-item">
                                    <div class="event-info-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="event-info-text">
                                        <div class="event-info-label">Địa điểm</div>
                                        <div class="event-info-value"><?php echo htmlspecialchars($event['dia_diem']); ?></div>
                                    </div>
                                </div>
                                <div class="event-info-item">
                                    <div class="event-info-icon">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div class="event-info-text">
                                        <div class="event-info-label">Thể loại</div>
                                        <div class="event-info-value"><?php echo htmlspecialchars($event['tenloaisukien']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ticket Section -->
                        <?php
                        // Lấy thông tin vé từ model
                        $eventModel = new Event();
                        $tickets = $eventModel->getEventTickets($event['ma_su_kien']);
                        ?>
                        <div class="ticket-section" id="ticketSection">
                            <div class="ticket-header">
                                <i class="fas fa-ticket-alt"></i>
                                <h3>Vé sự kiện</h3>
                            </div>
                            <div class="ticket-content">
                                <div class="ticket-types">
                                    <?php if (empty($tickets)): ?>
                                        <p>Hiện chưa có thông tin vé cho sự kiện này.</p>
                                    <?php else: ?>
                                        <?php foreach ($tickets as $ticket): ?>
                                            <div class="ticket-type">
                                                <div class="ticket-type-info">
                                                    <div class="ticket-type-name"><?php echo htmlspecialchars($ticket['ten_loai_ve']); ?></div>
                                                    <div class="ticket-type-desc"><?php echo htmlspecialchars($ticket['mo_ta'] ?? 'Vé tham dự sự kiện'); ?></div>
                                                </div>
                                                <div class="ticket-type-price">
                                                    <?php if ($ticket['gia_ve'] == 0): ?>
                                                        Miễn phí
                                                    <?php else: ?>
                                                        <?php echo number_format($ticket['gia_ve']); ?>đ
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="ticket-actions">
                                    <?php if (isset($_SESSION['user'])): ?>
                                        <a href="<?php echo BASE_URL; ?>/booking/<?php echo IdHasher::encode($event['ma_su_kien']); ?>" class="btn btn-primary btn-block btn-buy-tickets" id="btnBuyTickets">Mua vé ngay</a>
                                    <?php else: ?>
                                        <a href="<?php echo BASE_URL; ?>/login?redirect=<?php echo urlencode('/booking/' . IdHasher::encode($event['ma_su_kien'])); ?>" class="btn btn-primary btn-block btn-buy-tickets" id="btnBuyTickets">Đăng nhập để mua vé</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Comments Section -->
            <?php
            // Khởi tạo model bình luận
            $commentModel = new Comment();

            // Lấy dữ liệu bình luận và đánh giá
            $comments = $commentModel->getApprovedCommentsByEventId($event['ma_su_kien']);
            $commentCount = count($comments);
            $averageRating = $commentModel->getAverageRating($event['ma_su_kien']);
            $ratingDistribution = $commentModel->getRatingDistribution($event['ma_su_kien']);

            // Tính tổng số đánh giá
            $totalRatings = array_sum($ratingDistribution);
            ?>

            <div class="comments-section">
                <div class="comments-header">
                    <i class="fas fa-comments"></i>
                    <h3>Đánh giá và bình luận</h3>
                </div>
                <div class="comments-content">
                    <!-- Rating Summary -->
                    <div class="rating-summary">
                        <div class="rating-average">
                            <div class="rating-number"><?php echo number_format($averageRating, 1); ?></div>
                            <div class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?php echo $i <= round($averageRating) ? 'fas' : 'far'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="rating-count"><?php echo $totalRatings; ?> đánh giá</div>
                        </div>
                        <div class="rating-distribution">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <div class="rating-bar">
                                    <div class="rating-label">
                                        <i class="fas fa-star"></i> <?php echo $i; ?>
                                    </div>
                                    <div class="rating-progress">
                                        <?php
                                        $percentage = $totalRatings > 0 ? ($ratingDistribution[$i] / $totalRatings) * 100 : 0;
                                        ?>
                                        <div class="rating-progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <div class="rating-percent"><?php echo $ratingDistribution[$i]; ?></div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Comment Form -->
                    <div class="comment-form-container">
                        <?php if ($event['trang_thai'] != 'DA_DUYET'): ?>
                            <div class="pending-event-notice">
                                <i class="fas fa-exclamation-circle"></i>
                                Sự kiện này đang chờ duyệt. Bình luận và đánh giá sẽ được mở sau khi sự kiện được duyệt.
                            </div>
                        <?php elseif (isset($_SESSION['user'])): ?>
                            <?php
                            // Kiểm tra xem người dùng đã bình luận cho sự kiện này chưa
                            $hasCommented = $commentModel->hasUserCommented($event['ma_su_kien'], $_SESSION['user']['id']);
                            
                            if ($hasCommented):
                                // Kiểm tra trạng thái bình luận
                                $commentStatus = $commentModel->getUserCommentStatus($event['ma_su_kien'], $_SESSION['user']['id']);
                                
                                if ($commentStatus == 'CHO_DUYET'):
                                ?>
                                <div class="pending-comment-notice">
                                    <i class="fas fa-info-circle"></i>
                                    Bạn đã gửi bình luận cho sự kiện này. Bình luận của bạn đang chờ duyệt.
                                </div>
                                <?php else: ?>
                                <div class="approved-comment-notice">
                                    <i class="fas fa-check-circle"></i>
                                    Bạn đã đánh giá cho sự kiện này.
                                </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <h4 class="comment-form-header">Viết đánh giá của bạn</h4>
                                <form action="<?php echo BASE_URL; ?>/event/comment/add" method="POST" class="comment-form">
                                    <input type="hidden" name="event_id" value="<?php echo $event['ma_su_kien']; ?>">
                                    <input type="hidden" name="redirect" value="<?php echo BASE_URL; ?>/event/<?php echo intval(IdHasher::encode($event['ma_su_kien'])); ?>">

                                    <div class="star-rating">
                                        <?php for ($i = 5; $i >= 1; $i--): ?>
                                            <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" <?php echo $i == 5 ? 'checked' : ''; ?>>
                                            <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                                        <?php endfor; ?>
                                    </div>

                                    <textarea name="comment" class="comment-textarea" placeholder="Chia sẻ trải nghiệm của bạn về sự kiện này..." required></textarea>

                                    <button type="submit" class="comment-submit">
                                        <i class="fas fa-paper-plane"></i> Gửi đánh giá
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="login-to-comment">
                                <i class="fas fa-user-lock"></i>
                                <p>Vui lòng <a href="<?php echo BASE_URL; ?>/login">đăng nhập</a> để viết đánh giá</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Comment List -->
                    <div class="comment-list">
                        <?php if (!empty($comments)): ?>
                            <?php foreach ($comments as $comment): ?>
                                <div class="comment-item">
                                    <div class="comment-avatar">
                                        <?php if (!empty($comment['avatar'])): ?>
                                            <img src="<?php echo BASE_URL; ?>/public/uploads/avatars/<?php echo htmlspecialchars($comment['avatar']); ?>" alt="Avatar">
                                        <?php else: ?>
                                            <img src="<?php echo BASE_URL; ?>/public/images/default-avatar.png" alt="Default Avatar">
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-content">
                                        <div class="comment-header">
                                            <div class="comment-author"><?php echo htmlspecialchars($comment['ho_ten']); ?></div>
                                            <div class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['ngay_tao'])); ?></div>
                                        </div>
                                        <div class="comment-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="<?php echo $i <= $comment['diem_danh_gia'] ? 'fas' : 'far'; ?> fa-star"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['noi_dung'])); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-comments">
                                <i class="far fa-comment-dots"></i>
                                <p>Chưa có đánh giá nào cho sự kiện này. Hãy là người đầu tiên đánh giá!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Related Events -->
            <?php
            // Lấy các sự kiện liên quan (cùng thể loại)
            $relatedEvents = $eventModel->getEventsByType($event['maloaisukien']);
            // Loại bỏ sự kiện hiện tại khỏi danh sách
            $relatedEvents = array_filter($relatedEvents, function($e) use ($event) {
                return $e['ma_su_kien'] != $event['ma_su_kien'];
            });
            // Giới hạn chỉ lấy 4 sự kiện
            $relatedEvents = array_slice($relatedEvents, 0, 4);
            ?>
            <?php if (!empty($relatedEvents)): ?>
                <div class="related-events">
                    <div class="related-events-header">
                        <h2 class="related-events-title">Sự kiện liên quan</h2>
                    </div>
                    <div class="event-grid">
                        <?php foreach ($relatedEvents as $relatedEvent): ?>
                            <div class="event-card">
                                <div class="event-image">
                                    <?php if (!empty($relatedEvent['hinh_anh']) && file_exists(BASE_PATH . '/' . $relatedEvent['hinh_anh'])): ?>
                                        <img src="<?php echo BASE_URL; ?>/<?php echo htmlspecialchars($relatedEvent['hinh_anh']); ?>" 
                                             alt="<?php echo htmlspecialchars($relatedEvent['ten_su_kien']); ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <img src="<?php echo BASE_URL; ?>/public/images/placeholder.jpg" 
                                             alt="<?php echo htmlspecialchars($relatedEvent['ten_su_kien']); ?>"
                                             loading="lazy">
                                    <?php endif; ?>
                                    <div class="event-date">
                                        <span class="day"><?php echo date('d', strtotime($relatedEvent['ngay_dien_ra'])); ?></span>
                                        <span class="month"><?php echo date('M', strtotime($relatedEvent['ngay_dien_ra'])); ?></span>
                                    </div>
                                </div>
                                <div class="event-info">
                                    <h3 class="event-title"><?php echo htmlspecialchars($relatedEvent['ten_su_kien']); ?></h3>
                                    <div class="event-meta">
                                        <span><i class="far fa-clock"></i> <?php echo date('H:i', strtotime($relatedEvent['gio_dien_ra'])); ?></span>
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($relatedEvent['dia_diem']); ?></span>
                                    </div>
                                    <div class="event-price">
                                        <?php if ($relatedEvent['gia_ve_min'] == $relatedEvent['gia_ve_max']): ?>
                                            <span class="price"><?php echo number_format($relatedEvent['gia_ve_min']); ?>đ</span>
                                        <?php else: ?>
                                            <span class="price"><?php echo number_format($relatedEvent['gia_ve_min']); ?>đ - <?php echo number_format($relatedEvent['gia_ve_max']); ?>đ</span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo BASE_URL; ?>/event/<?php echo IdHasher::encode($relatedEvent['ma_su_kien']); ?>" class="btn btn-primary">Xem chi tiết</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
// Function to copy event link to clipboard
function copyEventLink() {
    const eventUrl = window.location.href;
    const tempInput = document.createElement('input');
    tempInput.value = eventUrl;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);
    const notification = document.createElement('div');
    notification.className = 'copy-notification';
    notification.innerHTML = '<i class="fas fa-check-circle"></i> Đã sao chép liên kết sự kiện!';
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.padding = '10px 20px';
    notification.style.background = 'var(--accent-color)';
    notification.style.color = 'white';
    notification.style.borderRadius = '4px';
    notification.style.boxShadow = 'var(--shadow-md)';
    notification.style.zIndex = '9999';
    notification.style.display = 'flex';
    notification.style.alignItems = 'center';
    notification.style.gap = '8px';
    notification.style.animation = 'fadeInUp 0.3s ease-out forwards';
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.animation = 'fadeOutDown 0.3s ease-out forwards';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// --- FLOATING TICKET SECTION: TỰ ĐỘNG SCROLL LÊN & HIỆN BUTTON "ĐẶT VÉ NGAY" ---
let isFloating = false;
let ticketAutoScrolled = false;


function handleTicketFloat() {
    const ticketSection = document.getElementById('ticketSection');
    const eventGallery = document.querySelector('.event-gallery');
    if (!ticketSection || !eventGallery) return;

    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const triggerPoint = eventGallery.offsetTop + eventGallery.offsetHeight + 200;

    if (scrollTop > triggerPoint) {
        if (!isFloating) {
            ticketSection.classList.add('floating');
            isFloating = true;
            ticketAutoScrolled = false;
        }
        if (!ticketAutoScrolled) {
            setTimeout(function() {
                const rect = ticketSection.getBoundingClientRect();
                if (rect.top > window.innerHeight * 0.4 || rect.bottom > window.innerHeight || rect.top < 0) {
                    ticketSection.scrollIntoView({behavior: 'smooth', block: 'center'});
                }
            }, 150);
            ticketAutoScrolled = true;
        }
    } else {
        if (isFloating) {
            ticketSection.classList.remove('floating');
            isFloating = false;
            ticketAutoScrolled = false;
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let ticking = false;
    function onScroll() {
        if (!ticking) {
            requestAnimationFrame(function() {
                handleTicketFloat();
                ticking = false;
            });
            ticking = true;
        }
    }
    window.addEventListener('scroll', onScroll);
    setTimeout(handleTicketFloat, 50);

    // Nếu bấm nút "Đặt vé ngay" khi đang floating, tự động đóng floating sau khi bấm (tùy UX)
    document.getElementById('btnBuyTickets')?.addEventListener('click', function() {
        if (isFloating) closeFloatingTicket();
    });
});
</script>

<?php require_once 'views/layouts/footer.php'; ?>