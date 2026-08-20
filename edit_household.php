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

// පවතින ගෘහ විස්තර ලබා ගැනීම
$stmt = $pdo->prepare("SELECT * FROM households WHERE id = ?");
$stmt->execute([$id]);
$hh = $stmt->fetch();

if (!$hh) {
    die("ගෘහ විස්තර සොයා ගැනීමට නොහැකි විය!");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hh_no = $_POST['hh_no'];
    $address = $_POST['address'];
    $housing_structure = $_POST['housing_structure'];
    $roof_material = $_POST['roof_material'];
    $wall_material = $_POST['wall_material'];
    $floor_material = $_POST['floor_material'];
    $water_source = $_POST['water_source'];

    try {
        $updateStmt = $pdo->prepare("UPDATE households 
            SET hh_no = ?, address = ?, housing_structure = ?, roof_material = ?, wall_material = ?, floor_material = ?, water_source = ?
            WHERE id = ?");
        
        $updateStmt->execute([$hh_no, $address, $housing_structure, $roof_material, $wall_material, $floor_material, $water_source, $id]);

        header("Location: household_view.php?id=" . $id);
        exit;
    } catch (PDOException $e) {
        $error = "Update කිරීමට නොහැකි විය: " . $e->getMessage();
    }
}
?>
<?php
$active      = 'households';
$page_title  = 'ගෘහ විස්තර වෙනස් කිරීම';
$page_icon   = 'fa-pen-to-square';
$breadcrumbs = [['label' => 'ගෘහ ලැයිස්තුව', 'url' => 'households_list.php'], ['label' => 'Edit ' . $hh['hh_no']]];
require 'includes/header.php';
?>
    <div class="max-w-3xl mx-auto">
        <!-- Gradient Header -->
        <div class="relative overflow-hidden gn-gradient-bg gn-dot-grid p-6 rounded-2xl shadow-lg mb-6" data-reveal>
            <div class="gn-blob b1" style="width:120px;height:120px;background:#facc15;top:-30px;right:20px;"></div>
            <h2 class="relative z-10 text-xl font-bold text-white flex items-center gap-2">
                <span class="gn-badge-amber gn-badge-grad w-9 h-9 text-sm"><i class="fa-solid fa-pen-to-square"></i></span>
                ගෘහ විස්තර වෙනස් කිරීම (Edit Household)
            </h2>
            <p class="relative z-10 text-xs text-blue-100 mt-1.5">ගෘහ අංකය <?php echo htmlspecialchars($hh['hh_no']); ?></p>
        </div>

        <?php if ($error): ?>
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-xs px-4 py-3 rounded-xl flex items-center gap-2" data-reveal>
                <i class="fa-solid fa-triangle-exclamation"></i><span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <div class="gn-hover-lift bg-white rounded-2xl p-6 border border-slate-200 shadow-sm" data-reveal>
        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label"><i class="fa-solid fa-hashtag text-blue-500 mr-1"></i>ගෘහ අංකය (HH No) *</label>
                    <input type="text" name="hh_no" class="form-control" value="<?php echo htmlspecialchars($hh['hh_no']); ?>" required>
                </div>
                <div>
                    <label class="form-label"><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i>ලිපිනය</label>
                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($hh['address']); ?>">
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-building text-indigo-500 mr-1"></i>Housing Structure</label>
                    <select name="housing_structure" class="form-select">
                        <?php
                        $structures = ['Single house - single storeyed','Single house - two storeyed','Single house - more than two storeyed','Attached house 1st Floor','Attached house 2nd Floor','Attached house From 3 to 4 Floors','Attached house From 5 to 10 Floors','Attached house From 11 to 19 Floors','Attached house From 20 Floors or more','Other'];
                        foreach ($structures as $s) {
                            $selected = ($hh['housing_structure'] == $s) ? 'selected' : '';
                            echo "<option value=\"$s\" $selected>$s</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-house-flag text-amber-500 mr-1"></i>Roof Material</label>
                    <select name="roof_material" class="form-select">
                        <?php
                        $roofs = ['Tile','Asbestos','Concrete','Zink Aluminium Sheet','Metal Sheet','Cadjan/ Palmyrah/ Straw','Other (specify)','Not relevant'];
                        foreach ($roofs as $r) {
                            $selected = ($hh['roof_material'] == $r) ? 'selected' : '';
                            echo "<option value=\"$r\" $selected>$r</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-border-all text-violet-500 mr-1"></i>Wall Material</label>
                    <select name="wall_material" class="form-select">
                        <?php
                        $walls = ['Bricks','Cement block','Granite/ Cube stones','Cabook','Pressed soil bricks','Warichchi / Mud','Cadjan/ Palmyrah','Planks/ Metal Sheets/ Asbestos','Zink Aluminium Sheets','Other (specify)','Not relevant'];
                        foreach ($walls as $w) {
                            $selected = ($hh['wall_material'] == $w) ? 'selected' : '';
                            echo "<option value=\"$w\" $selected>$w</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="form-label"><i class="fa-solid fa-layer-group text-pink-500 mr-1"></i>Floor Material</label>
                    <select name="floor_material" class="form-select">
                        <?php
                        $floors = ['Cement','Terrazzo/Tile/Granite/Wood (Finished)','Concrete','Mud','Wood','Sand','Other (specify)','Not relevant'];
                        foreach ($floors as $f) {
                            $selected = ($hh['floor_material'] == $f) ? 'selected' : '';
                            echo "<option value=\"$f\" $selected>$f</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="form-label"><i class="fa-solid fa-droplet text-cyan-500 mr-1"></i>Water Source</label>
                    <select name="water_source" class="form-select">
                        <?php
                        $waters = ['Water Board','Well','Tube Well','Other'];
                        foreach ($waters as $ws) {
                            $selected = ($hh['water_source'] == $ws) ? 'selected' : '';
                            echo "<option value=\"$ws\" $selected>$ws</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4 pt-3 border-t border-slate-100">
                <a href="household_view.php?id=<?php echo $id; ?>" class="gn-ripple btn btn-secondary">&larr; අවලංගු කරන්න</a>
                <button type="submit" class="gn-ripple gn-shine btn btn-primary font-bold px-5"><i class="fa-solid fa-check mr-1"></i> Update කරන්න</button>
            </div>
        </form>
        </div>
    </div>
<?php require 'includes/footer.php'; ?>