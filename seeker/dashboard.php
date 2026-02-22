<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeeker()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch applications
$stmt = $conn->prepare('
    SELECT a.*, j.title, j.location, e.company_name 
    FROM applications a 
    JOIN jobs j ON a.job_id = j.id 
    JOIN employers e ON j.employer_id = e.id 
    WHERE a.seeker_id = (SELECT id FROM seekers WHERE user_id = ?)
    ORDER BY a.created_at DESC
');
$stmt->bind_param("i", $user_id);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
?>

<div class="container">
    <div class="dashboard-header">
        <h1>My Dashboard</h1>
        <a href="profile.php" class="btn btn-outline">Edit Profile</a>
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

<?php include '../includes/footer.php'; ?>