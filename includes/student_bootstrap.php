<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/ip_helpers.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit;
}

$studentId = (int) $_SESSION['user_id'];
$studentName = htmlspecialchars($_SESSION['username'] ?? 'Student', ENT_QUOTES, 'UTF-8');
$studentEmail = htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8');
$loginIpDisplay = htmlspecialchars($_SESSION['login_ip'] ?? get_client_ip(), ENT_QUOTES, 'UTF-8');
