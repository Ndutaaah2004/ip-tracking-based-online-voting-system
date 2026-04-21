<?php

declare(strict_types=1);

/** @var string $pageTitle */
/** @var string $activeNav home|elections|results|voters */

$pageTitle = $pageTitle ?? 'Staff';
$activeNav = $activeNav ?? 'home';

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> — Staff</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-pVZC+SxNn8Qqxn2/nG6xg5kFf/4kzOt4G+q7tk+ObbTS4mHnVYlJTbRb/6Nd2xk7fZfP1Qz5B7t6z7y9qKPL2g=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="dash-body">
    <header class="dash-top dash-top-staff">
        <h1>
            <a href="staff_dashboard.php" class="dash-logo-link"><i class="fas fa-vote-yea"></i> Voting System</a>
        </h1>
        <nav class="dash-nav">
            <span class="dash-user"><?php echo $staffName; ?><?php echo $staffEmail !== '' ? ' · ' . $staffEmail : ''; ?><span class="dash-ip" title="IP address recorded at sign-in"><i class="fas fa-network-wired" aria-hidden="true"></i> <?php echo $loginIpDisplay; ?></span></span>
            <a href="logout.php" class="dash-logout">Sign out</a>
        </nav>
    </header>

    <nav class="dash-subnav dash-subnav-staff" aria-label="Staff sections">
        <a href="staff_dashboard.php" class="<?php echo $activeNav === 'home' ? 'is-active' : ''; ?>"><i class="fas fa-house"></i> Home</a>
        <a href="staff_elections.php" class="<?php echo $activeNav === 'elections' ? 'is-active' : ''; ?>"><i class="fas fa-calendar-plus"></i> Elections</a>
        <a href="staff_results.php" class="<?php echo $activeNav === 'results' ? 'is-active' : ''; ?>"><i class="fas fa-chart-simple"></i> Results</a>
        <a href="staff_voters.php" class="<?php echo $activeNav === 'voters' ? 'is-active' : ''; ?>"><i class="fas fa-users"></i> Voters</a>
    </nav>
