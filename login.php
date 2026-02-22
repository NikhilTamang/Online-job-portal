<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];

        if ($user['role'] == 'seeker') {
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

        <?php if ($error): ?>
            <div class="badge badge-danger mb-4 badge-block">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="you@example.com">
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