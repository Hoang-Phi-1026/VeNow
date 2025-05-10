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
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Người dùng</th>
                                        <th>Sự kiện</th>
                                        <th>Đánh giá</th>
                                        <th>Nội dung</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingComments as $comment): ?>
                                        <tr>
                                            <td><?php echo $comment['ma_binh_luan']; ?></td>
                                            <td>
                                                <div class="user-info">
                                                    <?php if (!empty($comment['avatar'])): ?>
                                                        <img src="<?php echo BASE_URL; ?>/public/uploads/avatars/<?php echo htmlspecialchars($comment['avatar']); ?>" alt="Avatar" class="user-avatar">
                                                    <?php else: ?>
                                                        <img src="<?php echo BASE_URL; ?>/public/images/default-avatar.png" alt="Default Avatar" class="user-avatar">
                                                    <?php endif; ?>
                                                    <span><?php echo htmlspecialchars($comment['ho_ten']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>/event/<?php echo $comment['ma_su_kien']; ?>" target="_blank">
                                                    <?php echo htmlspecialchars($comment['ten_su_kien']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <div class="rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="<?php echo $i <= $comment['diem_danh_gia'] ? 'fas' : 'far'; ?> fa-star"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="comment-content">
                                                    <?php echo nl2br(htmlspecialchars($comment['noi_dung'])); ?>
                                                </div>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($comment['ngay_tao'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <form action="<?php echo BASE_URL; ?>/reviews/approve" method="POST" class="d-inline">
                                                        <input type="hidden" name="comment_id" value="<?php echo $comment['ma_binh_luan']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm" title="Duyệt">
                                                            <i class="fas fa-check"></i> Duyệt
                                                        </button>
                                                    </form>
                                                    <form action="<?php echo BASE_URL; ?>/reviews/reject" method="POST" class="d-inline">
                                                        <input type="hidden" name="comment_id" value="<?php echo $comment['ma_binh_luan']; ?>">
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Từ chối" onclick="return confirm('Bạn có chắc chắn muốn từ chối bình luận này?')">
                                                            <i class="fas fa-times"></i> Từ chối
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
