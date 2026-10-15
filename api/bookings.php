<?php
/*
| Booking endpoints (JSON).
|   GET  ?action=slots&counselor_id=1&date=2026-10-20   -> free time slots
|   POST action=create   counselor_id, session_type, date, time, reason, csrf_token
|   POST action=cancel   booking_id, csrf_token                (client or counsellor)
|   POST action=confirm  booking_id, csrf_token                (counsellor)
|   POST action=decline  booking_id, csrf_token                (counsellor)
*/
require_once __DIR__ . "/../includes/bootstrap.php";

if (empty($_SESSION["logged_in"])) json_out(["ok" => false, "error" => "Not signed in."], 401);

$uid  = (int)currentUserId();
$role = currentUserRole();
$action = $_REQUEST["action"] ?? "";

if ($action === "slots") {
    $cid  = filter_input(INPUT_GET, "counselor_id", FILTER_VALIDATE_INT);
    $date = $_GET["date"] ?? "";
    if (!$cid || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < date("Y-m-d")) {
        json_out(["ok" => false, "error" => "Choose a valid date."], 422);
    }
    json_out(["ok" => true, "slots" => free_slots($pdo, $cid, $date)]);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") json_out(["ok" => false, "error" => "POST required."], 405);
verifyCsrfToken();

try {
    if ($action === "create") {
        if ($role !== "client") json_out(["ok" => false, "error" => "Only clients can book."], 403);

        $cid   = filter_input(INPUT_POST, "counselor_id", FILTER_VALIDATE_INT);
        $type  = $_POST["session_type"] ?? "";
        $date  = $_POST["date"] ?? "";
        $time  = $_POST["time"] ?? "";
        $reason = trim($_POST["reason"] ?? "");

        if (!$cid || !in_array($type, ["video", "chat"], true) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            || !in_array($time, booking_slots(), true) || $reason === "") {
            json_out(["ok" => false, "error" => "Please complete every field."], 422);
        }
        if ($date < date("Y-m-d")) json_out(["ok" => false, "error" => "Choose a future date."], 422);

        $c = $pdo->prepare("SELECT u.id, u.name, cp.session_types FROM users u
            INNER JOIN roles r ON r.id = u.role_id
            INNER JOIN counselor_profiles cp ON cp.user_id = u.id
            WHERE u.id = ? AND r.name = 'counsellor' AND u.status = 'active' AND cp.status = 'active'");
        $c->execute([$cid]);
        $counselor = $c->fetch();
        if (!$counselor) json_out(["ok" => false, "error" => "Counsellor not available."], 404);
        if (!in_array($type, explode(",", $counselor["session_types"]), true)) {
            json_out(["ok" => false, "error" => "This counsellor does not offer that session type."], 422);
        }

        $pdo->beginTransaction();
        // Lock the counsellor's rows for the day so two clients cannot take the same slot.
        $chk = $pdo->prepare("SELECT id FROM bookings WHERE counselor_id = ? AND booking_date = ?
            AND start_time = ? AND status IN ('pending','confirmed') FOR UPDATE");
        $chk->execute([$cid, $date, $time . ":00"]);
        if ($chk->fetch()) {
            $pdo->rollBack();
            json_out(["ok" => false, "error" => "That time was just taken. Please choose another."], 409);
        }

        $svc = (int)$pdo->query("SELECT id FROM services WHERE status='active' ORDER BY id LIMIT 1")->fetchColumn();
        $end = date("H:i:s", strtotime($time) + 3600);
        $pdo->prepare("INSERT INTO bookings
            (client_id, counselor_id, service_id, session_type, booking_date, start_time, end_time, reason, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')")
            ->execute([$uid, $cid, $svc, $type, $date, $time . ":00", $end, $reason]);
        $bid = (int)$pdo->lastInsertId();
        $pdo->commit();

        notify($pdo, $cid, "New booking request",
            current_user($pdo)["name"] . " requested a " . $type . " session on " . fmt_date($date) . " at " . fmt_time($time) . ".",
            "booking");
        notify($pdo, $uid, "Booking submitted",
            "Your request with " . $counselor["name"] . " is awaiting confirmation.", "booking");
        audit($pdo, "booking_created", "booking", $bid);
        json_out(["ok" => true, "booking_id" => $bid, "redirect" => "bookings.php"]);
    }

    $bid = filter_input(INPUT_POST, "booking_id", FILTER_VALIDATE_INT);
    if (!$bid) json_out(["ok" => false, "error" => "Invalid booking."], 422);

    $b = $pdo->prepare("SELECT b.*, cu.name AS client_name, pu.name AS counselor_name
        FROM bookings b
        INNER JOIN users cu ON cu.id = b.client_id
        INNER JOIN users pu ON pu.id = b.counselor_id
        WHERE b.id = ?");
    $b->execute([$bid]);
    $booking = $b->fetch();
    if (!$booking) json_out(["ok" => false, "error" => "Booking not found."], 404);

    $isClient = $role === "client" && (int)$booking["client_id"] === $uid;
    $isCounselor = $role === "counsellor" && (int)$booking["counselor_id"] === $uid;
    if (!$isClient && !$isCounselor && $role !== "admin") json_out(["ok" => false, "error" => "Forbidden."], 403);

    $when = fmt_date($booking["booking_date"]) . " at " . fmt_time($booking["start_time"]);

    if ($action === "cancel") {
        if (!in_array($booking["status"], ["pending", "confirmed"], true)) {
            json_out(["ok" => false, "error" => "This booking can no longer be cancelled."], 409);
        }
        $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?")->execute([$bid]);
        $pdo->prepare("UPDATE sessions SET status='cancelled' WHERE booking_id=? AND status='scheduled'")->execute([$bid]);
        $other = $isClient ? (int)$booking["counselor_id"] : (int)$booking["client_id"];
        $by = $isClient ? $booking["client_name"] : $booking["counselor_name"];
        notify($pdo, $other, "Booking cancelled", "$by cancelled the session on $when.", "booking");
        audit($pdo, "booking_cancelled", "booking", $bid);
        json_out(["ok" => true]);
    }

    if (in_array($action, ["confirm", "decline"], true)) {
        if (!$isCounselor) json_out(["ok" => false, "error" => "Only the counsellor can do this."], 403);
        if ($booking["status"] !== "pending") json_out(["ok" => false, "error" => "Booking is not pending."], 409);

        if ($action === "confirm") {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=?")->execute([$bid]);
            $isVideo = $booking["session_type"] === "video";
            $pdo->prepare("INSERT INTO sessions (booking_id, session_type, meeting_type, meeting_link, status)
                           VALUES (?, ?, ?, ?, 'scheduled')")
                ->execute([$bid, $booking["session_type"], $isVideo ? "video_call" : "chat_room",
                           $isVideo ? make_meeting_link($bid) : null]);
            $pdo->commit();
            notify($pdo, (int)$booking["client_id"], "Booking confirmed",
                $booking["counselor_name"] . " confirmed your session on $when.", "booking");
            audit($pdo, "booking_confirmed", "booking", $bid);
        } else {
            $pdo->prepare("UPDATE bookings SET status='declined' WHERE id=?")->execute([$bid]);
            notify($pdo, (int)$booking["client_id"], "Booking declined",
                $booking["counselor_name"] . " could not accept the session on $when. Please choose another time.", "booking");
            audit($pdo, "booking_declined", "booking", $bid);
        }
        json_out(["ok" => true]);
    }

    json_out(["ok" => false, "error" => "Unknown action."], 400);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log("bookings api: " . $e->getMessage());
    json_out(["ok" => false, "error" => "Something went wrong. Please try again."], 500);
}
