<?php
include 'includes/header.php';
?>

<div class="card">
    <div class="form">

        <div class="info">
            <h2>Welcome Back</h2>
        </div>

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