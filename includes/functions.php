<?php

// Escape output
function esc($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Check if employer is logged in
function isEmployer()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employer';
}

// Check if seeker is logged in
function isSeeker()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'seeker';
}

// Check if admin is logged in
function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect helper
function redirect($page)
{
    header("Location: $page");
    exit();
}
