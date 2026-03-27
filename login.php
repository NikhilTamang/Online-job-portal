<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if (isset($_GET['job_id']) && (int)$_GET['job_id'] > 0) {
    $_SESSION['apply_job_id'] = (int)$_GET['job_id'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['name'];

        if ($user['role'] == 'seeker') {
            $chk = $conn->prepare("SELECT headline, skills, preferred_category FROM seekers WHERE user_id = ?");
            $chk->bind_param("i", $user['id']);
            $chk->execute();
            $chk_data = $chk->get_result()->fetch_assoc();

            $profileIncomplete = !$chk_data ||
                empty($chk_data['headline']) ||
                empty($chk_data['skills']) ||
                empty($chk_data['preferred_category']);

            if ($profileIncomplete) {
                header('Location: seeker/profile-setup.php');
                exit();
            }

            if (!empty($_SESSION['apply_job_id'])) {
                $intended_job_id = (int)$_SESSION['apply_job_id'];
                unset($_SESSION['apply_job_id']);
                header("Location: seeker/apply.php?job_id=$intended_job_id&auto=1");
                exit();
            }

            header('Location: seeker/dashboard.php');
            exit();

        } elseif ($user['role'] == 'employer') {
            header('Location: employer/dashboard.php');
            exit();
        } elseif ($user['role'] == 'admin') {
            header('Location: admin/dashboard.php');
            exit();
        }
    } else {
        $error = "Invalid email or password";
    }
}

include 'includes/header.php';
?>

<div class="auth-container auth-container-sm">
    <div class="card">
        <h2 class="text-center auth-title">Welcome Back</h2>

        <?php if (isset($_GET['registered'])): ?>
            <div class="badge badge-success mb-4 badge-block">
                Account created successfully! Please login.
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['apply_job_id'])): ?>
            <div class="badge badge-warning mb-4 badge-block" style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">
                🔒 Please log in to complete your job application.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="badge badge-danger mb-4 badge-block">
                <?= esc($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="you@example.com"
                    value="<?= isset($_POST['email']) ? esc($_POST['email']) : '' ?>">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
        </form>

        <p class="text-center mt-4 auth-footer">
            Don't have an account? <a href="register.php" class="auth-link">Create account</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>