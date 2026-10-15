<?php

require_once "../config/auth.php";
requireRole("admin");

require_once "../config/database.php";
require_once "../config/security.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: counsellors.php");
    exit;
}

verifyCsrfToken();

function finishCounsellorAction(string $message, string $type = "success"): void
{
    header(
        "Location: counsellors.php?message=" .
        urlencode($message) .
        "&type=" . urlencode($type)
    );
    exit;
}

try {
    $action = $_POST["action"] ?? "";

    if ($action === "create" || $action === "update") {
        $userId = (int) ($_POST["user_id"] ?? 0);
        $name = trim($_POST["name"] ?? "");
        $email = strtolower(trim($_POST["email"] ?? ""));
        $phone = trim($_POST["phone"] ?? "");
        $qualification = trim($_POST["qualification"] ?? "");
        $specialization = trim($_POST["specialization"] ?? "");
        $experience = filter_var(
            $_POST["years_experience"] ?? 0,
            FILTER_VALIDATE_INT
        );
        $languages = trim($_POST["languages"] ?? "English");
        $bio = trim($_POST["bio"] ?? "");
        $status = $_POST["status"] ?? "pending";
        $password = $_POST["password"] ?? "";

        if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            finishCounsellorAction(
                "Enter a valid counsellor name and email.",
                "error"
            );
        }

        if ($experience === false || $experience < 0 || $experience > 80) {
            finishCounsellorAction(
                "Enter a valid number of years of experience.",
                "error"
            );
        }

        if (!in_array($status, ["active", "pending", "inactive"], true)) {
            finishCounsellorAction("Invalid counsellor status.", "error");
        }

        $check = $pdo->prepare(
            "SELECT id FROM users WHERE email = ? AND id <> ?"
        );
        $check->execute([$email, $action === "update" ? $userId : 0]);

        if ($check->fetch()) {
            finishCounsellorAction(
                "This email address is already registered.",
                "error"
            );
        }

        $roleStmt = $pdo->prepare(
            "SELECT id FROM roles WHERE name = 'counsellor'"
        );
        $roleStmt->execute();
        $roleId = $roleStmt->fetchColumn();

        if (!$roleId) {
            finishCounsellorAction(
                "The counsellor role was not found in the roles table.",
                "error"
            );
        }

        $pdo->beginTransaction();

        if ($action === "create") {
            if (strlen($password) < 8) {
                $pdo->rollBack();
                finishCounsellorAction(
                    "The password must contain at least 8 characters.",
                    "error"
                );
            }

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

            $userId = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare(
                "INSERT INTO counselor_profiles
                (user_id, qualification, specialization, years_experience,
                 languages, bio, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $userId,
                $qualification,
                $specialization,
                $experience,
                $languages,
                $bio,
                $status
            ]);

            $pdo->commit();

            finishCounsellorAction("Counsellor created successfully.");
        }

        if ($userId < 1) {
            $pdo->rollBack();
            finishCounsellorAction("Invalid counsellor selected.", "error");
        }

        $stmt = $pdo->prepare(
            "SELECT u.id
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND r.name = 'counsellor'"
        );
        $stmt->execute([$userId]);

        if (!$stmt->fetch()) {
            $pdo->rollBack();
            finishCounsellorAction(
                "The selected account is not a counsellor.",
                "error"
            );
        }

        if ($password !== "" && strlen($password) < 8) {
            $pdo->rollBack();
            finishCounsellorAction(
                "A new password must contain at least 8 characters.",
                "error"
            );
        }

        if ($password !== "") {
            $stmt = $pdo->prepare(
                "UPDATE users
                 SET name = ?, email = ?, phone = ?, status = ?, password = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $name,
                $email,
                $phone !== "" ? $phone : null,
                $status,
                password_hash($password, PASSWORD_DEFAULT),
                $userId
            ]);
        } else {
            $stmt = $pdo->prepare(
                "UPDATE users
                 SET name = ?, email = ?, phone = ?, status = ?
                 WHERE id = ?"
            );

            $stmt->execute([
                $name,
                $email,
                $phone !== "" ? $phone : null,
                $status,
                $userId
            ]);
        }

        $stmt = $pdo->prepare(
            "INSERT INTO counselor_profiles
            (user_id, qualification, specialization, years_experience,
             languages, bio, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                qualification = VALUES(qualification),
                specialization = VALUES(specialization),
                years_experience = VALUES(years_experience),
                languages = VALUES(languages),
                bio = VALUES(bio),
                status = VALUES(status)"
        );

        $stmt->execute([
            $userId,
            $qualification,
            $specialization,
            $experience,
            $languages,
            $bio,
            $status
        ]);

        $pdo->commit();

        finishCounsellorAction("Counsellor updated successfully.");
    }

    if ($action === "change_status") {
        $userId = (int) ($_POST["user_id"] ?? 0);
        $status = $_POST["status"] ?? "";

        if (!in_array($status, ["active", "pending", "inactive"], true)) {
            finishCounsellorAction("Invalid status.", "error");
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            "SELECT u.id
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND r.name = 'counsellor'"
        );
        $stmt->execute([$userId]);

        if (!$stmt->fetch()) {
            $pdo->rollBack();
            finishCounsellorAction("Counsellor not found.", "error");
        }

        $stmt = $pdo->prepare(
            "UPDATE users SET status = ? WHERE id = ?"
        );
        $stmt->execute([$status, $userId]);

        $stmt = $pdo->prepare(
            "UPDATE counselor_profiles SET status = ? WHERE user_id = ?"
        );
        $stmt->execute([$status, $userId]);

        $pdo->commit();

        finishCounsellorAction("Counsellor status updated.");
    }

    finishCounsellorAction("Unknown action.", "error");

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Counsellor management error: " . $e->getMessage());

    finishCounsellorAction(
        "The operation failed. Check the database structure and try again.",
        "error"
    );
}