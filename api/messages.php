<?php
/*
| Messaging endpoints (JSON). Clients and counsellors only.
|   GET  ?action=conversations
|   GET  ?action=thread&conversation_id=1&after=0     (also marks incoming messages read)
|   POST action=send   conversation_id, message, csrf_token
|   POST action=start  counselor_id (client) | client_id (counsellor), csrf_token
*/
require_once __DIR__ . "/../includes/bootstrap.php";

if (empty($_SESSION["logged_in"])) json_out(["ok" => false, "error" => "Not signed in."], 401);
$uid  = (int)currentUserId();
$role = currentUserRole();
if (!in_array($role, ["client", "counsellor"], true)) json_out(["ok" => false, "error" => "Forbidden."], 403);

$action = $_REQUEST["action"] ?? "";

function conversation_for_user(PDO $pdo, int $convId, int $uid): ?array
{
    $s = $pdo->prepare("SELECT * FROM conversations WHERE id = ? AND (client_id = ? OR counselor_id = ?)");
    $s->execute([$convId, $uid, $uid]);
    return $s->fetch() ?: null;
}

try {
    if ($action === "conversations") {
        $me = $role === "client" ? "client_id" : "counselor_id";
        $other = $role === "client" ? "counselor_id" : "client_id";
        $s = $pdo->prepare("SELECT c.id, u.id AS other_id, u.name,
              (SELECT m.message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message,
              (SELECT m.created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_at,
              (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.receiver_id = ? AND m.read_at IS NULL) AS unread
            FROM conversations c INNER JOIN users u ON u.id = c.$other
            WHERE c.$me = ? ORDER BY COALESCE(last_at, c.created_at) DESC");
        $s->execute([$uid, $uid]);
        json_out(["ok" => true, "conversations" => $s->fetchAll()]);
    }

    if ($action === "thread") {
        $convId = filter_input(INPUT_GET, "conversation_id", FILTER_VALIDATE_INT);
        $after  = (int)($_GET["after"] ?? 0);
        if (!$convId || !conversation_for_user($pdo, $convId, $uid)) json_out(["ok" => false, "error" => "Not found."], 404);

        $pdo->prepare("UPDATE messages SET read_at = NOW()
                       WHERE conversation_id = ? AND receiver_id = ? AND read_at IS NULL")->execute([$convId, $uid]);

        $s = $pdo->prepare("SELECT id, sender_id, message, created_at FROM messages
                            WHERE conversation_id = ? AND id > ? ORDER BY id ASC LIMIT 200");
        $s->execute([$convId, $after]);
        $rows = array_map(fn($m) => [
            "id" => (int)$m["id"],
            "mine" => (int)$m["sender_id"] === $uid,
            "message" => $m["message"],
            "time" => fmt_time($m["created_at"]),
            "date" => date("Y-m-d", strtotime($m["created_at"])),
        ], $s->fetchAll());
        json_out(["ok" => true, "messages" => $rows]);
    }

    if ($_SERVER["REQUEST_METHOD"] !== "POST") json_out(["ok" => false, "error" => "POST required."], 405);
    verifyCsrfToken();

    if ($action === "send") {
        $convId = filter_input(INPUT_POST, "conversation_id", FILTER_VALIDATE_INT);
        $text = trim($_POST["message"] ?? "");
        if ($text === "" || mb_strlen($text) > 4000) json_out(["ok" => false, "error" => "Message must be 1-4000 characters."], 422);
        $conv = $convId ? conversation_for_user($pdo, $convId, $uid) : null;
        if (!$conv) json_out(["ok" => false, "error" => "Conversation not found."], 404);

        $receiver = (int)$conv["client_id"] === $uid ? (int)$conv["counselor_id"] : (int)$conv["client_id"];
        $pdo->prepare("INSERT INTO messages (conversation_id, sender_id, receiver_id, message) VALUES (?, ?, ?, ?)")
            ->execute([$convId, $uid, $receiver, $text]);
        $mid = (int)$pdo->lastInsertId();
        $pdo->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?")->execute([$convId]);

        if (setting($pdo, "message_notifications", "1") === "1") {
            notify($pdo, $receiver, "New message", current_user($pdo)["name"] . " sent you a message.", "message");
        }
        json_out(["ok" => true, "id" => $mid]);
    }

    if ($action === "start") {
        if ($role === "client") {
            $other = filter_input(INPUT_POST, "counselor_id", FILTER_VALIDATE_INT);
            $chk = $pdo->prepare("SELECT u.id FROM users u INNER JOIN roles r ON r.id = u.role_id
                                  WHERE u.id = ? AND r.name = 'counsellor' AND u.status = 'active'");
            $chk->execute([$other]);
            if (!$other || !$chk->fetch()) json_out(["ok" => false, "error" => "Counsellor not found."], 404);
            $convId = get_or_create_conversation($pdo, $uid, $other);
        } else {
            $other = filter_input(INPUT_POST, "client_id", FILTER_VALIDATE_INT);
            // A counsellor may only message clients they have a booking with.
            $chk = $pdo->prepare("SELECT 1 FROM bookings WHERE client_id = ? AND counselor_id = ? LIMIT 1");
            $chk->execute([$other, $uid]);
            if (!$other || !$chk->fetch()) json_out(["ok" => false, "error" => "Client not found."], 404);
            $convId = get_or_create_conversation($pdo, $other, $uid);
        }
        json_out(["ok" => true, "conversation_id" => $convId]);
    }

    json_out(["ok" => false, "error" => "Unknown action."], 400);
} catch (Throwable $e) {
    error_log("messages api: " . $e->getMessage());
    json_out(["ok" => false, "error" => "Something went wrong."], 500);
}
