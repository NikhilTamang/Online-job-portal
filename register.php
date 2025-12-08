<?php
session_start();

require_once('includes/db.php');
require_once('includes/functions.php');

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0){
        $error = "Email already registered.";
    } else {
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $name, $email, $password, $role);
        $stmt->execute();

        header("Location: login.php");
        exit;
    }
}
include 'includes/header.php';
?>

<div class="card">
    <div class="form">

        <div class="info">
            <h2>Create Account</h2>
            <p class="text-sm">Join us to find your next opportunity</p>
        </div>

        <?php if ($error): ?>
            <div class="error-msg">
                <?= $error ?>
            </div>
        <?php endif; ?>       

        <!-- Registraion Form  -->
        <form method="post" class="text-sm">
            <div class="flex-col form-data">
                <label class="text-black">Full Name</label>
                <input type="text" name="name" required placeholder="Full name/Company name">
            </div>

            <div class="flex-col form-data">
                <label class="text-black">Email Address</label>
                <input type="email" name="email" required placeholder="test@gmail.com">
            </div>

            <div class="flex-col form-data">
                <label class="text-black">Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <div class="flex-col form-data">
                <label class="text-black">I want to...</label>
                <select name="role" class="text-black">
                    <option value="seeker">Find a Job</option>
                    <option value="employer">Hire Talent</option>
                </select>
            </div>
                
            <button class="blue-btn" type="submit">Sign Up</button>
        </form>
        <div class="info-bottom text-sm">
            <p>Already have an account?</p>
            <a class="text-blue" href="/Online-job-portal/login.php">Sign in</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>