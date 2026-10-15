<?php
/*
|   POST action=toggle_save  resource_id                      (any signed-in user) -> JSON
|   POST action=upload       title, description, type, category, external_url, file  (counsellor/admin)
|   POST action=delete       resource_id                      (owner or admin)
|   POST action=set_status   resource_id, status              (admin)
*/
require_once __DIR__ . "/../includes/bootstrap.php";

if (empty($_SESSION["logged_in"])) json_out(["ok" => false, "error" => "Not signed in."], 401);
if ($_SERVER["REQUEST_METHOD"] !== "POST") json_out(["ok" => false, "error" => "POST required."], 405);
verifyCsrfToken();

$uid = (int)currentUserId();
$role = currentUserRole();
$action = $_POST["action"] ?? "";
$rid = filter_input(INPUT_POST, "resource_id", FILTER_VALIDATE_INT);

$home = $role === "admin" ? "../Admin/resources.php" : "../counselor/resources.php";

try {
    if ($action === "toggle_save") {
        if (!$rid) json_out(["ok" => false, "error" => "Invalid resource."], 422);
        $has = $pdo->prepare("SELECT id FROM saved_resources WHERE user_id=? AND resource_id=?");
        $has->execute([$uid, $rid]);
        if ($has->fetch()) {
            $pdo->prepare("DELETE FROM saved_resources WHERE user_id=? AND resource_id=?")->execute([$uid, $rid]);
            json_out(["ok" => true, "saved" => false]);
        }
        $pdo->prepare("INSERT INTO saved_resources (user_id, resource_id) SELECT ?, id FROM resources WHERE id=? AND status='published'")->execute([$uid, $rid]);
        json_out(["ok" => true, "saved" => true]);
    }

    if (!in_array($role, ["counsellor", "admin"], true)) json_out(["ok" => false, "error" => "Forbidden."], 403);

    if ($action === "upload") {
        $title = trim($_POST["title"] ?? "");
        $type = $_POST["type"] ?? "";
        $url = trim($_POST["external_url"] ?? "");
        if ($title === "" || !in_array($type, ["article", "guide", "video", "exercise"], true)) {
            flash_set("Title and a valid type are required.", "error");
            redirect($home);
        }
        if ($url !== "" && !preg_match('#^https?://#i', $url)) {
            flash_set("Link must start with http:// or https://", "error");
            redirect($home);
        }
        $path = null;
        if (!empty($_FILES["file"]["name"]) && $_FILES["file"]["error"] === UPLOAD_ERR_OK) {
            $allowed = ["pdf" => "application/pdf", "png" => "image/png", "jpg" => "image/jpeg", "jpeg" => "image/jpeg",
                        "mp4" => "video/mp4", "docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document"];
            $ext = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
            $mime = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES["file"]["tmp_name"]);
            if (!isset($allowed[$ext]) || ($mime !== $allowed[$ext] && !($ext === "docx" && $mime === "application/zip"))) {
                flash_set("Unsupported file type.", "error");
                redirect($home);
            }
            if ($_FILES["file"]["size"] > 25 * 1024 * 1024) { flash_set("File is larger than 25 MB.", "error"); redirect($home); }
            $name = bin2hex(random_bytes(12)) . "." . $ext;
            if (!move_uploaded_file($_FILES["file"]["tmp_name"], __DIR__ . "/../uploads/resources/" . $name)) throw new RuntimeException("Upload failed.");
            $path = "uploads/resources/" . $name;
        }
        if (!$path && $url === "") { flash_set("Add a file or a link.", "error"); redirect($home); }

        // Counsellor uploads wait for admin approval when the platform requires it.
        $status = $role === "admin" ? "published" : (setting($pdo, "counsellor_admin_approval", "1") === "1" ? "pending" : "published");
        $pdo->prepare("INSERT INTO resources (title, description, file_path, external_url, type, category, uploaded_by, status)
                       VALUES (?,?,?,?,?,?,?,?)")
            ->execute([$title, trim($_POST["description"] ?? "") ?: null, $path, $url ?: null, $type, trim($_POST["category"] ?? "") ?: null, $uid, $status]);
        audit($pdo, "resource_created", "resource", (int)$pdo->lastInsertId());
        flash_set($status === "pending" ? "Resource submitted for admin approval." : "Resource published.");
        redirect($home);
    }

    if (!$rid) { flash_set("Invalid resource.", "error"); redirect($home); }

    if ($action === "delete") {
        $own = $pdo->prepare("SELECT uploaded_by, file_path FROM resources WHERE id=?");
        $own->execute([$rid]);
        $r = $own->fetch();
        if ($r && ($role === "admin" || (int)$r["uploaded_by"] === $uid)) {
            $pdo->prepare("DELETE FROM resources WHERE id=?")->execute([$rid]);
            if ($r["file_path"] && strpos($r["file_path"], "uploads/resources/") === 0) @unlink(__DIR__ . "/../" . $r["file_path"]);
            audit($pdo, "resource_deleted", "resource", $rid);
            flash_set("Resource deleted.");
        } else {
            flash_set("You cannot delete this resource.", "error");
        }
        redirect($home);
    }

    if ($action === "set_status" && $role === "admin") {
        $status = $_POST["status"] ?? "";
        if (in_array($status, ["published", "draft", "archived", "pending"], true)) {
            $pdo->prepare("UPDATE resources SET status=? WHERE id=?")->execute([$status, $rid]);
            audit($pdo, "resource_status_" . $status, "resource", $rid);
            flash_set("Resource updated.");
        }
        redirect($home);
    }

    flash_set("Unknown action.", "error");
} catch (Throwable $e) {
    error_log("resources api: " . $e->getMessage());
    flash_set("Something went wrong.", "error");
}
redirect($home);
