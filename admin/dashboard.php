<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'seeker'");
$total_seekers = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'employer'");
$total_employers = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM jobs");
$total_jobs = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM applications");
$total_applications = $result->fetch_assoc()['total'];

include '../includes/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <h1>Admin Dashboard</h1>
    <span class="badge badge-primary" style="font-size:0.85rem;padding:0.4rem 1.1rem;">⚙️ Admin Panel</span>
</div>

<div class="stats-grid">
    <div class="card text-center">
        <p class="stat-label">Total Job Seekers</p>
        <div class="stat-value stat-value-primary"><?= $total_seekers ?></div>
    </div>
    <div class="card text-center">
        <p class="stat-label">Total Employers</p>
        <div class="stat-value stat-value-primary"><?= $total_employers ?></div>
    </div>
    <div class="card text-center">
        <p class="stat-label">Total Jobs Posted</p>
        <div class="stat-value stat-value-primary"><?= $total_jobs ?></div>
    </div>
    <div class="card text-center">
        <p class="stat-label">Total Applications</p>
        <div class="stat-value stat-value-primary"><?= $total_applications ?></div>
    </div>
</div>

<div class="admin-nav-grid">
    <a href="users.php" class="card admin-nav-card">
        <div class="admin-nav-icon">👥</div>
        <h3>Manage Users</h3>
        <p>View all registered job seekers and employers.</p>
    </a>
    <a href="jobs.php" class="card admin-nav-card">
        <div class="admin-nav-icon">💼</div>
        <h3>Manage Jobs</h3>
        <p>View, edit and remove job postings from the system.</p>
    </a>
</div>

<?php include '../includes/footer.php'; ?>