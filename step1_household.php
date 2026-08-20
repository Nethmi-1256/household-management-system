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
    <div class="max-w-md mx-auto">
        <!-- Step Progress Indicator -->
        <div class="flex items-center justify-center gap-2 mb-6" data-reveal>
            <?php for ($i = 1; $i <= 4; $i++): $isDone = $i < 1; $isNow = $i === 1; ?>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition
                        <?php echo $isNow ? 'gn-badge-blue gn-badge-grad text-white' : 'bg-slate-100 text-slate-400'; ?>">
                        <?php echo $i; ?>
                    </div>
                    <?php if ($i < 4): ?><div class="w-8 h-0.5 bg-slate-200"></div><?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <div class="gn-hover-lift bg-white rounded-2xl border border-slate-200 shadow-sm p-6" data-reveal>
            <div class="mb-5 text-center">
                <span class="gn-badge-blue gn-badge-grad text-[10px] font-bold px-3 py-1 rounded-full">Step 1 of 4</span>
                <h2 class="text-xl font-bold mt-3 text-slate-800 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-house-circle-check text-blue-600"></i> ගෘහ විස්තරය
                </h2>
                <p class="text-xs text-slate-500 mt-1">නව ගෘහයක අංකය සහ ලිපිනය ඇතුළත් කරන්න</p>
            </div>

            <form method="POST" action="">
                <div class="mb-4">
                    <label class="form-label"><i class="fa-solid fa-hashtag text-blue-500 mr-1"></i>ගෘහ අංකය (Household No) *</label>
                    <input type="text" name="hh_no" class="form-control" required placeholder="උදා: 12/A">
                </div>

                <div class="mb-5">
                    <label class="form-label"><i class="fa-solid fa-location-dot text-rose-500 mr-1"></i>ලිපිනය (Address)</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="ලිපිනය ඇතුළත් කරන්න"></textarea>
                </div>

                <button type="submit" class="gn-ripple gn-shine btn btn-primary w-100 font-bold py-2.5">ඊළඟ පියවර <i class="fa-solid fa-arrow-right ml-1"></i></button>
            </form>
        </div>
    </div>
<?php require 'includes/footer.php'; ?>