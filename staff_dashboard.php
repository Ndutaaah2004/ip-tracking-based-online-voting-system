<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/staff_bootstrap.php';

$pageTitle = 'Staff dashboard';
$activeNav = 'home';
require_once __DIR__ . '/includes/staff_header.php';

?>
<main class="dash-main">
    <section class="dash-welcome">
        <h2>Staff console</h2>
        <p>Hello, <?php echo $staffName; ?>. Create elections, review results, and monitor student voters from the sections below.</p>
        <span class="dash-badge staff">Staff</span>
    </section>

    <div class="dash-grid">
        <a href="staff_elections.php" class="dash-tile-link">
            <article class="dash-tile">
                <i class="fas fa-calendar-plus"></i>
                <h3>Elections</h3>
                <p>Create ballots with choices and voting windows. View everything you have scheduled.</p>
                <span class="dash-tile-cta">Open <i class="fas fa-arrow-right"></i></span>
            </article>
        </a>
        <a href="staff_results.php" class="dash-tile-link">
            <article class="dash-tile">
                <i class="fas fa-chart-simple"></i>
                <h3>Results</h3>
                <p>See vote totals, percentages, and turnout for each election.</p>
                <span class="dash-tile-cta">Open <i class="fas fa-arrow-right"></i></span>
            </article>
        </a>
        <a href="staff_voters.php" class="dash-tile-link">
            <article class="dash-tile">
                <i class="fas fa-users"></i>
                <h3>Voters</h3>
                <p>Browse registered students and who has voted in each election.</p>
                <span class="dash-tile-cta">Open <i class="fas fa-arrow-right"></i></span>
            </article>
        </a>
    </div>
</main>
<?php require_once __DIR__ . '/includes/staff_footer.php'; ?>
