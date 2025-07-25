<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tambahkan meta tag no-cache -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Login - SiPAK</title>
    <link rel="stylesheet" href="../assets/css/styles_index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <h1 class="main-title">SiPAK</h1>
            <h2>Login Pegawai</h2>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Form dengan autocomplete off dan teknik anti-autofill -->
            <form id="loginForm" method="POST" autocomplete="off">
                <div class="input-group">
                    <label for="username"><i class="fas fa-user"></i>  Username</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" 
                           autocomplete="new-username" readonly onfocus="this.removeAttribute('readonly')">
                </div>
                
                <div class="input-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" 
                           autocomplete="new-password" readonly onfocus="this.removeAttribute('readonly')">
                    <span class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                
                <div class="input-group">
                    <label for="role"><i class="fas fa-user-tag"></i> Pilih Role</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Pilih peran Anda</option>
                        <option value="pegawai">Pegawai</option>
                        <option value="petugas">Petugas</option>
                    </select>
                </div>
                
                <button type="submit">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
                
                <div class="button-group">
                    <a href="../web/index.php" class="button">
                        <i class="fas fa-arrow-left"></i> Kembali ke Web
                    </a>
                    <span>|</span>
                    <a href="login_admin.php" class="button">
                        <i class="fas fa-user-shield"></i> Admin
                    </a>
                </div>
                
                <p class="register-link">
                    Belum punya akun? <a href="../register/register.php">Daftar disini</a>
                </p>
            </form>
        </div>
    </div>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Aang_Isco24. All rights reserved.</p>
    </footer>
    
    <script>
        // Reset form dan blokir autofill
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loginForm').reset();
            
            // Blokir tombol back
            history.pushState(null, null, location.href);
            window.onpopstate = function() {
                history.go(1);
                location.reload(); // Force reload jika tetap mencoba back
            };
        });
        function togglePassword() {
            const passwordField = document.getElementById("password");
            const icon = document.querySelector(".toggle-password i");
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordField.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }

        document.getElementById('loginForm').onsubmit = function() {
            const role = document.getElementById('role').value;
            this.action = role === 'pegawai' 
                ? 'login_process_pegawai.php' 
                : 'login_process_petugas.php';
        };
    </script>
</body>
</html>