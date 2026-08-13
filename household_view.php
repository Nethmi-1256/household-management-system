<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$id = $_GET['id'] ?? 0;

$stmt = $pdo->prepare("SELECT * FROM households WHERE id = ?");
$stmt->execute([$id]);
$hh = $stmt->fetch();

if (!$hh) {
    die("ගෘහ විස්තර සොයා ගැනීමට නොහැකි විය!");
}

$stmt_m = $pdo->prepare("SELECT * FROM members WHERE household_id = ?");
$stmt_m->execute([$id]);
$members = $stmt_m->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Household Details - <?php echo htmlspecialchars($hh['hh_no']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-8 px-4">
    <div class="max-w-7xl mx-auto bg-white rounded-xl shadow-md p-6">
        
        <!-- Top Bar -->
        <div class="d-flex justify-content-between align-items-center border-b pb-3 mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">ගෘහ අංකය: <span class="text-blue-600"><?php echo htmlspecialchars($hh['hh_no']); ?></span></h2>
                <p class="text-gray-500">ලිපිනය: <?php echo htmlspecialchars($hh['address'] ?: 'ගල්හේන'); ?></p>
            </div>
            <div class="flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">&larr; Dashboard</a>
                <a href="edit_household.php?id=<?php echo $hh['id']; ?>" class="btn btn-warning btn-sm font-semibold">✏️ ගෙදර විස්තර වෙනස් කරන්න</a>
                <a href="delete_household.php?id=<?php echo $hh['id']; ?>" class="btn btn-danger btn-sm font-semibold" onclick="return confirm('මෙම මුළු ගෘහ විස්තරය සහ ඊට අදාළ සියලුම සාමාජිකයින් මකා දැමීමට විශ්වාසද?');">🗑️ මකා දමන්න</a>
            </div>
        </div>

        <!-- Housing Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 bg-slate-50 p-4 rounded-lg text-sm border">
            <div><strong>ව්‍යුහය:</strong> <?php echo htmlspecialchars($hh['housing_structure']); ?></div>
            <div><strong>වහලය:</strong> <?php echo htmlspecialchars($hh['roof_material']); ?></div>
            <div><strong>බිත්ති:</strong> <?php echo htmlspecialchars($hh['wall_material']); ?></div>
            <div><strong>ගෙබිම:</strong> <?php echo htmlspecialchars($hh['floor_material']); ?></div>
            <div><strong>ජලය:</strong> <?php echo htmlspecialchars($hh['water_source']); ?></div>
        </div>

        <!-- Members Header -->
        <div class="flex justify-between items-center mb-3">
            <h4 class="text-xl font-bold text-gray-700">පදිංචි සාමාජිකයින් (<?php echo count($members); ?>)</h4>
        </div>

        <!-- Members Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-xs">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>නම සහ උපන්දිනය</th>
                        <th>NIC</th>
                        <th>සම්බන්ධය / විවාහක තත්ත්වය</th>
                        <th>ජාතිය / ආගම</th>
                        <th>අධ්‍යාපනය</th>
                        <th>රැකියාව</th>
                        <th>උපකරණ / වාහන</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $idx => $m): ?>
                        <tr>
                            <td><?php echo $idx + 1; ?></td>
                            <td class="font-bold text-sm">
                                <?php echo htmlspecialchars($m['full_name']); ?>
                                <br><span class="text-xs text-gray-500"><?php echo $m['gender']; ?> | <?php echo $m['dob']; ?></span>
                            </td>
                            <td><code><?php echo htmlspecialchars($m['nic'] ?: 'N/A'); ?></code></td>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($m['relationship']); ?></span><br>
                                <span class="badge bg-info text-dark mt-1"><?php echo htmlspecialchars($m['marital_status']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($m['nationality']); ?><br><strong><?php echo htmlspecialchars($m['religion']); ?></strong></td>
                            <td><?php echo htmlspecialchars($m['educationLevel']); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($m['employment_status']); ?></strong>
                                <?php if ($m['occupation']): ?>
                                    <br><span class="text-gray-600"><?php echo htmlspecialchars($m['occupation']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    <?php if ($m['has_smart_phone']): ?><span class="bg-blue-100 text-blue-800 px-1 rounded">Smart Phone</span><?php endif; ?>
                                    <?php if ($m['has_laptop']): ?><span class="bg-indigo-100 text-indigo-800 px-1 rounded">Laptop</span><?php endif; ?>
                                    <?php if ($m['has_land_phone']): ?><span class="bg-gray-100 text-gray-800 px-1 rounded">Land Phone</span><?php endif; ?>
                                    <?php if ($m['has_radio']): ?><span class="bg-gray-100 text-gray-800 px-1 rounded">Radio</span><?php endif; ?>
                                    <?php if ($m['has_tv']): ?><span class="bg-gray-100 text-gray-800 px-1 rounded">TV</span><?php endif; ?>
                                    <?php if ($m['has_threewheel']): ?><span class="bg-green-100 text-green-800 px-1 rounded">3 Wheel</span><?php endif; ?>
                                    <?php if ($m['has_motorcycle']): ?><span class="bg-amber-100 text-amber-800 px-1 rounded">Bike</span><?php endif; ?>
                                    <?php if ($m['has_bicycle']): ?><span class="bg-teal-100 text-teal-800 px-1 rounded">Bicycle</span><?php endif; ?>
                                    <?php if ($m['has_other_vehicle']): ?><span class="bg-purple-100 text-purple-800 px-1 rounded">Other Veh.</span><?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="flex justify-center gap-1">
                                    <a href="edit_member.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-warning text-xs">Edit</a>
                                    <a href="delete_member.php?id=<?php echo $m['id']; ?>&hh_id=<?php echo $hh['id']; ?>" class="btn btn-sm btn-outline-danger text-xs" onclick="return confirm('මෙම සාමාජිකයා මකා දැමීමට විශ්වාසද?');">Del</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>