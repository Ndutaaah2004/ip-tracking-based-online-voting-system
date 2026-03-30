<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: ../login.php');
    exit;
}

function redirect_create_error(string $msg): void
{
    header('Location: ../staff_election_create.php?error=' . urlencode($msg));
    exit;
}

function parse_datetime_local(string $raw): ?string
{
    $raw = trim(str_replace('T', ' ', $raw));
    if ($raw === '') {
        return null;
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $raw)) {
        $raw .= ':00';
    }
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $raw);
    if (!$dt) {
        $dt = DateTime::createFromFormat('Y-m-d H:i', substr($raw, 0, 16));
        if ($dt) {
            $dt->setTime((int) $dt->format('H'), (int) $dt->format('i'), 0);
        }
    }
    return $dt ? $dt->format('Y-m-d H:i:s') : null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../staff_election_create.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$opensRaw = $_POST['opens_at'] ?? '';
$closesRaw = $_POST['closes_at'] ?? '';
$optionsRaw = (string) ($_POST['options'] ?? '');

if ($title === '' || strlen($title) > 255) {
    redirect_create_error('Title is required (max 255 characters).');
}

$opensAt = parse_datetime_local($opensRaw);
$closesAt = parse_datetime_local($closesRaw);
if ($opensAt === null || $closesAt === null) {
    redirect_create_error('Please provide valid open and close date/times.');
}

if ($opensAt >= $closesAt) {
    redirect_create_error('Close time must be after open time.');
}

$lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $optionsRaw) ?: [])));
if (count($lines) < 2) {
    redirect_create_error('Enter at least two ballot choices (one per line).');
}

foreach ($lines as $line) {
    if (strlen($line) > 255) {
        redirect_create_error('Each choice must be 255 characters or less.');
    }
}

$descValue = $description;

$conn->begin_transaction();

try {
    $insE = $conn->prepare(
        'INSERT INTO elections (title, description, opens_at, closes_at) VALUES (?, ?, ?, ?)'
    );
    if (!$insE) {
        throw new RuntimeException('prepare election');
    }
    $insE->bind_param('ssss', $title, $descValue, $opensAt, $closesAt);
    if (!$insE->execute()) {
        throw new RuntimeException('insert election');
    }
    $electionId = (int) $conn->insert_id;
    $insE->close();

    $insO = $conn->prepare(
        'INSERT INTO ballot_options (election_id, label, sort_order) VALUES (?, ?, ?)'
    );
    if (!$insO) {
        throw new RuntimeException('prepare option');
    }

    $sort = 0;
    foreach ($lines as $label) {
        $insO->bind_param('isi', $electionId, $label, $sort);
        if (!$insO->execute()) {
            throw new RuntimeException('insert option');
        }
        ++$sort;
    }
    $insO->close();

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    redirect_create_error('Could not create election. Please try again.');
}

header('Location: ../staff_elections.php?created=1');
exit;
