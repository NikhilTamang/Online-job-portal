<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeeker()) {
    if (isset($_POST['job_id'])) {
        $_SESSION['apply_job_id'] = (int)$_POST['job_id'];
    } elseif (isset($_GET['job_id'])) {
        $_SESSION['apply_job_id'] = (int)$_GET['job_id'];
    }
    redirect('../login.php');
}

$job_id  = 0;
$autoApply = false;
if (isset($_POST['job_id'])) {
    $job_id = (int)$_POST['job_id'];
} elseif (isset($_GET['job_id'])) {
    $job_id    = (int)$_GET['job_id'];
    $autoApply = isset($_GET['auto']) && $_GET['auto'] == '1';
}

if (!$job_id) {
    redirect('../index.php');
}

$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';

$stmt = $conn->prepare("SELECT title, deadline, category FROM jobs WHERE id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    redirect('../index.php');
}

$today = date('Y-m-d');

if (!empty($job['deadline']) && $today > $job['deadline']) {
    $error = "Application Failed: The job deadline has passed.";
}

if (!$error) {
    $pref_stmt = $conn->prepare("SELECT preferred_category FROM seekers WHERE user_id = ?");
    $pref_stmt->bind_param("i", $user_id);
    $pref_stmt->execute();
    $pref_data = $pref_stmt->get_result()->fetch_assoc();

    $pref_cats = !empty($pref_data['preferred_category'])
        ? array_map('trim', explode(',', $pref_data['preferred_category']))
        : [];

    if (!empty($pref_cats) && !empty($job['category']) && !in_array($job['category'], $pref_cats)) {
        $error = "You can only apply to jobs that match your preferred categories.";
    }
}

if (!$error) {
    $dup = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = (SELECT id FROM seekers WHERE user_id = ?)");
    $dup->bind_param("ii", $job_id, $user_id);
    $dup->execute();
    $dup->store_result();
    if ($dup->num_rows > 0) {
        $error = "You have already applied for this job.";
    }
}

$confirmed = (isset($_POST['confirm']) && $_POST['confirm'] == '1') || $autoApply;

if ($confirmed && !$error) {
    $dl_stmt = $conn->prepare("SELECT deadline FROM jobs WHERE id = ?");
    $dl_stmt->bind_param("i", $job_id);
    $dl_stmt->execute();
    $latest = $dl_stmt->get_result()->fetch_assoc();

    if (!empty($latest['deadline']) && $today > $latest['deadline']) {
        $error = "Application Failed: The job deadline has passed.";
    } else {
        $sk = $conn->prepare("SELECT id FROM seekers WHERE user_id = ?");
        $sk->bind_param("i", $user_id);
        $sk->execute();
        $seeker = $sk->get_result()->fetch_assoc();

        if ($seeker) {
            $ins = $conn->prepare("INSERT INTO applications (job_id, seeker_id) VALUES (?, ?)");
            $ins->bind_param("ii", $job_id, $seeker['id']);
            if ($ins->execute()) {
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
            <?php if ($autoApply): ?>
                <div class="badge badge-warning alert-badge" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;margin-bottom:1rem;">
                    🔒 You were redirected here after logging in. Confirm to complete your application.
                </div>
            <?php endif; ?>

            <?php if (!empty($job['deadline'])): ?>
                <p class="text-center" style="font-size:0.875rem;color:#6b7280;margin-bottom:0.5rem;">
                    Application deadline: <strong><?= date('M j, Y', strtotime($job['deadline'])) ?></strong>
                </p>
            <?php endif; ?>

            <p class="text-center apply-text">
                Are you sure you want to apply for this position? We will share your profile and resume with the employer.
            </p>

            <form method="post" action="apply.php">
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