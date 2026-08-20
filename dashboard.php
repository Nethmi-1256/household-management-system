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
<?php
$active     = 'dashboard';
$page_title = 'පාලන පුවරුව';
$page_icon  = 'fa-gauge-high';
$extra_head = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
require 'includes/header.php';
?>
            
            <!-- Welcome Banner & Quick Shortcuts -->
            <div class="relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8 gn-gradient-bg gn-dot-grid p-6 sm:p-7 rounded-2xl shadow-lg" data-reveal>
                <div class="gn-blob b1" style="width:160px;height:160px;background:#60a5fa;top:-50px;right:60px;"></div>
                <div class="gn-blob b2" style="width:120px;height:120px;background:#f472b6;bottom:-40px;right:220px;"></div>
                <div class="relative z-10">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                        ආයුබෝවන්, ප්‍රජා සංවර්ධන නිලධාරීතුමිය!
                        <span class="inline-block gn-float">👋</span>
                    </h2>
                    <p class="text-sm text-blue-100 mt-1">වසමේ සමස්ත තොරතුරු සහ සංඛ්‍යාලේඛන එකම බැල්මකින් මෙතැනින් පරීක්ෂා කරන්න.</p>
                </div>
                <div class="relative z-10 flex flex-wrap gap-2">
                    <a href="households_list.php" class="gn-ripple gn-glass px-3 py-2 text-xs font-semibold rounded-lg text-white hover:bg-white/20 transition inline-flex items-center gap-1">
                        <i class="fa-solid fa-list"></i> සියලුම නිවාස
                    </a>
                    <a href="voters_filters.php?type=voter" class="gn-ripple gn-glass px-3 py-2 text-xs font-semibold rounded-lg text-white hover:bg-white/20 transition inline-flex items-center gap-1">
                        <i class="fa-solid fa-check-to-slot"></i> 🗳️ ඡන්ද ලැයිස්තුව (18+)
                    </a>
                    <a href="reports.php" class="gn-ripple gn-shine px-3 py-2 text-xs font-semibold rounded-lg bg-white text-indigo-700 hover:bg-blue-50 transition inline-flex items-center gap-1 shadow">
                        <i class="fa-solid fa-chart-pie"></i> ප්‍රස්ථාරික වාර්තා
                    </a>
                </div>
            </div>

            <!-- Stat Cards (KPIs) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                
                <!-- Card 1: Total Households -->
                <div class="gn-hover-lift bg-white rounded-2xl p-5 border border-slate-200 shadow-sm" data-reveal data-tilt>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">මුළු ගෘහ සංඛ්‍යාව</p>
                            <h3 class="text-3xl font-extrabold text-slate-800 mt-2" data-countup="<?php echo (int) $total_households; ?>">0</h3>
                        </div>
                        <div class="gn-badge-blue gn-badge-grad gn-icon-badge p-3 w-12 h-12 text-xl">
                            <i class="fa-solid fa-house font-bold"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500 flex items-center gap-1">
                        <span class="text-emerald-600 font-bold"><i class="fa-solid fa-circle-check"></i> Active</span>
                        <span>ලියාපදිංචි නිවාස</span>
                    </div>
                </div>

                <!-- Card 2: Total Population -->
                <div class="gn-hover-lift bg-white rounded-2xl p-5 border border-slate-200 shadow-sm" data-reveal data-tilt>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">මුළු ජනගහනය</p>
                            <h3 class="text-3xl font-extrabold text-emerald-600 mt-2" data-countup="<?php echo (int) $total_members; ?>">0</h3>
                        </div>
                        <div class="gn-badge-emerald gn-badge-grad gn-icon-badge p-3 w-12 h-12 text-xl">
                            <i class="fa-solid fa-users font-bold"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500 flex items-center gap-1">
                        <span>සාමාන්‍යයෙන් ගෙදරකට: </span>
                        <strong class="text-slate-700"><?php echo $total_households > 0 ? round($total_members / $total_households, 1) : 0; ?> යි</strong>
                    </div>
                </div>

                <!-- Card 3: Male Population -->
                <div class="gn-hover-lift bg-white rounded-2xl p-5 border border-slate-200 shadow-sm" data-reveal data-tilt>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">පුරුෂ සංඛ්‍යාව</p>
                            <h3 class="text-3xl font-extrabold text-cyan-600 mt-2" data-countup="<?php echo (int) $male_count; ?>">0</h3>
                        </div>
                        <div class="gn-badge-cyan gn-badge-grad gn-icon-badge p-3 w-12 h-12 text-xl">
                            <i class="fa-solid fa-mars"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500">
                        <div class="gn-bar-track mb-1.5">
                            <div class="gn-bar-fill bg-cyan-500" data-bar-fill="<?php echo $total_members > 0 ? round(($male_count / $total_members) * 100, 1) : 0; ?>"></div>
                        </div>
                        <span class="font-semibold text-cyan-700">
                            <?php echo $total_members > 0 ? round(($male_count / $total_members) * 100, 1) : 0; ?>%
                        </span> ජනගහනයෙන්
                    </div>
                </div>

                <!-- Card 4: Female Population -->
                <div class="gn-hover-lift bg-white rounded-2xl p-5 border border-slate-200 shadow-sm" data-reveal data-tilt>
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">ස්ත්‍රී සංඛ්‍යාව</p>
                            <h3 class="text-3xl font-extrabold text-pink-600 mt-2" data-countup="<?php echo (int) $female_count; ?>">0</h3>
                        </div>
                        <div class="gn-badge-pink gn-badge-grad gn-icon-badge p-3 w-12 h-12 text-xl">
                            <i class="fa-solid fa-venus"></i>
                        </div>
                    </div>
                    <div class="mt-3 text-xs text-slate-500">
                        <div class="gn-bar-track mb-1.5">
                            <div class="gn-bar-fill bg-pink-500" data-bar-fill="<?php echo $total_members > 0 ? round(($female_count / $total_members) * 100, 1) : 0; ?>"></div>
                        </div>
                        <span class="font-semibold text-pink-700">
                            <?php echo $total_members > 0 ? round(($female_count / $total_members) * 100, 1) : 0; ?>%
                        </span> ජනගහනයෙන්
                    </div>
                </div>

            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Gender Doughnut Chart -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm gn-hover-lift" data-reveal>
                    <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <span class="gn-badge-blue gn-badge-grad w-8 h-8 text-sm"><i class="fa-solid fa-chart-pie"></i></span>
                            ස්ත්‍රී / පුරුෂ අනුපාතය
                        </h3>
                        <a href="reports.php" class="text-xs text-blue-600 font-semibold hover:underline">විස්තර &rarr;</a>
                    </div>
                    <div class="relative h-60 flex justify-center items-center">
                        <canvas id="genderChart"></canvas>
                    </div>
                </div>

                <!-- Employment Status Bar Chart -->
                <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm lg:col-span-2 gn-hover-lift" data-reveal>
                    <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <span class="gn-badge-violet gn-badge-grad w-8 h-8 text-sm"><i class="fa-solid fa-briefcase"></i></span>
                            රැකියා/වෘත්තීය තත්ත්වය
                        </h3>
                        <a href="reports.php" class="text-xs text-blue-600 font-semibold hover:underline">සියල්ල බලන්න &rarr;</a>
                    </div>
                    <div class="relative h-60">
                        <canvas id="empChart"></canvas>
                    </div>
                </div>

            </div>

            <!-- Assets & Vehicles Summary Section -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm mb-8" data-reveal>
                <div class="flex justify-between items-center mb-5 border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span class="gn-badge-emerald gn-badge-grad w-8 h-8 text-sm"><i class="fa-solid fa-laptop-house"></i></span>
                        සම්පත්, වාහන සහ විද්‍යුත් උපකරණ එකතුව
                    </h3>
                    <span class="text-xs bg-slate-100 text-slate-600 font-semibold px-2.5 py-1 rounded-full">වසමේ එකතුව</span>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4 text-center">

                    <div class="gn-hover-lift bg-slate-50 p-4 rounded-xl border border-slate-100" data-reveal>
                        <div class="gn-badge-blue gn-badge-grad gn-icon-badge w-11 h-11 text-lg mx-auto mb-2"><i class="fa-solid fa-mobile-screen-button"></i></div>
                        <div class="text-2xl font-extrabold text-slate-800" data-countup="<?php echo (int) ($assets['smart_phone'] ?? 0); ?>">0</div>
                        <div class="text-xs font-semibold text-slate-500 mt-1">Smart Phones</div>
                    </div>

                    <div class="gn-hover-lift bg-slate-50 p-4 rounded-xl border border-slate-100" data-reveal>
                        <div class="gn-badge-violet gn-badge-grad gn-icon-badge w-11 h-11 text-lg mx-auto mb-2"><i class="fa-solid fa-laptop"></i></div>
                        <div class="text-2xl font-extrabold text-slate-800" data-countup="<?php echo (int) ($assets['laptop'] ?? 0); ?>">0</div>
                        <div class="text-xs font-semibold text-slate-500 mt-1">Laptops</div>
                    </div>

                    <div class="gn-hover-lift bg-slate-50 p-4 rounded-xl border border-slate-100" data-reveal>
                        <div class="gn-badge-amber gn-badge-grad gn-icon-badge w-11 h-11 text-lg mx-auto mb-2"><i class="fa-solid fa-motorcycle"></i></div>
                        <div class="text-2xl font-extrabold text-slate-800" data-countup="<?php echo (int) ($assets['motorcycle'] ?? 0); ?>">0</div>
                        <div class="text-xs font-semibold text-slate-500 mt-1">Motor Cycles</div>
                    </div>

                    <div class="gn-hover-lift bg-slate-50 p-4 rounded-xl border border-slate-100" data-reveal>
                        <div class="gn-badge-emerald gn-badge-grad gn-icon-badge w-11 h-11 text-lg mx-auto mb-2"><i class="fa-solid fa-taxi"></i></div>
                        <div class="text-2xl font-extrabold text-slate-800" data-countup="<?php echo (int) ($assets['threewheel'] ?? 0); ?>">0</div>
                        <div class="text-xs font-semibold text-slate-500 mt-1">Three Wheelers</div>
                    </div>

                    <div class="gn-hover-lift bg-slate-50 p-4 rounded-xl border border-slate-100 col-span-2 sm:col-span-1" data-reveal>
                        <div class="gn-badge-cyan gn-badge-grad gn-icon-badge w-11 h-11 text-lg mx-auto mb-2"><i class="fa-solid fa-person-biking"></i></div>
                        <div class="text-2xl font-extrabold text-slate-800" data-countup="<?php echo (int) ($assets['bicycle'] ?? 0); ?>">0</div>
                        <div class="text-xs font-semibold text-slate-500 mt-1">Bicycles</div>
                    </div>

                </div>
            </div>

            <!-- Recent Households Table -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm" data-reveal>
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-5 border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                            <span class="gn-badge-blue gn-badge-grad w-8 h-8 text-sm"><i class="fa-solid fa-clock-rotate-left"></i></span>
                            අවසානයට ඇතුළත් කළ ගෘහයන් (Recent Households)
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
                                            <a href="household_view.php?id=<?php echo $hh['id']; ?>" class="gn-ripple btn btn-sm btn-light border font-bold text-blue-600 hover:bg-blue-600 hover:text-white transition">
                                                විස්තර බලන්න &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-10 text-slate-400">
                                        <svg class="gn-empty-illust" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="70" cy="70" r="62" fill="#eef2ff"/>
                                            <rect x="38" y="55" width="64" height="48" rx="6" fill="#c7d2fe"/>
                                            <rect x="48" y="65" width="44" height="6" rx="3" fill="#ffffff"/>
                                            <rect x="48" y="77" width="30" height="6" rx="3" fill="#ffffff"/>
                                            <circle cx="70" cy="40" r="10" fill="#818cf8"/>
                                        </svg>
                                        <p class="mt-3 font-semibold text-slate-500">තවම දත්ත ඇතුළත් කර නැත.</p>
                                        <a href="step1_household.php" class="inline-flex items-center gap-1.5 mt-3 text-xs font-bold text-blue-600 hover:underline">
                                            <i class="fa-solid fa-plus"></i> පළමු ගෘහය එකතු කරන්න
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
<?php require 'includes/footer.php'; ?>