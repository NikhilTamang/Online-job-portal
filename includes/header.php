<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job-Portal</title>
    <link rel="stylesheet" href="/Online-job-portal/assets/css/style.css">
</head>

<body>

    <nav class="navbar">
        <div class="container menu">
            <a class="logo" href="/Online-job-portal/index.php">JobPortal</a>
            <ul class="flex navLinks">

                <li><a href="/Online-job-portal/index.php">Find Jobs</a></li>
            <?php if(isLoggedIn()):?>
            
                <?php if(isSeeker()):?>
                    <li><a href="/Online-job-portal/seeker/dashboard.php">Dashboard</a></li>
                    
                <?php else:?>
                    <li><a href="/Online-job-portal/employer/dashboard.php">Dashboard</a></li>
                <?php endif;?>    
                    <div class="btn">
                        <li><a class="blue-btn" href="/Online-job-portal/logout.php">Logout</a></li>
                    </div>

            <?php else:?>
                <li><a href="/Online-job-portal/login.php">Login</a></li>
                <div class="btn">
                    <li><a class="blue-btn" href="/Online-job-portal/register.php">Post a Job</a></li>
                </div>

            <?php endif;?>
            </ul>

            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <main class="container">