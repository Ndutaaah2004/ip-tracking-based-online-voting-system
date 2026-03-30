<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $activeNav home|ballots|history|account */

$pageTitle = $pageTitle ?? 'Student';
$activeNav = $activeNav ?? 'home';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> — Voting System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-pVZC+SxNn8Qqxn2/nG6xg5kFf/4kzOt4G+q7tk+ObbTS4mHnVYlJTbRb/6Nd2xk7fZfP1Qz5B7t6z7y9qKPL2g=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="dash-body">
    <header class="dash-top">
        <h1>
            <a href="student_dashboard.php" class="dash-logo-link"><i class="fas fa-vote-yea"></i> Voting System</a>
        </h1>
        <nav class="dash-nav">
            <span class="dash-user"><?php echo $studentName; ?><?php echo $studentEmail !== '' ? ' · ' . $studentEmail : ''; ?></span>
            <a href="logout.php" class="dash-logout">Sign out</a>
        </nav>
    </header>

    <nav class="dash-subnav" aria-label="Student sections">
        <a href="student_dashboard.php" class="<?php echo $activeNav === 'home' ? 'is-active' : ''; ?>"><i class="fas fa-house"></i> Home</a>
        <a href="student_ballots.php" class="<?php echo $activeNav === 'ballots' ? 'is-active' : ''; ?>"><i class="fas fa-list-check"></i> Active ballots</a>
        <a href="student_history.php" class="<?php echo $activeNav === 'history' ? 'is-active' : ''; ?>"><i class="fas fa-clock-rotate-left"></i> Vote history</a>
        <a href="student_account.php" class="<?php echo $activeNav === 'account' ? 'is-active' : ''; ?>"><i class="fas fa-user-gear"></i> Account</a>
    </nav>
