<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("admin");
require_once __DIR__ . "/../includes/page_init.php";
$entityKey = "organizations";
require_once __DIR__ . "/../includes/admin_entities.php";
$def = admin_entities()[$entityKey];
$search = trim($_GET["q"] ?? "");
$statusF = $_GET["status"] ?? "";
$table = $def["table"];
$where = []; $args = [];
if ($search !== "") { $where[] = "(name LIKE ? OR " . (isset($def["fields"]["location"]) ? "location" : "name") . " LIKE ?)"; $args[] = "%$search%"; $args[] = "%$search%"; }
if (isset($def["status_options"][$statusF])) { $where[] = "status = ?"; $args[] = $statusF; }
$st = $pdo->prepare("SELECT * FROM $table" . ($where ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY created_at DESC LIMIT 300");
$st->execute($args);
$rows = $st->fetchAll();
$counts = [];
foreach ($pdo->query("SELECT status, COUNT(*) c FROM $table GROUP BY status") as $r) $counts[$r["status"]] = (int)$r["c"];
$total = array_sum($counts);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Organizations Management - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-organizations.css">

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

                <a href="organizations.php"  class="active">
                    <i class="fa-solid fa-building"></i>
                    <span>Organizations</span>
                </a>
            </div>

            <div class="sidebar-section">
                <span class="sidebar-section-title">Reports</span>

                <a href="reports.php">
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


    <!-- ================= MAIN CONTENT ================= -->
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
                        placeholder="Search organizations..."
                    >
                </div>

            </div>

            <div class="header-right">

                <button class="notification-btn" title="Notifications">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notification-badge">4</span>
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


        <!-- ================= PAGE CONTENT ================= -->
        <section class="<?= h($entityKey) ?>-page" data-csrf="<?= h($csrf) ?>">
  <link rel="stylesheet" href="../assets/css/admin-extra.css">
  <?= flash_html() ?>
  <div class="page-header"><div><h1><?= h($def["plural"]) ?> Management</h1><p>Add, review and update <?= h(strtolower($def["plural"])) ?>.</p></div></div>
  <div class="admin-stats">
    <div class="admin-stat"><span>Total</span><strong><?= $total ?></strong></div>
    <?php foreach ($def["status_options"] as $k => $l): ?><div class="admin-stat"><span><?= h($l) ?></span><strong><?= (int)($counts[$k] ?? 0) ?></strong></div><?php endforeach; ?>
  </div>

  <div class="admin-box">
    <details class="admin-add"><summary>+ Add <?= h($def["title"]) ?></summary>
      <form class="admin-form" method="post" action="crud-actions.php">
        <?= csrf_field() ?><input type="hidden" name="entity" value="<?= h($entityKey) ?>"><input type="hidden" name="action" value="create">
        <div class="grid">
          <?php foreach ($def["fields"] as $key => [$label, $type, $required, $options]): ?>
          <div class="field"><label for="f_<?= h($key) ?>"><?= h($label) ?><?= $required ? " *" : "" ?></label>
            <?php if ($type === "select"): ?><select id="f_<?= h($key) ?>" name="<?= h($key) ?>"><?php foreach ($options as $v => $l): ?><option value="<?= h($v) ?>"><?= h($l) ?></option><?php endforeach; ?></select>
            <?php elseif ($type === "textarea"): ?><textarea id="f_<?= h($key) ?>" name="<?= h($key) ?>" rows="3"></textarea>
            <?php else: ?><input id="f_<?= h($key) ?>" type="<?= h($type) ?>" name="<?= h($key) ?>" <?= $required ? "required" : "" ?>><?php endif; ?></div>
          <?php endforeach; ?>
        </div>
        <p><button class="admin-btn" type="submit">Save <?= h($def["title"]) ?></button></p>
      </form>
    </details>
  </div>

  <div class="admin-box">
    <form class="admin-filters" method="get">
      <input type="text" name="q" value="<?= h($search) ?>" placeholder="Search by name or location...">
      <select name="status"><option value="">All status</option><?php foreach ($def["status_options"] as $k => $l): ?><option value="<?= h($k) ?>" <?= $statusF === $k ? "selected" : "" ?>><?= h($l) ?></option><?php endforeach; ?></select>
      <button class="admin-btn secondary" type="submit">Filter</button>
    </form>
    <div class="table-responsive" style="overflow-x:auto"><table class="admin-table">
      <thead><tr><?php foreach ($def["columns"] as $c): ?><th><?= h($def["fields"][$c][0]) ?></th><?php endforeach; ?><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <?php foreach ($def["columns"] as $c): ?><td><?= h($r[$c]) ?></td><?php endforeach; ?>
          <td><span class="status <?= h($r["status"]) ?>"><?= h(ucfirst($r["status"])) ?></span></td>
          <td>
            <form class="admin-inline" method="post" action="crud-actions.php">
              <?= csrf_field() ?><input type="hidden" name="entity" value="<?= h($entityKey) ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$r["id"] ?>">
              <select name="status"><?php foreach ($def["status_options"] as $k => $l): ?><option value="<?= h($k) ?>" <?= $r["status"] === $k ? "selected" : "" ?>><?= h($l) ?></option><?php endforeach; ?></select>
              <button class="admin-btn secondary" type="submit">Update</button>
            </form>
            <form class="admin-inline" method="post" action="crud-actions.php" onsubmit="return confirm('Delete this record permanently?')">
              <?= csrf_field() ?><input type="hidden" name="entity" value="<?= h($entityKey) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r["id"] ?>">
              <button class="admin-btn danger" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="<?= count($def["columns"]) + 2 ?>">Nothing found.</td></tr><?php endif; ?>
      </tbody></table></div>
  </div>
</section>
</main>
</div>
<script src="../assets/js/app.js"></script>

</body>
</html>
