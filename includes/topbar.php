<?php
// Expects config.php + auth.php already loaded and $u = current_user()
$u = current_user();
if ($u):
    $roleLabels = ['admin' => 'පරිපාලක', 'officer' => 'නිලධාරී', 'viewer' => 'නරඹන්නා'];
    $roleLabel = $roleLabels[$u['role']] ?? $u['role'];
    $roleColor = $u['role'] === 'admin' ? 'bg-rose-100 text-rose-700 border-rose-200' : ($u['role'] === 'officer' ? 'bg-blue-100 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-600 border-slate-200');
?>
<div style="position:fixed; top:10px; right:14px; z-index:9999;" class="no-print">
  <div class="bg-white shadow-lg rounded-full pl-3 pr-1.5 py-1.5 border border-slate-200 flex items-center gap-2 text-xs">
    <i class="fa-solid fa-circle-user text-slate-400"></i>
    <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($u['full_name']); ?></span>
    <span class="px-2 py-0.5 rounded-full border text-[10px] font-bold <?php echo $roleColor; ?>"><?php echo $roleLabel; ?></span>
    <?php if (is_admin()): ?>
      <a href="users_manage.php" title="පරිශීලකයින්" class="text-slate-400 hover:text-blue-600 px-1"><i class="fa-solid fa-users-gear"></i></a>
      <a href="audit_log.php" title="Audit Log" class="text-slate-400 hover:text-blue-600 px-1"><i class="fa-solid fa-clock-rotate-left"></i></a>
    <?php endif; ?>
    <a href="logout.php" title="ඉවත් වන්න" class="bg-slate-100 hover:bg-rose-100 hover:text-rose-700 text-slate-500 rounded-full w-7 h-7 flex items-center justify-center transition">
      <i class="fa-solid fa-power-off text-[11px]"></i>
    </a>
  </div>
</div>
<?php endif; ?>
