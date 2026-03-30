<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$studentId = (int) $_SESSION['user_id'];

function redirect_account(string $msg): void
{
    header('Location: ../student_account.php?error=' . urlencode($msg));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../student_account.php');
    exit;
}

$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($current === '' || $new === '' || $confirm === '') {
    redirect_account('All password fields are required.');
}

if ($new !== $confirm) {
    redirect_account('New password and confirmation do not match.');
}

if (strlen($new) < 8) {
    redirect_account('New password must be at least 8 characters.');
}

$stmt = $conn->prepare('SELECT password FROM students WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $studentId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row || !password_verify($current, $row['password'])) {
    redirect_account('Current password is incorrect.');
}

$hash = password_hash($new, PASSWORD_DEFAULT);
$upd = $conn->prepare('UPDATE students SET password = ? WHERE id = ?');
$upd->bind_param('si', $hash, $studentId);

if (!$upd->execute()) {
    $upd->close();
    redirect_account('Could not update password. Please try again.');
}
$upd->close();

session_regenerate_id(true);

header('Location: ../student_account.php?updated=1');
exit;
