<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$_SESSION = [];
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(session_name(), '', time() - 42000,
    $params["path"], $params["domain"],
    $params["secure"], $params["httponly"]
  );
}

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/log.php";

logAktivitas($conn, "Logout", "Admin keluar sistem");
session_destroy();

header("Location: /kaliwungu_dashboard/auth/login.php");
exit;
