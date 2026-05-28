<?php
$pdo = new PDO("mysql:host=127.0.0.1;dbname=mariyadhulja_db", "root", "");
$stmt = $pdo->query("DESCRIBE ujian_siswa");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
