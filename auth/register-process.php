<?php

session_start();

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Only allow POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../pages/register.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Get form data
|--------------------------------------------------------------------------
*/

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$role = trim($_POST["role"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";
$terms = isset($_POST["terms"]);


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

$errors = [];


if ($name === "") {
    $errors[] = "Full name is required.";
}


if ($email === "") {

    $errors[] = "Email address is required.";

} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $errors[] = "Please enter a valid email address.";

}


if ($role === "") {
    $errors[] = "Please select an account type.";
}


if ($password === "") {

    $errors[] = "Password is required.";

} elseif (strlen($password) < 8) {

    $errors[] = "Password must contain at least 8 characters.";

}


if ($password !== $confirmPassword) {
    $errors[] = "Passwords do not match.";
}


if (!$terms) {
    $errors[] = "You must accept the terms and privacy policy.";
}


/*
|--------------------------------------------------------------------------
| Check existing email
|--------------------------------------------------------------------------
*/

if (empty($errors)) {

    $stmt = $pdo->prepare(
        "SELECT id FROM users WHERE email = ? LIMIT 1"
    );

    $stmt->execute([$email]);

    $existingUser = $stmt->fetch();

    if ($existingUser) {
        $errors[] = "An account with this email already exists.";
    }

}


/*
|--------------------------------------------------------------------------
| If there are errors
|--------------------------------------------------------------------------
*/

if (!empty($errors)) {

    $_SESSION["register_errors"] = $errors;

    header("Location: ../pages/register.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Determine role
|--------------------------------------------------------------------------
*/

if ($role === "counsellor") {

    $roleName = "counsellor";

} else {

    $roleName = "client";

}


/*
|--------------------------------------------------------------------------
| Get role ID
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare(
    "SELECT id FROM roles WHERE name = ? LIMIT 1"
);

$stmt->execute([$roleName]);

$roleRecord = $stmt->fetch();


if (!$roleRecord) {

    $_SESSION["register_errors"] = [
        "Selected account role is not configured."
    ];

    header("Location: ../pages/register.php");
    exit;

}


$roleId = $roleRecord["id"];


/*
|--------------------------------------------------------------------------
| Hash password
|--------------------------------------------------------------------------
*/

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| Start database transaction
|--------------------------------------------------------------------------
*/

try {

    $pdo->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Insert user
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO users
        (
            role_id,
            name,
            email,
            phone,
            password,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            'active'
        )
    ");

    $stmt->execute([
        $roleId,
        $name,
        $email,
        $phone,
        $hashedPassword
    ]);


    $userId = $pdo->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | Create client profile
    |--------------------------------------------------------------------------
    */

    if ($roleName === "client") {

        $stmt = $pdo->prepare("
            INSERT INTO client_profiles
            (
                user_id,
                client_type,
                preferred_language,
                consent_status
            )
            VALUES
            (
                ?,
                'youth',
                'english',
                'pending'
            )
        ");

        $stmt->execute([
            $userId
        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Create counsellor profile
    |--------------------------------------------------------------------------
    */

    if ($roleName === "counsellor") {

        $stmt = $pdo->prepare("
            INSERT INTO counselor_profiles
            (
                user_id,
                qualification,
                specialization,
                years_experience,
                languages,
                status
            )
            VALUES
            (
                ?,
                '',
                '',
                0,
                'English',
                'pending'
            )
        ");

        $stmt->execute([
            $userId
        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Commit transaction
    |--------------------------------------------------------------------------
    */

    $pdo->commit();


    /*
    |--------------------------------------------------------------------------
    | Success message
    |--------------------------------------------------------------------------
    */

    $_SESSION["register_success"] =
        "Account created successfully. You can now log in.";


    header("Location: ../pages/login.php");
    exit;


} catch (PDOException $e) {

    /*
    |--------------------------------------------------------------------------
    | Rollback if something fails
    |--------------------------------------------------------------------------
    */

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }


    $_SESSION["register_errors"] = [
        "Registration failed. Please try again."
    ];


    /*
    | For development only:
    | You can temporarily uncomment this line
    | to see the actual database error.
    |
    | $_SESSION["register_errors"] = [$e->getMessage()];
    */


    header("Location: ../pages/register.php");
    exit;

}