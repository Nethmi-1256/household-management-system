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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $count = (int)$_POST['member_count'];
    if ($count > 0) {
        header("Location: step4_members.php?count=" . $count);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Step 3 - Member Count | GN 759/A Galhena</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10 px-4">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md p-6">
        <div class="mb-4 text-center">
            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">Step 3 of 4</span>
            <h2 class="text-2xl font-bold mt-2 text-gray-800">සාමාජිකයින් ගණන</h2>
            <p class="text-sm text-gray-500">මෙම නිවසේ පදිංචි මුළු සාමාජිකයින් ගණන ඇතුළත් කරන්න.</p>
        </div>

        <form method="POST" action="">
            <div class="mb-5">
                <label class="form-label font-semibold">පදිංචි සාමාජිකයින් ගණන *</label>
                <input type="number" name="member_count" class="form-control form-control-lg text-center font-bold" min="1" max="20" value="1" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 font-bold py-2">ඊළඟ පියවර (සාමාජික විස්තර) &rarr;</button>
        </form>
    </div>
</body>
</html>