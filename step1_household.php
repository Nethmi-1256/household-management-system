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
<?php
$active      = 'households';
$page_title  = 'නව ගෘහයක් එකතු කිරීම';
$page_icon   = 'fa-plus';
$breadcrumbs = [['label' => 'ගෘහ ලැයිස්තුව', 'url' => 'households_list.php'], ['label' => 'Step 1']];
require 'includes/header.php';
?>
    <div class="max-w-md mx-auto gn-card p-6">
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
<?php require 'includes/footer.php'; ?>