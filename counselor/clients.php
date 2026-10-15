<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("counsellor");
require_once __DIR__ . "/../includes/page_init.php";
$st = $pdo->prepare("SELECT u.id, u.name,
      MIN(b.created_at) AS first_contact,
      MAX(CASE WHEN b.status = 'completed' THEN b.booking_date END) AS last_session,
      MIN(CASE WHEN b.status IN ('pending','confirmed') AND b.booking_date >= CURDATE() THEN b.booking_date END) AS next_session,
      SUM(b.status = 'completed') AS done,
      (SELECT b2.reason FROM bookings b2 WHERE b2.client_id = u.id AND b2.counselor_id = ? ORDER BY b2.id DESC LIMIT 1) AS concern
    FROM bookings b INNER JOIN users u ON u.id = b.client_id
    WHERE b.counselor_id = ? GROUP BY u.id, u.name ORDER BY u.name");
$st->execute([$meId, $meId]);
$clients = $st->fetchAll();
foreach ($clients as &$c) {
    if (!$c["next_session"] && (int)$c["done"] > 0) $c["state"] = "completed";
    elseif ((int)$c["done"] === 0 && strtotime($c["first_contact"]) > strtotime("-14 days")) $c["state"] = "new";
    else $c["state"] = "active";
}
unset($c);
$n = ["all" => count($clients), "active" => 0, "new" => 0, "completed" => 0];
foreach ($clients as $c) $n[$c["state"]]++;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Clients - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor-clients.css">
</head>

<body>

<!-- ================= SIDEBAR ================= -->

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

            <a href="dashboard.php">
                <span>📊</span>
                <span>Dashboard</span>
            </a>

            <a href="clients.php"  class="active">
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

        <a href="../pages/logout.php" class="logout-link">
            <span>🚪</span>
            <span>Logout</span>
        </a>

    </div>

</aside>


<!-- ================= MAIN CONTENT ================= -->

<main class="dashboard-main">

    <!-- Header -->

    <header class="dashboard-header" style="margin-left: 250px;">

        <button class="mobile-menu" id="mobileMenu">
            ☰
        </button>

        <div class="header-search">

            <span>🔍</span>

            <input
                type="text"
                id="globalSearch"
                placeholder="Search clients, sessions..."
            >

        </div>

        <div class="header-right" >

            <button class="notification-btn">
                🔔
                <span class="notification-dot"></span>
            </button>

            <div class="header-profile">

                <div class="profile-avatar">
                    <?= h($meInitials) ?>
                </div>

                <div>
                    <strong>Dr. Jean Mukamana</strong>
                    <small>Counsellor</small>
                </div>

            </div>

        </div>

    </header>


    <!-- ================= PAGE ================= -->

    <section class="clients-page" style="margin-left: 250px;" data-csrf="<?= h($csrf) ?>">
  <div class="page-header"><div><h1>My Clients</h1><p>Clients who have booked sessions with you.</p></div></div>
  <div class="client-stats">
    <div class="client-stat-card"><div class="stat-icon">👥</div><div><span>Total Clients</span><strong><?= $n["all"] ?></strong></div></div>
    <div class="client-stat-card"><div class="stat-icon active-icon">🟢</div><div><span>Active Clients</span><strong><?= $n["active"] ?></strong></div></div>
    <div class="client-stat-card"><div class="stat-icon new-icon">✨</div><div><span>New Clients</span><strong><?= $n["new"] ?></strong></div></div>
    <div class="client-stat-card"><div class="stat-icon completed-icon">✓</div><div><span>Completed</span><strong><?= $n["completed"] ?></strong></div></div>
  </div>
  <div class="clients-toolbar">
    <div class="client-search"><span>🔍</span><input type="text" id="clientSearch" placeholder="Search by client name..."></div>
    <div class="filter-group"><select id="statusFilter"><option value="all">All Status</option><option value="active">Active</option><option value="new">New</option><option value="completed">Completed</option></select></div>
  </div>
  <div class="client-tabs">
    <?php foreach (["all" => "All Clients", "active" => "Active", "new" => "New", "completed" => "Completed"] as $k => $l): ?>
      <button class="client-tab <?= $k === "all" ? "active" : "" ?>" data-filter="<?= $k ?>"><?= $l ?> <span><?= $n[$k] ?></span></button>
    <?php endforeach; ?>
  </div>
  <div class="clients-container">
    <?php foreach ($clients as $c): ?>
    <div class="client-card" data-status="<?= h($c["state"]) ?>" data-concern="all" data-name="<?= h($c["name"]) ?>">
      <div class="client-avatar"><?= h(initials($c["name"])) ?></div>
      <div class="client-info">
        <div class="client-name-row"><h3><?= h($c["name"]) ?></h3><span class="status <?= h($c["state"]) ?>"><?= h(ucfirst($c["state"])) ?></span></div>
        <p class="client-concern"><?= h(mb_strimwidth((string)$c["concern"], 0, 80, "…")) ?></p>
        <div class="client-details">
          <span>📅 <?= $c["last_session"] ? "Last session: " . h(fmt_date($c["last_session"])) : "First contact: " . h(fmt_date($c["first_contact"])) ?></span>
          <span>⏰ Next: <?= $c["next_session"] ? h(fmt_date($c["next_session"])) : "Not scheduled" ?></span>
        </div>
      </div>
      <div class="client-actions"><a href="messages.php?with=<?= (int)$c["id"] ?>" class="message-btn">💬 Message</a><a href="session-notes.php?client=<?= (int)$c["id"] ?>" class="notes-btn">📝 Notes</a></div>
    </div>
    <?php endforeach; ?>
    <?php if (!$clients): ?><p class="profile-message">No clients yet. Clients appear here after they book with you.</p><?php endif; ?>
  </div>
</section>


</main>


<script src="../assets/js/dashboard.js"></script>
<script src="../assets/js/counselor-lists.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>