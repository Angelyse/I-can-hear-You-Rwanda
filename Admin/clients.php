
<?php
require_once "../config/auth.php";
requireRole("admin");

require_once "../config/database.php";
require_once "../config/security.php";

function h($value): string
{
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

$search = trim($_GET["search"] ?? "");
$status = $_GET["status"] ?? "";
$consent = $_GET["consent"] ?? "";

$allowedStatuses = ["active", "pending", "inactive", "suspended"];
$allowedConsents = ["pending", "accepted", "withdrawn"];

if (!in_array($status, $allowedStatuses, true)) {
    $status = "";
}

if (!in_array($consent, $allowedConsents, true)) {
    $consent = "";
}

$message = $_GET["message"] ?? "";
$messageType = ($_GET["type"] ?? "") === "error" ? "error" : "success";

try {
    // Statistics for client accounts.
    $statsQuery = $pdo->query("
        SELECT
            COUNT(*) AS total_clients,
            COALESCE(SUM(u.status = 'active'), 0) AS active_clients,
            COALESCE(SUM(u.status = 'pending'), 0) AS pending_clients,
            COALESCE(SUM(u.status = 'inactive'), 0) AS inactive_clients,
            COALESCE(SUM(u.status = 'suspended'), 0) AS suspended_clients
        FROM users u
        INNER JOIN roles r ON r.id = u.role_id
        WHERE r.name = 'client'
    ");

    $stats = $statsQuery->fetch();

    // Build the client search and filter query.
    $sql = "
        SELECT
            u.id,
            u.name,
            u.email,
            u.phone,
            u.status AS account_status,
            u.created_at,
            cp.client_type,
            cp.preferred_language,
            cp.display_name,
            cp.consent_status,
            cp.anonymous_profile
        FROM users u
        INNER JOIN roles r ON r.id = u.role_id
        LEFT JOIN client_profiles cp ON cp.user_id = u.id
        WHERE r.name = 'client'
    ";

    $params = [];

    if ($search !== "") {
        $sql .= "
            AND (
                u.name LIKE :search_name
                OR u.email LIKE :search_email
                OR u.phone LIKE :search_phone
            )
        ";

        $searchTerm = "%" . $search . "%";

        $params["search_name"] = $searchTerm;
        $params["search_email"] = $searchTerm;
        $params["search_phone"] = $searchTerm;
    }

    if ($status !== "") {
        $sql .= " AND u.status = :account_status";
        $params["account_status"] = $status;
    }

    if ($consent !== "") {
        $sql .= " AND cp.consent_status = :consent_status";
        $params["consent_status"] = $consent;
    }

    $sql .= " ORDER BY u.created_at DESC, u.id DESC";

    $clientsQuery = $pdo->prepare($sql);
    $clientsQuery->execute($params);
    $clients = $clientsQuery->fetchAll();

} catch (PDOException $e) {
    error_log("Admin clients page error: " . $e->getMessage());
    http_response_code(500);
    die(
        "Unable to load client records. Please check your database tables " .
        "and column names, then review the PHP error log."
    );
}

$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Client Management | I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-clients.css">

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="dashboard-container">

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <span>
                <img src="../assets/images/log.jpeg"
                     alt="I Can Hear You Rwanda logo">
            </span>

            <div>
                <h2>I Can Hear You Rwanda</h2>
                <p>Admin Panel</p>
            </div>
        </div>

        <nav class="sidebar-menu">
            <a href="dashboard.php" class="menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>

            <a href="users.php" class="menu-item">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>

            <a href="counsellors.php" class="menu-item">
                <i class="fas fa-user-doctor"></i>
                <span>Counsellors</span>
            </a>

            <a href="clients.php" class="menu-item active">
                <i class="fas fa-user-group"></i>
                <span>Clients</span>
            </a>

            <a href="bookings.php" class="menu-item">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
            </a>

            <a href="sessions.php" class="menu-item">
                <i class="fas fa-video"></i>
                <span>Sessions</span>
            </a>

            <a href="resources.php" class="menu-item">
                <i class="fas fa-book-open"></i>
                <span>Resources</span>
            </a>

            <a href="programs.php" class="menu-item">
                <i class="fas fa-graduation-cap"></i>
                <span>Programs</span>
            </a>

            <a href="schools.php" class="menu-item">
                <i class="fas fa-school"></i>
                <span>Schools</span>
            </a>

            <a href="organizations.php" class="menu-item">
                <i class="fas fa-building"></i>
                <span>Organizations</span>
            </a>

            <a href="reports.php" class="menu-item">
                <i class="fas fa-chart-pie"></i>
                <span>Reports</span>
            </a>

            <a href="notifications.php" class="menu-item">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>

            <a href="settings.php" class="menu-item">
                <i class="fas fa-gear"></i>
                <span>Settings</span>
            </a>

            <a href="../pages/logout.php" class="menu-item">
                <i class="fas fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </nav>
    </aside>

    <main class="dashboard-main clients-page" >

        <header class="clients-header" style="margin-left: 250px;">
            <div>
                <h1>Client Management</h1>
                <p>View client accounts and manage account access.</p>
            </div>

            <button type="button" class="mobile-menu-button"
                    id="mobileMenu" aria-label="Open navigation">
                <i class="fas fa-bars"></i>
            </button>
        </header>

        <?php if ($message !== ""): ?>
            <div class="client-notice <?= h($messageType) ?>">
                <i class="fas <?= $messageType === "success"
                    ? "fa-circle-check" : "fa-circle-exclamation" ?>"></i>
                <?= h($message) ?>
            </div>
        <?php endif; ?>

        <section class="client-stats">

            <div class="client-stat-card">
                <div class="client-stat-icon total-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div>
                    <p>Total Clients</p>
                    <h2><?= number_format((int)$stats["total_clients"]) ?></h2>
                </div>
            </div>

            <div class="client-stat-card">
                <div class="client-stat-icon active-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div>
                    <p>Active</p>
                    <h2><?= number_format((int)$stats["active_clients"]) ?></h2>
                </div>
            </div>

            <div class="client-stat-card">
                <div class="client-stat-icon pending-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p>Pending</p>
                    <h2><?= number_format((int)$stats["pending_clients"]) ?></h2>
                </div>
            </div>

            <div class="client-stat-card">
                <div class="client-stat-icon inactive-icon">
                    <i class="fas fa-user-lock"></i>
                </div>
                <div>
                    <p>Inactive</p>
                    <h2><?= number_format(
                        (int)$stats["inactive_clients"] +
                        (int)$stats["suspended_clients"]
                    ) ?></h2>
                </div>
            </div>

        </section>

        <section class="clients-content">

            <div class="clients-section-heading">
                <div>
                    <h2>Registered Clients</h2>
                    <p><?= count($clients) ?> matching account(s)</p>
                </div>
            </div>

            <form method="GET" action="clients.php" class="clients-filters">

                <div class="client-search">
                    <i class="fas fa-search"></i>
                    <input
                        type="search"
                        name="search"
                        value="<?= h($search) ?>"
                        placeholder="Search name, email or phone..."
                    >
                </div>

                <select name="status" aria-label="Filter by account status">
                    <option value="">All account statuses</option>
                    <option value="active" <?= $status === "active" ? "selected" : "" ?>>Active</option>
                    <option value="pending" <?= $status === "pending" ? "selected" : "" ?>>Pending</option>
                    <option value="inactive" <?= $status === "inactive" ? "selected" : "" ?>>Inactive</option>
                    <option value="suspended" <?= $status === "suspended" ? "selected" : "" ?>>Suspended</option>
                </select>

                <select name="consent" aria-label="Filter by consent status">
                    <option value="">All consent statuses</option>
                    <option value="pending" <?= $consent === "pending" ? "selected" : "" ?>>Consent pending</option>
                    <option value="accepted" <?= $consent === "accepted" ? "selected" : "" ?>>Consent accepted</option>
                    <option value="withdrawn" <?= $consent === "withdrawn" ? "selected" : "" ?>>Consent withdrawn</option>
                </select>

                <button type="submit" class="client-filter-button">
                    <i class="fas fa-filter"></i> Filter
                </button>

                <a href="clients.php" class="client-reset-button">Reset</a>

            </form>

            <div class="clients-table-wrapper">
                <table class="clients-table">
                    <thead>
                    <tr>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Client Type</th>
                        <th>Language</th>
                        <th>Consent</th>
                        <th>Account Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($clients)): ?>
                        <tr>
                            <td colspan="8" class="clients-empty">
                                <i class="fas fa-users"></i>
                                <p>No clients found.</p>
                                <span>Try changing your search or filters.</span>
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($clients as $client): ?>
                            <?php
                            $accountStatus = $client["account_status"] ?? "pending";
                            $consentStatus = $client["consent_status"] ?? "pending";
                            $displayName = !empty($client["anonymous_profile"])
                                ? ($client["display_name"] ?: "Anonymous client")
                                : $client["name"];
                            ?>

                            <tr>
                                <td>
                                    <div class="client-name-cell">
                                        <div class="client-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <strong><?= h($displayName) ?></strong>
                                            <small>Client ID: <?= (int)$client["id"] ?></small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <div class="client-contact">
                                        <span><?= h($client["email"]) ?></span>
                                        <small><?= h($client["phone"] ?: "No phone provided") ?></small>
                                    </div>
                                </td>

                                <td><?= h(
                                    ucwords(str_replace(
                                        "_", " ",
                                        $client["client_type"] ?? "Not specified"
                                    ))
                                ) ?></td>

                                <td><?= h(
                                    ucfirst($client["preferred_language"] ?? "Not specified")
                                ) ?></td>

                                <td>
                                    <span class="client-status consent-<?= h($consentStatus) ?>">
                                        <?= h(ucfirst($consentStatus)) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="client-status status-<?= h($accountStatus) ?>">
                                        <?= h(ucfirst($accountStatus)) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= $client["created_at"]
                                        ? h(date("d M Y", strtotime($client["created_at"])))
                                        : "—" ?>
                                </td>

                                <td>
                                    <?php if ($accountStatus !== "active"): ?>
                                        <form method="POST"
                                              action="client-actions.php"
                                              class="client-action-form"
                                              onsubmit="return confirm('Activate this client account?');">
                                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                                            <input type="hidden" name="client_id" value="<?= (int)$client["id"] ?>">
                                            <input type="hidden" name="action" value="activate">
                                            <button type="submit" class="client-action activate-action">
                                                Activate
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST"
                                              action="client-actions.php"
                                              class="client-action-form"
                                              onsubmit="return confirm('Deactivate this client account?');">
                                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                                            <input type="hidden" name="client_id" value="<?= (int)$client["id"] ?>">
                                            <input type="hidden" name="action" value="deactivate">
                                            <button type="submit" class="client-action deactivate-action">
                                                Deactivate
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($accountStatus !== "suspended"): ?>
                                        <form method="POST"
                                              action="client-actions.php"
                                              class="client-action-form"
                                              onsubmit="return confirm('Suspend this client account?');">
                                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                                            <input type="hidden" name="client_id" value="<?= (int)$client["id"] ?>">
                                            <input type="hidden" name="action" value="suspend">
                                            <button type="submit" class="client-action suspend-action">
                                                Suspend
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="clients-privacy-note">
                <i class="fas fa-shield-halved"></i>
                Private wellness information is not displayed in this list.
                Access to sensitive client records should be limited to authorized personnel.
            </p>

        </section>

    </main>
</div>

<script>
document.getElementById("mobileMenu")?.addEventListener("click", function () {
    document.getElementById("sidebar")?.classList.toggle("show");
});
</script>

</body>
</html>
