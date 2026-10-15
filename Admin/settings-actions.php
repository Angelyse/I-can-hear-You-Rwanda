<?php
/* Save platform settings. POST: setting[key]=value (existing keys only), csrf_token */
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("admin");
if ($_SERVER["REQUEST_METHOD"] !== "POST") redirect("settings.php");
verifyCsrfToken();

try {
    $existing = $pdo->query("SELECT setting_key FROM settings")->fetchAll(PDO::FETCH_COLUMN);
    $booleans = $pdo->query("SELECT setting_key FROM settings WHERE setting_value IN ('0','1')")->fetchAll(PDO::FETCH_COLUMN);
    $posted = $_POST["setting"] ?? [];
    $upd = $pdo->prepare("UPDATE settings SET setting_value = ?, updated_by = ? WHERE setting_key = ?");
    foreach ($existing as $key) {
        if (in_array($key, $booleans, true)) {
            $upd->execute([isset($posted[$key]) ? "1" : "0", currentUserId(), $key]);
        } elseif (isset($posted[$key]) && is_string($posted[$key])) {
            $upd->execute([mb_substr(trim($posted[$key]), 0, 1000), currentUserId(), $key]);
        }
    }
    audit($pdo, "settings_updated", "settings", null);
    flash_set("Settings saved.");
} catch (Throwable $e) {
    error_log("settings-actions: " . $e->getMessage());
    flash_set("Could not save settings.", "error");
}
redirect("settings.php");
