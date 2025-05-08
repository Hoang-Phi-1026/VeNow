<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang | TicketBox</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/../views/layouts/header.php'; ?>
    
    <div class="container" style="text-align: center; padding: 100px 0;">
        <h1 style="font-size: 72px; margin-bottom: 20px;">404</h1>
        <h2 style="margin-bottom: 20px;">Không tìm thấy trang</h2>
        <p style="margin-bottom: 30px;">Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di chuyển.</p>
        <a href="<?php echo BASE_URL; ?>/" class="btn btn-primary">Quay lại trang chủ</a>
    </div>
    
    <?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
</body>
</html>
