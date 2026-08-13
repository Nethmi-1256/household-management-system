<?php
require_once 'config.php';

try {
    // 1. users වගුව සදාගැනීම (ENUM සමඟ)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('Admin', 'Grama Niladhari', 'Development Officer') DEFAULT 'Development Officer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. admin ගිණුම සඳහා නිවැරදි Hash එක සකස් කිරීම (Username: admin, Password: admin123)
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $full_name = 'M.P.J.Liyanage';
    $role = 'Development Officer';

    // දැනටමත් admin නමින් පරිශීලකයෙක් ඇත්නම් මුරපදය අලුත් කරයි, නැතහොත් අලුතින් එක් කරයි
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?) 
                           ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role)");
    $stmt->execute([$username, $password, $full_name, $role]);

    echo "<div style='font-family: sans-serif; padding: 20px; color: green;'>
            <h2>✅ සාර්ථකයි!</h2>
            <p>Admin ගිණුම සාර්ථකව සකස් කරන ලදී.</p>
            <p><b>Username:</b> admin</p>
            <p><b>Password:</b> admin123</p>
            <br>
            <a href='login.php' style='background: blue; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Login පිටුවට යන්න</a>
          </div>";

} catch (PDOException $e) {
    echo "<div style='font-family: sans-serif; padding: 20px; color: red;'>
            <h2>❌ දෝෂයකි:</h2>
            <p>" . $e->getMessage() . "</p>
          </div>";
}
?>