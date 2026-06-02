<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/auth.php";
require_once __DIR__ . "/../config/log.php";
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /kaliwungu_dashboard/pages/data_list.php");
  exit;
}

$bulanNama = [
  1=>"Januari",2=>"Februari",3=>"Maret",4=>"April",5=>"Mei",6=>"Juni",
  7=>"Juli",8=>"Agustus",9=>"September",10=>"Oktober",11=>"November",12=>"Desember"
];

function int0(string $key): int {
  $v = $_POST[$key] ?? 0;
  if (is_string($v)) {
    $v = trim($v);
    if ($v === '') $v = '0';
    $v = str_replace([",", "."], "", $v);
  }
  $n = (int)$v;
  return $n < 0 ? 0 : $n;
}

/** 1) ambil tahun/bulan */
$tahun = (int)($_POST['tahun'] ?? 0);
$bulan = (int)($_POST['bulan'] ?? 0);
if ($tahun <= 0 || $bulan < 1 || $bulan > 12) {
  die("Tahun/Bulan tidak valid.");
}

/** 2) ambil input dari form */
$awal_l   = int0('awal_l');
$awal_p   = int0('awal_p');

$lahir_l  = int0('lahir_l');
$lahir_p  = int0('lahir_p');

$mati_l   = int0('mati_l');
$mati_p   = int0('mati_p');

$datang_l = int0('datang_l');
$datang_p = int0('datang_p');

$pindah_l = int0('pindah_l');
$pindah_p = int0('pindah_p');

/** 3) hitung total */
$awal_total   = $awal_l + $awal_p;
$lahir_total  = $lahir_l + $lahir_p;
$mati_total   = $mati_l + $mati_p;
$datang_total = $datang_l + $datang_p;
$pindah_total = $pindah_l + $pindah_p;

/** 4) HITUNG AKHIR OTOMATIS (sesuai catatan di form) */
$akhir_l = $awal_l + $lahir_l + $datang_l - $mati_l - $pindah_l;
$akhir_p = $awal_p + $lahir_p + $datang_p - $mati_p - $pindah_p;

if ($akhir_l < 0 || $akhir_p < 0) {
  logAktivitas($conn, "Edit Data Bulanan (Gagal)", "Akhir negatif | Tahun $tahun Bulan {$bulanNama[$bulan]}");
  die("Perhitungan tidak valid: penduduk akhir tidak boleh negatif. Cek input mati/pindah.");
}

$akhir_total = $akhir_l + $akhir_p;

try {
  $conn->begin_transaction();

  /** 5) validasi awal_total = akhir_total bulan sebelumnya (kalau prev ada) */
  if ($bulan > 1) {
    $prev = $bulan - 1;
    $stmtPrev = $conn->prepare("SELECT akhir_total FROM penduduk_bulanan WHERE tahun=? AND bulan=? LIMIT 1");
    $stmtPrev->bind_param("ii", $tahun, $prev);
    $stmtPrev->execute();
    $prevRow = $stmtPrev->get_result()->fetch_assoc();

    if ($prevRow) {
      $prevAkhir = (int)$prevRow['akhir_total'];
      if ($awal_total !== $prevAkhir) {
        logAktivitas(
          $conn,
          "Edit Data Bulanan (Gagal)",
          "Validasi awal_total!=akhir_total prev | Tahun $tahun Bulan {$bulanNama[$bulan]} | Awal=$awal_total PrevAkhir=$prevAkhir"
        );
        $conn->rollback();
        die("Validasi gagal: Awal bulan harus sama dengan Akhir bulan sebelumnya. (Akhir bulan $prev = $prevAkhir, tapi Awal bulan ini = $awal_total)");
      }
    }
  }

  /** 6) UPSERT (insert kalau belum ada, update kalau sudah ada) */
  $stmt = $conn->prepare("
    INSERT INTO penduduk_bulanan
      (tahun, bulan,
       awal_l, awal_p, awal_total,
       lahir_l, lahir_p, lahir_total,
       mati_l, mati_p, mati_total,
       datang_l, datang_p, datang_total,
       pindah_l, pindah_p, pindah_total,
       akhir_l, akhir_p, akhir_total)
    VALUES
      (?,?,  ?,?,?,  ?,?,?,  ?,?,?,  ?,?,?,  ?,?,?,  ?,?,?)
    ON DUPLICATE KEY UPDATE
       awal_l=VALUES(awal_l), awal_p=VALUES(awal_p), awal_total=VALUES(awal_total),
       lahir_l=VALUES(lahir_l), lahir_p=VALUES(lahir_p), lahir_total=VALUES(lahir_total),
       mati_l=VALUES(mati_l), mati_p=VALUES(mati_p), mati_total=VALUES(mati_total),
       datang_l=VALUES(datang_l), datang_p=VALUES(datang_p), datang_total=VALUES(datang_total),
       pindah_l=VALUES(pindah_l), pindah_p=VALUES(pindah_p), pindah_total=VALUES(pindah_total),
       akhir_l=VALUES(akhir_l), akhir_p=VALUES(akhir_p), akhir_total=VALUES(akhir_total)
  ");

  $stmt->bind_param(
    "iiiiiiiiiiiiiiiiiiii",
    $tahun, $bulan,
    $awal_l, $awal_p, $awal_total,
    $lahir_l, $lahir_p, $lahir_total,
    $mati_l, $mati_p, $mati_total,
    $datang_l, $datang_p, $datang_total,
    $pindah_l, $pindah_p, $pindah_total,
    $akhir_l, $akhir_p, $akhir_total
  );

  if (!$stmt->execute()) {
    logAktivitas($conn, "Edit Data Bulanan (Gagal)", "DB error: {$conn->error} | Tahun $tahun Bulan {$bulanNama[$bulan]}");
    $conn->rollback();
    die("Gagal menyimpan data: " . $conn->error);
  }

  logAktivitas(
    $conn,
    "Edit Data Bulanan",
    "Tahun $tahun Bulan {$bulanNama[$bulan]} | Awal=$awal_total Lahir=$lahir_total Mati=$mati_total Datang=$datang_total Pindah=$pindah_total Akhir=$akhir_total"
  );

  $conn->commit();
  header("Location: /kaliwungu_dashboard/pages/data_list.php?tahun={$tahun}&status=ok");
  exit;

} catch (Throwable $e) {
  if ($conn) $conn->rollback();
  logAktivitas($conn, "Edit Data Bulanan (Error)", "Exception: ".$e->getMessage()." | Tahun $tahun Bulan {$bulanNama[$bulan]}");
  die("Terjadi kesalahan: " . $e->getMessage());
}
