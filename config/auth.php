<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function is_logged_in(): bool {
  return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function require_login(): void {
  if (!is_logged_in()) {
    header("Location: /kaliwungu_dashboard/auth/login.php");
    exit;
  }
}
