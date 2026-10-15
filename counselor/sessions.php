<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("counsellor");
require_once __DIR__ . "/../includes/page_init.php";
$st = $pdo->prepare("SELECT b.*, u.name AS client_name, s.id AS session_id, s.status AS session_status
    FROM bookings b INNER JOIN users u ON u.id = b.client_id INNER JOIN sessions s ON s.booking_id = b.id
    WHERE b.counselor_id = ? ORDER BY b.booking_date DESC, b.start_time DESC");
$st->execute([$meId]);
$rows = $st->fetchAll();
function sess_group(array $r): string {
    if ($r["session_status"] === "cancelled" || $r["status"] === "cancelled") return "cancelled";
    return $r["session_status"] === "completed" ? "completed" : "upcoming";
}
$c = ["all" => count($rows), "upcoming" => 0, "completed" => 0, "cancelled" => 0, "today" => 0, "month" => 0];
foreach ($rows as $r) {
    $c[sess_group($r)]++;
    if ($r["booking_date"] === date("Y-m-d")) $c["today"]++;
    if (substr($r["booking_date"], 0, 7) === date("Y-m")) $c["month"]++;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sessions | I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor-sessions.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<div class="dashboard-layout">

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar" id="sidebar">

        <!-- Logo -->
        <div class="sidebar-logo">
            <a href="dashboard.php" class="logo">
                <span>
                    <img src="../assets/images/log.jpeg" alt="I Can Hear You">
                </span>
                <div>
                    <strong>I Can Hear You</strong>
                    <small>Rwanda</small>
                </div>
            </a>
        </div>

        <!-- Main Menu -->
        <div class="sidebar-section">

            <span class="sidebar-section-title">Main Menu</span>

            <nav class="sidebar-nav">

                <a href="dashboard.php">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>

                <a href="clients.php">
                    <i class="fa-solid fa-users"></i>
                    <span>My Clients</span>
                </a>

                <a href="bookings.php">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>

                <a href="messages.php">
                    <i class="fa-solid fa-comments"></i>
                    <span>Messages</span>
                    <?php if ($unreadMsgs > 0): ?><span class="nav-badge"><?= (int)$unreadMsgs ?></span><?php endif; ?>
                </a>

                <a href="sessions.php"  class="active">
                    <i class="fa-solid fa-video"></i>
                    <span>Sessions</span>
                </a>

            </nav>

        </div>

        <!-- Management -->
        <div class="sidebar-section">

            <span class="sidebar-section-title">Management</span>

            <nav class="sidebar-nav">

                <a href="session-notes.php">
                    <i class="fa-solid fa-note-sticky"></i>
                    <span>Session Notes</span>
                </a>

                <a href="resources.php">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Resources</span>
                </a>

            </nav>

        </div>

        <!-- Account -->
        <div class="sidebar-section">

            <span class="sidebar-section-title">Account</span>

            <nav class="sidebar-nav">

                <a href="profile.php">
                    <i class="fa-solid fa-user"></i>
                    <span>My Profile</span>
                </a>

                <a href="notifications.php">
                    <i class="fa-solid fa-bell"></i>
                    <span>Notifications</span>
                </a>

                <a href="settings.php">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>

                <a href="../pages/logout.php" class="logout-link">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>

            </nav>

        </div>

    </aside>


    <!-- ================= MAIN CONTENT ================= -->
    <main class="dashboard-main">

        <!-- Top Header -->
        <header class="dashboard-header" style="margin-left: 250px;">

            <button class="mobile-menu" id="mobileMenu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="header-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="globalSearch"
                    placeholder="Search sessions..."
                >

            </div>

            <div class="header-actions">

                <button class="header-icon notification-btn">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notification-dot"></span>
                </button>

                <div class="header-profile">

                    <div class="header-avatar">
                        <?= h($meInitials) ?>
                    </div>

                    <div class="header-user-info">
                        <strong>Dr. Jean Mukamana</strong>
                        <span>Counsellor</span>
                    </div>

                    <i class="fa-solid fa-chevron-down"></i>

                </div>

            </div>

        </header>


        <!-- Page Content -->
        <div class="sessions-page" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="page-heading"><div><h1>Sessions</h1><p>Manage your upcoming and completed counselling sessions.</p></div></div>
  <div class="session-stats">
    <div class="session-stat-card"><div class="stat-icon today"><i class="fa-solid fa-calendar-day"></i></div><div><span>Today's Sessions</span><strong><?= $c["today"] ?></strong></div></div>
    <div class="session-stat-card"><div class="stat-icon upcoming"><i class="fa-solid fa-clock"></i></div><div><span>Upcoming</span><strong><?= $c["upcoming"] ?></strong></div></div>
    <div class="session-stat-card"><div class="stat-icon completed"><i class="fa-solid fa-circle-check"></i></div><div><span>Completed</span><strong><?= $c["completed"] ?></strong></div></div>
    <div class="session-stat-card"><div class="stat-icon month"><i class="fa-solid fa-chart-column"></i></div><div><span>This Month</span><strong><?= $c["month"] ?></strong></div></div>
  </div>
  <div class="session-controls">
    <div class="session-search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="sessionSearch" placeholder="Search by client name..."></div>
    <select id="statusFilter"><option value="all">All Statuses</option><option value="upcoming">Upcoming</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select>
    <select id="typeFilter"><option value="all">All Session Types</option><option value="video">Video</option><option value="chat">Chat</option></select>
    <input type="date" id="dateFilter">
  </div>
  <div class="session-tabs">
    <?php foreach (["all" => "All", "upcoming" => "Upcoming", "completed" => "Completed", "cancelled" => "Cancelled"] as $k => $l): ?>
      <button class="session-tab <?= $k === "all" ? "active" : "" ?>" data-tab="<?= $k ?>"><?= $l ?> <span><?= $c[$k] ?></span></button><?php endforeach; ?>
  </div>
  <div class="sessions-container" id="sessionsContainer">
    <?php foreach ($rows as $r): $g = sess_group($r); $t = strtotime($r["booking_date"]); $due = $r["booking_date"] <= date("Y-m-d"); ?>
    <div class="session-card" data-status="<?= $g ?>" data-type="<?= h($r["session_type"]) ?>" data-date="<?= h($r["booking_date"]) ?>" data-client="<?= h(mb_strtolower($r["client_name"])) ?>">
      <div class="session-date"><span class="date-day"><?= date("d", $t) ?></span><span class="date-month"><?= strtoupper(date("M", $t)) ?></span></div>
      <div class="session-client"><div class="client-avatar"><?= h(initials($r["client_name"])) ?></div><div><h3><?= h($r["client_name"]) ?></h3><p><?= h(mb_strimwidth((string)$r["reason"], 0, 60, "…")) ?></p></div></div>
      <div class="session-time"><strong><?= h(fmt_time($r["start_time"])) ?> - <?= h(fmt_time($r["end_time"])) ?></strong><span><?= $r["session_type"] === "video" ? "Video Session" : "Chat Session" ?></span></div>
      <div class="session-status"><span class="status-badge <?= $g ?>-badge"><?= ucfirst($g === "upcoming" ? ($r["session_status"] === "active" ? "In progress" : "Confirmed") : $g) ?></span></div>
      <div class="session-actions">
        <?php if ($g === "upcoming"): ?>
          <?php if ($r["session_type"] === "video"): ?><button class="action-btn primary" data-start-session="<?= (int)$r["session_id"] ?>">Start Session</button>
          <?php else: ?><a class="action-btn primary" href="messages.php?with=<?= (int)$r["client_id"] ?>">Open Chat</a><?php endif; ?>
          <?php if ($due): ?><button class="action-btn" data-complete-session="<?= (int)$r["session_id"] ?>">Mark Completed</button><?php endif; ?>
        <?php elseif ($g === "completed"): ?><a class="action-btn" href="session-notes.php?session=<?= (int)$r["session_id"] ?>">Session Notes</a><?php endif; ?>
        <a class="action-btn" href="messages.php?with=<?= (int)$r["client_id"] ?>" title="Message"><i class="fa-regular fa-comment"></i></a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="empty-sessions" id="emptySessions" <?= $rows ? "" : 'style="display:block"' ?>><h3>No sessions found</h3><p>No sessions match your current search or filters.</p></div>
</div>
</main>

</div>


<script src="../assets/js/counselor-lists.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>