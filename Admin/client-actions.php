
<?php
require_once "../config/auth.php";
requireRole("admin");

require_once "../config/database.php";
require_once "../config/security.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: clients.php");
    exit;
}

verifyCsrfToken();

$clientId = filter_input(INPUT_POST, "client_id", FILTER_VALIDATE_INT);
$action = $_POST["action"] ?? "";

$allowedStatuses = [
    "activate" => "active",
    "deactivate" => "inactive",
    "suspend" => "suspended"
];

if (!$clientId || !isset($allowedStatuses[$action])) {
    header("Location: clients.php?message=" .
        urlencode("Invalid client or action.") . "&type=error");
    exit;
}

try {
    // Confirm that this user is a registered client.
    $check = $pdo->prepare("
        SELECT u.id
        FROM users u
        INNER JOIN roles r ON r.id = u.role_id
        WHERE u.id = :id AND r.name = 'client'
        LIMIT 1
    ");
    $check->execute(["id" => $clientId]);

    if (!$check->fetch()) {
        throw new RuntimeException("Client account was not found.");
    }

    $newStatus = $allowedStatuses[$action];

    $update = $pdo->prepare("
        UPDATE users
        SET status = :status
        WHERE id = :id
    ");
    $update->execute([
        "status" => $newStatus,
        "id" => $clientId
    ]);

    // Record the administrative action when audit_logs is available.
    try {
        $audit = $pdo->prepare("
            INSERT INTO audit_logs
                (user_id, action, entity_type, entity_id, created_at)
            VALUES
                (:user_id, :action, 'client', :entity_id, NOW())
        ");
        $audit->execute([
            "user_id" => currentUserId(),
            "action" => "client_status_changed_to_" . $newStatus,
            "entity_id" => $clientId
        ]);
    } catch (PDOException $auditError) {
        // The status change should not fail solely because the
        // optional audit log has a different schema.
        error_log("Client status audit log: " . $auditError->getMessage());
    }

    $messages = [
        "active" => "Client account activated successfully.",
        "inactive" => "Client account deactivated successfully.",
        "suspended" => "Client account suspended successfully."
    ];

    header("Location: clients.php?message=" .
        urlencode($messages[$newStatus]) . "&type=success");
    exit;

} catch (Throwable $e) {
    error_log("Client action error: " . $e->getMessage());

    $message = $e instanceof RuntimeException
        ? $e->getMessage()
        : "Unable to update the client account. Please check the database configuration.";

    header("Location: clients.php?message=" .
        urlencode($message) . "&type=error");
    exit;
}
