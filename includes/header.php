<?php
/**
 * Shared Header / Sidebar / Topbar
 * ---------------------------------
 * Expected variables set by the calling page BEFORE including this file:
 *   $active        (string) key of the current nav item - highlights sidebar link
 *   $page_title    (string) shown in <title> and the topbar badge
 *   $page_icon     (string, optional) FontAwesome class for the topbar badge, e.g. 'fa-chart-line'
 *   $breadcrumbs   (array, optional) [['label' => '...', 'url' => '...'], ['label' => 'Current']]
 *   $extra_head    (string, optional) raw HTML injected just before </head> (extra CSS etc.)
 *
 * Requires: $pdo (from config.php) and an active session with user_id already verified
 * by the calling page.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$active      = $active ?? '';
$page_title  = $page_title ?? 'GN 759/A Galhena';
$page_icon   = $page_icon ?? 'fa-gauge-high';
$breadcrumbs = $breadcrumbs ?? [];

// ---- Sidebar quick-stats (best effort - never fatal if it fails) ----
$__hh_count = 0;
$__mem_count = 0;
$__notif_items = [];
try {
    $__hh_count  = (int) $pdo->query("SELECT COUNT(*) FROM households")->fetchColumn();
    $__mem_count = (int) $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn();
    $__notif_stmt = $pdo->query("SELECT id, hh_no, address, created_at FROM households ORDER BY id DESC LIMIT 5");
    $__notif_items = $__notif_stmt->fetchAll();
} catch (Throwable $e) {
    // Silently ignore - stats/notifications are a bonus, not critical
}

// ---- Nav structure (single source of truth for the whole system) ----
$__nav_items = [
    ['key' => 'dashboard', 'icon' => 'fa-chart-line',          'label' => 'පාලන පුවරුව',        'sub' => 'Dashboard',        'url' => 'dashboard.php'],
    ['key' => 'households','icon' => 'fa-house-user',          'label' => 'ගෘහ ලැයිස්තුව',       'sub' => 'Households',       'url' => 'households_list.php'],
    ['key' => 'voters',    'icon' => 'fa-check-to-slot',       'label' => 'ඡන්ද/වයස් ලැයිස්තු',  'sub' => 'Voters & Age',     'url' => 'voters_filters.php'],
    ['key' => 'welfare',   'icon' => 'fa-hand-holding-heart',  'label' => 'සහනාධාර',            'sub' => 'Welfare',          'url' => 'welfare_tracking.php'],
    ['key' => 'reports',   'icon' => 'fa-file-invoice',        'label' => 'වාර්තා & ගණනය',       'sub' => 'Reports',          'url' => 'reports.php'],
    ['key' => 'search',    'icon' => 'fa-magnifying-glass',    'label' => 'සොයන්න',              'sub' => 'Search',           'url' => 'search.php'],
    ['key' => 'import',    'icon' => 'fa-file-csv',            'label' => 'CSV Import',          'sub' => 'Import',           'url' => 'import_csv.php'],
];

// ---- Generic flash messages driven by common ?added / ?updated / ?deleted GET flags ----
$__flash = null;
if (isset($_SESSION['flash'])) {
    $__flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
} elseif (isset($_GET['added'])) {
    $__flash = ['type' => 'success', 'msg' => 'දත්ත සාර්ථකව එකතු කරන ලදී!'];
} elseif (isset($_GET['updated'])) {
    $__flash = ['type' => 'success', 'msg' => 'දත්ත සාර්ථකව යාවත්කාලීන කරන ලදී!'];
} elseif (isset($_GET['deleted']) && !isset($_GET['welfare_type']) && $active !== 'welfare') {
    $__flash = ['type' => 'danger', 'msg' => 'දත්ත සාර්ථකව මකා දමන ලදී.'];
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - GN 759/A Galhena</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/theme.css">

    <?php if (!empty($extra_head)) { echo $extra_head; } ?>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex">

<!-- Mobile sidebar overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden lg:hidden"></div>

<!-- Sidebar Navigation (Left) -->
<aside id="appSidebar" class="w-64 bg-slate-900 text-white flex flex-col fixed inset-y-0 left-0 z-50 shadow-xl -translate-x-full lg:translate-x-0 transition-transform duration-200">
    <!-- Branding -->
    <div class="h-20 flex items-center gap-3 px-6 bg-slate-950 border-b border-slate-800 shrink-0">
        <div class="gn-badge-blue gn-badge-grad p-2.5 w-11 h-11 text-lg font-bold gn-float">
            <i class="fa-solid fa-building-columns"></i>
        </div>
        <div class="min-w-0">
            <h1 class="text-sm font-bold tracking-wide leading-tight truncate gn-gradient-text">759/A ගල්හේන</h1>
            <p class="text-[11px] text-slate-400">ග්‍රාම නිලධාරී වසම</p>
        </div>
        <button id="sidebarCloseBtn" class="ml-auto text-slate-400 hover:text-white lg:hidden">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 gap-2 px-3 pt-4">
        <div class="bg-slate-800/60 rounded-xl px-3 py-2 text-center gn-hover-lift">
            <div class="text-base font-extrabold text-white leading-tight" data-countup="<?php echo (int) $__hh_count; ?>">0</div>
            <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide"><i class="fa-solid fa-house text-blue-400 mr-1"></i>ගෘහ</div>
        </div>
        <div class="bg-slate-800/60 rounded-xl px-3 py-2 text-center gn-hover-lift">
            <div class="text-base font-extrabold text-white leading-tight" data-countup="<?php echo (int) $__mem_count; ?>">0</div>
            <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide"><i class="fa-solid fa-users text-emerald-400 mr-1"></i>සාමාජිකයින්</div>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto">
        <p class="px-3.5 pt-1 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-500">ප්‍රධාන මෙනුව</p>
        <?php foreach ($__nav_items as $item): ?>
            <?php $isActive = ($active === $item['key']); ?>
            <a href="<?php echo $item['url']; ?>"
               class="gn-ripple flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm transition <?php echo $isActive
                    ? 'font-semibold bg-blue-600 text-white shadow-md shadow-blue-600/30'
                    : 'font-medium text-slate-300 hover:text-white hover:bg-slate-800/70'; ?>">
                <i class="fa-solid <?php echo $item['icon']; ?> w-5 text-center text-base"></i>
                <span><?php echo $item['label']; ?></span>
                <?php if ($isActive): ?><i class="fa-solid fa-circle text-[6px] ml-auto"></i><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Sidebar Footer Actions -->
    <div class="p-4 border-t border-slate-800 bg-slate-950/50 space-y-2 shrink-0">
        <a href="step1_household.php" class="gn-ripple gn-shine w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow-md flex items-center justify-center gap-2 transition">
            <i class="fa-solid fa-plus text-sm"></i>
            <span>අලුත් ගෙදරක් එකතු කරන්න</span>
        </a>
        <a href="logout.php" class="gn-ripple w-full bg-rose-600/10 hover:bg-rose-600 text-rose-300 hover:text-white font-bold text-xs px-4 py-2 rounded-xl flex items-center justify-center gap-2 transition">
            <i class="fa-solid fa-right-from-bracket text-sm"></i>
            <span>පද්ධතියෙන් ඉවත් වන්න</span>
        </a>
    </div>
</aside>

<!-- Main Content Wrapper (Right Side) -->
<div class="flex-1 lg:ml-64 flex flex-col min-h-screen w-full">

    <!-- Top Header / Navbar -->
    <header class="bg-white border-b border-slate-200 min-h-20 px-4 sm:px-8 py-3 flex items-center justify-between sticky top-0 z-30 shadow-sm no-print gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <button id="sidebarOpenBtn" class="lg:hidden text-slate-600 hover:text-blue-600 text-xl shrink-0">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="hidden sm:inline-flex text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg border border-blue-100 shrink-0">
                <i class="fa-solid <?php echo $page_icon; ?> mr-1"></i> <?php echo htmlspecialchars($page_title); ?>
            </span>
            <div class="min-w-0 hidden md:block">
                <?php if (!empty($breadcrumbs)): ?>
                    <nav class="text-xs text-slate-500 font-medium truncate">
                        <a href="dashboard.php" class="hover:text-blue-600"><i class="fa-solid fa-house text-[10px]"></i></a>
                        <?php foreach ($breadcrumbs as $bc): ?>
                            <span class="mx-1 text-slate-300">/</span>
                            <?php if (!empty($bc['url'])): ?>
                                <a href="<?php echo htmlspecialchars($bc['url']); ?>" class="hover:text-blue-600"><?php echo htmlspecialchars($bc['label']); ?></a>
                            <?php else: ?>
                                <span class="text-slate-700 font-semibold"><?php echo htmlspecialchars($bc['label']); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                <?php else: ?>
                    <h2 class="text-base font-bold text-slate-800 truncate">ග්‍රාම නිලධාරී වසම් තොරතුරු පද්ධතිය</h2>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <!-- Global Search -->
            <form action="search.php" method="GET" class="hidden md:flex items-center bg-slate-100 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-500/40 px-3 py-2 rounded-xl border border-slate-200 text-sm text-slate-600 w-56 lg:w-64 transition">
                <i class="fa-solid fa-magnifying-glass mr-2 text-slate-400"></i>
                <input type="text" name="q" placeholder="වසම තුළ සොයන්න..." class="bg-transparent outline-none w-full placeholder:text-slate-400">
            </form>
            <a href="search.php" class="md:hidden text-slate-500 hover:text-blue-600 text-lg w-9 h-9 flex items-center justify-center rounded-lg hover:bg-slate-100">
                <i class="fa-solid fa-magnifying-glass"></i>
            </a>

            <!-- Notifications -->
            <div class="relative">
                <button id="notifBtn" class="gn-bell relative text-slate-500 hover:text-blue-600 w-9 h-9 flex items-center justify-center rounded-lg hover:bg-slate-100 transition">
                    <i class="fa-solid fa-bell text-lg"></i>
                    <?php if (count($__notif_items) > 0): ?>
                        <span class="gn-pulse-ring absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white text-rose-500"></span>
                    <?php endif; ?>
                </button>
                <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50">
                    <div class="px-4 py-3 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-700">මෑතකදී එකතු කළ ගෘහ</span>
                        <span class="text-[10px] bg-blue-100 text-blue-700 font-bold px-2 py-0.5 rounded-full"><?php echo count($__notif_items); ?></span>
                    </div>
                    <div class="max-h-72 overflow-y-auto divide-y divide-slate-50">
                        <?php if (count($__notif_items) > 0): ?>
                            <?php foreach ($__notif_items as $n): ?>
                                <a href="household_view.php?id=<?php echo $n['id']; ?>" class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition">
                                    <div class="gn-badge-emerald gn-badge-grad w-8 h-8 shrink-0">
                                        <i class="fa-solid fa-house text-xs"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-800 truncate">ගෘහ අංකය <?php echo htmlspecialchars($n['hh_no']); ?></p>
                                        <p class="text-[11px] text-slate-500 truncate"><?php echo htmlspecialchars($n['address'] ?: '759/A ගල්හේන'); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs text-slate-400 text-center py-6">නව දැනුම්දීම් නොමැත.</p>
                        <?php endif; ?>
                    </div>
                    <a href="households_list.php" class="block text-center text-xs font-bold text-blue-600 hover:bg-blue-50 py-2.5">සියල්ල බලන්න</a>
                </div>
            </div>

            <!-- Admin Profile Dropdown -->
            <div class="relative pl-2 sm:pl-3 border-l border-slate-200">
                <button id="userMenuBtn" class="flex items-center gap-2 sm:gap-3 group">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 via-indigo-500 to-violet-600 text-white font-bold flex items-center justify-center shadow-lg shadow-blue-500/30 shrink-0 ring-2 ring-white group-hover:scale-105 transition">
                        <span><?php echo strtoupper(substr($_SESSION['full_name'] ?? 'GN', 0, 2)); ?></span>
                    </div>
                    <div class="hidden lg:block text-left">
                        <p class="text-xs font-bold text-slate-800 leading-tight"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'නිලධාරී'); ?></p>
                        <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($_SESSION['role'] ?? 'වසම 759/A'); ?></p>
                    </div>
                    <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 hidden lg:block"></i>
                </button>
                <div id="userMenuDropdown" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50">
                    <div class="px-4 py-3 bg-slate-50 border-b border-slate-100">
                        <p class="text-xs font-bold text-slate-800"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'නිලධාරී'); ?></p>
                        <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></p>
                    </div>
                    <a href="dashboard.php" class="flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                        <i class="fa-solid fa-gauge-high w-4"></i> පාලන පුවරුව
                    </a>
                    <a href="logout.php" class="flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">
                        <i class="fa-solid fa-right-from-bracket w-4"></i> ඉවත් වන්න
                    </a>
                </div>
            </div>
        </div>
    </header>

    <?php if ($__flash): ?>
        <div class="max-w-7xl w-full mx-auto px-4 sm:px-8 pt-4 no-print">
            <div class="flash-alert rounded-xl px-4 py-3 text-sm font-semibold flex items-center justify-between gap-3 shadow-sm border
                <?php echo $__flash['type'] === 'success' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200'; ?>">
                <span><i class="fa-solid <?php echo $__flash['type'] === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?> mr-2"></i><?php echo htmlspecialchars($__flash['msg']); ?></span>
                <button onclick="this.closest('.flash-alert').remove()" class="text-current opacity-60 hover:opacity-100"><i class="fa-solid fa-xmark"></i></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Page Content -->
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-8 py-6 sm:py-8 flex-grow">
