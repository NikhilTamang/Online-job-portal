<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isEmployer()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

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
    <a href="post-job.php" class="btn btn-primary">Post a New Job</a>
</div>

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
                                <a href="applicants.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-outline">View Applicants</a>
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