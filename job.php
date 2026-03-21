<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_GET['id'])) {
    redirect('index.php');
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare('
    SELECT j.*, e.company_name, e.user_id as employer_user_id 
    FROM jobs j 
    JOIN employers e ON j.employer_id = e.id 
    WHERE j.id = ?
');
$stmt->bind_param("i", $id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    redirect('index.php');
}


$today      = date('Y-m-d');
$isExpired  = !empty($job['deadline']) && $today > $job['deadline'];

include 'includes/header.php';
?>

<div class="container">
    <div class="job-detail-card">
        <div class="job-header">
            <h1 class="job-title"><?= esc($job['title']) ?></h1>
            <div class="job-meta">
                <span class="job-company"><?= esc($job['company_name']) ?></span>
                <span>•</span>
                <span>Posted <?= date('M j, Y', strtotime($job['created_at'])) ?></span>
            </div>

            
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
                <span class="job-detail-value">$<?= number_format($job['salary']) ?></span>
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
                <?php else: ?>
                    <p class="job-cta-text">Apply now to get in touch with the employer.</p>
                <?php endif; ?>
            </div>

            <?php if ($isExpired): ?>
                
                <button class="btn btn-secondary" disabled>Deadline Passed</button>

            <?php elseif (isSeeker()): ?>
                <?php
                
                $user_id = $_SESSION['user_id'];
                $stmt = $conn->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = (SELECT id FROM seekers WHERE user_id = ?)");
                $stmt->bind_param("ii", $job['id'], $user_id);
                $stmt->execute();
                $stmt->store_result();
                $hasApplied = $stmt->num_rows > 0;
                ?>
                <?php if ($hasApplied): ?>
                    <button class="btn btn-secondary" disabled>Applied</button>
                <?php else: ?>
                    <form action="seeker/apply.php" method="POST" class="action-form">
                        <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                        <button type="submit" class="btn btn-primary">Apply Now</button>
                    </form>
                <?php endif; ?>

            <?php elseif (!isLoggedIn()): ?>
                <a href="login.php" class="btn btn-primary">Login to Apply</a>
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