<?php
include 'koneksi.php';
?>
<html>
<head>
    <title>Status Kelayakan Bansos</title>
</head>
<body>

<h1>Data Status Kelayakan Penerima Bansos</h1>
<p>Berikut adalah daftar warga yang telah mendaftar beserta status dan alasan kelayakan mereka:</p>

<h3>Aturan Kelayakan Bansos (Sistem)</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>Prioritas</th>
        <th>Nama Aturan</th>
        <th>Kondisi</th>
        <th>Keputusan</th>
        <th>Keterangan</th>
    </tr>
    <?php
    $rules_query = mysqli_query($koneksi, "SELECT * FROM rules ORDER BY priority_order ASC, id ASC");
    if ($rules_query && mysqli_num_rows($rules_query) > 0) {
        while ($r = mysqli_fetch_assoc($rules_query)) {
            $cond_strs = [];
            $cond_query = mysqli_query($koneksi, "SELECT * FROM conditions WHERE rule_id = " . $r['id'] . " ORDER BY id ASC");
            $idx = 0;
            while ($c = mysqli_fetch_assoc($cond_query)) {
                $prefix = ($idx > 0) ? " <b>" . htmlspecialchars($c['logical_operator']) . "</b> " : "";
                $cond_strs[] = $prefix . htmlspecialchars($c['variable_name']) . " " . htmlspecialchars($c['operator']) . " '" . htmlspecialchars($c['target_value']) . "'";
                $idx++;
            }
            $kondisi_gabungan = implode("<br>", $cond_strs);
            if (empty($kondisi_gabungan)) $kondisi_gabungan = "-";
            
            echo "<tr>
                    <td align='center'>{$r['priority_order']}</td>
                    <td><b>{$r['rule_name']}</b></td>
                    <td>{$kondisi_gabungan}</td>
                    <td>{$r['conclusion_value']}</td>
                    <td>{$r['description']}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5' align='center'>Belum ada aturan.</td></tr>";
    }
    ?>
</table>

<hr>

<h3>Data Pendaftar Bansos</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>No</th>
        <th>Nama Lengkap</th>
        <th>Provinsi</th>
        <th>Kabupaten/Kota</th>
        <th>Status Kelayakan</th>
        <th>Alasan Lengkap</th>
    </tr>
    <?php
    $no = 1;
    $result = mysqli_query($koneksi, "SELECT * FROM bansos ORDER BY id DESC");
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $status_tampil = ($row['status_kelayakan'] == 'Layak') ? 'LULUS' : 'TIDAK LULUS';
            echo "<tr>
                    <td>{$no}</td>
                    <td>{$row['nama']}</td>
                    <td>{$row['nama_prov']}</td>
                    <td>{$row['nama_kab']}</td>
                    <td>{$status_tampil}</td>
                    <td>{$row['alasan']}</td>
                  </tr>";
            $no++;
        }
    } else {
        echo "<tr><td colspan='6' align='center'>Belum ada data warga.</td></tr>";
    }
    ?>
</table>

<br>
<a href="index.php">Kembali ke Halaman Utama</a>

</body>
</html>
