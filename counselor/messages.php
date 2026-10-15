<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("counsellor");
require_once __DIR__ . "/../includes/page_init.php";
$with = filter_input(INPUT_GET, "with", FILTER_VALIDATE_INT);
if ($with) {
    $chk = $pdo->prepare("SELECT 1 FROM bookings WHERE client_id = ? AND counselor_id = ? LIMIT 1");
    $chk->execute([$with, $meId]);
    if ($chk->fetch()) redirect("messages.php?c=" . get_or_create_conversation($pdo, $with, $meId));
}
$list = $pdo->prepare("SELECT c.id, u.name,
      (SELECT message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message,
      (SELECT created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_at,
      (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.receiver_id = ? AND m.read_at IS NULL) AS unread
    FROM conversations c INNER JOIN users u ON u.id = c.client_id WHERE c.counselor_id = ?
    ORDER BY COALESCE(last_at, c.created_at) DESC");
$list->execute([$meId, $meId]);
$convs = $list->fetchAll();
$activeId = filter_input(INPUT_GET, "c", FILTER_VALIDATE_INT) ?: ($convs[0]["id"] ?? 0);
$active = null;
foreach ($convs as $cv) if ((int)$cv["id"] === (int)$activeId) $active = $cv;
$msgs = [];
if ($active) {
    $pdo->prepare("UPDATE messages SET read_at = NOW() WHERE conversation_id = ? AND receiver_id = ? AND read_at IS NULL")->execute([$activeId, $meId]);
    $m = $pdo->prepare("SELECT id, sender_id, message, created_at FROM messages WHERE conversation_id = ? ORDER BY id ASC LIMIT 200");
    $m->execute([$activeId]);
    $msgs = $m->fetchAll();
}
$myClients = $pdo->prepare("SELECT DISTINCT u.id, u.name FROM bookings b INNER JOIN users u ON u.id = b.client_id WHERE b.counselor_id = ? ORDER BY u.name");
$myClients->execute([$meId]);
$myClients = $myClients->fetchAll();
$totalUnread = array_sum(array_column($convs, "unread"));
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf" content="<?= h($csrf) ?>">

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Messages - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor-messages.css">

</head>


<body>


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar" id="sidebar">


    <!-- Logo -->

    <div class="sidebar-logo">

        <a href="dashboard.php" class="logo">

            <span>

                <img
                    src="../assets/images/log.jpeg"
                    alt="I Can Hear You"
                >

            </span>

            <strong>I Can Hear You Rwanda</strong>

        </a>

    </div>


    <!-- Main Menu -->

    <div class="sidebar-section">

        <span class="sidebar-section-title">
            Main Menu
        </span>


        <nav class="sidebar-nav">


            <a href="dashboard.php">

                <span>📊</span>

                <span>Dashboard</span>

            </a>


            <a href="clients.php">

                <span>👥</span>

                <span>My Clients</span>

            </a>


            <a href="bookings.php">

                <span>📅</span>

                <span>Bookings</span>

            </a>


            <a href="messages.php"  class="active">

                <span>💬</span>

                <span>Messages</span>

                <?php if ($unreadMsgs > 0): ?><span class="nav-badge"><?= (int)$unreadMsgs ?></span><?php endif; ?>

            </a>


            <a href="sessions.php">

                <span>🎥</span>

                <span>Sessions</span>

            </a>


        </nav>

    </div>


    <!-- Management -->

    <div class="sidebar-section">

        <span class="sidebar-section-title">
            Management
        </span>


        <nav class="sidebar-nav">


            <a href="session-notes.php">

                <span>📝</span>

                <span>Session Notes</span>

            </a>


            <a href="resources.php">

                <span>📚</span>

                <span>Resources</span>

            </a>


        </nav>

    </div>


    <!-- Account -->

    <div class="sidebar-section">

        <span class="sidebar-section-title">
            Account
        </span>


        <nav class="sidebar-nav">


            <a href="profile.php">

                <span>👤</span>

                <span>My Profile</span>

            </a>


            <a href="notifications.php">

                <span>🔔</span>

                <span>Notifications</span>

            </a>


            <a href="settings.php">

                <span>⚙️</span>

                <span>Settings</span>

            </a>


        </nav>

    </div>


    <!-- Logout -->

    <div class="sidebar-bottom">

        <a
            href="../pages/logout.php"
            class="logout-link"
        >

            <span>🚪</span>

            <span>Logout</span>

        </a>

    </div>


</aside>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<main class="dashboard-main">


    <!-- HEADER -->

    <header class="dashboard-header" style="margin-left: 250px;">


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
                placeholder="Search clients, sessions..."
            >

        </div>


        <div class="header-right">


            <button class="notification-btn">

                🔔

                <span class="notification-dot"></span>

            </button>


            <div class="header-profile">


                <div class="profile-avatar">
                    <?= h($meInitials) ?>
                </div>


                <div>

                    <strong>
                        Dr. Jean Mukamana
                    </strong>

                    <small>
                        Counsellor
                    </small>

                </div>


            </div>


        </div>


    </header>



    <!-- =================================================
         MESSAGES PAGE
    ================================================= -->

    <section class="messages-page" data-csrf="<?= h($csrf) ?>" data-conversation="<?= (int)$activeId ?>" data-me="<?= $meId ?>" data-role="counsellor" data-layout="counselor">
  <div class="messages-page-header"><div><h1>Messages</h1><p>Communicate securely with your clients.</p></div>
    <button class="new-message-btn" id="newMessageBtn">✏️ New Message</button></div>
  <div id="newConversationBox" style="display:none;margin:0 0 12px">
    <select id="newConversationSelect"><option value="">Choose a client…</option>
      <?php foreach ($myClients as $c): ?><option value="<?= (int)$c["id"] ?>"><?= h($c["name"]) ?></option><?php endforeach; ?></select></div>
  <div class="messaging-container">
    <aside class="conversation-panel">
      <div class="conversation-header"><h2>Conversations</h2><span><?= (int)$totalUnread ?> unread</span></div>
      <div class="conversation-search"><span>🔍</span><input type="text" id="conversationSearch" placeholder="Search conversations..."></div>
      <div class="conversation-list" id="conversationList">
        <?php foreach ($convs as $cv): ?>
        <div class="conversation-item <?= (int)$cv["id"] === (int)$activeId ? "active" : "" ?>" data-name="<?= h($cv["name"]) ?>" onclick="location.href='messages.php?c=<?= (int)$cv["id"] ?>'">
          <div class="conversation-avatar"><?= h(initials($cv["name"])) ?></div>
          <div class="conversation-info"><div class="conversation-top"><strong><?= h($cv["name"]) ?></strong><small><?= h(fmt_ago($cv["last_at"])) ?></small></div>
            <div class="conversation-bottom"><p><?= h(mb_strimwidth($cv["last_message"] ?? "No messages yet", 0, 30, "…")) ?></p>
              <?php if ((int)$cv["unread"] > 0): ?><span class="unread-count"><?= (int)$cv["unread"] ?></span><?php endif; ?></div></div>
        </div>
        <?php endforeach; ?>
        <?php if (!$convs): ?><p class="profile-message" style="padding:16px">No conversations yet.</p><?php endif; ?>
      </div>
    </aside>
    <section class="chat-panel">
      <?php if ($active): ?>
      <div class="chat-header"><div class="chat-user"><div class="chat-avatar"><?= h(initials($active["name"])) ?></div><div><strong><?= h($active["name"]) ?></strong></div></div></div>
      <div class="security-notice">🔒 <span>Messages are private and securely transmitted.</span></div>
      <div class="chat-messages" id="chatMessages">
        <?php foreach ($msgs as $m): $mine = (int)$m["sender_id"] === $meId; ?>
        <div class="message <?= $mine ? "sent" : "received" ?>" data-id="<?= (int)$m["id"] ?>">
          <?php if (!$mine): ?><div class="message-avatar"><?= h(initials($active["name"])) ?></div><?php endif; ?>
          <div class="message-content"><div class="message-bubble"><?= nl2br(h($m["message"])) ?></div><span class="message-time"><?= h(fmt_time($m["created_at"])) ?></span></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="message-input-area"><textarea id="messageInput" rows="1" placeholder="Write a message..." maxlength="4000"></textarea><button class="send-btn" id="sendBtn">➤</button></div>
      <?php else: ?><div class="chat-messages"><p class="profile-message" style="padding:24px">Select a conversation or start a new one.</p></div><?php endif; ?>
    </section>
  </div>
</section>


</main>



<script src="../assets/js/dashboard.js"></script>

<script src="../assets/js/messages.js"></script>


<script src="../assets/js/app.js"></script>
</body>

</html>