<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeeker()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$error   = '';
$success = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM seekers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$seeker = $stmt->get_result()->fetch_assoc();

$all_categories = [
    'IT'          => 'Information Technology',
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['setup_profile'])) {
    $name      = trim($_POST['name']);
    $email     = trim($_POST['email']);
    $headline  = trim($_POST['headline']);
    $skills    = trim($_POST['skills']);
    $pref_cats = isset($_POST['preferred_category']) ? $_POST['preferred_category'] : [];

    if (empty($name) || empty($email) || empty($headline) || empty($skills)) {
        $error = "Please fill in all required fields.";
    } elseif (empty($pref_cats)) {
        $error = "Please select at least one preferred job category.";
    } else {
        $valid_keys = array_keys($all_categories);
        $pref_cats  = array_filter($pref_cats, fn($c) => in_array($c, $valid_keys));

        if (empty($pref_cats)) {
            $error = "Invalid category selection.";
        } else {
            $pref_str = implode(',', $pref_cats);

            if (isset($_FILES['resume']) && $_FILES['resume']['size'] > 0) {
                $file = $_FILES['resume'];
                $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
                if (strtolower($ext) !== 'pdf') {
                    $error = "Only PDF files are allowed for the resume.";
                } else {
                    if (!file_exists('../uploads/resumes')) {
                        mkdir('../uploads/resumes', 0777, true);
                    }
                    $newFileName = 'resume_' . $user_id . '_' . time() . '.pdf';
                    $destination = '../uploads/resumes/' . $newFileName;
                    if (!move_uploaded_file($file['tmp_name'], $destination)) {
                        $error = "Failed to upload resume.";
                    } else {
                        $r = $conn->prepare("UPDATE seekers SET resume_path = ? WHERE user_id = ?");
                        $r->bind_param("si", $newFileName, $user_id);
                        $r->execute();
                    }
                }
            }

            if (!$error) {
                $stmt1 = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt1->bind_param("ssi", $name, $email, $user_id);
                $stmt1->execute();

                $stmt2 = $conn->prepare("UPDATE seekers SET headline = ?, skills = ?, preferred_category = ? WHERE user_id = ?");
                $stmt2->bind_param("sssi", $headline, $skills, $pref_str, $user_id);
                $stmt2->execute();

                $_SESSION['name'] = $name;

                redirect('dashboard.php');
            }
        }
    }
}

$selected_cats = [];
if (!empty($_POST['preferred_category'])) {
    $selected_cats = (array)$_POST['preferred_category'];
} elseif (!empty($seeker['preferred_category'])) {
    $selected_cats = explode(',', $seeker['preferred_category']);
}

include '../includes/header.php';
?>

<div class="profile-container">
    <div class="flex justify-between items-center mb-4">
        <h1>Complete Your Profile</h1>
    </div>

    <p style="color:#6b7280;margin-bottom:1.5rem;text-align:center;">
        Tell us a bit about yourself so we can personalise your job experience.
    </p>

    <?php if ($error): ?>
        <div class="badge badge-danger alert-badge"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="setup_profile" value="1">

            <div class="form-group">
                <label>Full Name <span style="color:red">*</span></label>
                <input type="text" name="name" required
                    value="<?= esc($_POST['name'] ?? $user['name'] ?? '') ?>"
                    placeholder="Your full name">
            </div>

            <div class="form-group">
                <label>Email Address <span style="color:red">*</span></label>
                <input type="email" name="email" required
                    value="<?= esc($_POST['email'] ?? $user['email'] ?? '') ?>"
                    placeholder="you@example.com">
            </div>

            <div class="form-group">
                <label>Professional Headline <span style="color:red">*</span></label>
                <input type="text" name="headline" required
                    value="<?= esc($_POST['headline'] ?? $seeker['headline'] ?? '') ?>"
                    placeholder="e.g. Full Stack Developer">
            </div>

            <div class="form-group">
                <label>Skills <span style="color:red">*</span></label>
                <textarea name="skills" rows="3" required
                    placeholder="e.g. PHP, MySQL, JavaScript, Communication"><?= esc($_POST['skills'] ?? $seeker['skills'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Resume (PDF)</label>
                <?php if (!empty($seeker['resume_path'])): ?>
                    <div class="resume-link-container">
                        Current: <a href="../uploads/resumes/<?= esc($seeker['resume_path']) ?>"
                            target="_blank" class="resume-link">View Resume</a>
                    </div>
                <?php endif; ?>
                <input type="file" name="resume" accept=".pdf">
            </div>

            <div class="form-group">
                <label>Preferred Job Categories <span style="color:red">*</span></label>
                <p style="font-size:0.8rem;color:#6b7280;margin-bottom:0.5rem;">
                    Select one or more categories. Only jobs in these categories will be shown to you.
                </p>
                <div class="category-checkbox-grid">
                    <?php foreach ($all_categories as $val => $label): ?>
                        <label class="category-checkbox-label">
                            <input type="checkbox" name="preferred_category[]"
                                value="<?= esc($val) ?>"
                                <?= in_array($val, $selected_cats) ? 'checked' : '' ?>>
                            <?= esc($label) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full">Save &amp; Continue</button>
        </form>
    </div>
</div>

<style>
.category-checkbox-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 0.5rem;
    margin-top: 0.25rem;
}
.category-checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.9rem;
    cursor: pointer;
    padding: 0.4rem 0.6rem;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    transition: background 0.15s;
}
.category-checkbox-label:hover {
    background: #f3f4f6;
}
.category-checkbox-label input[type="checkbox"] {
    accent-color: #4f46e5;
    width: 16px;
    height: 16px;
}
</style>

<?php include '../includes/footer.php'; ?>
