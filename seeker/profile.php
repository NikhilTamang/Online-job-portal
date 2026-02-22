<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeeker()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM seekers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seeker = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $headline = trim($_POST['headline']);
    $skills   = trim($_POST['skills']);

    if (isset($_FILES['resume']) && $_FILES['resume']['size'] > 0) {
        $file = $_FILES['resume'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (strtolower($ext) != 'pdf') {
            $error = "Only PDF allowed for resume.";
        } else {
            if (!file_exists('../uploads/resumes')) {
                mkdir('../uploads/resumes', 0777, true);
            }

            $newFileName = 'resume_' . $user_id . '_' . time() . '.pdf';
            $destination = '../uploads/resumes/' . $newFileName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt2 = $conn->prepare("UPDATE seekers SET resume_path = ? WHERE user_id = ?");
                $stmt2->bind_param("si", $newFileName, $user_id);
                $stmt2->execute();
            } else {
                $error = "Failed to upload resume.";
            }
        }
    }

    if (!$error) {
        $stmt3 = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt3->bind_param("ssi", $name, $email, $user_id);
        $stmt3->execute();

        $stmt4 = $conn->prepare("UPDATE seekers SET headline = ?, skills = ? WHERE user_id = ?");
        $stmt4->bind_param("ssi", $headline, $skills, $user_id);
        $stmt4->execute();

        $user['name']  = $name;
        $user['email'] = $email;

        $success = "Profile updated successfully!";

        $stmt = $conn->prepare("SELECT * FROM seekers WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $seeker = $stmt->get_result()->fetch_assoc();
    }
}

include '../includes/header.php';
?>

<div class="profile-container">
    <div class="flex justify-between items-center mb-4">
        <h1>Edit Profile</h1>
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

        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= esc($user['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= esc($user['email']) ?>" required>
            </div>

            <div class="form-group">
                <label class="text-black">Headline</label>
                <input type="text" name="headline" required placeholder="Your professional headline" value="<?= esc($seeker['headline'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="text-black">Skills</label>
                <textarea class="skills" name="skills" rows="4" placeholder="List your skills separated by commas"><?= esc($seeker['skills'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Resume (PDF)</label>
                <?php if (!empty($seeker['resume_path'])): ?>
                    <div class="resume-link-container">
                        Current: <a href="../uploads/resumes/<?= esc($seeker['resume_path']) ?>" target="_blank" class="resume-link">View Resume</a>
                    </div>
                <?php endif; ?>
                <input type="file" name="resume" accept=".pdf">
            </div>

            <button type="submit" class="btn btn-primary btn-full">Update Profile</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>