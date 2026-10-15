<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$cid = filter_input(INPUT_GET, "counselor", FILTER_VALIDATE_INT);
$st = $pdo->prepare("SELECT u.id, u.name, cp.qualification, cp.session_types FROM users u
    INNER JOIN roles r ON r.id = u.role_id INNER JOIN counselor_profiles cp ON cp.user_id = u.id
    WHERE u.id = ? AND r.name = 'counsellor' AND u.status = 'active' AND cp.status = 'active'");
$st->execute([$cid]);
$c = $st->fetch();
if (!$c) { flash_set("Please choose a counsellor first.", "error"); redirect("find-counselor.php"); }
$types = explode(",", (string)$c["session_types"]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf" content="<?= h($csrf) ?>">

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Book Session - I Can Hear You Rwanda</title>

    <link rel="stylesheet"
          href="../assets/css/dashboard.css">
    <link rel="stylesheet"
          href="../assets/css/style.css">

    <link rel="stylesheet"
          href="../assets/css/booking.css">

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


        <div class="sidebar-bottom">

            <a href="../pages/logout.php">
                <span>🚪</span>
                Logout
            </a>

        </div>

    </aside>


    <!-- MAIN -->

    <main class="main-content">


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


        <!-- BOOKING CONTENT -->

        <section class="booking-page" data-counselor-id="<?= (int)$c["id"] ?>" data-csrf="<?= h($csrf) ?>">
  <a href="counselor-profile.php?id=<?= (int)$c["id"] ?>" class="back-link">← Back to Counsellor Profile</a>
  <div class="booking-layout">
    <div class="booking-card">
      <h1>Book a Counselling Session</h1>
      <p class="booking-description">Choose your preferred session type, date and available time.</p>
      <div id="bookingError" class="flash-message flash-error" style="display:none"></div>
      <form id="bookingForm">
        <div class="form-group"><label>Counsellor</label>
          <div class="selected-counselor"><div class="booking-avatar"><?= h(initials($c["name"])) ?></div>
            <div><strong><?= h($c["name"]) ?></strong><small><?= h($c["qualification"]) ?></small></div></div></div>
        <div class="form-group"><label>Session Type</label>
          <div class="session-types">
            <?php if (in_array("chat", $types)): ?><label class="session-choice"><input type="radio" name="session_type" value="chat" checked><span class="choice-content"><strong>💬 Chat Session</strong><small>Communicate through private messaging</small></span></label><?php endif; ?>
            <?php if (in_array("video", $types)): ?><label class="session-choice"><input type="radio" name="session_type" value="video" <?= in_array("chat", $types) ? "" : "checked" ?>><span class="choice-content"><strong>🎥 Video Session</strong><small>Private online video meeting</small></span></label><?php endif; ?>
          </div></div>
        <div class="form-group"><label for="sessionDate">Select Date</label><input type="date" id="sessionDate" required></div>
        <div class="form-group"><label for="sessionTime">Select Time</label>
          <select id="sessionTime" required><option value="">Choose a date first</option></select></div>
        <div class="form-group"><label for="reason">What would you like to discuss?</label>
          <textarea id="reason" rows="5" placeholder="Briefly describe what you would like support with..." required></textarea></div>
        <div class="privacy-note">🔒 Your information is private and confidential.</div>
        <button type="submit" class="confirm-booking">Confirm Booking</button>
      </form>
    </div>
    <div class="booking-summary"><h2>Booking Summary</h2>
      <div class="summary-counselor"><div class="booking-avatar"><?= h(initials($c["name"])) ?></div><div><strong><?= h($c["name"]) ?></strong><small><?= h($c["qualification"]) ?></small></div></div>
      <div class="summary-line"><span>Session</span><strong id="summaryType"><?= in_array("chat", $types) ? "Chat Session" : "Video Session" ?></strong></div>
      <div class="summary-line"><span>Date</span><strong id="summaryDate">Not selected</strong></div>
      <div class="summary-line"><span>Time</span><strong id="summaryTime">Not selected</strong></div>
      <div class="summary-note">After your counsellor confirms, you will receive a notification with the session details.</div>
    </div>
  </div>
</section>


    </main>

</div>


<script src="../assets/js/dashboard.js"></script>

<script src="../assets/js/booking.js"></script>

<script src="../assets/js/app.js"></script>
</body>

</html>