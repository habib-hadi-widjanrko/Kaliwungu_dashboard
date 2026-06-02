<?php
function logAktivitas($conn, $aksi, $keterangan = '') {
  if (session_status() === PHP_SESSION_NONE) session_start();
  if (!isset($_SESSION['user_id'])) return;

  $stmt = $conn->prepare(
    "INSERT INTO log_aktivitas (user_id, aksi, keterangan) VALUES (?,?,?)"
  );
  $stmt->bind_param(
    "iss",
    $_SESSION['user_id'],
    $aksi,
    $keterangan
  );
  $stmt->execute();
}
