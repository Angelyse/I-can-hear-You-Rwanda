<?php
/*
| Session endpoints.
|   POST action=start      session_id            (counsellor)  -> JSON
|   POST action=complete   session_id            (counsellor)  -> JSON
|   POST action=feedback   session_id, rating, comment (client) -> JSON
|   POST action=save_note  session_id, discussion, tools_used, progress, next_steps (counsellor, form post)
*/
require_once __DIR__ . "/../includes/bootstrap.php";

if (empty($_SESSION["logged_in"])) json_out(["ok" => false, "error" => "Not signed in."], 401);
if ($_SERVER["REQUEST_METHOD"] !== "POST") json_out(["ok" => false, "error" => "POST required."], 405);
verifyCsrfToken();

$uid = (int)currentUserId();
$role = currentUserRole();
$action = $_POST["action"] ?? "";
$sid = filter_input(INPUT_POST, "session_id", FILTER_VALIDATE_INT);
if (!$sid) json_out(["ok" => false, "error" => "Invalid session."], 422);

$st = $pdo->prepare("SELECT s.*, b.client_id, b.counselor_id, b.id AS booking_id
    FROM sessions s INNER JOIN bookings b ON b.id = s.booking_id WHERE s.id = ?");
$st->execute([$sid]);
$sess = $st->fetch();
if (!$sess) json_out(["ok" => false, "error" => "Session not found."], 404);

try {
    if (in_array($action, ["start", "complete", "save_note"], true)) {
        if ($role !== "counsellor" || (int)$sess["counselor_id"] !== $uid) json_out(["ok" => false, "error" => "Forbidden."], 403);
    }

    if ($action === "start") {
        if ($sess["status"] !== "scheduled") json_out(["ok" => false, "error" => "Session cannot be started."], 409);
        $pdo->prepare("UPDATE sessions SET status='active', started_at=NOW() WHERE id=?")->execute([$sid]);
        notify($pdo, (int)$sess["client_id"], "Session started", "Your counsellor has started the session.", "booking");
        audit($pdo, "session_started", "session", $sid);
        json_out(["ok" => true, "meeting_link" => $sess["meeting_link"]]);
    }

    if ($action === "complete") {
        if (!in_array($sess["status"], ["scheduled", "active"], true)) json_out(["ok" => false, "error" => "Session already closed."], 409);
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE sessions SET status='completed', started_at=COALESCE(started_at, NOW()), ended_at=NOW() WHERE id=?")->execute([$sid]);
        $pdo->prepare("UPDATE bookings SET status='completed' WHERE id=?")->execute([$sess["booking_id"]]);
        $pdo->commit();
        notify($pdo, (int)$sess["client_id"], "Session completed", "Please share feedback about your session.", "booking");
        audit($pdo, "session_completed", "session", $sid);
        json_out(["ok" => true]);
    }

    if ($action === "feedback") {
        if ($role !== "client" || (int)$sess["client_id"] !== $uid) json_out(["ok" => false, "error" => "Forbidden."], 403);
        if ($sess["status"] !== "completed") json_out(["ok" => false, "error" => "Feedback opens after the session."], 409);
        $rating = filter_input(INPUT_POST, "rating", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
        if (!$rating) json_out(["ok" => false, "error" => "Choose a rating from 1 to 5."], 422);
        $dup = $pdo->prepare("SELECT id FROM feedback WHERE session_id=? AND client_id=?");
        $dup->execute([$sid, $uid]);
        if ($dup->fetch()) json_out(["ok" => false, "error" => "Feedback already submitted."], 409);
        $pdo->prepare("INSERT INTO feedback (session_id, client_id, rating, comment) VALUES (?,?,?,?)")
            ->execute([$sid, $uid, $rating, trim($_POST["comment"] ?? "") ?: null]);
        audit($pdo, "feedback_submitted", "session", $sid);
        json_out(["ok" => true]);
    }

    if ($action === "save_note") {
        $existing = $pdo->prepare("SELECT id FROM session_notes WHERE session_id=? AND counselor_id=?");
        $existing->execute([$sid, $uid]);
        $vals = [trim($_POST["discussion"] ?? ""), trim($_POST["tools_used"] ?? ""), trim($_POST["progress"] ?? ""), trim($_POST["next_steps"] ?? "")];
        if ($nid = $existing->fetchColumn()) {
            $pdo->prepare("UPDATE session_notes SET discussion=?, tools_used=?, progress=?, next_steps=? WHERE id=?")->execute([...$vals, $nid]);
        } else {
            $pdo->prepare("INSERT INTO session_notes (session_id, counselor_id, discussion, tools_used, progress, next_steps) VALUES (?,?,?,?,?,?)")
                ->execute([$sid, $uid, ...$vals]);
        }
        audit($pdo, "session_note_saved", "session", $sid);
        flash_set("Session note saved.");
        redirect("../counselor/session-notes.php?session=" . $sid);
    }

    json_out(["ok" => false, "error" => "Unknown action."], 400);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("sessions api: " . $e->getMessage());
    json_out(["ok" => false, "error" => "Something went wrong."], 500);
}
