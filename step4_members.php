<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if (!isset($_SESSION['current_hh_id'])) {
    header('Location: step1_household.php');
    exit;
}

$hh_id = $_SESSION['current_hh_id'];
$count = isset($_GET['count']) ? (int)$_GET['count'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $names = $_POST['full_name'];
    $nics = $_POST['nic'];
    $rels = $_POST['relationship'];
    $maritals = $_POST['marital_status'];
    $genders = $_POST['gender'];
    $dobs = $_POST['dob'];
    $nationalities = $_POST['nationality'];
    $religions = $_POST['religion'];
    $edus = $_POST['educationLevel'];
    $emps = $_POST['employment_status'];
    $jobs = $_POST['occupation'];

    $stmt = $pdo->prepare("INSERT INTO members 
        (household_id, full_name, nic, relationship, marital_status, gender, dob, nationality, religion, educationLevel, employment_status, occupation,
         has_radio, has_tv, has_land_phone, has_smart_phone, has_laptop, has_threewheel, has_motorcycle, has_bicycle, has_other_vehicle) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    for ($i = 0; $i < count($names); $i++) {
        if (!empty($names[$i])) {
            $stmt->execute([
                $hh_id,
                $names[$i],
                $nics[$i] ?: NULL,
                $rels[$i],
                $maritals[$i],
                $genders[$i],
                $dobs[$i],
                $nationalities[$i],
                $religions[$i],
                $edus[$i],
                $emps[$i],
                $jobs[$i] ?: NULL,
                isset($_POST['has_radio'][$i]) ? 1 : 0,
                isset($_POST['has_tv'][$i]) ? 1 : 0,
                isset($_POST['has_land_phone'][$i]) ? 1 : 0,
                isset($_POST['has_smart_phone'][$i]) ? 1 : 0,
                isset($_POST['has_laptop'][$i]) ? 1 : 0,
                isset($_POST['has_threewheel'][$i]) ? 1 : 0,
                isset($_POST['has_motorcycle'][$i]) ? 1 : 0,
                isset($_POST['has_bicycle'][$i]) ? 1 : 0,
                isset($_POST['has_other_vehicle'][$i]) ? 1 : 0
            ]);
        }
    }

    unset($_SESSION['step1']);
    unset($_SESSION['current_hh_id']);

    header("Location: household_view.php?id=" . $hh_id);
    exit;
}
?>
<?php
$active      = 'households';
$page_title  = 'නව ගෘහයක් එකතු කිරීම';
$page_icon   = 'fa-plus';
$breadcrumbs = [['label' => 'ගෘහ ලැයිස්තුව', 'url' => 'households_list.php'], ['label' => 'Step 4']];
require 'includes/header.php';
?>
    <div class="max-w-6xl mx-auto">
        <!-- Step Progress Indicator -->
        <div class="flex items-center justify-center gap-2 mb-6" data-reveal>
            <?php for ($i = 1; $i <= 4; $i++): $isDone = $i < 4; $isNow = $i === 4; ?>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition
                        <?php echo $isNow ? 'gn-badge-emerald gn-badge-grad text-white' : ($isDone ? 'gn-badge-emerald gn-badge-grad text-white' : 'bg-slate-100 text-slate-400'); ?>">
                        <?php echo $isDone ? '<i class="fa-solid fa-check text-[10px]"></i>' : $i; ?>
                    </div>
                    <?php if ($i < 4): ?><div class="w-8 h-0.5 bg-emerald-300"></div><?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <div class="relative overflow-hidden gn-gradient-bg gn-dot-grid p-6 rounded-2xl shadow-lg mb-6" data-reveal>
            <div class="gn-blob b1" style="width:130px;height:130px;background:#34d399;top:-30px;right:40px;"></div>
            <span class="relative z-10 gn-glass text-[10px] font-bold text-white px-3 py-1 rounded-full inline-flex items-center gap-1"><i class="fa-solid fa-flag-checkered"></i> Final Step 4 of 4</span>
            <h2 class="relative z-10 text-xl font-bold text-white mt-3 flex items-center gap-2">
                <i class="fa-solid fa-users"></i> සාමාජිකයින්ගේ විස්තර ඇතුළත් කිරීම <span class="gn-badge-blue gn-badge-grad text-xs px-2.5 py-1">එකතුව: <?php echo $count; ?></span>
            </h2>
        </div>

        <form method="POST" action="">
            <?php for ($i = 0; $i < $count; $i++): ?>
                <div class="gn-hover-lift bg-white rounded-2xl border border-slate-200 p-5 mb-6 shadow-sm" data-reveal>
                    <h5 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3 flex items-center gap-2">
                        <span class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white text-xs flex items-center justify-center shrink-0"><?php echo $i + 1; ?></span>
                        සාමාජිකයා #<?php echo $i + 1; ?>
                    </h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                        <div class="md:col-span-2">
                            <label class="form-label"><i class="fa-solid fa-signature text-blue-500 mr-1"></i>සම්පූර්ණ නම *</label>
                            <input type="text" name="full_name[]" class="form-control" placeholder="නම ඇතුළත් කරන්න" required>
                        </div>
                        <div>
                            <label class="form-label"><i class="fa-solid fa-id-card text-slate-500 mr-1"></i>ජා.හැ. අංකය (NIC)</label>
                            <input type="text" name="nic[]" class="form-control" placeholder="123456789V">
                        </div>
                        <div>
                            <label class="form-label"><i class="fa-solid fa-cake-candles text-pink-500 mr-1"></i>උපන් දිනය *</label>
                            <input type="date" name="dob[]" class="form-control" required>
                        </div>

                        <div>
                            <label class="form-label"><i class="fa-solid fa-people-arrows text-indigo-500 mr-1"></i>ගෘහ මූලිකයාට ඇති සම්බන්ධය</label>
                            <select name="relationship[]" class="form-select">
                                <option value="Head">ගෘහ මූලිකයා (Head)</option>
                                <option value="Spouse">ස්වාමිපුරුෂයා / බිරිඳ</option>
                                <option value="Son">පුතා</option>
                                <option value="Daughter">දුව</option>
                                <option value="Father">පියා</option>
                                <option value="Mother">මව</option>
                                <option value="Other">වෙනත්</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label"><i class="fa-solid fa-ring text-amber-500 mr-1"></i>විවාහක තත්ත්වය</label>
                            <select name="marital_status[]" class="form-select">
                                <option value="Unmarried">අවිවාහක (Unmarried)</option>
                                <option value="Married">විවාහක (Married)</option>
                                <option value="Divorced">වික්ශේපිත/දික්කසාද (Divorced)</option>
                                <option value="Widowed">වැන්දඹු (Widowed)</option>
                                <option value="Separated">වෙන්වූ (Separated)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label"><i class="fa-solid fa-venus-mars text-violet-500 mr-1"></i>ස්ත්‍රී / පුරුෂ භාවය</label>
                            <select name="gender[]" class="form-select">
                                <option value="Male">පුරුෂ (Male)</option>
                                <option value="Female">ස්ත්‍රී (Female)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label"><i class="fa-solid fa-flag text-cyan-500 mr-1"></i>ජාතිය (Nationality)</label>
                            <select name="nationality[]" class="form-select">
                                <option value="Sinhala">Sinhala (සිංහල)</option>
                                <option value="Tamil">Tamil (දෙමළ)</option>
                                <option value="Muslim">Muslim (මුස්ලිම්)</option>
                                <option value="Burger">Burger (බර්ගර්)</option>
                                <option value="Other">Other (වෙනත්)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label"><i class="fa-solid fa-place-of-worship text-emerald-500 mr-1"></i>ආගම (Religion)</label>
                            <select name="religion[]" class="form-select">
                                <option value="Buddhism">බෞද්ධ (Buddhism)</option>
                                <option value="Hinduism">හින්දු (Hinduism)</option>
                                <option value="Islam">ඉස්ලාම් (Islam)</option>
                                <option value="Roman Catholic">රෝමානු කතෝලික (Roman Catholic)</option>
                                <option value="Other Christian">වෙනත් කිතුනු (Other Christian)</option>
                                <option value="Other">වෙනත් (Other)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label"><i class="fa-solid fa-graduation-cap text-blue-500 mr-1"></i>අධ්‍යාපන මට්ටම</label>
                            <select name="educationLevel[]" class="form-select">
                                <option value="No Schooling">No Schooling</option>
                                <option value="Primary (Grade 1-5)">Primary (Grade 1-5)</option>
                                <option value="Passed Grade 6">Passed Grade 6</option>
                                <option value="Passed Grade 7">Passed Grade 7</option>
                                <option value="Passed Grade 8">Passed Grade 8</option>
                                <option value="Passed Grade 9">Passed Grade 9</option>
                                <option value="Passed Grade 10">Passed Grade 10</option>
                                <option value="Passed O/L">Passed O/L</option>
                                <option value="Passed A/L">Passed A/L</option>
                                <option value="Diploma / Degree">Diploma / Degree</option>
                                <option value="Still Studying">Still Studying</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label"><i class="fa-solid fa-briefcase text-amber-500 mr-1"></i>රැකියා තත්ත්වය</label>
                            <select name="employment_status[]" class="form-select">
                                <option value="Government">Government</option>
                                <option value="Private">Private</option>
                                <option value="Semi-Government">Semi-Government</option>
                                <option value="Self-Employed">Self-Employed</option>
                                <option value="Foreign Employment">Foreign Employment</option>
                                <option value="Pensioner">Pensioner</option>
                                <option value="Unemployed">Unemployed</option>
                                <option value="Student">Student</option>
                                <option value="Infant/Child">Infant/Child</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label"><i class="fa-solid fa-user-tie text-slate-500 mr-1"></i>රැකියාව / තනතුර (Occupation)</label>
                            <input type="text" name="occupation[]" class="form-control" placeholder="උදා: ගුරු, රියදුරු, වෙළඳ">
                        </div>
                    </div>

                    <!-- Assets & Vehicles -->
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                        <div class="font-bold text-xs text-slate-600 mb-3 flex items-center gap-1.5">
                            <i class="fa-solid fa-boxes-stacked text-indigo-500"></i>සාමාජිකයා සතු උපකරණ සහ වාහන විස්තර:
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
                            <?php
                                $__assets_step4 = [
                                    ['has_radio', 'fa-radio', 'Radio'],
                                    ['has_tv', 'fa-tv', 'TV'],
                                    ['has_land_phone', 'fa-phone', 'Land Phone'],
                                    ['has_smart_phone', 'fa-mobile-screen-button', 'Smart Phone'],
                                    ['has_laptop', 'fa-laptop', 'Laptop'],
                                    ['has_threewheel', 'fa-taxi', 'Three Wheeler'],
                                    ['has_motorcycle', 'fa-motorcycle', 'Motor Cycle'],
                                    ['has_bicycle', 'fa-person-biking', 'Bicycle'],
                                    ['has_other_vehicle', 'fa-car', 'Other Vehicle'],
                                ];
                                foreach ($__assets_step4 as $af):
                                    [$field, $icon, $label] = $af;
                            ?>
                            <label class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg px-2.5 py-2 cursor-pointer hover:border-blue-300 transition">
                                <input type="checkbox" name="<?php echo $field; ?>[<?php echo $i; ?>]" value="1" class="form-check-input mt-0">
                                <i class="fa-solid <?php echo $icon; ?> text-slate-400 text-xs"></i>
                                <span class="text-xs font-medium text-slate-600"><?php echo $label; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="gn-ripple gn-shine btn btn-success btn-lg px-5 font-bold"><i class="fa-solid fa-floppy-disk mr-2"></i>සියලු දත්ත සුරකින්න (Save All Data)</button>
            </div>
        </form>
    </div>
<?php require 'includes/footer.php'; ?>