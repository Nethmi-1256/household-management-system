<?php
session_start();
require_once 'config.php';

// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කිරීම
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // 1. මුළු ගෘහ ගණන (Total Households)
    $total_households = $pdo->query("SELECT COUNT(*) FROM households")->fetchColumn() ?: 0;
    // ... (ඉතිරි කේතය සාමාන්‍ය පරිදි පවතී)

    // 2. මුළු ජනගහනය (Total Members)
    $total_members = $pdo->query("SELECT COUNT(*) FROM members")->fetchColumn() ?: 0;

    // 3. ස්ත්‍රී / පුරුෂ ගණන (Gender Distribution)
    $gender_stmt = $pdo->query("SELECT gender, COUNT(*) as count FROM members GROUP BY gender");
    $gender_raw = $gender_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $male_count = $gender_raw['Male'] ?? 0;
    $female_count = $gender_raw['Female'] ?? 0;

    // 4. රැකියා තත්ත්වය (Employment Status)
    $emp_stmt = $pdo->query("SELECT employment_status, COUNT(*) as count FROM members GROUP BY employment_status ORDER BY count DESC LIMIT 6");
    $emp_data = $emp_stmt->fetchAll();
    
    $emp_labels = array_column($emp_data, 'employment_status');
    $emp_counts = array_column($emp_data, 'count');

    // 5. උපකරණ සහ වාහන එකතුව (Assets Summary)
    $assets_stmt = $pdo->query("SELECT 
        SUM(has_smart_phone) as smart_phone,
        SUM(has_laptop) as laptop,
        SUM(has_threewheel) as threewheel,
        SUM(has_motorcycle) as motorcycle,
        SUM(has_bicycle) as bicycle
        FROM members");
    $assets = $assets_stmt->fetch() ?: [];

    // 6. අවසානයට ඇතුළත් කළ ගෘහ 5 (Recent Households)
    $recent_stmt = $pdo->query("SELECT h.*, COUNT(m.id) as member_count 
                                FROM households h 
                                LEFT JOIN members m ON h.id = m.household_id 
                                GROUP BY h.id 
                                ORDER BY h.id DESC LIMIT 5");
    $recent_households = $recent_stmt->fetchAll();

} catch (PDOException $e) {
    die("පද්ධති දත්ත ලබා ගැනීමේ දෝෂයකි: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="si">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GN 759/A Galhena</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans min-h-screen flex">

    <!-- Sidebar Navigation (Left) -->
    <aside class="w-64 bg-slate-900 text-white flex flex-col fixed inset-y-0 left-0 z-50 shadow-xl">
        <!-- Branding -->
        <div class="h-20 flex items-center gap-3 px-6 bg-slate-950 border-b border-slate-800">
            <div class="bg-blue-600 text-white p-2.5 rounded-xl text-lg font-bold shadow">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <div>
                <h1 class="text-sm font-bold tracking-wide leading-tight">759/A ගල්හේන  </h1>
                <p class="text-[11px] text-slate-400">ග්‍රාම නිලධාරී වසම</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 px-3 py-6 space-y-1.5 overflow-y-auto">
            <a href="dashboard.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold bg-blue-600 text-white shadow-sm">
                <i class="fa-solid fa-chart-line w-5 text-center text-base"></i> Dashboard
            </a>
            <a href="households_list.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/70 transition">
                <i class="fa-solid fa-house-user w-5 text-center text-base"></i> ගෘහ ලැයිස්තුව
            </a>
            <a href="voters_filters.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/70 transition">
                <i class="fa-solid fa-check-to-slot w-5 text-center text-base"></i> ඡන්ද/වයස් ලැයිස්තු
            </a>
            <a href="welfare_tracking.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/70 transition">
                <i class="fa-solid fa-hand-holding-heart w-5 text-center text-base"></i> සහනාධාර
            </a>
            <a href="reports.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/70 transition">
                <i class="fa-solid fa-file-invoice w-5 text-center text-base"></i> වාර්තා & ගණනය කිරීම්
            </a>
            <a href="search.php" class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/70 transition">
                <i class="fa-solid fa-magnifying-glass w-5 text-center text-base"></i> සොයන්න
            </a>
        </div>

        <!-- Sidebar Footer Action -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/50">
            <a href="step1_household.php" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs px-4 py-3 rounded-xl shadow-md flex items-center justify-center gap-2 transition">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>අලුත් ගෙදරක් එකතු කරන්න</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Wrapper (Right Side) -->
    <div class="flex-1 ml-64 flex flex-col min-h-screen">
        
        <!-- Top Header / Navbar -->
        <header class="bg-white border-b border-slate-200 h-20 px-8 flex items-center justify-between sticky top-0 z-40 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg border border-blue-100">
                    <i class="fa-solid fa-gauge-high mr-1"></i> පාලන පුවරුව
                </span>
                <h2 class="text-lg font-bold text-slate-800 hidden sm:block">ග්‍රාම නිලධාරී වසම් තොරතුරු පද්ධතිය</h2>
            </div>

            <div class="flex items-center gap-4">
                <!-- Quick Search Input or Notification icon -->
                <div class="hidden md:flex items-center bg-slate-100 px-3 py-2 rounded-xl border border-slate-200 text-sm text-slate-500 w-64">
                    <i class="fa-solid fa-magnifying-glass mr-2 text-slate-400"></i>
                    <span>වසම තුළ සොයන්න...</span>
                </div>

                <!-- Admin Profile Badge -->
                <div class="flex items-center gap-3 pl-3 border-l border-slate-200">
                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center shadow-inner">
                        <span>GN</span>
                    </div>
                    <div class="hidden lg:block text-left">
                        <p class="text-xs font-bold text-slate-800 leading-tight">ප්‍රජා සංවර්ධන නිලධාරී</p>
                        <p class="text-[11px] text-slate-500">වසම 759/A - ගල්හේන</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Container -->
        <main class="max-w-7xl w-full mx-auto px-8 py-8 flex-grow">
            
            <!-- Welcome Banner & Quick Shortcuts -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">ආයුබෝවන්, ප්‍රජා සංවර්ධන නිලධාරීතුමිය! 👋</h2>
                    <p class="text-sm text-slate-500 mt-1">වසමේ සමස්ත තොරතුරු සහ සංඛ්‍යාලේඛන එකම බැල්මකින් මෙතැනින් පරීක්ෂා කරන්න.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="households_list.php" class="px-3 py-2 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 transition inline-flex items-center gap-1">
                        <i class="fa-solid fa-list text-slate-500"></i> සියලුම නිවාස
                    </a>
                    <a href="voters_filters.php?type=voter" class="px-3 py-2 text-xs font-semibold rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 transition inline-flex items-center gap-1">
                        <i class="fa-solid fa-check-to-slot"></i> 🗳️ ඡන්ද ලැයිස්තුව (18+)
                    </a>
                    <a href="reports.php" class="px-3 py-2 text-xs font-semibold rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition inline-flex items-center gap-1">
                        <i class="fa-solid fa-chart-pie"></i> ප්‍රස්ථාරික වාර්තා
                    </a>
                </div>
            </div>

            <!-- Stat Cards (KPIs) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                
                <!-- Card 1: Total Households -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">මුළු ගෘහ සංඛ්‍යාව</p>
                            <h3 class="text-3xl font-extrabold text-slate-800 mt-2"><?php echo number_format($total_households); ?></h3>
                        </div>
                        <div class="bg-blue-50 text-blue-600 p-3 rounded-xl text-xl">
                            <i class="fa-solid fa-house font-bold"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500 flex items-center gap-1">
                        <span class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> Active</span>
                        <span>ලියාපදිංචි නිවාස</span>
                    </div>
                </div>

                <!-- Card 2: Total Population -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">මුළු ජනගහනය</p>
                            <h3 class="text-3xl font-extrabold text-emerald-600 mt-2"><?php echo number_format($total_members); ?></h3>
                        </div>
                        <div class="bg-emerald-50 text-emerald-600 p-3 rounded-xl text-xl">
                            <i class="fa-solid fa-users font-bold"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500 flex items-center gap-1">
                        <span>සාමාන්‍යයෙන් ගෙදරකට: </span>
                        <strong class="text-slate-700"><?php echo $total_households > 0 ? round($total_members / $total_households, 1) : 0; ?> යි</strong>
                    </div>
                </div>

                <!-- Card 3: Male Population -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">පුරුෂ සංඛ්‍යාව</p>
                            <h3 class="text-3xl font-extrabold text-cyan-600 mt-2"><?php echo number_format($male_count); ?></h3>
                        </div>
                        <div class="bg-cyan-50 text-cyan-600 p-3 rounded-xl text-xl">
                            <i class="fa-solid fa-mars"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500">
                        <span class="font-semibold text-cyan-700">
                            <?php echo $total_members > 0 ? round(($male_count / $total_members) * 100, 1) : 0; ?>%
                        </span> ජනගහනයෙන්
                    </div>
                </div>

                <!-- Card 4: Female Population -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ස්ත්‍රී සංඛ්‍යාව</p>
                            <h3 class="text-3xl font-extrabold text-pink-600 mt-2"><?php echo number_format($female_count); ?></h3>
                        </div>
                        <div class="bg-pink-50 text-pink-600 p-3 rounded-xl text-xl">
                            <i class="fa-solid fa-venus"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500">
                        <span class="font-semibold text-pink-700">
                            <?php echo $total_members > 0 ? round(($female_count / $total_members) * 100, 1) : 0; ?>%
                        </span> ජනගහනයෙන්
                    </div>
                </div>

            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Gender Doughnut Chart -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-800">
                            <i class="fa-solid fa-chart-pie text-blue-600 mr-2"></i>ස්ත්‍රී / පුරුෂ අනුපාතය
                        </h3>
                        <a href="reports.php" class="text-xs text-blue-600 font-semibold hover:underline">විස්තර &rarr;</a>
                    </div>
                    <div class="relative h-60 flex justify-center items-center">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>

                <!-- Employment Status Bar Chart -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm lg:col-span-2">
                    <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-800">
                            <i class="fa-solid fa-briefcase text-indigo-600 mr-2"></i>රැකියා/වෘත්තීය තත්ත්වය
                        </h3>
                        <a href="reports.php" class="text-xs text-blue-600 font-semibold hover:underline">සියල්ල බලන්න &rarr;</a>
                    </div>
                    <div class="relative h-60">
                        <canvas id="empChart"></canvas>
                    </div>
                </div>

            </div>

            <!-- Assets & Vehicles Summary Section -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm mb-8">
                <div class="flex justify-between items-center mb-5 border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-800">
                        <i class="fa-solid fa-laptop-house text-emerald-600 mr-2"></i>සම්පත්, වාහන සහ විද්‍යුත් උපකරණ එකතුව
                    </h3>
                    <span class="text-xs bg-slate-100 text-slate-600 font-semibold px-2.5 py-1 rounded-full">වසමේ එකතුව</span>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 text-center">
                    
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 hover:border-blue-300 transition">
                        <div class="text-slate-500 mb-1 text-lg"><i class="fa-solid fa-mobile-screen-button text-blue-600"></i></div>
                        <div class="text-2xl font-extrabold text-slate-800"><?php echo number_format($assets['smart_phone'] ?? 0); ?></div>
                        <div class="text-xs font-semibold text-slate-500 mt-1">Smart Phones</div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 hover:border-indigo-300 transition">
                        <div class="text-slate-500 mb-1 text-lg"><i class="fa-solid fa-laptop text-indigo-600"></i></div>
                        <div class="text-2xl font-extrabold text-slate-800"><?php echo number_format($assets['laptop'] ?? 0); ?></div>
                        <div class="text-xs font-semibold text-slate-500 mt-1">Laptops</div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 hover:border-amber-300 transition">
                        <div class="text-slate-500 mb-1 text-lg"><i class="fa-solid fa-motorcycle text-amber-600"></i></div>
                        <div class="text-2xl font-extrabold text-slate-800"><?php echo number_format($assets['motorcycle'] ?? 0); ?></div>
                        <div class="text-xs font-semibold text-slate-500 mt-1">Motor Cycles</div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 hover:border-emerald-300 transition">
                        <div class="text-slate-500 mb-1 text-lg"><i class="fa-solid fa-taxi text-emerald-600"></i></div>
                        <div class="text-2xl font-extrabold text-slate-800"><?php echo number_format($assets['threewheel'] ?? 0); ?></div>
                        <div class="text-xs font-semibold text-slate-500 mt-1">Three Wheelers</div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 hover:border-teal-300 transition col-span-2 sm:col-span-1">
                        <div class="text-slate-500 mb-1 text-lg"><i class="fa-solid fa-person-biking text-teal-600"></i></div>
                        <div class="text-2xl font-extrabold text-slate-800"><?php echo number_format($assets['bicycle'] ?? 0); ?></div>
                        <div class="text-xs font-semibold text-slate-500 mt-1">Bicycles</div>
                    </div>

                </div>
            </div>

            <!-- Recent Households Table -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-5 border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-800">
                            <i class="fa-solid fa-clock-rotate-left text-blue-600 mr-2"></i>අවසානයට ඇතුළත් කළ ගෘහයන් (Recent Households)
                        </h3>
                    </div>
                    <a href="households_list.php" class="text-xs font-bold text-blue-600 hover:underline">
                        සම්පූර්ණ ලැයිස්තුව බලන්න &rarr;
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-sm mb-0">
                        <thead class="table-light">
                            <tr class="text-slate-600">
                                <th>ගෘහ අංකය (HH No)</th>
                                <th>ලිපිනය</th>
                                <th>නිවසේ ව්‍යුහය</th>
                                <th>ජල මූලාශ්‍රය</th>
                                <th>සාමාජිකයින්</th>
                                <th class="text-end">ක්‍රියාමාර්ග (Action)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recent_households) > 0): ?>
                                <?php foreach ($recent_households as $hh): ?>
                                    <tr>
                                        <td class="font-bold text-blue-700">
                                            <i class="fa-solid fa-hashtag text-xs text-slate-400"></i>
                                            <?php echo htmlspecialchars($hh['hh_no']); ?>
                                        </td>
                                        <td class="text-slate-700"><?php echo htmlspecialchars($hh['address'] ?: 'ගල්හේන'); ?></td>
                                        <td>
                                            <span class="badge bg-slate-100 text-slate-800 border">
                                                <?php echo htmlspecialchars($hh['housing_structure'] ?: 'සඳහන් කර නැත'); ?>
                                            </span>
                                        </td>
                                        <td class="text-slate-600"><?php echo htmlspecialchars($hh['water_source'] ?: '-'); ?></td>
                                        <td>
                                            <span class="badge bg-blue-50 text-blue-700 border border-blue-200 rounded-pill px-3 py-1">
                                                <i class="fa-solid fa-user text-xs mr-1"></i><?php echo $hh['member_count']; ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="household_view.php?id=<?php echo $hh['id']; ?>" class="btn btn-sm btn-light border font-bold text-blue-600 hover:bg-blue-600 hover:text-white transition">
                                                විස්තර බලන්න &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-400">
                                        <i class="fa-solid fa-folder-open text-2xl mb-2 block"></i>
                                        තවම දත්ත ඇතුළත් කර නැත.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
            759/A ගල්හේන ග්‍රාම නිලධාරී වසම් පද්ධතිය &copy; <?php echo date('Y'); ?>
        </footer>
    </div>

    <!-- Chart.js Setup Scripts -->
    <script>
        // 1. Gender Doughnut Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['පුරුෂ (Male)', 'ස්ත්‍රී (Female)'],
                datasets: [{
                    data: [<?php echo $male_count; ?>, <?php echo $female_count; ?>],
                    backgroundColor: ['#06b6d4', '#ec4899'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // 2. Employment Bar Chart
        const empCtx = document.getElementById('empChart').getContext('2d');
        new Chart(empCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($emp_labels); ?>,
                datasets: [{
                    label: 'සාමාජිකයින් ගණන',
                    data: <?php echo json_encode($emp_counts); ?>,
                    backgroundColor: '#4f46e5',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    </script>
</body>
</html>