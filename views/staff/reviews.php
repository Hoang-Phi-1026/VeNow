<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Quản lý bình luận</h2>

    <?php if (empty($pendingComments)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Không có bình luận nào đang chờ duyệt.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>Sự kiện</th>
                        <th>Nội dung</th>
                        <th>Đánh giá</th>
                        <th>Ngày gửi</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingComments as $comment): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($comment['avatar'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/uploads/avatars/' . $comment['avatar'])): ?>
                                        <img src="<?= BASE_URL ?>/public/uploads/avatars/<?= htmlspecialchars($comment['avatar']) ?>" 
                                             class="rounded-circle me-2" 
                                             width="40" 
                                             height="40" 
                                             alt="Avatar">
                                    <?php else: ?>
                                        <img src="<?= BASE_URL ?>/public/images/default-avatar.png" 
                                             class="rounded-circle me-2" 
                                             width="40" 
                                             height="40" 
                                             alt="Default Avatar">
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($comment['ho_ten']) ?></span>
                                </div>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/event/<?= $comment['ma_su_kien'] ?>" target="_blank">
                                    <?= htmlspecialchars($comment['ten_su_kien']) ?>
                                </a>
                            </td>
                            <td><?= nl2br(htmlspecialchars($comment['noi_dung'])) ?></td>
                            <td>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="<?= $i <= $comment['diem_danh_gia'] ? 'fas' : 'far' ?> fa-star text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($comment['ngay_tao'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <form action="<?= BASE_URL ?>/staff/reviews/approve" method="POST" class="d-inline">
                                        <input type="hidden" name="id" value="<?= $comment['ma_binh_luan'] ?>">
                                        <button type="submit" 
                                                class="btn btn-success btn-sm" 
                                                onclick="return confirm('Bạn có chắc muốn duyệt bình luận này?')">
                                            <i class="fas fa-check"></i> Duyệt
                                        </button>
                                    </form>
                                    <form action="<?= BASE_URL ?>/staff/reviews/reject" method="POST" class="d-inline ms-2">
                                        <input type="hidden" name="id" value="<?= $comment['ma_binh_luan'] ?>">
                                        <button type="submit" 
                                                class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Bạn có chắc muốn từ chối bình luận này?')">
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
