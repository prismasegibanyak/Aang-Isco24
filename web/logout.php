<?php
// Hancurkan session TANPA sisa
session_start();
$_SESSION = [];
session_unset();
session_destroy();

// Hapus cookie session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Blokir cache browser dengan header + redirect unik
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 01 Jan 2000 00:00:00 GMT");

// Redirect dengan parameter random (WAJIB)
header("Location: ../login/index.php?r=" . bin2hex(random_bytes(8)));
exit();
?>