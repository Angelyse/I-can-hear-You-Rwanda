<?php
/* Broadcast a notification to a group of users. POST: title, message, audience, type, csrf_token */
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("admin");
if ($_SERVER["REQUEST_METHOD"] !== "POST") redirect("notifications.php");
verifyCsrfToken();

$title = trim($_POST["title"] ?? "");
$message = trim($_POST["message"] ?? "");
$audience = $_POST["audience"] ?? "";
$type = in_array($_POST["type"] ?? "", ["announcement", "system", "security"], true) ? $_POST["type"] : "announcement";
$roles = ["all" => null, "clients" => "client", "counsellors" => "counsellor", "admins" => "admin"];

if ($title === "" || $message === "" || !array_key_exists($audience, $roles)) {
    flash_set("Title, message and audience are required.", "error");
    redirect("notifications.php");
}
try {
    $sql = "SELECT u.id FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE u.status = 'active'";
    $args = [];
    if ($roles[$audience]) { $sql .= " AND r.name = ?"; $args[] = $roles[$audience]; }
    $q = $pdo->prepare($sql);
    $q->execute($args);
    $ids = $q->fetchAll(PDO::FETCH_COLUMN);

    $pdo->beginTransaction();
    $ins = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, audience, status) VALUES (?, ?, ?, ?, ?, 'sent')");
    foreach ($ids as $uid) $ins->execute([$uid, mb_substr($title, 0, 255), $message, $type, $audience]);
    $pdo->commit();
    audit($pdo, "notification_broadcast_" . $audience, "notification", null);
    flash_set("Notification sent to " . count($ids) . " user(s).");
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("notification-actions: " . $e->getMessage());
    flash_set("Could not send the notification.", "error");
}
redirect("notifications.php");
