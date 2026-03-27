<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare('
    SELECT j.*, e.company_name, e.user_id as employer_user_id,
           u.name as recruiter_name
    FROM jobs j
    JOIN employers e ON j.employer_id = e.id
    JOIN users u ON e.user_id = u.id
    WHERE j.id = ?
');
$stmt->bind_param("i", $id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    redirect('index.php');
}

$today     = date('Y-m-d');
$isExpired = !empty($job['deadline']) && $today > $job['deadline'];

$hasApplied      = false;
$categoryBlocked = false;
$seekerPrefCats  = [];

if (isSeeker()) {
    $uid = $_SESSION['user_id'];

    $chk = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = (SELECT id FROM seekers WHERE user_id = ?)");
    $chk->bind_param("ii", $job['id'], $uid);
    $chk->execute();
    $chk->store_result();
    $hasApplied = $chk->num_rows > 0;

    $ps = $conn->prepare("SELECT preferred_category FROM seekers WHERE user_id = ?");
    $ps->bind_param("i", $uid);
    $ps->execute();
    $pref_row      = $ps->get_result()->fetch_assoc();
    $seekerPrefCats = !empty($pref_row['preferred_category'])
        ? array_map('trim', explode(',', $pref_row['preferred_category']))
        : [];

    if (!empty($seekerPrefCats) && !empty($job['category']) && !in_array($job['category'], $seekerPrefCats)) {
        $categoryBlocked = true;
    }
}

include 'includes/header.php';
?>

<div class="container">
    <div class="job-detail-card">
        <div class="job-header">
            <h1 class="job-title"><?= esc($job['title']) ?></h1>
            <div class="job-meta">
                <span class="job-company">🏢 <?= esc($job['company_name']) ?></span>
                <span>•</span>
                <span>👤 <?= esc($job['recruiter_name']) ?></span>
                <span>•</span>
                <span>Posted <?= date('M j, Y', strtotime($job['created_at'])) ?></span>
            </div>

            <?php if (!empty($job['category'])): ?>
                <div style="margin-top:0.5rem;">
                    <span class="job-card-category"><?= esc($job['category']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($job['deadline'])): ?>
                <div class="vacancy-status <?= $isExpired ? 'vacancy-closed' : 'vacancy-open' ?>">
                    <?php if ($isExpired): ?>
                        ⛔ Closed &mdash; Deadline was <?= date('M j, Y', strtotime($job['deadline'])) ?>
                    <?php else: ?>
                        ✅ Open &mdash; Apply before <?= date('M j, Y', strtotime($job['deadline'])) ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="job-detail-grid">
            <div class="job-detail-item">
                <span class="job-detail-label">Location</span>
                <span class="job-detail-value"><?= esc($job['location']) ?></span>
            </div>
            <div class="job-detail-item">
                <span class="job-detail-label">Salary</span>
                <span class="job-detail-value">Rs <?= number_format($job['salary']) ?></span>
            </div>
            <?php if (!empty($job['deadline'])): ?>
            <div class="job-detail-item">
                <span class="job-detail-label">Application Deadline</span>
                <span class="job-detail-value <?= $isExpired ? 'deadline-expired' : 'deadline-active' ?>">
                    <?= date('M j, Y', strtotime($job['deadline'])) ?>
                </span>
            </div>
            <?php endif; ?>
        </div>

        <div class="job-section">
            <h3 class="job-section-title">Job Description</h3>
            <div class="job-description-content">
                <?= nl2br(esc($job['description'])) ?>
            </div>
        </div>


        <div class="job-cta">
            <div>
                <h4 class="job-cta-title">Interested in this role?</h4>
                <?php if ($isExpired): ?>
                    <p class="job-cta-text deadline-expired-msg">
                        ⚠️ Job Not Available &mdash; Application deadline has passed.
                    </p>
                <?php elseif ($categoryBlocked): ?>
                    <p class="job-cta-text" style="color:#b45309;">
                        ⚠️ You are not eligible to apply for this category.<br>
                        <small style="color:#6b7280;">
                            This job is in <strong><?= esc($job['category']) ?></strong>, but your preferred
                            categories are: <strong><?= esc(implode(', ', $seekerPrefCats)) ?></strong>.
                            <a href="seeker/profile.php" style="color:#4f46e5;">Update preferences</a> to apply.
                        </small>
                    </p>
                <?php else: ?>
                    <p class="job-cta-text">Apply now to get in touch with the employer.</p>
                <?php endif; ?>
            </div>

            <?php if ($isExpired): ?>
                <button class="btn btn-secondary" disabled>Deadline Passed</button>

            <?php elseif ($categoryBlocked): ?>
                <button class="btn btn-secondary" disabled
                    title="Your preferred categories do not include '<?= esc($job['category']) ?>'">
                    Not Eligible
                </button>

            <?php elseif (isSeeker()): ?>
                <?php if ($hasApplied): ?>
                    <button class="btn btn-secondary" disabled>✔ Applied</button>
                <?php else: ?>
                    <form action="seeker/apply.php" method="POST" class="action-form">
                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                        <button type="submit" class="btn btn-primary">Apply Now</button>
                    </form>
                <?php endif; ?>

            <?php elseif (!isLoggedIn()): ?>
                <?php
                $_SESSION['apply_job_id'] = $job['id'];
                ?>
                <a href="login.php?from=job&job_id=<?= $job['id'] ?>" class="btn btn-primary">Login to Apply</a>

            <?php endif; ?>
        </div>
    </div>

    <div class="back-link-container">
        <a href="index.php" class="back-link">
            &larr; Back to Jobs
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>