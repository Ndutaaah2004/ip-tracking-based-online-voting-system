<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/staff_bootstrap.php';

$error = isset($_GET['error']) ? trim((string) $_GET['error']) : '';

$defaultOpen = (new DateTime('now'))->format('Y-m-d\TH:i');
$defaultClose = (new DateTime('+7 days'))->format('Y-m-d\TH:i');

$pageTitle = 'Create election';
$activeNav = 'elections';
require_once __DIR__ . '/includes/staff_header.php';

?>
<main class="dash-main">
    <p class="dash-back"><a href="staff_elections.php"><i class="fas fa-arrow-left"></i> All elections</a></p>

    <section class="dash-panel">
        <h2 class="dash-panel-title">Create election</h2>
        <p class="dash-panel-lead">Set a title, optional description, voting window, and one choice per line (minimum two choices).</p>

        <?php if ($error !== ''): ?>
            <div class="dash-alert dash-alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form class="staff-form" action="process/staff_election_create_process.php" method="post">
            <div class="form-row-dash">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required maxlength="255" placeholder="e.g. Student Council president">
            </div>
            <div class="form-row-dash">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" class="form-textarea" rows="3" placeholder="Instructions or context for voters"></textarea>
            </div>
            <div class="form-row-grid">
                <div class="form-row-dash">
                    <label for="opens_at">Opens</label>
                    <input type="datetime-local" id="opens_at" name="opens_at" required value="<?php echo htmlspecialchars($defaultOpen, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="form-row-dash">
                    <label for="closes_at">Closes</label>
                    <input type="datetime-local" id="closes_at" name="closes_at" required value="<?php echo htmlspecialchars($defaultClose, ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>
            <div class="form-row-dash">
                <label for="options">Ballot choices (one per line)</label>
                <textarea id="options" name="options" class="form-textarea" rows="6" required placeholder="Candidate A&#10;Candidate B&#10;Candidate C"></textarea>
            </div>
            <button type="submit" class="btn-dash btn-dash-primary"><i class="fas fa-check"></i> Create election</button>
        </form>
    </section>
</main>
<?php require_once __DIR__ . '/includes/staff_footer.php'; ?>
