<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/staff_bootstrap.php';

function staff_format_student_ip(?string $ip): string
{
    if ($ip === null || $ip === '') {
        return '—';
    }

    return htmlspecialchars($ip, ENT_QUOTES, 'UTF-8');
}

$electionFilter = isset($_GET['election_id']) ? (int) $_GET['election_id'] : 0;

$pageTitle = 'Voters';
$activeNav = 'voters';
require_once __DIR__ . '/includes/staff_header.php';

$studentsSql = 'SELECT id, name, email, created_at, registration_ip, last_login_ip FROM students ORDER BY created_at DESC';
$sr = $conn->query($studentsSql);
$students = $sr ? $sr->fetch_all(MYSQLI_ASSOC) : [];

$er = $conn->query('SELECT id, title, closes_at FROM elections ORDER BY closes_at DESC');
$elections = $er ? $er->fetch_all(MYSQLI_ASSOC) : [];

$voted = [];
$notVoted = [];
$selectedElection = null;

if ($electionFilter > 0) {
    $est = $conn->prepare('SELECT id, title, opens_at, closes_at FROM elections WHERE id = ? LIMIT 1');
    $est->bind_param('i', $electionFilter);
    $est->execute();
    $selectedElection = $est->get_result()->fetch_assoc();
    $est->close();

    if ($selectedElection) {
        $vSql = <<<SQL
SELECT s.id, s.name, s.email, v.voted_at
FROM votes v
INNER JOIN students s ON s.id = v.student_id
WHERE v.election_id = ?
ORDER BY v.voted_at DESC
SQL;
        $vSt = $conn->prepare($vSql);
        $vSt->bind_param('i', $electionFilter);
        $vSt->execute();
        $voted = $vSt->get_result()->fetch_all(MYSQLI_ASSOC);
        $vSt->close();

        $nvSql = <<<SQL
SELECT s.id, s.name, s.email
FROM students s
WHERE NOT EXISTS (
  SELECT 1 FROM votes v WHERE v.student_id = s.id AND v.election_id = ?
)
ORDER BY s.name ASC
SQL;
        $nvSt = $conn->prepare($nvSql);
        $nvSt->bind_param('i', $electionFilter);
        $nvSt->execute();
        $notVoted = $nvSt->get_result()->fetch_all(MYSQLI_ASSOC);
        $nvSt->close();
    }
}

?>
<main class="dash-main">
    <section class="dash-panel">
        <h2 class="dash-panel-title">Voters</h2>
        <p class="dash-panel-lead">All registered students and, when you pick an election, who has voted and who has not.</p>

        <h3 class="results-section-title">Registered students</h3>
        <?php if (count($students) === 0): ?>
            <p class="dash-panel-lead">No student accounts yet. Students can register via <a href="signup.php">sign up</a>.</p>
        <?php else: ?>
            <p class="voters-count"><strong><?php echo count($students); ?></strong> student<?php echo count($students) === 1 ? '' : 's'; ?> total</p>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Registration IP</th>
                            <th>Last login IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('M j, Y', strtotime($s['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="table-ip"><?php echo staff_format_student_ip($s['registration_ip'] ?? null); ?></td>
                                <td class="table-ip"><?php echo staff_format_student_ip($s['last_login_ip'] ?? null); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="dash-panel staff-voters-participation">
        <h2 class="dash-panel-title">Participation by election</h2>
        <p class="dash-panel-lead">Select a ballot to see voters who submitted a choice and students who have not voted yet.</p>

        <?php if (count($elections) === 0): ?>
            <p class="dash-panel-lead">No elections exist yet. <a href="staff_election_create.php">Create one</a>.</p>
        <?php else: ?>
            <form class="staff-filter-form" method="get" action="staff_voters.php">
                <label for="election_id" class="sr-only">Election</label>
                <select name="election_id" id="election_id" onchange="this.form.submit()">
                    <option value="0">— Select election —</option>
                    <?php foreach ($elections as $el): ?>
                        <?php $eid = (int) $el['id']; ?>
                        <option value="<?php echo $eid; ?>" <?php echo $electionFilter === $eid ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($el['title'], ENT_QUOTES, 'UTF-8'); ?> (closes <?php echo htmlspecialchars(date('M j, Y', strtotime($el['closes_at'])), ENT_QUOTES, 'UTF-8'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>

            <?php if ($electionFilter > 0 && !$selectedElection): ?>
                <div class="dash-alert dash-alert-error">Election not found.</div>
            <?php elseif ($electionFilter > 0 && $selectedElection): ?>
                <?php $selEid = (int) $selectedElection['id']; ?>
                <div class="staff-toolbar staff-toolbar-actions staff-voters-report-bar">
                    <h3 class="results-section-title staff-voters-election-title"><?php echo htmlspecialchars($selectedElection['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <a href="staff_results_pdf.php?e=<?php echo $selEid; ?>&amp;ref=voters" class="btn-dash btn-dash-primary" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i> PDF report</a>
                </div>
                <p class="voters-count">
                    <strong><?php echo count($voted); ?></strong> voted ·
                    <strong><?php echo count($notVoted); ?></strong> not yet voted
                    · <a href="staff_results.php?e=<?php echo $selEid; ?>" class="table-link">View tallies</a>
                </p>

                <div class="voters-split">
                    <div>
                        <h4 class="voters-subtitle"><i class="fas fa-check-circle"></i> Voted</h4>
                        <?php if (count($voted) === 0): ?>
                            <p class="dash-panel-lead">No votes yet.</p>
                        <?php else: ?>
                            <div class="table-wrap">
                                <table class="data-table data-table-compact">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Voted at</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($voted as $v): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($v['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($v['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($v['voted_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h4 class="voters-subtitle"><i class="fas fa-user-clock"></i> Not voted</h4>
                        <?php if (count($notVoted) === 0): ?>
                            <p class="dash-panel-lead">Everyone registered has voted (or there are no students).</p>
                        <?php else: ?>
                            <div class="table-wrap">
                                <table class="data-table data-table-compact">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($notVoted as $nv): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($nv['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($nv['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </section>
</main>
<?php require_once __DIR__ . '/includes/staff_footer.php'; ?>
