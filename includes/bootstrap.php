<?php
/*
| Loads everything a protected page needs.
| Usage:  require_once __DIR__ . "/../includes/bootstrap.php";  requireRole("client");
*/
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/security.php";
require_once __DIR__ . "/helpers.php";
