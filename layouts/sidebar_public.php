<?php
// sidebar public: hanya link Dashboard Publik + Login Admin
?>
<div class="col-12 col-lg-3 col-xl-2">
  <div class="bg-white rounded-4 shadow-sm p-3">
    <div class="d-grid gap-2">
      <a class="btn <?= ($current==='public_dashboard') ? 'btn-primary' : 'btn-soft' ?>"
         href="/kaliwungu_dashboard/public/index.php">
        📊 Dashboard Publik
      </a>
      <a class="btn btn-soft" href="/kaliwungu_dashboard/auth/login.php">
        🔐 Login Admin
      </a>
    </div>
  </div>
</div>
