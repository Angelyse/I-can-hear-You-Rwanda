<?php
require_once __DIR__ . "/../includes/bootstrap.php";
requireRole("client");
require_once __DIR__ . "/../includes/page_init.php";
$st = $pdo->prepare("SELECT r.*, (SELECT COUNT(*) FROM saved_resources sr WHERE sr.resource_id = r.id AND sr.user_id = ?) AS saved
    FROM resources r WHERE r.status = 'published' ORDER BY r.created_at DESC");
$st->execute([$meId]);
$res = $st->fetchAll();
$cats = array_values(array_unique(array_filter(array_column($res, "category"))));
$icons = ["article" => "📄", "guide" => "📘", "video" => "🎬", "exercise" => "🧘"];
$verbs = ["article" => "Read Article", "guide" => "Read Guide", "video" => "Watch Video", "exercise" => "Start Exercise"];
function slug(string $s): string { return strtolower(trim(preg_replace('/[^a-z0-9]+/i', "-", $s), "-")); }
?>



<?php
// Resources page - frontend only
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf" content="<?= h($csrf) ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Resources - I Can Hear You Rwanda</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/resources.css">
</head>

<body>

<div class="dashboard-layout">

    <!-- =========================
         SIDEBAR
    ========================== -->

    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">

            <a href="../index.php" class="logo">

                <span>
                    <img
                        src="../assets/images/log.jpeg"
                        alt="I Can Hear You"
                    >
                </span>

                <strong>I Can Hear You Rwanda</strong>

            </a>

        </div>


        <!-- USER PROFILE -->

        <div class="user-mini-profile">

            <div class="user-avatar">
                <?= h($meInitials) ?>
            </div>

            <div>
                <h4><?= h($meName) ?></h4>
                <span>Client</span>
            </div>

        </div>


        <!-- NAVIGATION -->

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

            <a href="resources.php" class="sidebar-link active"><span>📚</span>Resources</a>

            <a href="profile.php" class="sidebar-link"><span>👤</span>My Profile</a>

            <a href="notifications.php" class="sidebar-link"> <span>🔔</span> Notifications</a>

            <a href="settings.php" class="sidebar-link"> <span>⚙️</span>Settings </a>

            </div>


            <div class="sidebar-bottom">

                <a href="../pages/logout.php">
                    <span class="nav-icon">↪</span>
                    Logout
                </a>

            </div>

        </nav>

    </aside>


    <!-- =========================
         MAIN CONTENT
    ========================== -->

    <main class="dashboard-main" style="margin-left: 250px;">


        <!-- HEADER -->

        <header class="dashboard-header">

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
                    placeholder="Search..."
                >

            </div>


            <div class="header-actions">

                <button class="notification-btn">
                    🔔
                    <span></span>
                </button>


                <div class="header-profile">

                    <div class="header-avatar">
                        <?= h($meInitials) ?>
                    </div>

                    <div>
                        <strong><?= h($meName) ?></strong>
                        <small>Client</small>
                    </div>

                </div>

            </div>

        </header>


        <!-- =========================
             RESOURCES
        ========================== -->

        <section class="resources-page" data-csrf="<?= h($csrf) ?>">
  <div class="resources-heading"><div><h1>Resources</h1><p>Explore helpful materials for your mental wellness and personal growth.</p></div></div>
  <div class="resource-search-box"><span>🔍</span><input type="text" id="resourceSearch" placeholder="Search resources..."></div>
  <div class="resource-categories">
    <button class="category-btn active" data-category="all">All</button>
    <?php foreach ($cats as $c): ?><button class="category-btn" data-category="<?= h(slug($c)) ?>"><?= h($c) ?></button><?php endforeach; ?>
  </div>
  <div class="resources-count"><span id="resourceCount"><?= count($res) ?> resources</span></div>
  <div class="resources-grid" id="resourcesGrid">
    <?php foreach ($res as $r):
      $link = $r["file_path"] ? "../" . $r["file_path"] : $r["external_url"]; ?>
    <article class="resource-card" data-category="<?= h(slug((string)$r["category"])) ?>" data-title="<?= h($r["title"]) ?>">
      <div class="resource-image"><?= $icons[$r["type"]] ?? "📄" ?></div>
      <div class="resource-content">
        <span class="resource-type"><?= h(ucfirst($r["type"])) ?></span>
        <h2><?= h($r["title"]) ?></h2>
        <p><?= h($r["description"]) ?></p>
        <div class="resource-meta"><span><?= h($r["category"]) ?></span></div>
        <?php if ($link): ?><a class="resource-btn" href="<?= h($link) ?>" target="_blank" rel="noopener noreferrer"><?= $verbs[$r["type"]] ?? "Open" ?></a><?php endif; ?>
        <button class="resource-btn save-resource" data-id="<?= (int)$r["id"] ?>"><?= $r["saved"] ? "★ Saved" : "☆ Save" ?></button>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <div class="no-resources" id="noResources" <?= $res ? "" : 'style="display:block"' ?>><div>🔎</div><h2>No resources found</h2><p>Try another search term or category.</p></div>
</section>


    </main>

</div>


<script src="../assets/js/resources.js"></script>

<script src="../assets/js/app.js"></script>
</body>
</html>