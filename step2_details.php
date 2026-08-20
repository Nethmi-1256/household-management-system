<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Step 1 එක සම්පූර්ණ කර නැත්නම් Step 1 වෙත යවන්න
if (!isset($_SESSION['step1'])) {
    header('Location: step1_household.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hh_no = $_SESSION['step1']['hh_no'];
    $address = $_SESSION['step1']['address'];

    $housing_structure = $_POST['housing_structure'] ?? 'Single house - single storeyed';
    $roof_material = $_POST['roof_material'] ?? 'Tile';
    $wall_material = $_POST['wall_material'] ?? 'Bricks';
    $floor_material = $_POST['floor_material'] ?? 'Cement';
    $water_source = $_POST['water_source'] ?? 'Water Board';

    try {
        $stmt = $pdo->prepare("INSERT INTO households 
            (hh_no, address, housing_structure, roof_material, wall_material, floor_material, water_source)
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $hh_no, 
            $address,
            $housing_structure, 
            $roof_material, 
            $wall_material, 
            $floor_material, 
            $water_source
        ]);

        // 1. සාදනු ලැබූ household ID එක Session එකට Save කරන්න
        $_SESSION['current_hh_id'] = $pdo->lastInsertId();
        
        // 2. Step 1 session data මකා දමන්න
        unset($_SESSION['step1']);

        // 3. Step 3 (සාමාජිකයින් ගණන ඇතුළත් කරන පිටුවට) Redirect කරන්න
        header('Location: step3_member_count.php');
        exit;

    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<?php
$active      = 'households';
$page_title  = 'නව ගෘහයක් එකතු කිරීම';
$page_icon   = 'fa-plus';
$breadcrumbs = [['label' => 'ගෘහ ලැයිස්තුව', 'url' => 'households_list.php'], ['label' => 'Step 2']];
require 'includes/header.php';
?>
    <div class="max-w-4xl mx-auto">
        <!-- Step Progress Indicator -->
        <div class="flex items-center justify-center gap-2 mb-6" data-reveal>
            <?php for ($i = 1; $i <= 4; $i++): $isDone = $i < 2; $isNow = $i === 2; ?>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition
                        <?php echo $isNow ? 'gn-badge-blue gn-badge-grad text-white' : ($isDone ? 'gn-badge-emerald gn-badge-grad text-white' : 'bg-slate-100 text-slate-400'); ?>">
                        <?php echo $isDone ? '<i class="fa-solid fa-check text-[10px]"></i>' : $i; ?>
                    </div>
                    <?php if ($i < 4): ?><div class="w-8 h-0.5 <?php echo $isDone ? 'bg-emerald-300' : 'bg-slate-200'; ?>"></div><?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <div class="gn-hover-lift bg-white rounded-2xl border border-slate-200 shadow-sm p-6" data-reveal>
        <div class="mb-5 text-center">
            <span class="gn-badge-blue gn-badge-grad text-[10px] font-bold px-3 py-1 rounded-full">Step 2 of 4</span>
            <h2 class="text-xl font-bold mt-3 text-slate-800 flex items-center justify-center gap-2">
                <i class="fa-solid fa-trowel-bricks text-blue-600"></i> නිවසේ ව්‍යුහය සහ ද්‍රව්‍ය විස්තර
            </h2>
            <p class="text-xs text-slate-500 mt-1">ගෘහ අංකය: <strong class="text-blue-700"><?php echo htmlspecialchars($_SESSION['step1']['hh_no']); ?></strong></p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-xs px-4 py-3 rounded-xl flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation"></i><span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                
                <div class="col-span-2 md:col-span-1">
                    <label class="form-label"><i class="fa-solid fa-building text-indigo-500 mr-1"></i>Housing Structure</label>
                    <select name="housing_structure" class="form-select" required>
                        <option value="Single house - single storeyed">Single house - single storeyed</option>
                        <option value="Single house - two storeyed">Single house - two storeyed</option>
                        <option value="Single house - more than two storeyed">Single house - more than two storeyed</option>
                        <option value="Attached house 1st Floor">Attached house 1st Floor</option>
                        <option value="Attached house 2nd Floor">Attached house 2nd Floor</option>
                        <option value="Attached house From 3 to 4 Floors">Attached house From 3 to 4 Floors</option>
                        <option value="Attached house From 5 to 10 Floors">Attached house From 5 to 10 Floors</option>
                        <option value="Attached house From 11 to 19 Floors">Attached house From 11 to 19 Floors</option>
                        <option value="Attached house From 20 Floors or more">Attached house From 20 Floors or more</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="form-label"><i class="fa-solid fa-house-flag text-amber-500 mr-1"></i>Roof Material</label>
                    <select name="roof_material" class="form-select" required>
                        <option value="Tile">Tile</option>
                        <option value="Asbestos">Asbestos</option>
                        <option value="Concrete">Concrete</option>
                        <option value="Zink Aluminium Sheet">Zink Aluminium Sheet</option>
                        <option value="Metal Sheet">Metal Sheet</option>
                        <option value="Cadjan/ Palmyrah/ Straw">Cadjan/ Palmyrah/ Straw</option>
                        <option value="Other (specify)">Other (specify)</option>
                        <option value="Not relevant">Not relevant</option>
                    </select>
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="form-label"><i class="fa-solid fa-border-all text-violet-500 mr-1"></i>Wall Material</label>
                    <select name="wall_material" class="form-select" required>
                        <option value="Bricks">Bricks</option>
                        <option value="Cement block">Cement block</option>
                        <option value="Granite/ Cube stones">Granite/ Cube stones</option>
                        <option value="Cabook">Cabook</option>
                        <option value="Pressed soil bricks">Pressed soil bricks</option>
                        <option value="Warichchi / Mud">Warichchi / Mud</option>
                        <option value="Cadjan/ Palmyrah">Cadjan/ Palmyrah</option>
                        <option value="Planks/ Metal Sheets/ Asbestos">Planks/ Metal Sheets/ Asbestos</option>
                        <option value="Zink Aluminium Sheets">Zink Aluminium Sheets</option>
                        <option value="Other (specify)">Other (specify)</option>
                        <option value="Not relevant">Not relevant</option>
                    </select>
                </div>

                <div class="col-span-2 md:col-span-1">
                    <label class="form-label"><i class="fa-solid fa-layer-group text-pink-500 mr-1"></i>Floor Material</label>
                    <select name="floor_material" class="form-select" required>
                        <option value="Cement">Cement</option>
                        <option value="Terrazzo/Tile/Granite/Wood (Finished)">Terrazzo/Tile/Granite/Wood (Finished)</option>
                        <option value="Concrete">Concrete</option>
                        <option value="Mud">Mud</option>
                        <option value="Wood">Wood</option>
                        <option value="Sand">Sand</option>
                        <option value="Other (specify)">Other (specify)</option>
                        <option value="Not relevant">Not relevant</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="form-label"><i class="fa-solid fa-droplet text-cyan-500 mr-1"></i>Water Source</label>
                    <select name="water_source" class="form-select" required>
                        <option value="Water Board">Water Board (ජල සම්පාදන මණ්ඩලය)</option>
                        <option value="Well">Well (ළිං ජලය)</option>
                        <option value="Tube Well">Tube Well (නළ ළිං)</option>
                        <option value="Other">Other (වෙනත්)</option>
                    </select>
                </div>

            </div>

            <div class="d-flex justify-content-between mt-4 pt-3 border-t border-slate-100">
                <a href="step1_household.php" class="gn-ripple btn btn-secondary px-4">&larr; Back</a>
                <button type="submit" class="gn-ripple gn-shine btn btn-primary px-5 font-bold">ඊළඟ පියවර (Next Step) <i class="fa-solid fa-arrow-right ml-1"></i></button>
            </div>
        </form>
        </div>
    </div>
<?php require 'includes/footer.php'; ?>