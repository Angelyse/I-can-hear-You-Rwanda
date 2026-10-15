<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$st = $pdo->prepare("SELECT b.*, u.name AS counselor_name, cp.qualification, s.id AS session_id,
      s.meeting_link, s.status AS session_status, f.id AS feedback_id
    FROM bookings b INNER JOIN users u ON u.id = b.counselor_id
    LEFT JOIN counselor_profiles cp ON cp.user_id = b.counselor_id
    LEFT JOIN sessions s ON s.booking_id = b.id
    LEFT JOIN feedback f ON f.session_id = s.id AND f.client_id = b.client_id
    WHERE b.client_id = ? AND b.status IN ('confirmed','completed')
    ORDER BY b.booking_date DESC, b.start_time DESC");
$st->execute([$meId]);
$all = $st->fetchAll();
$upcoming = array_reverse(array_values(array_filter($all, fn($b) => $b["status"] === "confirmed")));
$previous = array_values(array_filter($all, fn($b) => $b["status"] === "completed"));
?>


<?php
// My Sessions page - frontend only
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Sessions - I Can Hear You</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/sessions.css">
</head>

<body>

<div class="dashboard-layout" style="margin-left: 250px;">

    <!-- =========================
         SIDEBAR
    ========================== -->

    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <a href="../index.php" class="logo">
                <span>
                    <img src="../assets/images/log.jpeg" alt="I Can Hear You">
                </span>
                <strong>I Can Hear You Rwanda</strong>
            </a>
        </div>

        <div class="user-mini-profile">
            <div class="user-avatar"><?= h($meInitials) ?></div>

            <div>
                <h4><?= h($meName) ?></h4>
                <span>Client</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <p class="nav-title">MAIN MENU</p>

            <a href="dashboard.php" class="sidebar-link">

                <span>🏠</span> Dashboard </a>

            <a href="find-counselor.php" class="sidebar-link">

                <span>🧑‍⚕️</span>Find Counsellor</a>

            <a href="bookings.php" class="sidebar-link"> <span>📅</span> My Bookings</a>

            <a href="messages.php"class="sidebar-link"> <span>💬</span>Messages
 <?php if ($unreadMsgs > 0): ?><span class="notification-count"><?= (int)$unreadMsgs ?></span><?php endif; ?></a>

            <a href="sessions.php" class="sidebar-link active"><span>🎥</span>My Sessions </a>

            <p class="nav-title">SUPPORT</p>

            <a href="resources.php" class="sidebar-link"><span>📚</span>Resources</a>

            <a href="profile.php" class="sidebar-link"><span>👤</span>My Profile</a>

            <a href="notifications.php" class="sidebar-link"> <span>🔔</span> Notifications</a>

            <a href="settings.php" class="sidebar-link"> <span>⚙️</span>Settings </a>

            <!-- LOGOUT -->

            <div class="sidebar-bottom">

                <a href="../pages/logout.php">

                    <span class="nav-icon">↪</span>

                    Logout

                </a>

            </div>

        </nav>

    </aside>


    <!-- =========================
         MAIN CONTENT
    ========================== -->

    <main class="dashboard-main">


        <!-- HEADER -->

        <header class="dashboard-header">

            <button
                class="mobile-menu"
                id="mobileMenu"
            >
                ☰
            </button>


            <div class="header-search">

                <span>🔍</span>

                <input
                    type="text"
                    placeholder="Search..."
                >

            </div>


            <div class="header-actions">

                <button class="notification-btn">

                    🔔

                    <span></span>

                </button>


                <div class="header-profile">

                    <div class="header-avatar">
                        <?= h($meInitials) ?>
                    </div>

                    <div>

                        <strong><?= h($meName) ?></strong>

                        <small>Client</small>

                    </div>

                </div>

            </div>

        </header>


        <!-- =========================
             SESSIONS PAGE
        ========================== -->

        <section class="sessions-page" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="sessions-heading"><div><h1>My Sessions</h1><p>View your upcoming and previous counselling sessions.</p></div>
    <a href="find-counselor.php" class="book-session-btn">+ Book a Session</a></div>
  <div class="session-tabs">
    <button class="session-tab active" data-status="all">All Sessions</button>
    <button class="session-tab" data-status="upcoming">Upcoming</button>
    <button class="session-tab" data-status="completed">Completed</button>
  </div>

  <div class="session-section"><h2>Upcoming Sessions</h2>
    <?php foreach ($upcoming as $b): $t = strtotime($b["booking_date"]); ?>
    <div class="session-card upcoming" data-status="upcoming">
      <div class="session-date"><span class="month"><?= strtoupper(date("M", $t)) ?></span><strong><?= date("d", $t) ?></strong><span><?= date("Y", $t) ?></span></div>
      <div class="session-main">
        <div class="session-person"><div class="session-avatar"><?= h(initials($b["counselor_name"])) ?></div><div><h3><?= h($b["counselor_name"]) ?></h3><p><?= h($b["qualification"]) ?></p></div></div>
        <div class="session-info">
          <div><span>🕐 Time</span><strong><?= h(fmt_time($b["start_time"])) ?> - <?= h(fmt_time($b["end_time"])) ?></strong></div>
          <div><span><?= $b["session_type"] === "video" ? "🎥" : "💬" ?> Type</span><strong><?= $b["session_type"] === "video" ? "Video Session" : "Chat Session" ?></strong></div>
          <div><span>● Status</span><strong class="session-status confirmed">Confirmed</strong></div>
        </div>
        <div class="session-actions">
          <?php if ($b["session_type"] === "video" && $b["meeting_link"]): ?>
            <a class="join-session-btn" href="<?= h($b["meeting_link"]) ?>" target="_blank" rel="noopener noreferrer">🎥 Join Session</a>
          <?php else: ?>
            <a class="join-session-btn" href="messages.php?with=<?= (int)$b["counselor_id"] ?>">💬 Open Chat</a>
          <?php endif; ?>
          <a href="messages.php?with=<?= (int)$b["counselor_id"] ?>" class="message-session-btn">💬 Message</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$upcoming): ?><p class="profile-message">No upcoming sessions.</p><?php endif; ?>
  </div>

  <div class="session-section"><div class="section-title-row"><h2>Previous Sessions</h2><span><?= count($previous) ?> sessions</span></div>
    <?php foreach ($previous as $b): $t = strtotime($b["booking_date"]); ?>
    <div class="session-card completed" data-status="completed">
      <div class="session-date"><span class="month"><?= strtoupper(date("M", $t)) ?></span><strong><?= date("d", $t) ?></strong><span><?= date("Y", $t) ?></span></div>
      <div class="session-main">
        <div class="session-person"><div class="session-avatar"><?= h(initials($b["counselor_name"])) ?></div><div><h3><?= h($b["counselor_name"]) ?></h3><p><?= h($b["qualification"]) ?></p></div></div>
        <div class="session-info">
          <div><span>🕐 Time</span><strong><?= h(fmt_time($b["start_time"])) ?> - <?= h(fmt_time($b["end_time"])) ?></strong></div>
          <div><span><?= $b["session_type"] === "video" ? "🎥" : "💬" ?> Type</span><strong><?= $b["session_type"] === "video" ? "Video Session" : "Chat Session" ?></strong></div>
          <div><span>● Status</span><strong class="session-status completed-status">Completed</strong></div>
        </div>
        <div class="session-actions">
          <?php if ($b["session_id"] && !$b["feedback_id"]): ?>
            <button class="feedback-btn" data-session="<?= (int)$b["session_id"] ?>">⭐ Give Feedback</button>
          <?php elseif ($b["feedback_id"]): ?><span class="session-status completed-status">Feedback sent ✓</span><?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="no-sessions" id="noSessions"><div class="no-sessions-icon">🎥</div><h2>No sessions found</h2><p>You don't have any sessions in this category.</p><a href="find-counselor.php">Book a Session</a></div>
</section>


    </main>

</div>


<script src="../assets/js/sessions.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>