<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$role = isset($_GET['role']) ? $_GET['role'] : 'seeker';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Email already registered";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);

        if ($stmt->execute()) {
            $id = $conn->insert_id;

            if ($role == 'employer') {
                // Create empty employer profile
                $stmt = $conn->prepare("INSERT INTO employers (user_id, company_name) VALUES (?, ?)");
                $stmt->bind_param("is", $id, $name);
                $stmt->execute();
            } else {
                // Create empty seeker profile
                $stmt = $conn->prepare("INSERT INTO seekers (user_id) VALUES (?)");
                $stmt->bind_param("i", $id);
                $stmt->execute();
            }

            redirect('login.php?registered=1');
        } else {
            $error = "Registration failed";
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-container auth-container-md">
    <div class="card">
        <h2 class="auth-title">Create Account</h2>
        <p class="auth-subtitle">Join us to find your next opportunity</p>

        <?php if ($error): ?>
            <div class="badge badge-danger mb-4 badge-block">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required placeholder="John Doe">
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="you@example.com">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <div class="form-group">
                <label>I am a...</label>
                <select name="role">
                    <option value="seeker" <?= $role == 'seeker' ? 'selected' : '' ?>>Job Seeker</option>
                    <option value="employer" <?= $role == 'employer' ? 'selected' : '' ?>>Employer</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        </form>

        <p class="auth-footer">
            Already have an account? <a href="login.php" class="auth-link">Sign in</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>