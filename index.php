<?php
$db = new SQLite3('database.sqlite');

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$totalQuery = $db->querySingle("SELECT COUNT(*) FROM buku_tamu");
$totalPages = ceil($totalQuery / $limit);

$data = $db->query("SELECT * FROM buku_tamu ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
setlocale(LC_TIME, 'id_ID.UTF-8');

function formatTanggalIndoLengkap($tanggal) {
    $hariArray = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $bulanArray = [
        '01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April',
        '05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus',
        '09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'
    ];
    $tglObj = DateTime::createFromFormat('Y-m-d', $tanggal);
    if (!$tglObj) return $tanggal;
    $hari = $hariArray[(int)$tglObj->format('w')];
    $tgl = $tglObj->format('d');
    $bulan = $bulanArray[$tglObj->format('m')];
    $tahun = $tglObj->format('Y');
    return "$hari, $tgl $bulan $tahun";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Buku Tamu Dinas Pendidikan Kota Salatiga</title>
  <link rel="stylesheet" href="styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    .pagination-wrapper {
      display: flex;
      justify-content: center;
      margin-top: 20px;
    }
    .pagination {
      margin-bottom: 0;
    }
    .pagination li a, .pagination li span {
      border-radius: 6px !important;
      margin: 0 3px;
    }
  </style>
</head>
<body>
<header>
  <div class="container">
    <div class="header-container">
      <div class="header-logo-left">
        <img src="images/Lambang_Kota_Salatiga.png" class="logo_dikdas" alt="Logo Kota" />
      </div>
      <div class="header-text">
        <h1>Dinas Pendidikan Kota Salatiga</h1>
        <p>Sampaikan kunjungan dan penilaian Anda terhadap pelayanan</p>
        <p>📞 0813-3035-5957</p>
      </div>
      <div class="header-logo-right">
        <img src="images/Disdik_Logo.png" class="logo" alt="Logo Dinas" />
      </div>
    </div>
  </div>
</header>

<main>
<?php if (isset($_GET['thanks']) && $_GET['thanks'] == '1'): ?>
  <div class="popup">✅ Terima kasih sudah melakukan penilaian di layanan kami.</div>
<?php endif; ?>

<section class="form-card">
  <h2>📋 Formulir Buku Tamu</h2>
  <form id="guestForm" action="save.php" method="POST" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="tanggal" value="<?= date('Y-m-d') ?>">
    <?php
    $fields = [
        ['nama', '🧑 Nama', 'text', 'Masukkan nama lengkap'],
        ['keperluan', '🎯 Keperluan', 'text', 'Masukkan keperluan Anda'],
        ['telepon', '📞 No. Telepon', 'text', 'Masukkan nomor telepon'],
        ['alamat', '🏠 Alamat/Asal Lembaga', 'text', 'Masukkan asal lembaga/Alamat lengkap'],
        ['jabatan', '💼 Jabatan/Pekerjaan', 'text', 'Masukkan jabatan atau pekerjaan'],
        ['bidang', '🏢 Bidang yang Dituju', 'text', 'Masukkan bidang tujuan']
    ];
    foreach ($fields as $field) {
        echo '<div class="form-group">';
        echo '<label for="'.$field[0].'">'.$field[1].':<span class="required">*</span></label>';
        echo '<input type="'.$field[2].'" id="'.$field[0].'" name="'.$field[0].'" required placeholder="'.$field[3].'">';
        echo '</div>';
    }
    ?>
    <div class="form-group full-width">
      <label for="keterangan">🗒️ Keterangan:</label>
      <textarea id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan kunjungan (boleh kosong)..."></textarea>
    </div>
    <!-- <div class="form-group full-width">
      <label for="rating">⭐ Rating Layanan:<span class="required">*</span></label>
      <select id="rating" name="rating" required>
        <option value="">Pilih rating</option>
        <option value="5">⭐⭐⭐⭐⭐ (Sangat Baik)</option>
        <option value="4">⭐⭐⭐⭐ (Baik)</option>
        <option value="3">⭐⭐⭐ (Cukup)</option>
        <option value="2">⭐⭐ (Kurang)</option>
        <option value="1">⭐ (Buruk)</option>
      </select>
    </div> -->
    <div class="form-group full-width">
      <label for="foto">📷 Foto Tamu:<span class="required">*</span></label><br />
      <video id="video" width="320" height="240" autoplay muted playsinline style="border:1px solid #ccc;"></video>
      <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
      <input type="hidden" name="foto" id="fotoInput" required />
      <p><small>Tekan tombol di bawah untuk mengambil foto dan kirim formulir.</small></p>
      <button type="button" id="captureBtn">📸 Ambil Foto & Kirim</button>
    </div>
    <button type="submit" id="submitBtn" style="display:none;">📝 Simpan</button>
  </form>
</section>

<section class="data-card" style="position: relative;">
  <h2>📑 Daftar Pengunjung</h2>
    

	  
  <div class="table-wrapper" style="overflow-x:auto;">
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Hari/Tanggal</th>
          <th>Nama</th>
          <th>Alamat</th>
          <th>Jabatan</th>
          <th>Bidang</th>
          <th>Keperluan</th>
          <th>Telepon</th>
          <th>Keterangan</th>
          <th>Rating</th>
          <th>Foto</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $no = $offset + 1;
      $ratingText = [1 => 'Buruk', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Baik', 5 => 'Sangat Baik'];
      while ($row = $data->fetchArray(SQLITE3_ASSOC)):
      ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= formatTanggalIndoLengkap($row['tanggal']) ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['alamat']) ?></td>
          <td><?= htmlspecialchars($row['jabatan']) ?></td>
          <td><?= htmlspecialchars($row['bidang']) ?></td>
          <td><?= htmlspecialchars($row['keperluan']) ?></td>
          <td><?= htmlspecialchars($row['telepon']) ?></td>
          <td><?= htmlspecialchars($row['keterangan']) ?></td>
          <td><?= isset($ratingText[(int)$row['rating']]) ? $ratingText[(int)$row['rating']] : '-' ?></td>
          <td>
            <?php if (!empty($row['foto'])): ?>
              <img src="uploads/<?= htmlspecialchars($row['foto']) ?>" alt="Foto Tamu" style="max-width:100px;" />
            <?php else: ?> 
              -
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination + Export Button -->
<div class="d-flex justify-content-between align-items-center mt-3 flex-wrap" style="gap: 1rem;">

  <!-- Pagination -->
  <div class="pagination-wrapper">
    <nav aria-label="Page navigation">
      <ul class="pagination pagination-sm mb-0">
        <?php if ($page > 1): ?>
          <li class="page-item"><a class="page-link" href="?page=1">⏮</a></li>
          <li class="page-item"><a class="page-link" href="?page=<?= $page - 1 ?>">←</a></li>
        <?php endif; ?>

        <?php
        $range = 2;
        for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++):
        ?>
          <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
          <li class="page-item"><a class="page-link" href="?page=<?= $page + 1 ?>">→</a></li>
          <li class="page-item"><a class="page-link" href="?page=<?= $totalPages ?>">⏭</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>

  <!-- Export Button -->
  <a href="export_csv.php" class="btn btn-success">
    📥 Export ke Excel (CSV)
  </a>
</div>


</section>
</main>

<script>
(() => {
  const video = document.getElementById('video');
  const canvas = document.getElementById('canvas');
  const fotoInput = document.getElementById('fotoInput');
  const form = document.getElementById('guestForm');
  const captureBtn = document.getElementById('captureBtn');
  let stream = null;

  function stopCamera() {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
      stream = null;
    }
  }

  navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
    .then(s => {
      stream = s;
      video.srcObject = stream;
    })
    .catch(err => {
      alert('Gagal mengakses kamera: ' + err);
    });

  captureBtn.addEventListener('click', () => {
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const dataURL = canvas.toDataURL('image/png');
    fotoInput.value = dataURL;
    form.submit();
    stopCamera();
  });

  form.addEventListener('submit', e => {
    if (!fotoInput.value) {
      alert('Foto belum diambil. Silakan tekan tombol "Ambil Foto & Kirim".');
      e.preventDefault();
    }
  });

  window.addEventListener('beforeunload', () => stopCamera());
})();
</script>
</body>
</html>
