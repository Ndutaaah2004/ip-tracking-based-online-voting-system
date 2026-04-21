<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

/* =========================
   AUTH CHECK
   ========================= */
if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

/* =========================
   CAPTURE USER IP ADDRESS
   ========================= */
function getUserIP(): string {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    }
}

$userIP = getUserIP();

/* =========================
   STORE IP IN SESSION
   ========================= */
$_SESSION['user_ip'] = $userIP;

/* =========================
   OPTIONAL: LOG IP TO FILE
   ========================= */
$logFile = __DIR__ . '/../ip_logs.txt';
$logEntry = date('Y-m-d H:i:s') . " | Student ID: " . $_SESSION['user_id'] . " | IP: " . $userIP . PHP_EOL;
file_put_contents($logFile, $logEntry, FILE_APPEND);


/* =========================
   USER DETAILS
   ========================= */
$studentId = (int) $_SESSION['user_id'];
$studentName = htmlspecialchars($_SESSION['username'] ?? 'Student', ENT_QUOTES, 'UTF-8');
$studentEmail = htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8');

?>
