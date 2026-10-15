<?php
$db = new SQLite3('database.sqlite');

// Buat folder uploads jika belum ada
if (!file_exists('uploads')) {
    mkdir('uploads', 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = date('Y-m-d');
    $keperluan = isset($_POST['keperluan']) ? trim($_POST['keperluan']) : '';
    $nama = isset($_POST['nama']) ? trim($_POST['nama']) : '';
    $telepon = isset($_POST['telepon']) ? trim($_POST['telepon']) : '';
    $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
    $keterangan = isset($_POST['keterangan']) ? trim($_POST['keterangan']) : '';
    $jabatan = isset($_POST['jabatan']) ? trim($_POST['jabatan']) : '';
    $bidang = isset($_POST['bidang']) ? trim($_POST['bidang']) : '';
    $fotoBase64 = isset($_POST['foto']) ? $_POST['foto'] : '';
    $created_at = date('Y-m-d H:i:s');

    // Validasi
    if (
        !$keperluan || !$nama || !$telepon || !$alamat ||
        !$jabatan || !$bidang || !$fotoBase64
    ) {
        die("Semua field wajib diisi (kecuali keterangan), dan foto harus diambil.");
    }

    // Proses Foto
    $fotoBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $fotoBase64);
    $fotoData = base64_decode($fotoBase64);
    if ($fotoData === false) {
        die("Format foto tidak valid.");
    }

    $safeNama = preg_replace('/[^a-zA-Z0-9_\-]/', '', $nama);
    $fotoName = time() . '_' . $safeNama . '.png';
    $filePath = 'uploads/' . $fotoName;

    if (file_put_contents($filePath, $fotoData) === false) {
        die("Gagal menyimpan file foto.");
    }

    // Simpan ke DB, rating awal = 0
    $stmt = $db->prepare("
        INSERT INTO buku_tamu 
        (tanggal, keperluan, nama, telepon, alamat, keterangan, jabatan, rating, bidang, foto, created_at)
        VALUES 
        (:tanggal, :keperluan, :nama, :telepon, :alamat, :keterangan, :jabatan, :rating, :bidang, :foto, :created_at)
    ");

    $stmt->bindValue(':tanggal', $tanggal, SQLITE3_TEXT);
    $stmt->bindValue(':keperluan', $keperluan, SQLITE3_TEXT);
    $stmt->bindValue(':nama', $nama, SQLITE3_TEXT);
    $stmt->bindValue(':telepon', $telepon, SQLITE3_TEXT);
    $stmt->bindValue(':alamat', $alamat, SQLITE3_TEXT);
    $stmt->bindValue(':keterangan', $keterangan, SQLITE3_TEXT);
    $stmt->bindValue(':jabatan', $jabatan, SQLITE3_TEXT);
    $stmt->bindValue(':rating', 0, SQLITE3_INTEGER); // rating diset 0 dulu
    $stmt->bindValue(':bidang', $bidang, SQLITE3_TEXT);
    $stmt->bindValue(':foto', $fotoName, SQLITE3_TEXT);
    $stmt->bindValue(':created_at', $created_at, SQLITE3_TEXT);

    $result = $stmt->execute();

    if ($result) {
        $last_id = $db->lastInsertRowID();

        // 🔁 Redirect langsung ke beri_rating.php
        header("Location: beri_rating.php?id=$last_id");
        exit;
    } else {
        $error = $db->lastErrorMsg();
        die("Gagal menyimpan data: " . $error);
    }
} else {
    die("Metode tidak diperbolehkan.");
}
