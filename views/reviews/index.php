<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="admin-container">
    <div class="container">
        <div class="admin-header">
            <h1 class="admin-title">Quản lý đánh giá</h1>
            <p class="admin-subtitle">Duyệt hoặc từ chối các đánh giá của người dùng</p>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="admin-content">
            <div class="card">
                <div class="card-header">
                    <h2>Đánh giá chờ duyệt</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingComments)): ?>
                        <div class="no-results">
                            <i class="fas fa-check-circle"></i>
                            <p>Không có đánh giá nào đang chờ duyệt.</p>
                        </div>
                    <?php else: ?>
                        <!-- Debug information -->
                        <?php if (isset($_GET['debug'])): ?>
                            <div class="alert alert-info">
                                <h4>Debug Information</h4>
                                <pre><?php print_r($pendingComments[0]); ?></pre>
                            </div>
                        <?php endif; ?>
                        
                        <div class="reviews-list">
                            <?php foreach ($pendingComments as $comment): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="user-info">
                                            <?php 
                                            // Debug avatar information
                                            if (isset($_GET['debug'])) {
                                                echo '<div class="debug-info">';
                                                echo 'Avatar field: ' . (isset($comment['avatar']) ? $comment['avatar'] : 'Not set') . '<br>';
                                                echo 'AVT field: ' . (isset($comment['avt']) ? $comment['avt'] : 'Not set') . '<br>';
                                                echo '</div>';
                                            }
                                            
                                            // Luôn lấy trường avt từ comment
                                            $avatarField = !empty($comment['avt']) ? $comment['avt'] : null;
                                            $avatarPath = '/venow/public/images/default-avatar.png'; // Đường dẫn tương đối

                                            if ($avatarField) {
                                                if (preg_match('/^https?:\/\//', $avatarField)) {
                                                    $avatarPath = $avatarField;
                                                } else {
                                                    $avatarPath = '/venow/public/uploads/avatars/' . rawurlencode(basename($avatarField));
                                                }
                                            }

                                            // Debug đường dẫn ảnh
                                            if (isset($_GET['debug'])) {
                                                echo '<div class="debug-info">Avatar URL: ' . htmlspecialchars($avatarPath) . '</div>';
                                            }
                                            ?>
                                            <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="User Avatar" class="user-avatar">
                                            <div class="user-details">
                                                <h3 class="user-name"><?php echo htmlspecialchars($comment['ho_ten'] ?? 'Unknown User'); ?></h3>
                                                <div class="rating">
                                                    <?php 
                                                    $rating = isset($comment['diem_danh_gia']) ? (int)$comment['diem_danh_gia'] : 0;
                                                    for ($i = 1; $i <= 5; $i++): 
                                                    ?>
                                                        <i class="<?php echo $i <= $rating ? 'fas' : 'far'; ?> fa-star"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="event-info">
                                            <span class="event-label">Sự kiện:</span>
                                            <a href="<?php echo BASE_URL; ?>/event/<?php echo $comment['ma_su_kien'] ?? ''; ?>" class="event-link">
                                                <?php echo htmlspecialchars($comment['ten_su_kien'] ?? 'Unknown Event'); ?>
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <div class="review-content">
                                        <p><?php echo nl2br(htmlspecialchars($comment['noi_dung'] ?? '')); ?></p>
                                    </div>
                                    
                                    <div class="review-footer">
                                        <div class="review-meta">
                                            <span class="review-id">ID: <?php echo $comment['ma_binh_luan'] ?? 'Unknown'; ?></span>
                                            <span class="review-date">
                                                <i class="far fa-clock"></i>
                                                <?php 
                                                echo isset($comment['ngay_tao']) 
                                                    ? date('d/m/Y H:i', strtotime($comment['ngay_tao'])) 
                                                    : 'Unknown date'; 
                                                ?>
                                            </span>
                                        </div>
                                        <div class="review-actions">
                                            <form action="<?php echo BASE_URL; ?>/reviews/approve" method="POST" class="action-form">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['ma_binh_luan'] ?? ''; ?>">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-check"></i> Duyệt
                                                </button>
                                            </form>
                                            <form action="<?php echo BASE_URL; ?>/reviews/reject" method="POST" class="action-form">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment['ma_binh_luan'] ?? ''; ?>">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Bạn có chắc chắn muốn từ chối bình luận này?')">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom styles for the reviews page */
.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.review-card {
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}

.review-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.review-header {
    padding: 1.25rem;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.user-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f0f0f0;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.rating {
    color: #ffc107;
    margin-top: 0.25rem;
}

.event-info {
    background-color: #f8f9fa;
    padding: 0.5rem 0.75rem;
    border-radius: 4px;
    font-size: 0.875rem;
}

.event-label {
    font-weight: 600;
    margin-right: 0.25rem;
}

.event-link {
    color: #007bff;
    text-decoration: none;
}

.event-link:hover {
    text-decoration: underline;
}

.review-content {
    padding: 1.25rem;
    background-color: #f9f9f9;
    font-size: 0.95rem;
    line-height: 1.5;
}

.review-footer {
    padding: 1rem 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #fff;
    flex-wrap: wrap;
    gap: 1rem;
}

.review-meta {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
    color: #6c757d;
}

.review-actions {
    display: flex;
    gap: 0.5rem;
}

.action-form {
    margin: 0;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    transition: background-color 0.2s;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-success:hover {
    background-color: #218838;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.debug-info {
    background-color: #f8d7da;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-family: monospace;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .review-header, .review-footer {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .review-actions {
        width: 100%;
        justify-content: space-between;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
