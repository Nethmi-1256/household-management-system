<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM households WHERE id = ?");
$stmt->execute([$id]);
$hh = $stmt->fetch();

if (!$hh) {
    die("ගෘහ විස්තර සොයා ගැනීමට නොහැකි විය!");
}

$stmt_m = $pdo->prepare("SELECT * FROM members WHERE household_id = ?");
$stmt_m->execute([$id]);
$members = $stmt_m->fetchAll();
?>
<?php
$active      = 'households';
$page_title  = 'ගෘහ අංකය ' . $hh['hh_no'];
$page_icon   = 'fa-house-user';
$breadcrumbs = [['label' => 'ගෘහ ලැයිස්තුව', 'url' => 'households_list.php'], ['label' => $hh['hh_no']]];
require 'includes/header.php';

// Small helper for member asset chips (icon + gradient + label)
function gn_asset_chip($active, $icon, $label, $grad) {
    if (!$active) return '';
    return '<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-1 rounded-lg text-white ' . $grad . '"><i class="fa-solid ' . $icon . '"></i>' . $label . '</span>';
}
?>
            <!-- Hero Header -->
            <div class="relative overflow-hidden flex flex-col md:flex-row justify-between items-center gap-4 mb-6 gn-gradient-bg gn-dot-grid p-6 rounded-2xl shadow-lg" data-reveal>
                <div class="gn-blob b1" style="width:150px;height:150px;background:#60a5fa;top:-40px;right:60px;"></div>
                <div class="gn-blob b2" style="width:100px;height:100px;background:#34d399;bottom:-30px;left:40%;"></div>
                <div class="relative z-10 flex items-center gap-4 text-center md:text-left">
                    <div class="gn-badge-blue gn-badge-grad w-14 h-14 text-2xl hidden sm:flex gn-float">
                        <i class="fa-solid fa-house-chimney"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">ගෘහ අංකය: <span class="text-cyan-200"><?php echo htmlspecialchars($hh['hh_no']); ?></span></h2>
                        <p class="text-blue-100 text-sm mt-1"><i class="fa-solid fa-location-dot mr-1"></i><?php echo htmlspecialchars($hh['address'] ?: 'ගල්හේන'); ?></p>
                    </div>
                </div>
                <div class="relative z-10 flex flex-wrap justify-center gap-2">
                    <a href="households_list.php" class="gn-ripple gn-glass btn btn-sm font-semibold text-white border-0">&larr; ලැයිස්තුවට</a>
                    <a href="edit_household.php?id=<?php echo $hh['id']; ?>" class="gn-ripple btn btn-sm btn-warning font-bold text-white"><i class="fa-solid fa-pen mr-1"></i> විස්තර වෙනස් කරන්න</a>
                    <a href="delete_household.php?id=<?php echo $hh['id']; ?>" class="gn-ripple btn btn-sm btn-danger font-bold" onclick="return confirm('මෙම මුළු ගෘහ විස්තරය සහ ඊට අදාළ සියලුම සාමාජිකයින් මකා දැමීමට විශ්වාසද?');"><i class="fa-solid fa-trash mr-1"></i> මකා දමන්න</a>
                </div>
            </div>

            <!-- Housing Info Cards -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                <?php
                    $__hinfo = [
                        ['icon' => 'fa-building',        'grad' => 'gn-badge-blue',    'label' => 'ව්‍යුහය',  'value' => $hh['housing_structure']],
                        ['icon' => 'fa-house-flag',       'grad' => 'gn-badge-amber',   'label' => 'වහලය',     'value' => $hh['roof_material']],
                        ['icon' => 'fa-border-all',       'grad' => 'gn-badge-violet',  'label' => 'බිත්ති',   'value' => $hh['wall_material']],
                        ['icon' => 'fa-layer-group',      'grad' => 'gn-badge-pink',    'label' => 'ගෙබිම',    'value' => $hh['floor_material']],
                        ['icon' => 'fa-droplet',          'grad' => 'gn-badge-cyan',    'label' => 'ජලය',      'value' => $hh['water_source']],
                    ];
                ?>
                <?php foreach ($__hinfo as $card): ?>
                <div class="gn-hover-lift bg-white rounded-xl p-3.5 border border-slate-200 shadow-sm" data-reveal>
                    <div class="<?php echo $card['grad']; ?> gn-badge-grad gn-icon-badge w-9 h-9 text-sm mb-2"><i class="fa-solid <?php echo $card['icon']; ?>"></i></div>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400"><?php echo $card['label']; ?></p>
                    <p class="text-xs font-bold text-slate-700 mt-0.5 truncate"><?php echo htmlspecialchars($card['value'] ?: '-'); ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Members Header -->
            <div class="flex justify-between items-center mb-3" data-reveal>
                <h4 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <span class="gn-badge-emerald gn-badge-grad w-8 h-8 text-sm"><i class="fa-solid fa-users"></i></span>
                    පදිංචි සාමාජිකයින් (<?php echo count($members); ?>)
                </h4>
            </div>

            <!-- Members Table -->
            <div class="gn-hover-lift bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden" data-reveal>
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-xs mb-0">
                        <thead class="table-light">
                            <tr class="text-slate-600">
                                <th>#</th>
                                <th>නම සහ උපන්දිනය</th>
                                <th>NIC</th>
                                <th>සම්බන්ධය / විවාහක තත්ත්වය</th>
                                <th>ජාතිය / ආගම</th>
                                <th>අධ්‍යාපනය</th>
                                <th>රැකියාව</th>
                                <th>උපකරණ / වාහන</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($members) > 0): ?>
                            <?php foreach ($members as $idx => $m): ?>
                                <tr>
                                    <td class="font-bold text-slate-400"><?php echo $idx + 1; ?></td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                                                <?php echo strtoupper(substr($m['full_name'] ?? '?', 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800"><?php echo htmlspecialchars($m['full_name']); ?></div>
                                                <div class="text-[10px] text-slate-500"><?php echo $m['gender']; ?> | <?php echo $m['dob']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><code class="text-[11px]"><?php echo htmlspecialchars($m['nic'] ?: 'N/A'); ?></code></td>
                                    <td>
                                        <span class="badge bg-slate-100 text-slate-700 border"><?php echo htmlspecialchars($m['relationship']); ?></span><br>
                                        <span class="badge bg-blue-50 text-blue-700 border border-blue-100 mt-1"><?php echo htmlspecialchars($m['marital_status']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($m['nationality']); ?><br><strong class="text-slate-700"><?php echo htmlspecialchars($m['religion']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($m['educationLevel']); ?></td>
                                    <td>
                                        <strong class="text-slate-700"><?php echo htmlspecialchars($m['employment_status']); ?></strong>
                                        <?php if ($m['occupation']): ?>
                                            <br><span class="text-slate-500"><?php echo htmlspecialchars($m['occupation']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap gap-1 max-w-[160px]">
                                            <?php
                                                echo gn_asset_chip($m['has_smart_phone'], 'fa-mobile-screen-button', 'Phone', 'gn-badge-blue');
                                                echo gn_asset_chip($m['has_laptop'], 'fa-laptop', 'Laptop', 'gn-badge-violet');
                                                echo gn_asset_chip($m['has_land_phone'], 'fa-phone', 'Land', 'gn-badge-cyan');
                                                echo gn_asset_chip($m['has_radio'], 'fa-radio', 'Radio', 'gn-badge-amber');
                                                echo gn_asset_chip($m['has_tv'], 'fa-tv', 'TV', 'gn-badge-pink');
                                                echo gn_asset_chip($m['has_threewheel'], 'fa-taxi', '3-Wheel', 'gn-badge-emerald');
                                                echo gn_asset_chip($m['has_motorcycle'], 'fa-motorcycle', 'Bike', 'gn-badge-amber');
                                                echo gn_asset_chip($m['has_bicycle'], 'fa-person-biking', 'Cycle', 'gn-badge-cyan');
                                                echo gn_asset_chip($m['has_other_vehicle'], 'fa-car', 'Other', 'gn-badge-violet');
                                            ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex justify-center gap-1">
                                            <a href="edit_member.php?id=<?php echo $m['id']; ?>" class="gn-ripple btn btn-sm btn-outline-warning text-xs"><i class="fa-solid fa-pen"></i></a>
                                            <a href="delete_member.php?id=<?php echo $m['id']; ?>&hh_id=<?php echo $hh['id']; ?>" class="gn-ripple btn btn-sm btn-outline-danger text-xs" onclick="return confirm('මෙම සාමාජිකයා මකා දැමීමට විශ්වාසද?');"><i class="fa-solid fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-10 text-slate-400">
                                        <svg class="gn-empty-illust" viewBox="0 0 140 140" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="70" cy="70" r="62" fill="#eef2ff"/>
                                            <circle cx="70" cy="52" r="16" fill="#818cf8"/>
                                            <path d="M40 100c0-16 13-28 30-28s30 12 30 28" fill="#c7d2fe"/>
                                        </svg>
                                        <p class="mt-3 font-semibold text-slate-500">තවම සාමාජිකයින් එකතු කර නැත.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
<?php require 'includes/footer.php'; ?>