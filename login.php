<?php
session_start();

require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            if ($user['role'] == 'seeker') {
                header("Location: seeker/dashboard.php");
                exit;
            } else {
                header("Location: employer/dashboard.php");
                exit;
            }
        } else {
            $error = "Invalid Password";
        }
    } else {
        $error = "Invalid Email";
    }
}
include 'includes/header.php';
?>

<div class="card">
    <div class="form">

        <div class="info">
            <h2>Welcome Back</h2>
        </div>

        <?php if ($error): ?>
            <div class="error-msg">
                <?= $error ?>
            </div>
        <?php endif; ?>    

        <!-- Login Form  -->
        <form method="post" class="text-sm">
            <div class="flex-col form-data">
                <label class="text-black">Email Address</label>
                <input type="email" name="email" required placeholder="test@gmail.com">
            </div>

            <div class="flex-col form-data">
                <label class="text-black">Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
                
            <button class="blue-btn" type="submit">Sign In</button>
        </form>
        <div class="info-bottom text-sm">
            <p>Don't have an account?</p>
            <a class="text-blue" href="/Online-job-portal/register.php">Create account</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>