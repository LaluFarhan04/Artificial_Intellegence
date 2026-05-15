<?php
include 'koneksi.php';

// Rule-based function untuk menentukan kelayakan bansos
function tentukanKelayakan($penghasilan, $jumlah_anak) {
    // 1 juta atau lebih tidak lulus (Tidak Layak)
    if ($penghasilan >= 1000000) {
        return ['status' => 'Tidak Layak', 'alasan' => 'Penghasilan di atas atau sama dengan Rp 1.000.000'];
    }
    
    // Jumlah anak kurang dari 3 tidak lulus
    if ($jumlah_anak < 3) {
        return ['status' => 'Tidak Layak', 'alasan' => 'Jumlah tanggungan anak kurang dari 3'];
    }
    
    return ['status' => 'Layak', 'alasan' => 'Memenuhi kriteria bansos (Penghasilan < Rp 1.000.000 dan Anak >= 3)'];
}

if (isset($_POST['submit'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $nama_prov = mysqli_real_escape_string($koneksi, $_POST['nama_prov']);
    $nama_kab = mysqli_real_escape_string($koneksi, $_POST['nama_kab']);
    $pekerjaan = mysqli_real_escape_string($koneksi, $_POST['pekerjaan']);
    $tanggungan = (int) $_POST['tanggungan'];
    $jumlah_anak = (int) $_POST['jumlah_anak'];
    $penghasilan = (float) $_POST['penghasilan_per_bulan'];
    
    // Cek apakah nama sudah ada di database (DINONAKTIFKAN SEMENTARA SESUAI PERMINTAAN)
    /*
    $cek_nama = mysqli_query($koneksi, "SELECT id FROM bansos WHERE nama = '$nama'");
    if (mysqli_num_rows($cek_nama) > 0) {
        $pesan_error = "Maaf, nama '$nama' sudah terdaftar. Tidak bisa mendaftar dua kali.";
    } else {
    */
        $hasil_kelayakan = tentukanKelayakan($penghasilan, $jumlah_anak);
        $status_kelayakan = $hasil_kelayakan['status'];
        $alasan = $hasil_kelayakan['alasan'];
        
        $query = "INSERT INTO bansos (nama, nama_prov, nama_kab, pekerjaan, tanggungan, jumlah_anak, penghasilan_per_bulan, status_kelayakan, alasan) 
                  VALUES ('$nama', '$nama_prov', '$nama_kab', '$pekerjaan', '$tanggungan', '$jumlah_anak', '$penghasilan', '$status_kelayakan', '$alasan')";
                  
        if (mysqli_query($koneksi, $query)) {
            $status_tampil = ($status_kelayakan == 'Layak') ? 'LULUS' : 'TIDAK LULUS';
            $warna_teks = ($status_kelayakan == 'Layak') ? 'green' : 'red';
            $pesan = "
                <fieldset style='border: 2px solid $warna_teks;'>
                    <legend><h3 style='color: $warna_teks; margin: 0;'>Hasil Pengecekan</h3></legend>
                    <table border='0' cellpadding='5'>
                        <tr><td>Nama</td><td>: <b>$nama</b></td></tr>
                        <tr><td>Provinsi</td><td>: <b>$nama_prov</b></td></tr>
                        <tr><td>Kab/Kota</td><td>: <b>$nama_kab</b></td></tr>
                        <tr><td>Pekerjaan</td><td>: <b>$pekerjaan</b></td></tr>
                        <tr><td>Tanggungan</td><td>: <b>$tanggungan</b></td></tr>
                        <tr><td>Jml Anak</td><td>: <b>$jumlah_anak</b></td></tr>
                        <tr><td>Penghasilan</td><td>: <b>Rp " . number_format($penghasilan, 0, ',', '.') . "</b></td></tr>
                    </table>
                    <hr>
                    <b>Status: <span style='color: $warna_teks;'>$status_tampil</span></b><br>
                    Alasan: $alasan
                    <br><br>
                    <div style='text-align: center;'>
                        <a href='index.php'><button type='button' style='padding: 8px 15px; font-weight: bold; cursor: pointer;'>Cek Warga Baru</button></a>
                    </div>
                </fieldset>
            ";
        } else {
            $pesan_error = "Gagal menambah data: " . mysqli_error($koneksi);
        }
    /* } */
}
?>
<html>
<head>
    <title>Cek Kelayakan Bansos</title>
</head>
<body>

<h1>Cek Kelayakan Bansos Warga</h1>
<hr>

<div align="center" style="margin-top: 30px;">
    <!-- Form (Atas) -->
    <fieldset style="width: 350px; text-align: left;">
        <legend><h3>Isi Data Anda</h3></legend>
        <form method="POST" action="">
            <table border="0" cellpadding="5">
                <tr><td>Nama Lengkap</td><td>: <input type="text" name="nama" required></td></tr>
                <tr><td>Provinsi</td><td>: <input type="text" name="nama_prov" required></td></tr>
                <tr><td>Kabupaten/Kota</td><td>: <input type="text" name="nama_kab" required></td></tr>
                <tr><td>Pekerjaan</td><td>: <input type="text" name="pekerjaan" required></td></tr>
                <tr><td>Tanggungan</td><td>: <input type="number" name="tanggungan" required></td></tr>
                <tr><td>Jumlah Anak</td><td>: <input type="number" name="jumlah_anak" required></td></tr>
                <tr><td>Penghasilan/Bulan</td><td>: <input type="number" name="penghasilan_per_bulan" required></td></tr>
                <tr>
                    <td colspan="2" align="center"><button type="submit" name="submit">Cek Status Kelayakan</button></td>
                </tr>
            </table>
        </form>
        <div style="text-align: center; margin-top: 10px;">
            <a href="status_kelayakan.php">Lihat Daftar Kelayakan Warga</a>
        </div>
    </fieldset>

    <!-- Hasil (Bawah) - Hanya muncul jika sudah di-submit -->
    <?php if (isset($pesan) || isset($pesan_error)): ?>
    <div style="display: block; text-align: left; width: 450px; margin: 20px auto 0 auto;">
        <?php 
        if (isset($pesan)) echo $pesan;
        if (isset($pesan_error)) echo "<p style='color:red; text-align:center;'><b>$pesan_error</b></p>"; 
        ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
