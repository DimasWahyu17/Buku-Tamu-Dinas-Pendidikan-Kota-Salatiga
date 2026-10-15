<?php
$db = new SQLite3('database.sqlite');

// Buat tabel jika belum ada
$db->exec("
    CREATE TABLE IF NOT EXISTS buku_tamu (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        tanggal TEXT NOT NULL,
        keperluan TEXT NOT NULL,
        nama TEXT NOT NULL,
        telepon TEXT NOT NULL,
        alamat TEXT NOT NULL,
        keterangan TEXT, -- boleh kosong
        jabatan TEXT NOT NULL,
        rating INTEGER NOT NULL,
        bidang TEXT NOT NULL,
        foto TEXT NOT NULL,
        created_at TEXT NOT NULL
    )
");

echo "✅ Tabel 'buku_tamu' berhasil dibuat atau sudah ada.";
