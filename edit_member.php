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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Member - <?php echo htmlspecialchars($m['full_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-8 px-4">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-md p-6">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 border-b pb-2">සාමාජික විස්තර වෙනස් කිරීම (Edit Member)</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                <div class="md:col-span-2">
                    <label class="form-label font-semibold">සම්පූර්ණ නම *</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($m['full_name']); ?>" required>
                </div>
                <div>
                    <label class="form-label font-semibold">ජා.හැ. අංකය (NIC)</label>
                    <input type="text" name="nic" class="form-control" value="<?php echo htmlspecialchars($m['nic']); ?>">
                </div>
                <div>
                    <label class="form-label font-semibold">උපන් දිනය *</label>
                    <input type="date" name="dob" class="form-control" value="<?php echo $m['dob']; ?>" required>
                </div>

                <div>
                    <label class="form-label font-semibold">ගෘහ මූලිකයාට ඇති සම්බන්ධය</label>
                    <input type="text" name="relationship" class="form-control" value="<?php echo htmlspecialchars($m['relationship']); ?>">
                </div>

                <div>
                    <label class="form-label font-semibold">විවාහක තත්ත්වය</label>
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
                    <label class="form-label font-semibold">ස්ත්‍රී / පුරුෂ භාවය</label>
                    <select name="gender" class="form-select">
                        <option value="Male" <?php echo $m['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $m['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>

                <div>
                    <label class="form-label font-semibold">ජාතිය</label>
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
                    <label class="form-label font-semibold">ආගම</label>
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
                    <label class="form-label font-semibold">අධ්‍යාපන මට්ටම</label>
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
                    <label class="form-label font-semibold">රැකියා තත්ත්වය</label>
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
                    <label class="form-label font-semibold">රැකියාව / තනතුර</label>
                    <input type="text" name="occupation" class="form-control" value="<?php echo htmlspecialchars($m['occupation']); ?>">
                </div>
            </div>

            <!-- Assets -->
            <div class="bg-slate-50 p-3 rounded border mb-4">
                <div class="font-bold text-xs text-gray-600 mb-2">උපකරණ සහ වාහන:</div>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
                    <label><input type="checkbox" name="has_radio" value="1" <?php echo $m['has_radio'] ? 'checked' : ''; ?>> Radio</label>
                    <label><input type="checkbox" name="has_tv" value="1" <?php echo $m['has_tv'] ? 'checked' : ''; ?>> TV</label>
                    <label><input type="checkbox" name="has_land_phone" value="1" <?php echo $m['has_land_phone'] ? 'checked' : ''; ?>> Land Phone</label>
                    <label><input type="checkbox" name="has_smart_phone" value="1" <?php echo $m['has_smart_phone'] ? 'checked' : ''; ?>> Smart Phone</label>
                    <label><input type="checkbox" name="has_laptop" value="1" <?php echo $m['has_laptop'] ? 'checked' : ''; ?>> Laptop</label>
                    <label><input type="checkbox" name="has_threewheel" value="1" <?php echo $m['has_threewheel'] ? 'checked' : ''; ?>> Three Wheel</label>
                    <label><input type="checkbox" name="has_motorcycle" value="1" <?php echo $m['has_motorcycle'] ? 'checked' : ''; ?>> Motor Cycle</label>
                    <label><input type="checkbox" name="has_bicycle" value="1" <?php echo $m['has_bicycle'] ? 'checked' : ''; ?>> Bicycle</label>
                    <label><input type="checkbox" name="has_other_vehicle" value="1" <?php echo $m['has_other_vehicle'] ? 'checked' : ''; ?>> Other Vehicle</label>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="household_view.php?id=<?php echo $m['household_id']; ?>" class="btn btn-secondary">&larr; අවලංගු කරන්න</a>
                <button type="submit" class="btn btn-success font-bold">Member Update කරන්න &check;</button>
            </div>
        </form>
    </div>
</body>
</html>