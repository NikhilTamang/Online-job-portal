<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

// --- Search & Filter inputs ---
$search_query    = isset($_GET['q'])        ? trim($_GET['q'])        : '';
$filter_category = isset($_GET['category']) ? trim($_GET['category']) : '';

// --- Build base query ---
$query = "SELECT jobs.*, employers.company_name, users.name as recruiter_name 
          FROM jobs 
          JOIN employers ON jobs.employer_id = employers.id 
          JOIN users ON employers.user_id = users.id";

$conditions = [];
$params     = [];
$types      = '';

// Text search condition
if ($search_query) {
    $conditions[] = "(jobs.title LIKE ? OR jobs.location LIKE ? OR employers.company_name LIKE ?)";
    $like = "%$search_query%";
    $params = array_merge($params, [$like, $like, $like]);
    $types .= 'sss';
}

// Category filter condition
if ($filter_category) {
    $conditions[] = "jobs.category = ?";
    $params[] = $filter_category;
    $types   .= 's';
}

// Assemble WHERE clause if conditions exist
if (!empty($conditions)) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= " ORDER BY created_at DESC";

// Apply limit only when browsing with no filters
$is_filtering = $search_query || $filter_category;
if (!$is_filtering) {
    $query .= " LIMIT 6";
}

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include 'includes/header.php';
?>

<div class="hero-section text-center">
    <h1 class="hero-title">Find Your Dream Job Today</h1>
    <p class="hero-subtitle">
        Browse thousands of job openings from top companies and startups.
    </p>

    <div class="search-wrapper search-wrapper-wide mb-8">
        <form action="index.php" method="GET">
            <div class="search-input-group">
                <!-- Search icon -->
                <svg class="search-icon" width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>

                <!-- Keyword search input -->
                <input type="text" class="search-input" name="q"
                    placeholder="Job title, keywords, or company..."
                    autocomplete="off"
                    value="<?= isset($_GET['q']) ? esc($_GET['q']) : '' ?>">

                <!-- Category filter divider -->
                <span class="search-divider"></span>

                <!-- Category filter dropdown -->
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

                <!-- Search button -->
                <button type="submit" class="search-btn">
                    Search
                </button>
            </div>
        </form>
    </div>

    <?php if (!isLoggedIn()): ?>
        <div class="flex justify-center gap-4 mt-4">
            <a href="register.php?role=seeker" class="btn btn-outline hero-btn">Find a Job</a>
            <a href="register.php?role=employer" class="btn btn-outline hero-btn">Post a Job</a>
        </div>
    <?php endif; ?>
</div>

<div class="flex justify-between items-center mb-8">
    <h2><?= $is_filtering ? 'Search Results' : 'Latest Opportunities' ?></h2>
</div>

<div class="jobs-grid">
    <?php foreach ($jobs as $job): ?>
        <div class="card flex flex-col">
            <div class="mb-4">
                <h3 class="job-card-title"><?= esc($job['title']) ?></h3>
                <div class="job-card-company">
                    Recruiter: <?= esc($job['company_name']) ?>
                </div>
                <div class="job-card-meta">
                    <span><?= esc($job['location']) ?></span>
                    <span class="job-card-divider">|</span>
                    <span>$<?= number_format($job['salary']) ?></span>
                </div>
            </div>

            <p class="job-card-description">
                <?= nl2br(esc(substr($job['description'], 0, 100))) ?>...
            </p>

            <div class="mt-auto">
                <a href="job.php?id=<?= $job['id'] ?>" class="btn btn-outline btn-full">View Details</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (count($jobs) === 0): ?>
    <div class="text-center empty-state">
        <p><?= $is_filtering ? 'No jobs found matching your search.' : 'No jobs posted yet. Check back soon!' ?></p>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>