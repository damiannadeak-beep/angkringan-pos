<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak QR Code - {{ $meja->nama_meja_atau_nomor }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f4f4f4;
            margin: 0;
        }
        .qr-card {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 350px;
            border: 2px dashed #b34b63;
        }
        .qr-card h1 {
            color: #b34b63;
            margin-top: 0;
            font-size: 24px;
        }
        .qr-card h2 {
            margin: 10px 0 20px;
            font-size: 32px;
            color: #333;
        }
        .qr-image {
            margin-bottom: 20px;
        }
        .qr-image img {
            width: 250px;
            height: 250px;
        }
        .footer-text {
            font-size: 14px;
            color: #666;
            line-height: 1.5;
        }
        .print-btn {
            margin-top: 20px;
            background-color: #b34b63;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .print-btn:hover {
            background-color: #8c3a4d;
        }
        @media print {
            body {
                background-color: #fff;
            }
            .qr-card {
                box-shadow: none;
                border: 2px solid #000;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="qr-card">
        <h1>ANGKRINGAN POS</h1>
        <h2>{{ $meja->nama_meja_atau_nomor }}</h2>
        
        <div class="qr-image">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ urlencode($url) }}" alt="QR Code">
        </div>
        
        <div class="footer-text">
            <strong>Scan QR Code</strong><br>
            Untuk melihat menu dan memesan langsung dari HP Anda.
        </div>
        
        <button class="print-btn" onclick="window.print()">Cetak (Print)</button>
    </div>

</body>
</html>
