<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo sự kiện mới - Venow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Reset CSS nhẹ nhàng */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header, footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 15px 0;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .h1 {
            font-size: 48px;
            color: #ff3c00;
            font-weight: bold;
            text-align: center;
            transition: transform 0.3s ease, color 0.3s ease;
        }

        .h1:hover {
            transform: scale(1.1);
            color: #ff7f50;
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

    <div class="main-content">
        <div class="h1">TRANG TẠO SỰ KIỆN</div>
    </div>

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

</body>
</html>
