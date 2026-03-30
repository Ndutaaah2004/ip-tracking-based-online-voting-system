<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/staff_bootstrap.php';

$pageTitle = 'Elections';
$activeNav = 'elections';
require_once __DIR__ . '/includes/staff_header.php';

$created = isset($_GET['created']);

$sql = <<<SQL
SELECT
  e.id,
  e.title,
  e.description,
  e.opens_at,
  e.closes_at,
  (SELECT COUNT(*) FROM ballot_options bo WHERE bo.election_id = e.id) AS option_count,
  (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id) AS vote_count
FROM elections e
ORDER BY e.opens_at DESC
SQL;

$rows = $conn->query($sql);
$list = $rows ? $rows->fetch_all(MYSQLI_ASSOC) : [];

function staff_election_status(array $e): string
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

?>
<main class="dash-main">
    <section class="dash-panel">
        <div class="staff-toolbar">
            <div>
                <h2 class="dash-panel-title">Elections</h2>
                <p class="dash-panel-lead staff-panel-lead">All ballots. Students only see elections during the open window on their Active ballots page after signing in.</p>
            </div>
            <a href="staff_election_create.php" class="btn-dash btn-dash-primary"><i class="fas fa-plus"></i> Create election</a>
        </div>

        <?php if ($created): ?>
            <div class="dash-alert dash-alert-success">Election created successfully.</div>
        <?php endif; ?>

        <?php if (count($list) === 0): ?>
            <div class="dash-empty">
                <i class="fas fa-calendar-xmark"></i>
                <p><strong>No elections yet</strong></p>
                <p class="dash-empty-hint">Create your first ballot with choices and a voting window.</p>
                <a href="staff_election_create.php" class="btn-dash btn-dash-primary">Create election</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Opens</th>
                            <th>Closes</th>
                            <th>Choices</th>
                            <th>Votes</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($list as $e): ?>
                            <?php
                            $st = staff_election_status($e);
                            $tagClass = $st === 'open' ? 'tag-open' : ($st === 'upcoming' ? 'tag-upcoming' : 'tag-closed');
                            $eid = (int) $e['id'];
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($e['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <?php if (!empty($e['description'])): ?>
                                        <br><span class="table-sub"><?php
                                            $d = (string) $e['description'];
                                        echo htmlspecialchars(strlen($d) > 100 ? substr($d, 0, 97) . '…' : $d, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="tag <?php echo $tagClass; ?>"><?php echo htmlspecialchars($st, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($e['opens_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($e['closes_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo (int) $e['option_count']; ?></td>
                                <td><?php echo (int) $e['vote_count']; ?></td>
                                <td><a href="staff_results.php?e=<?php echo $eid; ?>" class="table-link">Results</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require_once __DIR__ . '/includes/staff_footer.php'; ?>
