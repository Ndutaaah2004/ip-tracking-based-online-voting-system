<?php
$error = isset($_GET['error']) ? trim((string) $_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account — Voting System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-pVZC+SxNn8Qqxn2/nG6xg5kFf/4kzOt4G+q7tk+ObbTS4mHnVYlJTbRb/6Nd2xk7fZfP1Qz5B7t6z7y9qKPL2g=="
          crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="css/style.css">
    <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm').value;
            if (password !== confirm) {
                alert('Passwords do not match.');
                return false;
            }
            return true;
        }
    </script>
</head>

<body class="auth-body">
    <div class="auth-grid" aria-hidden="true"></div>
    <div class="auth-page">
        <div class="auth-card">
            <div class="auth-brand">
                <span class="auth-brand-icon"><i class="fas fa-user-plus"></i></span>
            </div>
            <h1>Create account</h1>
            <p class="lead">Join as a student or staff member to access the voting platform.</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="process/register_process.php" method="post" onsubmit="return validateForm()" novalidate>
                <div class="form-group">
                    <label for="role">Account type</label>
                    <select id="role" name="role" required>
                        <option value="">Select role</option>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="name">Display name</label>
                    <input type="text" id="name" name="name" required placeholder="Your full name" autocomplete="name">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="you@example.com" autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters">
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm password</label>
                    <input type="password" id="confirm" name="confirm_password" required autocomplete="new-password">
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check"></i> Create account
                </button>
            </form>

            <p class="auth-footer">Already registered? <a href="login.php">Sign in</a></p>
            <a href="index.php" class="back-home"><i class="fas fa-arrow-left"></i> Back to home</a>
        </div>
    </div>
</body>

</html>
