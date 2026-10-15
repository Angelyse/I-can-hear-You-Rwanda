<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Check if user is logged in
|--------------------------------------------------------------------------
*/

function requireLogin()
{
    if (
        !isset($_SESSION["logged_in"]) ||
        $_SESSION["logged_in"] !== true
    ) {

        header("Location: ../pages/login.php");

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Get current user ID
|--------------------------------------------------------------------------
*/

function currentUserId()
{
    return $_SESSION["user_id"] ?? null;
}


/*
|--------------------------------------------------------------------------
| Get current user role
|--------------------------------------------------------------------------
*/

function currentUserRole()
{
    return $_SESSION["user_role"] ?? null;
}


/*
|--------------------------------------------------------------------------
| Check user role
|--------------------------------------------------------------------------
*/

function requireRole($role)
{
    requireLogin();

    if (
        !isset($_SESSION["user_role"]) ||
        $_SESSION["user_role"] !== $role
    ) {

        http_response_code(403);

        die("Access denied.");
    }
}