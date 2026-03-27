<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isEmployer()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

$u_stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$u_stmt->bind_param("i", $user_id);
$u_stmt->execute();
$user = $u_stmt->get_result()->fetch_assoc();

$e_stmt = $conn->prepare("SELECT company_name FROM employers WHERE user_id = ?");
$e_stmt->bind_param("i", $user_id);
$e_stmt->execute();
$employer_profile = $e_stmt->get_result()->fetch_assoc();

if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['job_id'])) {
    $jid = (int)$_POST['job_id'];

    $stmt = $conn->prepare("SELECT id FROM jobs WHERE id = ? AND employer_id = (SELECT id FROM employers WHERE user_id = ?)");
    $stmt->bind_param("ii", $jid, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
        $stmt->bind_param("i", $jid);
        if ($stmt->execute()) {
            $success = "Job posting deleted successfully.";
        } else {
            $error = "Failed to delete job.";
        }
    } else {
        $error = "Unauthorized action.";
    }
}

$stmt = $conn->prepare('
    SELECT j.*, COUNT(a.id) as applicant_count
    FROM jobs j
    LEFT JOIN applications a ON j.id = a.job_id
    WHERE j.employer_id = (SELECT id FROM employers WHERE user_id = ?)
    GROUP BY j.id
    ORDER BY j.created_at DESC
');
$stmt->bind_param("i", $user_id);
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
?>

<div class="flex justify-between items-center mb-4">
    <h1>Employer Dashboard</h1>
    <div class="flex gap-4">
        <a href="profile.php" class="btn btn-outline">Edit Profile</a>
        <a href="post-job.php" class="btn btn-primary">Post a New Job</a>
    </div>
</div>

<?php if ($error): ?>
    <div class="badge badge-danger alert-badge mb-4"><?= esc($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="badge badge-success alert-badge mb-4"><?= esc($success) ?></div>
<?php endif; ?>

<div class="card" style="margin-bottom:1.5rem;display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;">
    <div class="user-avatar-circle employer-avatar">
        <?= strtoupper(substr($user['name'], 0, 1)) ?>
    </div>
    <div>
        <div style="font-size:1.25rem;font-weight:700;color:#111827;"><?= esc($user['name']) ?></div>
        <div style="font-size:0.85rem;color:#6b7280;margin-top:2px;">
            <span class="badge" style="font-size:0.75rem;padding:2px 10px;border-radius:999px;background:#d1fae5;color:#065f46;">Employer</span>
        </div>
        <?php if (!empty($employer_profile['company_name'])): ?>
            <div style="margin-top:6px;color:#374151;font-size:0.9rem;">🏢 <?= esc($employer_profile['company_name']) ?></div>
        <?php endif; ?>
        <div style="margin-top:4px;color:#6b7280;font-size:0.82rem;">✉️ <?= esc($user['email']) ?></div>
    </div>
    <div style="margin-left:auto;text-align:right;">
        <div style="font-size:2rem;font-weight:800;color:#4f46e5;text-align:center;"><?= count($jobs) ?></div>
        <div style="font-size:0.8rem;color:#6b7280;">Active Posting<?= count($jobs) !== 1 ? 's' : '' ?></div>
    </div>
</div>

<div class="card">
    <h2 class="dashboard-title">My Job Postings</h2>

    <?php if (count($jobs) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Category</th>
                        <th>Posted On</th>
                        <th>Applicants</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td>
                                <a href="../job.php?id=<?= $job['id'] ?>" class="table-link">
                                    <?= esc($job['title']) ?>
                                </a>
                                <?php if (!empty($employer_profile['company_name'])): ?>
                                    <div style="font-size:0.78rem;color:#6b7280;">
                                        🏢 <?= esc($employer_profile['company_name']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($job['category'])): ?>
                                    <span class="job-card-category" style="font-size:0.76rem;"><?= esc($job['category']) ?></span>
                                <?php else: ?>
                                    <span style="color:#9ca3af;">—</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M j, Y', strtotime($job['created_at'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $job['applicant_count'] > 0 ? 'success' : 'warning' ?>">
                                    <?= $job['applicant_count'] ?> Applicant<?= $job['applicant_count'] != 1 ? 's' : '' ?>
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="applicants.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-outline">Applicants</a>
                                    <a href="edit-job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Delete this job? All applications will be removed.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center empty-state">
            <p>You haven't posted any jobs yet.</p>
            <a href="post-job.php" class="btn btn-primary mt-4">Post Your First Job</a>
        </div>
    <?php endif; ?>
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
.employer-avatar {
    background: linear-gradient(135deg, #10b981, #059669);
}
</style>

<?php include '../includes/footer.php'; ?>