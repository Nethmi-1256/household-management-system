<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Filter Presets & Input Values
$filter_type = $_GET['type'] ?? 'voter'; // voter, infants, school, working, seniors, custom
$search = trim($_GET['search'] ?? '');
$gender = $_GET['gender'] ?? '';
$min_age = $_GET['min_age'] ?? '';
$max_age = $_GET['max_age'] ?? '';

// Preset Logic setup
if ($filter_type === 'voter') {
    $min_age = 18;
    $max_age = '';
    $page_title = "ඡන්ද හිමි නාමලේඛනය (වයස 18+)";
} elseif ($filter_type === 'infants') {
    $min_age = 0;
    $max_age = 4;
    $page_title = "ළදරු සහ පෙර පාසල් ළමුන් (වයස 0-4)";
} elseif ($filter_type === 'school') {
    $min_age = 5;
    $max_age = 18;
    $page_title = "පාසල් වයසේ ළමුන් (වයස 5-18)";
} elseif ($filter_type === 'working') {
    $min_age = 19;
    $max_age = 59;
    $page_title = "ඵලදායී / වැඩකරන ජනගහනය (වයස 19-59)";
} elseif ($filter_type === 'seniors') {
    $min_age = 60;
    $max_age = '';
    $page_title = "ජ්‍යෙෂ්ඨ පුරවැසියන් (වයස 60+)";
} else {
    $page_title = "විශේෂිත වයස් කාණ්ඩ සෙවීම (Custom Filter)";
}

try {
    $where_clauses = ["m.dob IS NOT NULL"];
    $params = [];

    // Age Filter conditions
    if ($min_age !== '') {
        $where_clauses[] = "TIMESTAMPDIFF(YEAR, m.dob, CURDATE()) >= :min_age";
        $params['min_age'] = (int)$min_age;
    }
    if ($max_age !== '') {
        $where_clauses[] = "TIMESTAMPDIFF(YEAR, m.dob, CURDATE()) <= :max_age";
        $params['max_age'] = (int)$max_age;
    }
    if (!empty($gender)) {
        $where_clauses[] = "m.gender = :gender";
        $params['gender'] = $gender;
    }
    if (!empty($search)) {
        $where_clauses[] = "(m.full_name LIKE :search OR m.nic LIKE :search OR h.hh_no LIKE :search)";
        $params['search'] = "%" . $search . "%";
    }

    $where_sql = implode(' AND ', $where_clauses);

    // Fetch members list with calculated age
    $query = "SELECT m.*, h.hh_no, h.address, TIMESTAMPDIFF(YEAR, m.dob, CURDATE()) AS calculated_age 
              FROM members m 
              LEFT JOIN households h ON m.household_id = h.id 
              WHERE {$where_sql} 
              ORDER BY calculated_age DESC, m.full_name ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $members = $stmt->fetchAll();

    // Summary Counts
    $total_count = count($members);
    $male_count = 0;
    $female_count = 0;
    foreach ($members as $m) {
        if (($m['gender'] ?? '') === 'Male') $male_count++;
        if (($m['gender'] ?? '') === 'Female') $female_count++;
    }

} catch (PDOException $e) {
    die("දත්ත ලබා ගැනීමේ දෝෂයකි: " . $e->getMessage());
}
?>
<?php
$active      = 'voters';
$page_title  = 'ඡන්ද/වයස් ලැයිස්තු';
$page_icon   = 'fa-check-to-slot';
$breadcrumbs = [['label' => 'ඡන්ද/වයස් ලැයිස්තු']];
$extra_head  = '<style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 12pt; }
            .container-fluid { width: 100% !important; padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; }
            .table-responsive { overflow: visible !important; }
        }
    </style>';
