<?php
require_once __DIR__ . "/../config/db.php";
$pageTitle = "Data Bulanan";
$current = "data";

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$bulanNama = [1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"];

$stmt = $conn->prepare("SELECT bulan, akhir_l, akhir_p, akhir_total, updated_at
                        FROM penduduk_bulanan WHERE tahun=?");
$stmt->bind_param("i", $tahun);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
  $data[(int)$row['bulan']] = $row;
}

include __DIR__ . "/../layouts/header.php";
include __DIR__ . "/../layouts/sidebar.php";
?>
<div class="col-12 col-lg-9 col-xl-10">
  <div class="bg-white rounded-4 shadow-sm p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <h4 class="mb-1">Data Bulanan</h4>
        <div class="text-muted">Tambah/edit data penduduk per bulan (global Desa Kaliwungu).</div>
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
        <button class="btn btn-primary mt-4">Terapkan</button>
        <a class="btn btn-soft mt-4" href="/kaliwungu_dashboard/pages/data_form.php?tahun=<?= $tahun ?>">+ Tambah</a>
      </form>
    </div>

    <div class="table-responsive mt-4">
      <table class="table align-middle">
        <thead class="table-light">
          <tr>
            <th>Bulan</th>
            <th>Akhir Bulan (Lk/Pr/Total)</th>
            <th>Terakhir Update</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php for($b=1; $b<=12; $b++): ?>
            <?php $row = $data[$b] ?? null; ?>
            <tr>
              <td><span class="badge-month"><?= $bulanNama[$b] ?></span></td>
              <td>
                <?php if($row): ?>
                  <?= (int)$row['akhir_l'] ?> / <?= (int)$row['akhir_p'] ?> / <b><?= (int)$row['akhir_total'] ?></b>
                <?php else: ?>
                  <span class="text-muted">Belum ada data.</span>
                <?php endif; ?>
              </td>
              <td>
                <?= $row ? date("d M Y H:i", strtotime($row['updated_at'])) : "-" ?>
              </td>
              <td class="text-end">
                <a class="btn btn-soft" href="/kaliwungu_dashboard/pages/data_form.php?tahun=<?= $tahun ?>&bulan=<?= $b ?>">
                  Input
                </a>
              </td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . "/../layouts/footer.php"; ?>
