<?php
/*
|--------------------------------------------------------------------------
| Back-end migration + optional demo data
|--------------------------------------------------------------------------
| 1. Import database/database.sql into MySQL first.
| 2. Run:   php database/migrate.php            (schema additions only)
|           php database/migrate.php --seed     (also add demo accounts/data)
|
| It is safe to run more than once. Browser access is limited to localhost.
*/

$isCli = PHP_SAPI === "cli";
if (!$isCli && !in_array($_SERVER["REMOTE_ADDR"] ?? "", ["127.0.0.1", "::1"], true)) {
    http_response_code(403);
    exit("Run this from the command line or from localhost.");
}
$seed = $isCli ? in_array("--seed", $argv ?? [], true) : isset($_GET["seed"]);

require_once __DIR__ . "/../config/database.php";

function out(string $m): void { echo $m . ($GLOBALS["isCli"] ? "\n" : "<br>"); }

function column_exists(PDO $pdo, string $table, string $col): bool
{
    $s = $pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $s->execute([$table, $col]);
    return (int)$s->fetchColumn() > 0;
}

/* ---------------- schema additions ---------------- */

if (!column_exists($pdo, "audit_logs", "entity_type")) {
    $pdo->exec("ALTER TABLE audit_logs ADD COLUMN entity_type VARCHAR(50) NULL AFTER action");
    out("audit_logs.entity_type added");
}
if (!column_exists($pdo, "audit_logs", "entity_id")) {
    $pdo->exec("ALTER TABLE audit_logs ADD COLUMN entity_id INT UNSIGNED NULL AFTER entity_type");
    out("audit_logs.entity_id added");
}
if (!column_exists($pdo, "bookings", "cancel_reason")) {
    $pdo->exec("ALTER TABLE bookings ADD COLUMN cancel_reason VARCHAR(255) NULL");
    out("bookings.cancel_reason added");
}
if (!column_exists($pdo, "resources", "external_url")) {
    $pdo->exec("ALTER TABLE resources ADD COLUMN external_url VARCHAR(500) NULL AFTER file_path");
    out("resources.external_url added");
}

$pdo->exec("CREATE TABLE IF NOT EXISTS user_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    pref_key VARCHAR(100) NOT NULL,
    pref_value TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_pref (user_id, pref_key),
    CONSTRAINT fk_user_settings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS saved_resources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    resource_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_saved (user_id, resource_id),
    CONSTRAINT fk_saved_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_saved_resource FOREIGN KEY (resource_id) REFERENCES resources(id) ON DELETE CASCADE
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    ip_address VARCHAR(45) NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_email (email, attempted_at)
)");

out("Schema additions complete.");

/* ---------------- demo data ---------------- */

if (!$seed) { out("Done. (Run with --seed to add demo data.)"); exit; }

$roleId = fn(string $n) => (int)$pdo->query("SELECT id FROM roles WHERE name = " . $pdo->quote($n))->fetchColumn();

