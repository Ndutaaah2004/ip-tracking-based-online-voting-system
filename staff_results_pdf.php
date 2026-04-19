<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/staff_bootstrap.php';
require_once __DIR__ . '/includes/staff_results_print_html.php';

$detailId = isset($_GET['e']) ? (int) $_GET['e'] : 0;
if ($detailId <= 0) {
    header('Location: staff_results.php');
    exit;
}

$studentTotal = 0;
$cSt = $conn->query('SELECT COUNT(*) AS c FROM students');
if ($cSt) {
    $studentTotal = (int) ($cSt->fetch_assoc()['c'] ?? 0);
}

$est = $conn->prepare(
    'SELECT id, title, description, opens_at, closes_at FROM elections WHERE id = ? LIMIT 1'
);
$est->bind_param('i', $detailId);
$est->execute();
$election = $est->get_result()->fetch_assoc();
$est->close();

if (!$election) {
    header('Location: staff_results.php');
    exit;
}

$tallySql = <<<SQL
SELECT bo.id, bo.label, bo.sort_order, COUNT(v.id) AS votes
FROM ballot_options bo
LEFT JOIN votes v ON v.ballot_option_id = bo.id
WHERE bo.election_id = ?
GROUP BY bo.id, bo.label, bo.sort_order
ORDER BY bo.sort_order ASC, bo.id ASC
SQL;
$tSt = $conn->prepare($tallySql);
$tSt->bind_param('i', $detailId);
$tSt->execute();
$tallies = $tSt->get_result()->fetch_all(MYSQLI_ASSOC);
$tSt->close();

$voteTotal = 0;
foreach ($tallies as $t) {
    $voteTotal += (int) $t['votes'];
}

$turnoutPct = $studentTotal > 0 ? round(100 * $voteTotal / $studentTotal, 1) : 0.0;

$now = time();
$o = strtotime($election['opens_at']);
$c = strtotime($election['closes_at']);
if ($now < $o) {
    $status = 'upcoming';
} elseif ($now > $c) {
    $status = 'closed';
} else {
    $status = 'open';
}

$generatedAt = staff_report_h(date('M j, Y g:i A T'));
$staffRaw = isset($_SESSION['username']) ? (string) $_SESSION['username'] : 'Staff';

$html = staff_build_results_report_html(
    $election,
    $tallies,
    $voteTotal,
    $studentTotal,
    $turnoutPct,
    $status,
    $generatedAt,
    $staffRaw,
    $detailId
);

header('Content-Type: text/html; charset=UTF-8');
echo $html;
