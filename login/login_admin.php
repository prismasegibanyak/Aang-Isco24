<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SiPAK</title>
    <link rel="stylesheet" href="../assets/css/styles_logadmin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-login-container">
        <div class="admin-login-form">
            <div class="admin-header">
                <h1 class="admin-title">SiPAK</h1>
                <h2 class="admin-subtitle">Login Admin</h2>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="admin-error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <form id="adminLoginForm" method="POST" action="login_admin_process.php">
                <div class="admin-input-group">
                    <label for="username"><i class="fas fa-user-shield"></i> Admin Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter admin username" autocomplete="username" required>
                </div>
                
                <div class="admin-input-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
                    <span class="admin-toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                
                <button type="submit" class="admin-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <div class="admin-footer">
                    <a href="index.php" class="admin-back-link">
                        <i class="fas fa-arrow-left"></i> Back to User Login
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="copyright">
        <p>&copy; <span id="current-year"></span> Aang_Isco24. All rights reserved.</p>
    </div>

    <script>
        // Dynamic year for copyright
        document.getElementById('current-year').textContent = new Date().getFullYear();
        
        // Enhanced password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.querySelector('.admin-toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        
        // Form submission animation
        document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
            const btn = this.querySelector('button');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Authenticating...';
            btn.disabled = true;
        });
    </script>
</body>
</html>