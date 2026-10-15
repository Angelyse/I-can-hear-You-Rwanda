<link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-counsellors.css">




    <?php

require_once "../config/auth.php";
requireRole("admin");

require_once "../config/database.php";
require_once "../config/security.php";

$csrfToken = generateCsrfToken();

$search = trim($_GET["search"] ?? "");
$statusFilter = $_GET["status"] ?? "";
$specializationFilter = $_GET["specialization"] ?? "";

$allowedStatuses = ["active", "pending", "inactive"];

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = "";
}

$where = ["r.name = 'counsellor'"];
$params = [];

if ($search !== "") {
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $term = "%" . $search . "%";
    array_push($params, $term, $term, $term);
}

if ($statusFilter !== "") {
    $where[] = "cp.status = ?";
    $params[] = $statusFilter;
}

if ($specializationFilter !== "") {
    $where[] = "cp.specialization = ?";
    $params[] = $specializationFilter;
}

$sql = "
    SELECT
        u.id,
        u.name,
        u.email,
        u.phone,
        u.status AS account_status,
        u.created_at,
        cp.qualification,
        cp.specialization,
        cp.years_experience,
        cp.languages,
        cp.bio,
        cp.status AS counsellor_status
    FROM users u
    INNER JOIN roles r ON r.id = u.role_id
    LEFT JOIN counselor_profiles cp ON cp.user_id = u.id
    WHERE " . implode(" AND ", $where) . "
    ORDER BY u.created_at DESC, u.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$counsellors = $stmt->fetchAll();

