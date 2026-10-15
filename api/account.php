<?php
/*
| Account endpoints used by profile/settings forms (classic POST + redirect).
|   action=client_profile | counselor_profile | password | prefs | deactivate
*/
require_once __DIR__ . "/../includes/bootstrap.php";

if (empty($_SESSION["logged_in"])) redirect("../pages/login.php");
if ($_SERVER["REQUEST_METHOD"] !== "POST") redirect("../pages/login.php");
verifyCsrfToken();

$uid  = (int)currentUserId();
$role = currentUserRole();
$action = $_POST["action"] ?? "";

// Only allow redirects back into this app's own dashboards.
$allowed = ["client/profile.php", "client/settings.php", "counselor/profile.php", "counselor/settings.php", "Admin/settings.php"];
$return = in_array($_POST["return"] ?? "", $allowed, true) ? $_POST["return"] : "";
$back = $return ? "../" . $return : "../pages/login.php";

try {
    if ($action === "client_profile" && $role === "client") {
        $name  = trim($_POST["full_name"] ?? "");
        $email = strtolower(trim($_POST["email"] ?? ""));
        $phone = trim($_POST["phone"] ?? "");
        $type  = $_POST["client_type"] ?? "youth";
        $lang  = $_POST["preferred_language"] ?? "english";
        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new RuntimeException("Enter a valid name and email.");
        if (!in_array($type, ["youth", "parent", "teacher", "organization_rep"], true)) $type = "youth";
        if (!in_array($lang, ["english", "kinyarwanda", "french"], true)) $lang = "english";

        $dup = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $dup->execute([$email, $uid]);
        if ($dup->fetch()) throw new RuntimeException("That email is already in use.");

        $pdo->beginTransaction();
        $pdo->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=?")->execute([$name, $email, $phone ?: null, $uid]);
        $pdo->prepare("INSERT INTO client_profiles (user_id, client_type, preferred_language, display_name, wellness_information, consent_status, anonymous_profile)
            VALUES (?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE client_type=VALUES(client_type), preferred_language=VALUES(preferred_language),
              display_name=VALUES(display_name), wellness_information=VALUES(wellness_information),
              consent_status=VALUES(consent_status), anonymous_profile=VALUES(anonymous_profile)")
            ->execute([$uid, $type, $lang, trim($_POST["display_name"] ?? "") ?: null,
                       trim($_POST["wellness_information"] ?? "") ?: null,
                       !empty($_POST["consent"]) ? "accepted" : "withdrawn", !empty($_POST["anonymous"]) ? 1 : 0]);
        $pdo->commit();
        $_SESSION["user_name"] = $name;
        $_SESSION["user_email"] = $email;
        audit($pdo, "profile_updated", "user", $uid);
        flash_set("Profile saved.");
        redirect($back);
    }

    if ($action === "counselor_profile" && $role === "counsellor") {
        $name = trim($_POST["full_name"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        if ($name === "") throw new RuntimeException("Name is required.");
        $types = array_values(array_intersect(["video", "chat"], (array)($_POST["session_types"] ?? [])));
        if (!$types) throw new RuntimeException("Select at least one session type.");
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE users SET name=?, phone=? WHERE id=?")->execute([$name, $phone ?: null, $uid]);
        $pdo->prepare("UPDATE counselor_profiles SET qualification=?, specialization=?, years_experience=?,
              languages=?, bio=?, availability=?, session_types=? WHERE user_id=?")
            ->execute([trim($_POST["qualification"] ?? ""), trim($_POST["specialization"] ?? ""),
                       max(0, (int)($_POST["years_experience"] ?? 0)), trim($_POST["languages"] ?? ""),
                       trim($_POST["bio"] ?? ""), trim($_POST["availability"] ?? ""), implode(",", $types), $uid]);
        $pdo->commit();
        $_SESSION["user_name"] = $name;
        audit($pdo, "counsellor_profile_updated", "user", $uid);
        flash_set("Profile saved.");
        redirect($back);
    }

    if ($action === "password") {
        $cur = $_POST["current_password"] ?? "";
        $new = $_POST["new_password"] ?? "";
        $min = max(8, (int)setting($pdo, "password_min_length", 8));
        $hash = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $hash->execute([$uid]);
        if (!password_verify($cur, (string)$hash->fetchColumn())) throw new RuntimeException("Current password is incorrect.");
        if ($new !== ($_POST["confirm_password"] ?? "")) throw new RuntimeException("New passwords do not match.");
        if (strlen($new) < $min) throw new RuntimeException("Password must be at least $min characters.");
        $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
        notify($pdo, $uid, "Password changed", "Your password was changed. If this was not you, contact support.", "security");
        audit($pdo, "password_changed", "user", $uid);
        flash_set("Password updated.");
        redirect($back);
    }

    if ($action === "prefs") {
        $scopes = [
            "client" => ["pref_new_messages", "pref_reminders", "pref_new_resources", "pref_system", "pref_show_profile", "pref_save_history"],
            "counsellor" => ["pref_new_bookings", "pref_cancellations", "pref_client_messages", "pref_reminders", "pref_weekly_summary"],
        ];
        foreach ($scopes[$role] ?? [] as $k) {
            // Unchecked checkboxes are not submitted, so a missing key means "off".
            set_user_pref($pdo, $uid, $k, isset($_POST[$k]) ? "1" : "0");
        }
        if ($role === "client") {
            $lang = $_POST["preferred_language"] ?? "";
            if (in_array($lang, ["english", "kinyarwanda", "french"], true)) {
                $pdo->prepare("UPDATE client_profiles SET preferred_language=?, anonymous_profile=? WHERE user_id=?")
                    ->execute([$lang, !empty($_POST["anonymous_profile"]) ? 1 : 0, $uid]);
            }
        }
        flash_set("Preferences saved.");
        redirect($back);
    }

    if ($action === "deactivate" && in_array($role, ["client", "counsellor"], true)) {
        $hash = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $hash->execute([$uid]);
        if (!password_verify($_POST["current_password"] ?? "", (string)$hash->fetchColumn())) {
            flash_set("Password is incorrect.", "error");
            redirect("../" . ($role === "client" ? "client" : "counselor") . "/settings.php");
        }
        $pdo->prepare("UPDATE users SET status='inactive' WHERE id=?")->execute([$uid]);
        audit($pdo, "account_deactivated", "user", $uid);
        redirect("../pages/logout.php");
    }

    flash_set("Unknown action.", "error");
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    flash_set($e instanceof RuntimeException ? $e->getMessage() : "Something went wrong. Please try again.", "error");
    if (!($e instanceof RuntimeException)) error_log("account api: " . $e->getMessage());
}
redirect($back);
