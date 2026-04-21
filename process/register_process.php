<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/ip_helpers.php';

function redirect_signup_error(string $msg): void
{
    header('Location: ../signup.php?error=' . urlencode($msg));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../signup.php');
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? '';
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($name === '' || $email === '' || $role === '' || $password === '') {
    redirect_signup_error('All fields are required.');
}

if ($password !== $confirm) {
    redirect_signup_error('Passwords do not match.');
}

if (strlen($password) < 8) {
    redirect_signup_error('Password must be at least 8 characters.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_signup_error('Please enter a valid email address.');
}

if ($role !== 'student' && $role !== 'staff') {
    redirect_signup_error('Invalid account type selected.');
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$check = $conn->prepare(
    'SELECT 1 FROM students WHERE email = ? UNION SELECT 1 FROM staff WHERE email = ? LIMIT 1'
);
$check->bind_param('ss', $email, $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $check->close();
    redirect_signup_error('That email is already registered.');
}
$check->close();

$registrationIp = get_client_ip();

if ($role === 'student') {
    $stmt = $conn->prepare('INSERT INTO students (name, email, password, registration_ip) VALUES (?, ?, ?, ?)');
} else {
    $stmt = $conn->prepare('INSERT INTO staff (name, email, password, registration_ip) VALUES (?, ?, ?, ?)');
}

if (!$stmt) {
    redirect_signup_error('Registration failed. Please try again.');
}

$stmt->bind_param('ssss', $name, $email, $hash, $registrationIp);
$ok = $stmt->execute();
$stmt->close();

if ($ok) {
    header('Location: ../login.php?registered=1');
    exit;
}

redirect_signup_error('Could not create account. Please try again.');
