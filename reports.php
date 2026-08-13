<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


try {
    // 1. ප්‍රධාන සංඛ්‍යාලේඛන (Overview Counts)
    $stmt_hh = $pdo->query("SELECT COUNT(*) AS total_hh FROM households");
    $total_hh = $stmt_hh->fetchColumn();

    $stmt_m = $pdo->query("SELECT 
        COUNT(*) AS total_members,
        SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) AS total_male,
        SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) AS total_female
        FROM members");
    $member_stats = $stmt_m->fetch();

    $total_members = $member_stats['total_members'] ?: 0;
    $total_male = $member_stats['total_male'] ?: 0;
    $total_female = $member_stats['total_female'] ?: 0;
    $avg_per_hh = $total_hh > 0 ? round($total_members / $total_hh, 1) : 0;

    // 2. වයස් කාණ්ඩ අනුව සංඛ්‍යාලේඛන (Age Group Breakdown)
    $stmt_age = $pdo->query("SELECT 
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 0 AND 4 THEN 1 ELSE 0 END) AS age_0_4,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 5 AND 14 THEN 1 ELSE 0 END) AS age_5_14,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 15 AND 59 THEN 1 ELSE 0 END) AS age_15_59,
        SUM(CASE WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= 60 THEN 1 ELSE 0 END) AS age_60_plus
        FROM members WHERE dob IS NOT NULL");
    $age_stats = $stmt_age->fetch();

    // 3. අධ්‍යාපන මට්ටම අනුව (Education Counts)
    $stmt_edu = $pdo->query("SELECT educationLevel, COUNT(*) as cnt FROM members GROUP BY educationLevel ORDER BY cnt DESC");
    $edu_data = $stmt_edu->fetchAll();

    // 4. රැකියා තත්ත්වය අනුව (Employment Counts)
    $stmt_emp = $pdo->query("SELECT employment_status, COUNT(*) as cnt FROM members GROUP BY employment_status ORDER BY cnt DESC");
    $emp_data = $stmt_emp->fetchAll();

    // 5. ජල මූලාශ්‍ර අනුව (Water Source Counts)
    $stmt_water = $pdo->query("SELECT water_source, COUNT(*) as cnt FROM households GROUP BY water_source");
    $water_data = $stmt_water->fetchAll();

    // 6. නිවාස ව්‍යුහය අනුව (Housing Structure Counts)
    $stmt_struct = $pdo->query("SELECT housing_structure, COUNT(*) as cnt FROM households GROUP BY housing_structure");
    $struct_data = $stmt_struct->fetchAll();

    // 7. වාහන හිමිකාරිත්වය (Vehicle Counts)
    $stmt_veh = $pdo->query("SELECT 
        SUM(has_threewheel) AS threewheel,
        SUM(has_motorcycle) AS motorcycle,
        SUM(has_bicycle) AS bicycle,
        SUM(has_other_vehicle) AS other_veh
        FROM members");
    $veh_data = $stmt_veh->fetch();

    // 8. විද්‍යුත් උපකරණ සහ සන්නිවේදනය (Tech & Asset Counts)
    $stmt_asset = $pdo->query("SELECT 
        SUM(has_smart_phone) AS smart_phone,
        SUM(has_tv) AS tv,
        SUM(has_radio) AS radio,
        SUM(has_laptop) AS laptop,
        SUM(has_land_phone) AS land_phone
        FROM members");
    $asset_data = $stmt_asset->fetch();

} catch (PDOException $e) {
    die("වාර්තා දත්ත ලබා ගැනීමට නොහැකි විය: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Counts - GN 759/A Galhena</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js for Visual Graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .shadow-sm, .shadow { box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 text-gray-800 font-sans py-8 px-4">

    <div class="max-w-7xl mx-auto">
        
        <!-- Header & Action Controls -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 bg-white p-5 rounded-xl shadow-sm border">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">📊 වාර්තා සහ ගණනය කිරීම් (Reports & Counts)</h1>
                <p class="text-sm text-gray-500">759/A ගල්හේන ග්‍රාම නිලධාරී වසමේ සමස්ත දත්ත විග්‍රහය</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-2 no-print">
                <a href="dashboard.php" class="btn btn-outline-secondary font-semibold">&larr; Dashboard</a>
                <a href="households_list.php" class="btn btn-outline-primary font-semibold">🏠 ගෘහ ලැයිස්තුව</a>
                <button onclick="window.print();" class="btn btn-success font-bold">🖨️ Print / Save PDF</button>
            </div>
        </div>

        <!-- Overview KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-xl border shadow-sm text-center border-l-4 border-l-blue-600">
                <div class="text-xs font-bold text-gray-500 uppercase">සම්පූර්ණ ගෘහ ගණන</div>
                <div class="text-3xl font-extrabold text-blue-600 mt-1"><?php echo number_format($total_hh); ?></div>
                <div class="text-xs text-gray-400 mt-1">Households</div>
            </div>
            <div class="bg-white p-5 rounded-xl border shadow-sm text-center border-l-4 border-l-emerald-600">
                <div class="text-xs font-bold text-gray-500 uppercase">සම්පූර්ණ ජනගහනය</div>
                <div class="text-3xl font-extrabold text-emerald-600 mt-1"><?php echo number_format($total_members); ?></div>
                <div class="text-xs text-gray-400 mt-1">Total Population</div>
            </div>
            <div class="bg-white p-5 rounded-xl border shadow-sm text-center border-l-4 border-l-purple-600">
                <div class="text-xs font-bold text-gray-500 uppercase">ස්ත්‍රී / පුරුෂ අනුපාතය</div>
                <div class="text-xl font-bold text-purple-700 mt-2">
                    👨‍💼 <?php echo $total_male; ?> | 👩‍💼 <?php echo $total_female; ?>
                </div>
                <div class="text-xs text-gray-400 mt-1">Male / Female</div>
            </div>
            <div class="bg-white p-5 rounded-xl border shadow-sm text-center border-l-4 border-l-amber-500">
                <div class="text-xs font-bold text-gray-500 uppercase">ගෘහයක සාමාන්‍ය පිරිස</div>
                <div class="text-3xl font-extrabold text-amber-600 mt-1"><?php echo $avg_per_hh; ?></div>
                <div class="text-xs text-gray-400 mt-1">Avg Members / Household</div>
            </div>
        </div>

        <!-- Charts Row 1: Age & Employment -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Age Group Chart & Table -->
            <div class="bg-white p-5 rounded-xl border shadow-sm">
                <h3 class="text-md font-bold text-gray-800 mb-3 border-b pb-2">👶 වයස් කාණ්ඩ අනුව ජනගහනය</h3>
                <div class="h-56">
                    <canvas id="ageChart"></canvas>
                </div>
                <table class="table table-sm text-sm mt-4 mb-0">
                    <thead class="table-light">
                        <tr><th>වයස් කාණ්ඩය</th><th class="text-end">සංඛ්‍යාව</th><th class="text-end">ප්‍රතිශතය</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>අවුරුදු 0 - 4 (ළදරු/පෙර පාසල්)</td><td class="text-end font-bold"><?php echo $age_stats['age_0_4'] ?: 0; ?></td><td class="text-end"><?php echo $total_members ? round(($age_stats['age_0_4']/$total_members)*100, 1) : 0; ?>%</td></tr>
                        <tr><td>අවුරුදු 5 - 14 (පාසල් වයස)</td><td class="text-end font-bold"><?php echo $age_stats['age_5_14'] ?: 0; ?></td><td class="text-end"><?php echo $total_members ? round(($age_stats['age_5_14']/$total_members)*100, 1) : 0; ?>%</td></tr>
                        <tr><td>අවුරුදු 15 - 59 (ඵලදායී වයස)</td><td class="text-end font-bold"><?php echo $age_stats['age_15_59'] ?: 0; ?></td><td class="text-end"><?php echo $total_members ? round(($age_stats['age_15_59']/$total_members)*100, 1) : 0; ?>%</td></tr>
                        <tr><td>අවුරුදු 60+ (ජ්‍යෙෂ්ඨ පුරවැසියන්)</td><td class="text-end font-bold"><?php echo $age_stats['age_60_plus'] ?: 0; ?></td><td class="text-end"><?php echo $total_members ? round(($age_stats['age_60_plus']/$total_members)*100, 1) : 0; ?>%</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- Employment Chart & Table -->
            <div class="bg-white p-5 rounded-xl border shadow-sm">
                <h3 class="text-md font-bold text-gray-800 mb-3 border-b pb-2">💼 රැකියා/ස්වයං රැකියා තත්ත්වය</h3>
                <div class="h-56">
                    <canvas id="empChart"></canvas>
                </div>
                <table class="table table-sm text-sm mt-4 mb-0">
                    <thead class="table-light">
                        <tr><th>තත්ත්වය</th><th class="text-end">සංඛ්‍යාව</th><th class="text-end">ප්‍රතිශතය</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($emp_data as $emp): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($emp['employment_status'] ?: 'සඳහන් කර නැත'); ?></td>
                            <td class="text-end font-bold"><?php echo $emp['cnt']; ?></td>
                            <td class="text-end"><?php echo $total_members ? round(($emp['cnt']/$total_members)*100, 1) : 0; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Charts Row 2: Education & Infrastructure -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Education Table & Progress -->
            <div class="bg-white p-5 rounded-xl border shadow-sm">
                <h3 class="text-md font-bold text-gray-800 mb-3 border-b pb-2">🎓 අධ්‍යාපන මට්ටම (Education Level)</h3>
                <table class="table table-hover text-sm mb-0">
                    <thead class="table-light">
                        <tr><th>මට්ටම</th><th class="text-end">සංඛ්‍යාව</th><th class="text-end">ප්‍රතිශතය</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($edu_data as $edu): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($edu['educationLevel'] ?: 'සඳහන් කර නැත'); ?></td>
                            <td class="text-end font-bold"><?php echo $edu['cnt']; ?></td>
                            <td class="text-end"><?php echo $total_members ? round(($edu['cnt']/$total_members)*100, 1) : 0; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Water Source Breakdown -->
            <div class="bg-white p-5 rounded-xl border shadow-sm">
                <h3 class="text-md font-bold text-gray-800 mb-3 border-b pb-2">🚰 ජල මූලාශ්‍ර භාවිතය (Water Source)</h3>
                <div class="h-48 mb-3">
                    <canvas id="waterChart"></canvas>
                </div>
                <table class="table table-sm text-sm mb-0">
                    <thead class="table-light">
                        <tr><th>මූලාශ්‍රය</th><th class="text-end">ගෘහ සංඛ්‍යාව</th><th class="text-end">ප්‍රතිශතය</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($water_data as $w): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($w['water_source'] ?: 'සඳහන් කර නැත'); ?></td>
                            <td class="text-end font-bold"><?php echo $w['cnt']; ?></td>
                            <td class="text-end"><?php echo $total_hh ? round(($w['cnt']/$total_hh)*100, 1) : 0; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Vehicles & Asset Summary Row -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Vehicle Ownership -->
            <div class="bg-white p-5 rounded-xl border shadow-sm">
                <h3 class="text-md font-bold text-gray-800 mb-3 border-b pb-2">🛵 වාහන හිමිකාරිත්වය (Vehicles Count)</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 bg-slate-50 border rounded-lg text-center">
                        <span class="text-2xl">🛺</span>
                        <div class="text-xs text-gray-500 font-bold mt-1">ත්‍රිරෝද රථ (3-Wheelers)</div>
                        <div class="text-xl font-extrabold text-blue-700"><?php echo number_format($veh_data['threewheel'] ?: 0); ?></div>
                    </div>
                    <div class="p-3 bg-slate-50 border rounded-lg text-center">
                        <span class="text-2xl">🏍️</span>
                        <div class="text-xs text-gray-500 font-bold mt-1">මෝටර් සයිකල්</div>
                        <div class="text-xl font-extrabold text-blue-700"><?php echo number_format($veh_data['motorcycle'] ?: 0); ?></div>
                    </div>
                    <div class="p-3 bg-slate-50 border rounded-lg text-center">
                        <span class="text-2xl">🚲</span>
                        <div class="text-xs text-gray-500 font-bold mt-1">පාපැදි (Bicycles)</div>
                        <div class="text-xl font-extrabold text-blue-700"><?php echo number_format($veh_data['bicycle'] ?: 0); ?></div>
                    </div>
                    <div class="p-3 bg-slate-50 border rounded-lg text-center">
                        <span class="text-2xl">🚗</span>
                        <div class="text-xs text-gray-500 font-bold mt-1">වෙනත් වාහන</div>
                        <div class="text-xl font-extrabold text-blue-700"><?php echo number_format($veh_data['other_veh'] ?: 0); ?></div>
                    </div>
                </div>
            </div>

            <!-- Electronic Assets -->
            <div class="bg-white p-5 rounded-xl border shadow-sm">
                <h3 class="text-md font-bold text-gray-800 mb-3 border-b pb-2">📱 විද්‍යුත් උපකරණ සහ සන්නිවේදනය</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-center">
                        <span class="text-2xl">📱</span>
                        <div class="text-xs text-emerald-800 font-bold mt-1">Smart Phones</div>
                        <div class="text-lg font-extrabold text-emerald-700"><?php echo number_format($asset_data['smart_phone'] ?: 0); ?></div>
                    </div>
                    <div class="p-3 bg-sky-50 border border-sky-200 rounded-lg text-center">
                        <span class="text-2xl">📺</span>
                        <div class="text-xs text-sky-800 font-bold mt-1">TV</div>
                        <div class="text-lg font-extrabold text-sky-700"><?php echo number_format($asset_data['tv'] ?: 0); ?></div>
                    </div>
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-center">
                        <span class="text-2xl">📻</span>
                        <div class="text-xs text-amber-800 font-bold mt-1">Radio</div>
                        <div class="text-lg font-extrabold text-amber-700"><?php echo number_format($asset_data['radio'] ?: 0); ?></div>
                    </div>
                    <div class="p-3 bg-purple-50 border border-purple-200 rounded-lg text-center">
                        <span class="text-2xl">💻</span>
                        <div class="text-xs text-purple-800 font-bold mt-1">Laptop</div>
                        <div class="text-lg font-extrabold text-purple-700"><?php echo number_format($asset_data['laptop'] ?: 0); ?></div>
                    </div>
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-center col-span-2 md:col-span-2">
                        <span class="text-2xl">☎️</span>
                        <div class="text-xs text-rose-800 font-bold mt-1">ජංගම නොවන දුරකථන (Landline)</div>
                        <div class="text-lg font-extrabold text-rose-700"><?php echo number_format($asset_data['land_phone'] ?: 0); ?></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Chart.js Setup Script -->
    <script>
        // 1. Age Distribution Doughnut Chart
        new Chart(document.getElementById('ageChart'), {
            type: 'doughnut',
            data: {
                labels: ['0-4 අවුරුදු', '5-14 අවුරුදු', '15-59 අවුරුදු', '60+ අවුරුදු'],
                datasets: [{
                    data: [
                        <?php echo $age_stats['age_0_4'] ?: 0; ?>,
                        <?php echo $age_stats['age_5_14'] ?: 0; ?>,
                        <?php echo $age_stats['age_15_59'] ?: 0; ?>,
                        <?php echo $age_stats['age_60_plus'] ?: 0; ?>
                    ],
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 2. Employment Bar Chart
        new Chart(document.getElementById('empChart'), {
            type: 'bar',
            data: {
                labels: [<?php foreach($emp_data as $e) echo "'" . addslashes($e['employment_status'] ?: 'වෙනත්') . "',"; ?>],
                datasets: [{
                    label: 'සාමාජිකයින් ගණන',
                    data: [<?php foreach($emp_data as $e) echo $e['cnt'] . ","; ?>],
                    backgroundColor: '#6366f1'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // 3. Water Source Pie Chart
        new Chart(document.getElementById('waterChart'), {
            type: 'pie',
            data: {
                labels: [<?php foreach($water_data as $w) echo "'" . addslashes($w['water_source'] ?: 'වෙනත්') . "',"; ?>],
                datasets: [{
                    data: [<?php foreach($water_data as $w) echo $w['cnt'] . ","; ?>],
                    backgroundColor: ['#06b6d4', '#10b981', '#8b5cf6', '#f43f5e']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>

</body>
</html>