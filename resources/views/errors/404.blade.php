<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan - 404</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 500px;
            width: 90%;
        }
        .error-code {
            font-size: 120px;
            font-weight: 800;
            color: #ff6b6b;
            line-height: 1;
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 80px;
            color: #ff6b6b;
            margin-bottom: 20px;
        }
        .error-message {
            font-size: 24px;
            font-weight: 700;
            color: #2b3440;
            margin-bottom: 10px;
        }
        .error-description {
            color: #6b7280;
            margin-bottom: 30px;
        }
        .btn-home {
            background-color: #ff6b6b;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            background-color: #fa5252;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="error-code">404</div>
        <div class="error-message">Waduh! Halaman Tidak Ditemukan</div>
        <div class="error-description">
            Maaf, halaman yang Anda cari mungkin telah dihapus, diubah namanya, atau tidak tersedia untuk sementara waktu.
        </div>
        <a href="{{ url('/') }}" class="btn-home">
            <i class="bi bi-house-door-fill me-2"></i>Kembali ke Beranda
        </a>
    </div>
</body>
</html>
