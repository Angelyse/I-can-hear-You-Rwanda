<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("admin");
require_once __DIR__ . "/../includes/page_init.php";
$statusF = $_GET["status"] ?? "";
$where = ""; $args = [];
if (in_array($statusF, ["published","draft","archived","pending"], true)) { $where = " WHERE r.status = ?"; $args[] = $statusF; }
$st = $pdo->prepare("SELECT r.*, u.name AS owner FROM resources r INNER JOIN users u ON u.id = r.uploaded_by" . $where . " ORDER BY (r.status='pending') DESC, r.created_at DESC LIMIT 300");
$st->execute($args);
$rows = $st->fetchAll();
$c = [];
foreach ($pdo->query("SELECT status, COUNT(*) n FROM resources GROUP BY status") as $r) $c[$r["status"]] = (int)$r["n"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Resources Management - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin-resources.css">

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

                <a href="resources.php"  class="active">
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
                </a>

                <a href="settings.php">
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


    <!-- ================= MAIN CONTENT ================= -->
    <main class="dashboard-main">

        <!-- Header -->
        <header class="dashboard-header">

            <button class="mobile-menu" id="mobileMenu">
                <i class="fas fa-bars"></i>
            </button>

            <div class="header-search">
                <i class="fas fa-search"></i>
                <input
                    type="text"
                    id="headerSearch"
                    placeholder="Search resources..."
                >
            </div>

            <div class="header-right">

                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span>4</span>
                </button>

                <div class="admin-profile">
                    <div class="admin-avatar"><?= h($meInitials) ?></div>

                    <div>
                        <strong><?= h($meName) ?></strong>
                        <small>System Administrator</small>
                    </div>
                </div>

            </div>

        </header>


        <!-- Page -->
        <section class="resources-page" data-csrf="<?= h($csrf) ?>">
  <link rel="stylesheet" href="../assets/css/admin-extra.css">
  <?= flash_html() ?>
  <div class="page-header"><div><h1>Resources Management</h1><p>Review, publish and organise wellness resources.</p></div></div>
  <div class="admin-stats">
    <div class="admin-stat"><span>Total</span><strong><?= array_sum($c) ?></strong></div>
    <?php foreach (["published","pending","draft","archived"] as $k): ?><div class="admin-stat"><span><?= ucfirst($k) ?></span><strong><?= (int)($c[$k] ?? 0) ?></strong></div><?php endforeach; ?>
  </div>
  <div class="admin-box"><details class="admin-add"><summary>+ Add Resource</summary>
    <form class="admin-form" method="post" action="../api/resources.php" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="action" value="upload">
      <div class="grid">
        <div class="field"><label>Title *</label><input type="text" name="title" required maxlength="255"></div>
        <div class="field"><label>Type</label><select name="type"><option value="article">Article</option><option value="guide">Guide</option><option value="video">Video</option><option value="exercise">Exercise</option></select></div>
        <div class="field"><label>Category</label><input type="text" name="category" maxlength="100"></div>
        <div class="field"><label>External link</label><input type="url" name="external_url" placeholder="https://"></div>
        <div class="field"><label>File (PDF, DOCX, JPG, PNG, MP4 · 25 MB)</label><input type="file" name="file" accept=".pdf,.docx,.jpg,.jpeg,.png,.mp4"></div>
      </div>
      <div class="field" style="margin-top:12px"><label>Description</label><textarea name="description" rows="3"></textarea></div>
      <p><button class="admin-btn" type="submit">Publish</button></p>
    </form></details></div>
  <div class="admin-box">
    <form class="admin-filters" method="get"><select name="status"><option value="">All status</option>
      <?php foreach (["published","pending","draft","archived"] as $k): ?><option value="<?= $k ?>" <?= $statusF === $k ? "selected" : "" ?>><?= ucfirst($k) ?></option><?php endforeach; ?></select>
      <button class="admin-btn secondary" type="submit">Filter</button></form>
    <div style="overflow-x:auto"><table class="admin-table">
      <thead><tr><th>Title</th><th>Type</th><th>Category</th><th>Uploaded by</th><th>Added</th><th>Status</th><th>Actions</th></tr></thead><tbody>
      <?php foreach ($rows as $r): $link = $r["file_path"] ? "../" . $r["file_path"] : $r["external_url"]; ?>
        <tr><td><?= $link ? '<a href="' . h($link) . '" target="_blank" rel="noopener noreferrer">' . h($r["title"]) . "</a>" : h($r["title"]) ?></td>
          <td><?= h(ucfirst($r["type"])) ?></td><td><?= h($r["category"]) ?></td><td><?= h($r["owner"]) ?></td><td><?= h(fmt_date($r["created_at"])) ?></td>
          <td><span class="status <?= h($r["status"]) ?>"><?= h(ucfirst($r["status"])) ?></span></td>
          <td>
            <form class="admin-inline" method="post" action="../api/resources.php"><?= csrf_field() ?><input type="hidden" name="action" value="set_status"><input type="hidden" name="resource_id" value="<?= (int)$r["id"] ?>">
              <select name="status"><?php foreach (["published","pending","draft","archived"] as $k): ?><option value="<?= $k ?>" <?= $r["status"] === $k ? "selected" : "" ?>><?= ucfirst($k) ?></option><?php endforeach; ?></select>
              <button class="admin-btn secondary" type="submit">Update</button></form>
            <form class="admin-inline" method="post" action="../api/resources.php" onsubmit="return confirm('Delete this resource?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="resource_id" value="<?= (int)$r["id"] ?>"><button class="admin-btn danger" type="submit">Delete</button></form>
          </td></tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="7">No resources found.</td></tr><?php endif; ?>
      </tbody></table></div>
  </div>
</section>
</main>
</div>
<script src="../assets/js/app.js"></script>

</body>
</html>
