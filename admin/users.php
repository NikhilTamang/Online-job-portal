<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$users = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <h1>Manage Users</h1>
    <a href="dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
</div>

<div class="card">
    <h2 class="dashboard-title">All Registered Users</h2>
    <?php if (count($users) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= $u['id'] ?></td>
                            <td><?= esc($u['name']) ?></td>
                            <td><?= esc($u['email']) ?></td>
                            <td>
                                <span class="badge badge-<?= $u['role'] === 'employer' ? 'warning' : 'success' ?>">
                                    <?= ucfirst(esc($u['role'])) ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center empty-state">
            <p>No users found.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>