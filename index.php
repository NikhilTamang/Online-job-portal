<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$search_query    = isset($_GET['q'])        ? trim($_GET['q'])        : '';
$filter_category = isset($_GET['category']) ? trim($_GET['category']) : '';
$is_filtering    = $search_query || $filter_category;

$per_page    = 9;
$current_page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);

$base = "FROM jobs
         JOIN employers ON jobs.employer_id = employers.id
         JOIN users    ON employers.user_id  = users.id";

$conditions = [];
$params     = [];
$types      = '';

if ($search_query) {
    $conditions[] = "(jobs.title LIKE ? OR jobs.location LIKE ? OR employers.company_name LIKE ?)";
    $like = "%$search_query%";
    $params = array_merge($params, [$like, $like, $like]);
    $types .= 'sss';
}

if ($filter_category) {
    $conditions[] = "jobs.category = ?";
    $params[] = $filter_category;
    $types   .= 's';
}

$where = !empty($conditions) ? ' WHERE ' . implode(' AND ', $conditions) : '';

$total_jobs   = 0;
$total_pages  = 1;

if (!$is_filtering) {
    $count_stmt = $conn->prepare("SELECT COUNT(*) as cnt $base");
    $count_stmt->execute();
    $total_jobs  = $count_stmt->get_result()->fetch_assoc()['cnt'];
    $total_pages = max(1, (int)ceil($total_jobs / $per_page));
    $current_page = min($current_page, $total_pages);
}

