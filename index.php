<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novatech - Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Theme CSS -->
    <link href="assets/css/theme.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="platform-info">
            <h1>Welcome to Novatech</h1>
            <p>Your trusted platform for smart investments</p>
        </div>
        <div class="form-container">
            <form method="POST" action="auth/login.php">
                <h3>Login</h3>
                <div class="mb-3">
                    <label for="loginPhone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="loginPhone" name="phone_number" placeholder="Enter your phone number" required>
                </div>
                <div class="mb-3">
                    <label for="loginPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="text-center mt-3">
                <a href="signup.php" class="btn btn-link">Don't have an account? Sign up</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Theme JS -->
    <script src="assets/js/theme.js"></script>
</body>
</html>
