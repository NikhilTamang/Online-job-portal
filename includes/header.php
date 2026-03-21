<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobPortal — Find Your Next Opportunity</title>
    <meta name="description" content="Browse thousands of job openings from top companies and startups. Find your dream job today.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="/Online-job-portal/assets/css/style.css">
</head>

<body>

    <nav class="navbar">
        <div class="container">
            <a href="/Online-job-portal/index.php" class="logo">⚡ JobPortal</a>

            <button class="hamburger" id="hamburger" aria-label="Toggle navigation">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <?php
            $current = basename($_SERVER['PHP_SELF']);
            $dir     = basename(dirname($_SERVER['PHP_SELF']));
            ?>

            <ul class="nav-links" id="nav-links">
                <li>
                    <a href="/Online-job-portal/index.php"
                       class="<?= ($current === 'index.php' && $dir !== 'admin' && $dir !== 'seeker' && $dir !== 'employer') ? 'active' : '' ?>">
                        Find Jobs
                    </a>
                </li>

                <?php if (isLoggedIn()): ?>
                    <?php if (isSeeker()): ?>
                        <li>
                            <a href="/Online-job-portal/seeker/dashboard.php"
                               class="<?= $dir === 'seeker' ? 'active' : '' ?>">
                                Dashboard
                            </a>
                        </li>
                    <?php elseif (isEmployer()): ?>
                        <li>
                            <a href="/Online-job-portal/employer/dashboard.php"
                               class="<?= $dir === 'employer' ? 'active' : '' ?>">
                                Dashboard
                            </a>
                        </li>
                    <?php elseif (isAdmin()): ?>
                        <li>
                            <a href="/Online-job-portal/admin/dashboard.php"
                               class="<?= $dir === 'admin' ? 'active' : '' ?>">
                                Admin
                            </a>
                        </li>
                    <?php endif; ?>
                    <li>
                        <a href="/Online-job-portal/logout.php" class="btn btn-sm btn-outline">
                            Sign Out
                        </a>
                    </li>
                <?php else: ?>
                    <li><a href="/Online-job-portal/login.php" class="<?= $current === 'login.php' ? 'active' : '' ?>">Login</a></li>
                    <li>
                        <a href="/Online-job-portal/register.php" class="btn btn-sm btn-primary">
                            Sign Up
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="container">