function ensure_user(PDO $pdo, int $role, string $name, string $email, string $pass, ?string $phone = null): int
{
    $s = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $s->execute([$email]);
    if ($id = $s->fetchColumn()) return (int)$id;
    $pdo->prepare("INSERT INTO users (role_id, name, email, phone, password, status, email_verified_at)
                   VALUES (?, ?, ?, ?, ?, 'active', NOW())")
        ->execute([$role, $name, $email, $phone, password_hash($pass, PASSWORD_DEFAULT)]);
    return (int)$pdo->lastInsertId();
}

ensure_user($pdo, $roleId("admin"), "Admin User", "admin@icanhearyou.rw", "Admin@12345");

$counsellors = [
    ["Dr. Jean Mukamana", "jean@icanhearyou.rw", "Clinical Psychologist", "Anxiety & Stress", 8, "Kinyarwanda,English", "video,chat"],
    ["Alice Niyonsenga", "alice.n@icanhearyou.rw", "Family Counsellor", "Family Counselling", 6, "English,Kinyarwanda", "video,chat"],
    ["David Kamanzi", "david@icanhearyou.rw", "Youth Counsellor", "Youth Counselling", 5, "Kinyarwanda", "chat"],
    ["Marie Uwase", "marie@icanhearyou.rw", "Relationship Counsellor", "Relationship Counselling", 7, "English", "video,chat"],
    ["Patrick Habimana", "patrick@icanhearyou.rw", "Career Counsellor", "Career Counselling", 9, "French,English", "chat"],
];
$cids = [];
foreach ($counsellors as [$n, $e, $q, $sp, $yrs, $lang, $types]) {
    $id = ensure_user($pdo, $roleId("counsellor"), $n, $e, "Counsellor@123");
    $cids[] = $id;
    $pdo->prepare("INSERT IGNORE INTO counselor_profiles
        (user_id, qualification, specialization, years_experience, languages, bio, availability, status, session_types)
        VALUES (?, ?, ?, ?, ?, ?, 'Mon-Fri 09:00-17:00', 'active', ?)")
        ->execute([$id, $q, $sp, $yrs, $lang, "$n supports clients with $sp.", $types]);
}

$clients = [
    ["Alice Mukeshimana", "alice.m@example.com", "youth"],
    ["Eric Nsengiyumva", "eric@example.com", "youth"],
    ["Grace Uwimana", "grace@example.com", "parent"],
];
$clid = [];
foreach ($clients as [$n, $e, $t]) {
    $id = ensure_user($pdo, $roleId("client"), $n, $e, "Client@12345");
    $clid[] = $id;
    $pdo->prepare("INSERT IGNORE INTO client_profiles (user_id, client_type, preferred_language, consent_status)
                   VALUES (?, ?, 'english', 'accepted')")->execute([$id, $t]);
}

if ((int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn() === 0) {
    $svc = (int)$pdo->query("SELECT id FROM services ORDER BY id LIMIT 1")->fetchColumn();
    $ins = $pdo->prepare("INSERT INTO bookings
        (client_id, counselor_id, service_id, session_type, booking_date, start_time, end_time, reason, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $ins->execute([$clid[0], $cids[0], $svc, "video", date("Y-m-d", strtotime("+2 days")), "10:00", "11:00", "Feeling stressed about studies", "confirmed"]);
    $b1 = (int)$pdo->lastInsertId();
    $pdo->prepare("INSERT INTO sessions (booking_id, session_type, meeting_type, meeting_link, status)
                   VALUES (?, 'video', 'video_call', ?, 'scheduled')")->execute([$b1, make_link($b1)]);
    $ins->execute([$clid[0], $cids[1], $svc, "chat", date("Y-m-d", strtotime("+5 days")), "14:00", "15:00", "Family communication", "pending"]);
    $ins->execute([$clid[0], $cids[2], $svc, "chat", date("Y-m-d", strtotime("-7 days")), "11:00", "12:00", "Peer pressure", "completed"]);
    $b3 = (int)$pdo->lastInsertId();
    $pdo->prepare("INSERT INTO sessions (booking_id, session_type, meeting_type, status, started_at, ended_at)
                   VALUES (?, 'chat', 'chat_room', 'completed', ?, ?)")
        ->execute([$b3, date("Y-m-d 11:00:00", strtotime("-7 days")), date("Y-m-d 12:00:00", strtotime("-7 days"))]);
    $ins->execute([$clid[1], $cids[0], $svc, "chat", date("Y-m-d", strtotime("+1 day")), "09:00", "10:00", "Exam anxiety", "pending"]);
    out("Demo bookings created.");
}
function make_link(int $id): string { return "https://meet.jit.si/ICanHearYou-$id-" . bin2hex(random_bytes(5)); }

if ((int)$pdo->query("SELECT COUNT(*) FROM resources")->fetchColumn() === 0) {
    $adminId = (int)$pdo->query("SELECT id FROM users WHERE email='admin@icanhearyou.rw'")->fetchColumn();
    $ins = $pdo->prepare("INSERT INTO resources (title, description, external_url, type, category, uploaded_by, status)
                          VALUES (?, ?, ?, ?, ?, ?, 'published')");
    $ins->execute(["Managing Exam Stress", "Practical tips for staying calm during exams.", "https://www.who.int/campaigns/world-mental-health-day", "article", "Stress", $adminId]);
    $ins->execute(["Breathing Exercise: 4-7-8", "A short guided breathing practice.", "https://www.youtube.com/results?search_query=4-7-8+breathing", "exercise", "Anxiety", $adminId]);
    $ins->execute(["Talking to Your Teenager", "A guide for parents on open communication.", "https://www.who.int/health-topics/adolescent-health", "guide", "Family", $adminId]);
    out("Demo resources created.");
}

if ((int)$pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn() === 0) {
    $pdo->exec("INSERT INTO programs (name, program_type, description, start_date, status) VALUES
        ('Schools Wellness Week', 'school', 'Mental wellness awareness in secondary schools.', CURDATE(), 'active'),
        ('Parent Support Circle', 'community', 'Monthly peer-support meetings for parents.', CURDATE(), 'upcoming')");
    $pdo->exec("INSERT INTO schools (name, type, location, contact_person, student_count, status) VALUES
        ('Green Hills Academy', 'Secondary', 'Kigali', 'Mr. Habimana', 900, 'active')");
    $pdo->exec("INSERT INTO organizations (name, type, location, contact_person, email, status) VALUES
        ('Hope Youth Network', 'NGO', 'Kigali', 'Ms. Uwera', 'info@hope.example', 'active')");
    out("Demo programs/schools/organizations created.");
}

out("");
out("Demo logins:");
out("  admin       admin@icanhearyou.rw   / Admin@12345");
out("  counsellor  jean@icanhearyou.rw    / Counsellor@123");
out("  client      alice.m@example.com    / Client@12345");
out("Change these passwords before going live.");
