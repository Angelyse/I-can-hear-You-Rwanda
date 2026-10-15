<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("counsellor");
require_once __DIR__ . "/../includes/page_init.php";
$st = $pdo->prepare("SELECT r.*, u.name AS owner FROM resources r INNER JOIN users u ON u.id = r.uploaded_by
    WHERE r.status = 'published' OR r.uploaded_by = ? ORDER BY r.created_at DESC");
$st->execute([$meId]);
$res = $st->fetchAll();
$cats = array_values(array_unique(array_filter(array_column($res, "category"))));
$byType = fn($types) => count(array_filter($res, fn($r) => in_array($r["type"], $types)));
function rslug(string $s): string { return strtolower(trim(preg_replace('/[^a-z0-9]+/i', "-", $s), "-")); }
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Resources | I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/counselor-resources.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body>

<div class="dashboard-layout">

    <!-- =========================================
         SIDEBAR
    ========================================== -->

    <aside class="sidebar" id="sidebar">

        <!-- Logo -->
        <div class="sidebar-logo">

            <a href="dashboard.php" class="logo">

                <span>
                    <img src="../assets/images/log.jpeg"
                         alt="I Can Hear You">
                </span>

                <div>
                    <strong>I Can Hear You Rwanda</strong>
                    <small>Rwanda</small>
                </div>

            </a>

        </div>


        <!-- Main Menu -->
        <div class="sidebar-section">

            <span class="sidebar-section-title">
                Main Menu
            </span>

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

            <span class="sidebar-section-title">
                Management
            </span>

            <nav class="sidebar-nav">

                <a href="session-notes.php">
                    <i class="fa-solid fa-note-sticky"></i>
                    <span>Session Notes</span>
                </a>

                <a href="resources.php"  class="active">
                    <i class="fa-solid fa-book-open"></i>
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


    <!-- =========================================
         MAIN CONTENT
    ========================================== -->

    <main class="dashboard-main">


        <!-- Header -->
        <header class="dashboard-header">

            <button class="mobile-menu" id="mobileMenu">

                <i class="fa-solid fa-bars"></i>

            </button>


            <div class="header-search" style="margin-left: 250px;">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="globalSearch"
                    placeholder="Search resources..."
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

                        <strong>
                            Dr. Jean Mukamana
                        </strong>

                        <span>
                            Counsellor
                        </span>

                    </div>

                    <i class="fa-solid fa-chevron-down"></i>

                </div>

            </div>

        </header>


        <!-- =========================================
             PAGE
        ========================================== -->

        <div class="resources-page" data-csrf="<?= h($csrf) ?>">
  <?= flash_html() ?>
  <div class="page-heading"><div><h1>Resources</h1><p>Manage counselling resources and materials for your clients.</p></div>
    <a class="add-resource-btn" href="#addResource"><i class="fa-solid fa-plus"></i> Add Resource</a></div>
  <div class="resource-stats">
    <div class="resource-stat-card"><div class="resource-stat-icon total"><i class="fa-solid fa-book-open"></i></div><div><span>Total Resources</span><strong><?= count($res) ?></strong></div></div>
    <div class="resource-stat-card"><div class="resource-stat-icon articles"><i class="fa-solid fa-file-lines"></i></div><div><span>Articles & Guides</span><strong><?= $byType(["article", "guide"]) ?></strong></div></div>
    <div class="resource-stat-card"><div class="resource-stat-icon videos"><i class="fa-solid fa-video"></i></div><div><span>Videos</span><strong><?= $byType(["video"]) ?></strong></div></div>
    <div class="resource-stat-card"><div class="resource-stat-icon exercises"><i class="fa-solid fa-person-walking"></i></div><div><span>Exercises</span><strong><?= $byType(["exercise"]) ?></strong></div></div>
  </div>
  <div class="resource-controls">
    <div class="resource-search"><i class="fa-solid fa-magnifying-glass"></i><input type="text" id="resourceSearch" placeholder="Search resources..."></div>
    <select id="categoryFilter"><option value="all">All Categories</option><?php foreach ($cats as $c): ?><option value="<?= h(rslug($c)) ?>"><?= h($c) ?></option><?php endforeach; ?></select>
    <select id="typeFilter"><option value="all">All Types</option><option value="article">Article</option><option value="guide">Guide</option><option value="video">Video</option><option value="exercise">Exercise</option></select>
  </div>
  <div class="resources-grid" id="resourcesGrid">
    <?php foreach ($res as $r): $link = $r["file_path"] ? "../" . $r["file_path"] : $r["external_url"]; ?>
    <div class="resource-card" data-category="<?= h(rslug((string)$r["category"])) ?>" data-type="<?= h($r["type"]) ?>" data-title="<?= h($r["title"]) ?>">
      <div class="resource-card-content">
        <div class="resource-card-top"><span class="resource-type"><?= h(ucfirst($r["type"])) ?></span><?php if ($r["status"] !== "published"): ?><span class="resource-type"><?= h(ucfirst($r["status"])) ?></span><?php endif; ?></div>
        <h3><?= h($r["title"]) ?></h3><p><?= h($r["description"]) ?></p>
        <div class="resource-meta"><span><?= h($r["category"]) ?></span><span><?= h(fmt_date($r["created_at"])) ?></span></div>
        <div class="resource-actions">
          <?php if ($link): ?><a href="<?= h($link) ?>" target="_blank" rel="noopener noreferrer">Open</a><?php endif; ?>
          <?php if ((int)$r["uploaded_by"] === $meId): ?>
          <form method="post" action="../api/resources.php" style="display:inline" onsubmit="return confirm('Delete this resource?')">
            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="resource_id" value="<?= (int)$r["id"] ?>"><button type="submit">Delete</button></form>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="empty-state" id="emptyResources" <?= $res ? 'style="display:none"' : "" ?>><h3>No resources yet</h3></div>

  <div class="profile-card" id="addResource" style="margin-top:28px">
    <div class="card-header"><div><h2>Add Resource</h2><p>Share a file (PDF, DOCX, JPG, PNG, MP4 up to 25 MB) or a link.</p></div></div>
    <form method="post" action="../api/resources.php" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="action" value="upload">
      <div class="form-grid">
        <div class="form-group"><label>Title</label><input type="text" name="title" required maxlength="255"></div>
        <div class="form-group"><label>Type</label><select name="type"><option value="article">Article</option><option value="guide">Guide</option><option value="video">Video</option><option value="exercise">Exercise</option></select></div>
        <div class="form-group"><label>Category</label><input type="text" name="category" placeholder="e.g. Stress Management" maxlength="100"></div>
        <div class="form-group"><label>External link (optional)</label><input type="url" name="external_url" placeholder="https://"></div>
      </div>
      <div class="form-group full-width"><label>Description</label><textarea name="description" rows="3"></textarea></div>
      <div class="form-group"><label>File (optional)</label><input type="file" name="file" accept=".pdf,.docx,.jpg,.jpeg,.png,.mp4"></div>
      <button type="submit" class="save-resource-btn">Save Resource</button>
    </form>
  </div>
</div>
</main>
</div>
<script src="../assets/js/dashboard.js"></script>
<script src="../assets/js/counselor-lists.js"></script>
<script src="../assets/js/app.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>
