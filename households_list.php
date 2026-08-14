<?php
session_start();
require_once 'config.php';

// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Filter සදහා GET Parameters ලබා ගැනීම
$search = trim($_GET['search'] ?? '');
$structure = trim($_GET['structure'] ?? '');
$water = trim($_GET['water'] ?? '');
$roof = trim($_GET['roof'] ?? '');

// මුලික SQL Query එක (Members count එකත් සමග)
$sql = "SELECT h.*, COUNT(m.id) AS member_count 
        FROM households h 
        LEFT JOIN members m ON h.id = m.household_id 
        WHERE 1=1";

$params = [];

// Search Filter (ගෘහ අංකය හෝ ලිපිනය අනුව)
if ($search !== '') {
    $sql .= " AND (h.hh_no LIKE ? OR h.address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Housing Structure Filter
if ($structure !== '') {
    $sql .= " AND h.housing_structure = ?";
    $params[] = $structure;
}

// Water Source Filter
if ($water !== '') {
    $sql .= " AND h.water_source = ?";
    $params[] = $water;
}

// Roof Material Filter
if ($roof !== '') {
    $sql .= " AND h.roof_material = ?";
    $params[] = $roof;
}

$sql .= " GROUP BY h.id ORDER BY h.id DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $households = $stmt->fetchAll();
} catch (PDOException $e) {
    die("දත්ත ලබා ගැනීමට නොහැකි විය: " . $e->getMessage());
}
?>
<?php
$active      = 'households';
$page_title  = 'ගෘහ ලැයිස්තුව';
$page_icon   = 'fa-house-user';
$breadcrumbs = [['label' => 'ගෘහ ලැයිස්තුව']];
require 'includes/header.php';
?>
            <!-- Page Title Header -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div>
                    <h1 class="text-xl font-bold text-slate-800">සියලුම ගෘහ ලැයිස්තුව (All Households)</h1>
                    <p class="text-xs text-slate-500 mt-1">759/A ගල්හේන ග්‍රාම නිලධාරී වසම</p>
                </div>
                <div class="mt-4 md:mt-0 flex gap-2">
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-sm font-semibold">&larr; Dashboard</a>
                    <a href="step1_household.php" class="btn btn-primary btn-sm font-bold">+ අලුත් ගෙදරක් එකතු කරන්න</a>
                </div>
            </div>

            <!-- Advanced Filters Box -->
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-200 mb-6">
                <h3 class="text-sm font-bold text-slate-700 mb-3">🔍 දත්ත Filter කරන්න (Filter Households)</h3>
                
                <form method="GET" action="" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    
                    <!-- Search Box -->
                    <div>
                        <label class="form-label text-xs font-bold text-slate-600">ගෘහ අංකය / ලිපිනය</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="උදා: 172/B හෝ ගල්හේන" value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <!-- Housing Structure Dropdown -->
                    <div>
                        <label class="form-label text-xs font-bold text-slate-600">නිවසේ ව්‍යුහය (Structure)</label>
                        <select name="structure" class="form-select form-select-sm">
                            <option value="">සියල්ල (All)</option>
                            <option value="Single house - single storeyed" <?php echo $structure === 'Single house - single storeyed' ? 'selected' : ''; ?>>Single house - single storeyed</option>
                            <option value="Single house - two storeyed" <?php echo $structure === 'Single house - two storeyed' ? 'selected' : ''; ?>>Single house - two storeyed</option>
                            <option value="Single house - more than two storeyed" <?php echo $structure === 'Single house - more than two storeyed' ? 'selected' : ''; ?>>Single house - more than two storeyed</option>
                            <option value="Attached house 1st Floor" <?php echo $structure === 'Attached house 1st Floor' ? 'selected' : ''; ?>>Attached house 1st Floor</option>
                            <option value="Other" <?php echo $structure === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- Water Source Dropdown -->
                    <div>
                        <label class="form-label text-xs font-bold text-slate-600">ජල මූලාශ්‍රය (Water Source)</label>
                        <select name="water" class="form-select form-select-sm">
                            <option value="">සියල්ල (All)</option>
                            <option value="Water Board" <?php echo $water === 'Water Board' ? 'selected' : ''; ?>>Water Board</option>
                            <option value="Well" <?php echo $water === 'Well' ? 'selected' : ''; ?>>Well (ළිං ජලය)</option>
                            <option value="Tube Well" <?php echo $water === 'Tube Well' ? 'selected' : ''; ?>>Tube Well</option>
                            <option value="Other" <?php echo $water === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <!-- Roof Material Dropdown -->
                    <div>
                        <label class="form-label text-xs font-bold text-slate-600">වහලයේ ද්‍රව්‍ය (Roof Material)</label>
                        <select name="roof" class="form-select form-select-sm">
                            <option value="">සියල්ල (All)</option>
                            <option value="Tile" <?php echo $roof === 'Tile' ? 'selected' : ''; ?>>Tile</option>
                            <option value="Asbestos" <?php echo $roof === 'Asbestos' ? 'selected' : ''; ?>>Asbestos</option>
                            <option value="Concrete" <?php echo $roof === 'Concrete' ? 'selected' : ''; ?>>Concrete</option>
                            <option value="Zink Aluminium Sheet" <?php echo $roof === 'Zink Aluminium Sheet' ? 'selected' : ''; ?>>Zink Aluminium Sheet</option>
                        </select>
                    </div>

                    <!-- Filter & Reset Buttons -->
                    <div class="md:col-span-4 flex justify-end gap-2 mt-2">
                        <a href="households_list.php" class="btn btn-light btn-sm text-slate-600 font-semibold border">Reset Filter</a>
                        <button type="submit" class="btn btn-primary btn-sm px-4 font-bold">Filter කරන්න &rarr;</button>
                    </div>

                </form>
            </div>

            <!-- Result Summary -->
            <div class="flex justify-between items-center mb-3">
                <div class="text-sm text-slate-600">
                    සොයාගත් ගෘහ සංඛ්‍යාව: <strong class="text-blue-700"><?php echo count($households); ?></strong>
                </div>
            </div>

            <!-- Households Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-sm mb-0">
                        <thead class="table-light">
                            <tr class="text-slate-600">
                                <th>#</th>
                                <th>ගෘහ අංකය (HH No)</th>
                                <th>ලිපිනය</th>
                                <th>ව්‍යුහය (Structure)</th>
                                <th>ජල මූලාශ්‍රය</th>
                                <th>වහලය</th>
                                <th class="text-center">සාමාජිකයින්</th>
                                <th class="text-end">ක්‍රියාකාරකම් (Actions)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($households) > 0): ?>
                                <?php foreach ($households as $idx => $hh): ?>
                                    <tr class="cursor-pointer hover:bg-blue-50/50 transition" onclick="window.location='household_view.php?id=<?php echo $hh['id']; ?>'">
                                        <td class="font-bold text-slate-500"><?php echo $idx + 1; ?></td>
                                        <td class="font-bold text-blue-700 text-base">
                                            <a href="household_view.php?id=<?php echo $hh['id']; ?>" class="hover:underline">
                                                <?php echo htmlspecialchars($hh['hh_no']); ?>
                                            </a>
                                        </td>
                                        <td class="text-slate-700"><?php echo htmlspecialchars($hh['address'] ?: '759/A ගල්හේන'); ?></td>
                                        <td><span class="badge bg-slate-100 text-slate-800 border"><?php echo htmlspecialchars($hh['housing_structure']); ?></span></td>
                                        <td class="text-slate-600"><?php echo htmlspecialchars($hh['water_source']); ?></td>
                                        <td class="text-slate-600"><?php echo htmlspecialchars($hh['roof_material']); ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-blue-50 text-blue-700 border border-blue-200 rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-user text-xs mr-1"></i><?php echo $hh['member_count']; ?> දෙනෙකි
                                            </span>
                                        </td>
                                        <td class="text-end" onclick="event.stopPropagation();">
                                            <div class="flex justify-end gap-1">
                                                <a href="household_view.php?id=<?php echo $hh['id']; ?>" class="btn btn-sm btn-light border font-bold text-blue-600 hover:bg-blue-600 hover:text-white transition">විස්තර</a>
                                                <a href="edit_household.php?id=<?php echo $hh['id']; ?>" class="btn btn-sm btn-outline-warning">✏️</a>
                                                <a href="delete_household.php?id=<?php echo $hh['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('මෙම ගෘහය මකා දැමීමට විශ්වාසද?');">🗑️</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-6 text-slate-400">
                                        <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                        ගැළපෙන ගෘහ විස්තර සොයා ගැනීමට නොහැකි විය.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

<?php require 'includes/footer.php'; ?>