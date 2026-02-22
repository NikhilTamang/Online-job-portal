<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isEmployer()) {
    redirect('../login.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $salary = $_POST['salary'];

    $stmt = $conn->prepare("SELECT id FROM employers WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $employer = $stmt->get_result()->fetch_assoc();

    if ($employer) {
        $stmt = $conn->prepare("INSERT INTO jobs (employer_id, title, description, location, salary) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssd", $employer['id'], $title, $description, $location, $salary);
        if ($stmt->execute()) {
            $success = "Job posted successfully!";
        } else {
            $error = "Failed to post job.";
        }
    } else {
        $error = "Employer profile not found.";
    }
}

include '../includes/header.php';
?>

<div class="post-job-container">
    <div class="flex justify-between items-center mb-4">
        <h1>Post a New Job</h1>
        <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <div class="card">
        <?php if ($error): ?>
            <div class="badge badge-danger alert-badge">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="badge badge-success alert-badge">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Job Title</label>
                <input type="text" name="title" required placeholder="e.g. Senior Software Engineer">
            </div>

            <div class="form-group">
                <label>Location</label>
                <input type="text" name="location" required placeholder="e.g. New York, NY (or Remote)">
            </div>

            <div class="form-group">
                <label>Salary ($)</label>
                <input type="number" name="salary" required placeholder="e.g. 120000">
            </div>

            <div class="form-group">
                <label>Job Description</label>
                <textarea name="description" rows="10" required placeholder="Describe the role, responsibilities, and requirements..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Post Job</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>