<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/student_bootstrap.php';

$pageTitle = 'Active ballots';
$activeNav = 'ballots';
require_once __DIR__ . '/includes/student_header.php';

$sql = <<<SQL
SELECT
  e.id,
  e.title,
  e.description,
  e.opens_at,
  e.closes_at,
  v.id AS vote_id,
  bo.label AS choice_label
FROM elections e
LEFT JOIN votes v ON v.election_id = e.id AND v.student_id = ?
LEFT JOIN ballot_options bo ON bo.id = v.ballot_option_id
WHERE e.opens_at <= NOW() AND e.closes_at >= NOW()
ORDER BY e.closes_at ASC
SQL;

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $studentId);
$stmt->execute();
$ballots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$listError = isset($_GET['error']) ? trim((string) $_GET['error']) : '';

?>
<main class="dash-main">
    <section class="dash-panel">
        <h2 class="dash-panel-title">Active ballots</h2>
        <p class="dash-panel-lead">Ballots open right now. You can vote once per election. After you submit, your choice is recorded in <a href="student_history.php">vote history</a>.</p>

        <?php if ($listError !== ''): ?>
            <div class="dash-alert dash-alert-error"><?php echo htmlspecialchars($listError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if (count($ballots) === 0): ?>
            <div class="dash-empty">
                <i class="fas fa-inbox"></i>
                <p><strong>No open ballots</strong></p>
                <p class="dash-empty-hint">When staff schedule an election, it will show up here during the voting window.</p>
                <p class="dash-empty-hint">To try the app, run <code>seed_demo_ballot.sql</code> in MySQL (see project file).</p>
            </div>
        <?php else: ?>
            <ul class="ballot-list">
                <?php foreach ($ballots as $b): ?>
                    <?php
                    $eid = (int) $b['id'];
                    $voted = $b['vote_id'] !== null;
                    ?>
                    <li class="ballot-card">
                        <div class="ballot-card-main">
                            <h3><?php echo htmlspecialchars($b['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <?php if ($b['description']): ?>
                                <p class="ballot-desc"><?php echo nl2br(htmlspecialchars($b['description'], ENT_QUOTES, 'UTF-8')); ?></p>
                            <?php endif; ?>
                            <p class="ballot-meta">
                                <i class="fas fa-calendar"></i>
                                Closes <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($b['closes_at'])), ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        </div>
                        <div class="ballot-card-aside">
                            <?php if ($voted): ?>
                                <span class="ballot-status ballot-status-done"><i class="fas fa-check-circle"></i> Voted</span>
                                <p class="ballot-choice">You chose: <strong><?php echo htmlspecialchars((string) $b['choice_label'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                                <a href="student_vote.php?e=<?php echo $eid; ?>" class="btn-dash btn-dash-ghost">View receipt</a>
                            <?php else: ?>
                                <span class="ballot-status ballot-status-open"><i class="fas fa-circle-dot"></i> Open</span>
                                <a href="student_vote.php?e=<?php echo $eid; ?>" class="btn-dash btn-dash-primary">Cast vote</a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</main>
<?php require_once __DIR__ . '/includes/student_footer.php'; ?>
