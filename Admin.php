<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

include 'koneksi.php';

// Rule-based function untuk menentukan kelayakan bansos
function tentukanKelayakan($penghasilan, $jumlah_anak) {
    if ($penghasilan >= 1000000) {
        return ['status' => 'Tidak Layak', 'alasan' => 'Penghasilan di atas atau sama dengan Rp 1.000.000'];
    }
    if ($jumlah_anak < 3) {
        return ['status' => 'Tidak Layak', 'alasan' => 'Jumlah tanggungan anak kurang dari 3'];
    }
    return ['status' => 'Layak', 'alasan' => 'Memenuhi kriteria bansos (Penghasilan < Rp 1.000.000 dan Anak >= 3)'];
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id_hapus = (int) $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM bansos WHERE id = $id_hapus");
    header("Location: admin.php");
    exit();
}

// Proses Simpan (Tambah / Edit)
if (isset($_POST['simpan'])) {
    $id = (int) $_POST['id'];
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $nama_prov = mysqli_real_escape_string($koneksi, $_POST['nama_prov']);
    $nama_kab = mysqli_real_escape_string($koneksi, $_POST['nama_kab']);
    $pekerjaan = mysqli_real_escape_string($koneksi, $_POST['pekerjaan']);
    $tanggungan = (int) $_POST['tanggungan'];
    $jumlah_anak = (int) $_POST['jumlah_anak'];
    $penghasilan = (float) $_POST['penghasilan_per_bulan'];
    
    $hasil_kelayakan = tentukanKelayakan($penghasilan, $jumlah_anak);
    $status_kelayakan = $hasil_kelayakan['status'];
    $alasan = $hasil_kelayakan['alasan'];
    
    if ($id > 0) {
        // Update
        $query = "UPDATE bansos SET 
                    nama='$nama', nama_prov='$nama_prov', nama_kab='$nama_kab', 
                    pekerjaan='$pekerjaan', tanggungan='$tanggungan', jumlah_anak='$jumlah_anak',
                    penghasilan_per_bulan='$penghasilan', 
                    status_kelayakan='$status_kelayakan',
                    alasan='$alasan'
                  WHERE id=$id";
    } else {
        // Insert
        $query = "INSERT INTO bansos (nama, nama_prov, nama_kab, pekerjaan, tanggungan, jumlah_anak, penghasilan_per_bulan, status_kelayakan, alasan) 
                  VALUES ('$nama', '$nama_prov', '$nama_kab', '$pekerjaan', '$tanggungan', '$jumlah_anak', '$penghasilan', '$status_kelayakan', '$alasan')";
    }
    mysqli_query($koneksi, $query);
    header("Location: admin.php");
    exit();
}

// Ambil data untuk Edit
$edit_id = 0;
$edit_nama = '';
$edit_nama_prov = '';
$edit_nama_kab = '';
$edit_pekerjaan = '';
$edit_tanggungan = '';
$edit_anak = '';
$edit_penghasilan = '';

if (isset($_GET['edit'])) {
    $id_edit = (int) $_GET['edit'];
    $res = mysqli_query($koneksi, "SELECT * FROM bansos WHERE id = $id_edit");
    if ($data = mysqli_fetch_assoc($res)) {
        $edit_id = $data['id'];
        $edit_nama = $data['nama'];
        $edit_nama_prov = $data['nama_prov'];
        $edit_nama_kab = $data['nama_kab'];
        $edit_pekerjaan = $data['pekerjaan'];
        $edit_tanggungan = $data['tanggungan'];
        $edit_anak = $data['jumlah_anak'];
        $edit_penghasilan = $data['penghasilan_per_bulan'];
    }
}
?>
<html>
<head>
    <title>Halaman Admin Bansos</title>
</head>
<body>

<h1>Halaman Admin - Data Lengkap Bansos</h1>
<a href="index.php">Lihat Web Warga</a> | <a href="logout.php">Logout</a>
<hr>

<h3><?= ($edit_id > 0) ? 'Edit Data Warga' : 'Tambah Data Warga' ?></h3>
<form method="POST" action="admin.php">
    <input type="hidden" name="id" value="<?= $edit_id ?>">
    <table border="0" cellpadding="5">
        <tr><td>Nama Lengkap</td><td>: <input type="text" name="nama" value="<?= $edit_nama ?>" required></td></tr>
        <tr><td>Provinsi</td><td>: <input type="text" name="nama_prov" value="<?= $edit_nama_prov ?>" required></td></tr>
        <tr><td>Kabupaten/Kota</td><td>: <input type="text" name="nama_kab" value="<?= $edit_nama_kab ?>" required></td></tr>
        <tr><td>Pekerjaan</td><td>: <input type="text" name="pekerjaan" value="<?= $edit_pekerjaan ?>" required></td></tr>
        <tr><td>Tanggungan</td><td>: <input type="number" name="tanggungan" value="<?= $edit_tanggungan ?>" required></td></tr>
        <tr><td>Jumlah Anak</td><td>: <input type="number" name="jumlah_anak" value="<?= $edit_anak ?>" required></td></tr>
        <tr><td>Penghasilan / Bulan</td><td>: <input type="number" name="penghasilan_per_bulan" value="<?= $edit_penghasilan ?>" required></td></tr>
        <tr>
            <td></td>
            <td>
                <button type="submit" name="simpan"><?= ($edit_id > 0) ? 'Update Data' : 'Tambah Data' ?></button>
                <?php if ($edit_id > 0): ?> <a href="admin.php">Batal Edit</a> <?php endif; ?>
            </td>
        </tr>
    </table>
</form>

<hr>

<h3>Daftar Warga</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Provinsi</th>
        <th>Kabupaten/Kota</th>
        <th>Pekerjaan</th>
        <th>Tanggungan</th>
        <th>Anak</th>
        <th>Penghasilan (Rp)</th>
        <th>Status</th>
        <th>Alasan</th>
        <th>Aksi</th>
    </tr>
    <?php
    $result = mysqli_query($koneksi, "SELECT * FROM bansos ORDER BY id DESC");
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $status_tampil = ($row['status_kelayakan'] == 'Layak') ? 'LULUS' : 'TIDAK LULUS';
            echo "<tr>
                    <td>{$row['id']}</td>
                    <td>{$row['nama']}</td>
                    <td>{$row['nama_prov']}</td>
                    <td>{$row['nama_kab']}</td>
                    <td>{$row['pekerjaan']}</td>
                    <td>{$row['tanggungan']}</td>
                    <td>{$row['jumlah_anak']}</td>
                    <td>" . number_format($row['penghasilan_per_bulan'], 0, ',', '.') . "</td>
                    <td><b>{$status_tampil}</b></td>
                    <td>{$row['alasan']}</td>
                    <td>
                        <a href='admin.php?edit={$row['id']}'>Edit</a> | 
                        <a href='admin.php?hapus={$row['id']}' onclick='return confirm(\"Yakin hapus?\");'>Hapus</a>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='11' align='center'>Belum ada data</td></tr>";
    }
    ?>
</table>

</body>
</html>
