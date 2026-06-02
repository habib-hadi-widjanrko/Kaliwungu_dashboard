<?php
$current = $current ?? '';
function active($name, $current) {
  return $name === $current ? 'active fw-semibold' : '';
}
?>

<!-- SIDEBAR DESKTOP -->
<div class="d-none d-lg-block col-lg-3 col-xl-2">
  <div class="bg-white rounded-4 shadow-sm p-3">
    <div class="list-group list-group-flush">
      <a href="/kaliwungu_dashboard/pages/dashboard.php"
         class="list-group-item list-group-item-action <?= active('dashboard', $current) ?>">
        📊 Dashboard
      </a>
      <a href="/kaliwungu_dashboard/pages/data_list.php"
         class="list-group-item list-group-item-action <?= active('data', $current) ?>">
        🗂️ Data Bulanan
      </a>
    </div>
  </div>
</div>

<!-- SIDEBAR MOBILE (OFFCANVAS) -->
<div class="offcanvas offcanvas-start d-lg-none"
     tabindex="-1"
     id="mobileSidebar">

  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="offcanvas-body p-2">
    <div class="list-group list-group-flush">
      <a href="/kaliwungu_dashboard/pages/dashboard.php"
         class="list-group-item list-group-item-action <?= active('dashboard', $current) ?>">
        📊 Dashboard
      </a>
      <a href="/kaliwungu_dashboard/pages/data_list.php"
         class="list-group-item list-group-item-action <?= active('data', $current) ?>">
        🗂️ Data Bulanan
      </a>
    </div>
  </div>
</div>
