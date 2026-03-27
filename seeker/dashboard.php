<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeeker()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

$u_stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$user = $u_stmt->get_result()->fetch_assoc();

$s_stmt = $conn->prepare("SELECT id, headline, preferred_category FROM seekers WHERE user_id = ?");
$s_stmt->bind_param("i", $user_id);
$s_stmt->execute();
$seeker = $s_stmt->get_result()->fetch_assoc();

if (!$seeker || empty($seeker['preferred_category'])) {
    redirect('profile-setup.php');
}

$stmt = $conn->prepare('
    SELECT a.*, j.title, j.location, j.category, e.company_name
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN employers e ON j.employer_id = e.id
    WHERE a.seeker_id = ?
    ORDER BY a.created_at DESC
');
$stmt->bind_param("i", $seeker['id']);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$pref_cats = array_map('trim', explode(',', $seeker['preferred_category']));

include '../includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1>Job Seeker Dashboard</h1>
    </div>

    <div class="card" style="margin-bottom:1.5rem;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
        <div class="user-avatar-circle">
            <?= strtoupper(substr($user['name'], 0, 1)) ?>
        </div>
        <div>
            <div style="font-size:1.25rem;font-weight:700;color:#111827;"><?= esc($user['name']) ?></div>
            <div style="font-size:0.85rem;color:#6b7280;margin-top:2px;">
                <span class="badge badge-primary" style="font-size:0.75rem;padding:2px 10px;border-radius:999px;background:#ede9fe;color:#5b21b6;">Job Seeker</span>
            </div>
            <?php if (!empty($seeker['headline'])): ?>
                <div style="margin-top:6px;color:#374151;font-size:0.9rem;">📝 <?= esc($seeker['headline']) ?></div>
            <?php endif; ?>
            <div style="margin-top:4px;font-size:0.82rem;color:#6b7280;">
                Preferred:
                <?php foreach ($pref_cats as $cat): ?>
                    <span class="job-card-category" style="font-size:0.75rem;padding:1px 8px;margin-right:3px;"><?= esc($cat) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="margin-left:auto;">
            <a href="profile.php" class="btn btn-outline" style="font-size:0.85rem;">Edit Profile</a>
        </div>
    </div>

    <div class="card">
        <h2 class="dashboard-title">My Applications</h2>

        <?php if (count($applications) > 0): ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Category</th>
                            <th>Applied On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td>
                                    <a href="../job.php?id=<?= $app['job_id'] ?>" class="table-link">
                                        <?= esc($app['title']) ?>
                                    </a>
                                </td>
                                <td><?= esc($app['company_name']) ?></td>
                                <td>
                                    <?php if (!empty($app['category'])): ?>
                                        <span class="job-card-category" style="font-size:0.76rem;"><?= esc($app['category']) ?></span>
                                    <?php else: ?>
                                        <span style="color:#9ca3af;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('M j, Y', strtotime($app['created_at'])) ?></td>
                                <td>
                                    <span class="badge badge-<?= $app['status'] == 'accepted' ? 'success' : ($app['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($app['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center empty-state">
                <p>You haven't applied to any jobs yet.</p>
                <a href="../index.php" class="btn btn-primary mt-4">Browse Jobs</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.user-avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6366f1, #8b5cf6);
    color: #fff;
    font-size: 1.6rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
</style>

<?php include '../includes/footer.php'; ?>