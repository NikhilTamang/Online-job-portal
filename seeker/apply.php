<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeeker()) {
    redirect('../login.php');
}

if (!isset($_POST['job_id'])) {
    redirect('../index.php');
}

$job_id = (int)$_POST['job_id'];
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch job details
$stmt = $conn->prepare("SELECT title FROM jobs WHERE id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    redirect('../index.php');
}

// Check if user already applied
$stmt = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = (SELECT id FROM seekers WHERE user_id = ?)");
$stmt->bind_param("ii", $job_id, $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $error = "You have already applied for this job.";
}

// Handle application
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm']) && !$error) {
    // Get seeker ID
    $stmt = $conn->prepare("SELECT id FROM seekers WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $seeker = $stmt->get_result()->fetch_assoc();

    if ($seeker) {
        $stmt2 = $conn->prepare("INSERT INTO applications (job_id, seeker_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $job_id, $seeker['id']);
        if ($stmt2->execute()) {
            $success = "Application submitted successfully!";
        } else {
            $error = "Failed to submit application.";
        }
    } else {
        $error = "Seeker profile not found.";
    }
}

include '../includes/header.php';
?>

<div class="apply-container">
    <div class="card">
        <h2 class="text-center apply-title">Apply for <?= esc($job['title']) ?></h2>

        <?php if ($error): ?>
            <div class="badge badge-danger alert-badge">
                <?= $error ?>
            </div>
            <div class="text-center mt-4">
                <a href="../job.php?id=<?= $job_id ?>" class="btn btn-outline">Back to Job</a>
            </div>
        <?php elseif ($success): ?>
            <div class="badge badge-success alert-badge">
                <?= $success ?>
            </div>
            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            </div>
        <?php else: ?>
            <p class="text-center apply-text">
                Are you sure you want to apply for this position? We will share your profile and resume with the employer.
            </p>

            <form method="post">
                <input type="hidden" name="job_id" value="<?= $job_id ?>">
                <input type="hidden" name="confirm" value="1">

                <div class="apply-actions">
                    <a href="../job.php?id=<?= $job_id ?>" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">Confirm Application</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>