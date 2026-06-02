<?php 
if (session_status() === PHP_SESSION_NONE) session_start();
$isAdmin = isset($_SESSION['user_id']);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $pageTitle ?? "Dashboard Statistik Penduduk Desa" ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <link href="/kaliwungu_dashboard/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark" style="background:#1f6fb2;">
  <div class="container-fluid px-3">

    <!-- LEFT: tombol menu mobile + logo -->
    <div class="d-flex align-items-center gap-3">
      <!-- Tombol menu (mobile only) -->
      <button class="btn btn-outline-light d-lg-none"
              type="button"
              data-bs-toggle="offcanvas"
              data-bs-target="#mobileSidebar">
        ☰
      </button>

      <img src="/kaliwungu_dashboard/assets/img/logo-kudus.png"
           alt="Logo Kudus"
           class="d-block"
           style="height:36px">


      <div class="d-none d-sm-block">
        <div class="fw-bold">Dashboard Statistik Penduduk Desa</div>
        <small class="opacity-75">Desa Kaliwungu • Kudus • Jawa Tengah</small>
      </div>
    </div>

    <!-- RIGHT: login/logout -->
    <div class="d-flex gap-3 align-items-center">
      <?php if($isAdmin): ?>
        <span class="text-white opacity-75 d-none d-md-inline">Admin</span>
        <a class="text-white text-decoration-none" href="/kaliwungu_dashboard/auth/logout.php">Logout</a>
      <?php else: ?>
        <a class="text-white text-decoration-none" href="/kaliwungu_dashboard/auth/login.php">Login Admin</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container-fluid">
  <div class="row g-4 p-3 p-md-4">
