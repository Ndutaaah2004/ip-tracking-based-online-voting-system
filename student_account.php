<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/student_bootstrap.php';

$error = isset($_GET['error']) ? trim((string) $_GET['error']) : '';
$ok = isset($_GET['updated']) && $_GET['updated'] === '1';

$pageTitle = 'Account';
$activeNav = 'account';
require_once __DIR__ . '/includes/student_header.php';

?>
<main class="dash-main">
    <section class="dash-panel">
        <h2 class="dash-panel-title">Account</h2>
        <p class="dash-panel-lead">Your profile and password for the student portal.</p>

        <?php if ($error !== ''): ?>
            <div class="dash-alert dash-alert-error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($ok): ?>
            <div class="dash-alert dash-alert-success">Password updated successfully.</div>
        <?php endif; ?>

        <div class="account-grid">
            <div class="account-card">
                <h3><i class="fas fa-id-card"></i> Profile</h3>
                <dl class="account-dl">
                    <dt>Display name</dt>
                    <dd><?php echo $studentName; ?></dd>
                    <dt>Email</dt>
                    <dd><?php echo $studentEmail !== '' ? $studentEmail : '—'; ?></dd>
                    <dt>Account type</dt>
                    <dd><span class="dash-badge student">Student</span></dd>
                </dl>
                <p class="account-hint">To change your name or email, ask a staff administrator.</p>
            </div>

            <div class="account-card">
                <h3><i class="fas fa-key"></i> Change password</h3>
                <form class="account-form" action="process/student_account_process.php" method="post" autocomplete="off">
                    <div class="form-row-dash">
                        <label for="current_password">Current password</label>
                        <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
                    </div>
                    <div class="form-row-dash">
                        <label for="new_password">New password</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
                    </div>
                    <div class="form-row-dash">
                        <label for="confirm_password">Confirm new password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn-dash btn-dash-primary">Update password</button>
                </form>
            </div>
        </div>

        <div class="account-security">
            <h3><i class="fas fa-shield-halved"></i> Security tips</h3>
            <ul>
                <li>Sign out when using shared or lab computers.</li>
                <li>Use a unique password you do not reuse on other sites.</li>
                <li>Never share your login details with anyone else.</li>
            </ul>
        </div>
    </section>
</main>
<?php require_once __DIR__ . '/includes/student_footer.php'; ?>
