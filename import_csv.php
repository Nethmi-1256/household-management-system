<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Execution Time Limit එක වැඩි කිරීම (ලොකු Data ප්‍රමාණයක් ඇතුළත් වන බැවින්)
set_time_limit(300);

$csvFile = 'data.csv';

if (!file_exists($csvFile)) {
    echo '<!DOCTYPE html><html lang="si"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSV Import Error</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;600;700&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/theme.css"></head>
    <body class="bg-slate-100 min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-rose-100 p-8 text-center">
            <div class="gn-badge-pink gn-badge-grad w-16 h-16 text-2xl mx-auto mb-4"><i class="fa-solid fa-file-circle-exclamation"></i></div>
            <h2 class="text-lg font-bold text-slate-800 mb-2">CSV ගොනුව සොයාගත නොහැක</h2>
            <p class="text-sm text-slate-500 mb-6">"' . htmlspecialchars($csvFile) . '" ගොනුව සොයා ගැනීමට නොහැකි විය. කරුණාකර Excel file එක <code class="bg-slate-100 px-1.5 py-0.5 rounded">data.csv</code> ලෙස Save කර project folder එකට එක් කරන්න.</p>
            <a href="dashboard.php" class="gn-ripple inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition"><i class="fa-solid fa-arrow-left"></i> Dashboard වෙත ආපසු</a>
        </div>
    </body></html>';
    exit;
}

$handle = fopen($csvFile, "r");

