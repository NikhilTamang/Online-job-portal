<?php
session_start();

require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeeker()) {
    header('Location: ../login.php');
}

include '../includes/header.php';
?>
<div>
    This is the Seeker Dashboard
</div>
<?php include '../includes/footer.php'; ?>