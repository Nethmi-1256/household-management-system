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
    die("<div style='color:red; font-weight:bold;'>Error: '$csvFile' ගොනුව සොයා ගැනීමට නොහැකි විය. කරුණාකර Excel file එක data.csv ලෙස Save කර project folder එකට එක් කරන්න.</div>");
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

        echo "<div style='font-family:sans-serif; padding:20px; background:#e6fffa; border:1px solid #38b2ac; border-radius:8px; max-width:600px; margin:40px auto;'>";
        echo "<h2 style='color:#234e52; margin-top:0;'>🎉 Import සාර්ථකයි!</h2>";
        echo "<p><strong>එකතු කරන ලද ගෘහ සංඛ්‍යාව:</strong> $hh_count</p>";
        echo "<p><strong>එකතු කරන ලද සාමාජිකයින් සංඛ්‍යාව:</strong> $success_count</p>";
        echo "<a href='dashboard.php' style='display:inline-block; padding:10px 20px; background:#319795; color:#fff; text-decoration:none; border-radius:5px; font-weight:bold;'>Dashboard වෙත යන්න &rarr;</a>";
        echo "</div>";

    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<div style='color:red; font-weight:bold;'>Error importing data: " . $e->getMessage() . "</div>";
    }
}
?>