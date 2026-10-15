<?php
/*
| Common page variables for signed-in dashboards.
| Expects bootstrap.php + requireRole() to have been called already.
*/
$me = current_user($pdo);
$meId = (int)($me["id"] ?? 0);
$meName = $me["name"] ?? "User";
$meInitials = initials($meName);
$unreadMsgs = unread_messages($pdo, $meId);
$unreadNotifs = unread_notifications($pdo, $meId);
$csrf = generateCsrfToken();
