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
</head>
<body class="bg-slate-900 font-sans min-h-screen flex items-center justify-center px-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl p-8 border border-slate-100">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex bg-blue-600 text-white w-14 h-14 rounded-2xl items-center justify-center text-2xl shadow-lg mb-3">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <h1 class="text-xl font-extrabold text-slate-800">ග්‍රාම නිලධාරී වසම් පද්ධතිය</h1>
            <p class="text-xs text-slate-500 mt-1">759/A ගල්හේන - කළමනාකරණ පිවිසුම</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-xs px-4 py-3 rounded-xl flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">පරිශීලක නාමය (Username)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="username" required class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:outline-none focus:border-blue-600 transition" placeholder="admin ඇතුළත් කරන්න">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">මුරපදය (Password)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" required class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 focus:bg-white focus:outline-none focus:border-blue-600 transition" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm py-3.5 rounded-xl shadow-lg shadow-blue-500/20 transition flex items-center justify-center gap-2">
                <span>පද්ධතියට පිවිසෙන්න</span>
                <i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>

        <div class="mt-8 text-center text-xs text-slate-400 border-t border-slate-100 pt-5">
             ආරක්‍ෂිත පද්ධතියකි &copy; <?php echo date('Y'); ?> ගල්හේන
        </div>
    </div>

</body>
</html>