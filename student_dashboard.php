<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/student_bootstrap.php';

$pageTitle = 'Student dashboard';
$activeNav = 'home';
require_once __DIR__ . '/includes/student_header.php';

?>
<main class="dash-main">
    <section class="dash-welcome">
        <h2>Hello, <?php echo $studentName; ?></h2>
        <p>Use the navigation above to open the ballots, review your vote history, or manage your account password.</p>
        <span class="dash-badge student">Student</span>
    </section>

    <div class="dash-grid">
        <a href="student_ballots.php" class="dash-tile-link">
            <article class="dash-tile">
                <i class="fas fa-list-check"></i>
                <h3>Active ballots</h3>
                <p>See what is open now and cast your vote once per election.</p>
                <span class="dash-tile-cta">Open <i class="fas fa-arrow-right"></i></span>
            </article>
        </a>
        <a href="student_history.php" class="dash-tile-link">
            <article class="dash-tile">
                <i class="fas fa-clock-rotate-left"></i>
                <h3>Vote history</h3>
                <p>Read-only list of elections you have already participated in.</p>
                <span class="dash-tile-cta">Open <i class="fas fa-arrow-right"></i></span>
            </article>
        </a>
        <a href="student_account.php" class="dash-tile-link">
            <article class="dash-tile">
                <i class="fas fa-user-gear"></i>
                <h3>Account</h3>
                <p>View your profile and change your password securely.</p>
                <span class="dash-tile-cta">Open <i class="fas fa-arrow-right"></i></span>
            </article>
        </a>
    </div>
</main>
<?php require_once __DIR__ . '/includes/student_footer.php'; ?>
