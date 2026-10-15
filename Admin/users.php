<?php

require_once "../config/auth.php";
requireRole("admin");

require_once "../config/database.php";
require_once "../config/security.php";

$csrfToken = generateCsrfToken();

$search = trim($_GET["search"] ?? "");
$roleFilter = $_GET["role"] ?? "";
$statusFilter = $_GET["status"] ?? "";

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

if (!in_array($roleFilter, $allowedRoles, true)) {
    $roleFilter = "";
}

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = "";
}

$where = [];
$params = [];

if ($search !== "") {
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $term = "%" . $search . "%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

if ($roleFilter !== "") {
    $where[] = "r.name = ?";
    $params[] = $roleFilter;
}

if ($statusFilter !== "") {
    $where[] = "u.status = ?";
    $params[] = $statusFilter;
}

$sql = "
    SELECT
        u.id,
        u.name,
        u.email,
        u.phone,
        u.status,
        u.created_at,
        r.name AS role_name
    FROM users u
    INNER JOIN roles r ON u.role_id = r.id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$stats = $pdo->query("
    SELECT
        COUNT(*) AS total_users,
        SUM(status = 'active') AS active_users,
        SUM(status = 'pending') AS pending_users,
        SUM(status = 'inactive') AS inactive_users
    FROM users
")->fetch();

$roleCounts = $pdo->query("
    SELECT r.name, COUNT(u.id) AS total
    FROM roles r
    LEFT JOIN users u ON u.role_id = r.id
    GROUP BY r.id, r.name
")->fetchAll();

$message = $_GET["message"] ?? "";
$messageType = $_GET["type"] ?? "success";

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function statusClass(string $status): string
{
    $allowed = ["active", "pending", "inactive", "suspended"];

    return in_array($status, $allowed, true) ? $status : "inactive";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>User Management | I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-users.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

<div class="dashboard-layout">

    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <span>
                <img src="../assets/images/log.jpeg" alt="Logo">
            </span>
            <div>
                <strong>I Can Hear You</strong>
                <small>Rwanda</small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php">
                <i class="fas fa-chart-line"></i><span>Dashboard</span>
            </a>
            <a href="users.php" class="active">
                <i class="fas fa-users"></i><span>Users</span>
            </a>
            <a href="counsellors.php">
                <i class="fas fa-user-doctor"></i><span>Counsellors</span>
            </a>
            <a href="clients.php">
                <i class="fas fa-user"></i><span>Clients</span>
            </a>
            <a href="bookings.php">
                <i class="fas fa-calendar-check"></i><span>Bookings</span>
            </a>
            <a href="sessions.php">
                <i class="fas fa-video"></i><span>Sessions</span>
            </a>

            <div class="sidebar-section">
                <span class="sidebar-section-title">Management</span>
                <a href="resources.php">
                    <i class="fas fa-book-open"></i><span>Resources</span>
                </a>
                <a href="programs.php">
                    <i class="fas fa-layer-group"></i><span>Programs</span>
                </a>
                <a href="schools.php">
                    <i class="fas fa-school"></i><span>Schools</span>
                </a>
                <a href="organizations.php">
                    <i class="fas fa-building"></i><span>Organizations</span>
                </a>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-title">System</span>
                <a href="reports.php">
                    <i class="fas fa-chart-pie"></i><span>Reports & Analytics</span>
                </a>
                <a href="notifications.php">
                    <i class="fas fa-bell"></i><span>Notifications</span>
                </a>
                <a href="settings.php">
                    <i class="fas fa-gear"></i><span>Settings</span>
                </a>
                <a href="../pages/logout.php">
                    <i class="fas fa-right-from-bracket"></i><span>Logout</span>
                </a>
            </div>
        </nav>
    </aside>

    <main class="dashboard-main">

        <header class="dashboard-header">
            <button type="button" id="mobileMenu" aria-label="Toggle menu">
                <i class="fas fa-bars"></i>
            </button>

            <div>
                <h2>User Management</h2>
                <p>Manage registered platform accounts.</p>
            </div>

            <div class="header-user">
                <i class="fas fa-user-shield"></i>
                <span>
                    <?= h($_SESSION["user_name"] ?? "Administrator") ?>
                </span>
            </div>
        </header>

        <section class="users-page">

            <div class="users-page-heading">
                <div>
                    <h1>Users Management</h1>
                    <p>View, add, update and manage user accounts.</p>
                </div>

                <button type="button" class="primary-button" id="openAddUser">
                    <i class="fas fa-plus"></i> Add User
                </button>
            </div>

            <?php if ($message !== ""): ?>
                <div class="notice <?= h($messageType) ?>">
                    <?= h($message) ?>
                </div>
            <?php endif; ?>

            <div class="user-stat-grid">
                <article class="user-stat-card">
                    <span>Total Users</span>
                    <strong><?= (int) ($stats["total_users"] ?? 0) ?></strong>
                </article>

                <article class="user-stat-card">
                    <span>Active Users</span>
                    <strong><?= (int) ($stats["active_users"] ?? 0) ?></strong>
                </article>

                <article class="user-stat-card">
                    <span>Pending Users</span>
                    <strong><?= (int) ($stats["pending_users"] ?? 0) ?></strong>
                </article>

                <article class="user-stat-card">
                    <span>Inactive Users</span>
                    <strong><?= (int) ($stats["inactive_users"] ?? 0) ?></strong>
                </article>
            </div>

            <div class="role-counts">
                <?php foreach ($roleCounts as $item): ?>
                    <span>
                        <?= h(ucfirst($item["name"])) ?>:
                        <strong><?= (int) $item["total"] ?></strong>
                    </span>
                <?php endforeach; ?>
            </div>

            <form method="GET" action="users.php" class="user-filters">
                <input
                    type="search"
                    name="search"
                    placeholder="Search name, email or phone..."
                    value="<?= h($search) ?>"
                >

                <select name="role">
                    <option value="">All roles</option>
                    <?php foreach ($allowedRoles as $role): ?>
                        <option value="<?= h($role) ?>"
                            <?= $roleFilter === $role ? "selected" : "" ?>>
                            <?= h(ucfirst($role)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status">
                    <option value="">All statuses</option>
                    <?php foreach ($allowedStatuses as $status): ?>
                        <option value="<?= h($status) ?>"
                            <?= $statusFilter === $status ? "selected" : "" ?>>
                            <?= h(ucfirst($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="primary-button">Filter</button>
                <a href="users.php" class="secondary-button">Clear</a>
            </form>

            <div class="users-table-wrapper">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!$users): ?>
                        <tr>
                            <td colspan="6" class="empty-users">
                                No users found. Try changing your filters.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?= h($user["name"]) ?></strong>
                                    <small class="user-email">
                                        <?= h($user["email"]) ?>
                                    </small>
                                </td>

                                <td><?= h($user["phone"] ?: "—") ?></td>

                                <td>
                                    <span class="role-label">
                                        <?= h(ucfirst($user["role_name"])) ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="status-label <?= h(statusClass($user["status"])) ?>">
                                        <?= h(ucfirst($user["status"])) ?>
                                    </span>
                                </td>

                                <td>
                                    <?= h(
                                        $user["created_at"]
                                            ? date("d M Y", strtotime($user["created_at"]))
                                            : "—"
                                    ) ?>
                                </td>

                                <td class="user-actions">
                                    <button
                                        type="button"
                                        class="edit-user"
                                        data-id="<?= (int) $user["id"] ?>"
                                        data-name="<?= h($user["name"]) ?>"
                                        data-email="<?= h($user["email"]) ?>"
                                        data-phone="<?= h($user["phone"]) ?>"
                                        data-role="<?= h($user["role_name"]) ?>"
                                        data-status="<?= h($user["status"]) ?>"
                                    >Edit</button>

                                    <form method="POST" action="user-actions.php">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= h($csrfToken) ?>">
                                        <input type="hidden" name="action"
                                               value="change_status">
                                        <input type="hidden" name="user_id"
                                               value="<?= (int) $user["id"] ?>">
                                        <input type="hidden" name="status"
                                               value="<?= $user["status"] === "active" ? "inactive" : "active" ?>">

                                        <button type="submit"
                                            class="status-action"
                                            <?= (int) $user["id"] === (int) currentUserId() ? "disabled" : "" ?>
                                            onclick="return confirm('Change this user account status?')">
                                            <?= $user["status"] === "active" ? "Deactivate" : "Activate" ?>
                                        </button>
                                    </form>

                                    <form method="POST" action="user-actions.php">
                                        <input type="hidden" name="csrf_token"
                                               value="<?= h($csrfToken) ?>">
                                        <input type="hidden" name="action"
                                               value="delete">
                                        <input type="hidden" name="user_id"
                                               value="<?= (int) $user["id"] ?>">

                                        <button type="submit"
                                            class="delete-action"
                                            <?= (int) $user["id"] === (int) currentUserId() ? "disabled" : "" ?>
                                            onclick="return confirm('Permanently delete this user? Related records may prevent deletion. Deactivation is safer.')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="users-result-count">
                Showing <?= count($users) ?> matching user(s).
            </p>

        </section>
    </main>
</div>

<!-- Add/Edit User Modal -->
<div class="user-modal" id="userModal" hidden>
    <div class="user-modal-content">
        <div class="modal-heading">
            <div>
                <h2 id="modalTitle">Add User</h2>
                <p>Enter the user's account information.</p>
            </div>

            <button type="button" id="closeUserModal" aria-label="Close">
                &times;
            </button>
        </div>

        <form method="POST" action="user-actions.php" id="userForm">
            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="user_id" id="userId" value="">

            <label for="userName">Full Name</label>
            <input type="text" id="userName" name="name" required>

            <label for="userEmail">Email Address</label>
            <input type="email" id="userEmail" name="email" required>

            <label for="userPhone">Phone Number</label>
            <input type="text" id="userPhone" name="phone">

            <label for="userRole">Role</label>
            <select id="userRole" name="role" required>
                <?php foreach ($allowedRoles as $role): ?>
                    <option value="<?= h($role) ?>">
                        <?= h(ucfirst($role)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="userStatus">Account Status</label>
            <select id="userStatus" name="status" required>
                <?php foreach ($allowedStatuses as $status): ?>
                    <option value="<?= h($status) ?>">
                        <?= h(ucfirst($status)) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="userPassword">
                Password <span id="passwordHint">(required for new users)</span>
            </label>
            <input type="password" id="userPassword" name="password"
                   minlength="8" autocomplete="new-password">
            <small>For editing, leave blank to keep the current password.</small>

            <div class="modal-buttons">
                <button type="button" id="cancelUserModal"
                        class="secondary-button">Cancel</button>
                <button type="submit" class="primary-button">Save User</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/admin-users.js"></script>

<script>
document.getElementById("mobileMenu")?.addEventListener("click", function () {
    document.getElementById("sidebar")?.classList.toggle("show");
});
</script>

</body>
</html>