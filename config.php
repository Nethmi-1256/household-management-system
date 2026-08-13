<?php
// session එක ආරම්භ කර නොමැති නම් පමණක් ආරම්භ කරයි
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$db   = 'gn_galhena_db'; // ඔබේ Database නම මෙහි ඇතුළත් කරන්න
$user = 'root';
$pass = ''; // ඔබේ Database password එක (සාමාන්‍යයෙන් හිස්ව පවතී)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // දෝෂයක් මතු වුවහොත් පැහැදිලිව පෙන්වයි
    die("Database Connection Error: " . $e->getMessage());
}
?>