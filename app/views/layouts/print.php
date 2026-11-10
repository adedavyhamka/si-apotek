<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? APP_NAME) ?> — <?= APP_NAME ?></title>
    <link rel="icon" type="image/png" href="assets/images/favicon-si-apotek.png">
    <link href="assets/css/report.css" rel="stylesheet">
</head>
<body class="<?= !empty($report['landscape']) ? 'report-landscape' : '' ?>">

    <div class="print-toolbar no-print" style="display:flex;gap:10px;justify-content:center;align-items:center;padding:14px;background:#eef2f7;border-bottom:1px solid #d5dde5;margin-bottom:20px;">
        <button onclick="window.print()" style="background:#16834B;color:#fff;border:none;padding:9px 18px;border-radius:6px;font-weight:600;cursor:pointer;">
            &#128424;&#65039; Cetak / Simpan PDF
        </button>
        <a href="index.php?m=laporan" style="background:#fff;border:1px solid #cbd5d1;padding:9px 16px;border-radius:6px;text-decoration:none;color:#344054;">
            &larr; Kembali ke Laporan
        </a>
    </div>

    <div class="report-doc" style="max-width:1000px;margin:0 auto;padding:0 6px;">
        <?= $content ?>
    </div>

    <script>
        if (new URLSearchParams(location.search).has('autoprint')) {
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 500);
            });
        }
    </script>
</body>
</html>
