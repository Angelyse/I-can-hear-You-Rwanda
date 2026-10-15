<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("admin");
require_once __DIR__ . "/../includes/page_init.php";
$rows = $pdo->query("SELECT setting_key, setting_value, setting_group FROM settings ORDER BY setting_group, setting_key")->fetchAll();
$groups = [];
foreach ($rows as $r) $groups[$r["setting_group"]][] = $r;
$isBool = fn($r) => in_array($r["setting_value"], ["0", "1"], true);
$pretty = fn($k) => ucfirst(str_replace("_", " ", $k));
?>

<?php
$pageTitle = "System Settings";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-settings.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
                I Can Hear You Rwanda
            </a>
        </div>

        <nav class="sidebar-nav">

            <div class="sidebar-section">
                <span class="sidebar-section-title">Main Menu</span>

                <a href="dashboard.php">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>

                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>

                <a href="counsellors.php">
                    <i class="fas fa-user-doctor"></i>
                    <span>Counsellors</span>
                </a>

                <a href="clients.php">
                    <i class="fas fa-user-group"></i>
                    <span>Clients</span>
                </a>

                <a href="bookings.php">
                    <i class="fas fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>

                <a href="sessions.php">
                    <i class="fas fa-video"></i>
                    <span>Sessions</span>
                </a>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-title">Management</span>

                <a href="resources.php">
                    <i class="fas fa-book-open"></i>
                    <span>Resources</span>
                </a>

                <a href="programs.php">
                    <i class="fas fa-layer-group"></i>
                    <span>Programs</span>
                </a>

                <a href="schools.php">
                    <i class="fas fa-school"></i>
                    <span>Schools</span>
                </a>

                <a href="organizations.php">
                    <i class="fas fa-building"></i>
                    <span>Organizations</span>
                </a>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-title">Reports</span>

                <a href="reports.php">
                    <i class="fas fa-chart-pie"></i>
                    <span>Reports & Analytics</span>
                </a>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-title">System</span>

                <a href="notifications.php">
                    <i class="fas fa-bell"></i>
                    <span>Notifications</span>
                    <?php if ($unreadMsgs > 0): ?><span class="nav-badge"><?= (int)$unreadMsgs ?></span><?php endif; ?>
                </a>

                <a href="settings.php"  class="active">
                    <i class="fas fa-gear"></i>
                    <span>Settings</span>
                </a>

                <a href="../pages/logout.php">
                    <i class="fas fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </div>

        </nav>
    </aside>


    <!-- ================= MAIN ================= -->
    <main class="dashboard-main">

        <!-- TOP HEADER -->
        <header class="dashboard-header">

            <button class="mobile-menu" id="mobileMenu">
                <i class="fas fa-bars"></i>
            </button>

            <div class="header-search">
                <i class="fas fa-search"></i>
                <input
                    type="text"
                    id="headerSearch"
                    placeholder="Search settings..."
                >
            </div>

            <div class="header-actions">

                <button class="header-icon">
                    <i class="fas fa-bell"></i>
                    <span class="notification-dot"></span>
                </button>

                <div class="header-profile">
                    <div class="header-avatar">AD</div>

                    <div class="header-user-info">
                        <strong><?= h($meName) ?></strong>
                        <small>System Administrator</small>
                    </div>

                    <i class="fas fa-chevron-down"></i>
                </div>

            </div>
        </header>


        <!-- PAGE CONTENT -->
        <div class="settings-page" data-csrf="<?= h($csrf) ?>">
  <link rel="stylesheet" href="../assets/css/admin-extra.css">
  <?= flash_html() ?>
  <div class="page-header"><div><h1>System Settings</h1><p>Configure the platform.</p></div></div>
  <form class="admin-form" method="post" action="settings-actions.php">
    <?= csrf_field() ?>
    <?php foreach ($groups as $group => $items): ?>
    <div class="admin-box"><h2><?= h(ucfirst($group)) ?></h2>
      <div class="grid">
        <?php foreach ($items as $r): ?>
        <div class="field">
          <?php if ($isBool($r)): ?>
            <label><input type="checkbox" name="setting[<?= h($r["setting_key"]) ?>]" value="1" <?= $r["setting_value"] === "1" ? "checked" : "" ?>> <?= h($pretty($r["setting_key"])) ?></label>
          <?php else: ?>
            <label for="s_<?= h($r["setting_key"]) ?>"><?= h($pretty($r["setting_key"])) ?></label>
            <input id="s_<?= h($r["setting_key"]) ?>" type="text" name="setting[<?= h($r["setting_key"]) ?>]" value="<?= h($r["setting_value"]) ?>">
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <p><button class="admin-btn" type="submit">Save settings</button></p>
  </form>
</div>
</main>
</div>
<script src="../assets/js/app.js"></script>

</body>
</html>