require 'includes/header.php';
?>
        <!-- Page Header & Action Controls -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    <i class="fa-solid fa-users-viewfinder text-blue-600 mr-2"></i><?php echo $page_title; ?>
                </h2>
                <p class="text-sm text-slate-500 mt-1">759/A ගල්හේන - වයස් කාණ්ඩ සහ ඡන්ද හිමි නාමලේඛන ලබාගැනීම</p>
            </div>
            <div class="flex flex-wrap gap-2 no-print">
                <button onclick="window.print();" class="btn btn-success font-bold text-sm rounded-lg px-4 shadow-sm">
                    <i class="fa-solid fa-print mr-1.5"></i> ලැයිස්තුව Print කරගන්න
                </button>
            </div>
        </div>

        <!-- Quick Filter Presets Buttons -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6 no-print">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block">Quick Presets (ඉක්මන් තෝරාගැනීම්):</label>
            <div class="flex flex-wrap gap-2">
                <a href="voters_filters.php?type=voter" class="btn btn-sm font-semibold rounded-lg <?php echo $filter_type === 'voter' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    🗳️ ඡන්දදායකයින් (18+)
                </a>
                <a href="voters_filters.php?type=infants" class="btn btn-sm font-semibold rounded-lg <?php echo $filter_type === 'infants' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    👶 ළදරු/පෙර පාසල් (0-4)
                </a>
                <a href="voters_filters.php?type=school" class="btn btn-sm font-semibold rounded-lg <?php echo $filter_type === 'school' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    🎒 පාසල් වයස (5-18)
                </a>
                <a href="voters_filters.php?type=working" class="btn btn-sm font-semibold rounded-lg <?php echo $filter_type === 'working' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    💼 වැඩකරන ජනගහනය (19-59)
                </a>
                <a href="voters_filters.php?type=seniors" class="btn btn-sm font-semibold rounded-lg <?php echo $filter_type === 'seniors' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    👴 ජ්‍යෙෂ්ඨ පුරවැසියන් (60+)
                </a>
            </div>
        </div>

        <!-- Custom Filter & Search Form -->
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 mb-6 no-print">
            <form method="GET" action="voters_filters.php" class="row g-3 items-end">
                <input type="hidden" name="type" value="custom">

                <div class="col-md-3">
                    <label class="form-label text-xs font-bold text-slate-600">නම / ජා.හැ. අංකය / ගෘහ අංකය</label>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control form-control-sm" placeholder="සොයන්න...">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label text-xs font-bold text-slate-600">අවම වයස (Min)</label>
                    <input type="number" name="min_age" value="<?php echo htmlspecialchars($min_age); ?>" class="form-control form-control-sm" placeholder="0">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label text-xs font-bold text-slate-600">උපරිම වයස (Max)</label>
                    <input type="number" name="max_age" value="<?php echo htmlspecialchars($max_age); ?>" class="form-control form-control-sm" placeholder="100">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-xs font-bold text-slate-600">ස්ත්‍රී / පුරුෂ භාවය</label>
                    <select name="gender" class="form-select form-select-sm">
                        <option value="">සියල්ල (All)</option>
                        <option value="Male" <?php echo $gender === 'Male' ? 'selected' : ''; ?>>පුරුෂ (Male)</option>
                        <option value="Female" <?php echo $gender === 'Female' ? 'selected' : ''; ?>>ස්ත්‍රී (Female)</option>
                    </select>
                </div>

                <div class="col-md-2 flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm font-bold w-100">
                        <i class="fa-solid fa-filter mr-1"></i> Filter
                    </button>
                    <a href="voters_filters.php?type=voter" class="btn btn-outline-secondary btn-sm font-bold">Clear</a>
                </div>
            </form>
        </div>

        <!-- KPI Count Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center border-l-4 border-l-blue-600">
                <p class="text-xs font-bold text-slate-400 uppercase">සම්පූර්ණ ප්‍රමාණය</p>
                <h3 class="text-2xl font-extrabold text-blue-900 mt-1"><?php echo number_format($total_count); ?></h3>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center border-l-4 border-l-cyan-500">
                <p class="text-xs font-bold text-slate-400 uppercase">පුරුෂ (Male)</p>
                <h3 class="text-2xl font-extrabold text-cyan-800 mt-1"><?php echo number_format($male_count); ?></h3>
            </div>
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center border-l-4 border-l-pink-500">
                <p class="text-xs font-bold text-slate-400 uppercase">ස්ත්‍රී (Female)</p>
                <h3 class="text-2xl font-extrabold text-pink-700 mt-1"><?php echo number_format($female_count); ?></h3>
            </div>
        </div>

        <!-- Filtered Results Table -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-sm mb-0">
                    <thead class="table-light">
                        <tr class="text-slate-600">
                            <th>#</th>
                            <th>සම්පූර්ණ නම</th>
                            <th>ජා.හැ. අංකය (NIC)</th>
                            <th>උපන් දිනය</th>
                            <th>වයස</th>
                            <th>ස්ත්‍රී/පුරුෂ</th>
                            <th>ගෘහ අංකය</th>
                            <th>ලිපිනය</th>
                            <th class="text-end no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($members) > 0): ?>
                            <?php $i = 1; foreach ($members as $m): ?>
                                <tr>
                                    <td class="text-slate-400 font-bold"><?php echo $i++; ?></td>
                                    <td class="font-semibold text-slate-800"><?php echo htmlspecialchars($m['full_name'] ?? 'නම ඇතුළත් කර නැත'); ?></td>
                                    <td>
                                        <?php if (!empty($m['nic'])): ?>
                                            <span class="badge bg-slate-100 text-slate-700 border font-mono"><?php echo htmlspecialchars($m['nic']); ?></span>
                                        <?php else: ?>
                                            <span class="text-slate-400 text-xs">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-slate-600"><?php echo htmlspecialchars($m['dob'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge bg-blue-50 text-blue-700 border border-blue-200 rounded-pill px-2.5 py-1">
                                            <?php echo $m['calculated_age']; ?> අවුරුදු
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($m['gender'] ?? '') === 'Male'): ?>
                                            <span class="text-cyan-700 font-bold"><i class="fa-solid fa-mars mr-1"></i>පුරුෂ</span>
                                        <?php else: ?>
                                            <span class="text-pink-600 font-bold"><i class="fa-solid fa-venus mr-1"></i>ස්ත්‍රී</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="font-bold text-blue-700"><?php echo htmlspecialchars($m['hh_no'] ?? '-'); ?></td>
                                    <td class="text-slate-600"><?php echo htmlspecialchars($m['address'] ?? 'ගල්හේන'); ?></td>
                                    <td class="text-end no-print">
                                        <a href="household_view.php?id=<?php echo $m['household_id']; ?>" class="btn btn-sm btn-light border text-blue-600 font-semibold hover:bg-blue-600 hover:text-white transition">
                                            ගෘහය බලන්න
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-8 text-slate-400">
                                    <i class="fa-solid fa-user-slash text-3xl mb-2 block"></i>
                                    තෝරාගත් නිර්ණායක වලට අදාළව සාමාජිකයින් හමුවූයේ නැත.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php require 'includes/footer.php'; ?>