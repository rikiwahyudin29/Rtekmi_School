<?php
$host = '127.0.0.1';
$db   = 'mariyadhulja_db';
$user = 'root'; // default for xampp
$pass = ''; // default for xampp
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$jadwal_id = 41;

$sql = "SELECT us.*, s.nama_lengkap as nama, s.nis as nomor_induk, k.nama_kelas, ju.bobot_pg as bobot_tf, ju.bobot_esai, du.visible_pg, du.visible_pgmulti, du.visible_pgtf, du.visible_pgcouple, du.visible_shortentry, du.visible_esai 
        FROM ujian_siswa as us 
        LEFT JOIN tbl_siswa as s ON s.id = us.siswa_id 
        LEFT JOIN tbl_kelas as k ON k.id = s.kelas_id 
        LEFT JOIN tbl_jadwal_ujian as ju ON ju.id = us.jadwal_id 
        LEFT JOIN draft_ujian as du ON du.id = ju.id_bank_soal 
        WHERE us.jadwal_id = $jadwal_id";
        
$stmt = $pdo->query($sql);
$data = $stmt->fetchAll();

echo json_encode([
    'data' => $data
]);
