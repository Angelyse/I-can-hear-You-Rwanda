<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$cid = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$st = $pdo->prepare("SELECT u.id, u.name, cp.* FROM users u
    INNER JOIN roles r ON r.id = u.role_id INNER JOIN counselor_profiles cp ON cp.user_id = u.id
    WHERE u.id = ? AND r.name = 'counsellor' AND u.status = 'active' AND cp.status = 'active'");
$st->execute([$cid]);
$c = $st->fetch();
if (!$c) { flash_set("Counsellor not found.", "error"); redirect("find-counselor.php"); }
$types = explode(",", (string)$c["session_types"]);
$langs = array_filter(array_map("trim", explode(",", (string)$c["languages"])));
$avg = $pdo->prepare("SELECT ROUND(AVG(f.rating),1) avg, COUNT(*) n FROM feedback f
    INNER JOIN sessions s ON s.id = f.session_id INNER JOIN bookings b ON b.id = s.booking_id WHERE b.counselor_id = ?");
$avg->execute([$cid]);
$rating = $avg->fetch();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Counsellor Profile - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor-profile.css">
</head>

<body>

<div class="dashboard-container">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <a href="../index.php" class="logo">

                <span>
                    <img
                        src="../assets/images/log.jpeg"
                        alt="I Can Hear You"
                    >
                </span>

                I Can Hear You Rwanda

            </a>
        </div>


         <nav class="sidebar-nav">

            <p class="nav-title">
                MAIN MENU
            </p>

            <a href="dashboard.php"
               class="sidebar-link">

                <span>🏠</span>

                Dashboard

            </a>


            <a href="find-counselor.php"
               class="sidebar-link">

                <span>🧑‍⚕️</span>

                Find Counsellor

            </a>


            <a href="bookings.php"
               class="sidebar-link">

                <span>📅</span>

                My Bookings

            </a>


            <a href="messages.php"
               class="sidebar-link">

                <span>💬</span>

                Messages

                <?php if ($unreadMsgs > 0): ?><span class="notification-count"><?= (int)$unreadMsgs ?></span><?php endif; ?>

            </a>


            <a href="sessions.php"
               class="sidebar-link">

                <span>🎥</span>

                My Sessions

            </a>


            <p class="nav-title">
                SUPPORT
            </p>


            <a href="resources.php"
               class="sidebar-link">

                <span>📚</span>

                Resources

            </a>


            <a href="profile.php"
               class="sidebar-link">

                <span>👤</span>

                My Profile

            </a>


            <a href="notifications.php"
               class="sidebar-link">

                <span>🔔</span>

                Notifications

            </a>


            <a href="settings.php"
               class="sidebar-link">

                <span>⚙️</span>

                Settings

            </a>

        </nav>


        <div class="sidebar-bottom">

            <a href="../pages/logout.php">
                <span>🚪</span>
                Logout
            </a>

        </div>

    </aside>


    <!-- MAIN CONTENT -->
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
                    placeholder="Search..."
                >

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
                        <strong><?= h($meName) ?></strong>
                        <small>Client</small>
                    </div>

                </div>

            </div>

        </header>


        <!-- PAGE -->
        <section class="profile-page">
  <a href="find-counselor.php" class="back-link">← Back to Counsellors</a>
  <div class="counselor-profile-header">
    <div class="profile-avatar"><?= h(initials($c["name"])) ?></div>
    <div class="profile-main-info">
      <div class="profile-name-row"><h1><?= h($c["name"]) ?></h1><span class="profile-status">● Available</span></div>
      <p class="profile-title"><?= h($c["qualification"]) ?></p>
      <div class="profile-specialization"><span><?= h($c["specialization"]) ?></span>
        <?php if ($rating["n"]): ?><span>⭐ <?= h($rating["avg"]) ?> (<?= (int)$rating["n"] ?>)</span><?php endif; ?></div>
    </div>
    <div class="profile-action"><a href="book-session.php?counselor=<?= (int)$c["id"] ?>" class="primary-button">Book a Session</a></div>
  </div>
  <div class="profile-content">
    <div class="profile-left">
      <div class="profile-card"><h2>About the Counsellor</h2><p><?= nl2br(h($c["bio"] ?: "This counsellor has not added a biography yet.")) ?></p></div>
      <div class="profile-card"><h2>Experience & Qualifications</h2>
        <div class="qualification-item"><div class="qualification-icon">🎓</div><div><strong><?= h($c["qualification"]) ?></strong><p>Professional Qualification</p></div></div>
        <div class="qualification-item"><div class="qualification-icon">💼</div><div><strong><?= (int)$c["years_experience"] ?>+ Years Experience</strong><p>Counselling and mental wellness support</p></div></div></div>
      <div class="profile-card"><h2>Areas of Specialization</h2><div class="specialization-list"><span><?= h($c["specialization"]) ?></span></div></div>
    </div>
    <div class="profile-right">
      <div class="profile-card"><h2>Session Options</h2>
        <?php if (in_array("chat", $types)): ?><div class="session-option"><div class="session-icon">💬</div><div><strong>Chat Session</strong><p>Private text conversation</p></div></div><?php endif; ?>
        <?php if (in_array("video", $types)): ?><div class="session-option"><div class="session-icon">🎥</div><div><strong>Video Session</strong><p>Private online video meeting</p></div></div><?php endif; ?></div>
      <div class="profile-card"><h2>Languages</h2><div class="language-list"><?php foreach ($langs as $l): ?><span><?= h($l) ?></span><?php endforeach; ?></div></div>
      <div class="profile-card"><h2>Availability</h2><div class="availability-list"><div><span>Working hours</span><strong><?= h($c["availability"] ?: "By appointment") ?></strong></div></div></div>
    </div>
  </div>
</section>


    </main>

</div>


<script src="../assets/js/dashboard.js"></script>

<script src="../assets/js/app.js"></script>
</body>

</html>