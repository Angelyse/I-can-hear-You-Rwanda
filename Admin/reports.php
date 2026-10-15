<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("admin");
require_once __DIR__ . "/../includes/page_init.php";
$from = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET["from"] ?? "") ? $_GET["from"] : date("Y-m-01", strtotime("-5 months"));
$to = preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET["to"] ?? "") ? $_GET["to"] : date("Y-m-d");

$byStatus = $pdo->prepare("SELECT status, COUNT(*) n FROM bookings WHERE booking_date BETWEEN ? AND ? GROUP BY status");
$byStatus->execute([$from, $to]);
$byStatus = $byStatus->fetchAll(PDO::FETCH_KEY_PAIR);

$monthly = $pdo->prepare("SELECT DATE_FORMAT(booking_date, '%Y-%m') AS m, COUNT(*) total, SUM(status='completed') done
    FROM bookings WHERE booking_date BETWEEN ? AND ? GROUP BY m ORDER BY m");
$monthly->execute([$from, $to]);
$monthly = $monthly->fetchAll();

$top = $pdo->prepare("SELECT u.name, COUNT(*) AS sessions,
      (SELECT ROUND(AVG(f.rating),1) FROM feedback f INNER JOIN sessions s ON s.id = f.session_id INNER JOIN bookings b2 ON b2.id = s.booking_id WHERE b2.counselor_id = u.id) AS rating
    FROM bookings b INNER JOIN users u ON u.id = b.counselor_id
    WHERE b.status = 'completed' AND b.booking_date BETWEEN ? AND ? GROUP BY u.id, u.name ORDER BY sessions DESC LIMIT 10");
$top->execute([$from, $to]);
$top = $top->fetchAll();

$usersByRole = $pdo->query("SELECT r.name, COUNT(*) n FROM users u INNER JOIN roles r ON r.id = u.role_id GROUP BY r.name")->fetchAll(PDO::FETCH_KEY_PAIR);
$avgRating = $pdo->query("SELECT ROUND(AVG(rating),2) FROM feedback")->fetchColumn();
$newUsers = $pdo->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at) BETWEEN ? AND ?");
$newUsers->execute([$from, $to]);
$newUsers = (int)$newUsers->fetchColumn();
$totalBookings = array_sum($byStatus);
$completionRate = $totalBookings ? round(($byStatus["completed"] ?? 0) / $totalBookings * 100) : 0;

if (($_GET["export"] ?? "") === "csv") {
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=bookings-report-$from-to-$to.csv");
    $out = fopen("php://output", "w");
    fputcsv($out, ["Month", "Total bookings", "Completed"]);
    foreach ($monthly as $m) fputcsv($out, [$m["m"], $m["total"], $m["done"]]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reports & Analytics - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
     <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-reports.css">

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >
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

                <a href="bookings.php">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>

                <a href="sessions.php">
                    <i class="fa-solid fa-video"></i>
                    <span>Sessions</span>
                </a>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-title">Management</span>

                <a href="resources.php">
                    <i class="fa-solid fa-book-open"></i>
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

            <div class="sidebar-section">
                <span class="sidebar-section-title">Reports</span>

                <a href="reports.php"  class="active">
                    <i class="fa-solid fa-chart-column"></i>
                    <span>Reports & Analytics</span>
                </a>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-title">System</span>

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

        <!-- Header -->
        <header class="dashboard-header">

            <div class="header-left">

                <button class="mobile-menu" id="mobileMenu">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <div class="header-search">
                    <i class="fa-solid fa-search"></i>

                    <input
                        type="text"
                        id="headerSearch"
                        placeholder="Search reports..."
                    >
                </div>

            </div>


            <div class="header-right">

                <button class="notification-btn">

                    <i class="fa-regular fa-bell"></i>

                    <span class="notification-badge">
                        4
                    </span>

                </button>


                <div class="admin-profile">

                    <div class="admin-avatar">
                        <?= h($meInitials) ?>
                    </div>

                    <div class="admin-profile-info">
                        <strong><?= h($meName) ?></strong>
                        <span>System Administrator</span>
                    </div>

                    <i class="fa-solid fa-chevron-down"></i>

                </div>

            </div>

        </header>


        <!-- ================= PAGE ================= -->
        <section class="reports-page">
  <link rel="stylesheet" href="../assets/css/admin-extra.css">
  <div class="page-header"><div><h1>Reports & Analytics</h1><p>Platform activity between <?= h(fmt_date($from)) ?> and <?= h(fmt_date($to)) ?>.</p></div></div>
  <div class="admin-box"><form class="admin-filters" method="get">
    <label>From <input type="date" name="from" value="<?= h($from) ?>"></label>
    <label>To <input type="date" name="to" value="<?= h($to) ?>"></label>
    <button class="admin-btn secondary" type="submit">Apply</button>
    <button class="admin-btn" type="submit" name="export" value="csv">Export CSV</button></form></div>
  <div class="admin-stats">
    <div class="admin-stat"><span>Bookings</span><strong><?= $totalBookings ?></strong></div>
    <div class="admin-stat"><span>Completion rate</span><strong><?= $completionRate ?>%</strong></div>
    <div class="admin-stat"><span>New users</span><strong><?= $newUsers ?></strong></div>
    <div class="admin-stat"><span>Avg. session rating</span><strong><?= $avgRating ? h($avgRating) : "—" ?></strong></div>
  </div>
  <div class="admin-box"><h2>Bookings by status</h2><table class="admin-table"><tbody>
    <?php foreach (["pending","confirmed","completed","cancelled","declined"] as $k): $n = (int)($byStatus[$k] ?? 0); ?>
      <tr><td style="width:140px"><?= ucfirst($k) ?></td><td><div style="background:#e5e7eb;border-radius:8px;height:10px;width:100%"><div style="background:#2563eb;height:10px;border-radius:8px;width:<?= $totalBookings ? round($n / $totalBookings * 100) : 0 ?>%"></div></div></td><td style="width:60px"><?= $n ?></td></tr>
    <?php endforeach; ?></tbody></table></div>
  <div class="admin-box"><h2>Bookings per month</h2><table class="admin-table"><thead><tr><th>Month</th><th>Total</th><th>Completed</th></tr></thead><tbody>
    <?php foreach ($monthly as $m): ?><tr><td><?= h($m["m"]) ?></td><td><?= (int)$m["total"] ?></td><td><?= (int)$m["done"] ?></td></tr><?php endforeach; ?>
    <?php if (!$monthly): ?><tr><td colspan="3">No data for this period.</td></tr><?php endif; ?></tbody></table></div>
  <div class="admin-box"><h2>Top counsellors (completed sessions)</h2><table class="admin-table"><thead><tr><th>Counsellor</th><th>Sessions</th><th>Rating</th></tr></thead><tbody>
    <?php foreach ($top as $t): ?><tr><td><?= h($t["name"]) ?></td><td><?= (int)$t["sessions"] ?></td><td><?= $t["rating"] ? "⭐ " . h($t["rating"]) : "—" ?></td></tr><?php endforeach; ?>
    <?php if (!$top): ?><tr><td colspan="3">No completed sessions in this period.</td></tr><?php endif; ?></tbody></table></div>
  <div class="admin-box"><h2>Users by role</h2><table class="admin-table"><tbody>
    <?php foreach ($usersByRole as $role => $n): ?><tr><td><?= h(ucfirst($role)) ?></td><td><?= (int)$n ?></td></tr><?php endforeach; ?></tbody></table></div>
</section>
</main>
</div>
<script src="../assets/js/app.js"></script>

</body>
</html>
