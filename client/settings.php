<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$prefs = [
  "pref_new_messages" => "New Messages|Notify me when a counsellor sends a message.",
  "pref_reminders" => "Appointment Reminders|Receive reminders before counselling sessions.",
  "pref_new_resources" => "New Resources|Notify me when new wellness resources are available.",
  "pref_system" => "System Notifications|Receive important system announcements.",
];
$privacy = [
  "pref_show_profile" => "Show Profile to Counsellors|Allow counsellors to see your basic profile information.",
  "pref_save_history" => "Save Chat History|Keep your previous conversations available in your account.",
];
$cp = $pdo->prepare("SELECT anonymous_profile, preferred_language FROM client_profiles WHERE user_id = ?");
$cp->execute([$meId]);
$cprof = $cp->fetch() ?: ["anonymous_profile" => 0, "preferred_language" => "english"];
?>


<?php
$pageTitle = "Settings";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo $pageTitle; ?> - I Can Hear You Rwanda
    </title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
</head>

<body>

<div class="dashboard-layout">

    <!-- ================= SIDEBAR ================= -->

    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">

            <a href="../index.php" class="logo">

                <span>
                    <img
                        src="../assets/images/log.jpeg"
                        alt="I Can Hear You"
                    >
                </span>

                <strong>I Can Hear You Rwanda</strong>

            </a>

        </div>


        <!-- USER -->

        <div class="sidebar-user">

            <div class="user-avatar">
                <?= h($meInitials) ?>
            </div>

            <div>
                <h4><?= h($meName) ?></h4>
                <p>Client</p>
            </div>

        </div>


        <!-- MAIN MENU -->

        <div class="sidebar-section">

            <span class="sidebar-section-title">
                Main Menu
            </span>

            <nav class="sidebar-nav">

                <a href="dashboard.php">
                    <span>🏠</span>
                    Dashboard
                </a>

                <a href="find-counselor.php">
                    <span>👩‍⚕️</span>
                    Find Counsellor
                </a>

                <a href="bookings.php">
                    <span>📅</span>
                    My Bookings
                </a>

                <a href="messages.php">
                    <span>💬</span>
                    Messages
                    <?php if ($unreadMsgs > 0): ?><span class="nav-badge"><?= (int)$unreadMsgs ?></span><?php endif; ?>
                </a>

                <a href="sessions.php">
                    <span>🎥</span>
                    My Sessions
                </a>

            </nav>

        </div>


        <!-- SUPPORT -->

        <div class="sidebar-section">

            <span class="sidebar-section-title">
                Support
            </span>

            <nav class="sidebar-nav">

                <a href="resources.php">
                    <span>📚</span>
                    Resources
                </a>

                <a href="profile.php">
                    <span>👤</span>
                    My Profile
                </a>

                <a href="notifications.php">
                    <span>🔔</span>
                    Notifications
                    <?php if ($unreadMsgs > 0): ?><span class="nav-badge"><?= (int)$unreadMsgs ?></span><?php endif; ?>
                </a>

                <a href="settings.php"  class="active">
                    <span>⚙️</span>
                    Settings
                </a>

            </nav>

        </div>


        <!-- LOGOUT -->

        <div class="sidebar-bottom">

            <a
                href="../pages/logout.php"
                class="logout-link"
            >
                <span>🚪</span>
                Logout
            </a>

        </div>

    </aside>


    <!-- ================= MAIN ================= -->

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

                <button class="header-icon">
                    🔔
                    <span class="header-notification-dot"></span>
                </button>


                <div class="header-profile">

                    <div class="user-avatar small">
                        <?= h($meInitials) ?>
                    </div>

                    <div>

                        <strong><?= h($meName) ?></strong>

                        <span>Client</span>

                    </div>

                </div>

            </div>

        </header>


        <!-- ================= SETTINGS CONTENT ================= -->

        <section class="settings-page" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="settings-header"><div><h1>Settings</h1><p>Manage your account, privacy, notifications and preferences.</p></div></div>
  <div class="settings-layout">
    <aside class="settings-menu">
      <button class="settings-menu-item active" data-section="account">👤 <span>Account</span></button>
      <button class="settings-menu-item" data-section="privacy">🔒 <span>Privacy</span></button>
      <button class="settings-menu-item" data-section="notifications">🔔 <span>Notifications</span></button>
      <button class="settings-menu-item" data-section="language">🌐 <span>Language</span></button>
      <button class="settings-menu-item danger-menu" data-section="danger">⚠️ <span>Danger Zone</span></button>
    </aside>
    <div class="settings-content">
      <div class="settings-section active" id="account">
        <div class="settings-section-header"><h2>Account Settings</h2><p>Change your password.</p></div>
        <form method="post" action="../api/account.php">
          <?= csrf_field() ?><input type="hidden" name="action" value="password"><input type="hidden" name="return" value="client/settings.php">
          <div class="form-group"><label>Current Password</label><input type="password" name="current_password" required autocomplete="current-password"></div>
          <div class="form-group"><label>New Password</label><input type="password" name="new_password" required minlength="8" autocomplete="new-password"></div>
          <div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" required minlength="8" autocomplete="new-password"></div>
          <button class="secondary-btn" type="submit">Change Password</button>
        </form>
        <p class="setting-description">To change your email or phone, use <a href="profile.php">My Profile</a>.</p>
      </div>
      <form method="post" action="../api/account.php" id="prefsForm">
        <?= csrf_field() ?><input type="hidden" name="action" value="prefs"><input type="hidden" name="return" value="client/settings.php">
        <div class="settings-section" id="privacy">
          <div class="settings-section-header"><h2>Privacy Settings</h2><p>Control how your information is shared.</p></div>
          <div class="setting-option"><div><strong>Anonymous Profile</strong><p>Hide your real name when interacting with counsellors.</p></div>
            <label class="switch"><input type="checkbox" name="anonymous_profile" value="1" <?= $cprof["anonymous_profile"] ? "checked" : "" ?>><span class="slider"></span></label></div>
          <?php foreach ($privacy as $k => $txt): [$t, $d] = explode("|", $txt); ?>
          <div class="setting-option"><div><strong><?= h($t) ?></strong><p><?= h($d) ?></p></div>
            <label class="switch"><input type="checkbox" name="<?= $k ?>" value="1" <?= user_pref($pdo, $meId, $k, "1") === "1" ? "checked" : "" ?>><span class="slider"></span></label></div>
          <?php endforeach; ?>
        </div>
        <div class="settings-section" id="notifications">
          <div class="settings-section-header"><h2>Notification Settings</h2><p>Choose which notifications you would like to receive.</p></div>
          <?php foreach ($prefs as $k => $txt): [$t, $d] = explode("|", $txt); ?>
          <div class="setting-option"><div><strong><?= h($t) ?></strong><p><?= h($d) ?></p></div>
            <label class="switch"><input type="checkbox" name="<?= $k ?>" value="1" <?= user_pref($pdo, $meId, $k, "1") === "1" ? "checked" : "" ?>><span class="slider"></span></label></div>
          <?php endforeach; ?>
        </div>
        <div class="settings-section" id="language">
          <div class="settings-section-header"><h2>Language</h2><p>Select your preferred language.</p></div>
          <div class="form-group"><label>Preferred Language</label><select name="preferred_language">
            <?php foreach (["english" => "English", "kinyarwanda" => "Kinyarwanda", "french" => "French"] as $v => $l): ?>
              <option value="<?= $v ?>" <?= $cprof["preferred_language"] === $v ? "selected" : "" ?>><?= $l ?></option><?php endforeach; ?></select></div>
        </div>
        <button type="submit" class="secondary-btn" style="margin-top:16px">Save Preferences</button>
      </form>
      <div class="settings-section" id="danger">
        <div class="settings-section-header"><h2>Danger Zone</h2><p>Deactivate your account. An administrator can reactivate it later.</p></div>
        <form method="post" action="../api/account.php" onsubmit="return confirm('Deactivate your account? You will be signed out.');">
          <?= csrf_field() ?><input type="hidden" name="action" value="deactivate">
          <div class="form-group"><label>Confirm with your password</label><input type="password" name="current_password" required></div>
          <button type="submit" class="secondary-btn" style="color:#c0392b">Deactivate Account</button>
        </form>
      </div>
    </div>
  </div>
</section>


    </main>

</div>


<script src="../assets/js/dashboard.js"></script>
<script src="../assets/js/settings.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>