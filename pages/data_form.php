<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();

$pageTitle = "Input Data Bulanan";
$current = "data";

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 1;
if ($bulan < 1 || $bulan > 12) $bulan = 1;

$bulanNama = [
  1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",
  7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"
];

// ambil data bulan ini (kalau sudah ada)
$stmt = $conn->prepare("SELECT * FROM penduduk_bulanan WHERE tahun=? AND bulan=? LIMIT 1");
$stmt->bind_param("ii", $tahun, $bulan);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

// PREFILL: kalau belum ada data bulan ini, ambil awal dari akhir bulan sebelumnya
if (!$row && $bulan > 1) {
  $prev = $bulan - 1;
  $stmtPrev = $conn->prepare("SELECT akhir_l, akhir_p FROM penduduk_bulanan WHERE tahun=? AND bulan=? LIMIT 1");
  $stmtPrev->bind_param("ii", $tahun, $prev);
  $stmtPrev->execute();
  $prevRow = $stmtPrev->get_result()->fetch_assoc();

  if ($prevRow) {
    $row = [
      'awal_l' => (int)$prevRow['akhir_l'],
      'awal_p' => (int)$prevRow['akhir_p'],
      'lahir_l'=>0,'lahir_p'=>0,
      'mati_l'=>0,'mati_p'=>0,
      'datang_l'=>0,'datang_p'=>0,
      'pindah_l'=>0,'pindah_p'=>0
    ];
  }
}

function v($row, $k){ return $row ? (int)($row[$k] ?? 0) : 0; }

include __DIR__ . "/../layouts/header.php";
include __DIR__ . "/../layouts/sidebar.php";
?>
<div class="col-12 col-lg-9 col-xl-10">
  <div class="bg-white rounded-4 shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h3 class="mb-1">Input Data Bulanan</h3>
        <div class="text-muted">Tahun <?= $tahun ?> • Bulan <?= $bulanNama[$bulan] ?></div>
      </div>
      <a href="/kaliwungu_dashboard/pages/data_list.php?tahun=<?= $tahun ?>" class="btn btn-soft">Kembali</a>
    </div>

    <form class="mt-4" method="post" action="/kaliwungu_dashboard/pages/data_save.php">
      <input type="hidden" name="tahun" value="<?= $tahun ?>">
      <input type="hidden" name="bulan" value="<?= $bulan ?>">

      <h5 class="mt-3">Penduduk Awal Bulan</h5>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Awal Laki-laki</label>
          <input type="number" class="form-control" name="awal_l" value="<?= v($row,'awal_l') ?>" min="0" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Awal Perempuan</label>
          <input type="number" class="form-control" name="awal_p" value="<?= v($row,'awal_p') ?>" min="0" required>
        </div>
      </div>

      <h5 class="mt-4">Perubahan Bulan Ini</h5>
      <div class="row g-3">
        <?php foreach(['lahir','mati','datang','pindah'] as $k): ?>
          <div class="col-md-6">
            <label class="form-label"><?= ucfirst($k) ?> L</label>
            <input type="number" class="form-control" name="<?= $k ?>_l" value="<?= v($row,$k.'_l') ?>" min="0">
          </div>
          <div class="col-md-6">
            <label class="form-label"><?= ucfirst($k) ?> P</label>
            <input type="number" class="form-control" name="<?= $k ?>_p" value="<?= v($row,$k.'_p') ?>" min="0">
          </div>
        <?php endforeach; ?>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <a class="btn btn-soft" href="/kaliwungu_dashboard/pages/data_list.php?tahun=<?= $tahun ?>">Batal</a>
      </div>

      <div class="alert alert-info mt-4 mb-0">
        <b>Catatan:</b> Penduduk akhir & total dihitung otomatis.
      </div>
    </form>
  </div>
</div>
<?php include __DIR__ . "/../layouts/footer.php"; ?>
