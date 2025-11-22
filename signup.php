<?php
// Pre-fill the invitation code from the URL if available
$referralCode = isset($_GET['referral_code']) ? $_GET['referral_code'] : '';
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novatech - Signup</title>
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
            <form method="POST" action="auth/register.php" id="signupForm">
                <h3>Signup</h3>
                <div class="mb-3">
                    <label for="signupName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="signupName" name="name" placeholder="Enter your name" required>
                </div>
                <div class="mb-3">
                    <label for="signupPhone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="signupPhone" name="phone_number" placeholder="Enter your phone number" required>
                    <div id="phoneError" class="text-danger mt-1" style="display:none;">Invalid phone number. Must start with 078, 073, 072, 2507, or +2507 and be at least 10 digits.</div>
                </div>
                <div class="mb-3">
                    <label for="signupInvitationCode" class="form-label">Invitation Code</label>
                    <input type="text" class="form-control" id="signupInvitationCode" name="invitation_code" placeholder="Enter invitation code" required value="<?php echo htmlspecialchars($referralCode); ?>" readonly >
                </div>
                <div class="mb-3">
                    <label for="signupPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="signupPassword" name="password" placeholder="Create a password" min='6' required>
                </div>
                <div class="mb-3">
                    <label for="signupConfirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="signupConfirmPassword" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Signup</button>
            </form>
            <div class="text-center mt-3">
                <a href="index.php" class="btn btn-link">Already have an account? Login</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Theme JS -->
    <script src="assets/js/theme.js"></script>
    <!-- Custom JS -->
    <script>
        // Signup phone validation
        document.getElementById("signupForm").addEventListener("submit", function (e) {
            const phoneInput = document.getElementById("signupPhone").value.trim();
            const phoneError = document.getElementById("phoneError");
            const phonePattern = /^(078|073|072|2507|\+2507)\d{6,}$/;

            if (!phonePattern.test(phoneInput) || phoneInput.length < 10) {
                phoneError.style.display = "block";
                e.preventDefault();
            } else {
                phoneError.style.display = "none";
            }
        });
    </script>
</body>
</html>
