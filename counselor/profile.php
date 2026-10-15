<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("counsellor");
require_once __DIR__ . "/../includes/page_init.php";
$cp = $pdo->prepare("SELECT * FROM counselor_profiles WHERE user_id = ?");
$cp->execute([$meId]);
$p = $cp->fetch() ?: ["qualification" => "", "specialization" => "", "years_experience" => 0, "languages" => "", "bio" => "", "availability" => "", "session_types" => "video,chat", "status" => "pending"];
$types = explode(",", (string)$p["session_types"]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor-profile1.css">
</head>

<body>

<div class="dashboard-layout">

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <a href="dashboard.php" class="logo">
                <span>
                    <img src="../assets/images/log.jpeg" alt="I Can Hear You">
                </span>
                <strong>I Can Hear You Rwanda</strong>
            </a>
        </div>

        <nav class="sidebar-nav">

            <div class="sidebar-section">

                <span class="sidebar-section-title">Main Menu</span>

                <a href="dashboard.php">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>

                <a href="clients.php">
                    <span class="nav-icon">👥</span>
                    <span>My Clients</span>
                </a>

                <a href="bookings.php">
                    <span class="nav-icon">📅</span>
                    <span>Bookings</span>
                </a>

                <a href="messages.php">
                    <span class="nav-icon">💬</span>
                    <span>Messages</span>
                    <?php if ($unreadMsgs > 0): ?><span class="nav-badge"><?= (int)$unreadMsgs ?></span><?php endif; ?>
                </a>

                <a href="sessions.php">
                    <span class="nav-icon">🎥</span>
                    <span>Sessions</span>
                </a>

            </div>

            <div class="sidebar-section">

                <span class="sidebar-section-title">Management</span>

                <a href="session-notes.php">
                    <span class="nav-icon">📝</span>
                    <span>Session Notes</span>
                </a>

                <a href="resources.php">
                    <span class="nav-icon">📚</span>
                    <span>Resources</span>
                </a>

            </div>

            <div class="sidebar-section">

                <span class="sidebar-section-title">Account</span>

                <a href="profile.php"  class="active">
                    <span class="nav-icon">👤</span>
                    <span>My Profile</span>
                </a>

                <a href="notifications.php">
                    <span class="nav-icon">🔔</span>
                    <span>Notifications</span>
                </a>

                <a href="settings.php">
                    <span class="nav-icon">⚙️</span>
                    <span>Settings</span>
                </a>

                <a href="../pages/logout.php" class="logout-link">
                    <span class="nav-icon">🚪</span>
                    <span>Logout</span>
                </a>

            </div>

        </nav>

    </aside>


    <!-- ================= MAIN CONTENT ================= -->
    <main class="dashboard-main">

        <!-- TOP HEADER -->
        <header class="dashboard-header" style="margin-left: 250px;">

            <button class="mobile-menu" id="mobileMenu">
                ☰
            </button>

            <div class="header-search">
                <span>🔍</span>
                <input
                    type="text"
                    placeholder="Search..."
                    aria-label="Search"
                >
            </div>

            <div class="header-actions">

                <button class="notification-btn" title="Notifications">
                    🔔
                    <span class="notification-dot"></span>
                </button>

                <div class="header-profile">

                    <div class="header-avatar">
                        <?= h($meInitials) ?>
                    </div>

                    <div class="header-profile-info">
                        <strong>Dr. Jean Mukamana</strong>
                        <small>Counsellor</small>
                    </div>

                </div>

            </div>

        </header>


        <!-- ================= PROFILE PAGE ================= -->
        <div class="profile-page" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="page-header"><div><h1>My Profile</h1><p>Manage your professional information and profile settings.</p></div>
    <div class="profile-status"><span class="status-dot"></span> <?= $p["status"] === "active" ? "Visible to clients" : "Not visible yet — status: " . h($p["status"]) ?></div></div>
  <section class="profile-overview">
    <div class="profile-avatar-section"><div class="large-avatar"><?= h($meInitials) ?></div></div>
    <div class="profile-overview-info"><h2><?= h($meName) ?></h2><p class="professional-title"><?= h($p["qualification"]) ?></p>
      <div class="profile-tags"><span><?= h($p["specialization"]) ?></span></div>
      <p class="profile-member">Counsellor since <?= h(date("F Y", strtotime($me["created_at"]))) ?></p></div>
  </section>
  <form id="profileForm" method="post" action="../api/account.php">
    <?= csrf_field() ?><input type="hidden" name="action" value="counselor_profile"><input type="hidden" name="return" value="counselor/profile.php">
    <section class="profile-card"><div class="card-header"><div><h2>Personal Information</h2><p>Your basic account information.</p></div></div>
      <div class="form-grid">
        <div class="form-group"><label for="fullName">Full Name</label><input type="text" id="fullName" name="full_name" value="<?= h($meName) ?>" required></div>
        <div class="form-group"><label>Email Address</label><input type="email" value="<?= h($me["email"]) ?>" disabled></div>
        <div class="form-group"><label for="phone">Phone Number</label><input type="tel" id="phone" name="phone" value="<?= h($me["phone"]) ?>"></div>
      </div></section>
    <section class="profile-card"><div class="card-header"><div><h2>Professional Information</h2><p>Information clients can see.</p></div></div>
      <div class="form-grid">
        <div class="form-group"><label for="qualification">Qualification</label><input type="text" id="qualification" name="qualification" value="<?= h($p["qualification"]) ?>"></div>
        <div class="form-group"><label for="specialization">Specialization</label><input type="text" id="specialization" name="specialization" value="<?= h($p["specialization"]) ?>"></div>
        <div class="form-group"><label for="experience">Years of Experience</label><input type="number" id="experience" name="years_experience" value="<?= (int)$p["years_experience"] ?>" min="0" max="60"></div>
        <div class="form-group"><label for="languages">Languages</label><input type="text" id="languages" name="languages" value="<?= h($p["languages"]) ?>" placeholder="Kinyarwanda, English"></div>
        <div class="form-group"><label for="availability">Working hours</label><input type="text" id="availability" name="availability" value="<?= h($p["availability"]) ?>" placeholder="Mon-Fri 09:00-17:00"></div>
      </div>
      <div class="form-group full-width"><label for="bio">Professional Bio</label><textarea id="bio" name="bio" rows="6"><?= h($p["bio"]) ?></textarea></div>
      <div class="form-group"><label>Session types offered</label>
        <label><input type="checkbox" name="session_types[]" value="video" <?= in_array("video", $types) ? "checked" : "" ?>> Video</label>
        <label><input type="checkbox" name="session_types[]" value="chat" <?= in_array("chat", $types) ? "checked" : "" ?>> Chat</label></div>
    </section>
    <div class="form-actions"><button type="button" class="cancel-btn" onclick="location.reload()">Cancel</button><button type="submit" class="save-btn">Save Changes</button></div>
  </form>
</div>
</main>
</div>
<script src="../assets/js/dashboard.js"></script>
<script src="../assets/js/app.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>
