<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Pegawai - SiPAK</title>
    <link rel="stylesheet" href="../assets/css/styles_reg.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="register-form">
        <h1 class="main-title">SiPAK</h1>
        <h2>Registrasi Pegawai</h2>
        
        <form action="register_process_pegawai.php" method="POST" id="registrationForm">
            <div class="input-group">
                <label for="nip"><i class="fas fa-id-card"></i> NIP</label>
                <input type="text" id="nip" name="nip" placeholder="Masukkan NIP Anda 18 digit (cont.194508172000011001)" required>
            </div>
            
            <div class="input-group">
                <label for="nama"><i class="fas fa-user"></i> Nama Lengkap</label>
                <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap" required>
            </div>
            
            <div class="input-group">
                <label for="username"><i class="fas fa-user-tag"></i> Username</label>
                <input type="text" id="username" name="username" placeholder="Buat username unik" required>
            </div>
            
            <div class="input-group">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" placeholder="Buat password kuat" required>
                <span class="toggle-password" onclick="togglePassword()">
                    <i class="fas fa-eye"></i>
                </span>
            </div>
            
            <div class="input-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" placeholder="email@contoh.com" required>
            </div>
            
            <div class="input-group">
                <label for="no_hp"><i class="fas fa-phone"></i> Nomor HP</label>
                <input type="text" id="no_hp" name="no_hp" placeholder="0812-3456-7890" required>
            </div>
            
            <div id="error-message">
                <?php if (isset($_GET['error'])): ?>
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                <?php endif; ?>
            </div>
            
            <button type="submit">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
            
            <p class="login-link">
                Sudah memiliki akun? <a href="../login/index.php">Masuk disini</a>
            </p>
        </form>
    </div>
    
    <footer>
        <p>&copy; <span id="current-year"></span> Aang_Isco24. All rights reserved.</p>
    </footer>

    <script>
        // Dynamic year for footer
        document.getElementById('current-year').textContent = new Date().getFullYear();
        
        // Enhanced password toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
        
        // Form validation
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const no_hp = document.getElementById('no_hp').value;
            
            // Password strength check
            if (password.length < 8) {
                alert('Password harus minimal 8 karakter!');
                e.preventDefault();
            }
            
            // Phone number validation
            if (!/^[0-9]{10,13}$/.test(no_hp)) {
                alert('Nomor HP harus 10-13 digit angka!');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>