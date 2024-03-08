<?php

try {
    $pdo = new PDO(
        "sqlsrv:Server=172.18.3.91\\SIAPPSVR;Database=SIAPP_AIIA",
        "sikola",
        "SusahBanget23#"
    );

    echo "Koneksi ke database berhasil!";
} catch (PDOException $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}
