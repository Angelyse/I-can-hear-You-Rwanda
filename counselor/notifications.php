<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("counsellor");
require_once __DIR__ . "/../includes/page_init.php";
$n = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND status = 'sent' ORDER BY created_at DESC LIMIT 100");
$n->execute([$meId]);
$items = $n->fetchAll();
$unreadCount = count(array_filter($items, fn($i) => !$i["read_at"]));
$cat = fn($t) => $t === "booking" ? "appointment" : ($t === "message" ? "message" : "system");
$icon = fn($t) => $t === "booking" ? "📅" : ($t === "message" ? "💬" : "🔔");
$appt = count(array_filter($items, fn($i) => $i["type"] === "booking"));
$msg = count(array_filter($items, fn($i) => $i["type"] === "message"));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Notifications - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor-notifications.css">
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

                <a href="notifications.php"  class="active">
                    <span class="nav-icon">🔔</span>
                    <span>Notifications</span>
                    <span class="nav-badge notification-count">5</span>
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
                    id="globalSearch"
                    placeholder="Search..."
                >
            </div>

            <div class="header-actions">

                <button
                    class="notification-btn"
                    title="Notifications"
                >
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


        <!-- ================= PAGE ================= -->
        <div class="notifications-page" data-csrf="<?= h($csrf) ?>">
  <div class="page-header"><div><h1>Notifications</h1><p>Stay updated with your bookings, clients and sessions.</p></div>
    <button type="button" class="mark-all-btn" id="markAllRead">✓ Mark all as read</button></div>
  <div class="notification-stats">
    <div class="notification-stat"><div class="stat-icon">🔔</div><div><span>Total Notifications</span><strong id="totalNotifications"><?= count($items) ?></strong></div></div>
    <div class="notification-stat unread-stat"><div class="stat-icon">📩</div><div><span>Unread</span><strong id="unreadNotifications"><?= $unreadCount ?></strong></div></div>
    <div class="notification-stat"><div class="stat-icon">📅</div><div><span>Appointments</span><strong><?= $appt ?></strong></div></div>
    <div class="notification-stat"><div class="stat-icon">💬</div><div><span>Messages</span><strong><?= $msg ?></strong></div></div>
  </div>
  <div class="notification-controls">
    <div class="notification-tabs">
      <button class="notification-tab active" data-filter="all">All <span><?= count($items) ?></span></button>
      <button class="notification-tab" data-filter="unread">Unread <span><?= $unreadCount ?></span></button>
      <button class="notification-tab" data-filter="appointment">Appointments</button>
      <button class="notification-tab" data-filter="message">Messages</button>
      <button class="notification-tab" data-filter="system">System</button>
    </div>
    <div class="notification-search"><span>🔍</span><input type="text" id="notificationSearch" placeholder="Search notifications..."></div>
  </div>
  <section class="notification-card">
    <div class="notification-card-header"><h2>Recent Notifications</h2><span id="notificationResult"><?= count($items) ?> notifications</span></div>
    <div class="notification-list" id="notificationList">
      <?php foreach ($items as $i): ?>
      <article class="notification-item <?= $i["read_at"] ? "" : "unread" ?>" data-type="<?= $cat($i["type"]) ?>" data-id="<?= (int)$i["id"] ?>">
        <div class="notification-icon appointment-icon"><?= $icon($i["type"]) ?></div>
        <div class="notification-content"><div class="notification-title-row"><h3><?= h($i["title"]) ?></h3><?php if (!$i["read_at"]): ?><span class="unread-dot"></span><?php endif; ?></div>
          <p><?= h($i["message"]) ?></p><span class="notification-time"><?= h(fmt_date($i["created_at"])) ?> <?= h(fmt_time($i["created_at"])) ?></span></div>
        <div class="notification-actions"><button class="read-btn" title="Mark as read">✓</button><button class="delete-btn" title="Delete">🗑</button></div>
      </article>
      <?php endforeach; ?>
    </div>
    <div class="empty-state" id="emptyState" style="<?= $items ? "display:none" : "" ?>"><div class="empty-icon">🔔</div><h3>No notifications found</h3><p>You're all caught up.</p></div>
  </section>
</div>
</main>

</div>


<script src="../assets/js/counselor-notifications.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>