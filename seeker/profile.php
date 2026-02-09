<?php
session_start();

require_once '../includes/db.php';
require_once '../includes/functions.php';


if (!isLoggedIn() || !isSeeker()) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$result = $conn->prepare("SELECT * FROM users WHERE id=?");
$result->bind_param("i", $user_id);
$result->execute();
$userResult = $result->get_result();
$user = $userResult->fetch_assoc();

$result2 = $conn->prepare("SELECT * FROM seekers WHERE user_id=?");
$result2->bind_param("i", $user_id);
$result2->execute();
$seekerResult = $result2->get_result();
$seeker = $seekerResult->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $headline = trim($_POST['headline']);
    $skills = trim($_POST['skills']);

    if (empty($name) || empty($email) || empty($headline)) {
    $error = "Please fill all required fields.";
    }

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === 0) {
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

            $maxSize = 2 * 1024 * 1024; // 2MB
            if ($file['size'] > $maxSize) {
                $error = "Resume must be under 2MB.";
            }

            if (!empty($seeker['resume_path'])) {
                $old = '../uploads/resumes/' . $seeker['resume_path'];
                if (file_exists($old)) {
                    unlink($old);
                }
            }

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt2 = $conn->prepare("UPDATE seekers SET resume_path=? WHERE user_id=?");
                $stmt2->bind_param("si", $newFileName, $user_id);
                $stmt2->execute();
            } else {
                $error = "Failed to upload resume.";
            }
        }
    }

    if (!$error) {
        $stmt3 = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $stmt3->bind_param("ssi", $name, $email, $user_id);
        $stmt3->execute();

        $check = $conn->prepare("SELECT id FROM seekers WHERE user_id=?");
        $check->bind_param("i", $user_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $stmt4 = $conn->prepare("
                UPDATE seekers 
                SET headline=?, skills=? 
                WHERE user_id=?
            ");
            $stmt4->bind_param("ssi", $headline, $skills, $user_id);
            $stmt4->execute();
        } else {
            $stmt4 = $conn->prepare("
                INSERT INTO seekers (user_id, headline, skills) 
                VALUES (?, ?, ?)
            ");
            $stmt4->bind_param("iss", $user_id, $headline, $skills);
            $stmt4->execute();
        }

        $user['name'] = $name;
        $user['email'] = $email;

        $success = "Profile updated successfully!";

        $result2 = $conn->prepare("SELECT * FROM seekers WHERE user_id=?");
        $result2->bind_param("i", $user_id);
        $result2->execute();
        $seekerResult = $result2->get_result();
        $seeker = $seekerResult->fetch_assoc();
    }
}

include '../includes/header.php';
?>
<div>
    <div class="card edit-profile-form">
        <div class="dashboard-header">
            <h2>Edit Profile</h2>
            <a href="dashboard.php" class="gray-btn">Back to Dashboard</a>
        </div>

        <div class="form">
            <?php if ($error): ?>
                <div class="error-msg">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-msg">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <!-- Profile Edit Form  -->
            <form method="post" class="text-sm" enctype="multipart/form-data">
                <div class="flex-col form-data">
                    <label class="text-black">Full Name</label>
                    <input type="text" name="name" required placeholder="Full name/Company name" value="<?= esc($user['name']) ?>">
                </div>

                <div class="flex-col form-data">
                    <label class="text-black">Email Address</label>
                    <input type="email" name="email" required placeholder="test@gmail.com" value="<?= esc($user['email']) ?>">
                </div>

                <div class="flex-col form-data">
                    <label class="text-black">Headline</label>
                    <input type="text" name="headline" required placeholder="Your professional headline" value="<?= esc($seeker['headline'] ?? '') ?>">
                </div>

                <div class="flex-col form-data">
                    <label class="text-black">Skills</label>
                    <textarea name="skills" rows="4" placeholder="List your skills separated by commas"><?= esc($seeker['skills'] ?? '') ?></textarea>
                </div>

                <div class="flex-col form-data">
                    <label class="text-black">Resume (PDF)</label>
                    <?php if (!empty($seeker['resume_path'])): ?>
                        <div class="resume-link-container">
                            Current: <a href="../uploads/resumes/<?= esc($seeker['resume_path']) ?>" target="_blank" class="resume-link">View Resume</a>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="resume" accept=".pdf">
                </div>

                <button class="blue-btn" type="submit">Update Profile</button>
            </form>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>