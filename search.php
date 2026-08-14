<?php

session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Inputs
$q = trim($_GET['q'] ?? '');
$filter_gender = $_GET['gender'] ?? '';
$filter_employment = $_GET['employment'] ?? '';

$results = [];
$total_results = 0;
$error = null;

if ($q !== '' || $filter_gender !== '' || $filter_employment !== '') {
    try {
        $where_clauses = ["1=1"];
        $params = [];

        // Main keyword search
        if ($q !== '') {
            $where_clauses[] = "(
                m.full_name LIKE :q 
                OR m.nic LIKE :q 
                OR m.occupation LIKE :q 
                OR h.hh_no LIKE :q 
                OR h.address LIKE :q
            )";
            $params['q'] = '%' . $q . '%';
        }

        // Gender filter
        if (!empty($filter_gender)) {
            $where_clauses[] = "m.gender = :gender";
            $params['gender'] = $filter_gender;
        }

        // Employment Status filter
        if (!empty($filter_employment)) {
            $where_clauses[] = "m.employment_status = :employment";
            $params['employment'] = $filter_employment;
        }

        $where_sql = implode(' AND ', $where_clauses);

        // SQL Query strictly matched with the database schema
        $sql = "SELECT m.*, h.hh_no, h.address,
                       TIMESTAMPDIFF(YEAR, m.dob, CURDATE()) AS calculated_age
                FROM members m
                LEFT JOIN households h ON m.household_id = h.id
                WHERE {$where_sql}
                ORDER BY h.hh_no ASC, m.full_name ASC
                LIMIT 100";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll();
        $total_results = count($results);

    } catch (PDOException $e) {
        $error = "දත්ත සෙවීමේදී දෝෂයක් සිදු විය: " . $e->getMessage();
    }
}
?>
<?php
$active      = 'search';
$page_title  = 'තොරතුරු සෙවීම';
$page_icon   = 'fa-magnifying-glass';
$breadcrumbs = [['label' => 'සොයන්න']];
$extra_head  = '<style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11pt; }
            .container-fluid { width: 100% !important; padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; }
            .table-responsive { overflow: visible !important; }
        }
    </style>';
