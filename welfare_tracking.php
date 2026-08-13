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

// 1. New Welfare Record Insert Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_welfare') {
    $member_id = intval($_POST['member_id'] ?? 0);
    $welfare_type = trim($_POST['welfare_type'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $status = trim($_POST['status'] ?? 'සක්‍රීය');
    $remarks = trim($_POST['remarks'] ?? '');

    if ($member_id > 0 && !empty($welfare_type)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO welfare_records (member_id, welfare_type, amount, status, remarks) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$member_id, $welfare_type, $amount, $status, $remarks]);
            $message = "සහනාධාර තොරතුරු සාර්ථකව ඇතුළත් කරන ලදී!";
        } catch (PDOException $e) {
            $error = "දත්ත ඇතුළත් කිරීමේදී දෝෂයක් සිදු විය: " . $e->getMessage();
        }
    } else {
        $error = "කරුණාකර සාමාජිකයා සහ සහනාධාර වර්ගය තෝරන්න.";
    }
}

// 2. Delete Welfare Record Logic
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM welfare_records WHERE id = ?");
        $stmt->execute([$delete_id]);
        header("Location: welfare_tracking.php?deleted=1");
        exit;
    } catch (PDOException $e) {
        $error = "මකා දැමීමේදී දෝෂයක් සිදු විය: " . $e->getMessage();
    }
}

if (isset($_GET['deleted'])) {
    $message = "සහනාධාර තොරතුර සාර්ථකව මකා දමන ලදී.";
}

// Search and Filter Params
$filter_type = $_GET['welfare_type'] ?? '';
$filter_status = $_GET['status'] ?? '';
$search_q = trim($_GET['q'] ?? '');

$where = ["1=1"];
$params = [];

if ($filter_type !== '') {
    $where[] = "w.welfare_type = :type";
    $params['type'] = $filter_type;
}

if ($filter_status !== '') {
    $where[] = "w.status = :status";
    $params['status'] = $filter_status;
}

if ($search_q !== '') {
    $where[] = "(m.full_name LIKE :q OR m.nic LIKE :q OR h.hh_no LIKE :q)";
    $params['q'] = '%' . $search_q . '%';
}

$where_sql = implode(' AND ', $where);

