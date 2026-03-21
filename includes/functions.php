<?php


function esc($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}


function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}


function isEmployer()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'employer';
}


function isSeeker()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'seeker';
}


function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}


function redirect($page)
{
    header("Location: $page");
    exit();
}
