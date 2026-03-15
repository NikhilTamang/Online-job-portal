<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isEmployer()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$success = '';
$error   = '';

// Handle job deletion
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['job_id'])) {
    $jid = (int)$_POST['job_id'];
    // Verify ownership
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

// Fetch jobs posted by employer
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

<div class="flex justify-between items-center mb-8">
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

<div class="card">
    <h2 class="dashboard-title">My Job Postings</h2>

    <?php if (count($jobs) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Job Title</th>
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
                            </td>
                            <td><?= date('M j, Y', strtotime($job['created_at'])) ?></td>
                            <td>
                                <span class="badge badge-<?= $job['applicant_count'] > 0 ? 'success' : 'warning' ?>">
                                    <?= $job['applicant_count'] ?> Applicants
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

<?php include '../includes/footer.php'; ?>