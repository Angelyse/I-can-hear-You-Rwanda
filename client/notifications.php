<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$n = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND status = 'sent' ORDER BY created_at DESC LIMIT 100");
$n->execute([$meId]);
$items = $n->fetchAll();
$unreadCount = count(array_filter($items, fn($i) => !$i["read_at"]));
$apptCount = count(array_filter($items, fn($i) => $i["type"] === "booking"));
function notif_category(string $t): string { return $t === "booking" ? "appointment" : ($t === "message" ? "message" : "system"); }
function notif_icon(string $t): string { return $t === "booking" ? "📅" : ($t === "message" ? "💬" : "🔔"); }
?>


<?php
$pageTitle = "Notifications";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">
</head>
<body>

<div class="dashboard-layout">

    <!-- ================= SIDEBAR ================= -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <a href="../index.php" class="logo">
                <span>
                    <img src="../assets/images/log.jpeg" alt="I Can Hear You">
                </span>
                <strong>I Can Hear You Rwanda</strong>
            </a>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar"><?= h($meInitials) ?></div>
            <div>
                <h4><?= h($meName) ?></h4>
                <p>Client</p>
            </div>
        </div>

        <!-- Main Menu -->
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

            <a href="notifications.php" class="sidebar-link active"> <span>🔔</span> Notifications</a>

            <a href="settings.php" class="sidebar-link"> <span>⚙️</span>Settings </a>

            </div>
        </div>

        <div class="sidebar-bottom">
            <a href="../pages/logout.php" class="logout-link">
                <span>🚪</span>
                Logout
            </a>
        </div>

    </aside>


    <!-- ================= MAIN CONTENT ================= -->
    <section class="notifications-page" data-csrf="<?= h($csrf) ?>">
  <div class="notifications-header"><div class="notifications-title"><h1>Notifications</h1><p>Stay updated with your counselling activities and important information.</p></div>
    <button id="markAllRead" class="mark-all-btn">✓ Mark all as read</button></div>
  <div class="notification-summary">
    <div class="summary-card"><div class="summary-icon">🔔</div><div class="summary-info"><span>Total Notifications</span><strong id="totalNotifications"><?= count($items) ?></strong></div></div>
    <div class="summary-card"><div class="summary-icon">📩</div><div class="summary-info"><span>Unread Notifications</span><strong id="unreadNotifications"><?= $unreadCount ?></strong></div></div>
    <div class="summary-card"><div class="summary-icon">📅</div><div class="summary-info"><span>Appointments</span><strong><?= $apptCount ?></strong></div></div>
  </div>
  <div class="notifications-container">
    <div class="notifications-section-header"><div><h2>Recent Notifications</h2><p>Manage your latest updates and messages.</p></div></div>
    <div class="notification-tabs">
      <button class="notification-tab active" data-filter="all">All</button>
      <button class="notification-tab" data-filter="unread">Unread <span class="tab-count"><?= $unreadCount ?></span></button>
      <button class="notification-tab" data-filter="appointment">Appointments</button>
      <button class="notification-tab" data-filter="message">Messages</button>
      <button class="notification-tab" data-filter="system">System</button>
    </div>
    <div class="notifications-list" id="notificationsList">
      <?php foreach ($items as $i): ?>
      <div class="notification-item <?= $i["read_at"] ? "" : "unread" ?>" data-category="<?= notif_category($i["type"]) ?>" data-id="<?= (int)$i["id"] ?>">
        <div class="notification-icon appointment-icon"><?= notif_icon($i["type"]) ?></div>
        <div class="notification-content">
          <div class="notification-top"><h3><?= h($i["title"]) ?></h3><?php if (!$i["read_at"]): ?><span class="unread-dot"></span><?php endif; ?></div>
          <p><?= h($i["message"]) ?></p>
          <div class="notification-meta"><span><?= h(fmt_date($i["created_at"])) ?> <?= h(fmt_time($i["created_at"])) ?></span></div>
        </div>
        <div class="notification-actions"><button class="read-btn" title="Mark as read">✓</button><button class="delete-btn" title="Delete">🗑</button></div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="empty-notifications" id="emptyNotifications" <?= $items ? 'style="display:none"' : "" ?>><h3>You're all caught up</h3><p>No notifications yet.</p></div>
  </div>
</section>


    </main>

</div>


<script src="../assets/js/dashboard.js"></script>
<script src="../assets/js/notifications.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>