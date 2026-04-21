<?php
session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/ip_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$role = $_POST['role'] ?? '';
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (($role !== 'student' && $role !== 'staff') || $email === '' || $password === '') {
    header('Location: ../login.php?error=' . urlencode('Please select a role and enter your email and password.'));
    exit;
}

$table = $role === 'student' ? 'students' : 'staff';

$stmt = $conn->prepare("SELECT id, name, email, password FROM `$table` WHERE email = ? LIMIT 1");
if (!$stmt) {
    header('Location: ../login.php?error=' . urlencode('Something went wrong. Please try again.'));
    exit;
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row || !password_verify($password, $row['password'])) {
    header('Location: ../login.php?error=' . urlencode('Invalid email or password.'));
    exit;
}

$loginIp = get_client_ip();
$uid = (int) $row['id'];
$upd = $conn->prepare("UPDATE `$table` SET last_login_ip = ? WHERE id = ?");
if ($upd) {
    $upd->bind_param('si', $loginIp, $uid);
    $upd->execute();
    $upd->close();
}

session_regenerate_id(true);
$_SESSION['user_id'] = $uid;
$_SESSION['role'] = $role;
$_SESSION['username'] = $row['name'];
$_SESSION['email'] = $row['email'];
$_SESSION['login_ip'] = $loginIp;

if ($role === 'student') {
    header('Location: ../student_dashboard.php');
} else {
    header('Location: ../staff_dashboard.php');
}
exit;
