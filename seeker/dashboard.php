<?php
session_start();

require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isSeeker()) {
    header('Location: ../login.php');
}

include '../includes/header.php';
?>
<div class="dashboard-header">
    <h2>My Dashboard</h2>
    <a href="" class="gray-btn">Edit Profile</a>
</div>

<div class="dashboard-card">
    <p class="text-lg">My Applications</p>
    <div class="table-container text-sm">
        <table>
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Applied On</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="#" class="text-blue">Software Engineer</a></td>
                    <td>Nirajan Khadka</td>
                    <td>Nov 23, 2025</td>
                    <td><span class="accepted-msg">Accepted</span></td>
                </tr>
                <tr>
                    <td><a href="#" class="text-blue">Software Engineer</a></td>
                    <td>Nirajan Khadka</td>
                    <td>Nov 23, 2025</td>
                    <td><span class="rejected-msg">Rejected</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php include '../includes/footer.php'; ?>