<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Form Submission Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $member_id = intval($_POST['member_id'] ?? 0);
    $welfare_type = trim($_POST['welfare_type'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $status = trim($_POST['status'] ?? 'සක්‍රීය');
    $remarks = trim($_POST['remarks'] ?? '');

    if ($member_id > 0 && !empty($welfare_type)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO welfare_records (member_id, welfare_type, amount, status, remarks) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$member_id, $welfare_type, $amount, $status, $remarks]);
            
            // Redirect with success message
            header("Location: welfare_tracking.php?added=1");
            exit;
        } catch (PDOException $e) {
            $error = "දත්ත ඇතුළත් කිරීමේදී දෝෂයක් සිදු විය: " . $e->getMessage();
        }
    } else {
        $error = "කරුණාකර සාමාජිකයා සහ සහනාධාර වර්ගය තෝරන්න.";
    }
}

// Fetch all members for dropdown
try {
    $members_stmt = $pdo->query("SELECT m.id, m.full_name, m.nic, h.hh_no FROM members m LEFT JOIN households h ON m.household_id = h.id ORDER BY m.full_name ASC");
    $all_members = $members_stmt->fetchAll();
} catch (PDOException $e) {
    $error = "සාමාජිකයින්ගේ දත්ත ලබා ගැනීමේදී දෝෂයක් සිදු විය: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>නව සහනාධාරයක් ඇතුළත් කිරීම - GN 759/A Galhena</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <nav class="bg-slate-900 text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-600 text-white p-2 rounded-lg text-lg font-bold">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div>
                        <h1 class="text-lg font-bold tracking-wide leading-tight">ග්‍රාම නිලධාරී වසම 759/A</h1>
                        <p class="text-xs text-slate-400">ගල්හේන - තොරතුරු කළමනාකරණ පද්ධතිය</p>
                    </div>
                </div>

                <div class="hidden md:flex items-center space-x-2">
                    <a href="dashboard.php" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        <i class="fa-solid fa-chart-line mr-1.5"></i> Dashboard
                    </a>
                    <a href="households_list.php" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        <i class="fa-solid fa-house-user mr-1.5"></i> ගෘහ ලැයිස්තුව
                    </a>
                    <a href="welfare_tracking.php" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        <i class="fa-solid fa-hand-holding-heart mr-1.5"></i> සහනාධාර
                    </a>
                    <a href="search.php" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        <i class="fa-solid fa-magnifying-glass mr-1.5"></i> සොයන්න
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full">

        <!-- Back Button -->
        <div class="mb-4">
            <a href="welfare_tracking.php" class="text-sm text-slate-500 hover:text-slate-800 font-semibold inline-flex items-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> සහනාධාර ලැයිස්තුවට ආපසු යන්න
            </a>
        </div>

        <!-- Form Box -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100">
                <div class="bg-emerald-100 text-emerald-700 p-3 rounded-xl text-xl font-bold">
                    <i class="fa-solid fa-hand-holding-heart"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">නව සහනාධාර තොරතුරක් ඇතුළත් කිරීම</h2>
                    <p class="text-xs text-slate-500">ප්‍රතිලාභී සාමාජිකයාගේ විස්තර සහ සහනාධාර තොරතුරු පහත පෝරමයට ඇතුළත් කරන්න.</p>
                </div>
            </div>

            <!-- Error Alert -->
            <?php if ($error): ?>
                <div class="alert alert-danger shadow-sm rounded-xl mb-6 flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="add_welfare.php" class="row g-4">
                
                <!-- Member Selection -->
                <div class="col-12">
                    <label class="form-label font-bold text-xs text-slate-700">ප්‍රතිලාභී සාමාජිකයා තෝරන්න *</label>
                    <select name="member_id" class="form-select" required>
                        <option value="">-- සාමාජිකයා තෝරන්න --</option>
                        <?php foreach ($all_members as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php echo (isset($_GET['member_id']) && $_GET['member_id'] == $m['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m['full_name']); ?> (NIC: <?php echo htmlspecialchars($m['nic'] ?? 'නැත'); ?> | ගෘහ අංකය: <?php echo htmlspecialchars($m['hh_no'] ?? '-'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Welfare Type -->
                <div class="col-md-6">
                    <label class="form-label font-bold text-xs text-slate-700">සහනාධාර වර්ගය *</label>
                    <select name="welfare_type" class="form-select" required>
                        <option value="අස්වැසුම">අස්වැසුම</option>
                        <option value="සමෘද්ධි">සමෘද්ධි</option>
                        <option value="වැඩිහිටි දීමනාව">වැඩිහිටි දීමනාව</option>
                        <option value="ආබාධිත දීමනාව">ආබාධිත දීමනාව</option>
                        <option value="ශිෂ්‍යත්ව">ශිෂ්‍යත්ව</option>
                        <option value="වෙනත්">වෙනත්</option>
                    </select>
                </div>

                <!-- Monthly Amount -->
                <div class="col-md-6">
                    <label class="form-label font-bold text-xs text-slate-700">මාසික දීමනා මුදල (රු.)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-slate-100 text-slate-500 font-bold">Rs.</span>
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00">
                    </div>
                </div>

                <!-- Status -->
                <div class="col-md-6">
                    <label class="form-label font-bold text-xs text-slate-700">තත්ත්වය (Status)</label>
                    <select name="status" class="form-select">
                        <option value="සක්‍රීය">සක්‍රීය</option>
                        <option value="අත්හිටුවා ඇත">අත්හිටුවා ඇත</option>
                        <option value="අවලංගුයි">අවලංගුයි</option>
                    </select>
                </div>

                <!-- Remarks -->
                <div class="col-md-6">
                    <label class="form-label font-bold text-xs text-slate-700">විශේෂ සටහන්</label>
                    <input type="text" name="remarks" class="form-control" placeholder="උදා: ගිණුම් අංකය, කාණ්ඩය...">
                </div>

                <!-- Action Buttons -->
                <div class="col-12 flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="welfare_tracking.php" class="btn btn-outline-secondary font-bold px-4">අවලංගු කරන්න</a>
                    <button type="submit" class="btn btn-emerald-600 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i> සුරකින්න
                    </button>
                </div>

            </form>
        </div>

    </main>

    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 mt-auto">
        759/A ගල්හේන ග්‍රාම නිලධාරී වසම් පද්ධතිය &copy; <?php echo date('Y'); ?>
    </footer>

</body>
</html>