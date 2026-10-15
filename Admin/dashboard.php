<?php

require_once "../config/auth.php";
requireRole("admin");

require_once "../config/database.php";

// Count all registered users.
$totalUsers = (int) $pdo->query(
    "SELECT COUNT(*) FROM users"
)->fetchColumn();

// Count counsellors by their assigned role.
$totalCounsellors = (int) $pdo->query("
    SELECT COUNT(*)
    FROM users u
    INNER JOIN roles r ON u.role_id = r.id
    WHERE r.name = 'counsellor'
")->fetchColumn();

// Count clients by their assigned role.
$totalClients = (int) $pdo->query("
    SELECT COUNT(*)
    FROM users u
    INNER JOIN roles r ON u.role_id = r.id
    WHERE r.name = 'client'
")->fetchColumn();

// Count sessions currently marked active.
$activeSessions = (int) $pdo->query("
    SELECT COUNT(*)
    FROM sessions
    WHERE status = 'active'
")->fetchColumn();

// Count all bookings.
$totalBookings = (int) $pdo->query(
    "SELECT COUNT(*) FROM bookings"
)->fetchColumn();

// Count all programs.
$totalPrograms = (int) $pdo->query(
    "SELECT COUNT(*) FROM programs"
)->fetchColumn();

// Get the latest registered users.
$recentUsers = $pdo->query("
    SELECT
        u.id,
        u.name,
        u.email,
        u.status,
        u.created_at,
        r.name AS role_name
    FROM users u
    INNER JOIN roles r ON u.role_id = r.id
    ORDER BY u.created_at DESC, u.id DESC
    LIMIT 5
")->fetchAll();

// Get the latest bookings.
$recentBookings = $pdo->query("
    SELECT
        b.id,
        b.booking_date,
        b.start_time,
        b.status,
        b.session_type,
        client.name AS client_name,
        counselor.name AS counselor_name,
        s.name AS service_name
    FROM bookings b
    INNER JOIN users client ON b.client_id = client.id
    INNER JOIN users counselor ON b.counselor_id = counselor.id
    LEFT JOIN services s ON b.service_id = s.id
    ORDER BY b.booking_date DESC, b.start_time DESC, b.id DESC
    LIMIT 5
")->fetchAll();

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<div class="dashboard-container">

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <a href="dashboard.php" class="logo">
                <span>
                    <img src="../assets/images/log.jpeg" alt="I Can Hear You">
                </span>
                I Can Hear You Rwanda
            </a>
        </div>

        <div class="admin-profile">
            <div class="admin-avatar">
                AU
            </div>

            <div>
                <strong>Admin User</strong>
                <small>System Administrator</small>
            </div>
        </div>

        <nav class="sidebar-nav">

            <!-- MAIN MENU -->
            <div class="sidebar-section">
                <span class="sidebar-section-title">Main Menu</span>

                <a href="dashboard.php" class="active">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>

                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>

                <a href="counsellors.php">
                    <i class="fas fa-user-doctor"></i>
                    <span>Counsellors</span>
                </a>

                <a href="clients.php">
                    <i class="fas fa-user-group"></i>
                    <span>Clients</span>
                </a>

                <a href="bookings.php">
                    <i class="fas fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>

                <a href="sessions.php">
                    <i class="fas fa-video"></i>
                    <span>Sessions</span>
                </a>
            </div>

            <!-- MANAGEMENT -->
            <div class="sidebar-section">
                <span class="sidebar-section-title">Management</span>

                <a href="resources.php">
                    <i class="fas fa-book-open"></i>
                    <span>Resources</span>
                </a>

                <a href="programs.php">
                    <i class="fas fa-layer-group"></i>
                    <span>Programs</span>
                </a>

                <a href="schools.php">
                    <i class="fas fa-school"></i>
                    <span>Schools</span>
                </a>

                <a href="organizations.php">
                    <i class="fas fa-building"></i>
                    <span>Organizations</span>
                </a>
            </div>

            <!-- REPORTS -->
            <div class="sidebar-section">
                <span class="sidebar-section-title">Reports</span>

                <a href="reports.php">
                    <i class="fas fa-chart-pie"></i>
                    <span>Reports & Analytics</span>
                </a>
            </div>

            <!-- SYSTEM -->
            <div class="sidebar-section">
                <span class="sidebar-section-title">System</span>

                <a href="notifications.php">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <span class="sidebar-badge">5</span>
                </a>

                <a href="settings.php">
                    <i class="fas fa-gear"></i>
                    <span>Settings</span>
                </a>

                <a href="../pages/logout.php">
    <i class="fas fa-right-from-bracket"></i>
    <span>Logout</span>
</a>
            </div>

        </nav>

    </aside>


    <!-- ================= MAIN CONTENT ================= -->
    <main class="dashboard-main">

        <!-- TOP HEADER -->
        <header class="dashboard-header">

            <div class="header-left">

                <button class="mobile-menu" id="mobileMenu">
                    <i class="fas fa-bars"></i>
                </button>

                <div class="page-title">
                    <h1>Admin Dashboard</h1>
                    <p>Manage and monitor the I Can Hear You Rwanda platform.</p>
                </div>

            </div>

            <div class="header-right">

                <div class="header-search">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        id="globalSearch"
                        placeholder="Search..."
                    >
                </div>

                <button class="header-icon" id="notificationBtn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-dot"></span>
                </button>

                <div class="header-profile">
                    <div class="header-avatar">
                        AU
                    </div>

                    <div>
                        <strong>Admin User</strong>
                        <small>Administrator</small>
                    </div>

                    <i class="fas fa-chevron-down"></i>
                </div>

            </div>

        </header>


        <!-- ================= CONTENT ================= -->
        <div class="admin-content">

            <!-- WELCOME -->
            <section class="admin-welcome">

                <div>
                    <h2>Good morning, Admin 👋</h2>
                    <p>
                        Here is an overview of what is happening
                        on your platform today.
                    </p>
                </div>

                <div class="system-status">
                    <span class="status-dot"></span>
                    System Operational
                </div>

            </section>


            <!-- ================= STATS ================= -->
            <section class="admin-stats">

                <div class="admin-stat-card">

                    <div class="stat-icon users-icon">
                        <i class="fas fa-users"></i>
                    </div>

                    <div class="stat-info">
                        <span>Total Users</span>
                        <strong><?= number_format($totalUsers) ?></strong>
                        <small class="positive">
                            <i class="fas fa-arrow-up"></i>
                            12% this month
                        </small>
                    </div>

                </div>


                <div class="admin-stat-card">

                    <div class="stat-icon counsellor-icon">
                        <i class="fas fa-user-doctor"></i>
                    </div>

                    <div class="stat-info">
                        <span>Counsellors</span>
                        <strong><?= number_format($totalCounsellors) ?></strong>
                        <small class="positive">
                            <i class="fas fa-arrow-up"></i>
                            4 new this month
                        </small>
                    </div>

                </div>


                <div class="admin-stat-card">

                    <div class="stat-icon client-icon">
                        <i class="fas fa-user-group"></i>
                    </div>

                    <div class="stat-info">
                        <span>Active Clients</span>
                        <strong><?= number_format($totalClients) ?></strong>
                        <small class="positive">
                            <i class="fas fa-arrow-up"></i>
                            9% this month
                        </small>
                    </div>

                </div>


                <div class="admin-stat-card">

                    <div class="stat-icon session-icon">
                        <i class="fas fa-video"></i>
                    </div>

                    <div class="stat-info">
                        <span>Active Sessions</span>
                        <strong><?= number_format($activeSessions) ?></strong>
                        <small>
                            Currently active
                        </small>
                    </div>

                </div>

            </section>


            <!-- SECOND STATS -->
            <section class="admin-stats secondary-stats">

                <div class="admin-stat-card">

                    <div class="stat-icon booking-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>

                    <div class="stat-info">
                        <span>Total Bookings</span>
                        <strong><?= number_format($totalBookings) ?></strong>
                        <small class="positive">
                            <i class="fas fa-arrow-up"></i>
                            18% this month
                        </small>
                    </div>

                </div>


                <div class="admin-stat-card">

                    <div class="stat-icon completed-icon">
                        <i class="fas fa-circle-check"></i>
                    </div>

                    <div class="stat-info">
                        <span>Completed Sessions</span>
                        <strong><?= number_format($totalPrograms) ?></strong>
                        <small>
                            All time
                        </small>
                    </div>

                </div>


                <div class="admin-stat-card">

                    <div class="stat-icon resource-icon">
                        <i class="fas fa-book-open"></i>
                    </div>

                    <div class="stat-info">
                        <span>Resources</span>
                        <strong><?= number_format($totalUsers) ?></strong>
                        <small>
                            Published resources
                        </small>
                    </div>

                </div>


                <div class="admin-stat-card">

                    <div class="stat-icon program-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>

                    <div class="stat-info">
                        <span>Programs</span>
                        <strong><?= number_format($totalPrograms) ?></strong>
                        <small>
                            Active programs
                        </small>
                    </div>

                </div>

            </section>


            <!-- ================= QUICK ACTIONS ================= -->
            <section class="admin-section">

                <div class="section-header">
                    <div>
                        <h2>Quick Actions</h2>
                        <p>Common administrative tasks</p>
                    </div>
                </div>

                <div class="quick-actions">

                    <button class="quick-action" data-action="user">
                        <span>
                            <i class="fas fa-user-plus"></i>
                        </span>
                        <strong>Add User</strong>
                        <small>Create a new platform account</small>
                    </button>

                    <button class="quick-action" data-action="counsellor">
                        <span>
                            <i class="fas fa-user-doctor"></i>
                        </span>
                        <strong>Add Counsellor</strong>
                        <small>Register a counsellor</small>
                    </button>

                    <button class="quick-action" data-action="resource">
                        <span>
                            <i class="fas fa-book"></i>
                        </span>
                        <strong>Add Resource</strong>
                        <small>Publish a new resource</small>
                    </button>

                    <button class="quick-action" data-action="program">
                        <span>
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <strong>Create Program</strong>
                        <small>Create a wellness program</small>
                    </button>

                </div>

            </section>


            <!-- ================= TWO COLUMN AREA ================= -->
            <section class="admin-grid">


                <!-- RECENT BOOKINGS -->
                <div class="admin-panel">

                    <div class="panel-header">

                        <div>
                            <h2>Recent Bookings</h2>
                            <p>Latest counselling appointments</p>
                        </div>

                        <a href="bookings.php">View All</a>

                    </div>

                    <div class="booking-list">

                        <div class="booking-row">

                            <div class="booking-avatar">
                                AN
                            </div>

                            <div class="booking-details">
                                <strong>Alice Niyonsenga</strong>
                                <span>Dr. Jean Mukamana</span>
                            </div>

                            <div class="booking-time">
                                <strong>Oct 5</strong>
                                <span>09:00 AM</span>
                            </div>

                            <span class="status confirmed">
                                Confirmed
                            </span>

                        </div>
                        <tbody>
    <?php if (empty($recentBookings)): ?>

        <tr>
            <td colspan="5">No bookings have been created yet.</td>
        </tr>

    <?php else: ?>

        <?php foreach ($recentBookings as $booking): ?>

            <tr>
                <td>
                    <?= h($booking["client_name"]) ?>
                </td>

                <td>
                    <?= h($booking["counselor_name"]) ?>
                </td>

                <td>
                    <?= h(
                        $booking["booking_date"]
                            ? date(
                                "d M Y",
                                strtotime($booking["booking_date"])
                            )
                            : "—"
                    ) ?>
                </td>

                <td>
                    <?= h($booking["service_name"] ?? $booking["session_type"]) ?>
                </td>

                <td>
                    <?= h(ucfirst($booking["status"])) ?>
                </td>
            </tr>

        <?php endforeach; ?>

    <?php endif; ?>
</tbody>



                    </div>

                </div>


                <!-- RECENT USERS -->
                <div class="admin-panel">

                    <div class="panel-header">

                        <div>
                            <h2>Recent Users</h2>
                            <p>Newly registered users</p>
                        </div>

                        <a href="users.php">View All</a>

                    </div>

                    <div class="user-list">


                    <tbody>
    <?php if (empty($recentUsers)): ?>

        <tr>
            <td colspan="4">No users registered yet.</td>
        </tr>

    <?php else: ?>

        <?php foreach ($recentUsers as $user): ?>

            <tr>
                <td><?= h($user["name"]) ?></td>

                <td><?= h($user["email"]) ?></td>

                <td><?= h(ucfirst($user["role_name"])) ?></td>

                <td><?= h(ucfirst($user["status"])) ?></td>
            </tr>

        <?php endforeach; ?>

    <?php endif; ?>
</tbody>
<!-- 
                        <div class="user-row">

                            <div class="user-avatar">
                                AN
                            </div>

                            <div class="user-details">
                                <strong>Alice Niyonsenga</strong>
                                <span>alice@example.com</span>
                            </div>

                            <span class="role client-role">
                                Client
                            </span>

                        </div>
 
                        <div class="user-row">

                            <div class="user-avatar">
                                JM
                            </div>

                            <div class="user-details">
                                <strong>Dr. Jean Mukamana</strong>
                                <span>jean@example.com</span>
                            </div>

                            <span class="role counsellor-role">
                                Counsellor
                            </span>

                        </div>


                        <div class="user-row">

                            <div class="user-avatar">
                                SM
                            </div>

                            <div class="user-details">
                                <strong>Sarah Mukamana</strong>
                                <span>sarah@example.com</span>
                            </div>

                            <span class="role client-role">
                                Client
                            </span>

                        </div>


                        <div class="user-row">

                            <div class="user-avatar">
                                PH
                            </div>

                            <div class="user-details">
                                <strong>Patrick Habimana</strong>
                                <span>patrick@example.com</span>
                            </div>

                            <span class="role client-role">
                                Client
                            </span>

                        </div> -->

                    </div>

                </div>

            </section>


            <!-- ================= ANALYTICS ================= -->
            <section class="admin-grid analytics-grid">

                <div class="admin-panel">

                    <div class="panel-header">

                        <div>
                            <h2>Platform Activity</h2>
                            <p>Sessions completed this week</p>
                        </div>

                        <select id="activityPeriod">
                            <option>This Week</option>
                            <option>This Month</option>
                            <option>This Year</option>
                        </select>

                    </div>

                    <div class="activity-chart">

                        <div class="chart-value">
                            <strong><?= number_format($totalUsers) ?></strong>
                            <span>sessions</span>
                        </div>

                        <div class="chart-bars">

                            <div class="chart-column">
                                <div class="bar" style="height: 45%;"></div>
                                <span>Mon</span>
                            </div>

                            <div class="chart-column">
                                <div class="bar" style="height: 70%;"></div>
                                <span>Tue</span>
                            </div>

                            <div class="chart-column">
                                <div class="bar" style="height: 55%;"></div>
                                <span>Wed</span>
                            </div>

                            <div class="chart-column">
                                <div class="bar" style="height: 85%;"></div>
                                <span>Thu</span>
                            </div>

                            <div class="chart-column">
                                <div class="bar" style="height: 65%;"></div>
                                <span>Fri</span>
                            </div>

                            <div class="chart-column">
                                <div class="bar" style="height: 35%;"></div>
                                <span>Sat</span>
                            </div>

                            <div class="chart-column">
                                <div class="bar" style="height: 25%;"></div>
                                <span>Sun</span>
                            </div>

                        </div>

                    </div>

                </div>


                <!-- SYSTEM ACTIVITY -->
                <div class="admin-panel">

                    <div class="panel-header">

                        <div>
                            <h2>System Activity</h2>
                            <p>Recent administrative activity</p>
                        </div>

                        <a href="notifications.php">View All</a>

                    </div>

                    <div class="activity-list">

                        <div class="activity-item">

                            <div class="activity-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>

                            <div>
                                <strong>New user registered</strong>
                                <span>Sarah Mukamana joined the platform</span>
                                <small>10 minutes ago</small>
                            </div>

                        </div>


                        <div class="activity-item">

                            <div class="activity-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>

                            <div>
                                <strong>New booking created</strong>
                                <span>David booked a session</span>
                                <small>35 minutes ago</small>
                            </div>

                        </div>


                        <div class="activity-item">

                            <div class="activity-icon">
                                <i class="fas fa-book"></i>
                            </div>

                            <div>
                                <strong>Resource published</strong>
                                <span>New mental health guide added</span>
                                <small>1 hour ago</small>
                            </div>

                        </div>


                        <div class="activity-item">

                            <div class="activity-icon">
                                <i class="fas fa-shield-halved"></i>
                            </div>

                            <div>
                                <strong>Security check completed</strong>
                                <span>System security status verified</span>
                                <small>2 hours ago</small>
                            </div>

                        </div>

                    </div>

                </div>

            </section>


            <!-- ================= SYSTEM HEALTH ================= -->
            <section class="system-health">

                <div class="section-header">

                    <div>
                        <h2>System Health</h2>
                        <p>Current platform status</p>
                    </div>

                </div>

                <div class="health-grid">

                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-server"></i>
                        </div>

                        <div>
                            <strong>Server</strong>
                            <span class="health-good">Operational</span>
                        </div>
                    </div>


                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-database"></i>
                        </div>

                        <div>
                            <strong>Database</strong>
                            <span class="health-good">Operational</span>
                        </div>
                    </div>


                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-video"></i>
                        </div>

                        <div>
                            <strong>Video Service</strong>
                            <span class="health-good">Operational</span>
                        </div>
                    </div>


                    <div class="health-card">
                        <div class="health-icon">
                            <i class="fas fa-envelope"></i>
                        </div>

                        <div>
                            <strong>Notifications</strong>
                            <span class="health-good">Operational</span>
                        </div>
                    </div>

                </div>

            </section>

        </div>

    </main>

</div>


<script src="../assets/js/admin-dashboard.js"></script>

</body>
</html>