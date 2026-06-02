<?php
require_once __DIR__ . "/../config/db.php";

$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

$bulanNama = [1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"];

// ambil data 1 tahun
$stmt = $conn->prepare("SELECT * FROM penduduk_bulanan WHERE tahun=? ORDER BY bulan ASC");
$stmt->bind_param("i", $tahun);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while($r = $res->fetch_assoc()){
  $rows[(int)$r['bulan']] = $r;
}

// output CSV
$filename = "Data_Penduduk_Global_{$tahun}.csv";
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Pragma: no-cache');
header('Expires: 0');

// biar Excel Windows kebaca UTF-8
echo "\xEF\xBB\xBF";

$out = fopen('php://output', 'w');

fputcsv($out, [
  "Tahun","Bulan",
  "Awal_L","Awal_P","Awal_Total",
  "Lahir_L","Lahir_P","Lahir_Total",
  "Mati_L","Mati_P","Mati_Total",
  "Datang_L","Datang_P","Datang_Total",
  "Pindah_L","Pindah_P","Pindah_Total",
  "Akhir_L","Akhir_P","Akhir_Total",
  "Perubahan(Akhir-Awal)","Terakhir_Update"
]);

for($m=1;$m<=12;$m++){
  $r = $rows[$m] ?? null;

  $awal_l = $r ? (int)$r['awal_l'] : 0;
  $awal_p = $r ? (int)$r['awal_p'] : 0;
  $awal_t = $r ? (int)$r['awal_total'] : 0;

  $lahir_l = $r ? (int)$r['lahir_l'] : 0;
  $lahir_p = $r ? (int)$r['lahir_p'] : 0;
  $lahir_t = $r ? (int)$r['lahir_total'] : 0;

  $mati_l = $r ? (int)$r['mati_l'] : 0;
  $mati_p = $r ? (int)$r['mati_p'] : 0;
  $mati_t = $r ? (int)$r['mati_total'] : 0;

  $datang_l = $r ? (int)$r['datang_l'] : 0;
  $datang_p = $r ? (int)$r['datang_p'] : 0;
  $datang_t = $r ? (int)$r['datang_total'] : 0;

  $pindah_l = $r ? (int)$r['pindah_l'] : 0;
  $pindah_p = $r ? (int)$r['pindah_p'] : 0;
  $pindah_t = $r ? (int)$r['pindah_total'] : 0;

  $akhir_l = $r ? (int)$r['akhir_l'] : 0;
  $akhir_p = $r ? (int)$r['akhir_p'] : 0;
  $akhir_t = $r ? (int)$r['akhir_total'] : 0;

  $update = $r ? $r['updated_at'] : "-";
  $perubahan = $akhir_t - $awal_t;

  fputcsv($out, [
    $tahun, $bulanNama[$m],
    $awal_l,$awal_p,$awal_t,
    $lahir_l,$lahir_p,$lahir_t,
    $mati_l,$mati_p,$mati_t,
    $datang_l,$datang_p,$datang_t,
    $pindah_l,$pindah_p,$pindah_t,
    $akhir_l,$akhir_p,$akhir_t,
    $perubahan,$update
  ]);
}

fclose($out);
exit;
