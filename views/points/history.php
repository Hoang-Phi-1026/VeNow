<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/points.css">

<div class="container mt-4">
    <div class="page-header">
        <h1>Lịch sử điểm tích lũy</h1>
        <a href="<?= BASE_URL ?>/points" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i> Quay lại
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <!-- Bộ lọc -->
                    <div class="filter-container mb-4">
                        <form method="GET" action="<?= BASE_URL ?>/points/history" class="row g-3">
                            <div class="col-md-4">
                                <label for="type" class="form-label">Loại giao dịch</label>
                                <select name="type" id="type" class="form-select">
                                    <option value="" <?= $type == '' ? 'selected' : '' ?>>Tất cả</option>
                                    <option value="MUA_VE" <?= $type == 'MUA_VE' ? 'selected' : '' ?>>Mua vé</option>
                                    <option value="HOAN_VE" <?= $type == 'HOAN_VE' ? 'selected' : '' ?>>Hoàn vé</option>
                                    <option value="UU_DAI" <?= $type == 'UU_DAI' ? 'selected' : '' ?>>Sử dụng ưu đãi</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i> Lọc
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <?php if (empty($pointsHistory)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Không tìm thấy giao dịch điểm tích lũy nào.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Ngày</th>
                                        <th>Loại</th>
                                        <th>Điểm</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pointsHistory as $transaction): ?>
                                        <tr>
                                            <td><?= $transaction['ma_diem'] ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($transaction['ngay_nhan'])) ?></td>
                                            <td>
                                                <?php if ($transaction['nguon'] == 'MUA_VE'): ?>
                                                    <i class="fas fa-ticket-alt me-1"></i>
                                                <?php elseif ($transaction['nguon'] == 'HOAN_VE'): ?>
                                                    <i class="fas fa-undo me-1"></i>
                                                <?php else: ?>
                                                    <i class="fas fa-gift me-1"></i>
                                                <?php endif; ?>
                                                <?= $transaction['nguon_text'] ?>
                                            </td>
                                            <td class="<?= $transaction['so_diem'] > 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= $transaction['so_diem'] > 0 ? '+' : '' ?><?= number_format($transaction['so_diem']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Phân trang -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= BASE_URL ?>/points/history?page=<?= $page - 1 ?>&type=<?= $type ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="<?= BASE_URL ?>/points/history?page=<?= $i ?>&type=<?= $type ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="<?= BASE_URL ?>/points/history?page=<?= $page + 1 ?>&type=<?= $type ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
