<?php

    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    function isSeeker() {
        return isset($_SESSION['role']) && $_SESSION['role'] == 'seeker';
    }

    function isEmployer() {
        return isset($_SESSION['role']) && $_SESSION['role'] == 'employer';
    }
?>