// Fetch Welfare List
try {
    $sql = "SELECT w.*, m.full_name, m.nic, m.household_id, h.hh_no, h.address
            FROM welfare_records w
            JOIN members m ON w.member_id = m.id
            LEFT JOIN households h ON m.household_id = h.id
            WHERE {$where_sql}
            ORDER BY w.id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $welfare_list = $stmt->fetchAll();

    // Fetch members for the dropdown in modal
    $members_stmt = $pdo->query("SELECT m.id, m.full_name, m.nic, h.hh_no FROM members m LEFT JOIN households h ON m.household_id = h.id ORDER BY m.full_name ASC");
    $all_members = $members_stmt->fetchAll();

    // Summary Statistics
    $stat_stmt = $pdo->query("SELECT welfare_type, COUNT(*) as count, SUM(amount) as total_amount FROM welfare_records GROUP BY welfare_type");
    $stats = $stat_stmt->fetchAll();

} catch (PDOException $e) {
    $error = "දත්ත ලබා ගැනීමේදී දෝෂයක් සිදු විය: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>සහනාධාර ලැයිස්තුව - GN 759/A Galhena</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11pt; }
            .card { border: none !important; box-shadow: none !important; }
            .table-responsive { overflow: visible !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex flex-col">

    <!-- Top Navigation Bar -->
    <nav class="bg-slate-900 text-white shadow-md sticky top-0 z-50 no-print">
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
                    <a href="voters_filters.php" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        <i class="fa-solid fa-check-to-slot mr-1.5"></i> ඡන්ද/වයස් ලැයිස්තු
                    </a>
                    <a href="welfare_tracking.php" class="px-3 py-2 rounded-md text-sm font-semibold bg-blue-600 text-white shadow-sm">
                        <i class="fa-solid fa-hand-holding-heart mr-1.5"></i> සහනාධාර
                    </a>
                    <a href="search.php" class="px-3 py-2 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        <i class="fa-solid fa-magnifying-glass mr-1.5"></i> සොයන්න
                    </a>
                </div>

                <div>
                    <button type="button" data-bs-toggle="modal" data-bs-target="#addWelfareModal" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm px-4 py-2 rounded-lg shadow inline-flex items-center gap-2 transition">
                        <i class="fa-solid fa-plus"></i>
                        <span>නව සහනාධාරයක් එක් කරන්න</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full">

        <!-- Header Title -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    <i class="fa-solid fa-hand-holding-heart text-blue-600 mr-2"></i>සහනාධාර තොරතුරු කළමනාකරණය (Welfare Tracking)
                </h2>
                <p class="text-sm text-slate-500">වසමේ අස්වැසුම, සමෘද්ධි සහ අනෙකුත් සහනාධාර ලබන්නන්ගේ දත්ත ලැයිස්තුව</p>
            </div>
            <button onclick="window.print()" class="btn btn-outline-secondary font-bold text-sm no-print">
                <i class="fa-solid fa-print mr-1.5"></i> Print Report
            </button>
        </div>

        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-xl mb-6" role="alert">
                <i class="fa-solid fa-circle-check mr-2"></i><?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-xl mb-6" role="alert">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Summary Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 no-print">
            <?php 
            $type_counts = [];
            foreach ($stats as $st) {
                $type_counts[$st['welfare_type']] = $st;
            }
            $types = ['අස්වැසුම', 'සමෘද්ධි', 'වැඩිහිටි දීමනාව', 'ආබාධිත දීමනාව'];
            foreach ($types as $t): 
                $count = $type_counts[$t]['count'] ?? 0;
                $tot = $type_counts[$t]['total_amount'] ?? 0;
            ?>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 block mb-1"><?php echo $t; ?></span>
                    <div class="text-2xl font-extrabold text-blue-600"><?php echo $count; ?> <span class="text-xs text-slate-400 font-normal">දෙනෙක්</span></div>
                    <div class="text-xs text-slate-500 mt-1">මුළු මුදල: Rs. <?php echo number_format($tot, 2); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Search & Filter Box -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6 no-print">
            <form method="GET" action="welfare_tracking.php" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-xs font-bold text-slate-600">සෙවුම් පද (නම / NIC / ගෘහ අංකය)</label>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($search_q); ?>" class="form-control" placeholder="Search...">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-xs font-bold text-slate-600">සහනාධාර වර්ගය</label>
                    <select name="welfare_type" class="form-select">
                        <option value="">සියල්ල (All Types)</option>
                        <option value="අස්වැසුම" <?php echo $filter_type === 'අස්වැසුම' ? 'selected' : ''; ?>>අස්වැසුම</option>
                        <option value="සමෘද්ධි" <?php echo $filter_type === 'සමෘද්ධි' ? 'selected' : ''; ?>>සමෘද්ධි</option>
                        <option value="වැඩිහිටි දීමනාව" <?php echo $filter_type === 'වැඩිහිටි දීමනාව' ? 'selected' : ''; ?>>වැඩිහිටි දීමනාව</option>
                        <option value="ආබාධිත දීමනාව" <?php echo $filter_type === 'ආබාධිත දීමනාව' ? 'selected' : ''; ?>>ආබාධිත දීමනාව</option>
                        <option value="ශිෂ්‍යත්ව" <?php echo $filter_type === 'ශිෂ්‍යත්ව' ? 'selected' : ''; ?>>ශිෂ්‍යත්ව</option>
                        <option value="වෙනත්" <?php echo $filter_type === 'වෙනත්' ? 'selected' : ''; ?>>වෙනත්</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-xs font-bold text-slate-600">තත්ත්වය (Status)</label>
                    <select name="status" class="form-select">
                        <option value="">සියල්ල (All Status)</option>
                        <option value="සක්‍රීය" <?php echo $filter_status === 'සක්‍රීය' ? 'selected' : ''; ?>>සක්‍රීය</option>
                        <option value="අත්හිටුවා ඇත" <?php echo $filter_status === 'අත්හිටුවා ඇත' ? 'selected' : ''; ?>>අත්හිටුවා ඇත</option>
                        <option value="අවලංගුයි" <?php echo $filter_status === 'අවලංගුයි' ? 'selected' : ''; ?>>අවලංගුයි</option>
                    </select>
                </div>
                <div class="col-md-2 flex items-end gap-2">
                    <button type="submit" class="btn btn-primary w-full font-bold">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>
                    <a href="welfare_tracking.php" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>

        <!-- Welfare Recipients Table -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-sm mb-0">
                    <thead class="table-light">
                        <tr class="text-slate-600">
                            <th>#</th>
                            <th>ප්‍රතිලාභියාගේ නම</th>
                            <th>ජා.හැ. අංකය (NIC)</th>
                            <th>ගෘහ අංකය</th>
                            <th>සහනාධාර වර්ගය</th>
                            <th>දීමනා මුදල (රු.)</th>
                            <th>තත්ත්වය</th>
                            <th>සටහන්</th>
                            <th class="text-end no-print">ක්‍රියාකාරකම්</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($welfare_list)): ?>
                            <?php $i = 1; foreach ($welfare_list as $row): ?>
                                <tr>
                                    <td class="text-slate-400 font-bold"><?php echo $i++; ?></td>
                                    <td class="font-bold text-slate-800">
                                        <?php echo htmlspecialchars($row['full_name']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-slate-100 text-slate-700 border font-mono"><?php echo htmlspecialchars($row['nic'] ?? '-'); ?></span>
                                    </td>
                                    <td class="font-bold text-blue-700">
                                        <?php echo htmlspecialchars($row['hh_no'] ?? '-'); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-blue-100 text-blue-800 border border-blue-200 font-semibold">
                                            <?php echo htmlspecialchars($row['welfare_type']); ?>
                                        </span>
                                    </td>
                                    <td class="font-mono font-bold text-slate-800">
                                        <?php echo number_format($row['amount'], 2); ?>
                                    </td>
                                    <td>
                                        <?php if ($row['status'] === 'සක්‍රීය'): ?>
                                            <span class="badge bg-emerald-100 text-emerald-800 border border-emerald-300">සක්‍රීය</span>
                                        <?php elseif ($row['status'] === 'අත්හිටුවා ඇත'): ?>
                                            <span class="badge bg-amber-100 text-amber-800 border border-amber-300">අත්හිටුවා ඇත</span>
                                        <?php else: ?>
                                            <span class="badge bg-rose-100 text-rose-800 border border-rose-300">අවලංගුයි</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-xs text-slate-500">
                                        <?php echo htmlspecialchars($row['remarks'] ?? '-'); ?>
                                    </td>
                                    <td class="text-end no-print">
                                        <a href="household_view.php?id=<?php echo $row['household_id']; ?>" class="btn btn-sm btn-light border text-blue-600 hover:bg-blue-600 hover:text-white transition" title="ගෘහය බලන්න">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="welfare_tracking.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('මෙම සහනාධාර වාර්තාව මකා දැමීමට ඔබට විශ්වාසද?');" class="btn btn-sm btn-light border text-rose-600 hover:bg-rose-600 hover:text-white transition" title="මකා දමන්න">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-10 text-slate-400">
                                    <i class="fa-solid fa-hand-holding-heart text-4xl mb-3 block text-slate-300"></i>
                                    කිසිදු සහනාධාර වාර්තාවක් හමුවූයේ නැත.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Modal: Add Welfare Record -->
    <div class="modal fade" id="addWelfareModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-2xl border-0 shadow-lg">
                <form method="POST" action="welfare_tracking.php">
                    <input type="hidden" name="action" value="add_welfare">
                    <div class="modal-header bg-slate-900 text-white rounded-t-2xl">
                        <h5 class="modal-title font-bold"><i class="fa-solid fa-plus mr-2"></i>නව සහනාධාර තොරතුරක් ඇතුළත් කිරීම</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-6 row g-3">
                        <div class="col-md-12">
                            <label class="form-label font-bold text-xs text-slate-700">ප්‍රතිලාභී සාමාජිකයා තෝරන්න *</label>
                            <select name="member_id" class="form-select" required>
                                <option value="">-- සාමාජිකයා තෝරන්න --</option>
                                <?php foreach ($all_members as $m): ?>
                                    <option value="<?php echo $m['id']; ?>">
                                        <?php echo htmlspecialchars($m['full_name']); ?> (NIC: <?php echo htmlspecialchars($m['nic'] ?? 'නැත'); ?> | ගෘහ අංකය: <?php echo htmlspecialchars($m['hh_no'] ?? '-'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
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
                        <div class="col-md-6">
                            <label class="form-label font-bold text-xs text-slate-700">මාසික දීමනා මුදල (රු.)</label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="උදා: 5000.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold text-xs text-slate-700">තත්ත්වය (Status)</label>
                            <select name="status" class="form-select">
                                <option value="සක්‍රීය">සක්‍රීය</option>
                                <option value="අත්හිටුවා ඇත">අත්හිටුවා ඇත</option>
                                <option value="අවලංගුයි">අවලංගුයි</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-bold text-xs text-slate-700">විශේෂ සටහන්</label>
                            <input type="text" name="remarks" class="form-control" placeholder="උදා: ගිණුම් අංකය, කාණ්ඩය ආදිය...">
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 rounded-b-2xl">
                        <button type="button" class="btn btn-secondary font-bold" data-bs-dismiss="modal">වසා දමන්න</button>
                        <button type="submit" class="btn btn-emerald-600 bg-emerald-600 text-white font-bold px-4 hover:bg-emerald-700">සුරකින්න</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>