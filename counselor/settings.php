<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("counsellor");
require_once __DIR__ . "/../includes/page_init.php";
$prefs = [
  "pref_new_bookings" => "New booking requests|Notify me when a client requests a session.",
  "pref_cancellations" => "Cancellations|Notify me when a booking is cancelled.",
  "pref_client_messages" => "Client messages|Notify me when a client sends a message.",
  "pref_reminders" => "Session reminders|Remind me before upcoming sessions.",
  "pref_weekly_summary" => "Weekly summary|Receive a weekly activity summary.",
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Settings - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor-settings.css">
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

                <a href="profile.php">
                    <span class="nav-icon">👤</span>
                    <span>My Profile</span>
                </a>

                <a href="notifications.php">
                    <span class="nav-icon">🔔</span>
                    <span>Notifications</span>
                    <?php if ($unreadMsgs > 0): ?><span class="nav-badge"><?= (int)$unreadMsgs ?></span><?php endif; ?>
                </a>

                <a href="settings.php"  class="active">
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


    <!-- ================= MAIN ================= -->
    <main class="dashboard-main">

        <!-- HEADER -->
        <header class="dashboard-header">

            <button class="mobile-menu" id="mobileMenu">
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


        <!-- ================= SETTINGS PAGE ================= -->
        <div class="settings-page" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="page-header"><div><h1>Settings</h1><p>Manage your counsellor account and preferences.</p></div></div>
  <div class="settings-layout">
    <aside class="settings-menu">
      <button class="settings-tab active" data-section="notifications">🔔 <span><strong>Notifications</strong><small>Notification preferences</small></span></button>
      <button class="settings-tab" data-section="security">🛡️ <span><strong>Security</strong><small>Password</small></span></button>
      <button class="settings-tab danger-tab" data-section="danger">⚠️ <span><strong>Danger Zone</strong><small>Account actions</small></span></button>
    </aside>
    <div class="settings-content">
      <section class="settings-section active" id="notifications">
        <div class="section-header"><h2>Notification Settings</h2><p>Choose what you would like to be notified about.</p></div>
        <form method="post" action="../api/account.php" class="settings-card">
          <?= csrf_field() ?><input type="hidden" name="action" value="prefs"><input type="hidden" name="return" value="counselor/settings.php">
          <?php foreach ($prefs as $k => $txt): [$t, $d] = explode("|", $txt); ?>
          <div class="setting-row"><div><strong><?= h($t) ?></strong><p><?= h($d) ?></p></div>
            <label class="switch"><input type="checkbox" name="<?= $k ?>" value="1" <?= user_pref($pdo, $meId, $k, "1") === "1" ? "checked" : "" ?>><span class="slider"></span></label></div>
          <?php endforeach; ?>
          <button type="submit" class="save-btn">Save Changes</button>
        </form>
      </section>
      <section class="settings-section" id="security">
        <div class="section-header"><h2>Security</h2><p>Change your password.</p></div>
        <form method="post" action="../api/account.php" class="settings-card">
          <?= csrf_field() ?><input type="hidden" name="action" value="password"><input type="hidden" name="return" value="counselor/settings.php">
          <div class="form-group"><label>Current Password</label><input type="password" name="current_password" required autocomplete="current-password"></div>
          <div class="form-group"><label>New Password</label><input type="password" name="new_password" required minlength="8" autocomplete="new-password"></div>
          <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"></div>
          <button type="submit" class="save-btn">Change Password</button>
        </form>
      </section>
      <section class="settings-section" id="danger">
        <div class="section-header"><h2>Danger Zone</h2><p>Deactivate your account. An administrator can reactivate it.</p></div>
        <form method="post" action="../api/account.php" class="settings-card" onsubmit="return confirm('Deactivate your account? You will be signed out.');">
          <?= csrf_field() ?><input type="hidden" name="action" value="deactivate">
          <div class="form-group"><label>Confirm with your password</label><input type="password" name="current_password" required></div>
          <button type="submit" class="danger-outline">Deactivate Account</button>
        </form>
      </section>
    </div>
  </div>
</div>
</main>
</div>
<script src="../assets/js/dashboard.js"></script>
<script src="../assets/js/counselor-tabs.js"></script>
<script src="../assets/js/app.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>
