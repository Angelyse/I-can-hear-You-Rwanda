<?php

require_once "../config/auth.php";
requireRole("admin");

require_once "../config/database.php";
require_once "../config/security.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: users.php");
    exit;
}

verifyCsrfToken();

$action = $_POST["action"] ?? "";
$redirect = "users.php";

function finishAction(string $message, string $type = "success"): void
{
    header(
        "Location: users.php?message=" .
        urlencode($message) .
        "&type=" . urlencode($type)
    );
    exit;
}

function validRole(PDO $pdo, string $role): bool
{
    $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
    $stmt->execute([$role]);

    return (bool) $stmt->fetch();
}

try {
    if ($action === "create" || $action === "update") {
        $userId = (int) ($_POST["user_id"] ?? 0);
        $name = trim($_POST["name"] ?? "");
        $email = strtolower(trim($_POST["email"] ?? ""));
        $phone = trim($_POST["phone"] ?? "");
        $role = trim($_POST["role"] ?? "");
        $status = trim($_POST["status"] ?? "active");
        $password = $_POST["password"] ?? "";

        $allowedRoles = [
            "admin",
            "counsellor",
            "client",
            "coordinator",
            "supervisor"
        ];

        $allowedStatuses = [
            "active",
            "pending",
            "inactive",
            "suspended"
        ];

        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            finishAction("Enter a valid name and email address.", "error");
        }

        if (
            !in_array($role, $allowedRoles, true) ||
            !validRole($pdo, $role)
        ) {
            finishAction("Please select a valid account role.", "error");
        }

        if (!in_array($status, $allowedStatuses, true)) {
            finishAction("Please select a valid account status.", "error");
        }

        $check = $pdo->prepare(
            "SELECT id FROM users WHERE email = ? AND id <> ?"
        );
        $check->execute([$email, $action === "update" ? $userId : 0]);

        if ($check->fetch()) {
            finishAction("That email address is already registered.", "error");
        }

        $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $roleStmt->execute([$role]);
        $roleId = (int) $roleStmt->fetchColumn();

        if ($action === "create") {
            if (strlen($password) < 8) {
                finishAction(
                    "The new user's password must contain at least 8 characters.",
                    "error"
                );
            }

            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO users
                (name, email, phone, password, role_id, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())"
            );

            $stmt->execute([
                $name,
                $email,
                $phone !== "" ? $phone : null,
                password_hash($password, PASSWORD_DEFAULT),
                $roleId,
                $status
            ]);

            $newUserId = (int) $pdo->lastInsertId();

            if ($role === "client") {
                $stmt = $pdo->prepare(
                    "INSERT INTO client_profiles
                    (user_id, client_type, preferred_language, consent_status)
                    VALUES (?, 'youth', 'english', 'pending')"
                );
                $stmt->execute([$newUserId]);
            } elseif ($role === "counsellor") {
                $stmt = $pdo->prepare(
                    "INSERT INTO counselor_profiles
                    (user_id, qualification, specialization,
                     years_experience, languages, status)
                    VALUES (?, '', '', 0, 'English', 'pending')"
                );
                $stmt->execute([$newUserId]);
            }

            $pdo->commit();

            finishAction("User created successfully.");
        }

        if ($userId < 1) {
            finishAction("Invalid user selected.", "error");
        }

        // Prevent the current administrator from accidentally
        // changing their own role or disabling their own account.
        if ($userId === (int) currentUserId()) {
            $stmt = $pdo->prepare(
                "SELECT role_id FROM users WHERE id = ?"
            );
            $stmt->execute([$userId]);
            $currentRoleId = (int) $stmt->fetchColumn();

            $stmt = $pdo->prepare(
                "SELECT name FROM roles WHERE id = ?"
            );
            $stmt->execute([$currentRoleId]);
            $currentRole = $stmt->fetchColumn();

            if ($role !== $currentRole || $status !== "active") {
                finishAction(
                    "You cannot change your own role or deactivate your own account here.",
                    "error"
                );
            }
        }

        if ($password !== "" && strlen($password) < 8) {
            finishAction(
                "A new password must contain at least 8 characters.",
                "error"
            );
        }

        if ($password !== "") {
            $stmt = $pdo->prepare(
                "UPDATE users
                 SET name = ?, email = ?, phone = ?, role_id = ?,
                     status = ?, password = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $name,
                $email,
                $phone !== "" ? $phone : null,
                $roleId,
                $status,
                password_hash($password, PASSWORD_DEFAULT),
                $userId
            ]);
        } else {
            $stmt = $pdo->prepare(
                "UPDATE users
                 SET name = ?, email = ?, phone = ?, role_id = ?, status = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $name,
                $email,
                $phone !== "" ? $phone : null,
                $roleId,
                $status,
                $userId
            ]);
        }

        finishAction("User updated successfully.");
    }

    if ($action === "change_status") {
        $userId = (int) ($_POST["user_id"] ?? 0);
        $status = $_POST["status"] ?? "";

        if ($userId === (int) currentUserId()) {
            finishAction("You cannot change your own account status.", "error");
        }

        if (!in_array($status, ["active", "pending", "inactive", "suspended"], true)) {
            finishAction("Invalid account status.", "error");
        }

        $stmt = $pdo->prepare(
            "UPDATE users SET status = ? WHERE id = ?"
        );
        $stmt->execute([$status, $userId]);

        finishAction("User status updated.");
    }

    if ($action === "delete") {
        $userId = (int) ($_POST["user_id"] ?? 0);

        if ($userId === (int) currentUserId()) {
            finishAction("You cannot delete your own account.", "error");
        }

        // Deletion can fail when other records reference this user.
        // Deactivation is usually safer for preserving counselling records.
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "DELETE FROM client_profiles WHERE user_id = ?"
        );
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare(
            "DELETE FROM counselor_profiles WHERE user_id = ?"
        );
        $stmt->execute([$userId]);

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        $pdo->commit();

        finishAction("User deleted, if no related records prevent deletion.");
    }

    finishAction("Unknown action.", "error");

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // Log details privately in production; don't expose database errors.
    error_log("User management error: " . $e->getMessage());

    finishAction(
        "The operation could not be completed. Check whether related records depend on this user.",
        "error"
    );
}