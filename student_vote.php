<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/student_bootstrap.php';

$electionId = isset($_GET['e']) ? (int) $_GET['e'] : 0;
$error = isset($_GET['error']) ? trim((string) $_GET['error']) : '';

if ($electionId < 1) {
    header('Location: student_ballots.php');
    exit;
}

$stmt = $conn->prepare(
    'SELECT id, title, description, opens_at, closes_at FROM elections WHERE id = ? LIMIT 1'
);
$stmt->bind_param('i', $electionId);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$election) {
    header('Location: student_ballots.php?error=' . urlencode('Ballot not found.'));
    exit;
}

$now = time();
$opens = strtotime($election['opens_at']);
$closes = strtotime($election['closes_at']);
$isOpen = $now >= $opens && $now <= $closes;

$vStmt = $conn->prepare(
    'SELECT v.ballot_option_id, bo.label FROM votes v
     INNER JOIN ballot_options bo ON bo.id = v.ballot_option_id
     WHERE v.election_id = ? AND v.student_id = ? LIMIT 1'
);
$vStmt->bind_param('ii', $electionId, $studentId);
$vStmt->execute();
$existingVote = $vStmt->get_result()->fetch_assoc();
$vStmt->close();

$oStmt = $conn->prepare(
    'SELECT id, label FROM ballot_options WHERE election_id = ? ORDER BY sort_order ASC, id ASC'
);
$oStmt->bind_param('i', $electionId);
$oStmt->execute();
$options = $oStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$oStmt->close();

$pageTitle = $election['title'];
$activeNav = 'ballots';
require_once __DIR__ . '/includes/student_header.php';

?>
<main class="dash-main">
    <p class="dash-back"><a href="student_ballots.php"><i class="fas fa-arrow-left"></i> All active ballots</a></p>

    <section class="dash-panel">
        <h2 class="dash-panel-title"><?php echo htmlspecialchars($election['title'], ENT_QUOTES, 'UTF-8'); ?></h2>

        <?php if ($error !== ''): ?>
            <div class="dash-alert dash-alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($election['description']): ?>
            <p class="dash-panel-lead"><?php echo nl2br(htmlspecialchars($election['description'], ENT_QUOTES, 'UTF-8')); ?></p>
        <?php endif; ?>

        <p class="ballot-meta ballot-meta-block">
            <span><i class="fas fa-door-open"></i> Opens <?php echo htmlspecialchars(date('M j, Y g:i A', $opens), ENT_QUOTES, 'UTF-8'); ?></span>
            <span><i class="fas fa-door-closed"></i> Closes <?php echo htmlspecialchars(date('M j, Y g:i A', $closes), ENT_QUOTES, 'UTF-8'); ?></span>
        </p>

        <?php if (!$isOpen): ?>
            <div class="dash-empty">
                <i class="fas fa-calendar-xmark"></i>
                <p>This ballot is not open for voting right now.</p>
            </div>
        <?php elseif ($existingVote): ?>
            <div class="vote-receipt">
                <h3><i class="fas fa-check-circle"></i> Vote recorded</h3>
                <p>You submitted: <strong><?php echo htmlspecialchars($existingVote['label'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
                <p class="vote-receipt-note">You cannot change your vote. Contact staff if there is a problem.</p>
            </div>
        <?php elseif (count($options) === 0): ?>
            <div class="dash-empty">
                <p>No choices are configured for this ballot yet.</p>
            </div>
        <?php else: ?>
            <form class="vote-form" action="process/student_vote_process.php" method="post">
                <input type="hidden" name="election_id" value="<?php echo $electionId; ?>">
                <fieldset class="vote-options">
                    <legend class="sr-only">Select one option</legend>
                    <?php foreach ($options as $opt): ?>
                        <?php $oid = (int) $opt['id']; ?>
                        <label class="vote-option">
                            <input type="radio" name="ballot_option_id" value="<?php echo $oid; ?>" required>
                            <span class="vote-option-label"><?php echo htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </label>
                    <?php endforeach; ?>
                </fieldset>
                <button type="submit" class="btn-dash btn-dash-primary">Submit vote</button>
            </form>
        <?php endif; ?>
    </section>
</main>
<?php require_once __DIR__ . '/includes/student_footer.php'; ?>
