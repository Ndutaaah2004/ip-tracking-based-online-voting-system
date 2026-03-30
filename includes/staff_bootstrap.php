<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: login.php');
    exit;
}

$staffId = (int) $_SESSION['user_id'];
$staffName = htmlspecialchars($_SESSION['username'] ?? 'Staff', ENT_QUOTES, 'UTF-8');
$staffEmail = htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8');
