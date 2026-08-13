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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Step 4 - Member Details | GN 759/A Galhena</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-8 px-4">
    <div class="max-w-6xl mx-auto bg-white rounded-xl shadow-md p-6">
        <div class="mb-4 text-center">
            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Final Step 4 of 4</span>
            <h2 class="text-2xl font-bold mt-2 text-gray-800">සාමාජිකයින්ගේ විස්තර ඇතුළත් කිරීම (එකතුව: <?php echo $count; ?>)</h2>
        </div>

        <form method="POST" action="">
            <?php for ($i = 0; $i < $count; $i++): ?>
                <div class="border rounded-lg p-4 mb-6 bg-slate-50 shadow-sm">
                    <h5 class="font-bold text-blue-700 mb-3 border-b pb-2">සාමාජිකයා #<?php echo $i + 1; ?></h5>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
                        <div class="md:col-span-2">
                            <label class="form-label font-semibold">සම්පූර්ණ නම *</label>
                            <input type="text" name="full_name[]" class="form-control" placeholder="නම ඇතුළත් කරන්න" required>
                        </div>
                        <div>
                            <label class="form-label font-semibold">ජා.හැ. අංකය (NIC)</label>
                            <input type="text" name="nic[]" class="form-control" placeholder="123456789V">
                        </div>
                        <div>
                            <label class="form-label font-semibold">උපන් දිනය *</label>
                            <input type="date" name="dob[]" class="form-control" required>
                        </div>

                        <div>
                            <label class="form-label font-semibold">ගෘහ මූලිකයාට ඇති සම්බන්ධය</label>
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
                            <label class="form-label font-semibold">විවාහක තත්ත්වය</label>
                            <select name="marital_status[]" class="form-select">
                                <option value="Unmarried">අවිවාහක (Unmarried)</option>
                                <option value="Married">විවාහක (Married)</option>
                                <option value="Divorced">වික්ශේපිත/දික්කසාද (Divorced)</option>
                                <option value="Widowed">වැන්දඹු (Widowed)</option>
                                <option value="Separated">වෙන්වූ (Separated)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label font-semibold">ස්ත්‍රී / පුරුෂ භාවය</label>
                            <select name="gender[]" class="form-select">
                                <option value="Male">පුරුෂ (Male)</option>
                                <option value="Female">ස්ත්‍රී (Female)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label font-semibold">ජාතිය (Nationality)</label>
                            <select name="nationality[]" class="form-select">
                                <option value="Sinhala">Sinhala (සිංහල)</option>
                                <option value="Tamil">Tamil (දෙමළ)</option>
                                <option value="Muslim">Muslim (මුස්ලිම්)</option>
                                <option value="Burger">Burger (බර්ගර්)</option>
                                <option value="Other">Other (වෙනත්)</option>
                            </select>
                        </div>

                        <div>
                            <label class="form-label font-semibold">ආගම (Religion)</label>
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
                            <label class="form-label font-semibold">අධ්‍යාපන මට්ටම</label>
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
                            <label class="form-label font-semibold">රැකියා තත්ත්වය</label>
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
                            <label class="form-label font-semibold">රැකියාව / තනතුර (Occupation)</label>
                            <input type="text" name="occupation[]" class="form-control" placeholder="උදා: ගුරු, රියදුරු, වෙළඳ">
                        </div>
                    </div>

                    <!-- Assets & Vehicles -->
                    <div class="bg-white p-3 rounded border">
                        <div class="font-bold text-xs text-gray-600 mb-2">සාමාජිකයා සතු උපකරණ සහ වාහන විස්තර:</div>
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm">
                            <label><input type="checkbox" name="has_radio[<?php echo $i; ?>]" value="1"> Radio</label>
                            <label><input type="checkbox" name="has_tv[<?php echo $i; ?>]" value="1"> TV</label>
                            <label><input type="checkbox" name="has_land_phone[<?php echo $i; ?>]" value="1"> Land Phone</label>
                            <label><input type="checkbox" name="has_smart_phone[<?php echo $i; ?>]" value="1"> Smart Phone</label>
                            <label><input type="checkbox" name="has_laptop[<?php echo $i; ?>]" value="1"> Laptop</label>
                            <label><input type="checkbox" name="has_threewheel[<?php echo $i; ?>]" value="1"> Three Wheeler</label>
                            <label><input type="checkbox" name="has_motorcycle[<?php echo $i; ?>]" value="1"> Motor Cycle</label>
                            <label><input type="checkbox" name="has_bicycle[<?php echo $i; ?>]" value="1"> Bicycle</label>
                            <label><input type="checkbox" name="has_other_vehicle[<?php echo $i; ?>]" value="1"> Other Vehicle</label>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="submit" class="btn btn-success btn-lg px-5 font-bold">සියලු දත්ත සුරකින්න (Save All Data) &check;</button>
            </div>
        </form>
    </div>
</body>
</html>