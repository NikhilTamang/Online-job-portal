<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle delete
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['job_id'])) {
    $jid = (int)$_POST['job_id'];
    $stmt = $conn->prepare("DELETE FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $jid);
    if ($stmt->execute()) {
        $success = "Job deleted successfully.";
    } else {
        $error = "Failed to delete job.";
    }
}

// Handle edit
if (isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['job_id'])) {
    $jid         = (int)$_POST['job_id'];
    $title       = trim($_POST['title']);
    $location    = trim($_POST['location']);
    $salary      = trim($_POST['salary']);
    $description = trim($_POST['description']);

    $stmt = $conn->prepare("UPDATE jobs SET title = ?, location = ?, salary = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $title, $location, $salary, $description, $jid);
    if ($stmt->execute()) {
        $success = "Job updated successfully.";
    } else {
        $error = "Failed to update job.";
    }
}

// Fetch all jobs
$jobs = $conn->query("
    SELECT j.*, e.company_name, u.name as recruiter_name
    FROM jobs j
    JOIN employers e ON j.employer_id = e.id
    JOIN users u ON e.user_id = u.id
    ORDER BY j.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Editing mode
$editing = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    foreach ($jobs as $j) {
        if ((int)$j['id'] === $edit_id) {
            $editing = $j;
            break;
        }
    }
    // Refetch if deleted above
    if (!$editing) {
        $stmt = $conn->prepare("SELECT j.*, e.company_name FROM jobs j JOIN employers e ON j.employer_id = e.id WHERE j.id = ?");
        $stmt->bind_param("i", $edit_id);
        $stmt->execute();
        $editing = $stmt->get_result()->fetch_assoc();
    }
}

include '../includes/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <h1>Manage Jobs</h1>
    <a href="dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
</div>

<?php if ($error): ?>
    <div class="badge badge-danger alert-badge mb-4"><?= esc($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="badge badge-success alert-badge mb-4"><?= esc($success) ?></div>
<?php endif; ?>

<?php if ($editing): ?>
<div class="card mb-8 post-job-container">
    <h2 class="dashboard-title">Edit Job</h2>
    <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="job_id" value="<?= $editing['id'] ?>">
        <div class="form-group">
            <label>Job Title</label>
            <input type="text" name="title" value="<?= esc($editing['title']) ?>" required>
        </div>
        <div class="form-group">
            <label>Location</label>
            <input type="text" name="location" value="<?= esc($editing['location']) ?>" required>
        </div>
        <div class="form-group">
            <label>Salary ($)</label>
            <input type="number" name="salary" value="<?= esc($editing['salary']) ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="8" required><?= esc($editing['description']) ?></textarea>
        </div>
        <div class="flex gap-4">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="jobs.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="card">
    <h2 class="dashboard-title">All Job Postings (<?= count($jobs) ?>)</h2>
    <?php if (count($jobs) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Location</th>
                        <th>Posted On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $j): ?>
                        <tr>
                            <td><?= $j['id'] ?></td>
                            <td>
                                <a href="../job.php?id=<?= $j['id'] ?>" class="table-link" target="_blank">
                                    <?= esc($j['title']) ?>
                                </a>
                            </td>
                            <td><?= esc($j['company_name']) ?></td>
                            <td><?= esc($j['location']) ?></td>
                            <td><?= date('M j, Y', strtotime($j['created_at'])) ?></td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="jobs.php?edit=<?= $j['id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Delete this job posting? This will also remove all applications.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
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
            <p>No jobs have been posted yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
