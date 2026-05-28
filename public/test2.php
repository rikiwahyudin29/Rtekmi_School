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

$stmt2 = $pdo->query("SELECT GROUP_CONCAT(DISTINCT mk.nama_kelas SEPARATOR ', ') as kelas
                      FROM ujian_siswa us 
                      LEFT JOIN tbl_siswa ms ON ms.id = us.siswa_id 
                      LEFT JOIN tbl_kelas mk ON mk.id = ms.kelas_id 
                      WHERE us.jadwal_id = $jadwal_id");
$concat = $stmt2->fetchAll();

echo json_encode([
    'concat' => $concat
]);