require 'includes/header.php';
?>
        <!-- Search Header Box -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 mb-6 no-print">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">
                <i class="fa-solid fa-magnifying-glass text-blue-600 mr-2"></i>වසමේ දත්ත සොයන්න (Search Engine)
            </h2>
            <p class="text-sm text-slate-500 mb-6">නම, ජාතික හැඳුනුම්පත් අංකය (NIC), රැකියාව, ගෘහ අංකය හෝ ලිපිනය ඇතුළත් කර සෙවීම් කරන්න.</p>

            <form method="GET" action="search.php" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-xs font-bold text-slate-600">සෙවුම් පද (Keyword Search)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-slate-100 text-slate-500 border-end-0"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" name="q" value="<?php echo htmlspecialchars($q); ?>" class="form-control border-start-0" placeholder="උදා: 199012345678, පෙරේරා, 759/A/12, ගුරු...">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-xs font-bold text-slate-600">ස්ත්‍රී / පුරුෂ භාවය</label>
                    <select name="gender" class="form-select">
                        <option value="">සියල්ල (All)</option>
                        <option value="Male" <?php echo $filter_gender === 'Male' ? 'selected' : ''; ?>>පුරුෂ (Male)</option>
                        <option value="Female" <?php echo $filter_gender === 'Female' ? 'selected' : ''; ?>>ස්ත්‍රී (Female)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-xs font-bold text-slate-600">රැකියා තත්ත්වය</label>
                    <select name="employment" class="form-select">
                        <option value="">සියල්ල (All)</option>
                        <option value="Government" <?php echo $filter_employment === 'Government' ? 'selected' : ''; ?>>රජයේ (Government)</option>
                        <option value="Private" <?php echo $filter_employment === 'Private' ? 'selected' : ''; ?>>පෞද්ගලික (Private)</option>
                        <option value="Semi-Government" <?php echo $filter_employment === 'Semi-Government' ? 'selected' : ''; ?>>අර්ධ රාජ්‍ය</option>
                        <option value="Self-Employed" <?php echo $filter_employment === 'Self-Employed' ? 'selected' : ''; ?>>ස්වයං රැකියා</option>
                        <option value="Foreign Employment" <?php echo $filter_employment === 'Foreign Employment' ? 'selected' : ''; ?>>විදේශ රැකියා</option>
                        <option value="Pensioner" <?php echo $filter_employment === 'Pensioner' ? 'selected' : ''; ?>>විශ්‍රාමික</option>
                        <option value="Unemployed" <?php echo $filter_employment === 'Unemployed' ? 'selected' : ''; ?>>රැකියා නොමැති</option>
                        <option value="Student" <?php echo $filter_employment === 'Student' ? 'selected' : ''; ?>>ශිෂ්‍ය/ශිෂ්‍යාව</option>
                    </select>
                </div>

                <div class="col-12 flex justify-between items-center mt-4">
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-primary font-bold px-5 py-2 rounded-lg shadow-sm">
                            <i class="fa-solid fa-magnifying-glass mr-2"></i> සෙවීම ආරම්භ කරන්න
                        </button>
                        <a href="search.php" class="btn btn-outline-secondary font-bold px-4 py-2 rounded-lg">Clear All</a>
                    </div>
                    <?php if ($total_results > 0): ?>
                        <button type="button" onclick="window.print();" class="btn btn-success font-bold text-sm rounded-lg px-4 shadow-sm">
                            <i class="fa-solid fa-print mr-1.5"></i> Print Results
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="alert alert-danger shadow-sm rounded-xl mb-6"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Results Section -->
        <?php if ($q !== '' || $filter_gender !== '' || $filter_employment !== ''): ?>
            
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800">
                    සෙවුම් ප්‍රතිඵල <span class="bg-blue-100 text-blue-800 text-xs px-2.5 py-1 rounded-full ml-2"><?php echo $total_results; ?> ක් හමුවිය</span>
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-sm mb-0">
                        <thead class="table-light">
                            <tr class="text-slate-600">
                                <th>#</th>
                                <th>නම / සම්බන්ධතාවය</th>
                                <th>ජා.හැ. අංකය (NIC)</th>
                                <th>වයස / ලිංගිකත්වය</th>
                                <th>රැකියා තත්ත්වය & රැකියාව</th>
                                <th>ගෘහ අංකය & ලිපිනය</th>
                                <th class="text-end no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_results > 0): ?>
                                <?php $i = 1; foreach ($results as $row): ?>
                                    <tr>
                                        <td class="text-slate-400 font-bold"><?php echo $i++; ?></td>
                                        <td>
                                            <div class="font-bold text-slate-800"><?php echo htmlspecialchars($row['full_name'] ?? 'නම යොමු කර නැත'); ?></div>
                                            <span class="text-xs text-slate-400"><?php echo htmlspecialchars($row['relationship'] ?? 'Head'); ?></span>
                                        </td>
                                        <td>
                                            <?php if (!empty($row['nic'])): ?>
                                                <span class="badge bg-slate-100 text-slate-700 border font-mono"><?php echo htmlspecialchars($row['nic']); ?></span>
                                            <?php else: ?>
                                                <span class="text-slate-400 text-xs">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="text-slate-700 font-medium"><?php echo $row['calculated_age'] ?? '-'; ?> අවුරුදු</div>
                                            <span class="text-xs font-semibold <?php echo ($row['gender'] ?? '') === 'Male' ? 'text-cyan-600' : 'text-pink-600'; ?>">
                                                <?php echo ($row['gender'] ?? '') === 'Male' ? 'පුරුෂ' : (($row['gender'] ?? '') === 'Female' ? 'ස්ත්‍රී' : '-'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="font-semibold text-slate-700"><?php echo htmlspecialchars($row['employment_status'] ?? '-'); ?></div>
                                            <?php if (!empty($row['occupation'])): ?>
                                                <span class="text-xs text-slate-500"><?php echo htmlspecialchars($row['occupation']); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="font-bold text-blue-700 block"><?php echo htmlspecialchars($row['hh_no'] ?? '-'); ?></span>
                                            <span class="text-xs text-slate-500"><?php echo htmlspecialchars($row['address'] ?? 'ගල්හේන'); ?></span>
                                        </td>
                                        <td class="text-end no-print">
                                            <a href="household_view.php?id=<?php echo $row['household_id']; ?>" class="btn btn-sm btn-light border text-blue-600 font-semibold hover:bg-blue-600 hover:text-white transition">
                                                ගෘහය බලන්න
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-10 text-slate-400">
                                        <i class="fa-solid fa-magnifying-glass text-4xl mb-3 block text-slate-300"></i>
                                        ඔබ ඇතුළත් කළ සෙවුම් පද වලට අදාළ කිසිදු තොරතුරක් හමුවූයේ නැත.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm">
                <i class="fa-solid fa-users-rectangle text-5xl text-slate-300 mb-4 block"></i>
                <h4 class="text-slate-700 font-bold text-lg mb-1">දත්ත සෙවීම සඳහා ඉහත සෙවුම් තීරුවේ තොරතුරු ඇතුළත් කරන්න</h4>
                <p class="text-slate-400 text-sm">නම, ජාතික හැඳුනුම්පත, රැකියාව හෝ ගෘහ අංකය ඇතුළත් කර 'සෙවීම ආරම්භ කරන්න' බොත්තම ඔබන්න.</p>
            </div>
        <?php endif; ?>

<?php require 'includes/footer.php'; ?>