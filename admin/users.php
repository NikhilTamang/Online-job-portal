<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle delete
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['user_id'])) {
    $uid = (int)$_POST['user_id'];
    // Prevent admin from deleting themselves
    if ($uid === (int)$_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $uid);
        if ($stmt->execute()) {
            $success = "User deleted successfully.";
        } else {
            $error = "Failed to delete user.";
        }
    }
}


// Fetch all non-admin users
$users = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);


include '../includes/header.php';
?>

<div class="flex justify-between items-center mb-8">
    <h1>Manage Users</h1>
    <a href="dashboard.php" class="btn btn-outline">← Back to Dashboard</a>
</div>

<?php if ($error): ?>
    <div class="badge badge-danger alert-badge mb-4"><?= esc($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="badge badge-success alert-badge mb-4"><?= esc($success) ?></div>
<?php endif; ?>


<div class="card">
    <h2 class="dashboard-title">All Users</h2>
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
                        <th>Actions</th>
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
                            <td>
                                <div class="flex gap-2">
                                    <form method="POST" onsubmit="return confirm('Delete this user? This cannot be undone.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
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