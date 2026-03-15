<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isEmployer()) {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT * FROM employers WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$employer = $stmt->get_result()->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name         = trim($_POST['name']);
    $email        = trim($_POST['email']);
    $company_name = trim($_POST['company_name']);

    // Check email uniqueness
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error = "Email already in use by another account.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $user_id);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE employers SET company_name = ? WHERE user_id = ?");
        $stmt->bind_param("si", $company_name, $user_id);
        $stmt->execute();

        $_SESSION['name'] = $name;
        $success = "Profile updated successfully!";

        // Refresh
        $user['name']     = $name;
        $user['email']    = $email;
        $employer['company_name'] = $company_name;
    }
}



include '../includes/header.php';
?>

<div class="profile-container" style="max-width:650px;">
    <div class="flex justify-between items-center mb-4">
        <h1>Company Profile</h1>
        <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
    </div>

    <?php if ($error): ?>
        <div class="badge badge-danger alert-badge"><?= esc($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="badge badge-success alert-badge"><?= esc($success) ?></div>
    <?php endif; ?>

    <!-- Profile Update Form -->
    <div class="card mb-4">
        <h2 class="dashboard-title">Edit Profile</h2>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?= esc($user['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= esc($user['email']) ?>" required>
            </div>
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" value="<?= esc($employer['company_name'] ?? '') ?>" required placeholder="Your company name">
            </div>
            <button type="submit" name="update_profile" value="1" class="btn btn-primary btn-full">Update Profile</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
