<?php
/*
|--------------------------------------------------------------------------
| Shared helpers for the I Can Hear You Rwanda platform
|--------------------------------------------------------------------------
*/

function h($value): string
{
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

function initials(string $name): string
{
    $name = trim(preg_replace('/^(Dr\.?|Mr\.?|Mrs\.?|Ms\.?)\s+/i', "", $name));
    $parts = preg_split('/\s+/', $name);
    $out = "";
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out !== "" ? $out : "?";
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(generateCsrfToken()) . '">';
}

function redirect(string $url): void
{
    header("Location: " . $url);
    exit;
}

/* ---------- flash messages (stored in the session) ---------- */

function flash_set(string $message, string $type = "success"): void
{
    $_SESSION["flash"] = ["message" => $message, "type" => $type];
}

function flash_html(): string
{
    if (empty($_SESSION["flash"])) {
        // Also support the ?message=...&type=... pattern used by the admin actions.
        if (!empty($_GET["message"])) {
            $type = ($_GET["type"] ?? "success") === "error" ? "error" : "success";
            return '<div class="flash-message flash-' . $type . '">' . h($_GET["message"]) . '</div>';
        }
        return "";
    }
    $f = $_SESSION["flash"];
    unset($_SESSION["flash"]);
    return '<div class="flash-message flash-' . h($f["type"]) . '">' . h($f["message"]) . '</div>';
}

/* ---------- formatting ---------- */

function fmt_date(?string $date): string
{
    if (!$date) return "";
    $t = strtotime($date);
    return $t ? date("F j, Y", $t) : h($date);
}

function fmt_time(?string $time): string
{
    if (!$time) return "";
    $t = strtotime($time);
    return $t ? date("g:i A", $t) : h($time);
}

function fmt_ago(?string $datetime): string
{
    if (!$datetime) return "";
    $t = strtotime($datetime);
    if (!$t) return "";
    if (date("Y-m-d", $t) === date("Y-m-d")) return date("g:i A", $t);
    if (date("Y-m-d", $t) === date("Y-m-d", strtotime("-1 day"))) return "Yesterday";
    return date("M j", $t);
}

/* ---------- current user ---------- */

function current_user(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = $pdo->prepare("SELECT u.*, r.name AS role_name FROM users u
                           INNER JOIN roles r ON r.id = u.role_id WHERE u.id = ? LIMIT 1");
    $stmt->execute([currentUserId()]);
    $cache = $stmt->fetch() ?: [];
    return $cache;
}

function unread_notifications(PDO $pdo, int $userId): int
{
    $s = $pdo->prepare("SELECT COUNT(*) FROM notifications
                        WHERE user_id = ? AND read_at IS NULL AND status = 'sent'");
    $s->execute([$userId]);
    return (int)$s->fetchColumn();
}

function unread_messages(PDO $pdo, int $userId): int
{
    $s = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND read_at IS NULL");
    $s->execute([$userId]);
    return (int)$s->fetchColumn();
}

/* ---------- side effects: notifications + audit ---------- */

function notify(PDO $pdo, int $userId, string $title, string $message, string $type = "system"): void
{
    $s = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type, status)
                        VALUES (?, ?, ?, ?, 'sent')");
    $s->execute([$userId, $title, $message, $type]);
}

function audit(PDO $pdo, string $action, ?string $entityType = null, ?int $entityId = null): void
{
    try {
        $s = $pdo->prepare("INSERT INTO audit_logs
            (user_id, action, entity_type, entity_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?)");
        $s->execute([
            currentUserId(),
            $action,
            $entityType,
            $entityId,
            $_SERVER["REMOTE_ADDR"] ?? null,
            substr($_SERVER["HTTP_USER_AGENT"] ?? "", 0, 500),
        ]);
    } catch (Throwable $e) {
        error_log("audit: " . $e->getMessage());
    }
}

function setting(PDO $pdo, string $key, $default = null)
{
    static $all = null;
    if ($all === null) {
        $all = [];
        try {
            foreach ($pdo->query("SELECT setting_key, setting_value FROM settings") as $row) {
                $all[$row["setting_key"]] = $row["setting_value"];
            }
        } catch (Throwable $e) {
        }
    }
    return $all[$key] ?? $default;
}

/* ---------- user preferences (per-user key/value) ---------- */

function user_pref(PDO $pdo, int $userId, string $key, $default = null)
{
    $s = $pdo->prepare("SELECT pref_value FROM user_settings WHERE user_id = ? AND pref_key = ?");
    $s->execute([$userId, $key]);
    $v = $s->fetchColumn();
    return $v === false ? $default : $v;
}

function set_user_pref(PDO $pdo, int $userId, string $key, string $value): void
{
    $s = $pdo->prepare("INSERT INTO user_settings (user_id, pref_key, pref_value) VALUES (?, ?, ?)
                        ON DUPLICATE KEY UPDATE pref_value = VALUES(pref_value)");
    $s->execute([$userId, $key, $value]);
}

/* ---------- booking helpers ---------- */

function booking_slots(): array
{
    return ["09:00", "10:00", "11:00", "13:00", "14:00", "15:00", "16:00"];
}

function free_slots(PDO $pdo, int $counselorId, string $date): array
{
    $s = $pdo->prepare("SELECT start_time FROM bookings
        WHERE counselor_id = ? AND booking_date = ? AND status IN ('pending','confirmed')");
    $s->execute([$counselorId, $date]);
    $taken = array_map(fn($t) => substr($t, 0, 5), $s->fetchAll(PDO::FETCH_COLUMN));
    $free = array_values(array_diff(booking_slots(), $taken));
    if ($date === date("Y-m-d")) {
        $now = date("H:i");
        $free = array_values(array_filter($free, fn($t) => $t > $now));
    }
    return $free;
}

function make_meeting_link(int $bookingId): string
{
    return "https://meet.jit.si/ICanHearYou-" . $bookingId . "-" . bin2hex(random_bytes(5));
}

function get_or_create_conversation(PDO $pdo, int $clientId, int $counselorId): int
{
    $s = $pdo->prepare("SELECT id FROM conversations WHERE client_id = ? AND counselor_id = ? LIMIT 1");
    $s->execute([$clientId, $counselorId]);
    $id = $s->fetchColumn();
    if ($id) return (int)$id;
    $pdo->prepare("INSERT INTO conversations (client_id, counselor_id) VALUES (?, ?)")
        ->execute([$clientId, $counselorId]);
    return (int)$pdo->lastInsertId();
}

function json_out(array $data, int $status = 200): void
{
    http_response_code($status);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data);
    exit;
}

/* Status label → CSS-friendly pill */
function status_pill(string $status): string
{
    return '<span class="status ' . h($status) . '">' . h(ucfirst($status)) . '</span>';
}
