<?php
/* Admin cancel / complete for a booking. POST: booking_id, action(cancel|complete), csrf_token */
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("admin");
if ($_SERVER["REQUEST_METHOD"] !== "POST") redirect("bookings.php");
verifyCsrfToken();

$id = filter_input(INPUT_POST, "booking_id", FILTER_VALIDATE_INT);
$action = $_POST["action"] ?? "";
try {
    $b = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
    $b->execute([$id]);
    $booking = $b->fetch();
    if (!$booking) { flash_set("Booking not found.", "error"); redirect("bookings.php"); }

    if ($action === "cancel" && in_array($booking["status"], ["pending", "confirmed"], true)) {
        $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?")->execute([$id]);
        $pdo->prepare("UPDATE sessions SET status='cancelled' WHERE booking_id=? AND status IN ('scheduled','active')")->execute([$id]);
        foreach ([(int)$booking["client_id"], (int)$booking["counselor_id"]] as $uid) {
            notify($pdo, $uid, "Booking cancelled", "An administrator cancelled the session on " . fmt_date($booking["booking_date"]) . ".", "booking");
        }
        audit($pdo, "booking_cancelled_by_admin", "booking", $id);
        flash_set("Booking cancelled.");
    } elseif ($action === "complete" && $booking["status"] === "confirmed") {
        $pdo->prepare("UPDATE bookings SET status='completed' WHERE id=?")->execute([$id]);
        $pdo->prepare("UPDATE sessions SET status='completed', ended_at=NOW() WHERE booking_id=? AND status IN ('scheduled','active')")->execute([$id]);
        audit($pdo, "booking_completed_by_admin", "booking", $id);
        flash_set("Booking marked completed.");
    } else {
        flash_set("That action is not available for this booking.", "error");
    }
} catch (Throwable $e) {
    error_log("booking-actions: " . $e->getMessage());
    flash_set("Something went wrong.", "error");
}
redirect("bookings.php");
