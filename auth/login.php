<?php
require_once __DIR__ . "/../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $conn->prepare("SELECT id, username, password_hash FROM users WHERE username=? LIMIT 1");
  $stmt->bind_param("s", $username);
  $stmt->execute();
  $u = $stmt->get_result()->fetch_assoc();

  if ($u && password_verify($password, $u['password_hash'])) {
    $_SESSION['user_id'] = (int)$u['id'];
    $_SESSION['username'] = $u['username'];
    header("Location: /kaliwungu_dashboard/pages/dashboard.php");
    exit;
  } else {
    $error = "Username atau password salah.";
  }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-5">
      <div class="card shadow-sm rounded-4">
        <div class="card-body p-4">
          <h4 class="mb-2">Login Admin</h4>
          <div class="text-muted mb-3">Dashboard Statistik Penduduk Desa</div>

          <?php if($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="post">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input class="form-control" name="username" required autocomplete="username" />
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input class="form-control" type="password" name="password" required autocomplete="current-password" />
            </div>
            <button class="btn btn-primary w-100">Masuk</button>
          </form>

          <div class="text-muted small mt-3">
            Gunakan akun admin yang sudah dibuat.
          </div>


          <div class="mt-3">
            <a href="/kaliwungu_dashboard/public/index.php" class="text-decoration-none">Lihat Dashboard Publik</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
