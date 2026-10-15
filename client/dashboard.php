<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$stmt = $pdo->prepare("SELECT b.*, u.name AS counselor_name, cp.qualification, s.meeting_link
    FROM bookings b
    INNER JOIN users u ON u.id = b.counselor_id
    LEFT JOIN counselor_profiles cp ON cp.user_id = b.counselor_id
    LEFT JOIN sessions s ON s.booking_id = b.id
    WHERE b.client_id = ? AND b.status IN ('pending','confirmed')
      AND TIMESTAMP(b.booking_date, b.start_time) >= NOW()
    ORDER BY b.booking_date, b.start_time LIMIT 1");
$stmt->execute([$meId]);
$next = $stmt->fetch();

$q = $pdo->prepare("SELECT
    (SELECT COUNT(*) FROM bookings WHERE client_id = ? AND status IN ('pending','confirmed')
        AND TIMESTAMP(booking_date, start_time) >= NOW()) AS upcoming,
    (SELECT COUNT(*) FROM bookings WHERE client_id = ? AND status = 'completed') AS completed,
    (SELECT COUNT(*) FROM saved_resources WHERE user_id = ?) AS saved");
$q->execute([$meId, $meId, $meId]);
$counts = $q->fetch();

$cp = $pdo->prepare("SELECT * FROM client_profiles WHERE user_id = ?");
$cp->execute([$meId]);
$profile = $cp->fetch() ?: [];
$checks = [!empty($me["phone"]), !empty($profile["preferred_language"]), !empty($profile["display_name"]),
           !empty($profile["wellness_information"]), ($profile["consent_status"] ?? "") === "accepted"];
$pct = (int)round(array_sum(array_map("intval", $checks)) / count($checks) * 100);

$recommended = $pdo->query("SELECT u.id, u.name, cp.specialization,
      (SELECT COUNT(*) FROM bookings b WHERE b.counselor_id = u.id AND b.booking_date = CURDATE()
         AND b.status IN ('pending','confirmed')) AS today_load
    FROM users u INNER JOIN roles r ON r.id = u.role_id
    INNER JOIN counselor_profiles cp ON cp.user_id = u.id
    WHERE r.name = 'counsellor' AND u.status = 'active' AND cp.status = 'active'
    ORDER BY cp.years_experience DESC LIMIT 3")->fetchAll();

$recent = $pdo->prepare("SELECT c.id, u.name,
      (SELECT message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message,
      (SELECT created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_at,
      (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.receiver_id = ? AND m.read_at IS NULL) AS unread
    FROM conversations c INNER JOIN users u ON u.id = c.counselor_id
    WHERE c.client_id = ? ORDER BY last_at DESC LIMIT 3");
$recent->execute([$meId, $meId]);
$recent = $recent->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf" content="<?= h($csrf) ?>">

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Client Dashboard | I Can Hear You Rwanda</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <link rel="stylesheet"
          href="../assets/css/dashboard.css">

</head>

<body>

<!-- =====================================================
     DASHBOARD
===================================================== -->

<div class="dashboard">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <aside class="sidebar" id="sidebar">

        <!-- LOGO -->

        <div class="sidebar-logo">

            <a href="../index.php">

                <span class="logo-image">

                    <img
                        src="../assets/images/log.jpeg"
                        alt="I Can Hear You Logo">

                </span>

                <span>
                    I Can Hear You Rwanda
                </span>

            </a>

        </div>


        <!-- USER -->

        <div class="sidebar-user">

            <div class="user-avatar">
                <?= h($meInitials) ?>
            </div>

            <div>

                <strong>
                    <?= h($meName) ?>
                </strong>

                <small>
                    Client
                </small>

            </div>

        </div>


        <!-- NAVIGATION -->

        <nav class="sidebar-nav">

            <p class="nav-title">MAIN MENU</p>

            <a href="dashboard.php" class="sidebar-link active">

                <span>🏠</span> Dashboard </a>

            <a href="find-counselor.php" class="sidebar-link">

                <span>🧑‍⚕️</span>Find Counsellor</a>

            <a href="bookings.php" class="sidebar-link"> <span>📅</span> My Bookings</a>

            <a href="messages.php"class="sidebar-link"> <span>💬</span>Messages
 <?php if ($unreadMsgs > 0): ?><span class="notification-count"><?= (int)$unreadMsgs ?></span><?php endif; ?></a>

            <a href="sessions.php" class="sidebar-link"><span>🎥</span>My Sessions </a>

            <p class="nav-title">SUPPORT</p>

            <a href="resources.php" class="sidebar-link"><span>📚</span>Resources</a>

            <a href="profile.php" class="sidebar-link"><span>👤</span>My Profile</a>

            <a href="notifications.php" class="sidebar-link"> <span>🔔</span> Notifications</a>

            <a href="settings.php" class="sidebar-link"> <span>⚙️</span>Settings </a>

        </nav>


        <!-- SIDEBAR BOTTOM -->

        <div class="sidebar-bottom">

            <a href="../pages/logout.php">
    <i class="fas fa-right-from-bracket"></i>
    <span>Logout</span>
</a>

        </div>

    </aside>



    <!-- =================================================
         MAIN CONTENT
    ================================================== -->

    <main class="dashboard-main">


        <!-- TOP BAR -->

        <header class="dashboard-header">

            <button
                class="mobile-menu"
                id="mobileMenu">

                ☰

            </button>


            <div class="header-search">

                <span>🔍</span>

                <input
                    type="text"
                    placeholder="Search counsellors, resources...">

            </div>


            <div class="header-actions">


                <!-- NOTIFICATION -->

                <button class="icon-button">

                    🔔

                    <span class="notification-dot"></span>

                </button>


                <!-- PROFILE -->

                <div class="header-profile">

                    <div class="header-avatar">
                        <?= h($meInitials) ?>
                    </div>

                    <div>

                        <strong>
                            <?= h($meName) ?>
                        </strong>

                        <small>
                            Client
                        </small>

                    </div>

                </div>

            </div>

        </header>


        <!-- =================================================
             CONTENT
        ================================================== -->

        <section class="dashboard-content">
  <?= flash_html() ?>
  <div class="welcome-section">
    <div>
      <p class="welcome-small"><?= date("l, F j, Y") ?></p>
      <h1>Hello, <?= h(explode(" ", $meName)[0]) ?> 👋</h1>
      <p>How are you feeling today? We're here to support you.</p>
    </div>
    <a href="find-counselor.php" class="btn btn-primary">+ Book a Session</a>
  </div>

  <div class="stats-grid">
    <div class="stat-card"><div class="stat-icon blue">📅</div><div><span>Upcoming Sessions</span><strong><?= (int)$counts["upcoming"] ?></strong></div></div>
    <div class="stat-card"><div class="stat-icon green">💬</div><div><span>Unread Messages</span><strong><?= (int)$unreadMsgs ?></strong></div></div>
    <div class="stat-card"><div class="stat-icon purple">🎥</div><div><span>Completed Sessions</span><strong><?= (int)$counts["completed"] ?></strong></div></div>
    <div class="stat-card"><div class="stat-icon orange">📚</div><div><span>Saved Resources</span><strong><?= (int)$counts["saved"] ?></strong></div></div>
  </div>

  <div class="dashboard-grid">
    <div class="dashboard-card upcoming-card">
      <div class="card-header"><div><h2>Upcoming Session</h2><p>Your next counselling appointment</p></div><a href="bookings.php">View All</a></div>
      <?php if ($next): ?>
        <div class="session-info">
          <div class="counselor-avatar"><?= h(initials($next["counselor_name"])) ?></div>
          <div class="session-details">
            <h3><?= h($next["counselor_name"]) ?></h3>
            <p><?= h($next["qualification"] ?? "Counsellor") ?></p>
            <div class="session-meta"><span>📅 <?= h(fmt_date($next["booking_date"])) ?></span><span>🕐 <?= h(fmt_time($next["start_time"])) ?></span></div>
          </div>
          <span class="status-badge <?= h($next["status"]) ?>"><?= h(ucfirst($next["status"])) ?></span>
        </div>
        <div class="session-actions">
          <a href="sessions.php" class="btn btn-primary">Join Session</a>
          <a href="messages.php?with=<?= (int)$next["counselor_id"] ?>" class="btn btn-light">Message</a>
        </div>
      <?php else: ?>
        <p class="profile-message">You have no upcoming sessions.</p>
        <a href="find-counselor.php" class="text-link">Find a counsellor →</a>
      <?php endif; ?>
    </div>

    <div class="dashboard-card">
      <div class="card-header"><div><h2>Your Profile</h2><p>Complete your profile</p></div><span class="profile-percentage"><?= $pct ?>%</span></div>
      <div class="progress-container"><div class="progress-bar"><div class="progress" style="width: <?= $pct ?>%;"></div></div></div>
      <p class="profile-message"><?= $pct >= 100 ? "Your profile is complete. Thank you!" : "Add your phone, language and wellness information to finish your profile." ?></p>
      <a href="profile.php" class="text-link">Complete Profile →</a>
    </div>
  </div>

  <div class="dashboard-grid lower-grid">
    <div class="dashboard-card">
      <div class="card-header"><div><h2>Recommended Counsellors</h2><p>Professionals who may help you</p></div><a href="find-counselor.php">View All</a></div>
      <div class="counselor-list">
        <?php foreach ($recommended as $c): $busy = (int)$c["today_load"] >= count(booking_slots()); ?>
          <div class="counselor-item">
            <div class="counselor-avatar"><?= h(initials($c["name"])) ?></div>
            <div class="counselor-info"><h3><?= h($c["name"]) ?></h3><p><?= h($c["specialization"]) ?></p>
              <span class="<?= $busy ? "unavailable" : "available" ?>">● <?= $busy ? "Busy" : "Available" ?></span></div>
            <a href="counselor-profile.php?id=<?= (int)$c["id"] ?>" class="small-btn">View</a>
          </div>
        <?php endforeach; ?>
        <?php if (!$recommended): ?><p class="profile-message">No counsellors available yet.</p><?php endif; ?>
      </div>
    </div>

    <div class="dashboard-card">
      <div class="card-header"><div><h2>Recent Messages</h2><p>Your latest conversations</p></div><a href="messages.php">View All</a></div>
      <div class="message-list">
        <?php foreach ($recent as $m): ?>
          <a href="messages.php?c=<?= (int)$m["id"] ?>" class="message-item">
            <div class="counselor-avatar"><?= h(initials($m["name"])) ?></div>
            <div class="message-content"><div><strong><?= h($m["name"]) ?></strong><small><?= h(fmt_ago($m["last_at"])) ?></small></div>
              <p><?= h(mb_strimwidth($m["last_message"] ?? "No messages yet", 0, 40, "…")) ?></p></div>
            <?php if ((int)$m["unread"] > 0): ?><span class="unread"><?= (int)$m["unread"] ?></span><?php endif; ?>
          </a>
        <?php endforeach; ?>
        <?php if (!$recent): ?><p class="profile-message">No conversations yet.</p><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="quick-section"><h2>Quick Actions</h2>
    <div class="quick-grid">
      <a href="find-counselor.php" class="quick-card"><span>🧑‍⚕️</span><div><strong>Find a Counsellor</strong><small>Browse professionals</small></div></a>
      <a href="bookings.php" class="quick-card"><span>📅</span><div><strong>My Bookings</strong><small>Manage appointments</small></div></a>
      <a href="messages.php" class="quick-card"><span>💬</span><div><strong>Messages</strong><small>Message your counsellor</small></div></a>
      <a href="resources.php" class="quick-card"><span>📚</span><div><strong>Explore Resources</strong><small>Helpful materials</small></div></a>
    </div>
  </div>
</section>


    </main>

</div>


<script src="../assets/js/dashboard.js"></script>

<script src="../assets/js/app.js"></script>
</body>

</html>