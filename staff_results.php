<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/staff_bootstrap.php';

function staff_election_status_row(array $e): string
{
    $now = time();
    $o = strtotime($e['opens_at']);
    $c = strtotime($e['closes_at']);
    if ($now < $o) {
        return 'upcoming';
    }
    if ($now > $c) {
        return 'closed';
    }
    return 'open';
}

$detailId = isset($_GET['e']) ? (int) $_GET['e'] : 0;

$studentTotal = 0;
$cSt = $conn->query('SELECT COUNT(*) AS c FROM students');
if ($cSt) {
    $studentTotal = (int) ($cSt->fetch_assoc()['c'] ?? 0);
}

$election = null;
$tallies = [];
$voteTotal = 0;
$turnoutPct = 0.0;
$status = '';

if ($detailId > 0) {
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

    foreach ($tallies as $t) {
        $voteTotal += (int) $t['votes'];
    }

    $turnoutPct = $studentTotal > 0 ? round(100 * $voteTotal / $studentTotal, 1) : 0.0;
    $status = staff_election_status_row($election);
}

$list = [];
if ($detailId <= 0) {
    $listSql = <<<SQL
SELECT
  e.id,
  e.title,
  e.opens_at,
  e.closes_at,
  (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id) AS vote_count
FROM elections e
ORDER BY e.closes_at DESC
SQL;
    $lr = $conn->query($listSql);
    $list = $lr ? $lr->fetch_all(MYSQLI_ASSOC) : [];
}

$pageTitle = $detailId > 0 ? 'Election results' : 'Results';
$activeNav = 'results';
require_once __DIR__ . '/includes/staff_header.php';

if ($detailId > 0 && $election):
    ?>

<main class="dash-main">
    <p class="dash-back"><a href="staff_results.php"><i class="fas fa-arrow-left"></i> All results</a></p>

    <section class="dash-panel">
        <h2 class="dash-panel-title"><?php echo htmlspecialchars($election['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <?php if (!empty($election['description'])): ?>
            <p class="dash-panel-lead"><?php echo nl2br(htmlspecialchars($election['description'], ENT_QUOTES, 'UTF-8')); ?></p>
        <?php endif; ?>

        <div class="results-meta">
            <span class="tag <?php echo $status === 'open' ? 'tag-open' : ($status === 'upcoming' ? 'tag-upcoming' : 'tag-closed'); ?>"><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></span>
            <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($election['opens_at'])), ENT_QUOTES, 'UTF-8'); ?> — <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($election['closes_at'])), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>

        <div class="results-summary">
            <div class="results-stat">
                <span class="results-stat-value"><?php echo $voteTotal; ?></span>
                <span class="results-stat-label">Votes cast</span>
            </div>
            <div class="results-stat">
                <span class="results-stat-value"><?php echo $studentTotal; ?></span>
                <span class="results-stat-label">Registered students</span>
            </div>
            <div class="results-stat">
                <span class="results-stat-value"><?php echo htmlspecialchars((string) $turnoutPct, ENT_QUOTES, 'UTF-8'); ?>%</span>
                <span class="results-stat-label">Turnout</span>
            </div>
        </div>

        <h3 class="results-section-title">Tallies</h3>
        <?php if ($voteTotal === 0): ?>
            <p class="dash-panel-lead">No votes yet for this election.</p>
        <?php else: ?>
            <ul class="results-bars">
                <?php foreach ($tallies as $t): ?>
                    <?php
                    $v = (int) $t['votes'];
                    $pct = $voteTotal > 0 ? round(100 * $v / $voteTotal, 1) : 0;
                    ?>
                    <li class="result-row">
                        <div class="result-row-head">
                            <span class="result-label"><?php echo htmlspecialchars($t['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <span class="result-count"><?php echo $v; ?> (<?php echo htmlspecialchars((string) $pct, ENT_QUOTES, 'UTF-8'); ?>%)</span>
                        </div>
                        <div class="result-bar-track" role="presentation">
                            <div class="result-bar-fill" style="width: <?php echo htmlspecialchars((string) $pct, ENT_QUOTES, 'UTF-8'); ?>%;"></div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p class="results-foot"><a href="staff_voters.php?election_id=<?php echo $detailId; ?>" class="table-link"><i class="fas fa-users"></i> See voter participation for this election</a></p>
    </section>
</main>
<?php
else:
    ?>

<main class="dash-main">
    <section class="dash-panel">
        <h2 class="dash-panel-title">Results</h2>
        <p class="dash-panel-lead">Open an election to see choice breakdown, turnout vs registered students, and percentages.</p>

        <?php if (count($list) === 0): ?>
            <div class="dash-empty">
                <i class="fas fa-chart-bar"></i>
                <p><strong>No elections</strong></p>
                <p class="dash-empty-hint">Create an election first, then results will appear here.</p>
                <a href="staff_election_create.php" class="btn-dash btn-dash-primary">Create election</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Election</th>
                            <th>Status</th>
                            <th>Closes</th>
                            <th>Votes</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $e): ?>
                            <?php
                            $st = staff_election_status_row($e);
                            $tagClass = $st === 'open' ? 'tag-open' : ($st === 'upcoming' ? 'tag-upcoming' : 'tag-closed');
                            $eid = (int) $e['id'];
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($e['title'], ENT_QUOTES, 'UTF-8'); ?></strong></td>
                                <td><span class="tag <?php echo $tagClass; ?>"><?php echo htmlspecialchars($st, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($e['closes_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) $e['vote_count']; ?></td>
                                <td><a href="staff_results.php?e=<?php echo $eid; ?>" class="table-link">View results</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/staff_footer.php'; ?>
