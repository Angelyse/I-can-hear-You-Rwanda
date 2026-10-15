<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$cp = $pdo->prepare("SELECT * FROM client_profiles WHERE user_id = ?");
$cp->execute([$meId]);
$profile = $cp->fetch() ?: ["client_type" => "youth", "preferred_language" => "english", "display_name" => "", "wellness_information" => "", "consent_status" => "pending", "anonymous_profile" => 0];
$checks = [!empty($me["phone"]), !empty($profile["preferred_language"]), !empty($profile["display_name"]),
           !empty($profile["wellness_information"]), $profile["consent_status"] === "accepted"];
$pct = (int)round(array_sum(array_map("intval", $checks)) / count($checks) * 100);
?>


<?php
// My Profile page - frontend only
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
</head>

<body>

<div class="dashboard-layout">

    <!-- =========================
         SIDEBAR
    ========================== -->

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


        <!-- USER PROFILE -->

        <div class="user-mini-profile">

            <div class="user-avatar">
                <?= h($meInitials) ?>
            </div>

            <div>
                <h4><?= h($meName) ?></h4>
                <span>Client</span>
            </div>

        </div>


        <!-- NAVIGATION -->

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

            <a href="profile.php" class="sidebar-link active"><span>👤</span>My Profile</a>

            <a href="notifications.php" class="sidebar-link"> <span>🔔</span> Notifications</a>

            <a href="settings.php" class="sidebar-link"> <span>⚙️</span>Settings </a>


            </div>


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

    <main class="dashboard-main" style="margin-left: 250px;">


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
             PROFILE PAGE
        ========================== -->

        <section class="profile-page" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="profile-heading"><div><h1>My Profile</h1><p>Manage your personal information and preferences.</p></div></div>
  <div class="profile-overview">
    <div class="profile-avatar-large"><?= h($meInitials) ?></div>
    <div class="profile-overview-info"><h2><?= h($meName) ?></h2><p>Client</p><span>Member since <?= h(date("F Y", strtotime($me["created_at"]))) ?></span></div>
    <div class="profile-completion"><div class="completion-header"><span>Profile completion</span><strong><?= $pct ?>%</strong></div>
      <div class="progress-bar"><div class="progress-value" style="width: <?= $pct ?>%;"></div></div><small>Complete your profile to improve your experience.</small></div>
  </div>
  <form class="profile-form" id="profileForm" method="post" action="../api/account.php">
    <?= csrf_field() ?><input type="hidden" name="action" value="client_profile"><input type="hidden" name="return" value="client/profile.php">
    <div class="profile-card"><div class="card-heading"><div><h2>Personal Information</h2><p>Update your basic account information.</p></div></div>
      <div class="form-grid">
        <div class="form-group"><label for="fullName">Full Name</label><input type="text" id="fullName" name="full_name" value="<?= h($meName) ?>" required></div>
        <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="email" value="<?= h($me["email"]) ?>" required></div>
        <div class="form-group"><label for="phone">Phone Number</label><input type="tel" id="phone" name="phone" value="<?= h($me["phone"]) ?>"></div>
        <div class="form-group"><label for="clientType">Client Type</label><select id="clientType" name="client_type">
          <?php foreach (["youth" => "Youth", "parent" => "Parent", "teacher" => "Teacher", "organization_rep" => "Organization Representative"] as $v => $l): ?>
            <option value="<?= $v ?>" <?= $profile["client_type"] === $v ? "selected" : "" ?>><?= $l ?></option><?php endforeach; ?></select></div>
        <div class="form-group"><label for="displayName">Display Name</label><input type="text" id="displayName" name="display_name" value="<?= h($profile["display_name"]) ?>"><small>This name can be shown during anonymous counselling.</small></div>
        <div class="form-group"><label for="language">Preferred Language</label><select id="language" name="preferred_language">
          <?php foreach (["kinyarwanda" => "Kinyarwanda", "english" => "English", "french" => "French"] as $v => $l): ?>
            <option value="<?= $v ?>" <?= $profile["preferred_language"] === $v ? "selected" : "" ?>><?= $l ?></option><?php endforeach; ?></select></div>
      </div></div>
    <div class="profile-card"><div class="card-heading"><div><h2>Wellness Information</h2><p>Share information that may help us provide better support.</p></div></div>
      <div class="form-group"><label for="wellnessInformation">About You</label>
        <textarea id="wellnessInformation" name="wellness_information" rows="5" placeholder="Tell us anything you feel comfortable sharing..."><?= h($profile["wellness_information"]) ?></textarea><small>Only share information you are comfortable providing.</small></div></div>
    <div class="profile-card"><div class="card-heading"><div><h2>Privacy & Consent</h2><p>Manage how your information is used.</p></div></div>
      <div class="privacy-option"><div><h3>Counselling Consent</h3><p>I agree to receive counselling and mentorship services through this platform.</p></div>
        <label class="switch"><input type="checkbox" name="consent" value="1" <?= $profile["consent_status"] === "accepted" ? "checked" : "" ?>><span class="slider"></span></label></div>
      <div class="privacy-option"><div><h3>Anonymous Display</h3><p>Use my display name instead of my full name when appropriate.</p></div>
        <label class="switch"><input type="checkbox" name="anonymous" value="1" <?= $profile["anonymous_profile"] ? "checked" : "" ?>><span class="slider"></span></label></div></div>
    <div class="profile-actions"><button type="button" class="cancel-profile-btn" id="cancelProfile" onclick="location.reload()">Cancel</button><button type="submit" class="save-profile-btn">Save Changes</button></div>
  </form>
</section>


    </main>

</div>


<script src="../assets/js/profile.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>