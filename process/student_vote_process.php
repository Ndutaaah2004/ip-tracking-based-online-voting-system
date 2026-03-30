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

function redirect_vote_error(int $electionId, string $msg): void
{
    header('Location: ../student_vote.php?e=' . $electionId . '&error=' . urlencode($msg));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../student_ballots.php');
    exit;
}

$electionId = isset($_POST['election_id']) ? (int) $_POST['election_id'] : 0;
$optionId = isset($_POST['ballot_option_id']) ? (int) $_POST['ballot_option_id'] : 0;

if ($electionId < 1 || $optionId < 1) {
    header('Location: ../student_ballots.php');
    exit;
}

$stmt = $conn->prepare(
    'SELECT id, opens_at, closes_at FROM elections WHERE id = ? LIMIT 1'
);
$stmt->bind_param('i', $electionId);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$election) {
    header('Location: ../student_ballots.php');
    exit;
}

$now = time();
if ($now < strtotime($election['opens_at']) || $now > strtotime($election['closes_at'])) {
    redirect_vote_error($electionId, 'This ballot is closed. Your vote was not recorded.');
}

$check = $conn->prepare(
    'SELECT id FROM votes WHERE election_id = ? AND student_id = ? LIMIT 1'
);
$check->bind_param('ii', $electionId, $studentId);
$check->execute();
if ($check->get_result()->fetch_assoc()) {
    $check->close();
    redirect_vote_error($electionId, 'You have already voted in this election.');
}
$check->close();

$optStmt = $conn->prepare(
    'SELECT id FROM ballot_options WHERE id = ? AND election_id = ? LIMIT 1'
);
$optStmt->bind_param('ii', $optionId, $electionId);
$optStmt->execute();
if (!$optStmt->get_result()->fetch_assoc()) {
    $optStmt->close();
    redirect_vote_error($electionId, 'Invalid choice for this ballot.');
}
$optStmt->close();

$ins = $conn->prepare(
    'INSERT INTO votes (election_id, student_id, ballot_option_id) VALUES (?, ?, ?)'
);
$ins->bind_param('iii', $electionId, $studentId, $optionId);

if (!$ins->execute()) {
    $ins->close();
    if ($conn->errno === 1062) {
        redirect_vote_error($electionId, 'You have already voted in this election.');
    }
    redirect_vote_error($electionId, 'Could not save your vote. Please try again.');
}
$ins->close();

header('Location: ../student_vote.php?e=' . $electionId);
exit;
