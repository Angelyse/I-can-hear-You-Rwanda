<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("admin");
require_once __DIR__ . "/../includes/page_init.php";
$recent = $pdo->query("SELECT title, message, type, audience, MAX(created_at) AS sent_at, COUNT(*) AS recipients
    FROM notifications WHERE type IN ('announcement','system','security') AND audience IS NOT NULL
    GROUP BY title, message, type, audience ORDER BY sent_at DESC LIMIT 25")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notifications Management - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-notifications.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
</head>

<body>

<div class="dashboard-layout">

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <a href="dashboard.php" class="logo">
                <span>
                    <img src="../assets/images/log.jpeg" alt="I Can Hear You">
                </span>
                <strong>I Can Hear You Rwanda</strong>
            </a>
        </div>

        <nav class="sidebar-nav">

            <div class="sidebar-section">

                <span class="sidebar-section-title">
                    Main Menu
                </span>

                <a href="dashboard.php">
                    <i class="fa-solid fa-gauge"></i>
                    <span>Dashboard</span>
                </a>

                <a href="users.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Users</span>
                </a>

                <a href="counsellors.php">
                    <i class="fa-solid fa-user-doctor"></i>
                    <span>Counsellors</span>
                </a>

                <a href="clients.php">
                    <i class="fa-solid fa-user-group"></i>
                    <span>Clients</span>
                </a>

                <a href="bookings.php">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>

                <a href="sessions.php">
                    <i class="fa-solid fa-video"></i>
                    <span>Sessions</span>
                </a>

            </div>


            <div class="sidebar-section">

                <span class="sidebar-section-title">
                    Management
                </span>

                <a href="resources.php">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Resources</span>
                </a>

                <a href="programs.php">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Programs</span>
                </a>

                <a href="schools.php">
                    <i class="fa-solid fa-school"></i>
                    <span>Schools</span>
                </a>

                <a href="organizations.php">
                    <i class="fa-solid fa-building"></i>
                    <span>Organizations</span>
                </a>

            </div>


            <div class="sidebar-section">

                <span class="sidebar-section-title">
                    Reports
                </span>

                <a href="reports.php">
                    <i class="fa-solid fa-chart-column"></i>
                    <span>Reports & Analytics</span>
                </a>

            </div>


            <div class="sidebar-section">

                <span class="sidebar-section-title">
                    System
                </span>

                <a href="notifications.php"  class="active">
                    <i class="fa-solid fa-bell"></i>
                    <span>Notifications</span>
                </a>

                <a href="settings.php">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>

                <a href="../pages/logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>

            </div>

        </nav>

    </aside>


    <!-- ================= MAIN ================= -->
    <main class="dashboard-main">

        <!-- Header -->
        <header class="dashboard-header">

            <div class="header-left">

                <button class="mobile-menu" id="mobileMenu">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="header-search">

                    <i class="fa-solid fa-search"></i>

                    <input
                        type="text"
                        id="headerSearch"
                        placeholder="Search notifications..."
                    >

                </div>

            </div>


            <div class="header-right">

                <button class="notification-btn">

                    <i class="fa-regular fa-bell"></i>

                    <span class="notification-badge">
                        4
                    </span>

                </button>


                <div class="admin-profile">

                    <div class="admin-avatar">
                        <?= h($meInitials) ?>
                    </div>

                    <div class="admin-profile-info">
                        <strong><?= h($meName) ?></strong>
                        <span>System Administrator</span>
                    </div>

                    <i class="fa-solid fa-chevron-down"></i>

                </div>

            </div>

        </header>


        <!-- ================= PAGE CONTENT ================= -->
        <section class="notifications-page" data-csrf="<?= h($csrf) ?>">
  <link rel="stylesheet" href="../assets/css/admin-extra.css">
  <?= flash_html() ?>
  <div class="page-header"><div><h1>Notifications</h1><p>Send announcements to platform users.</p></div></div>
  <div class="admin-box"><h2>Send a notification</h2>
    <form class="admin-form" method="post" action="notification-actions.php">
      <?= csrf_field() ?>
      <div class="grid">
        <div class="field"><label>Audience</label><select name="audience"><option value="all">Everyone</option><option value="clients">Clients</option><option value="counsellors">Counsellors</option><option value="admins">Admins</option></select></div>
        <div class="field"><label>Type</label><select name="type"><option value="announcement">Announcement</option><option value="system">System</option><option value="security">Security</option></select></div>
        <div class="field"><label>Title</label><input type="text" name="title" required maxlength="255"></div>
      </div>
      <div class="field" style="margin-top:12px"><label>Message</label><textarea name="message" rows="4" required></textarea></div>
      <p><button class="admin-btn" type="submit">Send</button></p>
    </form>
  </div>
  <div class="admin-box"><h2>Recent broadcasts</h2>
    <table class="admin-table"><thead><tr><th>Sent</th><th>Title</th><th>Audience</th><th>Type</th><th>Recipients</th></tr></thead><tbody>
      <?php foreach ($recent as $r): ?><tr><td><?= h(fmt_date($r["sent_at"])) ?> <?= h(fmt_time($r["sent_at"])) ?></td><td><?= h($r["title"]) ?></td><td><?= h(ucfirst($r["audience"])) ?></td><td><?= h(ucfirst($r["type"])) ?></td><td><?= (int)$r["recipients"] ?></td></tr><?php endforeach; ?>
      <?php if (!$recent): ?><tr><td colspan="5">No broadcasts yet.</td></tr><?php endif; ?>
    </tbody></table>
  </div>
</section>
</main>
</div>
<script src="../assets/js/app.js"></script>

</body>
</html>
