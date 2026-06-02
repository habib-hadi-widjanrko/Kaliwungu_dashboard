<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_login();
$pageTitle = "Dashboard";
$current = "dashboard";

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : 'all'; // 'all' atau 1-12

$bulanNama = [1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"];

/** Ambil semua data 1 tahun (urut Jan–Des) */
$stmt = $conn->prepare("SELECT * FROM penduduk_bulanan WHERE tahun=? ORDER BY bulan ASC");
$stmt->bind_param("i", $tahun);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while($r = $res->fetch_assoc()){
  $rows[(int)$r['bulan']] = $r;
}

/** ===== (1) AUTO PILIH BULAN TERAKHIR YANG ADA DATA ===== */
$lastMonthWithData = 0;
foreach ($rows as $m => $r) $lastMonthWithData = $m;

// Kalau tahun tsb belum ada data sama sekali, fallback ke Januari
if ($lastMonthWithData <= 0) $lastMonthWithData = 1;

// Tentukan activeMonth yang benar-benar ada data (atau fallback)
if ($bulan === 'all') {
  $activeMonth = $lastMonthWithData; // "Semua Bulan" = fokus bulan terakhir (ringkasan)
} else {
  $activeMonth = (int)$bulan;
  if ($activeMonth < 1 || $activeMonth > 12 || !isset($rows[$activeMonth])) {
    // user pilih bulan yang belum ada data -> fallback ke terakhir yang ada
    $activeMonth = $lastMonthWithData;
    $bulan = (string)$activeMonth; // biar dropdown ikut sync
  }
}

$active = isset($rows[$activeMonth]) ? $rows[$activeMonth] : null;

/** Ringkasan card */
$totalAkhir = $active ? (int)$active['akhir_total'] : 0;
$lkAkhir    = $active ? (int)$active['akhir_l'] : 0;
$prAkhir    = $active ? (int)$active['akhir_p'] : 0;
$perubahan  = $active ? ((int)$active['akhir_total'] - (int)$active['awal_total']) : 0;

/** ===== (3) INSIGHT: PERTUMBUHAN TERTINGGI SETAHUN ===== */
$bulanTop = 0; $deltaTop = 0;
$bulanMin = 0; $deltaMin = 0;

if (!empty($rows)) {
  // pertumbuhan terbesar
  $ins1 = $conn->prepare("
    SELECT bulan, (akhir_total - awal_total) AS delta
    FROM penduduk_bulanan
    WHERE tahun=?
    ORDER BY delta DESC
    LIMIT 1
  ");
  $ins1->bind_param("i", $tahun);
  $ins1->execute();
  $top = $ins1->get_result()->fetch_assoc();
  $bulanTop = $top ? (int)$top['bulan'] : 0;
  $deltaTop = $top ? (int)$top['delta'] : 0;

  // penurunan terbesar (opsional, tetap kecil)
  $ins2 = $conn->prepare("
    SELECT bulan, (akhir_total - awal_total) AS delta
    FROM penduduk_bulanan
    WHERE tahun=?
    ORDER BY delta ASC
    LIMIT 1
  ");
  $ins2->bind_param("i", $tahun);
  $ins2->execute();
  $min = $ins2->get_result()->fetch_assoc();
  $bulanMin = $min ? (int)$min['bulan'] : 0;
  $deltaMin = $min ? (int)$min['delta'] : 0;
}

/** Data chart (Jan–Des, isi 0 kalau belum ada) */
$labels = [];
$akhirTotals = [];
$lahirTotals = [];
$matiTotals = [];
$datangTotals = [];
$pindahTotals = [];

for($m=1;$m<=12;$m++){
  $labels[] = $bulanNama[$m];
  $r = $rows[$m] ?? null;
  $akhirTotals[]  = $r ? (int)$r['akhir_total'] : 0;
  $lahirTotals[]  = $r ? (int)$r['lahir_total'] : 0;
  $matiTotals[]   = $r ? (int)$r['mati_total'] : 0;
  $datangTotals[] = $r ? (int)$r['datang_total'] : 0;
  $pindahTotals[] = $r ? (int)$r['pindah_total'] : 0;
}

include __DIR__ . "/../layouts/header.php";
include __DIR__ . "/../layouts/sidebar.php";
?>

<div class="col-12 col-lg-9 col-xl-10">
  <div class="bg-white rounded-4 shadow-sm p-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <h4 class="mb-1">Ringkasan</h4>
        <div class="text-muted">Data global Desa Kaliwungu (per bulan). Pilih tahun & bulan untuk melihat detail.</div>

        <!-- Insight kecil (3) -->
        <div class="text-muted mt-1">
          Pertumbuhan tertinggi:
          <b><?= $bulanTop ? $bulanNama[$bulanTop] : "-" ?></b>
          (<?= $deltaTop >= 0 ? "+" : "" ?><?= $deltaTop ?> jiwa)
          <?php if ($bulanMin): ?>
            • Penurunan terbesar:
            <b><?= $bulanNama[$bulanMin] ?></b>
            (<?= $deltaMin >= 0 ? "+" : "" ?><?= $deltaMin ?> jiwa)
          <?php endif; ?>
        </div>
      </div>

      <form class="d-flex align-items-center gap-2" method="get">
        <div>
          <small class="text-muted">Tahun</small>
          <select class="form-select" name="tahun">
            <?php for($y=$tahun-2; $y<=$tahun+1; $y++): ?>
              <option value="<?= $y ?>" <?= $y===$tahun?'selected':'' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <div>
          <small class="text-muted">Bulan</small>
          <select class="form-select" name="bulan">
            <option value="all" <?= $bulan==='all'?'selected':'' ?>>Semua Bulan</option>
            <?php for($m=1;$m<=12;$m++): ?>
              <option value="<?= $m ?>" <?= ((string)$m===(string)$bulan)?'selected':'' ?>><?= $bulanNama[$m] ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <button class="btn btn-primary mt-4">Terapkan</button>

        <!-- tombol input bulan tetap, tapi pakai activeMonth yang valid -->
        <a class="btn btn-soft mt-4" href="/kaliwungu_dashboard/pages/data_form.php?tahun=<?= $tahun ?>&bulan=<?= max(1,$activeMonth) ?>">
          + Input Bulan
        </a>
      </form>
    </div>

    <!-- Cards -->
    <div class="row g-3 mt-3">
      <div class="col-12 col-md-3">
        <div class="card-soft p-3">
          <!-- (2) label rapih pakai nama bulan -->
          <div class="text-muted small">Total Penduduk (Akhir <?= $bulanNama[$activeMonth] ?>)</div>
          <div class="d-flex align-items-end gap-2">
            <div class="display-6 fw-bold mb-0"><?= $totalAkhir ?></div>
            <div class="text-muted">Jiwa</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="card-soft p-3">
          <div class="text-muted small">Laki-laki (Akhir <?= $bulanNama[$activeMonth] ?>)</div>
          <div class="d-flex align-items-end gap-2">
            <div class="display-6 fw-bold mb-0"><?= $lkAkhir ?></div>
            <div class="text-muted">Jiwa</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="card-soft p-3">
          <div class="text-muted small">Perempuan (Akhir <?= $bulanNama[$activeMonth] ?>)</div>
          <div class="d-flex align-items-end gap-2">
            <div class="display-6 fw-bold mb-0"><?= $prAkhir ?></div>
            <div class="text-muted">Jiwa</div>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-3">
        <div class="card-soft p-3">
          <div class="text-muted small">Perubahan Bulan Ini (<?= $bulanNama[$activeMonth] ?>)</div>
          <div class="d-flex align-items-end gap-2">
            <div class="display-6 fw-bold mb-0"><?= ($perubahan>=0?'+':'').$perubahan ?></div>
            <div class="text-muted">Jiwa</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts -->
    <div class="row g-3 mt-3">
      <div class="col-12 col-lg-7">
        <div class="card-soft p-3">
          <h6 class="mb-1">Tren Penduduk Akhir Bulan (<?= $tahun ?>)</h6>
          <div class="text-muted small mb-2">Garis menunjukkan total penduduk akhir bulan per bulan.</div>
          <canvas id="chartAkhir"></canvas>
        </div>
      </div>

      <div class="col-12 col-lg-5">
        <div class="card-soft p-3">
          <!-- (2) judul rapih -->
          <h6 class="mb-1">Komposisi Gender (<?= $bulanNama[$activeMonth] ?> <?= $tahun ?>)</h6>
          <div class="text-muted small mb-2">Perbandingan laki-laki vs perempuan (akhir bulan).</div>
          <canvas id="chartGender"></canvas>
        </div>
      </div>
    </div>

    <div class="card-soft p-3 mt-3">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h6 class="mb-1">Perubahan Penduduk per Bulan (<?= $tahun ?>)</h6>
          <div class="text-muted small">Ringkasan Lahir, Mati, Datang, Pindah (total) tiap bulan.</div>
        </div>
        <div class="d-flex gap-2">
          <a class="btn btn-danger" href="/kaliwungu_dashboard/pages/export_pdf.php?tahun=<?= $tahun ?>">Export PDF</a>
          <a class="btn btn-dark" href="/kaliwungu_dashboard/pages/export_excel.php?tahun=<?= $tahun ?>">Export Excel</a>
        </div>
      </div>
      <div class="mt-3">
        <canvas id="chartPerubahan"></canvas>
      </div>
    </div>

    <!-- Table -->
    <div class="card-soft p-3 mt-3">
      <h6 class="mb-1">Tabel Data Bulanan</h6>
      <div class="card-soft p-3 mt-4">
        <h6 class="mb-2">Log Aktivitas Admin</h6>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead class="table-light">
              <tr>
                <th>Waktu</th>
                <th>Aksi</th>
                <th>Keterangan</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $log = $conn->query("
                SELECT l.*, u.username
                FROM log_aktivitas l
                JOIN users u ON u.id = l.user_id
                ORDER BY l.created_at DESC
                LIMIT 10
              ");
              while($r = $log->fetch_assoc()):
              ?>
                <tr>
                  <td><?= date('d-m-Y H:i', strtotime($r['created_at'])) ?></td>
                  <td><?= $r['aksi'] ?></td>
                  <td><?= $r['keterangan'] ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="text-muted small mb-2">Struktur mengikuti laporan: awal, lahir, mati, datang, pindah, akhir (L/P/Total).</div>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead class="table-light">
            <tr>
              <th>Bulan</th>
              <th>Awal (L/P/T)</th>
              <th>Lahir (L/P/T)</th>
              <th>Mati (L/P/T)</th>
              <th>Datang (L/P/T)</th>
              <th>Pindah (L/P/T)</th>
              <th>Akhir (L/P/T)</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php for($m=1;$m<=12;$m++): $r=$rows[$m]??null; ?>
              <tr>
                <td><span class="badge-month"><?= $bulanNama[$m] ?></span></td>
                <td><?= $r ? "{$r['awal_l']}/{$r['awal_p']}/{$r['awal_total']}" : "<span class='text-muted'>Belum ada data.</span>" ?></td>
                <td><?= $r ? "{$r['lahir_l']}/{$r['lahir_p']}/{$r['lahir_total']}" : "-" ?></td>
                <td><?= $r ? "{$r['mati_l']}/{$r['mati_p']}/{$r['mati_total']}" : "-" ?></td>
                <td><?= $r ? "{$r['datang_l']}/{$r['datang_p']}/{$r['datang_total']}" : "-" ?></td>
                <td><?= $r ? "{$r['pindah_l']}/{$r['pindah_p']}/{$r['pindah_total']}" : "-" ?></td>
                <td><?= $r ? "<b>{$r['akhir_l']}/{$r['akhir_p']}/{$r['akhir_total']}</b>" : "-" ?></td>
                <td class="text-end">
                  <a class="btn btn-soft" href="/kaliwungu_dashboard/pages/data_form.php?tahun=<?= $tahun ?>&bulan=<?= $m ?>">Input</a>
                </td>
              </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<script>
const labels = <?= json_encode($labels) ?>;

// 1) Tren Akhir
new Chart(document.getElementById('chartAkhir'), {
  type: 'line',
  data: {
    labels,
    datasets: [{
      label: 'Penduduk Akhir (Total)',
      data: <?= json_encode($akhirTotals) ?>,
      tension: 0.3
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: true } }
  }
});

// 2) Gender
new Chart(document.getElementById('chartGender'), {
  type: 'pie',
  data: {
    labels: ['Laki-laki', 'Perempuan'],
    datasets: [{
      data: [<?= $lkAkhir ?>, <?= $prAkhir ?>]
    }]
  },
  options: { responsive: true }
});

// 3) Perubahan per bulan
new Chart(document.getElementById('chartPerubahan'), {
  type: 'bar',
  data: {
    labels,
    datasets: [
      { label: 'Lahir',  data: <?= json_encode($lahirTotals) ?> },
      { label: 'Mati',   data: <?= json_encode($matiTotals) ?> },
      { label: 'Datang', data: <?= json_encode($datangTotals) ?> },
      { label: 'Pindah', data: <?= json_encode($pindahTotals) ?> }
    ]
  },
  options: {
    responsive: true,
    plugins: { legend: { position: 'top' } }
  }
});
</script>

<?php include __DIR__ . "/../layouts/footer.php"; ?>
