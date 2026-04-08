<?php
session_start();
require_once __DIR__ . '/config/config.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'staff') {
    header('Location: login.php');
    exit;
}

$name = htmlspecialchars($_SESSION['username'] ?? 'Staff', ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff dashboard — Voting System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-pVZC+SxNn8Qqxn2/nG6xg5kFf/4kzOt4G+q7tk+ObbTS4mHnVYlJTbRb/6Nd2xk7fZfP1Qz5B7t6z7y9qKPL2g=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="dash-body">
    <header class="dash-top">
        <h1><i class="fas fa-vote-yea"></i> Voting System</h1>
        <nav class="dash-nav">
            <span class="dash-user"><?php echo $name; ?><?php echo $email !== '' ? ' · ' . $email : ''; ?></span>
            <a href="logout.php" class="dash-logout">Sign out</a>
        </nav>
    </header>

    <main class="dash-main">
        <section class="dash-welcome">
            <h2>Staff console</h2>
            <p>Welcome, <?php echo $name; ?>. Use this area to manage the elections, candidates, and results when those tools are connected.</p>
            <span class="dash-badge staff">Staff</span>
        </section>

        <div class="dash-grid">
            <article class="dash-tile muted">
                <i class="fas fa-plus-circle"></i>
                <h3>Create election</h3>
                <p>Set up ballots, deadlines, and eligibility rules from here once the admin module is live.</p>
            </article>
            <article class="dash-tile muted">
                <i class="fas fa-chart-simple"></i>
                <h3>Results</h3>
                <p>Aggregated tallies and export options will be available in a future update.</p>
            </article>
            <article class="dash-tile muted">
                <i class="fas fa-users"></i>
                <h3>Voters</h3>
                <p>Review participation and resolve access issues when voter management is implemented.</p>
            </article>
        </div>
    </main>
</body>

</html>
