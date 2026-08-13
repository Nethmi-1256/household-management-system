<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['step1'] = [
        'hh_no' => $_POST['hh_no'],
        'address' => $_POST['address']
    ];
    header('Location: step2_details.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Step 1 - Household Info | GN 759/A</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-10 px-4">
    <div class="max-w-md mx-auto bg-white rounded-xl shadow-md p-6">
        <div class="mb-4 text-center">
            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">Step 1 of 2</span>
            <h2 class="text-2xl font-bold mt-2 text-gray-800">ගෘහ විස්තරය</h2>
        </div>

        <form method="POST" action="">
            <div class="mb-4">
                <label class="form-label font-semibold">ගෘහ අංකය (Household No) *</label>
                <input type="text" name="hh_no" class="form-control" required placeholder="උදා: 12/A">
            </div>
            
            <div class="mb-5">
                <label class="form-label font-semibold">ලිපිනය (Address)</label>
                <textarea name="address" class="form-control" rows="2" placeholder="ලිපිනය ඇතුළත් කරන්න"></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100 font-bold">ඊළඟ පියවර &rarr;</button>
        </form>
    </div>
</body>
</html>