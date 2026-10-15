<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("counsellor");
require_once __DIR__ . "/../includes/page_init.php";
$sid = filter_input(INPUT_GET, "session", FILTER_VALIDATE_INT);
$cid = filter_input(INPUT_GET, "client", FILTER_VALIDATE_INT);
$base = "SELECT s.id AS session_id, s.session_type, b.booking_date, b.start_time, b.end_time, b.client_id, u.name AS client_name,
      n.id AS note_id, n.discussion, n.tools_used, n.progress, n.next_steps, n.updated_at
    FROM sessions s INNER JOIN bookings b ON b.id = s.booking_id INNER JOIN users u ON u.id = b.client_id
    LEFT JOIN session_notes n ON n.session_id = s.id AND n.counselor_id = ?
    WHERE b.counselor_id = ? AND s.status = 'completed'";
$params = [$meId, $meId];
if ($cid) { $base .= " AND b.client_id = ?"; $params[] = $cid; }
$st = $pdo->prepare($base . " ORDER BY b.booking_date DESC, b.start_time DESC");
$st->execute($params);
$rows = $st->fetchAll();
$edit = null;
if ($sid) foreach ($rows as $r) if ((int)$r["session_id"] === $sid) $edit = $r;
$total = count(array_filter($rows, fn($r) => $r["note_id"]));
$pending = count($rows) - $total;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Session Notes | I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor-session-notes.css">

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

                <div>
                    <strong>I Can Hear You</strong>
                    <small>Rwanda</small>
                </div>

            </a>

        </div>


        <!-- Main Menu -->
        <div class="sidebar-section">

            <span class="sidebar-section-title">Main Menu</span>

            <nav class="sidebar-nav">

                <a href="dashboard.php">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>

                <a href="clients.php">
                    <i class="fa-solid fa-users"></i>
                    <span>My Clients</span>
                </a>

                <a href="bookings.php">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>

                <a href="messages.php">
                    <i class="fa-solid fa-comments"></i>
                    <span>Messages</span>
                    <?php if ($unreadMsgs > 0): ?><span class="nav-badge"><?= (int)$unreadMsgs ?></span><?php endif; ?>
                </a>

                <a href="sessions.php">
                    <i class="fa-solid fa-video"></i>
                    <span>Sessions</span>
                </a>

            </nav>

        </div>


        <!-- Management -->
        <div class="sidebar-section">

            <span class="sidebar-section-title">Management</span>

            <nav class="sidebar-nav">

                <a href="session-notes.php"  class="active">
                    <i class="fa-solid fa-note-sticky"></i>
                    <span>Session Notes</span>
                </a>

                <a href="resources.php">
                    <i class="fa-solid fa-book-open"></i>
                    <span>Resources</span>
                </a>

            </nav>

        </div>


        <!-- Account -->
        <div class="sidebar-section">

            <span class="sidebar-section-title">Account</span>

            <nav class="sidebar-nav">

                <a href="profile.php">
                    <i class="fa-solid fa-user"></i>
                    <span>My Profile</span>
                </a>

                <a href="notifications.php">
                    <i class="fa-solid fa-bell"></i>
                    <span>Notifications</span>
                </a>

                <a href="settings.php">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>

                <a href="../pages/logout.php" class="logout-link">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>

            </nav>

        </div>

    </aside>


    <!-- ================= MAIN ================= -->
    <main class="dashboard-main">

        <!-- Header -->
        <header class="dashboard-header" style="margin-left: 250px;">

            <button class="mobile-menu" id="mobileMenu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="header-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    placeholder="Search notes..."
                    id="globalSearch"
                >

            </div>


            <div class="header-actions">

                <button class="header-icon notification-btn">

                    <i class="fa-regular fa-bell"></i>

                    <span class="notification-dot"></span>

                </button>


                <div class="header-profile">

                    <div class="header-avatar">
                        <?= h($meInitials) ?>
                    </div>

                    <div class="header-user-info">

                        <strong>Dr. Jean Mukamana</strong>

                        <span>Counsellor</span>

                    </div>

                    <i class="fa-solid fa-chevron-down"></i>

                </div>

            </div>

        </header>


        <!-- ================= PAGE ================= -->
        <div class="notes-page" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="page-heading"><div><h1>Session Notes</h1><p>Record and manage notes from your completed counselling sessions.</p></div></div>
  <div class="notes-stats">
    <div class="note-stat-card"><div class="note-stat-icon total"><i class="fa-solid fa-note-sticky"></i></div><div><span>Total Notes</span><strong><?= $total ?></strong></div></div>
    <div class="note-stat-card"><div class="note-stat-icon pending"><i class="fa-solid fa-file-circle-exclamation"></i></div><div><span>Pending Notes</span><strong><?= $pending ?></strong></div></div>
  </div>

  <?php if ($edit): ?>
  <div class="note-card" style="margin-bottom:20px">
    <div class="note-card-header"><div class="note-client"><div class="note-avatar"><?= h(initials($edit["client_name"])) ?></div><div><h3><?= h($edit["client_name"]) ?></h3><span><?= h(fmt_date($edit["booking_date"])) ?>, <?= h(fmt_time($edit["start_time"])) ?></span></div></div></div>
    <form method="post" action="../api/sessions.php">
      <?= csrf_field() ?><input type="hidden" name="action" value="save_note"><input type="hidden" name="session_id" value="<?= (int)$edit["session_id"] ?>">
      <?php foreach (["discussion" => "Discussion", "tools_used" => "Tools / techniques used", "progress" => "Progress", "next_steps" => "Next steps"] as $f => $l): ?>
      <div class="form-group"><label for="f_<?= $f ?>"><?= $l ?></label><textarea id="f_<?= $f ?>" name="<?= $f ?>" rows="3"><?= h($edit[$f]) ?></textarea></div>
      <?php endforeach; ?>
      <button type="submit" class="note-action primary">Save Note</button>
      <a href="session-notes.php" class="note-action">Close</a>
    </form>
  </div>
  <?php endif; ?>

  <div class="notes-controls">
    <div class="notes-search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="notesSearch" placeholder="Search by client..."></div>
    <select id="notesFilter"><option value="all">All Notes</option><option value="done">Completed notes</option><option value="pending">Pending</option></select>
    <input type="date" id="notesDate">
  </div>
  <div class="notes-container" id="notesContainer">
    <?php foreach ($rows as $r): $has = (bool)$r["note_id"]; ?>
    <div class="note-card <?= $has ? "" : "pending-card" ?>" data-client="<?= h(mb_strtolower($r["client_name"])) ?>" data-date="<?= h($r["booking_date"]) ?>" data-status="<?= $has ? "done" : "pending" ?>">
      <div class="note-card-header"><div class="note-client"><div class="note-avatar"><?= h(initials($r["client_name"])) ?></div><div><h3><?= h($r["client_name"]) ?></h3><span><?= $r["session_type"] === "video" ? "Video" : "Chat" ?> session</span></div></div>
        <div class="note-date"><?= h(fmt_date($r["booking_date"])) ?></div></div>
      <?php if ($has): ?>
        <div class="note-preview"><h4>Discussion</h4><p><?= h(mb_strimwidth((string)$r["discussion"], 0, 160, "…")) ?></p></div>
        <div class="progress-preview"><div><strong>Progress</strong><span><?= h(mb_strimwidth((string)$r["progress"], 0, 120, "…")) ?></span></div></div>
      <?php else: ?>
        <div class="pending-message"><div><strong>Session note pending</strong><p>Please complete the note for this session.</p></div></div>
      <?php endif; ?>
      <div class="note-actions"><a class="note-action <?= $has ? "" : "primary" ?>" href="session-notes.php?session=<?= (int)$r["session_id"] ?>"><?= $has ? "Edit" : "Add Note" ?></a></div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="empty-notes" id="emptyNotes" <?= $rows ? "" : 'style="display:block"' ?>><h3>No completed sessions yet</h3><p>Notes can be added once a session is marked completed.</p></div>
</div>
</main>
</div>
<script src="../assets/js/dashboard.js"></script>
<script src="../assets/js/counselor-lists.js"></script>
<script src="../assets/js/app.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>
