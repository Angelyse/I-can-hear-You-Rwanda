<?php
/*
| Create / change status / delete for schools, organizations and programs.
| POST: entity, action (create|status|delete), id, csrf_token
*/
require_once __DIR__ . "/../includes/bootstrap.php";
require_once __DIR__ . "/../includes/admin_entities.php";
requireRole("admin");

if ($_SERVER["REQUEST_METHOD"] !== "POST") redirect("dashboard.php");
verifyCsrfToken();

$entities = admin_entities();
$entity = $_POST["entity"] ?? "";
if (!isset($entities[$entity])) redirect("dashboard.php");
$def = $entities[$entity];
$back = $entity . ".php";
$table = $def["table"]; // from the whitelist above, never from user input
$action = $_POST["action"] ?? "";
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

try {
    if ($action === "create") {
        [$values, $err] = admin_validate_entity($def, $_POST);
        if ($err) { flash_set($err, "error"); redirect($back); }
        $cols = array_keys($values);
        $sql = "INSERT INTO $table (" . implode(",", $cols) . ") VALUES (" . implode(",", array_fill(0, count($cols), "?")) . ")";
        $pdo->prepare($sql)->execute(array_values($values));
        audit($pdo, $entity . "_created", $entity, (int)$pdo->lastInsertId());
        flash_set($def["title"] . " added.");
    } elseif ($action === "status" && $id) {
        $status = $_POST["status"] ?? "";
        if (!isset($def["status_options"][$status])) { flash_set("Invalid status.", "error"); redirect($back); }
        $pdo->prepare("UPDATE $table SET status = ? WHERE id = ?")->execute([$status, $id]);
        audit($pdo, $entity . "_status_" . $status, $entity, $id);
        flash_set($def["title"] . " updated.");
    } elseif ($action === "delete" && $id) {
        $pdo->prepare("DELETE FROM $table WHERE id = ?")->execute([$id]);
        audit($pdo, $entity . "_deleted", $entity, $id);
        flash_set($def["title"] . " deleted.");
    } else {
        flash_set("Invalid request.", "error");
    }
} catch (Throwable $e) {
    error_log("crud-actions: " . $e->getMessage());
    flash_set("Could not save. Please try again.", "error");
}
redirect($back);
