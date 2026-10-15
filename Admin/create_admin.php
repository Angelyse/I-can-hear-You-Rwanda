<?php

require_once "../config/database.php";

/*
| SECURITY: this page can only be used to create the FIRST administrator.
| Once any admin exists it refuses to run, so it cannot be abused.
| Delete this file after setup.
*/
$adminExists = (int)$pdo->query("SELECT COUNT(*) FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.name = 'admin'")->fetchColumn();
if ($adminExists > 0) {
    http_response_code(403);
    exit("An administrator already exists. Delete Admin/create_admin.php.");
}


$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid name and email address.";

    } elseif (strlen($password) < 8) {
        $error = "Password must contain at least 8 characters.";

    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";

    } else {

        try {

            $check = $pdo->prepare(
                "SELECT id FROM users WHERE email = ?"
            );

            $check->execute([$email]);

            if ($check->fetch()) {

                $error = "This email is already registered.";

            } else {

                $roleQuery = $pdo->prepare(
                    "SELECT id FROM roles WHERE name = 'admin'"
                );

                $roleQuery->execute();

                $roleId = $roleQuery->fetchColumn();

                if (!$roleId) {

                    $error = "Admin role not found. Check the roles table.";

                } else {

                    $hashedPassword = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $stmt = $pdo->prepare(
                        "INSERT INTO users
                        (name, email, password, role_id, status, created_at)
                        VALUES (?, ?, ?, ?, 'active', NOW())"
                    );

                    $stmt->execute([
                        $name,
                        $email,
                        $hashedPassword,
                        $roleId
                    ]);

                    $message = "Administrator account created successfully!";
                }
            }

        } catch (PDOException $e) {

            error_log("Admin creation error: " . $e->getMessage());

            $error = "Could not create the account. Check the database setup.";
        }
    }
}

function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Administrator</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: #f2f6f5;
            font-family: Arial, sans-serif;
        }

        .admin-card {
            width: 100%;
            max-width: 440px;
            padding: 30px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            color: #247b67;
            font-size: 25px;
        }

        p {
            color: #69767b;
            line-height: 1.5;
        }

        label {
            display: block;
            margin: 16px 0 7px;
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #d7e0de;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 13px;
            border: none;
            border-radius: 8px;
            background: #247b67;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .message, .error {
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 8px;
        }

        .message {
            background: #e7f7ed;
            color: #1e7144;
        }

        .error {
            background: #fff0f0;
            color: #a32d2d;
        }
    </style>
</head>

<body>

<div class="admin-card">

    <h1>Create Administrator</h1>

    <p>Create the first administrator account for I Can Hear You Rwanda.</p>

    <?php if ($message !== ""): ?>
        <div class="message">
            <?= h($message) ?>
            <p>
                <a href="../pages/login.php">Go to login</a>
            </p>
        </div>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= h($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($message === ""): ?>

        <form method="POST">

            <label for="name">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                required
            >

            <label for="email">Administrator Email</label>
            <input
                type="email"
                id="email"
                name="email"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                minlength="8"
                required
            >

            <label for="confirm_password">Confirm Password</label>
            <input
                type="password"
                id="confirm_password"
                name="confirm_password"
                minlength="8"
                required
            >

            <button type="submit">Create Admin Account</button>

        </form>

    <?php endif; ?>

</div>

</body>
</html>