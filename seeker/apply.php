<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeeker()) {
    redirect('../login.php');
}

if (!isset($_POST['job_id'])) {
    redirect('../index.php');
}

$job_id  = (int)$_POST['job_id'];
$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';

// Fetch job details including deadline
$stmt = $conn->prepare("SELECT title, deadline FROM jobs WHERE id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    redirect('../index.php');
}

// --- Deadline check (first gate) ---
$today = date('Y-m-d');
if (!empty($job['deadline']) && $today > $job['deadline']) {
    $error = "Application Failed: The job deadline has passed.";
}

// Check if user already applied
if (!$error) {
    $stmt = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = (SELECT id FROM seekers WHERE user_id = ?)");
    $stmt->bind_param("ii", $job_id, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error = "You have already applied for this job.";
    }
}

// Handle confirmation & final submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm']) && !$error) {

    // --- Re-check deadline at submission time (second gate) ---
    $stmt = $conn->prepare("SELECT deadline FROM jobs WHERE id = ?");
    $stmt->bind_param("i", $job_id);
    $stmt->execute();
    $latest = $stmt->get_result()->fetch_assoc();

    if (!empty($latest['deadline']) && $today > $latest['deadline']) {
        $error = "Application Failed: The job deadline has passed.";
    } else {
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
}

include '../includes/header.php';
?>

<div class="apply-container">
    <div class="card">
        <h2 class="text-center apply-title">Apply for <?= esc($job['title']) ?></h2>

        <?php if ($error): ?>
            <div class="badge badge-danger alert-badge">
                <?= esc($error) ?>
            </div>
            <div class="text-center mt-4">
                <a href="../job.php?id=<?= $job_id ?>" class="btn btn-outline">Back to Job</a>
            </div>

        <?php elseif ($success): ?>
            <div class="badge badge-success alert-badge">
                <?= esc($success) ?>
            </div>
            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
            </div>

        <?php else: ?>
            <?php if (!empty($job['deadline'])): ?>
                <p class="text-center" style="font-size:0.875rem;color:#6b7280;margin-bottom:0.5rem;">
                    Application deadline: <strong><?= date('M j, Y', strtotime($job['deadline'])) ?></strong>
                </p>
            <?php endif; ?>

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