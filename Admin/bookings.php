<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("admin");
require_once __DIR__ . "/../includes/page_init.php";
$statusF = $_GET["status"] ?? "";
$q = trim($_GET["q"] ?? "");
$where = []; $args = [];
if (in_array($statusF, ["pending","confirmed","completed","cancelled","declined"], true)) { $where[] = "b.status = ?"; $args[] = $statusF; }
if ($q !== "") { $where[] = "(cu.name LIKE ? OR pu.name LIKE ?)"; $args[] = "%$q%"; $args[] = "%$q%"; }
$st = $pdo->prepare("SELECT b.*, cu.name AS client_name, pu.name AS counselor_name FROM bookings b
    INNER JOIN users cu ON cu.id = b.client_id INNER JOIN users pu ON pu.id = b.counselor_id"
    . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY b.booking_date DESC, b.start_time DESC LIMIT 300");
$st->execute($args);
$rows = $st->fetchAll();
$c = [];
foreach ($pdo->query("SELECT status, COUNT(*) n FROM bookings GROUP BY status") as $r) $c[$r["status"]] = (int)$r["n"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Bookings Management - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-bookings.css">

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

                <strong>I Can Hear You Rwanda</strong>

            </a>

        </div>


        <div class="admin-profile">

            <div class="admin-avatar">
                AD
            </div>

            <div>
                <strong><?= h($meName) ?></strong>
                <small>System Administrator</small>
            </div>

        </div>


        <nav class="sidebar-nav">

            <!-- MAIN MENU -->

            <div class="sidebar-section">

                <span class="sidebar-section-title">
                    Main Menu
                </span>

                <a href="dashboard.php">
                    <i class="fa-solid fa-gauge"></i>
                    <span>Dashboard</span>
                </a>

                <a href="users.php">
                    <i class="fa-solid fa-users"></i>
                    <span>Users</span>
                </a>

                <a href="counsellors.php">
                    <i class="fa-solid fa-user-doctor"></i>
                    <span>Counsellors</span>
                </a>

                <a href="clients.php">
                    <i class="fa-solid fa-user-group"></i>
                    <span>Clients</span>
                </a>

                <a href="bookings.php"  class="active">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>

                <a href="sessions.php">
                    <i class="fa-solid fa-video"></i>
                    <span>Sessions</span>
                </a>

            </div>


            <!-- MANAGEMENT -->

            <div class="sidebar-section">

                <span class="sidebar-section-title">
                    Management
                </span>

                <a href="resources.php">
                    <i class="fa-solid fa-book"></i>
                    <span>Resources</span>
                </a>

                <a href="programs.php">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>Programs</span>
                </a>

                <a href="schools.php">
                    <i class="fa-solid fa-school"></i>
                    <span>Schools</span>
                </a>

                <a href="organizations.php">
                    <i class="fa-solid fa-building"></i>
                    <span>Organizations</span>
                </a>

            </div>


            <!-- REPORTS -->

            <div class="sidebar-section">

                <span class="sidebar-section-title">
                    Reports
                </span>

                <a href="reports.php">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Reports & Analytics</span>
                </a>

            </div>


            <!-- SYSTEM -->

            <div class="sidebar-section">

                <span class="sidebar-section-title">
                    System
                </span>

                <a href="notifications.php">
                    <i class="fa-solid fa-bell"></i>
                    <span>Notifications</span>
                </a>

                <a href="settings.php">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>

                <a href="../pages/logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>

            </div>

        </nav>

    </aside>


    <!-- ================= MAIN ================= -->

    <main class="dashboard-main">

        <!-- HEADER -->

        <header class="top-header">

            <button class="mobile-menu" id="mobileMenu">
                <i class="fa-solid fa-bars"></i>
            </button>


            <div class="header-search">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="headerSearch"
                    placeholder="Search bookings..."
                >

            </div>


            <div class="header-right">

                <button class="notification-btn">

                    <i class="fa-regular fa-bell"></i>

                    <span>4</span>

                </button>


                <div class="header-user">

                    <div class="admin-avatar small">
                        AD
                    </div>

                    <div>
                        <strong><?= h($meName) ?></strong>
                        <small>Administrator</small>
                    </div>

                </div>

            </div>

        </header>


        <!-- ================= PAGE ================= -->

        <section class="bookings-page" data-csrf="<?= h($csrf) ?>">
  <link rel="stylesheet" href="../assets/css/admin-extra.css">
  <?= flash_html() ?>
  <div class="page-header"><div><h1>Bookings Management</h1><p>Monitor and manage all counselling bookings.</p></div></div>
  <div class="admin-stats">
    <div class="admin-stat"><span>Total</span><strong><?= array_sum($c) ?></strong></div>
    <?php foreach (["pending","confirmed","completed","cancelled"] as $k): ?><div class="admin-stat"><span><?= ucfirst($k) ?></span><strong><?= (int)($c[$k] ?? 0) ?></strong></div><?php endforeach; ?>
  </div>
  <div class="admin-box">
    <form class="admin-filters" method="get">
      <input type="text" name="q" value="<?= h($q) ?>" placeholder="Search client or counsellor...">
      <select name="status"><option value="">All status</option><?php foreach (["pending","confirmed","completed","cancelled","declined"] as $k): ?><option value="<?= $k ?>" <?= $statusF === $k ? "selected" : "" ?>><?= ucfirst($k) ?></option><?php endforeach; ?></select>
      <button class="admin-btn secondary" type="submit">Filter</button>
    </form>
    <div style="overflow-x:auto"><table class="admin-table">
      <thead><tr><th>#</th><th>Client</th><th>Counsellor</th><th>Date</th><th>Time</th><th>Type</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr><td><?= (int)$r["id"] ?></td><td><?= h($r["client_name"]) ?></td><td><?= h($r["counselor_name"]) ?></td>
          <td><?= h(fmt_date($r["booking_date"])) ?></td><td><?= h(fmt_time($r["start_time"])) ?></td><td><?= h(ucfirst($r["session_type"])) ?></td>
          <td><span class="status <?= h($r["status"]) ?>"><?= h(ucfirst($r["status"])) ?></span></td>
          <td>
            <?php if (in_array($r["status"], ["pending", "confirmed"])): ?>
            <form class="admin-inline" method="post" action="booking-actions.php" onsubmit="return confirm('Cancel this booking? Both users will be notified.')">
              <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int)$r["id"] ?>"><input type="hidden" name="action" value="cancel"><button class="admin-btn danger" type="submit">Cancel</button></form>
            <?php endif; ?>
            <?php if ($r["status"] === "confirmed"): ?>
            <form class="admin-inline" method="post" action="booking-actions.php">
              <?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int)$r["id"] ?>"><input type="hidden" name="action" value="complete"><button class="admin-btn secondary" type="submit">Complete</button></form>
            <?php endif; ?>
          </td></tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="8">No bookings found.</td></tr><?php endif; ?>
      </tbody></table></div>
  </div>
</section>
</main>
</div>
<script src="../assets/js/app.js"></script>

</body>
</html>
