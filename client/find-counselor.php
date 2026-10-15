<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$rows = $pdo->query("SELECT u.id, u.name, cp.qualification, cp.specialization, cp.years_experience,
      cp.languages, cp.session_types,
      (SELECT COUNT(*) FROM bookings b WHERE b.counselor_id = u.id AND b.booking_date = CURDATE()
         AND b.status IN ('pending','confirmed')) AS today_load
    FROM users u INNER JOIN roles r ON r.id = u.role_id
    INNER JOIN counselor_profiles cp ON cp.user_id = u.id
    WHERE r.name = 'counsellor' AND u.status = 'active' AND cp.status = 'active'
    ORDER BY u.name")->fetchAll();
$specs = array_unique(array_filter(array_column($rows, "specialization")));
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Find Counsellor - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor.css">
</head>

<body>

<div class="dashboard-container">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <a href="../index.php" class="logo">
                <span>
                    <img src="../assets/images/log.jpeg" alt="I Can Hear You">
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
               class="sidebar-link active">

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

        <!-- TOP HEADER -->
        <header class="dashboard-header">

            <button class="mobile-menu" id="mobileMenu">
                ☰
            </button>

            <div class="header-search">
                <input
                    type="text"
                    id="searchCounselor"
                    placeholder="Search counsellor..."
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


        <!-- PAGE CONTENT -->
        <section class="page-content">
  <div class="page-title"><div><h1>Find a Counsellor</h1><p>Find the right counsellor to support you.</p></div></div>
  <div class="counselor-filters">
    <div class="filter-search"><span>🔍</span><input type="text" id="counselorSearch" placeholder="Search by name or specialization..."></div>
    <select id="specializationFilter"><option value="">All Specializations</option>
      <?php foreach ($specs as $s): ?><option value="<?= h($s) ?>"><?= h($s) ?></option><?php endforeach; ?></select>
    <select id="languageFilter"><option value="">All Languages</option>
      <option value="English">English</option><option value="Kinyarwanda">Kinyarwanda</option><option value="French">French</option></select>
    <select id="availabilityFilter"><option value="">Availability</option>
      <option value="Available">Available Now</option><option value="Busy">Busy</option></select>
  </div>
  <div class="results-header"><h2>Available Counsellors</h2><span id="counselorCount"><?= count($rows) ?> counsellors</span></div>
  <div class="counselor-grid" id="counselorGrid">
    <?php foreach ($rows as $c):
      $busy = (int)$c["today_load"] >= count(booking_slots());
      $langs = array_map("trim", explode(",", (string)$c["languages"]));
      $types = explode(",", (string)$c["session_types"]);
      $typeLabel = in_array("video", $types) && in_array("chat", $types) ? "💻 Video & Chat" : (in_array("video", $types) ? "🎥 Video" : "💬 Chat");
    ?>
    <div class="counselor-card" data-name="<?= h($c["name"]) ?>" data-specialization="<?= h($c["specialization"]) ?>"
         data-language="<?= h(implode(" ", $langs)) ?>" data-availability="<?= $busy ? "Busy" : "Available" ?>">
      <div class="counselor-top"><div class="counselor-avatar"><?= h(initials($c["name"])) ?></div>
        <span class="availability <?= $busy ? "busy" : "available" ?>">● <?= $busy ? "Busy" : "Available" ?></span></div>
      <h3><?= h($c["name"]) ?></h3>
      <p class="qualification"><?= h($c["qualification"]) ?></p>
      <p class="specialization"><?= h($c["specialization"]) ?></p>
      <div class="counselor-info"><span>🗣 <?= h(implode(", ", $langs)) ?></span><span><?= $typeLabel ?></span></div>
      <div class="experience"><strong><?= (int)$c["years_experience"] ?>+</strong> years experience</div>
      <div class="card-actions">
        <a href="counselor-profile.php?id=<?= (int)$c["id"] ?>" class="view-profile">View Profile</a>
        <?php if ($busy): ?><button class="book-btn disabled" disabled>Currently Busy</button>
        <?php else: ?><a href="book-session.php?counselor=<?= (int)$c["id"] ?>" class="book-btn">Book Session</a><?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="no-results" id="noResults" <?= $rows ? "" : 'style="display:block"' ?>><div>🔍</div><h3>No counsellors found</h3><p>Try changing your search or filter options.</p></div>
</section>


    </main>

</div>


<script src="../assets/js/dashboard.js"></script>
<script src="../assets/js/counselor.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>