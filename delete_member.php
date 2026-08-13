<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;
$hh_id = $_GET['hh_id'] ?? 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        die("සාමාජිකයා මකා දැමීමට නොහැකි විය: " . $e->getMessage());
    }
}

header("Location: household_view.php?id=" . $hh_id);
exit;