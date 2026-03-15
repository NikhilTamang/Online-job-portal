<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/Online-job-portal/assets/css/style.css">
</head>

<body>

    <nav class="navbar">
        <div class="container">
            <a href="/Online-job-portal/index.php" class="logo">JobPortal</a>

            <button class="hamburger" id="hamburger" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <ul class="nav-links" id="nav-links">
                <li><a href="/Online-job-portal/index.php">Find Jobs</a></li>

                <?php if (isLoggedIn()): ?>
                    <?php if (isSeeker()): ?>
                        <li><a href="/Online-job-portal/seeker/dashboard.php">Dashboard</a></li>
                    <?php elseif (isEmployer()): ?>
                        <li><a href="/Online-job-portal/employer/dashboard.php">Dashboard</a></li>
                    <?php elseif (isAdmin()): ?>
                        <li><a href="/Online-job-portal/admin/dashboard.php">Admin</a></li>
                    <?php endif; ?>
                    <li><a href="/Online-job-portal/logout.php" class="btn btn-sm btn-secondary">Logout</a></li>
                <?php else: ?>
                    <li><a href="/Online-job-portal/login.php">Login</a></li>
                    <li><a href="/Online-job-portal/register.php" class="btn btn-sm btn-primary">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="container">