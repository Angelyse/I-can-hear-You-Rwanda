<?php
/*
| Notification endpoints (JSON) for any signed-in user.
|   GET  ?action=count
|   POST action=read      id, csrf_token
|   POST action=read_all  csrf_token
|   POST action=delete    id, csrf_token
*/
require_once __DIR__ . "/../includes/bootstrap.php";

if (empty($_SESSION["logged_in"])) json_out(["ok" => false, "error" => "Not signed in."], 401);
$uid = (int)currentUserId();
$action = $_REQUEST["action"] ?? "";

if ($action === "count") {
    json_out(["ok" => true, "notifications" => unread_notifications($pdo, $uid), "messages" => unread_messages($pdo, $uid)]);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") json_out(["ok" => false, "error" => "POST required."], 405);
verifyCsrfToken();

if ($action === "read") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ? AND read_at IS NULL")->execute([$id, $uid]);
    json_out(["ok" => true]);
}
if ($action === "read_all") {
    $pdo->prepare("UPDATE notifications SET read_at = NOW() WHERE user_id = ? AND read_at IS NULL")->execute([$uid]);
    json_out(["ok" => true]);
}
if ($action === "delete") {
    $id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
    $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?")->execute([$id, $uid]);
    json_out(["ok" => true]);
}
json_out(["ok" => false, "error" => "Unknown action."], 400);
