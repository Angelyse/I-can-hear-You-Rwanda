<?php

session_start();

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| Only allow POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../pages/login.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Get login information
|--------------------------------------------------------------------------
*/

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";


/*
|--------------------------------------------------------------------------
| Validate
|--------------------------------------------------------------------------
*/

$errors = [];


if ($email === "") {
    $errors[] = "Email address is required.";
}


if ($password === "") {
    $errors[] = "Password is required.";
}


if (!empty($errors)) {

    $_SESSION["login_errors"] = $errors;

    header("Location: ../pages/login.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Find user
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        users.id,
        users.name,
        users.email,
        users.password,
        users.status,
        roles.name AS role_name
    FROM users

    INNER JOIN roles
        ON users.role_id = roles.id

    WHERE users.email = ?

    LIMIT 1
");

$stmt->execute([$email]);

$user = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Check user
|--------------------------------------------------------------------------
*/

if (!$user) {

    $_SESSION["login_errors"] = [
        "Invalid email or password."
    ];

    header("Location: ../pages/login.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Verify password
|--------------------------------------------------------------------------
*/

if (!password_verify($password, $user["password"])) {

    $_SESSION["login_errors"] = [
        "Invalid email or password."
    ];

    header("Location: ../pages/login.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Check account status
|--------------------------------------------------------------------------
*/

if ($user["status"] !== "active") {

    $_SESSION["login_errors"] = [
        "Your account is not active. Please contact the administrator."
    ];

    header("Location: ../pages/login.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Regenerate session ID
|--------------------------------------------------------------------------
*/

session_regenerate_id(true);


/*
|--------------------------------------------------------------------------
| Store authenticated user
|--------------------------------------------------------------------------
*/

$_SESSION["user_id"] = $user["id"];

$_SESSION["user_name"] = $user["name"];

$_SESSION["user_email"] = $user["email"];

$_SESSION["user_role"] = $user["role_name"];

$_SESSION["logged_in"] = true;


/*
|--------------------------------------------------------------------------
| Update last login
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    UPDATE users
    SET last_login_at = NOW()
    WHERE id = ?
");

$stmt->execute([
    $user["id"]
]);


/*
|--------------------------------------------------------------------------
| Redirect according to role
|--------------------------------------------------------------------------
*/

switch ($user["role_name"]) {

    case "admin":

        header("Location: ../admin/dashboard.php");
        exit;


    case "counsellor":

        header("Location: ../counselor/dashboard.php");
        exit;


    case "client":

        header("Location: ../client/dashboard.php");
        exit;


    case "coordinator":

        /*
        | Coordinator dashboard will be created later.
        */

        header("Location: ../admin/dashboard.php");
        exit;


    case "supervisor":

        /*
        | Supervisor dashboard will be created later.
        */

        header("Location: ../counselor/dashboard.php");
        exit;


    default:

        session_destroy();

        header("Location: ../pages/login.php");
        exit;
}