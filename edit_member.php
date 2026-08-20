<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$error = '';

$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();

if (!$m) {
    die("සාමාජික විස්තර සොයා ගැනීමට නොහැකි විය!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updateStmt = $pdo->prepare("UPDATE members SET 
            full_name = ?, nic = ?, relationship = ?, marital_status = ?, gender = ?, dob = ?, nationality = ?, 
            religion = ?, educationLevel = ?, employment_status = ?, occupation = ?, 
            has_radio = ?, has_tv = ?, has_land_phone = ?, has_smart_phone = ?, has_laptop = ?, 
            has_threewheel = ?, has_motorcycle = ?, has_bicycle = ?, has_other_vehicle = ?
            WHERE id = ?");

        $updateStmt->execute([
            $_POST['full_name'],
            $_POST['nic'] ?: NULL,
            $_POST['relationship'],
            $_POST['marital_status'],
            $_POST['gender'],
            $_POST['dob'],
            $_POST['nationality'],
            $_POST['religion'],
            $_POST['educationLevel'],
            $_POST['employment_status'],
            $_POST['occupation'] ?: NULL,
            isset($_POST['has_radio']) ? 1 : 0,
            isset($_POST['has_tv']) ? 1 : 0,
            isset($_POST['has_land_phone']) ? 1 : 0,
            isset($_POST['has_smart_phone']) ? 1 : 0,
            isset($_POST['has_laptop']) ? 1 : 0,
            isset($_POST['has_threewheel']) ? 1 : 0,
            isset($_POST['has_motorcycle']) ? 1 : 0,
            isset($_POST['has_bicycle']) ? 1 : 0,
            isset($_POST['has_other_vehicle']) ? 1 : 0,
            $id
        ]);

        header("Location: household_view.php?id=" . $m['household_id']);
        exit;
    } catch (PDOException $e) {
        $error = "Update කිරීමට නොහැකි විය: " . $e->getMessage();
    }
}
?>
<?php
$active      = 'households';
$page_title  = 'සාමාජික විස්තර වෙනස් කිරීම';
$page_icon   = 'fa-pen-to-square';
$breadcrumbs = [['label' => 'ගෘහ ලැයිස්තුව', 'url' => 'households_list.php'], ['label' => 'Edit Member']];
require 'includes/header.php';
?>
    <div class="max-w-4xl mx-auto">
        <!-- Gradient Header -->
        <div class="relative overflow-hidden gn-gradient-bg gn-dot-grid p-6 rounded-2xl shadow-lg mb-6" data-reveal>
            <div class="gn-blob b1" style="width:120px;height:120px;background:#34d399;top:-30px;right:20px;"></div>
            <h2 class="relative z-10 text-xl font-bold text-white flex items-center gap-2">
                <span class="gn-badge-emerald gn-badge-grad w-9 h-9 text-sm"><i class="fa-solid fa-user-pen"></i></span>
                සාමාජික විස්තර වෙනස් කිරීම (Edit Member)
            </h2>
            <p class="relative z-10 text-xs text-blue-100 mt-1.5"><?php echo htmlspecialchars($m['full_name']); ?></p>
        </div>

        <?php if ($error): ?>
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-xs px-4 py-3 rounded-xl flex items-center gap-2" data-reveal>
                <i class="fa-solid fa-triangle-exclamation"></i><span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <div class="gn-hover-lift bg-white rounded-2xl p-6 border border-slate-200 shadow-sm" data-reveal>
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <div class="md:col-span-2">
                    <label class="form-label"><i class="fa-solid fa-signature text-blue-500 mr-1"></i>සම්පූර්ණ නම *</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($m['full_name']); ?>" required>
                </div>
                <div>
                    <label class="form-label"><i class="fa-solid fa-id-card text-slate-500 mr-1"></i>ජා.හැ. අංකය (NIC)</label>
                    <input type="text" name="nic" class="form-control" value="<?php echo htmlspecialchars($m['nic']); ?>">
                </div>
                <div>
                    <label class="form-label"><i class="fa-solid fa-cake-candles text-pink-500 mr-1"></i>උපන් දිනය *</label>
                    <input type="date" name="dob" class="form-control" value="<?php echo $m['dob']; ?>" required>
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-people-arrows text-indigo-500 mr-1"></i>ගෘහ මූලිකයාට ඇති සම්බන්ධය</label>
                    <input type="text" name="relationship" class="form-control" value="<?php echo htmlspecialchars($m['relationship']); ?>">
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-ring text-amber-500 mr-1"></i>විවාහක තත්ත්වය</label>
                    <select name="marital_status" class="form-select">
                        <?php
                        $maritals = ['Unmarried','Married','Divorced','Widowed','Separated'];
                        foreach ($maritals as $ms) {
                            $selected = ($m['marital_status'] == $ms) ? 'selected' : '';
                            echo "<option value=\"$ms\" $selected>$ms</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-venus-mars text-violet-500 mr-1"></i>ස්ත්‍රී / පුරුෂ භාවය</label>
                    <select name="gender" class="form-select">
                        <option value="Male" <?php echo $m['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $m['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-flag text-cyan-500 mr-1"></i>ජාතිය</label>
                    <select name="nationality" class="form-select">
                        <?php
                        $nats = ['Sinhala','Tamil','Muslim','Burger','Other'];
                        foreach ($nats as $nat) {
                            $selected = ($m['nationality'] == $nat) ? 'selected' : '';
                            echo "<option value=\"$nat\" $selected>$nat</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-place-of-worship text-emerald-500 mr-1"></i>ආගම</label>
                    <select name="religion" class="form-select">
                        <?php
                        $religions = ['Buddhism','Hinduism','Islam','Roman Catholic','Other Christian','Other'];
                        foreach ($religions as $rel) {
                            $selected = ($m['religion'] == $rel) ? 'selected' : '';
                            echo "<option value=\"$rel\" $selected>$rel</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-graduation-cap text-blue-500 mr-1"></i>අධ්‍යාපන මට්ටම</label>
                    <select name="educationLevel" class="form-select">
                        <?php
                        $edus = ['No Schooling','Primary (Grade 1-5)','Passed Grade 6','Passed Grade 7','Passed Grade 8','Passed Grade 9','Passed Grade 10','Passed O/L','Passed A/L','Diploma / Degree','Still Studying'];
                        foreach ($edus as $edu) {
                            $selected = ($m['educationLevel'] == $edu) ? 'selected' : '';
                            echo "<option value=\"$edu\" $selected>$edu</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-briefcase text-amber-500 mr-1"></i>රැකියා තත්ත්වය</label>
                    <select name="employment_status" class="form-select">
                        <?php
                        $emps = ['Government','Private','Semi-Government','Self-Employed','Foreign Employment','Pensioner','Unemployed','Student','Infant/Child'];
                        foreach ($emps as $emp) {
                            $selected = ($m['employment_status'] == $emp) ? 'selected' : '';
                            echo "<option value=\"$emp\" $selected>$emp</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-user-tie text-slate-500 mr-1"></i>රැකියාව / තනතුර</label>
                    <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($m['occupation']); ?>">
                </div>
            </div>

            <!-- Assets -->
            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 mb-4">
                <div class="font-bold text-xs text-slate-600 mb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-boxes-stacked text-indigo-500"></i>උපකරණ සහ වාහන:
                </div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
                    <?php
                        $__asset_fields = [
                            ['has_radio', 'fa-radio', 'Radio'],
                            ['has_tv', 'fa-tv', 'TV'],
                            ['has_land_phone', 'fa-phone', 'Land Phone'],
                            ['has_smart_phone', 'fa-mobile-screen-button', 'Smart Phone'],
                            ['has_laptop', 'fa-laptop', 'Laptop'],
                            ['has_threewheel', 'fa-taxi', 'Three Wheel'],
                            ['has_motorcycle', 'fa-motorcycle', 'Motor Cycle'],
                            ['has_bicycle', 'fa-person-biking', 'Bicycle'],
                            ['has_other_vehicle', 'fa-car', 'Other Vehicle'],
                        ];
                        foreach ($__asset_fields as $af):
                            [$field, $icon, $label] = $af;
                            $checked = $m[$field] ? 'checked' : '';
                    ?>
                    <label class="flex items-center gap-1.5 bg-white border border-slate-200 rounded-lg px-2.5 py-2 cursor-pointer hover:border-blue-300 transition">
                        <input type="checkbox" name="<?php echo $field; ?>" value="1" <?php echo $checked; ?> class="form-check-input mt-0">
                        <i class="fa-solid <?php echo $icon; ?> text-slate-400 text-xs"></i>
                        <span class="text-xs font-medium text-slate-600"><?php echo $label; ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="d-flex justify-content-between pt-3 border-t border-slate-100">
                <a href="household_view.php?id=<?php echo $m['household_id']; ?>" class="gn-ripple btn btn-secondary">&larr; අවලංගු කරන්න</a>
                <button type="submit" class="gn-ripple gn-shine btn btn-success font-bold px-5"><i class="fa-solid fa-check mr-1"></i> Member Update කරන්න</button>
            </div>
        </form>
        </div>
    </div>
<?php require 'includes/footer.php'; ?>