<?php
require_once __DIR__ . "/../config/db.php";

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$bulanNama = [1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"];

// Ambil data setahun
$stmt = $conn->prepare("SELECT * FROM penduduk_bulanan WHERE tahun=? ORDER BY bulan ASC");
$stmt->bind_param("i", $tahun);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while($r = $res->fetch_assoc()){
  $rows[(int)$r['bulan']] = $r;
}
function v($r,$k){ return $r ? (int)$r[$k] : 0; }
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan Kependudukan <?= $tahun ?></title>
<style>
  body{font-family:"Times New Roman",serif;color:#000;margin:22px}
  *{box-sizing:border-box}

  .kop{display:flex;gap:16px;align-items:center}
  .kop img{width:78px;height:78px}
  .kop .text{text-align:center;flex:1}
  .kop h1{margin:0;font-size:18px}
  .kop h2{margin:2px 0 0;font-size:16px}
  .kop p{margin:4px 0 0;font-size:12px}

  .line{border-top:3px solid #000;margin:10px 0 2px}
  .line2{border-top:1px solid #000;margin-bottom:14px}

  .meta{font-size:12px;margin-bottom:10px}
  .meta table{width:100%}
  .meta td{padding:2px 0}

  .judul{text-align:center;margin:12px 0}
  .judul u{font-weight:bold}

  table.data{width:100%;border-collapse:collapse;font-size:11px}
  table.data th, table.data td{
    border:1px solid #000;padding:5px;text-align:center;vertical-align:middle
  }
  table.data td.left{text-align:left}

  .note{font-size:11px;margin-top:10px;text-align:justify}
  .ttd{width:260px;margin-left:auto;margin-top:28px;font-size:12px;text-align:center}
  .ttd .space{height:70px}

  .no-print{text-align:right;margin-bottom:10px}
  .no-print button,.no-print a{
    padding:6px 10px;border:1px solid #000;background:#fff;
    text-decoration:none;color:#000;margin-left:6px;cursor:pointer
  }

  @media print{
    .no-print{display:none}
    body{margin:12mm}
  }
</style>
</head>
<body>

<div class="no-print">
  <button onclick="window.print()">Cetak / Simpan PDF</button>
  <a href="/kaliwungu_dashboard/pages/dashboard.php?tahun=<?= $tahun ?>">Kembali</a>
</div>

<!-- KOP SURAT RESMI -->
<div class="kop">
  <img src="/kaliwungu_dashboard/assets/img/logo-kudus.png" style="width:80px; height:auto;" alt="Logo Kudus">
  <div class="text">
    <h1>PEMERINTAH DESA KALIWUNGU</h1>
    <h2>KECAMATAN KALIWUNGU</h2>
    <h2>KABUPATEN KUDUS</h2>
    <p>Ds Kaliwungu RT 05 RW 03 Kodepos 59332</p>
    <p>Email: desakaliwungpemerintah@gmail.com Website desa-kaliwungu.kuduskab.go.id</p>
  </div>
</div>
<div class="line"></div>
<div class="line2"></div>

<!-- META SURAT -->
<div class="meta">
  <table>
    <tr>
      <td style="width:120px">Nomor</td>
      <td>: 470 / ____ / <?= date('Y') ?></td>
      <td style="width:160px">Kaliwungu, <?= date("d-m-Y") ?></td>
    </tr>
    <tr>
      <td>Perihal</td>
      <td>: Laporan Data Kependudukan</td>
      <td></td>
    </tr>
  </table>
</div>

<div class="judul">
  <u>LAPORAN DATA KEPENDUDUKAN DESA KALIWUNGU</u><br>
  <span>Tahun <?= $tahun ?></span>
</div>

<p style="font-size:12px;text-align:justify">
  Bersama ini kami sampaikan laporan data kependudukan penduduk Desa Kaliwungu
  secara global (tanpa per dusun) untuk Tahun <?= $tahun ?>, sebagai bahan
  administrasi dan evaluasi pemerintah desa.
</p>

<table class="data">
  <thead>
    <tr>
      <th rowspan="2">Bulan</th>
      <th colspan="3">Awal</th>
      <th colspan="3">Lahir</th>
      <th colspan="3">Mati</th>
      <th colspan="3">Datang</th>
      <th colspan="3">Pindah</th>
      <th colspan="3">Akhir</th>
    </tr>
    <tr>
      <?php for($i=0;$i<6;$i++): ?>
        <th>L</th><th>P</th><th>Jml</th>
      <?php endfor; ?>
    </tr>
  </thead>
  <tbody>
    <?php for($m=1;$m<=12;$m++): $r=$rows[$m]??null; ?>
    <tr>
      <td class="left"><b><?= $bulanNama[$m] ?></b></td>

      <td><?= v($r,'awal_l') ?></td>
      <td><?= v($r,'awal_p') ?></td>
      <td><b><?= v($r,'awal_total') ?></b></td>

      <td><?= v($r,'lahir_l') ?></td>
      <td><?= v($r,'lahir_p') ?></td>
      <td><b><?= v($r,'lahir_total') ?></b></td>

      <td><?= v($r,'mati_l') ?></td>
      <td><?= v($r,'mati_p') ?></td>
      <td><b><?= v($r,'mati_total') ?></b></td>

      <td><?= v($r,'datang_l') ?></td>
      <td><?= v($r,'datang_p') ?></td>
      <td><b><?= v($r,'datang_total') ?></b></td>

      <td><?= v($r,'pindah_l') ?></td>
      <td><?= v($r,'pindah_p') ?></td>
      <td><b><?= v($r,'pindah_total') ?></b></td>

      <td><b><?= v($r,'akhir_l') ?></b></td>
      <td><b><?= v($r,'akhir_p') ?></b></td>
      <td style="background:#eee"><b><?= v($r,'akhir_total') ?></b></td>
    </tr>
    <?php endfor; ?>
  </tbody>
</table>

<div class="note">
  <b>Keterangan:</b> Data bersifat global Desa Kaliwungu. Jumlah (Jml) merupakan
  penjumlahan penduduk Laki-laki (L) dan Perempuan (P).
</div>

<div class="ttd">
  <div>Kepala Desa Kaliwungu</div>
  <div class="space"></div>
  <div><u>( ______________________ )</u></div>
</div>

<script>
  window.onload = () => window.print();
</script>

</body>
</html>
