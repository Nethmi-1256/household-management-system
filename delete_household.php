<?php
session_start();
require_once 'config.php';


// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කරයි
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM households WHERE id = ?");
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        die("මකා දැමීමට නොහැකි විය: " . $e->getMessage());
    }
}

header("Location: dashboard.php");
exit;