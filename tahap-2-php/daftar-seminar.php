<?php

declare(strict_types=1);

require_once __DIR__ . '/Seminar.php';
session_start();

if (!isset($_SESSION['kuota'])) {
    $_SESSION['kuota'] = 40;
    $_SESSION['peserta'] = [];
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = md5((string) time());
}
if (!isset($_COOKIE['peran'])) {
    setcookie('peran', 'peserta');
}

$seminar = new seminar_kuota('Seminar Keamanan Web', 0);
$seminar->kuota = (int) $_SESSION['kuota'];

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
        exit('Token tidak cocok.');
    }

    $nim = trim($_POST['nim'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $aksi = $_POST['aksi'] ?? '';
    $jumlah = (int) ($_POST['jumlah'] ?? 1);

    if (strlen($nim) !== 10) {
        $errors[] = 'NIM harus 10 karakter.';
    }

    if ($nama === '') {
        $errors[] = 'Nama lengkap harus diisi.';
    }

    if ($jumlah <= 0) {
        $errors[] = 'Jumlah kursi harus minimal 1.';
    }

    if (count($errors) === 0) {
        switch ($aksi) {
            case 'daftar':
                if ($seminar->pesan($jumlah)) {
                    $_SESSION['peserta'][] = ['nim' => $nim, 'nama' => $nama];
                } else {
                    $errors[] = 'Pendaftaran gagal: sisa kursi tidak mencukupi.';
                }
                break;
            case 'batal':
                foreach ($_SESSION['peserta'] as $key => $p) {
                    if ($p['nim'] === $nim) {
                        unset($_SESSION['peserta'][$key]);
                        $_SESSION['peserta'] = array_values($_SESSION['peserta']);
                        break;
                    }
                }
                $seminar->batal($jumlah);
                break;
        }
        $_SESSION['kuota'] = $seminar->Sisa_Kursi();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pendaftaran Seminar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
  <h1 class="h3">Seminar Keamanan Web</h1>
  <p>Sisa kursi: <strong><?= $seminar->Sisa_Kursi() ?></strong></p>

  <?php foreach ($errors as $e): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endforeach; ?>

  <form method="POST" action="daftar-seminar.php" class="mb-4">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars((string) $_SESSION['csrf'], ENT_QUOTES, 'UTF-8') ?>">
    <input class="form-control mb-2" name="nim" placeholder="NIM">
    <input class="form-control mb-2" name="nama" placeholder="Nama lengkap">
    <input class="form-control mb-2" name="jumlah" type="number" value="1">
    <select class="form-select mb-2" name="aksi">
      <option value="daftar">Daftar</option>
      <option value="batal">Batalkan</option>
    </select>
    <button class="btn btn-primary">Kirim</button>
  </form>

  <h2 class="h5">Peserta terdaftar</h2>
  <ul class="list-group mb-4">
    <?php foreach ($_SESSION['peserta'] as $p): ?>
      <li class="list-group-item"><?= htmlspecialchars((string) $p['nim'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string) $p['nama'], ENT_QUOTES, 'UTF-8') ?></li>
    <?php endforeach; ?>
  </ul>

  <?php if (($_COOKIE['peran'] ?? '') === 'panitia'): ?>
    <div class="border border-danger p-3">
      <h2 class="h6">Panel panitia</h2>
      <a href="atur-ulang.php" class="btn btn-sm btn-danger">Atur ulang semua pendaftaran</a>
    </div>
  <?php endif; ?>

  <?php include __DIR__ . '/bagian-kaki.php'; ?>
</body>
</html>
