<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aang-Isco24 | Personal Website</title>
    <link rel="stylesheet" href="../assets/css/styles_web.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Selamat Datang di</h1>
            <h1><span class="highlight">Aang-Isco24</span></h1>
        </header>
        
        <main>
            <div class="content">
                <a href="../login/index.php" class="button">
                    <i class="fas fa-sign-in-alt" style="margin-right: 10px;"></i>
                    Masuk ke SiPAK
                </a>
            </div>
        </main>
    </div>
    
    <footer>
        <p>&copy; <span id="current-year"></span> Aang_Isco24. All rights reserved.</p>
    </footer>

    <script>
        // Dynamic year for footer
        document.getElementById('current-year').textContent = new Date().getFullYear();
        
        // Add floating particles effect
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.querySelector('body');
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random properties
                const size = Math.random() * 10 + 5;
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                const delay = Math.random() * 5;
                const duration = Math.random() * 10 + 10;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                particle.style.animationDelay = `${delay}s`;
                particle.style.animationDuration = `${duration}s`;
                
                body.appendChild(particle);
            }
        });
    </script>
</body>
</html>