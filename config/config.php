<?php

$host = getenv('DB_HOST') ?: 'private-db-mysql-ams3-57287-do-user-23404159-0.k.db.ondigitalocean.com';
$user = getenv('DB_USER') ?: 'app_user';
$password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'StrongPassword123!';
$database = getenv('DB_NAME') ?: 'voting_system';
$port = 25060;

$conn = new mysqli($host, $user, $password, $database,$port);

if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
