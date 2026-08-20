<?php
require_once 'config.php';

try {
    // 1. users වගුව සදාගැනීම (ENUM සමඟ)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('Admin', 'Grama Niladhari', 'Development Officer') DEFAULT 'Development Officer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. admin ගිණුම සඳහා නිවැරදි Hash එක සකස් කිරීම (Username: admin, Password: admin123)
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $full_name = 'M.P.J.Liyanage';
    $role = 'Development Officer';

    // දැනටමත් admin නමින් පරිශීලකයෙක් ඇත්නම් මුරපදය අලුත් කරයි, නැතහොත් අලුතින් එක් කරයි
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role)");
    $stmt->execute([$username, $password, $full_name, $role]);

    echo '<!DOCTYPE html><html lang="si"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Complete</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;600;700;800&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/theme.css"></head>
    <body class="bg-slate-100 min-h-screen flex items-center justify-center px-4">
        <div class="max-w-sm w-full bg-white rounded-2xl shadow-xl border border-emerald-100 p-8 text-center gn-hover-lift">
            <div class="gn-badge-emerald gn-badge-grad w-16 h-16 text-2xl mx-auto mb-4 gn-float"><i class="fa-solid fa-circle-check"></i></div>
            <h2 class="text-lg font-bold text-slate-800 mb-1">සාර්ථකයි!</h2>
            <p class="text-xs text-slate-500 mb-5">Admin ගිණුම සාර්ථකව සකස් කරන ලදී.</p>
            <div class="bg-slate-50 rounded-xl p-4 text-left text-xs space-y-1.5 mb-6 border border-slate-100">
                <div class="flex justify-between"><span class="text-slate-500">Username</span><strong class="text-slate-800">admin</strong></div>
                <div class="flex justify-between"><span class="text-slate-500">Password</span><strong class="text-slate-800">admin123</strong></div>
            </div>
            <a href="login.php" class="gn-ripple gn-shine inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-lg shadow-blue-500/20">
                Login පිටුවට යන්න <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </body></html>';

} catch (PDOException $e) {
    echo '<!DOCTYPE html><html lang="si"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/theme.css"></head>
    <body class="bg-slate-100 min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-rose-100 p-8 text-center">
            <div class="gn-badge-pink gn-badge-grad w-16 h-16 text-2xl mx-auto mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <h2 class="text-lg font-bold text-slate-800 mb-2">දෝෂයකි</h2>
            <p class="text-xs text-slate-500">' . htmlspecialchars($e->getMessage()) . '</p>
        </div>
    </body></html>';
}
?>