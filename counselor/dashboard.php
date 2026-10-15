<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("counsellor");
require_once __DIR__ . "/../includes/page_init.php";
$stats = $pdo->prepare("SELECT
  (SELECT COUNT(DISTINCT client_id) FROM bookings WHERE counselor_id = ?) AS clients,
  (SELECT COUNT(*) FROM bookings WHERE counselor_id = ? AND booking_date = CURDATE() AND status IN ('pending','confirmed')) AS today,
  (SELECT COUNT(*) FROM bookings WHERE counselor_id = ? AND status = 'completed') AS completed,
  (SELECT COUNT(*) FROM bookings WHERE counselor_id = ? AND status = 'pending') AS pending");
$stats->execute([$meId, $meId, $meId, $meId]);
$s = $stats->fetch();

$sch = $pdo->prepare("SELECT b.*, u.name AS client_name FROM bookings b INNER JOIN users u ON u.id = b.client_id
    WHERE b.counselor_id = ? AND b.booking_date = CURDATE() AND b.status IN ('pending','confirmed') ORDER BY b.start_time");
$sch->execute([$meId]);
$today = $sch->fetchAll();

$rc = $pdo->prepare("SELECT u.id, u.name, MAX(b.booking_date) AS last_date FROM bookings b
    INNER JOIN users u ON u.id = b.client_id WHERE b.counselor_id = ? GROUP BY u.id, u.name ORDER BY last_date DESC LIMIT 4");
$rc->execute([$meId]);
$recentClients = $rc->fetchAll();

$rm = $pdo->prepare("SELECT c.id, u.name,
      (SELECT message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message,
      (SELECT created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_at
    FROM conversations c INNER JOIN users u ON u.id = c.client_id WHERE c.counselor_id = ? ORDER BY last_at DESC LIMIT 4");
$rm->execute([$meId]);
$recentMsgs = $rm->fetchAll();
$hour = (int)date("G");
$greet = $hour < 12 ? "Good morning" : ($hour < 17 ? "Good afternoon" : "Good evening");
$cpst = $pdo->prepare("SELECT status FROM counselor_profiles WHERE user_id = ?");
$cpst->execute([$meId]);
$profileStatus = $cpst->fetchColumn() ?: "pending";
?>

<?php
$pageTitle = "Counsellor Dashboard";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf" content="<?= h($csrf) ?>">

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo $pageTitle; ?> - I Can Hear You Rwanda
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/dashboard.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/counselor-dashboard.css"
    >

</head>

<body>


<div class="dashboard-layout">


    <!-- =========================================
         SIDEBAR
    ========================================== -->

    <aside class="sidebar" id="sidebar">

    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="dashboard.php" class="logo">
            <span>
                <img src="../assets/images/log.jpeg" alt="I Can Hear You">
            </span>
            <strong>I Can Hear You Rwanda</strong>
        </a>
    </div>

    <!-- Main Menu -->
    <div class="sidebar-section">
        <span class="sidebar-section-title">Main Menu</span>

        <nav class="sidebar-nav">

            <a href="dashboard.php" class="active">
                <span>📊</span>
                <span>Dashboard</span>
            </a>

            <a href="clients.php">
                <span>👥</span>
                <span>My Clients</span>
            </a>

            <a href="bookings.php">
                <span>📅</span>
                <span>Bookings</span>
            </a>

            <a href="messages.php">
                <span>💬</span>
                <span>Messages</span>
                <?php if ($unreadMsgs > 0): ?><span class="nav-badge"><?= (int)$unreadMsgs ?></span><?php endif; ?>
            </a>

            <a href="sessions.php">
                <span>🎥</span>
                <span>Sessions</span>
            </a>

        </nav>
    </div>

    <!-- Management -->
    <div class="sidebar-section">
        <span class="sidebar-section-title">Management</span>

        <nav class="sidebar-nav">

            <a href="session-notes.php">
                <span>📝</span>
                <span>Session Notes</span>
            </a>

            <a href="resources.php">
                <span>📚</span>
                <span>Resources</span>
            </a>

        </nav>
    </div>

    <!-- Account -->
    <div class="sidebar-section">
        <span class="sidebar-section-title">Account</span>

        <nav class="sidebar-nav">

            <a href="profile.php">
                <span>👤</span>
                <span>My Profile</span>
            </a>

            <a href="notifications.php">
                <span>🔔</span>
                <span>Notifications</span>
            </a>

            <a href="settings.php">
                <span>⚙️</span>
                <span>Settings</span>
            </a>

        </nav>
    </div>

    <!-- Logout -->
    <div class="sidebar-bottom">
        <a href="../pages/logout.php">
    <i class="fas fa-right-from-bracket"></i>
    <span>Logout</span>
</a>
    </div>

</aside>



    <!-- =========================================
         MAIN CONTENT
    ========================================== -->

    <main class="dashboard-main">


        <!-- HEADER -->

        <header class="dashboard-header" style="margin-left: 250px;">


            <button
                class="mobile-menu"
                id="mobileMenu"
            >
                ☰
            </button>


            <div class="header-search">

                <span>
                    🔍
                </span>

                <input
                    type="text"
                    placeholder="Search clients, sessions..."
                >

            </div>


            <div class="header-actions">


                <button class="header-icon">

                    🔔

                    <span
                        class="header-notification-dot"
                    ></span>

                </button>


                <div class="header-profile">


                    <div class="user-avatar small">
                        <?= h($meInitials) ?>
                    </div>


                    <div>

                        <strong>
                            <?= h($meName) ?>
                        </strong>

                        <span>
                            Counsellor
                        </span>

                    </div>


                </div>


            </div>


        </header>



        <!-- =========================================
             PAGE CONTENT
        ========================================== -->

        <section class="counselor-dashboard" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="counselor-welcome">
    <div><h1><?= $greet ?>, <?= h($meName) ?> 👋</h1><p>Here's what's happening with your counselling practice today.</p></div>
    <div class="availability-status"><span class="status-dot"></span> <?= $profileStatus === "active" ? "Available for Sessions" : "Profile " . h($profileStatus) . " — awaiting admin approval" ?></div>
  </div>
  <div class="counselor-stats">
    <div class="counselor-stat-card"><div class="stat-icon">👥</div><div><span>Total Clients</span><strong><?= (int)$s["clients"] ?></strong></div></div>
    <div class="counselor-stat-card"><div class="stat-icon">📅</div><div><span>Today's Sessions</span><strong><?= (int)$s["today"] ?></strong></div></div>
    <div class="counselor-stat-card"><div class="stat-icon">💬</div><div><span>Unread Messages</span><strong><?= (int)$unreadMsgs ?></strong></div></div>
    <div class="counselor-stat-card"><div class="stat-icon">✓</div><div><span>Completed Sessions</span><strong><?= (int)$s["completed"] ?></strong></div></div>
  </div>
  <div class="counselor-dashboard-grid">
    <div class="dashboard-card schedule-card">
      <div class="card-header"><div><h2>Today's Schedule</h2><p><?= date("F j, Y") ?></p></div><a href="bookings.php">View All<?= $s["pending"] ? " (" . (int)$s["pending"] . " pending)" : "" ?></a></div>
      <div class="schedule-list">
        <?php foreach ($today as $b): $t = strtotime($b["start_time"]); ?>
        <div class="schedule-item">
          <div class="schedule-time"><strong><?= date("H:i", $t) ?></strong><span><?= date("A", $t) ?></span></div><div class="schedule-line"></div>
          <div class="schedule-info"><div class="client-avatar"><?= h(initials($b["client_name"])) ?></div><div><h3><?= h($b["client_name"]) ?></h3><p><?= h(ucfirst($b["status"])) ?></p></div></div>
          <div class="schedule-type <?= h($b["session_type"]) ?>"><?= $b["session_type"] === "video" ? "🎥 Video" : "💬 Chat" ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (!$today): ?><p class="profile-message">No sessions scheduled for today.</p><?php endif; ?>
      </div>
    </div>
    <div class="dashboard-card quick-actions-card">
      <div class="card-header"><div><h2>Quick Actions</h2><p>Common tasks</p></div></div>
      <div class="quick-actions">
        <a href="clients.php" class="quick-action"><span>👥</span><div><strong>View Clients</strong><small>Manage your clients</small></div></a>
        <a href="bookings.php" class="quick-action"><span>📅</span><div><strong>Manage Bookings</strong><small><?= (int)$s["pending"] ?> pending requests</small></div></a>
        <a href="messages.php" class="quick-action"><span>💬</span><div><strong>Messages</strong><small><?= (int)$unreadMsgs ?> unread messages</small></div></a>
        <a href="session-notes.php" class="quick-action"><span>📝</span><div><strong>Session Notes</strong><small>Record session progress</small></div></a>
      </div>
    </div>
    <div class="dashboard-card clients-card">
      <div class="card-header"><div><h2>Recent Clients</h2><p>Clients you've recently supported</p></div><a href="clients.php">View All</a></div>
      <div class="recent-clients">
        <?php foreach ($recentClients as $c): ?>
        <div class="recent-client"><div class="client-avatar"><?= h(initials($c["name"])) ?></div>
          <div class="client-info"><strong><?= h($c["name"]) ?></strong></div><span class="client-date"><?= h(fmt_date($c["last_date"])) ?></span></div>
        <?php endforeach; ?>
        <?php if (!$recentClients): ?><p class="profile-message">No clients yet.</p><?php endif; ?>
      </div>
    </div>
    <div class="dashboard-card messages-card">
      <div class="card-header"><div><h2>Recent Messages</h2><p>Latest conversations</p></div><a href="messages.php">View All</a></div>
      <div class="recent-messages">
        <?php foreach ($recentMsgs as $m): ?>
        <div class="recent-message"><div class="client-avatar"><?= h(initials($m["name"])) ?></div>
          <div class="message-preview"><div><strong><?= h($m["name"]) ?></strong><span><?= h(fmt_ago($m["last_at"])) ?></span></div>
            <p><?= h(mb_strimwidth($m["last_message"] ?? "No messages yet", 0, 60, "…")) ?></p></div></div>
        <?php endforeach; ?>
        <?php if (!$recentMsgs): ?><p class="profile-message">No conversations yet.</p><?php endif; ?>
      </div>
    </div>
  </div>
</section>



    </main>

</div>


<script src="../assets/js/dashboard.js"></script>

<script src="../assets/js/counselor-dashboard.js"></script>


<script src="../assets/js/app.js"></script>
</body>

</html>