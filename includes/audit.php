<?php
/**
 * includes/audit.php — call log_action() after any create/update/delete
 * or login/logout event. Requires $pdo (from config.php) and auth.php
 * to already be loaded.
 */
function log_action($pdo, $action, $entity_type, $entity_id = null, $details = '') {
    try {
        $user = current_user();
        $stmt = $pdo->prepare("INSERT INTO audit_log (user_id, username, action, entity_type, entity_id, details, ip_address)
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['id'] ?? null,
            $user['username'] ?? 'system',
            $action,
            $entity_type,
            $entity_id,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    } catch (PDOException $e) {
        // Never let audit logging break the main action
        error_log('audit log failed: ' . $e->getMessage());
    }
}
