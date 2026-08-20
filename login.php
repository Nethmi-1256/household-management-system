<?php
require_once 'config.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Session Variables සකස් කිරීම
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                header("Location: dashboard.php");
                exit();
            } else {
                $error = 'ඔබ ඇතුළත් කළ පරිශීලක නාමය (Username) හෝ මුරපදය වැරදිය.';
            }
        } catch (PDOException $e) {
            $error = 'දත්ත සමුදායේ දෝෂයකි: ' . $e->getMessage();
        }
    } else {
        $error = 'කරුණාකර සියලුම කොටස් පුරවන්න.';
    }
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GN 759/A Galhena</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/theme.css">
    <style>
        body { font-family: 'Inter', 'Noto Sans Sinhala', ui-sans-serif, system-ui, sans-serif; }
        @keyframes ginFadeUp { from { opacity:0; transform: translateY(14px);} to { opacity:1; transform: translateY(0);} }
        .gn-anim-in { animation: ginFadeUp .6s cubic-bezier(.2,.8,.2,1) both; }
        .gn-delay-1 { animation-delay: .08s; }
        .gn-delay-2 { animation-delay: .16s; }
        .gn-delay-3 { animation-delay: .24s; }
    </style>
</head>
<body class="bg-slate-950 font-sans min-h-screen flex items-center justify-center px-4 py-8">

<div class="max-w-5xl w-full grid grid-cols-1 lg:grid-cols-2 rounded-3xl overflow-hidden shadow-2xl border border-white/10">

    <!-- Left: animated illustrated hero panel -->
    <div class="relative hidden lg:flex flex-col justify-between p-10 gn-gradient-bg gn-dot-grid overflow-hidden">
        <!-- floating gradient blobs -->
        <div class="gn-blob b1" style="width:220px;height:220px;background:#60a5fa;top:-40px;left:-40px;"></div>
        <div class="gn-blob b2" style="width:180px;height:180px;background:#a78bfa;bottom:20px;right:-30px;"></div>
        <div class="gn-blob b3" style="width:140px;height:140px;background:#34d399;bottom:-40px;left:40%;"></div>

        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 gn-glass text-white text-xs font-bold px-3 py-1.5 rounded-full mb-8">
                <i class="fa-solid fa-shield-halved"></i> ආරක්ෂිත රාජ්‍ය පද්ධතියකි
            </div>
            <h2 class="text-3xl font-extrabold text-white leading-tight mb-3">
                ග්‍රාම නිලධාරී වසම<br> කළමනාකරණ පද්ධතිය
            </h2>
            <p class="text-blue-100 text-sm leading-relaxed max-w-sm">
                ගෘහ, පවුල් සාමාජිකයින්, ඡන්ද ලැයිස්තු, සහනාධාර සහ වාර්තා — සියල්ල එකම තැනකින්, පහසුවෙන් කළමනාකරණය කරන්න.
            </p>
        </div>

        <!-- Inline SVG illustration: house + people + document, fully original -->
        <div class="relative z-10 gn-float">
            <svg viewBox="0 0 320 220" class="w-full max-w-sm mx-auto" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="160" cy="200" rx="120" ry="12" fill="#000" opacity="0.15"/>
                <!-- house -->
                <rect x="60" y="90" width="120" height="90" rx="8" fill="#ffffff" opacity="0.95"/>
                <polygon points="55,95 120,45 185,95" fill="#facc15"/>
                <rect x="95" y="130" width="26" height="50" rx="3" fill="#2563eb"/>
                <rect x="140" y="115" width="24" height="24" rx="3" fill="#60a5fa"/>
                <rect x="140" y="115" width="24" height="24" rx="3" fill="none" stroke="#1e3a8a" stroke-width="2"/>
                <!-- document card -->
                <rect x="190" y="70" width="80" height="100" rx="10" fill="#ffffff"/>
                <rect x="202" y="88" width="56" height="8" rx="4" fill="#c7d2fe"/>
                <rect x="202" y="104" width="40" height="8" rx="4" fill="#e0e7ff"/>
                <rect x="202" y="120" width="56" height="8" rx="4" fill="#e0e7ff"/>
                <circle cx="230" cy="148" r="14" fill="#34d399"/>
                <path d="M223 148l5 5 10-10" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                <!-- people -->
                <circle cx="45" cy="165" r="12" fill="#fbbf24"/>
                <rect x="33" y="177" width="24" height="30" rx="10" fill="#fb923c"/>
                <circle cx="75" cy="170" r="10" fill="#f472b6"/>
                <rect x="65" y="180" width="20" height="26" rx="9" fill="#ec4899"/>
            </svg>
        </div>

        <div class="relative z-10 flex items-center gap-6 text-blue-100 text-xs font-semibold pt-6 border-t border-white/15">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-house-user"></i> ගෘහ කළමනාකරණය</span>
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-chart-pie"></i> සජීවී වාර්තා</span>
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-hand-holding-heart"></i> සහනාධාර</span>
        </div>
    </div>

    <!-- Right: login form -->
    <div class="bg-white p-8 sm:p-10 flex flex-col justify-center">
        <!-- Brand Header -->
        <div class="text-center mb-8 gn-anim-in">
            <div class="inline-flex bg-blue-600 text-white w-14 h-14 rounded-2xl items-center justify-center text-2xl shadow-lg shadow-blue-500/30 mb-3">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <h1 class="text-xl font-extrabold text-slate-800">ග්‍රාම නිලධාරී වසම් පද්ධතිය</h1>
            <p class="text-xs text-slate-500 mt-1">759/A ගල්හේන - කළමනාකරණ පිවිසුම</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-xs px-4 py-3 rounded-xl flex items-center gap-2 gn-anim-in">
                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login.php" method="POST" class="space-y-5">
            <div class="gn-anim-in gn-delay-1">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">පරිශීලක නාමය (Username)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="username" required class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 transition" placeholder="admin ඇතුළත් කරන්න">
                </div>
            </div>

            <div class="gn-anim-in gn-delay-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">මුරපදය (Password)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" id="gnPassField" required class="w-full pl-10 pr-11 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 transition" placeholder="••••••••">
                    <button type="button" id="gnTogglePass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-blue-600">
                        <i class="fa-solid fa-eye" id="gnToggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="gn-anim-in gn-delay-3 gn-ripple gn-shine w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-3.5 rounded-xl shadow-lg shadow-blue-500/20 transition flex items-center justify-center gap-2">
                <span>පද්ධතියට පිවිසෙන්න</span>
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>

        <div class="mt-8 text-center text-xs text-slate-400 border-t border-slate-100 pt-5 flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-lock text-[10px]"></i>
             ආරක්‍ෂිත පද්ධතියකි &copy; <?php echo date('Y'); ?> ගල්හේන
        </div>
    </div>
</div>

<script>
    var toggleBtn = document.getElementById('gnTogglePass');
    var passField = document.getElementById('gnPassField');
    var toggleIcon = document.getElementById('gnToggleIcon');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            var isPwd = passField.type === 'password';
            passField.type = isPwd ? 'text' : 'password';
            toggleIcon.classList.toggle('fa-eye');
            toggleIcon.classList.toggle('fa-eye-slash');
        });
    }
</script>

</body>
</html>