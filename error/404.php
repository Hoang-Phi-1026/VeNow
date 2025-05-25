<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Không tìm thấy trang | TicketBox</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .container {
            text-align: center;
            padding: 100px 0;
        }
        .error-404-image {
            max-width: 350px;
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto 30px auto;
        }
        .btn-primary {
            display: inline-block;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../views/layouts/header.php'; ?>
    
    <div class="container">
        <img src="<?php echo BASE_URL; ?>/public/images/error404.png" alt="404 Not Found" class="error-404-image">
        <a href="<?php echo BASE_URL; ?>/" class="btn btn-primary">Quay lại trang chủ</a>
    </div>
    
    <?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
</body>
</html>