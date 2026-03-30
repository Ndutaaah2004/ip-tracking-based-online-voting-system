<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/student_bootstrap.php';

$pageTitle = 'Vote history';
$activeNav = 'history';
require_once __DIR__ . '/includes/student_header.php';

$sql = <<<SQL
SELECT
  e.title AS election_title,
  bo.label AS choice_label,
  v.voted_at,
  e.closes_at,
  e.opens_at
FROM votes v
INNER JOIN elections e ON e.id = v.election_id
INNER JOIN ballot_options bo ON bo.id = v.ballot_option_id
WHERE v.student_id = ?
ORDER BY v.voted_at DESC
SQL;

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $studentId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<main class="dash-main">
    <section class="dash-panel">
        <h2 class="dash-panel-title">Vote history</h2>
        <p class="dash-panel-lead">A read-only log of ballots you participated in. Open ballots are listed on <a href="student_ballots.php">active ballots</a>.</p>

        <?php if (count($rows) === 0): ?>
            <div class="dash-empty">
                <i class="fas fa-scroll"></i>
                <p><strong>No votes yet</strong></p>
                <p class="dash-empty-hint">When you submit a ballot, it will appear here with the option you chose and the time you voted.</p>
                <a href="student_ballots.php" class="btn-dash btn-dash-primary">See active ballots</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Election</th>
                            <th>Your choice</th>
                            <th>Voted at</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <?php
                            $closes = strtotime($r['closes_at']);
                            $status = time() <= $closes ? 'Open' : 'Closed';
                            $statusClass = $status === 'Open' ? 'tag-open' : 'tag-closed';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['election_title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($r['choice_label'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($r['voted_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><span class="tag <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require_once __DIR__ . '/includes/student_footer.php'; ?>
