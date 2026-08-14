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
    <div class="max-w-3xl mx-auto gn-card p-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">ගෘහ විස්තර වෙනස් කිරීම (Edit Household)</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label font-semibold">ගෘහ අංකය (HH No) *</label>
                    <input type="text" name="hh_no" class="form-control" value="<?php echo htmlspecialchars($hh['hh_no']); ?>" required>
                </div>
                <div>
                    <label class="form-label font-semibold">ලිපිනය</label>
                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($hh['address']); ?>">
                </div>

                <div>
                    <label class="form-label font-semibold">Housing Structure</label>
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
                    <label class="form-label font-semibold">Roof Material</label>
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
                    <label class="form-label font-semibold">Wall Material</label>
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
                    <label class="form-label font-semibold">Floor Material</label>
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
                    <label class="form-label font-semibold">Water Source</label>
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

            <div class="d-flex justify-content-between mt-4">
                <a href="household_view.php?id=<?php echo $id; ?>" class="btn btn-secondary">&larr; අවලංගු කරන්න</a>
                <button type="submit" class="btn btn-primary font-bold">Update කරන්න &check;</button>
            </div>
        </form>
    </div>
<?php require 'includes/footer.php'; ?>