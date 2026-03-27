<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isEmployer()) {
    redirect('../login.php');
}

if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$job_id  = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';


$stmt = $conn->prepare("
    SELECT j.* FROM jobs j
    WHERE j.id = ? AND j.employer_id = (SELECT id FROM employers WHERE user_id = ?)
");
$stmt->bind_param("ii", $job_id, $user_id);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $location    = trim($_POST['location']);
    $salary      = trim($_POST['salary']);
    $description = trim($_POST['description']);
    $deadline    = trim($_POST['deadline']);
    $category    = trim($_POST['category']);

    if (empty($title) || empty($location) || empty($description)) {
        $error = "Title, location, and description are required.";
    } else {
        $deadlineVal = !empty($deadline) ? $deadline : null;
        
        $stmt = $conn->prepare("UPDATE jobs SET title = ?, location = ?, salary = ?, description = ?, deadline = ?, category = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $title, $location, $salary, $description, $deadlineVal, $category, $job_id);
        if ($stmt->execute()) {
            $success = "Job updated successfully!";
            
            $stmt = $conn->prepare("SELECT * FROM jobs WHERE id = ?");
            $stmt->bind_param("i", $job_id);
            $stmt->execute();
            $job = $stmt->get_result()->fetch_assoc();
        } else {
            $error = "Failed to update job.";
        }
    }
}

include '../includes/header.php';
?>

<div class="post-job-container">
    <div class="flex justify-between items-center mb-4">
        <h1>Edit Job</h1>
        <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <div class="card">
        <?php if ($error): ?>
            <div class="badge badge-danger alert-badge"><?= esc($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="badge badge-success alert-badge"><?= esc($success) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Job Title</label>
                <input type="text" name="title" value="<?= esc($job['title']) ?>" required placeholder="e.g. Senior Software Engineer">
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" value="<?= esc($job['location']) ?>" required placeholder="e.g. New York, NY (or Remote)">
            </div>

            <div class="form-group">
                <label>Salary ($)</label>
                <input type="number" name="salary" value="<?= esc($job['salary']) ?>" placeholder="e.g. 120000">
            </div>

            
            <div class="form-group">
                <label>Job Category</label>
                <select name="category">
                    <option value="">-- Select Category --</option>
                    <?php
                    $categories = ['IT','Marketing','Finance','Healthcare','Education','Engineering','Sales','Design','Operations','HR','Legal','Other'];
                    $cat_labels = ['IT'=>'IT','Marketing'=>'Marketing','Finance'=>'Finance','Healthcare'=>'Healthcare','Education'=>'Education','Engineering'=>'Engineering','Sales'=>'Sales','Design'=>'Design','Operations'=>'Operations','HR'=>'Human Resources','Legal'=>'Legal','Other'=>'Other'];
                    foreach ($categories as $cat): ?>
                        <option value="<?= esc($cat) ?>" <?= (isset($job['category']) && $job['category'] === $cat) ? 'selected' : '' ?>>
                            <?= esc($cat_labels[$cat]) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Application Deadline</label>
                <input type="date" name="deadline" value="<?= esc($job['deadline'] ?? '') ?>">
                <small style="color:#6b7280;font-size:0.8rem;">Leave blank to remove the deadline.</small>
            </div>

            <div class="form-group">
                <label>Job Description</label>
                <textarea name="description" rows="10" required placeholder="Describe the role, responsibilities, and requirements..."><?= esc($job['description']) ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Save Changes</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