if ($handle !== FALSE) {
    // Header පේළි අතහැරීම
    fgetcsv($handle, 1000, ","); // Row 0
    fgetcsv($handle, 1000, ","); // Row 1

    $current_hh_id = null;
    $success_count = 0;
    $hh_count = 0;

    $pdo->beginTransaction();

    try {
        while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
            
            // පේළියේ නම නැත්නම් Skip කරන්න
            $name = trim($data[2] ?? '');
            if (empty($name)) {
                continue;
            }

            $hh_no = trim($data[1] ?? '');

            // 1. අලුත් ගෘහ අංකයක් (HH No) හමු වූ විට අලුත් Household එකක් Create කිරීම
            if (!empty($hh_no) && $hh_no !== '*') {
                $stmt_hh = $pdo->prepare("INSERT INTO households (hh_no, address, housing_structure, roof_material, wall_material, floor_material, water_source) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                // ජල මූලාශ්‍රය Check කිරීම
                $water = 'Water Board';
                if (!empty($data[24] ?? '') || !empty($data[25] ?? '')) {
                    $water = !empty($data[25]) ? 'Well' : 'Water Board';
                }

                $stmt_hh->execute([
                    $hh_no,
                    '759/A ගල්හේන',
                    'Single house - single storeyed',
                    'Tile',
                    'Bricks',
                    'Cement',
                    $water
                ]);

                $current_hh_id = $pdo->lastInsertId();
                $hh_count++;
            }

            // ගෘහ ID එකක් නැත්නම් පළමු Household එක සාදාගැනීම
            if (!$current_hh_id) {
                $stmt_hh = $pdo->prepare("INSERT INTO households (hh_no, address) VALUES (?, ?)");
                $stmt_hh->execute(['DEFAULT', '759/A ගල්හේන']);
                $current_hh_id = $pdo->lastInsertId();
                $hh_count++;
            }

            // 2. Member Mapping & Formatting

            // Gender
            $gender_raw = strtolower(trim($data[3] ?? ''));
            $gender = ($gender_raw === 'm' || $gender_raw === 'male') ? 'Male' : 'Female';

            // DOB / Age Parsing
            $dob_raw = trim($data[5] ?? '');
            $dob = '2000-01-01'; // Default
            if (!empty($dob_raw) && strtotime($dob_raw)) {
                $dob = date('Y-m-d', strtotime($dob_raw));
            }

            // Nationality
            $nat_raw = strtoupper(trim($data[6] ?? ''));
            $nationality = 'Sinhala';
            if ($nat_raw === 'T') $nationality = 'Tamil';
            elseif ($nat_raw === 'M') $nationality = 'Muslim';
            elseif ($nat_raw === 'B') $nationality = 'Burger';

            // Religion
            $rel_raw = strtoupper(trim($data[7] ?? ''));
            $religion = 'Buddhism';
            if ($rel_raw === 'H') $religion = 'Hinduism';
            elseif ($rel_raw === 'I') $religion = 'Islam';
            elseif ($rel_raw === 'RC') $religion = 'Roman Catholic';
            elseif ($rel_raw === 'C') $religion = 'Other Christian';

            // Education
            $edu_raw = strtoupper(trim($data[8] ?? ''));
            $education = 'Primary (Grade 1-5)';
            if ($edu_raw === 'O/L') $education = 'Passed O/L';
            elseif ($edu_raw === 'A/L') $education = 'Passed A/L';
            elseif ($edu_raw === 'DEGREE') $education = 'Diploma / Degree';
            elseif ($edu_raw === 'SCHOOL' || $edu_raw === 'G6') $education = 'Still Studying';

            // Employment Status & Job
            $emp_status = 'Unemployed';
            $occupation = null;
            if (!empty($data[13] ?? '')) $emp_status = 'Government';
            elseif (!empty($data[14] ?? '')) $emp_status = 'Semi-Government';
            elseif (!empty($data[15] ?? '')) $emp_status = 'Private';
            elseif (!empty($data[16] ?? '')) $emp_status = 'Pensioner';
            elseif (!empty($data[17] ?? '')) $emp_status = 'Self-Employed';

            if (!empty($data[18] ?? '')) {
                $occupation = trim($data[18]);
            }

            // Assets / Vehicles Check
            $has_radio = !empty($data[19] ?? '') ? 1 : 0;
            $has_tv = !empty($data[20] ?? '') ? 1 : 0;
            $has_land_phone = !empty($data[21] ?? '') ? 1 : 0;
            $has_smart_phone = !empty($data[22] ?? '') ? 1 : 0;
            $has_laptop = !empty($data[23] ?? '') ? 1 : 0;
            $has_threewheel = !empty($data[26] ?? '') ? 1 : 0;
            $has_motorcycle = !empty($data[27] ?? '') ? 1 : 0;
            $has_bicycle = !empty($data[28] ?? '') ? 1 : 0;
            $has_other_vehicle = !empty($data[29] ?? '') ? 1 : 0;

            // Member Record Insert කිරීම
            $stmt_m = $pdo->prepare("INSERT INTO members 
                (household_id, full_name, relationship, gender, dob, nationality, religion, educationLevel, employment_status, occupation,
                 has_radio, has_tv, has_land_phone, has_smart_phone, has_laptop, has_threewheel, has_motorcycle, has_bicycle, has_other_vehicle)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt_m->execute([
                $current_hh_id,
                $name,
                'Member',
                $gender,
                $dob,
                $nationality,
                $religion,
                $education,
                $emp_status,
                $occupation,
                $has_radio,
                $has_tv,
                $has_land_phone,
                $has_smart_phone,
                $has_laptop,
                $has_threewheel,
                $has_motorcycle,
                $has_bicycle,
                $has_other_vehicle
            ]);

            $success_count++;
        }

        $pdo->commit();
        fclose($handle);

        echo '<!DOCTYPE html><html lang="si"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Import Success</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;600;700;800&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="assets/theme.css"></head>
        <body class="bg-slate-100 min-h-screen flex items-center justify-center px-4">
            <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-emerald-100 p-8 text-center gn-hover-lift">
                <div class="gn-badge-emerald gn-badge-grad w-16 h-16 text-2xl mx-auto mb-4 gn-float"><i class="fa-solid fa-circle-check"></i></div>
                <h2 class="text-xl font-bold text-slate-800 mb-1">Import සාර්ථකයි! 🎉</h2>
                <p class="text-xs text-slate-500 mb-5">CSV දත්ත සාර්ථකව පද්ධතියට ඇතුළත් කරන ලදී.</p>
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-blue-50 rounded-xl p-4">
                        <div class="text-2xl font-extrabold text-blue-700">' . (int) $hh_count . '</div>
                        <div class="text-[10px] font-bold uppercase tracking-wide text-blue-500 mt-1">ගෘහ</div>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-4">
                        <div class="text-2xl font-extrabold text-emerald-700">' . (int) $success_count . '</div>
                        <div class="text-[10px] font-bold uppercase tracking-wide text-emerald-500 mt-1">සාමාජිකයින්</div>
                    </div>
                </div>
                <a href="dashboard.php" class="gn-ripple gn-shine inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition shadow-lg shadow-blue-500/20">
                    Dashboard වෙත යන්න <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </body></html>';

    } catch (Exception $e) {
        $pdo->rollBack();
        echo '<!DOCTYPE html><html lang="si"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Import Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="assets/theme.css"></head>
        <body class="bg-slate-100 min-h-screen flex items-center justify-center px-4">
            <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-rose-100 p-8 text-center">
                <div class="gn-badge-pink gn-badge-grad w-16 h-16 text-2xl mx-auto mb-4"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <h2 class="text-lg font-bold text-slate-800 mb-2">Import දෝෂයක්</h2>
                <p class="text-xs text-slate-500 mb-6">' . htmlspecialchars($e->getMessage()) . '</p>
                <a href="dashboard.php" class="gn-ripple inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition"><i class="fa-solid fa-arrow-left"></i> Dashboard වෙත ආපසු</a>
            </div>
        </body></html>';
    }
}
?>