<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/event-detail.css">

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
                <img src="https://via.placeholder.com/1200x450/1eb75c/FFFFFF?text=<?php echo urlencode($event['ten_su_kien']); ?>" alt="<?php echo htmlspecialchars($event['ten_su_kien']); ?>" class="event-main-image" id="main-event-image">
                <div class="event-thumbnails">
                    <div class="event-thumbnail active" data-src="https://via.placeholder.com/1200x450/1eb75c/FFFFFF?text=<?php echo urlencode($event['ten_su_kien']); ?>">
                        <img src="https://via.placeholder.com/100x100/1eb75c/FFFFFF" alt="Thumbnail 1">
                    </div>
                    <div class="event-thumbnail" data-src="https://via.placeholder.com/1200x450/ff5722/FFFFFF?text=<?php echo urlencode($event['ten_su_kien']); ?>">
                        <img src="https://via.placeholder.com/100x100/ff5722/FFFFFF" alt="Thumbnail 2">
                    </div>
                    <div class="event-thumbnail" data-src="https://via.placeholder.com/1200x450/2196f3/FFFFFF?text=<?php echo urlencode($event['ten_su_kien']); ?>">
                        <img src="https://via.placeholder.com/100x100/2196f3/FFFFFF" alt="Thumbnail 3">
                    </div>
                </div>
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
                                <img src="https://via.placeholder.com/100x100/1eb75c/FFFFFF?text=<?php echo urlencode($event['tennhatochuc']); ?>" alt="Logo <?php echo htmlspecialchars($event['tennhatochuc']); ?>">
                            </div>
                            <div class="organizer-info">
                                <h4 class="organizer-name"><?php echo htmlspecialchars($event['tennhatochuc']); ?></h4>
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
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(BASE_URL . '/event/' . $event['ma_su_kien']); ?>" target="_blank" class="share-button share-facebook" aria-label="Chia sẻ lên Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(BASE_URL . '/event/' . $event['ma_su_kien']); ?>&text=<?php echo urlencode($event['ten_su_kien']); ?>" target="_blank" class="share-button share-twitter" aria-label="Chia sẻ lên Twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="mailto:?subject=<?php echo urlencode($event['ten_su_kien']); ?>&body=<?php echo urlencode('Xem sự kiện này: ' . BASE_URL . '/event/' . $event['ma_su_kien']); ?>" class="share-button share-email" aria-label="Chia sẻ qua Email">
                                <i class="fas fa-envelope"></i>
                            </a>
                            <button class="share-button share-link" onclick="copyEventLink()" aria-label="Sao chép liên kết">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Event Info & Tickets -->
                <div class="event-sidebar">
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
                    <div class="ticket-section">
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
                                <button class="btn-buy-tickets">
                                    <i class="fas fa-shopping-cart"></i> Mua vé ngay
                                </button>
                            </div>
                        </div>
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
                                    <img src="https://via.placeholder.com/400x250/1eb75c/FFFFFF?text=<?php echo urlencode($relatedEvent['ten_su_kien']); ?>" alt="<?php echo htmlspecialchars($relatedEvent['ten_su_kien']); ?>">
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
                                    <a href="<?php echo BASE_URL; ?>/event/<?php echo $relatedEvent['ma_su_kien']; ?>" class="btn btn-primary">Xem chi tiết</a>
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
    
    // Create a temporary input element
    const tempInput = document.createElement('input');
    tempInput.value = eventUrl;
    document.body.appendChild(tempInput);
    
    // Select and copy the link
    tempInput.select();
    document.execCommand('copy');
    
    // Remove the temporary element
    document.body.removeChild(tempInput);
    
    // Show a notification
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
    
    // Add animation keyframes
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeOutDown {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(20px);
            }
        }
    `;
    document.head.appendChild(style);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'fadeOutDown 0.3s ease-out forwards';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Image gallery functionality
document.addEventListener('DOMContentLoaded', function() {
    const mainImage = document.getElementById('main-event-image');
    const thumbnails = document.querySelectorAll('.event-thumbnail');
    
    thumbnails.forEach(thumbnail => {
        thumbnail.addEventListener('click', function() {
            // Remove active class from all thumbnails
            thumbnails.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked thumbnail
            this.classList.add('active');
            
            // Update main image
            const newSrc = this.getAttribute('data-src');
            mainImage.src = newSrc;
            
            // Add fade effect
            mainImage.style.opacity = '0';
            setTimeout(() => {
                mainImage.style.opacity = '1';
            }, 50);
        });
    });
    
    // Add smooth scroll to ticket section when clicking "Buy Tickets" button
    const buyTicketsBtn = document.querySelector('.btn-buy-tickets');
    if (buyTicketsBtn) {
        buyTicketsBtn.addEventListener('click', function() {
            const ticketSection = document.querySelector('.ticket-section');
            if (ticketSection) {
                ticketSection.scrollIntoView({ behavior: 'smooth' });
                
                // Add highlight effect
                ticketSection.style.transition = 'box-shadow 0.3s ease';
                ticketSection.style.boxShadow = '0 0 0 3px var(--accent-color)';
                
                setTimeout(() => {
                    ticketSection.style.boxShadow = 'var(--shadow-md)';
                }, 1500);
            }
        });
    }
    
    // Add hover effect to event cards
    const eventCards = document.querySelectorAll('.event-card');
    eventCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-8px)';
            card.style.boxShadow = 'var(--shadow-lg)';
            card.style.borderColor = 'var(--accent-color)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = '';
            card.style.boxShadow = '';
            card.style.borderColor = '';
        });
    });
});
</script>

<style>
/* Add some additional styles for better transitions */
.event-main-image {
    transition: opacity 0.3s ease;
}

.event-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}

/* Add a custom scrollbar for the event description */
.event-description {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 10px;
}

.event-description::-webkit-scrollbar {
    width: 6px;
}

.event-description::-webkit-scrollbar-track {
    background: var(--bg-tertiary);
    border-radius: var(--radius-full);
}

.event-description::-webkit-scrollbar-thumb {
    background-color: var(--accent-color);
    border-radius: var(--radius-full);
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
