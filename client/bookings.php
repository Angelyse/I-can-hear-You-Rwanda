<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$st = $pdo->prepare("SELECT b.*, u.name AS counselor_name, cp.qualification FROM bookings b
    INNER JOIN users u ON u.id = b.counselor_id LEFT JOIN counselor_profiles cp ON cp.user_id = b.counselor_id
    WHERE b.client_id = ? ORDER BY b.booking_date DESC, b.start_time DESC");
$st->execute([$meId]);
$bookings = $st->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf" content="<?= h($csrf) ?>">

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Bookings - I Can Hear You Rwanda</title>

    <link rel="stylesheet"href="../assets/css/dashboard.css">
    <link rel="stylesheet"href="../assets/css/style.css">

    <link rel="stylesheet"href="../assets/css/booking.css">

</head>


<body>

<div class="dashboard-container">


    <!-- SIDEBAR -->
 <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">

            <a href="../index.php"
               class="logo">

                <span>
                    <img
                        src="../assets/images/log.jpeg"
                        alt="I Can Hear You">
                </span>

                I Can Hear You Rwanda

            </a>

        </div>


       <nav class="sidebar-nav">

            <p class="nav-title">MAIN MENU</p>

            <a href="dashboard.php" class="sidebar-link">

                <span>🏠</span> Dashboard </a>

            <a href="find-counselor.php" class="sidebar-link">

                <span>🧑‍⚕️</span>Find Counsellor</a>

            <a href="bookings.php" class="sidebar-link active"> <span>📅</span> My Bookings</a>

            <a href="messages.php"class="sidebar-link"> <span>💬</span>Messages
 <?php if ($unreadMsgs > 0): ?><span class="notification-count"><?= (int)$unreadMsgs ?></span><?php endif; ?></a>

            <a href="sessions.php" class="sidebar-link"><span>🎥</span>My Sessions </a>

            <p class="nav-title">SUPPORT</p>

            <a href="resources.php" class="sidebar-link"><span>📚</span>Resources</a>

            <a href="profile.php" class="sidebar-link"><span>👤</span>My Profile</a>

            <a href="notifications.php" class="sidebar-link"> <span>🔔</span> Notifications</a>

            <a href="settings.php" class="sidebar-link"> <span>⚙️</span>Settings </a>

        </nav>


        <div class="sidebar-bottom">

            <a href="../pages/logout.php">
                <span>🚪</span>
                Logout
            </a>

        </div>

    </aside>


    <!-- MAIN CONTENT -->

    <main class="main-content" style="margin-left: 250px;">

        <!-- HEADER -->

        <header class="dashboard-header">

            <button
                class="mobile-menu"
                id="mobileMenu">

                ☰

            </button>


            <div class="header-search">

                <input
                    type="text"
                    placeholder="Search...">

            </div>


            <div class="header-right">

                <button class="notification-btn">
                    🔔
                </button>


                <div class="user-profile">

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

            </div>

        </header>


        <!-- PAGE -->

        <section class="bookings-page" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="bookings-title"><div><h1>My Bookings</h1><p>Manage your counselling appointments.</p></div>
    <a href="find-counselor.php" class="new-booking-btn">+ Book a Session</a></div>
  <div class="booking-tabs">
    <?php foreach (["all","pending","confirmed","completed","cancelled"] as $i => $t): ?>
      <button class="booking-tab <?= $i === 0 ? "active" : "" ?>" data-status="<?= $t ?>"><?= ucfirst($t) ?></button>
    <?php endforeach; ?>
  </div>
  <div class="bookings-list" id="bookingsList">
    <?php foreach ($bookings as $b): $cancellable = in_array($b["status"], ["pending","confirmed"]); ?>
    <div class="booking-card" data-status="<?= h($b["status"] === "declined" ? "cancelled" : $b["status"]) ?>" data-id="<?= (int)$b["id"] ?>">
      <div class="booking-person"><div class="booking-avatar"><?= h(initials($b["counselor_name"])) ?></div>
        <div><h3><?= h($b["counselor_name"]) ?></h3><p><?= h($b["qualification"]) ?></p></div></div>
      <div class="booking-details">
        <div><span>Session</span><strong><?= $b["session_type"] === "video" ? "🎥 Video Session" : "💬 Chat Session" ?></strong></div>
        <div><span>Date</span><strong><?= h(fmt_date($b["booking_date"])) ?></strong></div>
        <div><span>Time</span><strong><?= h(fmt_time($b["start_time"])) ?></strong></div>
      </div>
      <div class="booking-status"><?= status_pill($b["status"]) ?></div>
      <div class="booking-actions">
        <a href="sessions.php" class="details-btn">View Details</a>
        <?php if ($cancellable): ?><button class="cancel-btn" data-cancel="<?= (int)$b["id"] ?>">Cancel</button><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php if (!$bookings): ?><div class="no-sessions"><h2>No bookings yet</h2><p>Book your first session with a counsellor.</p><a href="find-counselor.php">Find a Counsellor</a></div><?php endif; ?>
</section>


    </main>

</div>


<script src="../assets/js/dashboard.js"></script>

<script src="../assets/js/bookings.js"></script>

<script src="../assets/js/app.js"></script>
</body>

</html>