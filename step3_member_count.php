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
<?php
$active      = 'households';
$page_title  = 'නව ගෘහයක් එකතු කිරීම';
$page_icon   = 'fa-plus';
$breadcrumbs = [['label' => 'ගෘහ ලැයිස්තුව', 'url' => 'households_list.php'], ['label' => 'Step 3']];
require 'includes/header.php';
?>
    <div class="max-w-md mx-auto">
        <!-- Step Progress Indicator -->
        <div class="flex items-center justify-center gap-2 mb-6" data-reveal>
            <?php for ($i = 1; $i <= 4; $i++): $isDone = $i < 3; $isNow = $i === 3; ?>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition
                        <?php echo $isNow ? 'gn-badge-blue gn-badge-grad text-white' : ($isDone ? 'gn-badge-emerald gn-badge-grad text-white' : 'bg-slate-100 text-slate-400'); ?>">
                        <?php echo $isDone ? '<i class="fa-solid fa-check text-[10px]"></i>' : $i; ?>
                    </div>
                    <?php if ($i < 4): ?><div class="w-8 h-0.5 <?php echo $isDone ? 'bg-emerald-300' : 'bg-slate-200'; ?>"></div><?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <div class="gn-hover-lift bg-white rounded-2xl border border-slate-200 shadow-sm p-6" data-reveal>
            <div class="mb-5 text-center">
                <span class="gn-badge-blue gn-badge-grad text-[10px] font-bold px-3 py-1 rounded-full">Step 3 of 4</span>
                <div class="gn-badge-violet gn-badge-grad w-14 h-14 text-2xl mx-auto my-4 gn-float"><i class="fa-solid fa-users"></i></div>
                <h2 class="text-xl font-bold text-slate-800">සාමාජිකයින් ගණන</h2>
                <p class="text-xs text-slate-500 mt-1">මෙම නිවසේ පදිංචි මුළු සාමාජිකයින් ගණන ඇතුළත් කරන්න.</p>
            </div>

            <form method="POST" action="">
                <div class="mb-5">
                    <label class="form-label text-center block"><i class="fa-solid fa-user-group text-violet-500 mr-1"></i>පදිංචි සාමාජිකයින් ගණන *</label>
                    <input type="number" name="member_count" class="form-control form-control-lg text-center font-bold text-2xl" min="1" max="20" value="1" required>
                </div>

                <button type="submit" class="gn-ripple gn-shine btn btn-primary w-100 font-bold py-2.5">ඊළඟ පියවර (සාමාජික විස්තර) <i class="fa-solid fa-arrow-right ml-1"></i></button>
            </form>
        </div>
    </div>
<?php require 'includes/footer.php'; ?>