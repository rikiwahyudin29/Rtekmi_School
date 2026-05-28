<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=mariyadhulja_db", "root", "");
$stmt = $pdo->query("SHOW TABLES LIKE 'tbl_jadwal_kelas'");
if ($stmt->fetch()) {
    echo "tbl_jadwal_kelas EXISTS!\n";
    $stmt2 = $pdo->query("SELECT * FROM tbl_jadwal_kelas LIMIT 5");
    print_r($stmt2->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo "tbl_jadwal_kelas DOES NOT EXIST!\n";
}