if ($is_filtering) {

    $stmt = $conn->prepare("SELECT jobs.*, employers.company_name, users.name as recruiter_name $base $where ORDER BY jobs.created_at DESC");
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
} else {

    $offset = ($current_page - 1) * $per_page;
    $stmt = $conn->prepare("SELECT jobs.*, employers.company_name, users.name as recruiter_name $base ORDER BY jobs.created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $per_page, $offset);
}

$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

function pageUrl($page, $q = '', $cat = '') {
    $params = ['page' => $page];
    if ($q)   $params['q']        = $q;
    if ($cat) $params['category'] = $cat;
    return 'index.php?' . http_build_query($params);
}

include 'includes/header.php';
?>

<div class="hero-section text-center">
    <h1 class="hero-title">Find Your Dream Job Today</h1>
    <p class="hero-subtitle">
        Browse thousands of job openings from top companies and startups. Your next opportunity is just a search away.
    </p>

    <div class="search-wrapper search-wrapper-wide mb-8">
        <form action="index.php" method="GET">
            <div class="search-input-group">

                <svg class="search-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>

                <input type="text" class="search-input" name="q"
                    placeholder="Job title, keywords, or company..."
                    autocomplete="off"
                    value="<?= isset($_GET['q']) ? esc($_GET['q']) : '' ?>">

                <span class="search-divider"></span>

                <select name="category" class="search-category-select" id="category-filter">
                    <option value="">All Categories</option>
                    <?php
                    $categories = [
                        'IT'          => 'IT',
                        'Marketing'   => 'Marketing',
                        'Finance'     => 'Finance',
                        'Healthcare'  => 'Healthcare',
                        'Education'   => 'Education',
                        'Engineering' => 'Engineering',
                        'Sales'       => 'Sales',
                        'Design'      => 'Design',
                        'Operations'  => 'Operations',
                        'HR'          => 'Human Resources',
                        'Legal'       => 'Legal',
                        'Other'       => 'Other',
                    ];
                    foreach ($categories as $val => $label):
                        $selected = (isset($_GET['category']) && $_GET['category'] === $val) ? 'selected' : '';
                    ?>
                        <option value="<?= esc($val) ?>" <?= $selected ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="search-btn">
                    Search
                </button>
            </div>
        </form>
    </div>

    <?php if (!isLoggedIn()): ?>
        <div class="flex justify-center gap-4 mt-4">
            <a href="register.php?role=seeker" class="btn btn-primary hero-btn">Find a Job</a>
            <a href="register.php?role=employer" class="btn btn-outline hero-btn">Post a Job</a>
        </div>
    <?php endif; ?>
</div>

<div class="section-header">
    <h2><?= $is_filtering ? 'Search Results' : 'Latest Opportunities' ?></h2>
    <?php if (!$is_filtering && $total_jobs > 0): ?>
        <span class="section-badge">
            <?= $total_jobs ?> <?= $total_jobs === 1 ? 'job' : 'jobs' ?> available
        </span>
    <?php elseif ($is_filtering && count($jobs) > 0): ?>
        <span class="section-badge">
            <?= count($jobs) ?> <?= count($jobs) === 1 ? 'result' : 'results' ?> found
        </span>
    <?php endif; ?>
</div>

<div class="jobs-grid">
    <?php foreach ($jobs as $job): ?>
        <div class="card flex flex-col">
            <div class="mb-4">
                <?php if (!empty($job['category'])): ?>
                    <span class="job-card-category"><?= esc($job['category']) ?></span>
                <?php endif; ?>
                <h3 class="job-card-title"><?= esc($job['title']) ?></h3>
                <div class="job-card-company">
                    🏢 <?= esc($job['company_name']) ?>
                </div>
                <div class="job-card-meta">
                    <span class="job-card-icon">📍</span>
                    <span><?= esc($job['location']) ?></span>
                    <span class="job-card-divider">|</span>
                    <span class="job-card-icon">💰</span>
                    <span>$<?= number_format($job['salary']) ?></span>
                </div>
            </div>

            <p class="job-card-description">
                <?= nl2br(esc(substr($job['description'], 0, 110))) ?>...
            </p>

            <div class="mt-auto">
                <a href="job.php?id=<?= $job['id'] ?>" class="btn btn-primary btn-full">View Details</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (count($jobs) === 0): ?>
    <div class="text-center empty-state">
        <p style="font-size:2.5rem;margin-bottom:1rem;">🔍</p>
        <p><?= $is_filtering ? 'No jobs found matching your search. Try different keywords.' : 'No jobs posted yet. Check back soon!' ?></p>
        <?php if ($is_filtering): ?>
            <a href="index.php" class="btn btn-outline" style="margin-top:1rem;">Clear Search</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (!$is_filtering && $total_pages > 1): ?>
<div class="pagination-wrapper">
    <p class="pagination-info">
        Showing <?= (($current_page - 1) * $per_page) + 1 ?>–<?= min($current_page * $per_page, $total_jobs) ?> of <?= $total_jobs ?> jobs
    </p>
    <nav class="pagination" aria-label="Job listings pagination">

        <?php if ($current_page > 1): ?>
            <a href="<?= esc(pageUrl($current_page - 1, $search_query, $filter_category)) ?>" class="page-link">← Prev</a>
        <?php else: ?>
            <span class="page-link disabled">← Prev</span>
        <?php endif; ?>

        <?php
        $start = max(1, $current_page - 2);
        $end   = min($total_pages, $current_page + 2);
        if ($start > 1): ?>
            <a href="<?= esc(pageUrl(1, $search_query, $filter_category)) ?>" class="page-link">1</a>
            <?php if ($start > 2): ?><span class="page-dots">…</span><?php endif; ?>
        <?php endif; ?>

        <?php for ($p = $start; $p <= $end; $p++): ?>
            <a href="<?= esc(pageUrl($p, $search_query, $filter_category)) ?>"
               class="page-link <?= $p === $current_page ? 'active' : '' ?>">
                <?= $p ?>
            </a>
        <?php endfor; ?>

        <?php if ($end < $total_pages): ?>
            <?php if ($end < $total_pages - 1): ?><span class="page-dots">…</span><?php endif; ?>
            <a href="<?= esc(pageUrl($total_pages, $search_query, $filter_category)) ?>" class="page-link"><?= $total_pages ?></a>
        <?php endif; ?>

        <?php if ($current_page < $total_pages): ?>
            <a href="<?= esc(pageUrl($current_page + 1, $search_query, $filter_category)) ?>" class="page-link">Next →</a>
        <?php else: ?>
            <span class="page-link disabled">Next →</span>
        <?php endif; ?>

    </nav>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>