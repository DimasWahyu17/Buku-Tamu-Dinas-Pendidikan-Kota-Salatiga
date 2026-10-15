<?php
$db = new SQLite3('database.sqlite');

// Set headers agar browser tahu ini file download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="buku_tamu.csv"');

// Buka output stream
$output = fopen('php://output', 'w');

// Tulis baris header
fputcsv($output, ['No', 'Tanggal', 'Nama', 'Alamat', 'Jabatan', 'Bidang', 'Keperluan', 'Telepon', 'Keterangan', 'Rating']);

// Ambil data dari database
$data = $db->query("SELECT * FROM buku_tamu ORDER BY created_at DESC");
$no = 1;

while ($row = $data->fetchArray(SQLITE3_ASSOC)) {
    fputcsv($output, [
        $no++,
        $row['tanggal'],
        $row['nama'],
        $row['alamat'],
        $row['jabatan'],
        $row['bidang'],
        $row['keperluan'],
        $row['telepon'],
        $row['keterangan'],
        $row['rating']
    ]);
}

fclose($output);
exit;
