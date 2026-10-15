<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("counsellor");
require_once __DIR__ . "/../includes/page_init.php";
$st = $pdo->prepare("SELECT b.*, u.name AS client_name, s.id AS session_id, s.meeting_link, s.status AS session_status
    FROM bookings b INNER JOIN users u ON u.id = b.client_id LEFT JOIN sessions s ON s.booking_id = b.id
    WHERE b.counselor_id = ? ORDER BY b.booking_date DESC, b.start_time DESC");
$st->execute([$meId]);
$rows = $st->fetchAll();
$cnt = ["all" => count($rows), "pending" => 0, "confirmed" => 0, "completed" => 0, "cancelled" => 0];
foreach ($rows as $r) {
    $k = $r["status"] === "declined" ? "cancelled" : $r["status"];
    $cnt[$k] = ($cnt[$k] ?? 0) + 1;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bookings - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor-bookings.css">
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

            <a href="clients.php">
                <span>👥</span>
                <span>My Clients</span>
            </a>

            <a href="bookings.php"  class="active">
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

    <header class="dashboard-header">

        <button class="mobile-menu" id="mobileMenu">
            ☰
        </button>


        <div class="header-search">

            <span>🔍</span>

            <input
                type="text"
                placeholder="Search clients, sessions..."
            >

        </div>


        <div class="header-right">

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

    <section class="bookings-page" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="page-header"><div><h1>Bookings</h1><p>Manage your client appointments and upcoming sessions.</p></div></div>
  <div class="booking-stats">
    <div class="booking-stat-card"><div class="booking-stat-icon">📅</div><div><span>Total Bookings</span><strong><?= $cnt["all"] ?></strong></div></div>
    <div class="booking-stat-card"><div class="booking-stat-icon pending-icon">⏳</div><div><span>Pending</span><strong><?= $cnt["pending"] ?></strong></div></div>
    <div class="booking-stat-card"><div class="booking-stat-icon confirmed-icon">✓</div><div><span>Confirmed</span><strong><?= $cnt["confirmed"] ?></strong></div></div>
    <div class="booking-stat-card"><div class="booking-stat-icon completed-icon">✓</div><div><span>Completed</span><strong><?= $cnt["completed"] ?></strong></div></div>
  </div>
  <div class="booking-toolbar">
    <div class="booking-search"><span>🔍</span><input type="text" id="bookingSearch" placeholder="Search by client name..."></div>
    <div class="booking-filters">
      <select id="statusFilter"><option value="all">All Status</option><option value="pending">Pending</option><option value="confirmed">Confirmed</option><option value="completed">Completed</option><option value="cancelled">Cancelled</option></select>
      <select id="sessionTypeFilter"><option value="all">All Session Types</option><option value="video">Video Call</option><option value="chat">Chat</option></select>
      <input type="date" id="dateFilter">
    </div>
  </div>
  <div class="booking-tabs">
    <?php foreach (["all" => "All", "pending" => "Pending", "confirmed" => "Confirmed", "completed" => "Completed", "cancelled" => "Cancelled"] as $k => $l): ?>
      <button class="booking-tab <?= $k === "all" ? "active" : "" ?>" data-filter="<?= $k ?>"><?= $l ?> <span><?= (int)$cnt[$k] ?></span></button>
    <?php endforeach; ?>
  </div>
  <div class="bookings-container">
    <?php foreach ($rows as $b):
      $t = strtotime($b["booking_date"]);
      $status = $b["status"] === "declined" ? "cancelled" : $b["status"];
      $due = $b["booking_date"] <= date("Y-m-d"); ?>
    <div class="booking-card" data-status="<?= h($status) ?>" data-type="<?= h($b["session_type"]) ?>" data-date="<?= h($b["booking_date"]) ?>" data-name="<?= h($b["client_name"]) ?>">
      <div class="booking-date"><span><?= strtoupper(date("M", $t)) ?></span><strong><?= date("d", $t) ?></strong><small><?= date("Y", $t) ?></small></div>
      <div class="booking-info">
        <div class="booking-title"><h3><?= h($b["client_name"]) ?></h3><span class="booking-status <?= h($status) ?>"><?= h(ucfirst($b["status"])) ?></span></div>
        <p><?= h(mb_strimwidth((string)$b["reason"], 0, 90, "…")) ?></p>
        <div class="booking-details"><span>🕙 <?= h(fmt_time($b["start_time"])) ?> - <?= h(fmt_time($b["end_time"])) ?></span><span><?= $b["session_type"] === "video" ? "🎥 Video Call" : "💬 Chat Session" ?></span></div>
      </div>
      <div class="booking-actions">
        <?php if ($b["status"] === "pending"): ?>
          <button class="approve-btn" data-confirm-booking="<?= (int)$b["id"] ?>">Approve</button>
          <button class="decline-btn" data-decline-booking="<?= (int)$b["id"] ?>">Decline</button>
        <?php elseif ($b["status"] === "confirmed" && $b["session_id"]): ?>
          <?php if ($b["session_type"] === "video"): ?><button class="join-btn" data-start-session="<?= (int)$b["session_id"] ?>">Start & Join</button>
          <?php else: ?><a class="join-btn" href="messages.php?with=<?= (int)$b["client_id"] ?>">Open Chat</a><?php endif; ?>
          <?php if ($due): ?><button class="view-btn" data-complete-session="<?= (int)$b["session_id"] ?>">Mark Completed</button><?php endif; ?>
          <button class="decline-btn" data-cancel="<?= (int)$b["id"] ?>">Cancel</button>
        <?php elseif ($b["status"] === "completed" && $b["session_id"]): ?>
          <a class="view-btn" href="session-notes.php?session=<?= (int)$b["session_id"] ?>">📝 Notes</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><p class="profile-message">No bookings yet.</p><?php endif; ?>
  </div>
</section>


</main>


<script src="../assets/js/dashboard.js"></script>
<script src="../assets/js/counselor-lists.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>