<?php
session_start();

require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isEmployer()) {
    header('Location: ../login.php');
}


include '../includes/header.php';
?>
<div>
    This is the Employer Dashboard
</div>
<?php include '../includes/footer.php'; ?>