<?php
session_start();

if (!empty($_SESSION['user_id']) && !empty($_SESSION['role'])) {
    if ($_SESSION['role'] === 'student') {
        header('Location: student_dashboard.php');
    } else {
        header('Location: staff_dashboard.php');
    }
    exit;
}

$error = isset($_GET['error']) ? trim((string) $_GET['error']) : '';
$registered = isset($_GET['registered']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — Voting System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-pVZC+SxNn8Qqxn2/nG6xg5kFf/4kzOt4G+q7tk+ObbTS4mHnVYlJTbRb/6Nd2xk7fZfP1Qz5B7t6z7y9qKPL2g=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="auth-body">
    <div class="auth-grid" aria-hidden="true"></div>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-brand">
                <span class="auth-brand-icon"><i class="fas fa-lock-open"></i></span>
            </div>
            <h1>Welcome back</h1>
            <p class="lead">Sign in with your school email to register as a valid voter.</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($registered): ?>
                <div class="alert alert-success" role="status">Account created. You can sign in now.</div>
            <?php endif; ?>

            <form action="process/login_process.php" method="post" novalidate>
                <div class="form-group">
                    <label for="role">Account type</label>
                    <select id="role" name="role" required>
                        <option value="">Select role</option>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" autocomplete="email" required placeholder="you@example.com">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-right-to-bracket"></i> Sign in
                </button>
            </form>

            <p class="auth-footer">No account? <a href="signup.php">Create one</a></p>
            <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i> Back to home</a>
        </div>
    </div>
</body>

</html>
