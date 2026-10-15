<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$with = filter_input(INPUT_GET, "with", FILTER_VALIDATE_INT);
if ($with) {
    $chk = $pdo->prepare("SELECT u.id FROM users u INNER JOIN roles r ON r.id=u.role_id
        WHERE u.id = ? AND r.name = 'counsellor' AND u.status = 'active'");
    $chk->execute([$with]);
    if ($chk->fetch()) { $convId = get_or_create_conversation($pdo, $meId, $with); redirect("messages.php?c=" . $convId); }
}
$list = $pdo->prepare("SELECT c.id, u.name,
      (SELECT message FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_message,
      (SELECT created_at FROM messages m WHERE m.conversation_id = c.id ORDER BY m.id DESC LIMIT 1) AS last_at,
      (SELECT COUNT(*) FROM messages m WHERE m.conversation_id = c.id AND m.receiver_id = ? AND m.read_at IS NULL) AS unread
    FROM conversations c INNER JOIN users u ON u.id = c.counselor_id WHERE c.client_id = ?
    ORDER BY COALESCE(last_at, c.created_at) DESC");
$list->execute([$meId, $meId]);
$convs = $list->fetchAll();
$activeId = filter_input(INPUT_GET, "c", FILTER_VALIDATE_INT) ?: ($convs[0]["id"] ?? 0);
$active = null;
foreach ($convs as $cv) { if ((int)$cv["id"] === (int)$activeId) $active = $cv; }
$msgs = [];
if ($active) {
    $pdo->prepare("UPDATE messages SET read_at = NOW() WHERE conversation_id = ? AND receiver_id = ? AND read_at IS NULL")->execute([$activeId, $meId]);
    $m = $pdo->prepare("SELECT id, sender_id, message, created_at FROM messages WHERE conversation_id = ? ORDER BY id ASC LIMIT 200");
    $m->execute([$activeId]);
    $msgs = $m->fetchAll();
}
// Counsellors the client can start a conversation with
$counsellors = $pdo->query("SELECT u.id, u.name FROM users u INNER JOIN roles r ON r.id=u.role_id
    WHERE r.name='counsellor' AND u.status='active' ORDER BY u.name")->fetchAll();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/messages.css">
</head>

<body>

<div class="dashboard-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <a href="../index.php" class="logo">
                <span>
                    <img src="../assets/images/log.jpeg" alt="I Can Hear You">
                </span>
                <strong>I Can Hear You Rwanda</strong>
            </a>
        </div>

        <div class="user-mini-profile">
            <div class="user-avatar"><?= h($meInitials) ?></div>

            <div>
                <h4><?= h($meName) ?></h4>
                <span>Client</span>
            </div>
        </div>

        <nav class="sidebar-nav">

            <p class="nav-title">MAIN MENU</p>

            <a href="dashboard.php" class="sidebar-link">

                <span>🏠</span> Dashboard </a>

            <a href="find-counselor.php" class="sidebar-link">

                <span>🧑‍⚕️</span>Find Counsellor</a>

            <a href="bookings.php" class="sidebar-link"> <span>📅</span> My Bookings</a>

            <a href="messages.php"class="sidebar-link active"> <span>💬</span>Messages
 <?php if ($unreadMsgs > 0): ?><span class="notification-count"><?= (int)$unreadMsgs ?></span><?php endif; ?></a>

            <a href="sessions.php" class="sidebar-link"><span>🎥</span>My Sessions </a>

            <p class="nav-title">SUPPORT</p>

            <a href="resources.php" class="sidebar-link"><span>📚</span>Resources</a>

            <a href="profile.php" class="sidebar-link"><span>👤</span>My Profile</a>

            <a href="notifications.php" class="sidebar-link"> <span>🔔</span> Notifications</a>

            <a href="settings.php" class="sidebar-link"> <span>⚙️</span>Settings </a>

            <div class="sidebar-bottom">
                <a href="../pages/logout.php">
                    <span class="nav-icon">↪</span>
                    Logout
                </a>
            </div>

        </nav>

    </aside>


    <!-- MAIN CONTENT -->
    <main class="dashboard-main" style="margin-left: 250px;">

        <!-- TOP HEADER -->
        <header class="dashboard-header">

            <button class="mobile-menu" id="mobileMenu">
                ☰
            </button>

            <div class="header-search">
                <span>🔍</span>
                <input
                    type="text"
                    id="messageSearch"
                    placeholder="Search conversations..."
                >
            </div>

            <div class="header-actions">

                <button class="notification-btn">
                    🔔
                    <span></span>
                </button>

                <div class="header-profile">
                    <div class="header-avatar"><?= h($meInitials) ?></div>

                    <div>
                        <strong><?= h($meName) ?></strong>
                        <small>Client</small>
                    </div>
                </div>

            </div>

        </header>


        <!-- MESSAGES -->
        <section class="messages-page" data-csrf="<?= h($csrf) ?>" data-conversation="<?= (int)$activeId ?>" data-me="<?= $meId ?>" data-role="client">
  <div class="page-heading"><div><h1>Messages</h1><p>Communicate privately with your counsellors.</p></div></div>
  <div class="messages-container">
    <aside class="conversation-sidebar">
      <div class="conversation-header"><h2>Conversations</h2><button class="new-message-btn" id="newMessageBtn" title="New conversation">+</button></div>
      <div id="newConversationBox" style="display:none;padding:8px 12px">
        <select id="newConversationSelect" style="width:100%"><option value="">Choose a counsellor…</option>
          <?php foreach ($counsellors as $c): ?><option value="<?= (int)$c["id"] ?>"><?= h($c["name"]) ?></option><?php endforeach; ?></select></div>
      <div class="conversation-search"><span>🔍</span><input type="text" id="conversationSearch" placeholder="Search..."></div>
      <div class="conversation-list" id="conversationList">
        <?php foreach ($convs as $cv): ?>
        <div class="conversation-item <?= (int)$cv["id"] === (int)$activeId ? "active" : "" ?>" data-name="<?= h($cv["name"]) ?>" data-id="<?= (int)$cv["id"] ?>" onclick="location.href='messages.php?c=<?= (int)$cv["id"] ?>'">
          <div class="conversation-avatar"><?= h(initials($cv["name"])) ?></div>
          <div class="conversation-info"><div class="conversation-top"><h3><?= h($cv["name"]) ?></h3><span><?= h(fmt_ago($cv["last_at"])) ?></span></div>
            <div class="conversation-bottom"><p><?= h(mb_strimwidth($cv["last_message"] ?? "No messages yet", 0, 36, "…")) ?></p>
              <?php if ((int)$cv["unread"] > 0): ?><span class="unread-count"><?= (int)$cv["unread"] ?></span><?php endif; ?></div></div>
        </div>
        <?php endforeach; ?>
      </div>
    </aside>
    <section class="chat-area">
      <?php if ($active): ?>
      <div class="chat-header"><div class="chat-person"><div class="chat-avatar"><?= h(initials($active["name"])) ?></div><div><h2><?= h($active["name"]) ?></h2></div></div></div>
      <div class="chat-messages" id="chatMessages">
        <?php foreach ($msgs as $m): $mine = (int)$m["sender_id"] === $meId; ?>
        <div class="message-row <?= $mine ? "sent" : "received" ?>" data-id="<?= (int)$m["id"] ?>">
          <?php if (!$mine): ?><div class="message-avatar"><?= h(initials($active["name"])) ?></div><?php endif; ?>
          <div class="message-content"><div class="message-bubble"><?= nl2br(h($m["message"])) ?></div><span class="message-time"><?= h(fmt_time($m["created_at"])) ?></span></div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="message-input-area">
        <input type="text" id="messageInput" placeholder="Type your message..." autocomplete="off" maxlength="4000">
        <button class="send-btn" id="sendMessage">➤</button>
      </div>
      <?php else: ?>
      <div class="chat-messages"><p class="profile-message" style="padding:24px">No conversation selected. Press “+” to start one.</p></div>
      <?php endif; ?>
    </section>
  </div>
</section>


    </main>

</div>


<script src="../assets/js/messages.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>