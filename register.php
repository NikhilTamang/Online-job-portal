<?php
include 'includes/header.php';
?>

<div class="card">
    <div class="form">

        <div class="info">
            <h2>Create Account</h2>
            <p class="text-sm">Join us to find your next opportunity</p>
        </div>

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