$stats = $pdo->query("
    SELECT
        COUNT(*) AS total,
        SUM(cp.status = 'active') AS active,
        SUM(cp.status = 'pending') AS pending,
        SUM(cp.status = 'inactive') AS inactive
    FROM users u
    INNER JOIN roles r ON r.id = u.role_id
    LEFT JOIN counselor_profiles cp ON cp.user_id = u.id
    WHERE r.name = 'counsellor'
")->fetch();

$specializations = $pdo->query("
    SELECT DISTINCT specialization
    FROM counselor_profiles
    WHERE specialization IS NOT NULL
      AND specialization <> ''
    ORDER BY specialization
")->fetchAll(PDO::FETCH_COLUMN);

$message = $_GET["message"] ?? "";
$messageType = $_GET["type"] ?? "success";

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function counsellorStatusClass(string $status): string
{
    return in_array($status, ["active", "pending", "inactive"], true)
        ? $status
        : "inactive";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Counsellor Management | I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-counsellors.css">

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
            <a href="users.php">
                <i class="fas fa-users"></i><span>Users</span>
            </a>
            <a href="counsellors.php" class="active">
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
                <h2>Counsellor Management</h2>
                <p>Review and manage counsellor accounts.</p>
            </div>

            <div class="header-user">
                <i class="fas fa-user-shield"></i>
                <span><?= h($_SESSION["user_name"] ?? "Administrator") ?></span>
            </div>
        </header>

        <section class="counsellors-page" style="margin-left: 260px;">

            <div class="counsellors-heading">
                <div>
                    <h1>Counsellors Management</h1>
                    <p>Manage counsellor profiles, qualifications and approval status.</p>
                </div>

                <button type="button" class="primary-button" id="openAddCounsellor">
                    <i class="fas fa-plus"></i> Add Counsellor
                </button>
            </div>

            <?php if ($message !== ""): ?>
                <div class="notice <?= h($messageType) ?>">
                    <?= h($message) ?>
                </div>
            <?php endif; ?>

            <div class="counsellor-stat-grid">
                <article class="counsellor-stat-card">
                    <span>Total Counsellors</span>
                    <strong><?= (int) ($stats["total"] ?? 0) ?></strong>
                </article>

                <article class="counsellor-stat-card">
                    <span>Active</span>
                    <strong><?= (int) ($stats["active"] ?? 0) ?></strong>
                </article>

                <article class="counsellor-stat-card">
                    <span>Pending Approval</span>
                    <strong><?= (int) ($stats["pending"] ?? 0) ?></strong>
                </article>

                <article class="counsellor-stat-card">
                    <span>Inactive</span>
                    <strong><?= (int) ($stats["inactive"] ?? 0) ?></strong>
                </article>
            </div>

            <form method="GET" action="counsellors.php" class="counsellor-filters">

                <input
                    type="search"
                    name="search"
                    value="<?= h($search) ?>"
                    placeholder="Search by name, email or phone..."
                >

                <select name="specialization">
                    <option value="">All specializations</option>
                    <?php foreach ($specializations as $specialization): ?>
                        <option
                            value="<?= h($specialization) ?>"
                            <?= $specializationFilter === $specialization ? "selected" : "" ?>
                        >
                            <?= h($specialization) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status">
                    <option value="">All statuses</option>
                    <?php foreach ($allowedStatuses as $status): ?>
                        <option
                            value="<?= h($status) ?>"
                            <?= $statusFilter === $status ? "selected" : "" ?>
                        >
                            <?= h(ucfirst($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="primary-button">Filter</button>
                <a href="counsellors.php" class="secondary-button">Clear</a>

            </form>

            <div class="counsellors-table-wrapper">
                <table class="counsellors-table">
                    <thead>
                        <tr>
                            <th>Counsellor</th>
                            <th>Specialization</th>
                            <th>Qualification</th>
                            <th>Experience</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (empty($counsellors)): ?>

                        <tr>
                            <td colspan="6" class="empty-counsellors">
                                No counsellors found. You can add a counsellor using the button above.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($counsellors as $counsellor): ?>
                            <?php
                            $status = $counsellor["counsellor_status"]
                                ?: $counsellor["account_status"];
                            ?>

                            <tr>
                                <td>
                                    <strong><?= h($counsellor["name"]) ?></strong>
                                    <small><?= h($counsellor["email"]) ?></small>
                                    <small><?= h($counsellor["phone"] ?: "No phone") ?></small>
                                </td>

                                <td><?= h($counsellor["specialization"] ?: "Not specified") ?></td>

                                <td><?= h($counsellor["qualification"] ?: "Not specified") ?></td>

                                <td><?= (int) ($counsellor["years_experience"] ?? 0) ?> years</td>

                                <td>
                                    <span class="counsellor-status <?= h(counsellorStatusClass($status)) ?>">
                                        <?= h(ucfirst($status)) ?>
                                    </span>
                                </td>

                                <td class="counsellor-actions">

                                    <button
                                        type="button"
                                        class="edit-counsellor"
                                        data-id="<?= (int) $counsellor["id"] ?>"
                                        data-name="<?= h($counsellor["name"]) ?>"
                                        data-email="<?= h($counsellor["email"]) ?>"
                                        data-phone="<?= h($counsellor["phone"]) ?>"
                                        data-qualification="<?= h($counsellor["qualification"]) ?>"
                                        data-specialization="<?= h($counsellor["specialization"]) ?>"
                                        data-experience="<?= (int) ($counsellor["years_experience"] ?? 0) ?>"
                                        data-languages="<?= h($counsellor["languages"]) ?>"
                                        data-bio="<?= h($counsellor["bio"]) ?>"
                                        data-status="<?= h($status) ?>"
                                    >Edit</button>

                                    <?php if ($status === "pending"): ?>
                                        <form method="POST" action="counsellor-actions.php">
                                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="user_id" value="<?= (int) $counsellor["id"] ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="approve-action"
                                                onclick="return confirm('Approve this counsellor?')">
                                                Approve
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($status === "active"): ?>
                                        <form method="POST" action="counsellor-actions.php">
                                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="user_id" value="<?= (int) $counsellor["id"] ?>">
                                            <input type="hidden" name="status" value="inactive">
                                            <button type="submit" class="status-action"
                                                onclick="return confirm('Deactivate this counsellor?')">
                                                Deactivate
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="counsellor-actions.php">
                                            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
                                            <input type="hidden" name="action" value="change_status">
                                            <input type="hidden" name="user_id" value="<?= (int) $counsellor["id"] ?>">
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="approve-action"
                                                onclick="return confirm('Activate this counsellor?')">
                                                Activate
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

            <p class="counsellor-result-count">
                Showing <?= count($counsellors) ?> counsellor(s).
            </p>

        </section>
    </main>
</div>

<!-- Add/Edit Counsellor Modal -->
<div class="counsellor-modal" id="counsellorModal" hidden>
    <div class="counsellor-modal-content">

        <div class="modal-heading">
            <div>
                <h2 id="modalTitle">Add Counsellor</h2>
                <p>Enter the counsellor's account and professional details.</p>
            </div>

            <button type="button" id="closeCounsellorModal" aria-label="Close">
                &times;
            </button>
        </div>

        <form method="POST" action="counsellor-actions.php" id="counsellorForm">

            <input type="hidden" name="csrf_token" value="<?= h($csrfToken) ?>">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="user_id" id="counsellorId">

            <label for="cName">Full Name</label>
            <input type="text" id="cName" name="name" required>

            <label for="cEmail">Email Address</label>
            <input type="email" id="cEmail" name="email" required>

            <label for="cPhone">Phone Number</label>
            <input type="text" id="cPhone" name="phone">

            <label for="cQualification">Qualification</label>
            <input type="text" id="cQualification" name="qualification">

            <label for="cSpecialization">Specialization</label>
            <input type="text" id="cSpecialization" name="specialization"
                   placeholder="e.g. Family Counselling">

            <label for="cExperience">Years of Experience</label>
            <input type="number" id="cExperience" name="years_experience"
                   min="0" max="80" value="0" required>

            <label for="cLanguages">Languages</label>
            <input type="text" id="cLanguages" name="languages" value="English"
                   placeholder="English, Kinyarwanda">

            <label for="cBio">Professional Biography</label>
            <textarea id="cBio" name="bio" rows="3"></textarea>

            <label for="cStatus">Account Status</label>
            <select id="cStatus" name="status" required>
                <option value="pending">Pending Approval</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <label for="cPassword">
                Temporary Password
                <span id="passwordHint">(required for new accounts)</span>
            </label>
            <input type="password" id="cPassword" name="password"
                   minlength="8" autocomplete="new-password">

            <small>For existing accounts, leave the password blank to keep it unchanged.</small>

            <div class="modal-buttons">
                <button type="button" id="cancelCounsellorModal"
                        class="secondary-button">Cancel</button>
                <button type="submit" class="primary-button">Save Counsellor</button>
            </div>

        </form>
    </div>
</div>

<script src="../assets/js/admin-counsellors.js"></script>

<script>
document.getElementById("mobileMenu")?.addEventListener("click", function () {
    document.getElementById("sidebar")?.classList.toggle("show");
});
</script>

</body>
</html>