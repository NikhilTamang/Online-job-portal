<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer/Exception.php';
require __DIR__ . '/../PHPMailer/PHPMailer.php';
require __DIR__ . '/../PHPMailer/SMTP.php';


if (!isLoggedIn() || !isEmployer()) {
    redirect('../login.php');
}

if (!isset($_GET['job_id'])) {
    redirect('dashboard.php');
}

$job_id = (int)$_GET['job_id'];


$stmt = $conn->prepare("SELECT title FROM jobs WHERE id = ? AND employer_id = (SELECT id FROM employers WHERE user_id = ?)");
$stmt->bind_param("ii", $job_id, $_SESSION['user_id']);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();

if (!$job) {
    redirect('dashboard.php');
}


function sendStatusEmail(string $toEmail, string $toName, string $jobTitle, string $status): bool
{
    $mail = new PHPMailer(true);

    try {
        
        $mail->SMTPDebug  = SMTP::DEBUG_OFF;               
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'get4juggernath@gmail.com';    
        $mail->Password   = 'hoeb jauk uypo sflc';                      
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        
        $mail->setFrom('get4juggernath@gmail.com', 'Job Portal');
        $mail->addAddress($toEmail, $toName);

        
        $mail->isHTML(true);

        if ($status === 'accepted') {
            $mail->Subject = "Congratulations! Your application for \"{$jobTitle}\" was accepted";
            $mail->Body    = "
                <p>Dear <strong>{$toName}</strong>,</p>
                <p>We are pleased to inform you that your application for the position of
                <strong>{$jobTitle}</strong> has been <span style='color:green;'><strong>accepted</strong></span>.</p>
                <p>Our team will be in touch with you shortly regarding the next steps.</p>
                <br>
                <p>Best regards,<br>The Job Portal Team</p>
            ";
            $mail->AltBody = "Dear {$toName}, your application for \"{$jobTitle}\" has been accepted. We will contact you soon.";
        } else {
            $mail->Subject = "Update on your application for \"{$jobTitle}\"";
            $mail->Body    = "
                <p>Dear <strong>{$toName}</strong>,</p>
                <p>Thank you for applying for the position of <strong>{$jobTitle}</strong>.</p>
                <p>After careful consideration, we regret to inform you that your application has
                <span style='color:red;'><strong>not been selected</strong></span> at this time.</p>
                <p>We encourage you to apply for future openings that match your skills.</p>
                <br>
                <p>Best regards,<br>The Job Portal Team</p>
            ";
            $mail->AltBody = "Dear {$toName}, unfortunately your application for \"{$jobTitle}\" was not selected this time. Thank you for applying.";
        }

        $mail->send();
        return true;

    } catch (Exception $e) {
        
        error_log("PHPMailer error for {$toEmail}: " . $mail->ErrorInfo);
        return false;
    }
}


$emailResult = null; 

if (isset($_POST['action']) && isset($_POST['application_id'])) {
    $status = $_POST['action']; 
    $app_id = (int)$_POST['application_id'];

    
    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $app_id);
    $stmt->execute();

    
    $stmt = $conn->prepare("
        SELECT u.name, u.email
        FROM applications a
        JOIN seekers s ON a.seeker_id = s.id
        JOIN users u ON s.user_id = u.id
        WHERE a.id = ?
    ");
    $stmt->bind_param("i", $app_id);
    $stmt->execute();
    $applicant = $stmt->get_result()->fetch_assoc();

    if ($applicant) {
        $emailResult = sendStatusEmail(
            $applicant['email'],
            $applicant['name'],
            $job['title'],
            $status
        );
    }
}


$stmt = $conn->prepare('
    SELECT a.*, u.name, u.email, s.resume_path 
    FROM applications a 
    JOIN seekers s ON a.seeker_id = s.id 
    JOIN users u ON s.user_id = u.id 
    WHERE a.job_id = ?
    ORDER BY a.created_at DESC
');
$stmt->bind_param("i", $job_id);
$stmt->execute();
$applicants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h1>Applicants</h1>
        <p class="subtitle">For job: <strong><?= esc($job['title']) ?></strong></p>
    </div>
    <a href="dashboard.php" class="btn btn-outline">Back to Dashboard</a>
</div>

<?php if ($emailResult === true): ?>
    <div class="alert alert-success">Email notification sent successfully to the applicant.</div>
<?php elseif ($emailResult === false): ?>
    <div class="alert alert-warning">Status updated, but the email notification could not be sent. Check your mail settings.</div>
<?php endif; ?>

<div class="card">
    <?php if (count($applicants) > 0): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Applicant Name</th>
                        <th>Email</th>
                        <th>Applied On</th>
                        <th>Resume</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $app): ?>
                        <tr>
                            <td><?= esc($app['name']) ?></td>
                            <td><a href="mailto:<?= esc($app['email']) ?>" class="email-link"><?= esc($app['email']) ?></a></td>
                            <td><?= date('M j, Y', strtotime($app['created_at'])) ?></td>
                            <td>
                                <?php if ($app['resume_path']): ?>
                                    <a href="../uploads/resumes/<?= esc($app['resume_path']) ?>" target="_blank" class="btn btn-sm btn-outline">View PDF</a>
                                <?php else: ?>
                                    <span class="no-resume">No Resume</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $app['status'] == 'accepted' ? 'success' : ($app['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($app['status'] == 'pending'): ?>
                                    <form method="POST" class="action-form">
                                        <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                        <button type="submit" name="action" value="accepted" class="btn btn-sm btn-success">Accept</button>
                                        <button type="submit" name="action" value="rejected" class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <span class="processed-text">Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center empty-state-sm">
            <p>No applicants for this job yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>