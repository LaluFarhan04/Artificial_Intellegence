CREATE DATABASE bansos_desa;
USE bansos_desa;

CREATE TABLE bansos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nama_prov VARCHAR(100) NOT NULL,
    nama_kab VARCHAR(100) NOT NULL,
    pekerjaan VARCHAR(100) NOT NULL,
    tanggungan INT NOT NULL,
    jumlah_anak INT NOT NULL,
    penghasilan_per_bulan DECIMAL(12,2) NOT NULL,
    status_kelayakan ENUM('Layak','Tidak Layak') NOT NULL,
    alasan VARCHAR(255) NOT NULL
);

INSERT INTO bansos (nama, nama_prov, nama_kab, pekerjaan, tanggungan, jumlah_anak, penghasilan_per_bulan, status_kelayakan) VALUES
('Ahmad', 'Jawa Barat', 'Bandung', 'Buruh Harian', 4, 3, 800000, 'Layak'),
('Rina', 'DKI Jakarta', 'Jakarta Selatan', 'PNS', 4, 3, 900000, 'Tidak Layak'),
('Budi', 'Jawa Tengah', 'Semarang', 'Pengangguran', 5, 4, 4000000, 'Tidak Layak'),
('Siti', 'Jawa Timur', 'Surabaya', 'Petani Kecil', 6, 4, 1000000, 'Tidak Layak'),
('Dewi', 'Banten', 'Tangerang', 'Pegawai Tetap', 2, 1, 3500000, 'Tidak Layak');

-- Tabel untuk menyimpan Rule/Aturan
CREATE TABLE rules (
    id INT PRIMARY KEY,
    rule_name VARCHAR(100) NOT NULL,
    priority_order INT NOT NULL,
    conclusion_variable VARCHAR(50) NOT NULL,
    conclusion_value VARCHAR(100) NOT NULL,
    description TEXT
);

-- Tabel untuk menyimpan Kondisi dari setiap Rule
CREATE TABLE conditions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rule_id INT NOT NULL,
    variable_name VARCHAR(50) NOT NULL,
    operator VARCHAR(10) NOT NULL,
    target_value VARCHAR(255) NOT NULL,
    logical_operator VARCHAR(10) DEFAULT 'AND',
    FOREIGN KEY (rule_id) REFERENCES rules(id) ON DELETE CASCADE
);

-- RULE 1: Cek Pekerjaan (Pengecualian Utama)
INSERT INTO rules (id, rule_name, priority_order, conclusion_variable, conclusion_value, description) 
VALUES (1, 'Aturan Profesi Mapan', 1, 'rekomendasi', 'TIDAK LAYAK', 'Penerima adalah PNS, TNI, POLRI, atau BUMN yang tidak berhak menerima bantuan.');
INSERT INTO conditions (rule_id, variable_name, operator, target_value) 
VALUES (1, 'pekerjaan', 'IN', 'PNS,TNI,POLRI,BUMN');

-- RULE 2: Cek DTKS
INSERT INTO rules (id, rule_name, priority_order, conclusion_variable, conclusion_value, description) 
VALUES (2, 'Aturan DTKS', 1, 'rekomendasi', 'TIDAK LAYAK', 'Nama tidak terdaftar dalam database DTKS Kemensos.');
INSERT INTO conditions (rule_id, variable_name, operator, target_value) 
VALUES (2, 'dtks', '=', 'Tidak');


-- RULE 6: PKH Pendidikan
INSERT INTO rules (id, rule_name, priority_order, conclusion_variable, conclusion_value, description) 
VALUES (6, 'PKH Pendidikan', 3, 'rekomendasi', 'LAYAK TERIMA PKH PENDIDIKAN', 'Keluarga sangat miskin dan memiliki anak sekolah.');
INSERT INTO conditions (rule_id, variable_name, operator, target_value) VALUES (6, 'tmp_status', '=', 'SANGAT MISKIN');
INSERT INTO conditions (rule_id, variable_name, operator, target_value) VALUES (6, 'sekolah', '=', 'Ya');

-- RULE 7: BPNT (Sembako)
INSERT INTO rules (id, rule_name, priority_order, conclusion_variable, conclusion_value, description) 
VALUES (7, 'Bantuan Sembako (BPNT)', 3, 'rekomendasi', 'LAYAK TERIMA BPNT', 'Keluarga klasifikasi Miskin berhak menerima bantuan pangan.');
INSERT INTO conditions (rule_id, variable_name, operator, target_value) VALUES (7, 'tmp_status', '=', 'MISKIN');

-- RULE 8: BLT Dana Desa (Untuk yang tidak masuk Desil 1-4 tapi tidak kerja)
INSERT INTO rules (id, rule_name, priority_order, conclusion_variable, conclusion_value, description) 
VALUES (8, 'BLT Dana Desa', 3, 'rekomendasi', 'LAYAK TERIMA BLT DANA DESA', 'Warga tidak bekerja namun tidak masuk dalam bantuan PKH/BPNT.');
INSERT INTO conditions (rule_id, variable_name, operator, target_value) VALUES (8, 'pekerjaan', '=', 'Tidak Kerja');
INSERT INTO conditions (rule_id, variable_name, operator, target_value) VALUES (8, 'desil', 'IN', '5,6');

-- RULE 9: Penolakan Desil Tinggi
INSERT INTO rules (id, rule_name, priority_order, conclusion_variable, conclusion_value, description) 
VALUES (9, 'Aturan Ekonomi Mampu', 1, 'rekomendasi', 'TIDAK LAYAK', 'Ekonomi di atas Desil 7 dianggap sudah mampu.');
INSERT INTO conditions (rule_id, variable_name, operator, target_value) 
VALUES (9, 'desil', 'IN', '7,8,9,10');

-- RULE 10: PKH Lansia
INSERT INTO rules (id, rule_name, priority_order, conclusion_variable, conclusion_value, description) 
VALUES (10, 'PKH Lansia', 3, 'rekomendasi', 'LAYAK TERIMA PKH LANSIA', 'Keluarga sangat miskin dengan tanggungan lansia.');
INSERT INTO conditions (rule_id, variable_name, operator, target_value) VALUES (10, 'tmp_status', '=', 'SANGAT MISKIN');
INSERT INTO conditions (rule_id, variable_name, operator, target_value) VALUES (10, 'pekerjaan', '=', 'Tidak Kerja');
